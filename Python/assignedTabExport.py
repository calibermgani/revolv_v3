import json
import os
import re
import sys
from datetime import date, datetime, timedelta
from decimal import Decimal

import mysql.connector
import csv
import math
from time import perf_counter


os.umask(0o002)


DB_CONFIG = {
    "user": os.environ.get("DB_USER", "root"),
    "password": os.environ.get("DB_PASSWORD", ""),
    "host": os.environ.get("DB_HOST", "127.0.0.1"),
    "port": int(os.environ.get("DB_PORT", 3306)),
    "database": os.environ.get("DB_DATABASE", "resolv"),
    "connection_timeout": 20,
    "autocommit": True,
}


EXCLUDED_COLUMNS = {
    "id",
    "ce_hold_reason",
    "qa_hold_reason",
    "QA_rework_comments",
    "QA_required_sampling",
    "coder_rework_reason",
    "coder_error_count",
    "qa_error_count",
    "tl_error_count",
    "tl_comments",
    "QA_followup_date",
    "CE_status_code",
    "CE_sub_status_code",
    "CE_followup_date",
    "cpt_trends",
    "icd_trends",
    "modifiers",
    "annex_coder_trends",
    "annex_qa_trends",
    "coder_cpt_trends",
    "coder_icd_trends",
    "coder_modifiers",
    "qa_cpt_trends",
    "qa_icd_trends",
    "qa_modifiers",
    "updated_at",
    "created_at",
    "deleted_at",
}


CLIENT_EXCLUDED_COLUMNS = {
    "id",
    "QA_emp_id",
    "ce_hold_reason",
    "qa_hold_reason",
    "qa_work_status",
    "QA_required_sampling",
    "QA_rework_comments",
    "coder_rework_status",
    "coder_rework_reason",
    "coder_error_count",
    "qa_error_count",
    "tl_error_count",
    "tl_comments",
    "QA_status_code",
    "QA_sub_status_code",
    "qa_classification",
    "qa_category",
    "qa_scope",
    "QA_followup_date",
    "CE_status_code",
    "CE_sub_status_code",
    "CE_followup_date",
    "cpt_trends",
    "icd_trends",
    "modifiers",
    "annex_coder_trends",
    "annex_qa_trends",
    "coder_cpt_trends",
    "coder_icd_trends",
    "coder_modifiers",
    "qa_cpt_trends",
    "qa_icd_trends",
    "qa_modifiers",
    "ar_status_code",
    "ar_action_code",
    "ar_denial_codes",
    "ar_substatus_codes",
    "updated_at",
    "created_at",
    "deleted_at",
}


PRIVILEGED_DESIGNATIONS = (
    "Manager",
    "VP",
    "Leader",
    "Team Lead",
    "CEO",
    "Vice",
    "Group Coordinator",
    "Subject Matter Expert",
    "Group Co-ordinator - Quality",
     "Group Co-ordinator - AR",
)


HEADER_RENAME_MAPPING = {
    "CE_emp_id": "AR Emp Id",
    "chart_status": "Charge Status",
    "coder_work_date": "AR Work Date",
    "coder_rework_status": "AR Rework Status",
}


DATE_FORMATS = (
    "%Y-%m-%d",
    "%Y-%m-%d %H:%M:%S",
    "%m/%d/%Y",
    "%m-%d-%Y",
    "%Y/%m/%d",
)

OUTPUT_DATE_FORMAT = "%m/%d/%Y"


NON_WORKABLE_FIELDS = {
    "ar_notes",
    "notes",
    "remarks",
    "comments",
}


def log(message):
    print(message, file=sys.stderr, flush=True)


def get_popup_non_visible_patient_columns(connection, project_id, sub_project_id):
    if not project_id:
        return set()

    cursor = connection.cursor(dictionary=True)

    try:
        cursor.execute(
            """
            SELECT label_name
            FROM form_configurations
            WHERE project_id = %s
              AND sub_project_id <=> %s
              AND field_type_3 = 'popup_non_visible'
              AND label_name IS NOT NULL
              AND label_name != ''
              AND deleted_at IS NULL
            """,
            (
                project_id,
                None if sub_project_id in (None, "", "--") else sub_project_id,
            ),
        )

        columns = set()

        for row in cursor.fetchall():
            label = str(row.get("label_name") or "").lower()
            label = label.replace(" ", "_").replace("/", "_else_")

            if label:
                columns.add(label)

        return columns
    finally:
        cursor.close()


