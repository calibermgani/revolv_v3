import sys
import json
import re
import os
import csv
import zipfile
from collections import defaultdict
from datetime import datetime, timedelta
import pandas as pd
import mysql.connector
os.umask(0o002)

db_config = {
    "user": os.environ.get("DB_USER", "root"),
    "password": os.environ.get("DB_PASSWORD", "resolv@2025!"),
    "host": os.environ.get("DB_HOST", "127.0.0.1"),
    "port": int(os.environ.get("DB_PORT", 3306)),
    "database": os.environ.get("DB_DATABASE", "resolv"),
    "connection_timeout": 30,
    "unix_socket": None,
    "autocommit": True,
}

CODE_MAPPING = {
    "ar_status_code": ("a_r_status_codes", "id", "status_code"),
    "ar_action_code": ("a_r_action_codes", "id", "action_code"),
    "ar_denial_codes": ("a_r_denial_codes", "id", "code_description"),
    "ar_substatus_codes": ("a_r_sub_status_codes", "id", "sub_status_code"),
    "QA_status_code": ("q_a_statuses", "id", "status_code"),
    "QA_sub_status_code": ("q_a_sub_statuses", "id", "sub_status_code"),
    "qa_classification": ("qa_class_cat_scopes", "id", "qa_classification"),
    "qa_category": ("qa_class_cat_scopes", "id", "qa_category"),
    "qa_scope": ("qa_class_cat_scopes", "id", "qa_scope"),
}

STATUS_MAPPING = {
    "AR Inprocess": "CE_Inprocess",
    "AR Pending": "CE_Pending",
    "AR Completed": "CE_Completed",
    "AR Hold": "CE_Hold",
    "QA Inprocess": "QA_Inprocess",
    "QA Pending": "QA_Pending",
    "QA Completed": "QA_Completed",
    "QA Hold": "QA_Hold",
    "AR Assigned": "CE_Assigned",
    "AR Non Workable": "AR_non_workable",
    "Auto Close": "Auto_Close",
}

NULL_AR_AT_STATUSES = (
    "CE_Hold",
    "CE_Pending",
    "CE_Assigned",
    "CE_Inprocess",
    "QA_Hold",
    "QA_Pending",
    "QA_Inprocess",
    "AR_non_workable",
)

CHUNKSIZE = 20000

def create_db_connection():
    try:
        conn = mysql.connector.connect(**db_config)
        if not conn.is_connected():
            raise Exception("Failed to connect to MySQL")
        session_cursor = conn.cursor()
        session_cursor.execute("SET SESSION net_read_timeout = 3600")
        session_cursor.execute("SET SESSION net_write_timeout = 3600")
        session_cursor.execute("SET SESSION wait_timeout = 7200")
        session_cursor.close()
        return conn
    except mysql.connector.Error as e:
        raise Exception(f"MySQL connection failed: {e}")

def close_quietly(conn):
    if not conn:
        return
    try:
        conn.close()
    except Exception:
        pass

def create_excel_writer(path):
    try:
        return pd.ExcelWriter(
            path,
            engine="xlsxwriter",
            engine_kwargs={"options": {"strings_to_urls": False}},
        )
    except TypeError:
        return pd.ExcelWriter(path, engine="xlsxwriter")

def sanitize_csv_chunk(chunk):
    chunk = chunk.copy()
    chunk.columns = [
        str(col).replace("\n", " ").replace("\r", " ").strip()
        for col in chunk.columns
    ]
    unnamed = [col for col in chunk.columns if col == ""]
    if unnamed:
        chunk = chunk.drop(columns=unnamed)

    object_cols = chunk.select_dtypes(include=["object", "string"]).columns
    for col in object_cols:
        chunk[col] = (
            chunk[col]
            .astype(str)
            .str.replace(r"[\r\n\t]+", " ", regex=True)
            .str.replace(r" {2,}", " ", regex=True)
            .str.strip()
        )
    return chunk

def write_chunk_to_csv(chunk, path, write_header):
    chunk = sanitize_csv_chunk(chunk)
    chunk.to_csv(
        path,
        mode="w" if write_header else "a",
        header=write_header,
        index=False,
        encoding="utf-8-sig" if write_header else "utf-8",
        lineterminator="\r\n",
        sep=",",
        quotechar='"',
        quoting=csv.QUOTE_MINIMAL,
        doublequote=True,
        na_rep="--",
    )

