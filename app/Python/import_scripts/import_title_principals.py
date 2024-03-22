from datetime import datetime
import psycopg2
import db_connector as db
import actions.stream as stream
from enums.URLS import URLS
from enums.PATHS import PATHS


def get_person_id(imdb_extern_id, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM people WHERE imdb_externid = %s;", (imdb_extern_id,))
        result = cursor.fetchone()
        if result:
            return result[0]
        return None

def get_person_id(imdb_extern_id, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM people WHERE imdb_externid = %s;", (imdb_extern_id,))
        result = cursor.fetchone()
        if result:
            return result[0]
        return None

def title_exists(title, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s;",
                       (title,))
        result = cursor.fetchone()
        if result:
            return result[0]
    return None


def load_episodes(conn):
    start_time = datetime.now()

    # Set data source
    url = URLS.TITLE_EPISODE.value
    path = PATHS.TITLE_EPISODE.value

    data_source = stream.fetch_source(path, url)

    try:
        rows_added = 0
        commit_count = 0

        for rows_processed, line in enumerate(data_source):

            # If it's the first row, extract column names
            if rows_processed == 0:
                COLUMN_NAMES = line
                continue  # Skip processing the first row

            row = dict(zip(COLUMN_NAMES, line))

            if title_exists(row['parentTconst'], conn):
                continue

            # Check if the film already exists in the database
            if title_exists(row['tconst'], conn):
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

                print("Loaded principal nr. " + str(rows_processed) + " with id " + row['tconst'])
                result = cursor.fetchone()
                if result is not None:
                    rows_added += 1

                commit_count += 1
                if commit_count == 5000:
                    conn.commit()
                    print("5000 films imported")
                    commit_count = 0

    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        conn.commit()

    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    print(str(rows_processed) + " rijen totaal in database")
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data ingeladen via stream in" + str(duration))


def execute():
    connection = db.get_connection()
    load_episodes(connection)


if __name__ == "__main__":
    execute()