def create_db_connection():
    try:
        connection = mysql.connector.connect(**DB_CONFIG)

        if not connection.is_connected():
            raise RuntimeError("Failed to connect to MySQL.")

        return connection
    except mysql.connector.Error as exc:
        raise RuntimeError(
            f"MySQL connection failed: {exc}"
        ) from exc


def validate_table_name(table_name):
    if not table_name:
        raise ValueError("Table name is missing.")

    if not re.fullmatch(r"[A-Za-z0-9_]+", table_name):
        raise ValueError("Invalid table name.")


def table_exists(connection, table_name):
    cursor = connection.cursor()

    try:
        cursor.execute(
            """
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = %s
              AND table_name = %s
            """,
            (
                DB_CONFIG["database"],
                table_name,
            ),
        )

        return cursor.fetchone()[0] > 0
    finally:
        cursor.close()


def get_table_column_metadata(connection, table_name):
    cursor = connection.cursor(dictionary=True)

    try:
        cursor.execute(
            f"SHOW COLUMNS FROM `{table_name}`"
        )

        return cursor.fetchall()
    finally:
        cursor.close()


def is_privileged_user(login_emp_id, designation):
    if login_emp_id == "Admin":
        return True

    designation = designation or ""

    return any(
        keyword in designation
        for keyword in PRIVILEGED_DESIGNATIONS
    )


def normalize_filter_value(value):
    if isinstance(value, list):
        return "_el_".join(
            str(item) for item in value
        )

    if isinstance(value, bool):
        return 1 if value else 0

    return value


def parse_exact_date(value):
    if not isinstance(value, str):
        return None

    value = value.strip()

    for date_format in DATE_FORMATS:
        try:
            return datetime.strptime(
                value,
                date_format
            ).date()
        except ValueError:
            continue

    return None


def append_dynamic_search_filters(
    search_filters,
    available_columns,
    where_clauses,
    parameters,
):
    for field, raw_value in search_filters.items():
        if field not in available_columns:
            continue

        value = normalize_filter_value(raw_value)

        if value is None or value == "":
            continue

        exact_date = parse_exact_date(value)

        if isinstance(value, (int, float, Decimal, bool)):
            where_clauses.append(
                f"`{field}` = %s"
            )
            parameters.append(value)

        elif exact_date is not None:
            where_clauses.append(
                f"DATE(`{field}`) = %s"
            )
            parameters.append(exact_date)

        elif "$" in str(value) or "." in str(value):
            where_clauses.append(
                f"`{field}` = %s"
            )
            parameters.append(str(value))

        else:
            where_clauses.append(
                f"`{field}` LIKE %s"
            )
            parameters.append(
                f"%{value}%"
            )