def load_ref_data(cursor, cols_to_select=None):
    ref_data = {}
    for col, (ref_table, id_col, def_col) in CODE_MAPPING.items():
        if cols_to_select is not None and col not in cols_to_select:
            continue
        if col == "ar_denial_codes":
            cursor.execute(
                f"SELECT id, denial_code, code_description FROM {ref_table}"
            )
            ref_data[col] = {
                str(r["id"]): f'{r["denial_code"]} - {r["code_description"]}'
                for r in cursor.fetchall()
            }
        elif col == "ar_substatus_codes":
            cursor.execute(
                f"""
                SELECT id, sub_status_code, sub_status_code_description
                FROM {ref_table}
                """
            )
            ref_data[col] = {
                str(r["id"]): f'{r["sub_status_code"]} - {r["sub_status_code_description"]}'
                for r in cursor.fetchall()
            }
        else:
            cursor.execute(f"SELECT {id_col}, {def_col} FROM {ref_table}")
            ref_data[col] = {
                str(r[id_col]): r[def_col] for r in cursor.fetchall()
            }
    return ref_data

def format_ar_at_series(series):
    ar_datetime = pd.to_datetime(series, errors="coerce")
    result = pd.Series(None, index=series.index, dtype=object)
    valid = ar_datetime.notna()
    before_cutoff = valid & (ar_datetime.dt.hour < 8)
    result.loc[valid & ~before_cutoff] = ar_datetime.loc[valid & ~before_cutoff].dt.strftime("%Y-%m-%d")
    result.loc[before_cutoff] = (
        ar_datetime.loc[before_cutoff] - pd.Timedelta(days=1)
    ).dt.strftime("%Y-%m-%d")
    return result

def format_chunk_dates(chunk):
    for col in list(chunk.columns):
        col_l = col.lower()
        if col_l == "ar_at":
            try:
                chunk[col] = format_ar_at_series(chunk[col])
            except Exception:
                pass
        elif "date" in col_l or col_l == "dos":
            try:
                chunk[col] = pd.to_datetime(chunk[col], errors="coerce").dt.strftime("%m/%d/%y")
            except Exception:
                pass
    return chunk

def add_aging_columns(chunk):
    dos = pd.to_datetime(chunk["dos"], errors="coerce")
    aging = (pd.Timestamp.now() - dos).dt.days
    chunk["Aging"] = aging
    aging_range = pd.Series(None, index=chunk.index, dtype=object)
    valid = aging.notna()
    aging_range.loc[valid & (aging <= 30)] = "0-30"
    aging_range.loc[valid & (aging > 30) & (aging <= 60)] = "31-60"
    aging_range.loc[valid & (aging > 60) & (aging <= 90)] = "61-90"
    aging_range.loc[valid & (aging > 90) & (aging <= 120)] = "91-120"
    aging_range.loc[valid & (aging > 120) & (aging <= 180)] = "121-180"
    aging_range.loc[valid & (aging > 180) & (aging <= 365)] = "181-365"
    aging_range.loc[valid & (aging > 365)] = "365+"
    chunk["Aging Range"] = aging_range
    return chunk

def apply_work_time(chunk, work_conn, project_id, sub_project_id):
    if "parent_id" not in chunk.columns:
        return chunk
    parent_ids = chunk["parent_id"].dropna().unique().tolist()
    parent_ids = [int(x) for x in parent_ids if str(x).isdigit()]
    if not parent_ids:
        chunk["work_time"] = None
        return chunk.drop(columns=["parent_id"])

    placeholders = ", ".join(["%s"] * len(parent_ids))
    query = f"""
        SELECT record_id, work_time
        FROM caller_charts_work_logs
        WHERE project_id = %s
          AND sub_project_id = %s
          AND record_id IN ({placeholders})
    """
    sub_cursor = work_conn.cursor(dictionary=True, buffered=True)
    sub_cursor.execute(query, (int(project_id), int(sub_project_id), *parent_ids))
    results = sub_cursor.fetchall()
    sub_cursor.close()
    work_time_map = {int(r["record_id"]): r["work_time"] for r in results} if results else {}
    chunk["work_time"] = chunk["parent_id"].map(
        lambda pid: work_time_map.get(int(pid), None) if pd.notna(pid) and str(pid).isdigit() else None
    )
    return chunk.drop(columns=["parent_id"])

