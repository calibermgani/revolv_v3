# ============================================================
# inventory upload
# laravel -> python -> excel/csv -> dynamic table bulk load
# ============================================================

import pymysql
import pandas as pd
import os
import sys
import json
import time
import re
import csv
from datetime import datetime


# ============================================================
# config
# ============================================================

db_config = {
    "host": "127.0.0.1",
    "port": 3306,
    "user": "root",
    "password": "resolv@2025!",
    "database": "resolv",
    "connect_timeout": 30,
    "read_timeout": 600,
    "write_timeout": 600,
    "charset": "utf8mb4",
    "cursorclass": pymysql.cursors.DictCursor,
    "local_infile": True,
    "autocommit": False
}

# ============================================================
# duplicate configuration
# ============================================================
# These columns are ignored while comparing duplicate row values.
# CE_Assigned is not a column. It is a value inside chart_status.
DUPLICATE_IGNORE_COLUMNS = {
    "invoke_date",
    "ar_emp_id",
    "ce_emp_id"
}

# chart_status is available in the dynamic table only.
# It is not required in inventory_upload_configuration data_columns/db_columns.
CHART_STATUS_COLUMN = "chart_status"

# Existing row blocks new insert only when chart_status = CE_Assigned.
DUPLICATE_BLOCKING_CHART_STATUS = "CE_Assigned"

base_dir = os.path.dirname(
    os.path.abspath(__file__)
)

error_log_file = os.path.join(
    base_dir,
    "local_db_error.log"
)


# ============================================================
# log helpers
# ============================================================

def log_info(message):

    print(
        "[info] " + str(message),
        file=sys.stderr,
        flush=True
    )


def log_error(message):

    print(
        "[error] " + str(message),
        file=sys.stderr,
        flush=True
    )

    with open(error_log_file, "a", encoding="utf-8") as f:

        f.write(
            datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            + " "
            + str(message)
            + "\n"
        )


def send_success(message, data):

    result = {
        "status": "success",
        "message": message,
        "data": data
    }

    print(
        json.dumps(result),
        flush=True
    )


def send_warning(message):

    result = {
        "status": "warning",
        "message": str(message)
    }

    print(
        json.dumps(result),
        flush=True
    )


# ============================================================
# input helpers
# ============================================================

def read_input_once():

    raw_input_data = sys.stdin.read()

    log_info("input received")

    if not raw_input_data.strip():

        raise Exception("empty input received from laravel")

    input_data = json.loads(raw_input_data)

    if "file_path" not in input_data:

        raise Exception("file_path is missing")

    if "project_id" not in input_data:

        raise Exception("project_id is missing")

    if "sub_project_id" not in input_data:

        raise Exception("sub_project_id is missing")

    if "file_name" not in input_data or not input_data["file_name"]:

        input_data["file_name"] = os.path.basename(
            input_data["file_path"]
        )

    return input_data


def validate_file_path_matches_project_folder(input_data):

    file_path = os.path.realpath(
        str(input_data["file_path"])
    )

    project_id = str(
        input_data["project_id"]
    ).strip()

    sub_project_id = str(
        input_data["sub_project_id"]
    ).strip()

    file_name = str(
        input_data.get("file_name", "")
    ).strip()

    if not os.path.isfile(file_path):

        raise Exception(
            "inventory not uploaded: file not found at uploaded path"
        )

    actual_file_name = os.path.basename(
        file_path
    )

    if file_name and file_name != actual_file_name:

        raise Exception(
            "inventory not uploaded: payload file name does not match uploaded file path"
        )

    normalized_parts = os.path.normpath(
        file_path
    ).split(os.sep)

    lower_parts = [
        str(part).lower()
        for part in normalized_parts
    ]

    if "inventory_uploads" not in lower_parts:

        raise Exception(
            "inventory not uploaded: file is not inside inventory_uploads folder"
        )

    upload_index = lower_parts.index(
        "inventory_uploads"
    )

    expected_project_index = upload_index + 1

    expected_sub_project_index = upload_index + 2

    if len(normalized_parts) <= expected_sub_project_index:

        raise Exception(
            "inventory not uploaded: uploaded file path must be inventory_uploads/"
            + project_id
            + "/"
            + sub_project_id
        )

    file_project_folder = normalized_parts[
        expected_project_index
    ]

    file_sub_project_folder = normalized_parts[
        expected_sub_project_index
    ]

    if file_project_folder != project_id or file_sub_project_folder != sub_project_id:

        raise Exception(
            "inventory not uploaded: uploaded file path does not match selected project and sub project folder. expected path inventory_uploads/"
            + project_id
            + "/"
            + sub_project_id
        )

    input_data["file_path"] = file_path

    input_data["file_name"] = actual_file_name

    return input_data


# ============================================================
# mysql helpers
# ============================================================

def get_connection():

    conn = pymysql.connect(
        **db_config
    )

    with conn.cursor() as cursor:

        cursor.execute("set session net_read_timeout = 600")

        cursor.execute("set session net_write_timeout = 600")

        cursor.execute("set session wait_timeout = 28800")

    log_info("mysql connected")

    return conn


