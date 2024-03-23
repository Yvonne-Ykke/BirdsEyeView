from datetime import datetime
import psycopg2
import db_connector as db
import actions.stream as stream
from enums.URLS import URLS
from enums.PATHS import PATHS


def get_person_id(imdb_extern_id, conn):
    """
    Get the internal ID of a person based on their IMDb external ID.

    Args:
    imdb_extern_id (str): IMDb external ID of the person.
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    int or None: Internal ID of the person if found, otherwise None.
    """
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM people WHERE imdb_externid = %s;", (imdb_extern_id,))
        result = cursor.fetchone()
        if result:
            return result[0]
        return None


def title_exists(title, conn):
    """
    Check if a title exists in the database.

    Args:
    title (str): IMDb external ID of the title.
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    int or None: Internal ID of the title if found, otherwise None.
    """
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s;", (title,))
        result = cursor.fetchone()
        if result:
            return result[0]
    return None


def load_principals(conn):
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



            if title_exists(row['parentTconst'], conn):
                print(f"{rows_processed} parent title already exists")
                continue

            # Check if the series/film already exists in the database
            title_id = title_exists(row['tconst'], conn)
            if title_id is None:
                print(f"{rows_processed} title already exists")
                continue

            with conn.cursor() as cursor:
                cursor.execute("""
                    INSERT INTO title_episodes (title_id, imdb_externid, season_number, episode_number)
                    VALUES (%s, %s, %s, %s)
                    RETURNING id;
                """, (
                    title_id,
                    row['tconst'],
                    row['seasonNumber'] if row['seasonNumber'] != '\\N' else None,
                    row['episodeNumber'] if row['episodeNumber'] != '\\N' else None,
                ))

                print(f"Loaded episode {row['tconst']} - Title ID: {title_id}")
                result = cursor.fetchone()
                if result is not None:
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
    load_episodes(connection)


if __name__ == "__main__":
    execute()
