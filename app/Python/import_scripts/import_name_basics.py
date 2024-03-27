from datetime import datetime
import os, sys
import actions.stream as stream
import db_connector as db
from enum import Enum
from enums.URLS import URLS
from enums.PATHS import PATHS

import psycopg2

current_dir = os.path.dirname(os.path.abspath(__file__))
parent_dir = os.path.dirname(current_dir)
sys.path.append(parent_dir)
import repositories.people_repository as people_repository

def handle_professions(row, professions, conn):
    for profession in row['primaryProfession'].split(','):
        if profession not in professions:
            profession_id = create_and_get_profession_id(profession, conn)
            professions[profession] = profession_id
    return professions

def insert_crew(conn, row, person_id, title_id, person_is_known_for_model):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s", (title_id,))
        result = cursor.fetchone()
        if result:
            db_title_id = result[0]

            cursor.execute("""
            INSERT INTO model_has_crew (model_type, model_id, people_id, person_is_known_for_model)
            VALUES (%s, %s, %s, %s)
            ON CONFLICT (model_id, people_id) DO NOTHING
            RETURNING id;
            """, ('App\Models\Title', db_title_id, person_id, person_is_known_for_model))
            print(f"Inserted crew for movie: {db_title_id}")

            if result is not None:
                crew_id = result[0]
                return crew_id
            else:
                print("Crew insert mislukt, ID is niet opgehaald. (NAME BASICS SCRIPT)")
                return None
        else:
            print("Film titel staat niet in de database.")
            return None

def insert_people_professions(conn, row, professions, person_id):
    with conn.cursor() as cursor:
        for profession_name in row['primaryProfession'].split(','):
            if profession_name in professions:
                profession_id = professions[profession_name]
                cursor.execute("""
                    INSERT INTO people_professions (people_id, profession_id)
                    VALUES (%s, %s)
                    ON CONFLICT (people_id, profession_id) DO NOTHING;
                """, (person_id, profession_id))
                print(f"Inserted profession '{profession_name}' for {row['primaryName']} with ID {person_id}")
            else:
                print(f"Profession '{profession_name}' not found in dictionary.")

def load_name_basics(conn):
    """
    Load and process TSV data from a stream.

    Args:
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    None
    """
    start_time = datetime.now()
    known_for_titles = {}
    professions = {}
    commit_interval = 1000  # Commit after every 1000 records processed
    rows_added = 0
    rows_processed = 0

    # set data source
    url = URLS.NAME_BASICS.value
    path = PATHS.NAME_BASICS.value
    data_source = stream.fetch_file_from_row(path, 0, url)

    try:
        for rows_processed, line in enumerate(data_source):
            # If it's the first row, extract column names
            if rows_processed == 0:
                COLUMN_NAMES = line
                continue  # Skip processing the first row

            row = dict(zip(COLUMN_NAMES, line))

            professions = handle_professions(row, professions, conn)
            person_id = people_repository.insert_person(conn, row)

            if person_id:
                rows_added += 1
            else:
               continue

            insert_people_professions(conn, row, professions, person_id)

            for title_id in row['knownForTitles'].split(','):
                insert_crew(conn, row, person_id, title_id, True)

            rows_processed += 1

            # Commit after every 1000 records processed
            if rows_processed % commit_interval == 0:
                conn.commit()
                print(f"Committed {rows_processed} records")

        # Commit any remaining records
        conn.commit()
    except KeyboardInterrupt:
        print("Process interrupted by keyboard")
    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        print(str(rows_added) + " nieuwe rijen toegevoegd.")
        print(str(rows_processed) + " rijen totaal in database")
        end_time = datetime.now()
        duration = end_time - start_time
        print("Data ingeladen via stream in " +  str(duration))

def create_and_get_profession_id(profession, conn):
    with conn.cursor() as cursor:
        cursor.execute("""
           INSERT INTO professions (name)
           VALUES (%s)
           ON CONFLICT (name) DO NOTHING
           RETURNING id;
        """, (profession,))
        profession_id = cursor.fetchone()
        if profession_id:
            print(f"created new profession: {profession} ")
            return profession_id[0]
        else:
            cursor.execute("SELECT id FROM professions WHERE name = %s;", (profession,))
            print(f"fetched profession: {profession}")
            return cursor.fetchone()[0]

def execute():

    connection = db.get_connection()
    load_name_basics(connection)

if __name__ == "__main__":
    execute()
