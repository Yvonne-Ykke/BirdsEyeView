from datetime import datetime
import db_connector as db
from enums.URLS import URLS
from enums.PATHS import PATHS
import actions.stream as stream
import psycopg2

# Constants
COMMIT_THRESHOLD = 5000

def load_crew(connection):
    """
    Load and process TSV data from a stream.

    Args:
    connection (psycopg2.extensions.connection): A connection to the database.

    Returns:
    None
    """
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
                # Get internal movie ID for tconst
                movie_id = get_movie_id(connection, row['tconst'])

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
    """
    Add crew members to the database.

    Args:
    connection (psycopg2.extensions.connection): A connection to the database.
    crew_list (list): List of crew members.
    movie_id (int): Internal movie ID.
    commit_count (int): Count of commits.
    role (str): Role of the crew members.
    rows_added (int): Count of rows added.

    Returns:
    tuple: Updated commit count and rows added.
    """
    for crew_member in crew_list:
        people_id = get_people_id(connection, crew_member)
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
            VALUES (%s, %s, %s);
            """, (model_type, movie_id, people_id))

def get_movie_id(connection, tconst):
    """
    Get movie ID from the database based on tconst.

    Args:
    connection (psycopg2.extensions.connection): A connection to the database.
    tconst (str): Movie tconst.

    Returns:
    int: Internal movie ID.
    """
    with connection.cursor() as cursor:
        # Query to get movie id based on the tconst
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s", (tconst,))
        result = cursor.fetchone()

        if result:
            db_title_id = result[0]
            return db_title_id

def get_people_id(connection, person_id):
    """
    Get people ID from the database based on person ID.

    Args:
    connection (psycopg2.extensions.connection): A connection to the database.
    person_id (str): Person ID.

    Returns:
    int: Internal people ID.
    """
    with connection.cursor() as cursor:
        # Query to get people id based on the nmconst
        cursor.execute("SELECT id FROM people WHERE imdb_externid = %s", (person_id,))
        result = cursor.fetchone()

        if result:
            people_id = result[0]
            return people_id
        else:
            print("Person is not inserted in people table")


def execute():
    """
    Execute the script.
    """
    connection = db.get_connection()
    load_crew(connection)

if __name__ == "__main__":
    execute()