def validate_db(conn):

    with conn.cursor() as cursor:

        cursor.execute("select version() as version")

        result = cursor.fetchone()

        log_info(
            "mysql version : "
            + str(result["version"])
        )

        cursor.execute("show variables like 'local_infile'")

        local_infile = cursor.fetchone()

        if local_infile and str(local_infile.get("Value", "")).lower() != "on":

            raise Exception(
                "inventory not uploaded: mysql local_infile is off. enable local_infile in azure mysql server parameters"
            )


# ============================================================
# configuration helpers
# ============================================================

def normalize_column_name(column_name):

    column_name = str(column_name).strip().lower()

    column_name = re.sub(
        r"[^a-z0-9]+",
        "_",
        column_name
    )

    return column_name.strip("_")


def clean_db_column_name(column_name):

    column_name = str(column_name).strip()

    column_name = column_name.replace(
        "`",
        ""
    )

    return column_name


def parse_configuration_columns(
        value,
        field_name,
        required,
        normalize_for_file
):

    if value is None:

        columns = []

    elif isinstance(value, list):

        columns = value

    else:

        raw_value = str(value).strip()

        if raw_value == "":

            columns = []

        else:

            try:

                decoded = json.loads(
                    raw_value
                )

                if isinstance(decoded, list):

                    columns = decoded

                else:

                    columns = re.split(
                        r"[,;\n\r]+",
                        raw_value
                    )

            except Exception:

                columns = re.split(
                    r"[,;\n\r]+",
                    raw_value
                )

    cleaned_columns = []

    for column in columns:

        if column is None:

            continue

        column = str(column).strip()

        if column == "":

            continue

        if normalize_for_file:

            column = normalize_column_name(
                column
            )

        else:

            column = clean_db_column_name(
                column
            )

        if column:

            cleaned_columns.append(
                column
            )

    if required and not cleaned_columns:

        raise Exception(
            "inventory not uploaded: "
            + field_name
            + " is missing in inventory upload configuration"
        )

    return cleaned_columns


def fetch_upload_configuration(
        conn,
        project_id,
        sub_project_id
):

    with conn.cursor() as cursor:

        cursor.execute(
            """
            select data_columns,
                   db_columns,
                   required_columns,
                   date_columns,
                   numeric_columns
            from inventory_upload_configuration
            where project_id = %s
            and sub_project_id = %s
            limit 1
            """,
            (
                project_id,
                sub_project_id
            )
        )

        configuration = cursor.fetchone()

    if not configuration:

        raise Exception(
            "inventory not uploaded: project and sub project combination is not configured in inventory upload configuration"
        )

    data_columns = parse_configuration_columns(
        configuration.get("data_columns"),
        "data_columns",
        True,
        True
    )

    db_columns = parse_configuration_columns(
        configuration.get("db_columns"),
        "db_columns",
        True,
        False
    )

    required_columns = parse_configuration_columns(
        configuration.get("required_columns"),
        "required_columns",
        True,
        True
    )

    date_columns = parse_configuration_columns(
        configuration.get("date_columns"),
        "date_columns",
        False,
        True
    )

    numeric_columns = parse_configuration_columns(
        configuration.get("numeric_columns"),
        "numeric_columns",
        False,
        True
    )

    if len(data_columns) != len(db_columns):

        raise Exception(
            "inventory not uploaded: data_columns count "
            + str(len(data_columns))
            + " does not match db_columns count "
            + str(len(db_columns))
            + " in inventory upload configuration"
        )

    invalid_required_columns = [
        column
        for column in required_columns
        if column not in data_columns
    ]

    if invalid_required_columns:

        raise Exception(
            "inventory not uploaded: required columns not present in data_columns: "
            + ", ".join(invalid_required_columns)
        )

    invalid_date_columns = [
        column
        for column in date_columns
        if column not in data_columns
    ]

    if invalid_date_columns:

        raise Exception(
            "inventory not uploaded: date columns not present in data_columns: "
            + ", ".join(invalid_date_columns)
        )

    invalid_numeric_columns = [
        column
        for column in numeric_columns
        if column not in data_columns
    ]

    if invalid_numeric_columns:

        raise Exception(
            "inventory not uploaded: numeric columns not present in data_columns: "
            + ", ".join(invalid_numeric_columns)
        )

    log_info("configuration loaded")

    return {
        "data_columns": data_columns,
        "db_columns": db_columns,
        "required_columns": required_columns,
        "date_columns": date_columns,
        "numeric_columns": numeric_columns
    }


# ============================================================
# table helpers
# ============================================================

def validate_mysql_identifier(identifier, label):

    identifier = str(identifier).strip()

    if not re.match(r"^[a-zA-Z0-9_]+$", identifier):

        raise Exception(
            "inventory not uploaded: invalid "
            + label
            + " name "
            + identifier
        )

    return identifier


def slugify_sub_project(name):

    name = str(name).lower()

    name = re.sub(
        r"[^a-z0-9]+",
        "_",
        name
    )

    return name.strip("_")


def generate_table_name(project_name, sub_project_name):

    project_clean = re.sub(
        r"[^a-z0-9]",
        "",
        str(project_name).lower()
    )

    sub_clean = slugify_sub_project(
        sub_project_name
    )

    if not project_clean or not sub_clean:

        raise Exception("invalid generated table name")

    table_name = (
        project_clean
        + "_"
        + sub_clean
    )

    return validate_mysql_identifier(
        table_name,
        "table"
    )