def append_business_status_filters(
    login_emp_id,
    designation,
    chart_status,
    record_status,
    available_columns,
    where_clauses,
    parameters,
):
    privileged = is_privileged_user(
        login_emp_id,
        designation,
    )

    start_date = (
        datetime.now() - timedelta(days=30)
    ).replace(
        hour=0,
        minute=0,
        second=0,
        microsecond=0,
    )

    end_date = datetime.now().replace(
        hour=23,
        minute=59,
        second=59,
        microsecond=999999,
    )

    if privileged:
        if record_status == "unassigned":
            where_clauses.append(
                "`chart_status` IN (%s, %s)"
            )
            parameters.extend([
                chart_status,
                "QA_Inprocess",
            ])

            where_clauses.append(
                "`qa_work_status` IS NULL"
            )

            where_clauses.append(
                "`QA_emp_id` IS NULL"
            )

        elif record_status == "Assigned":
            where_clauses.append(
                "`chart_status` IN (%s, %s)"
            )
            parameters.extend([
                chart_status,
                "QA_Inprocess",
            ])

            where_clauses.append(
                "`QA_emp_id` IS NOT NULL"
            )

            where_clauses.append(
                "`qa_work_status` = %s"
            )
            parameters.append("Sampling")

        elif record_status == "Auto_Close":
            where_clauses.append(
                "`qa_work_status` = %s"
            )
            parameters.append("Auto_Close")

        else:
            where_clauses.append(
                "`chart_status` = %s"
            )
            parameters.append(chart_status)

            if chart_status == "Rebuttal":
                where_clauses.append(
                    "`ar_manager_rebuttal_status` = %s"
                )
                parameters.append("agree")

            where_clauses.append(
                "`updated_at` BETWEEN %s AND %s"
            )
            parameters.extend([
                start_date,
                end_date,
            ])

    else:
        if record_status == "Assigned":
            where_clauses.append(
                "`chart_status` IN (%s, %s)"
            )
            parameters.extend([
                chart_status,
                "QA_Inprocess",
            ])

            where_clauses.append(
                "`QA_emp_id` = %s"
            )
            parameters.append(login_emp_id)

            where_clauses.append(
                "`qa_work_status` = %s"
            )
            parameters.append("Sampling")

        elif record_status == "Auto_Close":
            where_clauses.append(
                "`qa_work_status` = %s"
            )
            parameters.append("Auto_Close")

        else:
            where_clauses.append(
                "`chart_status` = %s"
            )
            parameters.append(chart_status)

            where_clauses.append(
                "`QA_emp_id` = %s"
            )
            parameters.append(login_emp_id)

            if chart_status == "Rebuttal":
                where_clauses.append(
                    "`ar_manager_rebuttal_status` = %s"
                )
                parameters.append("agree")

            where_clauses.append(
                "`updated_at` BETWEEN %s AND %s"
            )
            parameters.extend([
                start_date,
                end_date,
            ])


def append_client_business_status_filters(
    login_emp_id,
    designation,
    chart_status,
    record_status,
    resource_name,
    where_clauses,
    parameters,
):
    privileged = is_privileged_user(
        login_emp_id,
        designation,
    )

    start_date = (
        datetime.now() - timedelta(days=30)
    ).replace(
        hour=0,
        minute=0,
        second=0,
        microsecond=0,
    )

    end_date = datetime.now().replace(
        hour=23,
        minute=59,
        second=59,
        microsecond=999999,
    )

    if privileged:
        if record_status == "unassigned":
            where_clauses.append(
                "`chart_status` IN (%s, %s)"
            )
            parameters.extend([
                chart_status,
                "CE_Inprocess",
            ])
            where_clauses.append(
                "`CE_emp_id` IS NULL"
            )

        elif record_status == "assigned":
            where_clauses.append(
                "`chart_status` IN (%s, %s)"
            )
            parameters.extend([
                chart_status,
                "CE_Inprocess",
            ])

            if resource_name in (None, "", "null"):
                where_clauses.append(
                    "`CE_emp_id` IS NOT NULL"
                )
            else:
                where_clauses.append(
                    "`CE_emp_id` = %s"
                )
                parameters.append(resource_name)

        else:
            where_clauses.append(
                "`chart_status` = %s"
            )
            parameters.append(chart_status)

            if chart_status == "Rebuttal":
                where_clauses.append(
                    "(`ar_manager_rebuttal_status` IS NULL "
                    "OR `ar_manager_rebuttal_status` != %s)"
                )
                parameters.append("agree")

            where_clauses.append(
                "`updated_at` BETWEEN %s AND %s"
            )
            parameters.extend([
                start_date,
                end_date,
            ])

    else:
        if record_status == "assigned":
            where_clauses.append(
                "`chart_status` IN (%s, %s)"
            )
            parameters.extend([
                chart_status,
                "CE_Inprocess",
            ])
            where_clauses.append(
                "`CE_emp_id` = %s"
            )
            parameters.append(login_emp_id)

        else:
            where_clauses.append(
                "`chart_status` = %s"
            )
            parameters.append(chart_status)

            if chart_status == "Rebuttal":
                where_clauses.append(
                    "(`ar_manager_rebuttal_status` IS NULL "
                    "OR `ar_manager_rebuttal_status` != %s)"
                )
                parameters.append("agree")

            where_clauses.append(
                "`CE_emp_id` = %s"
            )
            parameters.append(login_emp_id)
            where_clauses.append(
                "`updated_at` BETWEEN %s AND %s"
            )
            parameters.extend([
                start_date,
                end_date,
            ])


