import sys
import json
import re
import os
from datetime import datetime, timedelta
import pandas as pd
import re
import mysql.connector
from dateutil import parser as date_parser
os.umask(0o002)

db_config = {
    "user": os.environ.get("DB_USER", "root"),
    "password": os.environ.get("DB_PASSWORD", "resolv@2025!"),
    "host": os.environ.get("DB_HOST", "127.0.0.1"),
    "port": int(os.environ.get("DB_PORT", 3306)),
    "database": os.environ.get("DB_DATABASE", "resolv"),
    "connection_timeout": 10,
    "unix_socket": None,
    "autocommit": True,
}

def create_db_connection():
    try:
        conn = mysql.connector.connect(**db_config)
        if not conn.is_connected():
            raise Exception("Failed to connect to MySQL")
        return conn
    except mysql.connector.Error as e:
        raise Exception(f"MySQL connection failed: {e}")

def get_project_details(project_id, sub_project_id):
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
    conn.close()
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

def export_to_excel(
    project_id,
    sub_project_id,
    date_range=None,
    checked_values=None,
    user_id=None,
    client_status=None,
    output_file=None,
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

    try:
        project_name, sub_project_name = get_project_details(project_id, sub_project_id)
        table_name = generate_table_name(project_name, sub_project_name)

        start_datetime, end_datetime = (None, None)
        if date_range:
            start_datetime, end_datetime = normalize_date_range(date_range)

        conn = create_db_connection()
        cursor = conn.cursor(dictionary=True, buffered=True)

        cursor.execute(f"SHOW TABLES LIKE '{table_name}'")
        if not cursor.fetchone():
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
            cursor.close()
            conn.close()
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
            "id",
        )
        project_columns = [c for c in all_columns if c not in exclude_cols]

        if checked_values:
            if checked_values[0] == "all":
                cols_to_select = project_columns
            else:
                cols_to_select = [c for c in checked_values if c in project_columns]
        else:
            cols_to_select = project_columns

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

        ref_data = {}
        for col, (ref_table, id_col, def_col) in CODE_MAPPING.items():
            if col not in cols_to_select:
                continue
            # -----------------------------
            # SPECIAL CASE: denial codes
            # -----------------------------
            if col == "ar_denial_codes":
                cursor.execute(f"""
                    SELECT id, denial_code, code_description
                    FROM {ref_table}
                """)
                ref_data[col] = {
                    str(r["id"]): f'{r["denial_code"]} - {r["code_description"]}'
                    for r in cursor.fetchall()
                }
            # -----------------------------
            # SPECIAL CASE: substatus codes
            # -----------------------------
            elif col == "ar_substatus_codes":
                cursor.execute(f"""
                    SELECT id, sub_status_code, sub_status_code_description
                    FROM {ref_table}
                """)
                ref_data[col] = {
                    str(r["id"]): f'{r["sub_status_code"]} - {r["sub_status_code_description"]}'
                    for r in cursor.fetchall()
                }
            # -----------------------------
            # NORMAL MAPPING (ALL OTHERS)
            # -----------------------------
            else:
                cursor.execute(f"SELECT {id_col}, {def_col} FROM {ref_table}")
                ref_data[col] = {
                    str(r[id_col]): r[def_col] for r in cursor.fetchall()
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
            where_clauses.append("ar_at BETWEEN %s AND %s")
            params.extend([start_datetime, end_datetime])

        if where_clauses:
            main_query += " WHERE " + " AND ".join(where_clauses)

        excel_name = table_name
        if excel_name.endswith("_datas"):
            excel_name = excel_name[:-6]
        output_file = output_file or f"{excel_name}.xlsx" #local upto 70000 rows
        # output_file = output_file or f"/tmp/{excel_name}.xlsx" # server upto 70000 rows
        # os.makedirs("/var/www/html/revolv_v3/storage/app/reports", exist_ok=True) #for bulk report storage in server
        os.makedirs("storage/app/reports", exist_ok=True) #for bulk report storage in local
 
 
        file_name = f"{excel_name}_{datetime.now().strftime('%Y%m%d%H%M%S')}.xlsx" #for bulk report storage file name
        # output_file = os.path.join("/var/www/html/revolv_v3/storage/app/reports", file_name) #for bulk report storage in server
        output_file = os.path.join("storage/app/reports", file_name) #for bulk report storage in local


        chunksize = 10000
        writer = pd.ExcelWriter(output_file, engine="xlsxwriter")
        row_num = 0

        # ---------- CHUNKED FETCH IMPLEMENTATION ----------
        cursor.execute(main_query, tuple(params))
        columns = [desc[0] for desc in cursor.description]

        while True:
            rows = cursor.fetchmany(chunksize)
            if not rows:
                break

            chunk = pd.DataFrame(rows, columns=columns)
            for col in chunk.columns:
                if "date" in col.lower() or col.lower() in ["dos"]:
                    try:
                        chunk[col] = pd.to_datetime(chunk[col], errors="coerce").dt.strftime("%m/%d/%y")
                    except Exception:
                        pass
            for col in CODE_MAPPING:
                if col in chunk.columns and col in ref_data:
                    chunk[col] = chunk[col].astype(str).map(ref_data[col]).fillna(chunk[col])

            if "chart_status" in chunk.columns:
                reverse_status_mapping = {v: k for k, v in STATUS_MAPPING.items()}
                chunk["chart_status"] = chunk["chart_status"].map(reverse_status_mapping).fillna(chunk["chart_status"])

            # work_time logic
            if 'parent_id' in chunk.columns:
                parent_ids = chunk['parent_id'].dropna().unique().tolist()
                if parent_ids:
                    parent_ids = [int(x) for x in parent_ids if str(x).isdigit()]
                    if parent_ids:
                        placeholders = ', '.join(['%s'] * len(parent_ids))
                        query = f"""
                            SELECT record_id, work_time
                            FROM caller_charts_work_logs
                            WHERE project_id = %s
                            AND sub_project_id = %s
                            AND record_id IN ({placeholders})
                        """
                        sub_params = (int(project_id), int(sub_project_id), *parent_ids)
                        sub_cursor = conn.cursor(dictionary=True, buffered=True)
                        sub_cursor.execute(query, sub_params)
                        results = sub_cursor.fetchall()
                        sub_cursor.close()

                        work_time_map = {int(r['record_id']): r['work_time'] for r in results} if results else {}
                        chunk['work_time'] = chunk['parent_id'].map(lambda pid: work_time_map.get(int(pid), None))
                    else:
                        chunk['work_time'] = None
                    chunk = chunk.drop(columns=['parent_id'])    
                else:
                    chunk['work_time'] = None

            # aging calculation
            if "dos" in chunk.columns:
                aging_counts = []
                aging_ranges = []
                current_date = datetime.now()
                for dos_val in chunk["dos"]:
                    if pd.isna(dos_val):
                        aging_counts.append(None)
                        aging_ranges.append(None)
                        continue
                    try:
                        dos_date = date_parser.parse(dos_val) if isinstance(dos_val, str) else dos_val
                        aging = (current_date - dos_date).days
                        aging_counts.append(aging)

                        if aging <= 30:
                            aging_range = "0-30"
                        elif aging <= 60:
                            aging_range = "31-60"
                        elif aging <= 90:
                            aging_range = "61-90"
                        elif aging <= 120:
                            aging_range = "91-120"
                        elif aging <= 180:
                            aging_range = "121-180"
                        elif aging <= 365:
                            aging_range = "181-365"
                        else:
                            aging_range = "365+"
                        aging_ranges.append(aging_range)
                    except Exception:
                        aging_counts.append(None)
                        aging_ranges.append(None)

                chunk["Aging"] = aging_counts
                chunk["Aging Range"] = aging_ranges

            # rename columns
            COLUMN_RENAME_MAPPING = {
                "chart_status": "Charge Status",
                "CE_emp_id": "AR Emp Id",
                "ce_hold_reason": "AR Hold Reason",
                "coder_work_date": "AR Work Date",
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
            chunk.to_excel(writer, index=False, startrow=row_num, header=(row_num==0))
            row_num += len(chunk)
            print(f"Written {row_num} rows", file=sys.stderr)

        writer.close()
        os.chmod(output_file, 0o664)
        cursor.close()
        conn.close()

        return os.path.abspath(output_file)

    except Exception as e:
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
            parser.add_argument("--sub_project_id", type=int, required=True)
            parser.add_argument("--date_range", type=str, default=None)
            parser.add_argument("--user_id", type=str, default=None)
            parser.add_argument("--client_status", type=str, default=None)
            cmd_args = parser.parse_args()
            args = vars(cmd_args)

        file_path = export_to_excel(
            project_id=args["project_id"],
            sub_project_id=args["sub_project_id"],
            date_range=args.get("date_range"),
            user_id=args.get("user_id"),
            client_status=args.get("client_status"),
        )
        print(file_path)
    except Exception as e:
        print(f"Export failed: {e}", file=sys.stderr)
        sys.exit(1)