def get_dynamic_table_name(conn, project_id, sub_project_id):

    with conn.cursor() as cursor:

        cursor.execute(
            """
            select project_name
            from projects
            where project_id = %s
            """,
            (
                project_id,
            )
        )

        project = cursor.fetchone()

        cursor.execute(
            """
            select sub_project_name
            from subprojects
            where sub_project_id = %s
            and project_id = %s
            """,
            (
                sub_project_id,
                project_id
            )
        )

        sub_project = cursor.fetchone()

    if not project or not sub_project:

        raise Exception(
            "inventory not uploaded: invalid project or sub project combination"
        )

    table_name = generate_table_name(
        project["project_name"],
        sub_project["sub_project_name"]
    )

    log_info(
        "table : "
        + table_name
    )

    return table_name


def validate_dynamic_table_columns(conn, table_name, db_columns):

    safe_table_name = validate_mysql_identifier(
        table_name,
        "table"
    )

    expected_columns = list(
        db_columns
    )

    if "invoke_date" not in expected_columns:

        expected_columns.append(
            "invoke_date"
        )

    for column_name in expected_columns:

        validate_mysql_identifier(
            column_name,
            "column"
        )

    with conn.cursor() as cursor:

        cursor.execute(
            "show columns from `" + safe_table_name + "`"
        )

        table_columns_result = cursor.fetchall()

    table_columns = set()

    for row in table_columns_result:

        table_columns.add(
            row.get("Field")
        )

    missing_db_columns = [
        column_name
        for column_name in expected_columns
        if column_name not in table_columns
    ]

    if missing_db_columns:

        raise Exception(
            "inventory not uploaded: configured db_columns not present in dynamic table "
            + safe_table_name
            + ": "
            + ", ".join(missing_db_columns)
        )

    log_info(
        "dynamic table db_columns validated"
    )


def validate_chart_status_column_exists(conn, table_name):

    safe_table_name = validate_mysql_identifier(
        table_name,
        "table"
    )

    with conn.cursor() as cursor:

        cursor.execute(
            f"""
            show columns
            from `{safe_table_name}`
            like %s
            """,
            (
                CHART_STATUS_COLUMN,
            )
        )

        result = cursor.fetchone()

    if not result:

        raise Exception(
            "inventory not uploaded: chart_status column is missing in dynamic table "
            + safe_table_name
        )


# ============================================================
# dataframe helpers
# ============================================================

def normalize_columns(df):

    df.columns = [
        normalize_column_name(col)
        for col in df.columns
    ]

    return df


def read_file(file_path):

    if not os.path.isfile(file_path):

        raise Exception(
            "file not found : "
            + file_path
        )

    if os.path.getsize(file_path) == 0:

        raise Exception(
            "file is empty : "
            + file_path
        )

    ext = os.path.splitext(file_path)[1].lower()

    start = time.time()

    if ext == ".csv":

        df = pd.read_csv(
            file_path,
            dtype=str,
            keep_default_na=False,
            na_values=[]
        )

    elif ext in [".xlsx", ".xls"]:

        df = pd.read_excel(
            file_path,
            dtype=str,
            keep_default_na=False
        )

    else:

        raise Exception(
            "unsupported file type : "
            + ext
        )

    df = normalize_columns(
        df
    )

    log_info(
        "file rows : "
        + str(len(df))
    )

    log_info(
        "file read time : "
        + format_duration(time.time() - start)
    )

    return df


def validate_required_columns(df, required_columns):

    missing_columns = [
        column_name
        for column_name in required_columns
        if column_name not in df.columns
    ]

    if missing_columns:

        raise Exception(
            "inventory not uploaded: missing required columns in uploaded file: "
            + ", ".join(missing_columns)
        )