def determine_client_export_status(
    chart_status,
    record_status,
):
    if record_status == "unassigned":
        return "Un" + str(chart_status or "").replace(
            "CE_",
            "",
        )

    if chart_status and "CE_" in chart_status:
        return chart_status.replace(
            "CE_",
            "",
        )

    if chart_status and "AR_" in chart_status:
        return chart_status.replace(
            "AR_",
            "",
        )

    if chart_status == "Revoke":
        return "Rework"

    return chart_status or "Client"


def determine_export_status(
    chart_status,
    record_status,
):
    if record_status == "unassigned":
        return "UnAssigned"

    if record_status == "Assigned":
        return "Assigned"

    if record_status == "Auto_Close":
        return "Auto_Close"

    if chart_status and "QA_" in chart_status:
        return chart_status.replace(
            "QA_",
            "",
        )

    return chart_status or "Quality"


def load_reference_map(
    connection,
    table_name,
    value_column,
):
    if not table_exists(connection, table_name):
        return {}

    cursor = connection.cursor(dictionary=True)

    try:
        cursor.execute(
            f"""
            SELECT `id`, `{value_column}`
            FROM `{table_name}`
            """
        )

        return {
            str(row["id"]): row[value_column]
            for row in cursor.fetchall()
        }
    finally:
        cursor.close()


def load_qa_reference_maps(connection):
    return {
        "QA_status_code": load_reference_map(
            connection,
            "q_a_statuses",
            "status_code",
        ),
        "QA_sub_status_code": load_reference_map(
            connection,
            "q_a_sub_statuses",
            "sub_status_code",
        ),
        "qa_classification": load_reference_map(
            connection,
            "qa_class_cat_scopes",
            "qa_classification",
        ),
        "qa_category": load_reference_map(
            connection,
            "qa_class_cat_scopes",
            "qa_category",
        ),
        "qa_scope": load_reference_map(
            connection,
            "qa_class_cat_scopes",
            "qa_scope",
        ),
    }


def find_non_workable_reason_table(connection):
    cursor = connection.cursor(dictionary=True)

    try:
        cursor.execute(
            """
            SELECT
                c.table_name
            FROM information_schema.columns c
            INNER JOIN information_schema.columns id_column
                ON id_column.table_schema = c.table_schema
               AND id_column.table_name = c.table_name
               AND id_column.column_name = 'id'
            WHERE c.table_schema = %s
              AND c.column_name = 'reason_type'
            ORDER BY
                CASE
                    WHEN c.table_name LIKE '%%non%%work%%'
                    THEN 0
                    ELSE 1
                END,
                c.table_name
            LIMIT 1
            """,
            (DB_CONFIG["database"],),
        )

        row = cursor.fetchone()

        return row["table_name"] if row else None
    finally:
        cursor.close()


def load_non_workable_reason_map(connection):
    reason_table = find_non_workable_reason_table(
        connection
    )

    if not reason_table:
        return {}

    return load_reference_map(
        connection,
        reason_table,
        "reason_type",
    )


def is_missing_value(value):
    if value is None:
        return True

    if isinstance(value, str):
        return value == ""

    if isinstance(value, float):
        return math.isnan(value)

    return False


def format_date_value(value):
    if value is None:
        return "--"

    normalized_date = None

    if isinstance(value, datetime):
        normalized_date = value.strftime("%m/%d/%Y")

    elif isinstance(value, date):
        normalized_date = value.strftime("%m/%d/%Y")

    else:
        value_text = str(value).strip()

        if value_text in (
            "",
            "--",
            "0000-00-00",
            "0000-00-00 00:00:00",
        ):
            return "--"

        supported_formats = (
            "%Y-%m-%d",
            "%Y-%m-%d %H:%M:%S",
            "%m/%d/%Y",
            "%m-%d-%Y",
            "%Y/%m/%d",
        )

        for input_format in supported_formats:
            try:
                parsed_date = datetime.strptime(
                    value_text,
                    input_format
                )

                normalized_date = parsed_date.strftime(
                    "%m/%d/%Y"
                )

                break

            except ValueError:
                continue

        if normalized_date is None:
            return value_text

  
    return f'="{normalized_date}"'

