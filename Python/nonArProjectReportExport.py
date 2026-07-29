import sys
import json
import re
import os
from datetime import datetime, timedelta
import pandas as pd
import mysql.connector
os.umask(0o002)

db_config = {
    "user": os.environ.get("DB_USER", "root"),
    "password": os.environ.get("DB_PASSWORD", ""),
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
    return start_date.date(), end_date.date()

def slugify_sub_project(sub_project_name):
    sub_project = sub_project_name.lower()
    # 1. replace ANY non-alphanumeric with space (important)
    sub_project = re.sub(r"[^a-z0-9]+", " ", sub_project)
    # 2. convert spaces to underscore
    sub_project = re.sub(r"\s+", "_", sub_project).strip("_")
    return sub_project
def generate_table_name(project_name, sub_project_name):
    # Same as Laravel:
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

        start_date, end_date = (None, None)
        if date_range:
            start_date, end_date = normalize_date_range(date_range)

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
            "id",
            "invoke_date",
            "created_at",
            "updated_at",
            "deleted_at",
        )
        project_columns = [c for c in all_columns if c not in exclude_cols]

        if checked_values:
            if checked_values[0] == "all":
                cols_to_select = project_columns
            else:
                cols_to_select = [c for c in checked_values if c in project_columns]
        else:
            cols_to_select = project_columns

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

        if user_id and "emp_id" in all_columns:
            where_clauses.append("emp_id = %s")
            params.append(user_id)

        if client_status:
            mapped_status = STATUS_MAPPING.get(client_status, client_status)
            if "charge_status" in all_columns:
                where_clauses.append("charge_status = %s")
                params.append(mapped_status)

        if date_range and "work_date" in all_columns:
            where_clauses.append("work_date BETWEEN %s AND %s")
            params.extend([start_date, end_date])

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
            if "charge_status" in chunk.columns:
                chunk["charge_status"] = (
                    chunk["charge_status"]
                    .astype("string")
                    .str.replace(r"^(CE_|QA_|AR_)", "", regex=True)
                )

            # rename columns
            COLUMN_RENAME_MAPPING = {
                "charge_status": "Charge Status",
                "emp_id": "Emp Id",
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

        if row_num == 0:
            empty_chunk = pd.DataFrame(columns=columns)

            COLUMN_RENAME_MAPPING = {
                "charge_status": "Charge Status",
                "emp_id": "Emp Id",
            }

            empty_chunk.rename(
                columns={
                    **COLUMN_RENAME_MAPPING,
                    **{
                        c: c.replace("_", " ").title()
                        for c in empty_chunk.columns
                        if c not in COLUMN_RENAME_MAPPING
                    },
                },
                inplace=True,
            )

            empty_chunk.to_excel(
                writer,
                index=False,
                startrow=0,
                header=True,
            )

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