def validate_file_headers_against_data_columns(df, configuration):

    file_columns = [
        str(column).strip()
        for column in df.columns
    ]

    data_columns = [
        str(column).strip()
        for column in configuration["data_columns"]
    ]

    file_column_set = set(
        file_columns
    )

    data_column_set = set(
        data_columns
    )

    # Keep existing CE/AR alias behavior, but validate it before insert also.
    # Example: config has ar_emp_id and file has ce_emp_id, or config has
    # ce_emp_id and file has ar_emp_id. Existing prepare_dataframe() already
    # copies this alias, so strict validation should not break that case.
    effective_file_column_set = set(
        file_column_set
    )

    if "ar_emp_id" in data_column_set and "ar_emp_id" not in effective_file_column_set and "ce_emp_id" in file_column_set:

        effective_file_column_set.add(
            "ar_emp_id"
        )

    if "ce_emp_id" in data_column_set and "ce_emp_id" not in effective_file_column_set and "ar_emp_id" in file_column_set:

        effective_file_column_set.add(
            "ce_emp_id"
        )

    missing_columns = sorted(
        data_column_set - effective_file_column_set
    )

    extra_columns = sorted(
        file_column_set - data_column_set
    )

    allowed_alias_extra_columns = []

    if "ar_emp_id" in data_column_set and "ce_emp_id" in file_column_set and "ce_emp_id" in extra_columns:

        allowed_alias_extra_columns.append(
            "ce_emp_id"
        )

    if "ce_emp_id" in data_column_set and "ar_emp_id" in file_column_set and "ar_emp_id" in extra_columns:

        allowed_alias_extra_columns.append(
            "ar_emp_id"
        )

    if allowed_alias_extra_columns:

        extra_columns = [
            column
            for column in extra_columns
            if column not in allowed_alias_extra_columns
        ]

    duplicate_file_columns = sorted(
        [
            column
            for column in file_column_set
            if file_columns.count(column) > 1
        ]
    )

    duplicate_config_columns = sorted(
        [
            column
            for column in data_column_set
            if data_columns.count(column) > 1
        ]
    )

    if duplicate_file_columns:

        raise Exception(
            "inventory not uploaded: duplicate columns found in uploaded file after normalization: "
            + ", ".join(duplicate_file_columns)
        )

    if duplicate_config_columns:

        raise Exception(
            "inventory not uploaded: duplicate columns found in inventory upload configuration data_columns after normalization: "
            + ", ".join(duplicate_config_columns)
        )

    if missing_columns or extra_columns:

        message_parts = []

        if missing_columns:

            message_parts.append(
                "missing configured data_columns in uploaded file: "
                + ", ".join(missing_columns)
            )

        if extra_columns:

            message_parts.append(
                "extra uploaded file columns not configured in data_columns: "
                + ", ".join(extra_columns)
            )

        raise Exception(
            "inventory not uploaded: uploaded file header does not exactly match inventory upload configuration data_columns. "
            + " | ".join(message_parts)
        )

    log_info(
        "file headers exactly validated against data_columns"
    )


def add_missing_optional_columns(df, data_columns):

    for column_name in data_columns:

        if column_name not in df.columns:

            df[column_name] = None

    return df


def apply_column_aliases(df, data_columns):

    if "ce_emp_id" in data_columns and "ce_emp_id" not in df.columns and "ar_emp_id" in df.columns:

        df["ce_emp_id"] = df["ar_emp_id"]

    if "ar_emp_id" in data_columns and "ar_emp_id" not in df.columns and "ce_emp_id" in df.columns:

        df["ar_emp_id"] = df["ce_emp_id"]

    return df


def prepare_dates(df, date_columns):

    for column_name in date_columns:

        if column_name in df.columns:

            df[column_name] = pd.to_datetime(
                df[column_name],
                errors="coerce"
            )

            df[column_name] = df[column_name].dt.strftime(
                "%Y-%m-%d"
            )

            df[column_name] = df[column_name].where(
                df[column_name].notna(),
                None
            )

    return df


def prepare_numeric(df, numeric_columns):

    for column_name in numeric_columns:

        if column_name in df.columns:

            df[column_name] = pd.to_numeric(
                df[column_name],
                errors="coerce"
            )

            df[column_name] = df[column_name].where(
                df[column_name].notna(),
                None
            )

    return df


def clean_dataframe_values(
        df,
        data_columns,
        date_columns,
        numeric_columns
):

    text_columns = [
        column_name
        for column_name in data_columns
        if column_name not in date_columns and column_name not in numeric_columns
    ]

    for column_name in text_columns:

        if column_name in df.columns:

            df[column_name] = df[column_name].where(
                df[column_name].notna(),
                None
            )

            df[column_name] = df[column_name].astype(
                "string"
            ).str.strip()

            df[column_name] = df[column_name].mask(
                (
                    df[column_name].isna()
                )
                |
                (
                    df[column_name] == ""
                )
                |
                (
                    df[column_name] == "-"
                )
                |
                (
                    df[column_name].str.lower() == "nan"
                ),
                None
            )

    return df


def prepare_dataframe(df, configuration):

    data_columns = configuration["data_columns"]

    required_columns = configuration["required_columns"]

    date_columns = configuration["date_columns"]

    numeric_columns = configuration["numeric_columns"]

    df = apply_column_aliases(
        df,
        data_columns
    )

    validate_required_columns(
        df,
        required_columns
    )

    df = add_missing_optional_columns(
        df,
        data_columns
    )

    df = prepare_dates(
        df,
        date_columns
    )

    df = prepare_numeric(
        df,
        numeric_columns
    )

    df = clean_dataframe_values(
        df,
        data_columns,
        date_columns,
        numeric_columns
    )

    df = df[
        data_columns
    ]

    return df


def filter_valid_dataframe(df, data_columns, required_columns):

    valid_mask = pd.Series(
        True,
        index=df.index
    )

    if "uid" in data_columns and "uid" in df.columns:

        uid_values = df["uid"].astype(
            "string"
        ).str.strip()

        valid_mask = valid_mask & uid_values.notna() & (uid_values != "")

    for column_name in required_columns:

        if column_name in df.columns:

            column_values = df[column_name].astype(
                "string"
            ).str.strip()

            valid_mask = valid_mask & column_values.notna() & (column_values != "")

    skipped_rows = int(
        (~valid_mask).sum()
    )

    valid_df = df.loc[
        valid_mask
    ].copy()

    row_errors = 0

    return valid_df, skipped_rows, row_errors