def calculate_fast_aging(dos_value, current_date):
    if is_missing_value(dos_value):
        return "--", "--"

    parsed_date = None

    if isinstance(dos_value, datetime):
        parsed_date = dos_value.date()

    elif isinstance(dos_value, date):
        parsed_date = dos_value

    elif isinstance(dos_value, str):
        stripped_value = dos_value.strip()

        if stripped_value in ("", "--", "0000-00-00"):
            return "--", "--"

        for date_format in DATE_FORMATS:
            try:
                parsed_date = datetime.strptime(
                    stripped_value,
                    date_format,
                ).date()
                break
            except ValueError:
                continue

    if parsed_date is None:
        return "--", "--"

    aging_count = (
        current_date - parsed_date
    ).days

    if aging_count <= 30:
        aging_range = "0-30"
    elif aging_count <= 60:
        aging_range = "31-60"
    elif aging_count <= 90:
        aging_range = "61-90"
    elif aging_count <= 120:
        aging_range = "91-120"
    elif aging_count <= 180:
        aging_range = "121-180"
    elif aging_count <= 365:
        aging_range = "181-365"
    else:
        aging_range = "365+"

    return aging_count, aging_range


def format_fast_chart_status(
    chart_status,
    ce_emp_id,
):
    if is_missing_value(chart_status):
        return "--"

    chart_status = str(chart_status)

    if "CE_" in chart_status:
        if is_missing_value(ce_emp_id):
            return (
                "Un " +
                chart_status.replace("CE_", "")
            )

        return (
            "AR " +
            chart_status.replace("CE_", "")
        )

    if "QA_" in chart_status:
        return (
            "QA " +
            chart_status.replace("QA_", "")
        )

    if chart_status == "AR_non_workable":
        return "Non Workable"

    if chart_status == "Auto_Close":
        return "Auto Close"

    return chart_status


def make_heading(field):
    if field in HEADER_RENAME_MAPPING:
        return HEADER_RENAME_MAPPING[field]

    return (
        field.replace("_else_", "/")
        .replace("_", " ")
        .title()
    )


