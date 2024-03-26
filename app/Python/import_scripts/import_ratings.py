from datetime import datetime
import psycopg2
import db_connector as db
import actions.stream as stream
from enums.URLS import URLS
from enums.PATHS import PATHS


def get_title_id(imdb_extern_id, conn):
    """
    Get the internal ID of a title based on its IMDb external ID.

    Args:
    imdb_extern_id (str): IMDb external ID of the title.
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    int or None: Internal ID of the title if found, otherwise None.
    """
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s;", (imdb_extern_id,))
        result = cursor.fetchone()
        if result:
            return result[0]
        return None

def load_ratings(conn):
    """
    Load ratings data from a stream and insert into the database.

    Args:
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    None
    """
    start_time = datetime.now()
    model_type = 'App\\Models\\Title'

    # Set data source
    url = URLS.TITLE_RATINGS.value
    path = PATHS.TITLE_RATINGS.value
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

            title_id = get_title_id(row['tconst'], conn)

            if title_id is None:
                print(f"{rows_processed} This title does not exist yet in titles table")
                continue

            with conn.cursor() as cursor:
                cursor.execute("""
                    INSERT INTO model_has_ratings (model_type, model_id, average_rating, number_votes)
                    VALUES (%s, %s, %s, %s)
                    ON CONFLICT (model_id)
                    DO UPDATE
                    Set
                        average_rating = EXCLUDED.average_rating,
                        number_votes = EXCLUDED.number_votes
                    RETURNING id;
                """, (
                    model_type,
                    title_id,
                    row['averageRating'],
                    row['numVotes'],
                ))

                result = cursor.fetchone()
                if result is not None:
                    rows_added += 1

                commit_count += 1
                if commit_count == 1000:
                    conn.commit()
                    print("1000 ratings imported")
                    commit_count = 0

                print(f"{rows_processed} Loaded rating {row.get('averageRating')} - Title ID: {title_id}")

    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
        print("Row:", row)  # Print the problematic row
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
    load_ratings(connection)


if __name__ == "__main__":
    execute()