def prepare_load_dataframe(df, configuration):

    data_columns = configuration["data_columns"]

    db_columns = configuration["db_columns"]

    df_for_load = df[
        data_columns
    ].copy()

    rename_map = dict(
        zip(
            data_columns,
            db_columns
        )
    )

    df_for_load = df_for_load.rename(
        columns=rename_map
    )

    insert_db_columns = list(
        db_columns
    )

    if "invoke_date" not in insert_db_columns:

        insert_db_columns.append(
            "invoke_date"
        )

        df_for_load["invoke_date"] = datetime.now().strftime(
            "%Y-%m-%d"
        )

    for column_name in insert_db_columns:

        validate_mysql_identifier(
            column_name,
            "column"
        )

    df_for_load = df_for_load[
        insert_db_columns
    ]

    return df_for_load, insert_db_columns


def get_assigned_count(df):

    assigned_column = None

    if "ce_emp_id" in df.columns:

        assigned_column = "ce_emp_id"

    elif "ar_emp_id" in df.columns:

        assigned_column = "ar_emp_id"

    if not assigned_column:

        return 0

    assigned_values = df[assigned_column].astype(
        "string"
    ).str.strip()

    assigned_count = assigned_values.notna() & (assigned_values != "")

    return int(
        assigned_count.sum()
    )


# ============================================================
# duplicate helpers
# ============================================================

def is_duplicate_ignore_column(column_name):

    normalized_name = normalize_column_name(
        column_name
    )

    return normalized_name in DUPLICATE_IGNORE_COLUMNS


def get_duplicate_compare_columns(db_columns):

    compare_columns = []

    ignored_columns = []

    for column_name in db_columns:

        validate_mysql_identifier(
            column_name,
            "column"
        )

        if is_duplicate_ignore_column(column_name):

            ignored_columns.append(
                column_name
            )

        else:

            compare_columns.append(
                column_name
            )

    if not compare_columns:

        raise Exception(
            "inventory not uploaded: duplicate check has no columns to compare"
        )

    log_info(
        "duplicate check comparing columns : "
        + ", ".join(compare_columns)
    )

    if ignored_columns:

        log_info(
            "duplicate check ignoring columns : "
            + ", ".join(ignored_columns)
        )

    return compare_columns, ignored_columns


def normalize_for_duplicate_compare(series):

    return (
        series.astype("string")
        .fillna("")
        .str.strip()
        .str.lower()
    )


def remove_duplicate_rows_from_file(df_for_load, compare_columns):

    if len(df_for_load) == 0:

        return df_for_load, 0

    compare_frame = pd.DataFrame(
        index=df_for_load.index
    )

    for column_name in compare_columns:

        compare_frame[column_name] = normalize_for_duplicate_compare(
            df_for_load[column_name]
        )

    duplicate_mask = compare_frame.duplicated(
        keep="first"
    )

    duplicate_file_rows = int(
        duplicate_mask.sum()
    )

    unique_df = df_for_load.loc[
        ~duplicate_mask
    ].copy()

    if duplicate_file_rows > 0:

        log_info(
            "duplicate rows inside uploaded file skipped : "
            + str(duplicate_file_rows)
        )

    return unique_df, duplicate_file_rows


def build_temp_table_name(prefix):

    table_name = (
        prefix
        + "_"
        + datetime.now().strftime("%Y%m%d%H%M%S%f")
    )

    return validate_mysql_identifier(
        table_name,
        "temporary table"
    )


def build_column_sql(columns, table_alias=None):

    safe_columns = []

    for column_name in columns:

        safe_column = validate_mysql_identifier(
            column_name,
            "column"
        )

        if table_alias:

            safe_alias = validate_mysql_identifier(
                table_alias,
                "table alias"
            )

            safe_columns.append(
                "`" + safe_alias + "`.`" + safe_column + "`"
            )

        else:

            safe_columns.append(
                "`" + safe_column + "`"
            )

    return ",".join(
        safe_columns
    )


def build_duplicate_hash_expression(compare_columns, table_alias=None):

    hash_parts = []

    for column_name in compare_columns:

        safe_column = validate_mysql_identifier(
            column_name,
            "column"
        )

        if table_alias:

            safe_alias = validate_mysql_identifier(
                table_alias,
                "table alias"
            )

            column_ref = (
                "`"
                + safe_alias
                + "`.`"
                + safe_column
                + "`"
            )

        else:

            column_ref = (
                "`"
                + safe_column
                + "`"
            )

        normalized_value = (
            "lower(trim(cast("
            + column_ref
            + " as char)))"
        )

        hash_parts.append(
            "ifnull(concat(char_length("
            + normalized_value
            + "), ':', "
            + normalized_value
            + "), '-1:NULL')"
        )

    return (
        "sha2(concat_ws('|', "
        + ", ".join(hash_parts)
        + "), 256)"
    )


# ============================================================
# bulk load helpers
# ============================================================