def write_query_directly_to_csv(
    connection,
    query,
    parameters,
    selected_columns,
    date_columns,
    output_file,
    reference_maps,
    non_workable_reason_map,
):
    total_started_at = perf_counter()

    with open(
        output_file,
        "w",
        newline="",
        encoding="utf-8-sig",
        buffering=4 * 1024 * 1024,
    ) as csv_file:
        writer = csv.writer(
            csv_file,
            delimiter=",",
            quotechar='"',
            quoting=csv.QUOTE_MINIMAL,
            lineterminator="\n",
        )

        headings = [
            make_heading(column)
            for column in selected_columns
        ]

        headings.extend([
            "Aging",
            "Aging Range",
        ])

        writer.writerow(headings)

        cursor = connection.cursor(
            buffered=False
        )

        current_date = datetime.now().date()
        total_written = 0
        fetch_size = 25000

        fetch_total = 0.0
        transform_total = 0.0
        write_total = 0.0

        column_index = {
            column: index
            for index, column in enumerate(
                selected_columns
            )
        }

        chart_status_index = column_index.get(
            "chart_status"
        )

        ce_emp_id_index = column_index.get(
            "CE_emp_id"
        )

        dos_index = column_index.get(
            "dos"
        )

        field_processors = []

        for field_index, field in enumerate(
            selected_columns
        ):
            if field == "chart_status":
                processor_type = "chart_status"

            elif field in reference_maps:
                processor_type = "reference"

            elif field in NON_WORKABLE_FIELDS:
                processor_type = "non_workable"

            elif field in date_columns:
                processor_type = "date"

            else:
                processor_type = "raw"

            field_processors.append(
                (
                    field_index,
                    field,
                    processor_type,
                )
            )

        try:
            execute_started_at = perf_counter()

            cursor.execute(
                query,
                tuple(parameters),
            )

            log(
                "Query execute initialization time: "
                f"{perf_counter() - execute_started_at:.2f} seconds"
            )

            while True:
                fetch_started_at = perf_counter()

                rows = cursor.fetchmany(
                    fetch_size
                )

                fetch_total += (
                    perf_counter() - fetch_started_at
                )

                if not rows:
                    break

                transform_started_at = perf_counter()
                output_rows = []

                for record in rows:
                    chart_status = (
                        record[chart_status_index]
                        if chart_status_index is not None
                        else None
                    )

                    ce_emp_id = (
                        record[ce_emp_id_index]
                        if ce_emp_id_index is not None
                        else None
                    )

                    output_row = []

                    for (
                        field_index,
                        field,
                        processor_type,
                    ) in field_processors:
                        value = record[field_index]

                        if processor_type == "chart_status":
                            value = format_fast_chart_status(
                                value,
                                ce_emp_id,
                            )

                        elif processor_type == "reference":
                            if value is None or value == "":
                                value = "--"
                            else:
                                value = (
                                    reference_maps[field].get(
                                        str(value),
                                        value,
                                    )
                                )

                        elif (
                            processor_type == "non_workable"
                            and chart_status == "AR_non_workable"
                        ):
                            if value is None or value == "":
                                value = ""
                            else:
                                value = (
                                    non_workable_reason_map.get(
                                        str(value),
                                        value,
                                    )
                                )

                        elif processor_type == "date":
                            value = format_date_value(
                                value
                            )

                        elif value is None or value == "":
                            value = "--"

                        output_row.append(value)

                    if dos_index is not None:
                        aging_count, aging_range = (
                            calculate_fast_aging(
                                record[dos_index],
                                current_date,
                            )
                        )
                    else:
                        aging_count = "--"
                        aging_range = "--"

                    output_row.extend([
                        aging_count,
                        aging_range,
                    ])

                    output_rows.append(output_row)

                transform_total += (
                    perf_counter() - transform_started_at
                )

                write_started_at = perf_counter()

                writer.writerows(output_rows)

                write_total += (
                    perf_counter() - write_started_at
                )

                total_written += len(output_rows)

                log(
                    f"Written {total_written} rows."
                )

        finally:
            cursor.close()

    log(
        f"Database fetch time: {fetch_total:.2f} seconds"
    )
    log(
        f"Transformation time: {transform_total:.2f} seconds"
    )
    log(
        f"CSV writing time: {write_total:.2f} seconds"
    )
    log(
        "Direct CSV function total time: "
        f"{perf_counter() - total_started_at:.2f} seconds"
    )

    return total_written


