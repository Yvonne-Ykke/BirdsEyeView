from datetime import datetime
import os
import actions.stream as stream
import db_connector as db
from enum import Enum
from enums.URLS import URLS
from enums.PATHS import PATHS

import psycopg2

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

    # set data source
    url = URLS.NAME_BASICS.value
    path = PATHS.NAME_BASICS.value
    data_source = stream.fetch_source(path, url)

    try:
        rows_added = 0

        for rows_processed, line in enumerate(data_source):

            # If it's the first row, extract column names
            if rows_processed == 0:
                COLUMN_NAMES = line
                continue  # Skip processing the first row

            row = dict(zip(COLUMN_NAMES, line))

            for profession in row['primaryProfession'].split(','):
                if profession not in professions:
                    profession_id = create_and_get_profession_id(profession, conn)
                    professions[profession] = profession_id

            for title_id in row['knownForTitles'].split(','):
                with conn.cursor() as cursor:
                    # Query om de people_id op te halen aan de hand van de title_id
                    cursor.execute("""
                        INSERT INTO people (imdb_externid, name, birth_year, death_year)
                        VALUES (%s, %s, %s, %s)
                        ON CONFLICT (imdb_externid) DO NOTHING
                        RETURNING id;
                    """, (row['nconst'], row['primaryName'],row['birthYear'] if row['birthYear'] != '\\N' and row['birthYear'].isdigit() else None
, row['deathYear'] if row['deathYear'] != '\\N' else None))

                    result = cursor.fetchone()
                    if result is not None:
                        rows_added += 1

                    # Check if title is already imported
                    if result is None:
                        continue

                    people_id = result[0]

                    cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s", (title_id,))
                    result = cursor.fetchone()
                    if result:
                        db_title_id = result[0]
                        cursor.execute("""
                        INSERT INTO model_has_crew (model_type, model_id, people_id, person_is_known_for_model)
                        VALUES (%s, %s, %s, %s);
                        """, ('App\Models\Title', db_title_id, people_id, True))
                    else:
                        print("title_id is nog niet bekend: ", title_id)

                    for profession, profession_id in professions.items():
                        with conn.cursor() as cursor:
                            cursor.execute("""
                                INSERT INTO people_professions (people_id, profession_id)
                                VALUES (%s, %s);
                            """, (people_id, profession_id))
            print(f"All crew of movie {row['nconst']} inserted")

    except KeyboardInterrupt:
        print("Process interrupted by keyboard")
    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        conn.commit()

    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    print(str(rows_processed) + " rijen totaal in database")
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data ingalden via stream in " +  str(duration))

def create_and_get_profession_id(profession, conn):
    with conn.cursor() as cursor:
        cursor.execute("""
           INSERT INTO professions (name)
           VALUES (%s)
           RETURNING id;
        """, (profession,))
        profession_id = cursor.fetchone()
        if profession_id:
            return profession_id[0]
        else:
            cursor.execute("SELECT id FROM professions WHERE name = %s;", (profession,))
            return cursor.fetchone()[0]

def execute():

    connection = db.get_connection()
    load_name_basics(connection)

if __name__ == "__main__":
    execute()
