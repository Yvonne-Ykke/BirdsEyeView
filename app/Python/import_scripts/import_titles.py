from datetime import datetime
import psycopg2
import db_connector as db
from enums.URLS import URLS
from enums.PATHS import PATHS
import actions.stream as stream

def load_titles(conn):

    start_time = datetime.now()
    genres_with_ids = {}

    # Set data source
    path = PATHS.TITLE_BASICS.value
    url = URLS.TITLE_BASICS.value
    data_source = stream.fetch_source(path, url)

    try:
        rows_added = 0
        commit_count = 0

        with conn.cursor() as cursor:
            for rows_processed, line in enumerate(data_source):

                # If it's the first row, extract column names
                if rows_processed == 0:
                    COLUMN_NAMES = line
                    continue  # Skip processing the first row

                # Als het aantal kolommen niet overeenkomt met het aantal kolommen in de header, sla de rij over
                if len(line) != len(COLUMN_NAMES):
                    print(f"Skipping row {rows_processed} because the number of columns does not match the header")
                    continue

                row = dict(zip(COLUMN_NAMES, line))

                # Check if the film already exists in the database
                cursor.execute("SELECT imdb_externid FROM titles WHERE imdb_externid = %s;", (row['tconst'],))
                result = cursor.fetchone()
                if result:
                    print(f"{str(rows_processed)} Skipping already imported film: {row['primaryTitle'][:255]}")
                    continue

                # Check if 'genres' key exists in the row
                if 'genres' in row:
                    genres = row['genres'].split(',')
                    for genre_name in genres:
                        if genre_name not in genres_with_ids:
                            genre_id = create_and_get_genre_id(genre_name, conn)
                            genres_with_ids[genre_name] = genre_id

                cursor.execute("""
                    INSERT INTO titles (imdb_externid, primary_title, type, is_adult, start_year, end_year, runtime_minutes, original_title)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                    ON CONFLICT (imdb_externid) DO UPDATE
                    SET
                        start_year = excluded.start_year
                        end_year = excluded.end_year
                        type = excluded.type
                    RETURNING id;
                """, (row['tconst'],
                 row['primaryTitle'][:255],
                 row['titleType'],
                 row['isAdult'] if row['isAdult'] != '\\N' else None,
                 row['startYear'] if row['startYear'] != '\\N' else None,
                 row['endYear'] if row['endYear'] != '\\N' else None,
                 row['runtimeMinutes'] if row['runtimeMinutes'] != '\\N' else None,
                 row['originalTitle'][:255]))

                result = cursor.fetchone()
                title_id = result[0]

                if 'genres' in row:
                    for genre in genres:
                        cursor.execute("""
                        INSERT INTO title_genres (title_id, genre_id)
                        VALUES (%s, %s);
                        """, (title_id, genres_with_ids[genre]))

                    if result is not None:
                        rows_added += 1

                print("Loaded " + str(rows_processed) + " new movies ")

                commit_count += 1
                if commit_count == 1000:
                    conn.commit()
                    print("1000 films imported")
                    commit_count = 0


    except KeyboardInterrupt:
        print("Process interrupted by keyboard")
    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
        print('\a')  # make a sound
    else:
        conn.commit()

    print("Data bevat: " + str(len(genres_with_ids)) + " genres.")
    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data ingeladen via stream in " + str(duration))


def create_and_get_genre_id(genre_name, conn):
    """
    Get the ID of the genre from the genres table, or create a new entry if it doesn't exist.

    Args:
    genre_name (str): The name of the genre.
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    int: The ID of the genre.
    """

    with conn.cursor() as cursor:
        if genre_name == '\\N':
            genre_name = 'undefined'
        cursor.execute("""
           INSERT INTO genres (name)
           VALUES (%s)
           ON CONFLICT (name) DO NOTHING
           RETURNING id;
        """, (genre_name,))
        genre_id = cursor.fetchone()
        if genre_id:
            return genre_id[0]
        else:
            cursor.execute("SELECT id FROM genres WHERE name = %s;", (genre_name,))
            return cursor.fetchone()[0]

def start_from(conn):
    """
    Get the row number to start importing from based on the number of existing rows in the titles table.

    Args:
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    int: The row number to start importing from.
    """
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT COUNT(*) FROM titles;")
            row_count = cursor.fetchone()[0]
            print(f"Total rows in titles table: {row_count}")
            return row_count + 1
    except psycopg2.Error as e:
        print("An error occurred while fetching row count:", e)
        return None


def execute():

    connection = db.get_connection()
    load_titles(connection)

if __name__ == "__main__":
    execute()