def generate_quality_export(payload):
    total_started_at = perf_counter()

    report_type = payload.get(
        "report_type",
        "quality",
    )
    table_name = payload.get("table_name")
    login_emp_id = payload.get("login_emp_id")
    designation = payload.get(
        "emp_designation",
        "",
    )
    chart_status = payload.get(
        "chart_status"
    )
    record_status = payload.get(
        "record_status_val"
    )
    resource_name = payload.get(
        "resource_name"
    )
    export_file_name = payload.get(
        "export_file_name",
        "Resolv",
    )
    search_filters = (
        payload.get("search_filters") or {}
    )
    reports_directory = payload.get(
        "reports_directory"
    )

    validate_table_name(table_name)

    if not reports_directory:
        raise ValueError(
            "Reports directory is missing."
        )

    os.makedirs(
        reports_directory,
        exist_ok=True,
    )

    connection = create_db_connection()

    try:
        if not table_exists(
            connection,
            table_name,
        ):
            raise RuntimeError(
                "Export table does not exist: "
                f"{table_name}"
            )

        column_metadata = get_table_column_metadata(
            connection,
            table_name,
        )

        all_columns = [
            column["Field"]
            for column in column_metadata
        ]

        date_columns = {
            column["Field"]
            for column in column_metadata
            if (
                str(column["Type"]).lower().startswith(
                    ("date", "datetime", "timestamp")
                )
                or "date" in column["Field"].lower()
                or column["Field"].lower() == "dos"
            )
        }

        excluded_columns = (
            CLIENT_EXCLUDED_COLUMNS
            if report_type == "client"
            else EXCLUDED_COLUMNS
        )

        selected_columns = [
            column
            for column in all_columns
            if column not in excluded_columns
        ]

        patient_exclude_columns = get_popup_non_visible_patient_columns(
            connection,
            payload.get("project_id"),
            payload.get("sub_project_id"),
        )

        if patient_exclude_columns:
            selected_columns = [
                column
                for column in selected_columns
                if column not in patient_exclude_columns
            ]

        if not selected_columns:
            raise RuntimeError(
                "No exportable columns were found."
            )

        where_clauses = []
        parameters = []

        append_dynamic_search_filters(
            search_filters=search_filters,
            available_columns=all_columns,
            where_clauses=where_clauses,
            parameters=parameters,
        )

        if report_type == "client":
            append_client_business_status_filters(
                login_emp_id=login_emp_id,
                designation=designation,
                chart_status=chart_status,
                record_status=record_status,
                resource_name=resource_name,
                where_clauses=where_clauses,
                parameters=parameters,
            )
        else:
            append_business_status_filters(
                login_emp_id=login_emp_id,
                designation=designation,
                chart_status=chart_status,
                record_status=record_status,
                available_columns=all_columns,
                where_clauses=where_clauses,
                parameters=parameters,
            )

        select_sql = ", ".join(
            f"`{column}`"
            for column in selected_columns
        )

        query = (
            f"SELECT {select_sql} "
            f"FROM `{table_name}`"
        )

        if where_clauses:
            query += (
                " WHERE " +
                " AND ".join(where_clauses)
            )

        if report_type == "client":
            export_status = determine_client_export_status(
                chart_status,
                record_status,
            )
        else:
            export_status = determine_export_status(
                chart_status,
                record_status,
            )

        safe_status = re.sub(
            r"[^A-Za-z0-9_-]+",
            "_",
            export_status,
        )

        safe_export_file_name = re.sub(
            r"[^A-Za-z0-9 _-]+",
            "_",
            str(export_file_name or "Resolv"),
        ).strip()

        if report_type == "client":
            file_name = (
                f"{safe_export_file_name} _ "
                f"{safe_status}_export_"
                f"{datetime.now().strftime('%Y%m%d%H%M%S')}.csv"
            )
        else:
            file_name = (
                f"Resolv_{safe_status}_Export_"
                f"{datetime.now().strftime('%Y%m%d%H%M%S')}.csv"
            )

        output_file = os.path.abspath(
            os.path.join(
                reports_directory,
                file_name,
            )
        )

        mapping_started_at = perf_counter()

        reference_maps = load_qa_reference_maps(
            connection
        )

        non_workable_reason_map = (
            load_non_workable_reason_map(
                connection
            )
        )

        log(
            "Reference mapping time: "
            f"{perf_counter() - mapping_started_at:.2f} seconds"
        )

        export_started_at = perf_counter()

        written_rows = (
            write_query_directly_to_csv(
                connection=connection,
                query=query,
                parameters=parameters,
                selected_columns=selected_columns,
                date_columns=date_columns,
                output_file=output_file,
                reference_maps=reference_maps,
                non_workable_reason_map=(
                    non_workable_reason_map
                ),
            )
        )

        log(
            "Query and CSV writing time: "
            f"{perf_counter() - export_started_at:.2f} seconds"
        )

        log(
            "CSV export completed with "
            f"{written_rows} rows."
        )

        log(
            "Total report time: "
            f"{perf_counter() - total_started_at:.2f} seconds"
        )

        os.chmod(output_file, 0o664)

        return output_file

    finally:
        connection.close()


def main():
    sys.stdout.reconfigure(
        line_buffering=True
    )

    sys.stderr.reconfigure(
        line_buffering=True
    )

    raw_input = sys.stdin.read().strip()

    if not raw_input:
        raise ValueError(
            "No export payload was received."
        )

    payload = json.loads(raw_input)

    output_file = generate_quality_export(
        payload
    )

    print(output_file, flush=True)


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(
            f"Export failed: {exc}",
            file=sys.stderr,
            flush=True,
        )
        sys.exit(1)