def get_popup_non_visible_patient_columns(cursor, project_id, sub_project_id):

    if not project_id:

        return set()
 
    cursor.execute(

        """

        SELECT label_name

        FROM form_configurations

        WHERE project_id = %s

          AND sub_project_id <=> %s

          AND field_type_3 = 'popup_non_visible'

          AND label_name IS NOT NULL

          AND label_name != ''

          AND LOWER(label_name) NOT IN (

              'ar denial codes',

              'ar substatus codes',

              'ar at',

              'qa at'

          )

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
 
        # Laravel labelNameToColumn equivalent

        label = (

            label

            .replace(" ", "_")

            .replace("/", "_else_")

        )
 
        if label:

            columns.add(label)
 
    return columns
 
def get_project_details(project_id, sub_project_id, conn=None):
    owns_conn = conn is None
    if owns_conn:
        conn = create_db_connection()
    cursor = conn.cursor(dictionary=True, buffered=True)
    cursor.execute(
        "SELECT project_name FROM projects WHERE project_id = %s", (project_id,)
    )
    project = cursor.fetchone()
    cursor.execute(
        "SELECT sub_project_name FROM subprojects WHERE sub_project_id = %s",
        (sub_project_id,),
    )
    subproject = cursor.fetchone()
    cursor.close()
    if owns_conn:
        close_quietly(conn)
    if not project or not subproject:
        raise Exception("Invalid project_id or sub_project_id")
    return project["project_name"], subproject["sub_project_name"]

def normalize_date_range(date_range_str):
    start_str, end_str = [d.strip() for d in date_range_str.split(" - ")]
    try:
        start_date = datetime.strptime(start_str, "%m-%d-%Y")
        end_date = datetime.strptime(end_str, "%m-%d-%Y")
    except ValueError:
        start_date = datetime.strptime(start_str, "%m/%d/%Y")
        end_date = datetime.strptime(end_str, "%m/%d/%Y")
    start_datetime = start_date.replace(hour=8, minute=0, second=0)
    end_datetime = (end_date + timedelta(days=1)).replace(hour=7, minute=59, second=59)

    return start_datetime, end_datetime

# def slugify_sub_project(sub_project_name):
#     sub_project = sub_project_name.lower()
#     sub_project = re.sub(r"[\s\-]+", "_", sub_project)
#     sub_project = re.sub(r"[^a-z0-9_]", "", sub_project)
#     return sub_project #sankar
# def slugify_sub_project(sub_project_name):
#     sub_project = sub_project_name.lower()
#     # 1. remove all non-alphanumeric except space
#     sub_project = re.sub(r"[^a-z0-9\s]", "", sub_project)
#     # 2. replace spaces with single underscore
#     sub_project = re.sub(r"\s+", "_", sub_project)
#     # 3. remove multiple underscores (final safety)
#     sub_project = re.sub(r"_+", "_", sub_project)
#     return sub_project.strip("_") #AR – Behavioral & Mental Health

def slugify_sub_project(sub_project_name):
    sub_project = sub_project_name.lower()
    # 1. replace ANY non-alphanumeric with space (important)
    sub_project = re.sub(r"[^a-z0-9]+", " ", sub_project)
    # 2. convert spaces to underscore
    sub_project = re.sub(r"\s+", "_", sub_project).strip("_")
    return sub_project
def generate_table_name(project_name, sub_project_name):
    # Same as Laravel:
    # Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)), '_')
    table_slug = (
        str(project_name).lower()
        + "_"
        + str(sub_project_name).lower()
    )

    # Laravel Str::slug with separator "_" converts hyphens to underscores.
    table_slug = re.sub(r"[-]+", "_", table_slug)

    # Laravel removes symbols like / instead of converting them to underscores.
    table_slug = table_slug.replace("@", "at")
    table_slug = re.sub(r"[^a-z0-9_\s]+", "", table_slug)

    # Laravel converts spaces and existing underscores to the separator.
    table_slug = re.sub(r"[_\s]+", "_", table_slug).strip("_")

    return f"{table_slug}_datas"

REPORTS_DIR = "storage/app/reports"

def is_sub_project_selected(sub_project_id):
    return sub_project_id not in (None, "", "--")

def get_project_name(project_id, conn=None):
    owns_conn = conn is None
    if owns_conn:
        conn = create_db_connection()
    cursor = conn.cursor(dictionary=True, buffered=True)
    cursor.execute(
        "SELECT project_name FROM projects WHERE project_id = %s", (project_id,)
    )
    project = cursor.fetchone()
    cursor.close()
    if owns_conn:
        close_quietly(conn)
    if not project:
        raise Exception("Invalid project_id")
    return project["project_name"]

def group_subprojects_by_labels(project_id, conn=None):
    """Group sub-projects that share the same form_configurations label set."""
    owns_conn = conn is None
    if owns_conn:
        conn = create_db_connection()
    cursor = conn.cursor(dictionary=True, buffered=True)
    cursor.execute(
        """
        SELECT
            fc.sub_project_id,
            sp.sub_project_name,
            fc.label_name
        FROM form_configurations fc
        INNER JOIN subprojects sp
            ON sp.project_id = fc.project_id
           AND sp.sub_project_id = fc.sub_project_id
        WHERE fc.project_id = %s
          AND fc.sub_project_id IS NOT NULL
          AND fc.deleted_at IS NULL
          AND sp.deleted_at IS NULL
          AND fc.label_name IS NOT NULL
          AND fc.label_name != ''
        """,
        (project_id,),
    )
    rows = cursor.fetchall()
    cursor.close()
    if owns_conn:
        close_quietly(conn)

    labels_by_sub = defaultdict(lambda: {"sub_project_name": None, "labels": set()})
    for row in rows:
        sub_id = row["sub_project_id"]
        labels_by_sub[sub_id]["sub_project_name"] = row["sub_project_name"]
        labels_by_sub[sub_id]["labels"].add(str(row["label_name"]).strip())

    groups = defaultdict(list)
    for sub_id, info in labels_by_sub.items():
        signature = tuple(sorted(info["labels"]))
        groups[signature].append(
            {
                "sub_project_id": sub_id,
                "sub_project_name": info["sub_project_name"],
            }
        )
    return [
        sorted(group, key=lambda sp: (sp["sub_project_name"] or "").lower())
        for group in groups.values()
    ]

def build_group_excel_basename(project_name, subprojects):
    if len(subprojects) == 1:
        table_name = generate_table_name(project_name, subprojects[0]["sub_project_name"])
        return table_name[:-6] if table_name.endswith("_datas") else table_name

    project_slug = slugify_sub_project(project_name)
    sub_slugs = [slugify_sub_project(sp["sub_project_name"]) for sp in subprojects]
    name = f"{project_slug}_{'_'.join(sub_slugs)}"
    if len(name) > 120:
        name = f"{project_slug}_{sub_slugs[0]}_and_{len(sub_slugs) - 1}_more"
    return name

def export_project_to_zip(
    project_id,
    date_range=None,
    checked_values=None,
    user_id=None,
    client_status=None,
):
    conn = None
    work_conn = None
    try:
        conn = create_db_connection()
        work_conn = create_db_connection()
        project_name = get_project_name(project_id, conn=conn)
        groups = group_subprojects_by_labels(project_id, conn=conn)
        if not groups:
            raise Exception("No sub-projects found in form_configurations for this project")

        print(
            f"Project {project_id}: {len(groups)} template group(s)",
            file=sys.stderr,
        )
        for idx, group in enumerate(groups, start=1):
            names = [sp["sub_project_name"] for sp in group]
            print(f"  Group {idx}: {names}", file=sys.stderr)

        meta_cursor = conn.cursor(dictionary=True, buffered=True)
        ref_data = load_ref_data(meta_cursor)
        meta_cursor.close()

        os.makedirs(REPORTS_DIR, exist_ok=True)
        timestamp = datetime.now().strftime("%Y%m%d%H%M%S")
        csv_files = []
        used_names = set()

        for group in groups:
            excel_base = build_group_excel_basename(project_name, group)
            original_base = excel_base
            suffix = 1
            while excel_base in used_names:
                excel_base = f"{original_base}_{suffix}"
                suffix += 1
            used_names.add(excel_base)
            file_name = f"{excel_base}_{timestamp}.csv"
            output_file = os.path.join(REPORTS_DIR, file_name)
            row_num = 0
            column_order = None

            for sp in group:
                result = export_to_excel(
                    project_id=project_id,
                    sub_project_id=sp["sub_project_id"],
                    date_range=date_range,
                    checked_values=checked_values,
                    user_id=user_id,
                    client_status=client_status,
                    output_file=output_file,
                    include_sub_project_name=True,
                    start_row=row_num,
                    column_order=column_order,
                    conn=conn,
                    work_conn=work_conn,
                    ref_data=ref_data,
                    output_format="csv",
                    shared_output=True,
                )
                if isinstance(result, dict):
                    row_num = result.get("row_num", row_num)
                    column_order = result.get("column_order", column_order)

            if row_num == 0:
                pd.DataFrame(
                    {"Message": ["No records found for this template group"]}
                ).to_csv(
                    output_file,
                    index=False,
                    encoding="utf-8-sig",
                    lineterminator="\r\n",
                    sep=",",
                    quoting=csv.QUOTE_MINIMAL,
                )

            os.chmod(output_file, 0o664)
            csv_files.append(output_file)

        zip_name = f"{slugify_sub_project(project_name)}_{timestamp}.zip"
        zip_path = os.path.join(REPORTS_DIR, zip_name)
        with zipfile.ZipFile(
            zip_path, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9
        ) as zf:
            for csv_path in csv_files:
                zf.write(csv_path, os.path.basename(csv_path))
        os.chmod(zip_path, 0o664)

        for csv_path in csv_files:
            try:
                os.remove(csv_path)
            except OSError:
                pass

        return os.path.abspath(zip_path)
    finally:
        close_quietly(conn)
        close_quietly(work_conn)

def export_to_excel(
    project_id,
    sub_project_id,
    date_range=None,
    checked_values=None,
    user_id=None,
    client_status=None,
    output_file=None,
    include_sub_project_name=False,
    excel_writer=None,
    start_row=0,
    column_order=None,
    conn=None,
    work_conn=None,
    ref_data=None,
    output_format="excel",
    shared_output=False,
):
    print(
        "Filters received:",
        project_id,
        sub_project_id,
        user_id,
        client_status,
        date_range,
        file=sys.stderr,
    )

    owns_conn = conn is None
    owns_work_conn = work_conn is None
    data_cursor = None
    try:
        if owns_conn:
            conn = create_db_connection()
        if owns_work_conn:
            work_conn = create_db_connection()

        project_name, sub_project_name = get_project_details(
            project_id, sub_project_id, conn=conn
        )
        table_name = generate_table_name(project_name, sub_project_name)

        start_datetime, end_datetime = (None, None)
        if date_range:
            start_datetime, end_datetime = normalize_date_range(date_range)

        cursor = conn.cursor(dictionary=True, buffered=True)

        cursor.execute(f"SHOW TABLES LIKE '{table_name}'")
        if not cursor.fetchone():
            cursor.close()
            if owns_conn:
                close_quietly(conn)
            if owns_work_conn:
                close_quietly(work_conn)
            if excel_writer is not None or shared_output:
                return {
                    "row_num": start_row,
                    "column_order": column_order,
                    "wrote_rows": False,
                }

            df_empty = pd.DataFrame(
                {"Message": ["No table found for given project/sub-project"]}
            )

            excel_name = table_name
            if excel_name.endswith("_datas"):
                excel_name = excel_name[:-6]

            reports_dir = "/var/www/html/revolv_v3/storage/app/reports"
            os.makedirs(reports_dir, exist_ok=True)

            file_name = f"{excel_name}_{datetime.now().strftime('%Y%m%d%H%M%S')}.xlsx"
            output_file = os.path.join(reports_dir, file_name)

            df_empty.to_excel(output_file, index=False, engine="xlsxwriter")
            os.chmod(output_file, 0o664)
            return os.path.abspath(output_file)

        cursor.execute(f"SHOW COLUMNS FROM {table_name}")
        all_columns = [row["Field"] for row in cursor.fetchall()]
        exclude_cols = (
            "QA_required_sampling",
            "QA_followup_date",
            "annex_coder_trends",
            "annex_qa_trends",
            "qa_cpt_trends",
            "qa_icd_trends",
            "qa_modifiers",
            "CE_status_code",
            "CE_sub_status_code",
            "CE_followup_date",
            "updated_at",
            "created_at",
            "deleted_at",
            "cpt_trends",
            "icd_trends",
            "modifiers",
            "coder_work_date",
            "id",
        )
        project_columns = [c for c in all_columns if c not in exclude_cols]
        patient_exclude_columns = get_popup_non_visible_patient_columns(
            cursor, project_id, sub_project_id
        )
        if patient_exclude_columns:
            project_columns = [
                c for c in project_columns if c not in patient_exclude_columns
            ]

        if checked_values:
            if checked_values[0] == "all":
                cols_to_select = project_columns
            else:
                cols_to_select = [c for c in checked_values if c in project_columns]
        else:
            cols_to_select = project_columns

        if ref_data is None:
            ref_data = load_ref_data(cursor, cols_to_select)

        select_cols_sql = ", ".join([f"`{col}`" for col in cols_to_select])
        main_query = f"SELECT {select_cols_sql} FROM `{table_name}`"
        where_clauses, params = [], []

        if user_id and ("CE_emp_id" in all_columns or "QA_emp_id" in all_columns):
            clause_parts = []
            if "CE_emp_id" in all_columns:
                clause_parts.append("CE_emp_id = %s")
                params.append(user_id)
            if "QA_emp_id" in all_columns:
                clause_parts.append("QA_emp_id = %s")
                params.append(user_id)
            where_clauses.append("(" + " OR ".join(clause_parts) + ")")

        if client_status:
            mapped_status = STATUS_MAPPING.get(client_status, client_status)
            if "chart_status" in all_columns:
                where_clauses.append("chart_status = %s")
                params.append(mapped_status)

        if date_range and "ar_at" in all_columns:
            if "chart_status" in all_columns:
                status_placeholders = ", ".join(["%s"] * len(NULL_AR_AT_STATUSES))
                where_clauses.append(
                    f"(ar_at BETWEEN %s AND %s OR (ar_at IS NULL AND chart_status IN ({status_placeholders})))"
                )
                params.extend([start_datetime, end_datetime, *NULL_AR_AT_STATUSES])
            else:
                where_clauses.append("(ar_at BETWEEN %s AND %s OR ar_at IS NULL)")
                params.extend([start_datetime, end_datetime])

        if where_clauses:
            main_query += " WHERE " + " AND ".join(where_clauses)

        excel_name = table_name
        if excel_name.endswith("_datas"):
            excel_name = excel_name[:-6]
        is_csv = output_format == "csv"
        external_writer = excel_writer is not None
        writer = None
        if is_csv:
            if not shared_output:
                os.makedirs(REPORTS_DIR, exist_ok=True)
                file_name = f"{excel_name}_{datetime.now().strftime('%Y%m%d%H%M%S')}.csv"
                output_file = os.path.join(REPORTS_DIR, file_name)
                row_num = 0
            else:
                row_num = start_row
        elif not external_writer:
            output_file = output_file or f"{excel_name}.xlsx" #local upto 70000 rows
            # output_file = output_file or f"/tmp/{excel_name}.xlsx" # server upto 70000 rows
            # os.makedirs("storage/app/reports", exist_ok=True) #for bulk report storage in server
            os.makedirs("/var/www/html/revolv_v3/storage/app/reports", exist_ok=True) #for bulk report storage in local
 
 
            file_name = f"{excel_name}_{datetime.now().strftime('%Y%m%d%H%M%S')}.xlsx" #for bulk report storage file name
            # output_file = os.path.join("storage/app/reports", file_name) #for bulk report storage in server
            output_file = os.path.join("/var/www/html/revolv_v3/storage/app/reports", file_name) #for bulk report storage in local
            writer = create_excel_writer(output_file)
            row_num = 0
        else:
            writer = excel_writer
            row_num = start_row

        cursor.close()
        reverse_status_mapping = {v: k for k, v in STATUS_MAPPING.items()}

        # Unbuffered fetch so 10-lakh rows are not loaded into memory at once.
        data_cursor = conn.cursor(dictionary=True, buffered=False)
        data_cursor.execute(main_query, tuple(params))
        columns = [desc[0] for desc in data_cursor.description]

        while True:
            rows = data_cursor.fetchmany(CHUNKSIZE)
            if not rows:
                break

            chunk = pd.DataFrame(rows, columns=columns)
            if "dos" in chunk.columns:
                add_aging_columns(chunk)
            format_chunk_dates(chunk)

            for col in CODE_MAPPING:
                if col in chunk.columns and col in ref_data:
                    chunk[col] = chunk[col].astype(str).map(ref_data[col]).fillna(chunk[col])

            if "chart_status" in chunk.columns:
                chunk["chart_status"] = chunk["chart_status"].map(reverse_status_mapping).fillna(chunk["chart_status"])

            chunk = apply_work_time(chunk, work_conn, project_id, sub_project_id)

            # rename columns
            COLUMN_RENAME_MAPPING = {
                "chart_status": "Charge Status",
                "CE_emp_id": "AR Emp Id",
                "ce_hold_reason": "AR Hold Reason",
                "ar_at": "AR Work Date",
                "coder_rework_status": "AR Rework Status",
                "coder_rework_reason": "AR Rework Reason",
                "coder_error_count": "AR Error Count",
                "ar_status_code": "Status Code",
                "ar_action_code": "Action Code",
                "ar_denial_codes": "Denial Code",
                "ar_substatus_codes": "Sub Status Code",
            }

            chunk.rename(
                columns={**COLUMN_RENAME_MAPPING,
                         **{c: c.replace("_", " ").title() for c in chunk.columns if c not in COLUMN_RENAME_MAPPING}},
                inplace=True
            )
            chunk = chunk.fillna("--")
            chunk.replace("", "--", inplace=True)
            if include_sub_project_name:
                chunk.insert(0, "Sub Project Name", sub_project_name)
            if column_order is None:
                column_order = list(chunk.columns)
            else:
                chunk = chunk.reindex(columns=column_order)
                chunk = chunk.fillna("--")
            # chunk.to_excel(writer, index=False, startrow=row_num, header=(row_num==0))
            # row_num += len(chunk)
            # print(f"Written {row_num} rows", file=sys.stderr)
            is_first_chunk = row_num == 0
            if is_csv:
                write_chunk_to_csv(chunk, output_file, is_first_chunk)
            else:
                chunk.to_excel(
                    writer,
                    index=False,
                    startrow=row_num if is_first_chunk else row_num + 1,
                    header=is_first_chunk
                )

            row_num += len(chunk)
            print(f"Written {row_num} rows", file=sys.stderr)

        if data_cursor is not None:
            data_cursor.close()
        if writer is not None and not external_writer:
            writer.close()
        if output_file and (not shared_output):
            os.chmod(output_file, 0o664)
        if owns_conn:
            close_quietly(conn)
        if owns_work_conn:
            close_quietly(work_conn)

        if external_writer or shared_output:
            return {
                "row_num": row_num,
                "column_order": column_order,
                "wrote_rows": row_num > start_row,
            }
        return os.path.abspath(output_file)

    except Exception as e:
        if data_cursor is not None:
            try:
                data_cursor.close()
            except Exception:
                pass
        if owns_conn:
            close_quietly(conn)
        if owns_work_conn:
            close_quietly(work_conn)
        raise Exception(f"Export failed: {e}")

if __name__ == "__main__":
    sys.stdout.reconfigure(line_buffering=True)
    sys.stderr.reconfigure(line_buffering=True)
    try:
        data = sys.stdin.read().strip()
        if data:
            args = json.loads(data)
        else:
            import argparse
            parser = argparse.ArgumentParser()
            parser.add_argument("--project_id", type=int, required=True)
            parser.add_argument("--sub_project_id", type=int, default=None)
            parser.add_argument("--date_range", type=str, default=None)
            parser.add_argument("--user_id", type=str, default=None)
            parser.add_argument("--client_status", type=str, default=None)
            cmd_args = parser.parse_args()
            args = vars(cmd_args)

        common_kwargs = {
            "project_id": args["project_id"],
            "date_range": args.get("date_range"),
            "user_id": args.get("user_id"),
            "client_status": args.get("client_status"),
        }
        if is_sub_project_selected(args.get("sub_project_id")):
            file_path = export_to_excel(
                sub_project_id=args["sub_project_id"],
                **common_kwargs,
            )
        else:
            file_path = export_project_to_zip(**common_kwargs)
        print(file_path)
    except Exception as e:
        print(f"Export failed: {e}", file=sys.stderr)
        sys.exit(1)