def write_temp_load_csv(df_for_load, file_path):

    temp_dir = os.path.dirname(
        file_path
    )

    temp_file_name = (
        "load_"
        + datetime.now().strftime("%Y%m%d%H%M%S%f")
        + ".csv"
    )

    temp_csv_path = os.path.join(
        temp_dir,
        temp_file_name
    )

    df_for_load.to_csv(
        temp_csv_path,
        index=False,
        header=False,
        sep=",",
        na_rep="\\N",
        quoting=csv.QUOTE_MINIMAL,
        quotechar='"',
        escapechar="\\",
        doublequote=False,
        lineterminator="\n",
        encoding="utf-8"
    )

    return temp_csv_path


def build_load_data_sql(table_name, db_columns):

    safe_table_name = validate_mysql_identifier(
        table_name,
        "table"
    )

    safe_columns = []

    for column_name in db_columns:

        safe_column = validate_mysql_identifier(
            column_name,
            "column"
        )

        safe_columns.append(
            "`" + safe_column + "`"
        )

    column_sql = ",".join(
        safe_columns
    )

    sql = f"""
    load data local infile %s
    into table `{safe_table_name}`
    character set utf8mb4
    fields terminated by ','
    optionally enclosed by '"'
    escaped by '\\\\'
    lines terminated by '\\n'
    (
        {column_sql}
    )
    """

    return sql


def load_records_with_local_infile(
        conn,
        table_name,
        df_for_load,
        db_columns,
        compare_columns,
        file_path
):

    temp_csv_path = None

    load_start = time.time()

    temp_load_table = build_temp_table_name(
        "tmp_inv_load"
    )

    temp_key_table = build_temp_table_name(
        "tmp_inv_keys"
    )

    duplicate_existing_rows = 0

    try:

        safe_target_table = validate_mysql_identifier(
            table_name,
            "table"
        )

        safe_temp_load_table = validate_mysql_identifier(
            temp_load_table,
            "temporary load table"
        )

        safe_temp_key_table = validate_mysql_identifier(
            temp_key_table,
            "temporary key table"
        )

        validate_chart_status_column_exists(
            conn,
            safe_target_table
        )

        temp_csv_path = write_temp_load_csv(
            df_for_load,
            file_path
        )

        log_info(
            "temporary load csv created : "
            + temp_csv_path
        )

        db_columns_sql = build_column_sql(
            db_columns
        )

        db_columns_select_sql = build_column_sql(
            db_columns,
            "n"
        )

        target_hash_sql = build_duplicate_hash_expression(
            compare_columns,
            "t"
        )

        load_hash_sql = build_duplicate_hash_expression(
            compare_columns
        )

        with conn.cursor() as cursor:

            cursor.execute("set session unique_checks = 0")

            cursor.execute("set session foreign_key_checks = 0")

            cursor.execute(
                f"""
                create temporary table `{safe_temp_load_table}` as
                select {db_columns_sql}
                from `{safe_target_table}`
                where 1 = 0
                """
            )

            cursor.execute(
                f"""
                alter table `{safe_temp_load_table}`
                add column `__duplicate_key` char(64) null
                """
            )

            load_sql = build_load_data_sql(
                safe_temp_load_table,
                db_columns
            )

            cursor.execute(
                load_sql,
                (
                    temp_csv_path,
                )
            )

            loaded_to_temp = cursor.rowcount

            log_info(
                "rows loaded into temporary table : "
                + str(loaded_to_temp)
            )

            cursor.execute(
                f"""
                create temporary table `{safe_temp_key_table}`
                (
                    duplicate_key char(64) not null,
                    primary key (duplicate_key)
                ) engine = InnoDB
                """
            )

            # Only existing rows with chart_status = CE_Assigned are duplicate blockers.
            # Existing CE_Pending / CE_Completed / any other chart_status will not block insert.
            cursor.execute(
                f"""
                insert ignore into `{safe_temp_key_table}`
                (
                    duplicate_key
                )
                select {target_hash_sql}
                from `{safe_target_table}` t
                where lower(trim(cast(t.`{CHART_STATUS_COLUMN}` as char))) = lower(trim(%s))
                """,
                (
                    DUPLICATE_BLOCKING_CHART_STATUS,
                )
            )

            existing_key_count = cursor.rowcount

            log_info(
                "existing CE_Assigned duplicate keys prepared : "
                + str(existing_key_count)
            )

            cursor.execute(
                f"""
                update `{safe_temp_load_table}`
                set `__duplicate_key` = {load_hash_sql}
                """
            )

            cursor.execute(
                f"""
                alter table `{safe_temp_load_table}`
                add index `idx_duplicate_key` (`__duplicate_key`)
                """
            )

            cursor.execute(
                f"""
                select count(*) as duplicate_existing_rows
                from `{safe_temp_load_table}` n
                inner join `{safe_temp_key_table}` e
                    on e.`duplicate_key` = n.`__duplicate_key`
                """
            )

            duplicate_result = cursor.fetchone()

            duplicate_existing_rows = int(
                duplicate_result.get("duplicate_existing_rows", 0)
            )

            if duplicate_existing_rows > 0:

                log_info(
                    "duplicate rows skipped because existing chart_status is CE_Assigned : "
                    + str(duplicate_existing_rows)
                )

            cursor.execute(
                f"""
                insert into `{safe_target_table}`
                (
                    {db_columns_sql}
                )
                select
                    {db_columns_select_sql}
                from `{safe_temp_load_table}` n
                left join `{safe_temp_key_table}` e
                    on e.`duplicate_key` = n.`__duplicate_key`
                where e.`duplicate_key` is null
                """
            )

            inserted = cursor.rowcount

            cursor.execute("set session foreign_key_checks = 1")

            cursor.execute("set session unique_checks = 1")

        conn.commit()

        load_time = time.time() - load_start

        rows_per_second = 0

        if load_time > 0:

            rows_per_second = inserted / load_time

        return {
            "inserted": inserted,
            "insert_time": load_time,
            "avg_batch_time": load_time,
            "rows_per_second": rows_per_second,
            "duplicate_existing_rows": duplicate_existing_rows
        }

    except Exception as e:

        try:

            with conn.cursor() as cursor:

                cursor.execute("set session foreign_key_checks = 1")

                cursor.execute("set session unique_checks = 1")

        except Exception as reset_error:

            log_error(
                "failed to reset mysql checks : "
                + str(reset_error)
            )

        conn.rollback()

        error_message = str(e)

        if "local infile" in error_message.lower() or "used command is not allowed" in error_message.lower():

            raise Exception(
                "inventory not uploaded: load data local infile failed. enable local_infile in azure mysql server parameters and keep local_infile true in pymysql connection. actual error: "
                + error_message
            )

        raise e

    finally:

        if temp_csv_path and os.path.isfile(temp_csv_path):

            os.remove(
                temp_csv_path
            )


