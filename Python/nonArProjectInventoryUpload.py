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
import traceback
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
# Only invoke_date is ignored for exact duplicate comparison.
# CE employee ID participates in the exact duplicate key so that:
# - same business row + same CE employee ID = duplicate
# - same business row + different CE employee ID = update existing CE_Completed row
# CE_Completed is not a column. It is a value inside charge_status.
DUPLICATE_IGNORE_COLUMNS = {
    "invoke_date",
    "charge_status"
}

EMPLOYEE_COLUMN_NAMES = {
    "emp_id"
}

# charge_status is available in the dynamic table only.
# It is not required in non_ar_inventory_upload_configuration data_columns/db_columns.
charge_status_COLUMN = "charge_status"
DEFAULT_charge_status = "CE_Completed"

# Existing row blocks new insert only when charge_status = CE_Completed.
DUPLICATE_BLOCKING_charge_status = "CE_Completed"

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

    error_text = str(message)

    print(
        "[error] " + error_text,
        file=sys.stderr,
        flush=True
    )

    log_line = (
        datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        + " "
        + error_text
        + "\n"
    )

    fallback_error_log_file = "/tmp/inventory_upload_local_db_error.log"

    try:

        log_dir = os.path.dirname(
            error_log_file
        )

        if log_dir:

            os.makedirs(
                log_dir,
                exist_ok=True
            )

        with open(error_log_file, "a", encoding="utf-8") as f:

            f.write(
                log_line
            )

    except Exception as log_write_error:

        try:

            with open(fallback_error_log_file, "a", encoding="utf-8") as f:

                f.write(
                    log_line
                )

                f.write(
                    datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    + " failed to write primary error log "
                    + error_log_file
                    + " : "
                    + str(log_write_error)
                    + "\n"
                )

        except Exception as fallback_write_error:

            print(
                "[error] failed to write fallback error log "
                + fallback_error_log_file
                + " : "
                + str(fallback_write_error),
                file=sys.stderr,
                flush=True
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


def send_warning(message, error_log_id=None):

    result = {
        "status": "warning",
        "message": str(message)
    }

    if error_log_id:

        result["data"] = {
            "error_log_id": error_log_id
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

    if "ar_nonproject_inventory_uploads" not in lower_parts:

        raise Exception(
            "inventory not uploaded: file is not inside ar_nonproject_inventory_uploads folder"
        )

    upload_index = lower_parts.index(
        "ar_nonproject_inventory_uploads"
    )

    expected_project_index = upload_index + 1

    expected_sub_project_index = upload_index + 2

    if len(normalized_parts) <= expected_sub_project_index:

        raise Exception(
            "inventory not uploaded: uploaded file path must be ar_nonproject_inventory_uploads/"
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
            "inventory not uploaded: uploaded file path does not match selected project and sub project folder. expected path ar_nonproject_inventory_uploads/"
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
            from non_ar_inventory_upload_configuration
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
        False,
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


def laravel_str_slug(value, separator="_"):

    value = str(value).lower()

    # Laravel Str::slug first converts the opposite separator into the requested separator.
    # For separator "_", hyphens are converted to underscores.
    if separator == "-":

        flip = "_"

    else:

        flip = "-"

    value = re.sub(
        r"[" + re.escape(flip) + r"]+",
        separator,
        value
    )

    # Laravel default slug dictionary converts @ to the word "at".
    value = value.replace(
        "@",
        separator + "at" + separator
    )

    # Match Laravel behavior: remove unsupported characters instead of replacing them.
    # Example: Dickson/EPIC/No Response becomes dicksonepicno_response.
    value = re.sub(
        r"[^" + re.escape(separator) + r"a-z0-9\s]+",
        "",
        value
    )

    # Convert whitespace and separator runs into a single separator.
    value = re.sub(
        r"[" + re.escape(separator) + r"\s]+",
        separator,
        value
    )

    return value.strip(
        separator
    )


def slugify_sub_project(name):

    return laravel_str_slug(
        name,
        "_"
    )


def generate_table_name(project_name, sub_project_name):

    base_name = (
        str(project_name).lower()
        + "_"
        + str(sub_project_name).lower()
    )
    suffix = "_datas"
    raw_table_name = f"{base_name}{suffix}"

    table_name = laravel_str_slug(
        raw_table_name,
        "_"
    )

    if not table_name:

        raise Exception("invalid generated table name")

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


def validate_charge_status_column_exists(conn, table_name):

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
                charge_status_COLUMN,
            )
        )

        result = cursor.fetchone()

    if not result:

        raise Exception(
            "inventory not uploaded: charge_status column is missing in dynamic table "
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


def read_csv_with_encoding_fallback(file_path, **read_csv_kwargs):

    encodings = [
        "utf-8-sig",
        "utf-8",
        "cp1252",
        "latin1"
    ]

    last_unicode_error = None

    for encoding_name in encodings:

        try:

            log_info(
                "trying csv encoding : "
                + encoding_name
            )

            df = pd.read_csv(
                file_path,
                encoding=encoding_name,
                **read_csv_kwargs
            )

            log_info(
                "csv encoding used : "
                + encoding_name
            )

            return df

        except UnicodeDecodeError as unicode_error:

            last_unicode_error = unicode_error

            log_info(
                "csv encoding failed : "
                + encoding_name
                + " : "
                + str(unicode_error)
            )

    raise Exception(
        "inventory not uploaded: uploaded CSV encoding is invalid. tried encodings: "
        + ", ".join(encodings)
    ) from last_unicode_error


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

        df = read_csv_with_encoding_fallback(
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

def validate_date_column_values(df, date_columns):

    date_errors = []

    # Accept date-only values using these separators.
    # Time, AM/PM, letters and other special characters are rejected.
    allowed_date_pattern = re.compile(
        r"^(?:"
        r"\d{1,2}[-/]\d{1,2}[-/]\d{4}"
        r"|"
        r"\d{4}[-/]\d{1,2}[-/]\d{1,2}"
        r")$"
    )

    for column_name in date_columns:

        if column_name not in df.columns:

            continue

        original_values = (
            df[column_name]
            .astype("string")
            .str.strip()
        )

        # Only actual blank values are allowed as empty.
        # Do not treat "-" as empty.
        empty_mask = (
            original_values.isna()
            |
            (original_values == "")
            |
            (original_values.str.lower() == "nan")
            |
            (original_values.str.lower() == "nat")
            |
            (original_values.str.lower() == "none")
        )

        non_empty_values = original_values[
            ~empty_mask
        ]

        format_valid_mask = non_empty_values.str.match(
            allowed_date_pattern,
            na=False
        )

        invalid_format_indexes = non_empty_values.index[
            ~format_valid_mask
        ].tolist()

        for row_index in invalid_format_indexes:

            date_errors.append(
                "row "
                + str(row_index + 2)
                + ", column "
                + column_name
                + ", invalid date format: "
                + str(original_values.loc[row_index])
            )

        format_valid_values = non_empty_values[
            format_valid_mask
        ]

        if not format_valid_values.empty:

            parsed_dates = pd.to_datetime(
                format_valid_values,
                errors="coerce",
                dayfirst=True
            )

            invalid_date_indexes = parsed_dates.index[
                parsed_dates.isna()
            ].tolist()

            for row_index in invalid_date_indexes:

                date_errors.append(
                    "row "
                    + str(row_index + 2)
                    + ", column "
                    + column_name
                    + ", invalid date value: "
                    + str(original_values.loc[row_index])
                )

    if date_errors:

        raise Exception(
            "inventory not uploaded: invalid date values found. "
            + " | ".join(date_errors[:20])
            + ". Date must not contain text, AM/PM, time "
              "or unsupported special characters."
        )        
def prepare_dates(df, date_columns):

    for column_name in date_columns:

        if column_name not in df.columns:

            continue

        original_values = (
            df[column_name]
            .astype("string")
            .str.strip()
        )

        empty_mask = (
            original_values.isna()
            |
            (original_values == "")
            |
            (original_values.str.lower() == "nan")
            |
            (original_values.str.lower() == "nat")
            |
            (original_values.str.lower() == "none")
        )

        parsed_dates = pd.to_datetime(
            original_values.where(
                ~empty_mask,
                None
            ),
            errors="coerce",
            dayfirst=True
        )

        formatted_dates = parsed_dates.dt.strftime(
            "%Y-%m-%d"
        )

        df[column_name] = formatted_dates.where(
            parsed_dates.notna(),
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

    df = add_missing_optional_columns(
        df,
        data_columns
    )

    validate_date_column_values(
        df,
        date_columns
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

    # Required columns should not block insertion.
    # All rows are allowed after header/configuration preparation.
    valid_df = df.copy()

    skipped_rows = 0

    row_errors = 0

    return valid_df, skipped_rows, row_errors


def prepare_load_dataframe(df, configuration):

    data_columns = configuration["data_columns"]
    db_columns = configuration["db_columns"]
    date_columns = configuration["date_columns"]

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

    date_db_columns = [
        rename_map[column_name]
        for column_name in date_columns
        if column_name in rename_map
    ]

    current_date = datetime.now().strftime(
        "%Y-%m-%d"
    )

    if "invoke_date" not in insert_db_columns:

        insert_db_columns.append(
            "invoke_date"
        )

    df_for_load["invoke_date"] = current_date

    if "invoke_date" not in date_db_columns:

        date_db_columns.append(
            "invoke_date"
        )

    if charge_status_COLUMN not in insert_db_columns:

        insert_db_columns.append(
            charge_status_COLUMN
        )

    df_for_load[charge_status_COLUMN] = (
        DEFAULT_charge_status
    )

    for column_name in insert_db_columns:

        validate_mysql_identifier(
            column_name,
            "column"
        )

    df_for_load = df_for_load[
        insert_db_columns
    ]

    return (
        df_for_load,
        insert_db_columns,
        date_db_columns
    )
    
def get_assigned_count(df):

    if "emp_id" not in df.columns:

        return 0

    assigned_values = df["emp_id"].astype(
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


def get_employee_column(db_columns):

    for column_name in db_columns:

        normalized_name = normalize_column_name(
            column_name
        )

        if normalized_name in EMPLOYEE_COLUMN_NAMES:

            return column_name

    raise Exception(
        "inventory not uploaded: emp_id column is missing "
        "from configured db_columns"
    )


def get_business_compare_columns(compare_columns, employee_column):

    validate_mysql_identifier(
        employee_column,
        "employee column"
    )

    business_compare_columns = [
        column_name
        for column_name in compare_columns
        if normalize_column_name(column_name) not in EMPLOYEE_COLUMN_NAMES
    ]

    if not business_compare_columns:

        raise Exception(
            "inventory not uploaded: duplicate check has no business columns available after excluding Emp Id field"
        )

    log_info(
        "CE_Completed business row comparison columns : "
        + ", ".join(business_compare_columns)
    )

    return business_compare_columns


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


def build_load_data_sql(
        table_name,
        db_columns,
        date_db_columns
):

    safe_table_name = validate_mysql_identifier(
        table_name,
        "table"
    )

    normalized_date_columns = {
        normalize_column_name(column_name)
        for column_name in date_db_columns
    }

    load_columns = []
    set_expressions = []

    for column_name in db_columns:

        safe_column = validate_mysql_identifier(
            column_name,
            "column"
        )

        if normalize_column_name(
            safe_column
        ) in normalized_date_columns:

            variable_name = (
                "@load_"
                + safe_column
            )

            load_columns.append(
                variable_name
            )

            set_expressions.append(
                "`"
                + safe_column
                + "` = CASE "
                + "WHEN TRIM("
                + variable_name
                + ") = '' THEN NULL "
                + "WHEN TRIM("
                + variable_name
                + ") = '\\\\N' THEN NULL "
                + "ELSE TRIM("
                + variable_name
                + ") END"
            )

        else:

            load_columns.append(
                "`" + safe_column + "`"
            )

    column_sql = ",".join(
        load_columns
    )

    set_sql = ""

    if set_expressions:

        set_sql = (
            "\nSET\n    "
            + ",\n    ".join(
                set_expressions
            )
        )

    sql = f"""
    LOAD DATA LOCAL INFILE %s
    INTO TABLE `{safe_table_name}`
    CHARACTER SET utf8mb4
    FIELDS TERMINATED BY ','
    OPTIONALLY ENCLOSED BY '"'
    ESCAPED BY '\\\\'
    LINES TERMINATED BY '\\n'
    (
        {column_sql}
    )
    {set_sql}
    """

    return sql
def load_records_with_local_infile(
        conn,
        table_name,
        df_for_load,
        db_columns,
        date_db_columns,
        compare_columns,
        file_path
):

    temp_csv_path = None

    load_start = time.time()

    temp_load_table = build_temp_table_name(
        "tmp_inv_load"
    )

    temp_business_key_table = build_temp_table_name(
        "tmp_inv_business_keys"
    )

    temp_exact_key_table = build_temp_table_name(
        "tmp_inv_exact_keys"
    )

    temp_update_source_table = build_temp_table_name(
        "tmp_inv_update_source"
    )

    duplicate_existing_rows = 0

    updated_existing_rows = 0

    update_candidate_rows = 0

    try:

        safe_target_table = validate_mysql_identifier(
            table_name,
            "table"
        )

        safe_temp_load_table = validate_mysql_identifier(
            temp_load_table,
            "temporary load table"
        )

        safe_temp_business_key_table = validate_mysql_identifier(
            temp_business_key_table,
            "temporary business key table"
        )

        safe_temp_exact_key_table = validate_mysql_identifier(
            temp_exact_key_table,
            "temporary exact key table"
        )

        safe_temp_update_source_table = validate_mysql_identifier(
            temp_update_source_table,
            "temporary update source table"
        )

        validate_charge_status_column_exists(
            conn,
            safe_target_table
        )

        employee_column = get_employee_column(
            db_columns
        )

        safe_employee_column = validate_mysql_identifier(
            employee_column,
            "employee column"
        )

        business_compare_columns = get_business_compare_columns(
            compare_columns,
            employee_column
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

        target_exact_hash_sql = build_duplicate_hash_expression(
            compare_columns,
            "t"
        )

        load_exact_hash_sql = build_duplicate_hash_expression(
            compare_columns
        )

        target_business_hash_sql = build_duplicate_hash_expression(
            business_compare_columns,
            "t"
        )

        load_business_hash_sql = build_duplicate_hash_expression(
            business_compare_columns
        )

        normalized_target_employee_sql = (
            "lower(trim(ifnull(cast(t.`"
            + safe_employee_column
            + "` as char), '')))"
        )

        normalized_update_employee_sql = (
            "lower(trim(ifnull(cast(u.`employee_value` as char), '')))"
        )

        normalized_load_employee_sql = (
            "lower(trim(ifnull(cast(n.`"
            + safe_employee_column
            + "` as char), '')))"
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
                add column `__load_row_id` bigint unsigned not null auto_increment primary key first,
                add column `__duplicate_key` char(64) null,
                add column `__business_key` char(64) null
                """
            )

            load_sql = build_load_data_sql(
                safe_temp_load_table,
                db_columns,
                date_db_columns
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
                update `{safe_temp_load_table}`
                set `__duplicate_key` = {load_exact_hash_sql},
                    `__business_key` = {load_business_hash_sql}
                """
            )

            cursor.execute(
                f"""
                alter table `{safe_temp_load_table}`
                add index `idx_duplicate_key` (`__duplicate_key`),
                add index `idx_business_key` (`__business_key`)
                """
            )

            cursor.execute(
                f"""
                create temporary table `{safe_temp_business_key_table}`
                (
                    business_key char(64) not null,
                    primary key (business_key)
                ) engine = InnoDB
                """
            )

            cursor.execute(
                f"""
                insert ignore into `{safe_temp_business_key_table}`
                (
                    business_key
                )
                select {target_business_hash_sql}
                from `{safe_target_table}` t
                where lower(trim(cast(t.`{charge_status_COLUMN}` as char))) = lower(trim(%s))
                """,
                (
                    DUPLICATE_BLOCKING_charge_status,
                )
            )

            existing_business_key_count = cursor.rowcount

            log_info(
                "existing CE_Completed business keys prepared : "
                + str(existing_business_key_count)
            )

            cursor.execute(
                f"""
                create temporary table `{safe_temp_exact_key_table}`
                (
                    duplicate_key char(64) not null,
                    primary key (duplicate_key)
                ) engine = InnoDB
                """
            )

            cursor.execute(
                f"""
                insert ignore into `{safe_temp_exact_key_table}`
                (
                    duplicate_key
                )
                select {target_exact_hash_sql}
                from `{safe_target_table}` t
                where lower(trim(cast(t.`{charge_status_COLUMN}` as char))) = lower(trim(%s))
                """,
                (
                    DUPLICATE_BLOCKING_charge_status,
                )
            )

            existing_exact_key_count = cursor.rowcount

            log_info(
                "existing CE_Completed exact duplicate keys prepared : "
                + str(existing_exact_key_count)
            )

            cursor.execute(
                f"""
                select count(*) as duplicate_existing_rows
                from `{safe_temp_load_table}` n
                inner join `{safe_temp_exact_key_table}` e
                    on e.`duplicate_key` = n.`__duplicate_key`
                """
            )

            duplicate_result = cursor.fetchone()

            duplicate_existing_rows = int(
                duplicate_result.get("duplicate_existing_rows", 0)
            )

            if duplicate_existing_rows > 0:

                log_info(
                    "duplicate rows skipped because existing CE_Completed row has the same CE employee ID : "
                    + str(duplicate_existing_rows)
                )

            cursor.execute(
                f"""
                create temporary table `{safe_temp_update_source_table}` as
                select
                    n.`__business_key` as `business_key`,
                    n.`{safe_employee_column}` as `employee_value`
                from `{safe_temp_load_table}` n
                where 1 = 0
                """
            )

            cursor.execute(
                f"""
                alter table `{safe_temp_update_source_table}`
                add primary key (`business_key`)
                """
            )

            cursor.execute(
                f"""
                insert ignore into `{safe_temp_update_source_table}`
                (
                    `business_key`,
                    `employee_value`
                )
                select
                    n.`__business_key`,
                    n.`{safe_employee_column}`
                from `{safe_temp_load_table}` n
                inner join `{safe_temp_business_key_table}` b
                    on b.`business_key` = n.`__business_key`
                left join `{safe_temp_exact_key_table}` e
                    on e.`duplicate_key` = n.`__duplicate_key`
                where e.`duplicate_key` is null
                and {normalized_load_employee_sql} <> ''
                order by n.`__load_row_id`
                """
            )

            update_candidate_rows = cursor.rowcount

            if update_candidate_rows > 0:

                log_info(
                    "CE_Completed rows eligible for CE employee ID update : "
                    + str(update_candidate_rows)
                )

            cursor.execute(
                f"""
                update `{safe_target_table}` t
                inner join `{safe_temp_update_source_table}` u
                    on u.`business_key` = {target_business_hash_sql}
                set t.`{safe_employee_column}` = u.`employee_value`
                where lower(trim(cast(t.`{charge_status_COLUMN}` as char))) = lower(trim(%s))
                and {normalized_target_employee_sql} <> {normalized_update_employee_sql}
                """,
                (
                    DUPLICATE_BLOCKING_charge_status,
                )
            )

            updated_existing_rows = cursor.rowcount

            if updated_existing_rows > 0:

                log_info(
                    "existing CE_Completed rows updated with new CE employee ID : "
                    + str(updated_existing_rows)
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
                left join `{safe_temp_business_key_table}` b
                    on b.`business_key` = n.`__business_key`
                where b.`business_key` is null
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
            "updated": updated_existing_rows,
            "update_candidate_rows": update_candidate_rows,
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
        upload_status,
        created_at,
        updated_at
    )
    values
    (
        %s,%s,%s,%s,%s,%s,%s,%s
    )
    """

    current_datetime = datetime.now().strftime(
        "%Y-%m-%d %H:%M:%S"
    )

    exe_date = current_datetime

    with conn.cursor() as cursor:

        cursor.execute(
            sql,
            (
                project_id,
                sub_project_id,
                file_name,
                exe_date,
                inventory_count,
                upload_status,
                current_datetime,
                current_datetime
            )
        )

    conn.commit()


def build_single_error_description(summary):

    description_parts = [
        "total_rows : " + str(summary["total_rows"]),
        "validated_rows : " + str(summary["validated_rows"]),
        "load_rows_after_file_duplicate_check : " + str(summary.get("load_rows_after_file_duplicate_check", "N/A")),
        "inserted : " + str(summary["inserted"]),
        "updated : " + str(summary.get("updated", 0)),
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

    successfully_processed_rows = (
        int(summary["inserted"])
        + int(summary.get("updated", 0))
    )

    if (
        successfully_processed_rows == int(summary["total_rows"])
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
        error_description,
        error_date,
        created_at,
        updated_at
    )
    values
    (
        %s,%s,%s,%s,%s,%s,%s
    )
    """

    current_datetime = datetime.now().strftime(
        "%Y-%m-%d %H:%M:%S"
    )

    with conn.cursor() as cursor:

        cursor.execute(
            sql,
            (
                project_id,
                sub_project_id,
                error_status_code,
                error_description,
                current_datetime,
                current_datetime,
                current_datetime
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


def get_frontend_error_response(error_message):

    error_text = str(error_message)

    lower_error = error_text.lower()

    error_rules = [
        {
            "code": "UPLOAD_REQUEST_INVALID",
            "keywords": [
                "empty input received",
                "file_path is missing",
                "project_id is missing",
                "sub_project_id is missing",
                "payload file name does not match",
                "file is not inside ar_nonproject_inventory_uploads folder",
                "uploaded file path does not match"
            ],
            "message": "Upload request is invalid. Please refresh the page and upload the file again."
        },
        {
            "code": "FILE_NOT_FOUND",
            "keywords": [
                "file not found",
                "file not found at uploaded path"
            ],
            "message": "Uploaded file could not be found. Please upload the file again."
        },
        {
            "code": "EMPTY_FILE",
            "keywords": [
                "file is empty",
                "file has no data rows"
            ],
            "message": "Uploaded file has no data rows. Please upload a file with valid records."
        },
        {
            "code": "UNSUPPORTED_FILE_TYPE",
            "keywords": [
                "unsupported file type"
            ],
            "message": "Unsupported file format. Please upload only CSV  file."
        },
        {
            "code": "CSV_ENCODING_INVALID",
            "keywords": [
                "uploaded csv encoding is invalid",
                "unicode"
            ],
            "message": "Uploaded CSV encoding is invalid. Please save the file as UTF-8 CSV and upload again."
        },
        {
            "code": "UPLOAD_CONFIGURATION_MISSING",
            "keywords": [
                "project and sub project combination is not configured",
                "data_columns is missing",
                "db_columns is missing",
                "inventory upload configuration"
            ],
            "message": "Upload configuration is missing or incorrect for the selected project and sub project."
        },
        {
            "code": "COLUMN_COUNT_MISMATCH",
            "keywords": [
                "data_columns count",
                "does not match db_columns count"
            ],
            "message": "Upload configuration column count mismatch."
        },
        {
            "code": "DATE_COLUMN_CONFIGURATION_INVALID",
            "keywords": [
                "date columns not present in data_columns"
            ],
            "message": "Date column configuration is incorrect."
        },
        {
            "code": "NUMERIC_COLUMN_CONFIGURATION_INVALID",
            "keywords": [
                "numeric columns not present in data_columns"
            ],
            "message": "Numeric column configuration is incorrect."
        },
        {
            "code": "INVALID_PROJECT_SUB_PROJECT",
            "keywords": [
                "invalid project or sub project combination"
            ],
            "message": "Selected project or sub project is invalid. Please refresh and try again."
        },
        {
            "code": "DYNAMIC_TABLE_MISSING_COLUMNS",
            "keywords": [
                "configured db_columns not present in dynamic table",
                "charge_status column is missing",
                "unknown column",
                "1054"
            ],
            "message": "Upload table configuration is incorrect."
        },
        {
            "code": "HEADER_MISMATCH",
            "keywords": [
                "uploaded file header does not exactly match",
                "missing configured data_columns",
                "extra uploaded file columns",
                "duplicate columns found in uploaded file"
            ],
            "message": "Uploaded file columns do not match the configured template. Please download the latest template and upload again."
        },
        {
            "code": "NO_VALID_ROWS",
            "keywords": [
                "no valid rows to insert",
                "all rows are duplicate inside uploaded file"
            ],
            "message": "No valid rows found for upload. Please verify the file data and upload again."
        },
        {
            "code": "LOCAL_INFILE_DISABLED",
            "keywords": [
                "local_infile is off",
                "load data local infile failed",
                "used command is not allowed"
            ],
            "message": "Bulk upload service is not enabled in database."
        },
        {
            "code": "DATABASE_CONNECTION_FAILED",
            "keywords": [
                "access denied",
                "can't connect",
                "connection refused",
                "connection timed out",
                "2003",
                "1045"
            ],
            "message": "Database connection failed."
        },
        {
            "code": "DATA_TOO_LONG",
            "keywords": [
                "data too long",
                "1406"
            ],
            "message": "One or more values exceed the allowed column length. Please correct the file and upload again."
        },
        {
            "code": "INVALID_DATA_FORMAT",
            "keywords": [
                "incorrect integer value",
                "incorrect date value",
                "truncated incorrect",
                "1366",
                "1292"
            ],
            "message": "One or more values have invalid format. Please correct the file and upload again."
        },
        {
            "code": "DATABASE_REQUIRED_VALUE_MISSING",
            "keywords": [
                "cannot be null",
                "1048"
            ],
            "message": "Required database value is missing. Please verify the file and upload configuration."
        },
        {
            "code": "INVALID_DATE_VALUES",
            "keywords": [
                "invalid date values found"
            ],
            "message": None
        },
    ]

    for rule in error_rules:

        for keyword in rule["keywords"]:

            if keyword in lower_error:

                if rule["code"] == "INVALID_DATE_VALUES":

                    return (
                        rule["code"],
                        error_text
                    )

                return (
                    rule["code"],
                    rule["message"]
                )

    return (
        "INVENTORY_UPLOAD_FAILED",
        "Inventory upload failed."
    )



def insert_inventory_upload_error_log(
        conn,
        project_id,
        sub_project_id,
        file_name,
        table_name,
        error_code,
        user_message,
        exception_type,
        exception_message,
        exception_trace,
        input_payload
):

    sql = """
    insert into inventory_upload_error_logs
    (
        project_id,
        sub_project_id,
        file_name,
        table_name,
        error_code,
        user_message,
        exception_type,
        exception_message,
        exception_trace,
        input_payload,
        created_at,
        updated_at
    )
    values
    (
        %s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s
    )
    """

    current_datetime = datetime.now().strftime(
        "%Y-%m-%d %H:%M:%S"
    )

    if input_payload is None:

        input_payload_json = None

    else:

        try:

            input_payload_json = json.dumps(
                input_payload,
                default=str
            )

        except Exception:

            input_payload_json = json.dumps(
                {
                    "payload_error": "failed to convert input payload to json"
                }
            )

    with conn.cursor() as cursor:

        cursor.execute(
            sql,
            (
                project_id,
                sub_project_id,
                file_name,
                table_name,
                error_code,
                user_message,
                exception_type,
                exception_message,
                exception_trace,
                input_payload_json,
                current_datetime,
                current_datetime
            )
        )

        error_log_id = cursor.lastrowid

    conn.commit()

    return error_log_id



def insert_failed_tracking(
        input_data,
        exception_obj,
        exception_trace,
        error_code,
        user_message
):

    conn = None

    error_log_id = None

    try:

        if isinstance(input_data, dict):

            safe_input_data = input_data

        else:

            safe_input_data = {}

        project_id = safe_input_data.get(
            "project_id"
        )

        sub_project_id = safe_input_data.get(
            "sub_project_id"
        )

        file_name = safe_input_data.get(
            "file_name"
        )

        file_path = safe_input_data.get(
            "file_path"
        )

        table_name = safe_input_data.get(
            "table_name"
        )

        if not file_name and file_path:

            file_name = os.path.basename(
                file_path
            )

        conn = get_connection()

        error_log_id = insert_inventory_upload_error_log(
            conn,
            project_id,
            sub_project_id,
            file_name,
            table_name,
            error_code,
            user_message,
            type(exception_obj).__name__,
            str(exception_obj),
            exception_trace,
            safe_input_data
        )

        if project_id and sub_project_id:

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
                (
                    user_message
                    + " | inventory_upload_error_log_id : "
                    + str(error_log_id)
                )
            )

    except Exception as tracking_error:

        log_error(
            "failed to insert failure tracking : "
            + str(tracking_error)
        )

    finally:

        if conn:

            conn.close()

    return error_log_id


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

        input_data["table_name"] = table_name

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

        (
            df_for_load,
            insert_db_columns,
            date_db_columns
        ) = prepare_load_dataframe(
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
            date_db_columns,
            duplicate_compare_columns,
            file_path
        )

        duplicate_existing_rows = int(
            insert_result.get("duplicate_existing_rows", 0)
        )

        updated_existing_rows = int(
            insert_result.get("updated", 0)
        )

        update_candidate_rows = int(
            insert_result.get("update_candidate_rows", 0)
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
            "updated": updated_existing_rows,
            "update_candidate_rows": update_candidate_rows,
            "skipped_rows": total_skipped_rows,
            "validation_skipped_rows": skipped_rows,
            "duplicate_file_rows": duplicate_file_rows,
            "duplicate_existing_rows": duplicate_existing_rows,
            "duplicate_rows": duplicate_rows,
            "duplicate_rule": "Repeated records with the same Emp Id are skipped. When the same record has a different Emp Id, the existing row is updated instead of creating another record.",
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

        message_parts = []

        inserted_count = int(
            insert_result["inserted"]
        )

        updated_count = int(
            updated_existing_rows
        )

        repeated_count = int(
            duplicate_rows
        )

        if inserted_count > 0:

            message_parts.append(
                str(inserted_count)
                + (
                    " new record added"
                    if inserted_count == 1
                    else " new records added"
                )
            )

        if updated_count > 0:

            message_parts.append(
                str(updated_count)
                + (
                    " existing row updated"
                    if updated_count == 1
                    else " existing rows updated"
                )
            )

        if repeated_count > 0:

            message_parts.append(
                str(repeated_count)
                + (
                    " duplicate record skipped"
                    if repeated_count == 1
                    else " duplicate records skipped"
                )
            )

        if not message_parts:

            message_summary = "No changes were required"

        elif len(message_parts) == 1:

            message_summary = message_parts[0]

        elif len(message_parts) == 2:

            message_summary = (
                message_parts[0]
                + " and "
                + message_parts[1]
            )

        else:

            message_summary = (
                ", ".join(message_parts[:-1])
                + ", and "
                + message_parts[-1]
            )

        message = (
            message_summary
            + ". Completed in "
            + format_duration(total_time)
            + "."
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

        exception_trace = traceback.format_exc()

        log_error(
            e
        )

        error_code, user_message = get_frontend_error_response(
            e
        )

        error_log_id = insert_failed_tracking(
            input_data,
            e,
            exception_trace,
            error_code,
            user_message
        )

        send_warning(
            user_message,
            error_log_id
        )

        sys.exit(1)


if __name__ == "__main__":

    main()