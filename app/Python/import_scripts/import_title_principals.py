from datetime import datetime
import db_connector as db
from enums.URLS import URLS
from enums.PATHS import PATHS
import actions.stream as stream
import psycopg2
import os
import sys

import import_scripts.import_name_basics as name_basics

current_dir = os.path.dirname(os.path.abspath(__file__))
parent_dir = os.path.dirname(current_dir)
sys.path.append(parent_dir)
import repositories.people_repository as people_repository
import repositories.title_repository as title_repository


def title_exists(title, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s;", (title,))
        result = cursor.fetchone()
        if result:
            return result[0]
    return None

def handle_professions(row, professions, conn):
    profession_id = None
    profession = row['category']
    if profession not in professions:
        profession_id = name_basics.create_and_get_profession_id(profession, conn)
        professions[profession] = profession_id
    else:
        profession_id = professions[profession]
    return professions, profession_id

def import_crew_professions(connection, crew_id, profession_id):
    with connection.cursor() as cursor:
          cursor.execute("""
            INSERT INTO crew_professions (crew_id, profession_id)
            VALUES (%s, %s)
            ON CONFLICT (crew_id, profession_id) DO NOTHING
            RETURNING id;
            """, (
            crew_id,
            profession_id,
            ))

          if cursor.fetchone():
            print(f"Imported crew member {crew_id} in crew_professions")

def load_principals(conn):

    professions = {}
    start_time = datetime.now()

    # Set data source
    url = URLS.TITLE_PRINCIPALS.value
    path = PATHS.TITLE_PRINCIPALS.value

    data_source = stream.fetch_source(path, url)

    try:
        rows_added = 0
        commit_count = 0
        rows_processed = 0

        for rows_processed, line in enumerate(data_source, start=1):

            # If it's the first row, extract column names
            if rows_processed == 1:
                COLUMN_NAMES = line
                continue  # Skip processing the first row

            row = dict(zip(COLUMN_NAMES, line))

            title_imdb_id = row['tconst']
            person_id = people_repository.get_person_id(conn, row['nconst'])
            crew_id = name_basics.insert_crew(conn, row, person_id, title_imdb_id, False)

            professions, profession_id = handle_professions(row, professions, conn)
            import_crew_professions(conn, crew_id, profession_id)

            rows_added += 1
            commit_count += 1
            if commit_count == 5000:
                conn.commit()
                print("5000 episodes imported")
                commit_count = 0

    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        conn.commit()

    print(f"{rows_added} new rows added.")
    print(f"{rows_processed} rows processed in total.")
    end_time = datetime.now()
    duration = end_time - start_time
    print(f"Data loaded via stream in {duration}")


def execute():
    """
    Execute the script.
    """
    connection = db.get_connection()
    load_principals(connection)


if __name__ == "__main__":
    execute()