# ============================================================
# tracking helpers
# ============================================================

def insert_inventory_exe_file(
        conn,
        project_id,
        sub_project_id,
        file_name,
        inventory_count,
        upload_status
):

    sql = """
    insert into inventory_exe_files
    (
        project_id,
        sub_project_id,
        file_name,
        exe_date,
        inventory_count,
        upload_status
    )
    values
    (
        %s,%s,%s,%s,%s,%s
    )
    """

    exe_date = datetime.now().strftime(
        "%Y-%m-%d %H:%M:%S"
    )

    with conn.cursor() as cursor:

        cursor.execute(
            sql,
            (
                project_id,
                sub_project_id,
                file_name,
                exe_date,
                inventory_count,
                upload_status
            )
        )

    conn.commit()


def build_single_error_description(summary):

    description_parts = [
        "total_rows : " + str(summary["total_rows"]),
        "validated_rows : " + str(summary["validated_rows"]),
        "load_rows_after_file_duplicate_check : " + str(summary.get("load_rows_after_file_duplicate_check", "N/A")),
        "inserted : " + str(summary["inserted"]),
        "assigned : " + str(summary["assigned"]),
        "unassigned : " + str(summary["unassigned"]),
        "skipped_rows : " + str(summary["skipped_rows"]),
        "validation_skipped_rows : " + str(summary.get("validation_skipped_rows", "N/A")),
        "duplicate_file_rows : " + str(summary.get("duplicate_file_rows", "N/A")),
        "duplicate_existing_rows : " + str(summary.get("duplicate_existing_rows", "N/A")),
        "duplicate_rows : " + str(summary.get("duplicate_rows", "N/A")),
        "duplicate_rule : " + str(summary.get("duplicate_rule", "N/A")),
        "row_errors : " + str(summary["row_errors"]),
        "table : " + str(summary["table"]),
        "file_name : " + str(summary["file_name"]),
        "total_time : " + str(summary["total_time"])
    ]

    return ", ".join(
        description_parts
    )



def get_success_status_code(summary):

    if (
        int(summary["inserted"]) == int(summary["total_rows"])
        and int(summary["skipped_rows"]) == 0
        and int(summary["row_errors"]) == 0
    ):

        return 200

    return 206


def insert_inventory_error_log(
        conn,
        project_id,
        sub_project_id,
        error_status_code,
        error_description
):

    sql = """
    insert into inventory_error_logs
    (
        project_id,
        sub_project_id,
        error_status_code,
        error_description
    )
    values
    (
        %s,%s,%s,%s
    )
    """

    with conn.cursor() as cursor:

        cursor.execute(
            sql,
            (
                project_id,
                sub_project_id,
                error_status_code,
                error_description
            )
        )

    conn.commit()


def insert_success_tracking(
        conn,
        project_id,
        sub_project_id,
        file_name,
        summary
):

    status_code = get_success_status_code(
        summary
    )

    error_description = build_single_error_description(
        summary
    )

    insert_inventory_exe_file(
        conn,
        project_id,
        sub_project_id,
        file_name,
        summary["inserted"],
        "success"
    )

    insert_inventory_error_log(
        conn,
        project_id,
        sub_project_id,
        status_code,
        error_description
    )


def insert_failed_tracking(input_data, error_message):

    conn = None

    try:

        if not input_data:

            return

        project_id = input_data.get(
            "project_id"
        )

        sub_project_id = input_data.get(
            "sub_project_id"
        )

        file_name = input_data.get(
            "file_name"
        )

        file_path = input_data.get(
            "file_path"
        )

        if not file_name and file_path:

            file_name = os.path.basename(
                file_path
            )

        conn = get_connection()

        insert_inventory_exe_file(
            conn,
            project_id,
            sub_project_id,
            file_name,
            0,
            "failed"
        )

        insert_inventory_error_log(
            conn,
            project_id,
            sub_project_id,
            500,
            str(error_message)
        )

    except Exception as tracking_error:

        log_error(
            "failed to insert failure tracking : "
            + str(tracking_error)
        )

    finally:

        if conn:

            conn.close()


