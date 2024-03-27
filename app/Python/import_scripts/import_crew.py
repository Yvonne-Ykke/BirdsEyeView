from datetime import datetime
import db_connector as db
from enums.URLS import URLS
from enums.PATHS import PATHS
import actions.stream as stream
import psycopg2
import os
import sys

current_dir = os.path.dirname(os.path.abspath(__file__))
parent_dir = os.path.dirname(current_dir)
sys.path.append(parent_dir)
import repositories.people_repository as people_repository
import repositories.title_repository as title_repository

# Constants
COMMIT_THRESHOLD = 100

def load_crew(connection):
    start_time = datetime.now()

    # Set data source
    url = URLS.TITLE_CREW.value
    path = PATHS.TITLE_CREW.value
    data_source = stream.fetch_source(path, url)

    try:
        rows_added = 0
        commit_count = 0

        print("Start loading crew data...")

        for rows_processed, line in enumerate(data_source):

            # If it's the first row, extract column names
            if rows_processed == 0:
                COLUMN_NAMES = line
                continue  # Skip processing the first row

            row = dict(zip(COLUMN_NAMES, line))

            # Check if the movie has directors or writers
            if row['directors'] != '\\N' or row['writers'] != '\\N':
                movie_id = title_repository.get_title_id(row['tconst'], connection)

                # Add directors to database
                if row['directors'] != '\\N':
                    directors = row['directors'].split(',')
                    commit_count, rows_added = add_crew_to_database(connection, directors, movie_id, commit_count, "directors", rows_added)

                # Add writers to database
                if row['writers'] != '\\N':
                    writers = row['writers'].split(',')
                    commit_count, rows_added = add_crew_to_database(connection, writers, movie_id, commit_count, "writers", rows_added)
            else:
                print("movie has no directors or writers")

            # Commit changes if threshold reached
            if commit_count >= COMMIT_THRESHOLD:
                connection.commit()
                print(f"{commit_count} changes committed to database")
                commit_count = 0

    except KeyboardInterrupt:
        print("Process interrupted by keyboard")
    except psycopg2.Error as e:
        connection.rollback()
        print("An error occurred during data processing:", e)
    else:
        connection.commit()

    print(str(rows_added) + " new rows added.")
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data loaded via stream in " + str(duration))

def add_crew_to_database(connection, crew_list, movie_id, commit_count, role, rows_added):

    for crew_member in crew_list:
        people_id = people_repository.get_person_id(connection, crew_member)
        if people_id is not None and movie_id is not None:
            add_crew_to_movie(connection, movie_id, people_id)
            print(f"{commit_count} added {role}: {people_id} to movie {movie_id}")
            commit_count += 1
            rows_added += 1
    return commit_count, rows_added

def add_crew_to_movie(connection, movie_id, people_id):
    """
    Add crew member to the movie in the database.

    Args:
    connection (psycopg2.extensions.connection): A connection to the database.
    movie_id (int): Internal movie ID.
    people_id (int): Internal people ID.

    Returns:
    None
    """
    # Set model type
    model_type = 'App\Models\Title'

    with connection.cursor() as cursor:
        cursor.execute("""
            INSERT INTO model_has_crew (model_type, model_id, people_id)
            VALUES (%s, %s, %s)
            ON CONFLICT (model_id, people_id) DO NOTHING;
            """, (model_type, movie_id, people_id))


def execute():
    """
    Execute the script.
    """
    connection = db.get_connection()
    load_crew(connection)

if __name__ == "__main__":
    execute()