# ============================================================
# utility helpers
# ============================================================

def format_duration(seconds):

    if seconds < 1:

        return str(round(seconds * 1000)) + "ms"

    if seconds < 60:

        return str(round(seconds, 2)) + "s"

    minutes = int(seconds // 60)

    remaining_seconds = round(seconds % 60, 2)

    return (
        str(minutes)
        + "m "
        + str(remaining_seconds)
        + "s"
    )


# ============================================================
# main process
# ============================================================

def process_inventory_upload(input_data):

    total_start = time.time()

    input_data = validate_file_path_matches_project_folder(
        input_data
    )

    file_path = input_data["file_path"]

    file_name = input_data["file_name"]

    project_id = input_data["project_id"]

    sub_project_id = input_data["sub_project_id"]

    conn = None

    try:

        conn = get_connection()

        validate_db(
            conn
        )

        configuration = fetch_upload_configuration(
            conn,
            project_id,
            sub_project_id
        )

        table_name = get_dynamic_table_name(
            conn,
            project_id,
            sub_project_id
        )

        validate_dynamic_table_columns(
            conn,
            table_name,
            configuration["db_columns"]
        )

        df = read_file(
            file_path
        )

        validate_file_headers_against_data_columns(
            df,
            configuration
        )

        total_rows = len(df)

        if total_rows == 0:

            raise Exception("inventory not uploaded: file has no data rows")

        prepare_start = time.time()

        df = prepare_dataframe(
            df,
            configuration
        )

        valid_df, skipped_rows, row_errors = filter_valid_dataframe(
            df,
            configuration["data_columns"],
            configuration["required_columns"]
        )

        prepare_time = time.time() - prepare_start

        if len(valid_df) == 0:

            raise Exception("inventory not uploaded: no valid rows to insert after validation")

        df_for_load, insert_db_columns = prepare_load_dataframe(
            valid_df,
            configuration
        )

        duplicate_compare_columns, duplicate_ignored_columns = get_duplicate_compare_columns(
            insert_db_columns
        )

        df_for_load, duplicate_file_rows = remove_duplicate_rows_from_file(
            df_for_load,
            duplicate_compare_columns
        )

        if len(df_for_load) == 0:

            raise Exception(
                "inventory not uploaded: all rows are duplicate inside uploaded file"
            )

        insert_result = load_records_with_local_infile(
            conn,
            table_name,
            df_for_load,
            insert_db_columns,
            duplicate_compare_columns,
            file_path
        )

        duplicate_existing_rows = int(
            insert_result.get("duplicate_existing_rows", 0)
        )

        duplicate_rows = int(
            duplicate_file_rows + duplicate_existing_rows
        )

        total_skipped_rows = int(
            skipped_rows + duplicate_rows
        )

        total_time = time.time() - total_start

        assigned = get_assigned_count(
            valid_df
        )

        unassigned = len(valid_df) - assigned

        response_data = {
            "table": table_name,
            "file_name": file_name,
            "project_id": project_id,
            "sub_project_id": sub_project_id,
            "total_rows": total_rows,
            "validated_rows": len(valid_df),
            "load_rows_after_file_duplicate_check": len(df_for_load),
            "inserted": insert_result["inserted"],
            "skipped_rows": total_skipped_rows,
            "validation_skipped_rows": skipped_rows,
            "duplicate_file_rows": duplicate_file_rows,
            "duplicate_existing_rows": duplicate_existing_rows,
            "duplicate_rows": duplicate_rows,
            "duplicate_rule": "same row skipped only when existing chart_status is CE_Assigned",
            "row_errors": row_errors,
            "assigned": assigned,
            "unassigned": unassigned,
            "prepare_time": format_duration(prepare_time),
            "insert_time": format_duration(insert_result["insert_time"]),
            "avg_batch_time": format_duration(insert_result["avg_batch_time"]),
            "rows_per_second": round(insert_result["rows_per_second"]),
            "total_time": format_duration(total_time)
        }

        insert_success_tracking(
            conn,
            project_id,
            sub_project_id,
            file_name,
            response_data
        )

        if int(insert_result["inserted"]) == 0:

            message = (
                "No rows inserted. Duplicate rows skipped: "
                + str(duplicate_rows)
            )

        else:

            message = (
                str(insert_result["inserted"])
                + " rows inserted successfully in "
                + format_duration(total_time)
                + ". Duplicate rows skipped: "
                + str(duplicate_rows)
            )
        send_success(
            message,
            response_data
        )

    finally:

        if conn:

            conn.close()


def main():

    input_data = None

    try:

        input_data = read_input_once()

        process_inventory_upload(
            input_data
        )

        sys.exit(0)

    except Exception as e:

        log_error(
            e
        )

        insert_failed_tracking(
            input_data,
            e
        )

        send_warning(
            "inventory not uploaded: " + str(e)
        )

        sys.exit(1)


if __name__ == "__main__":

    main()