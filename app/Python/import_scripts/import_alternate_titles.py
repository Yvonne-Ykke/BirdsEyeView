from datetime import datetime
import psycopg2
import db_connector as db
import actions.stream as stream
from enums.URLS import URLS
from enums.PATHS import PATHS


def get_title_id(imdb_extern_id, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s;", (imdb_extern_id,))
        result = cursor.fetchone()
        if result:
            return result[0]
        return None


def create_and_get_language_id(language_iso_6391, conn):
    """
    Create a new language entry if it doesn't exist in the database and return its id.
    """
    with conn.cursor() as cursor:
        cursor.execute("""
               INSERT INTO languages (iso_6391)
               VALUES (%s)
               ON CONFLICT (iso_6391) DO NOTHING
               RETURNING id;
            """, (language_iso_6391,))
        language_id = cursor.fetchone()
        if language_id:
            return language_id[0]
        else:
            cursor.execute("SELECT id FROM languages WHERE iso_6391 = %s;", (language_iso_6391,))
            language_id = cursor.fetchone()
            if language_id:
                return language_id[0]
            else:
                return None


def load_titles(conn):
    start_time = datetime.now()
    language_with_ids = {}

    # Set data source
    url = URLS.TITLE_AKAS.value
    path = PATHS.TITLE_AKAS.value
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

            title_id = get_title_id(row['titleId'], conn)

            if title_id is None:
                print(f"Title nr. {rows_processed} is not imported yet, alternate title can not be imported")
                continue

            if row['language'] == '\\N':
                language_with_ids[row['language']] = None

            if row['language'] not in language_with_ids:
                language_with_ids[row['language']] = create_and_get_language_id(row['language'], conn)

            with conn.cursor() as cursor:
                cursor.execute("""
                    INSERT INTO alternate_titles (title_id, language_id,
                    title, ordering, region, types, attributes, is_original_title)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                    ON CONFLICT (title_id, ordering) DO UPDATE
                    SET
                        title = EXCLUDED.title,
                        language_id = EXCLUDED.language_id,
                        ordering = EXCLUDED.ordering,
                        region = EXCLUDED.region,
                        types = EXCLUDED.types,
                        attributes = EXCLUDED.attributes,
                        is_original_title = EXCLUDED.is_original_title
                    RETURNING id;
                """, (
                    title_id,
                    language_with_ids[row['language']],
                    row['title'][:255],
                    row['ordering'] if row['ordering'] != '\\N' else None,
                    row['region'] if row['region'] != '\\N' else None,
                    row['types'] if row['types'] != '\\N' else None,
                    row['attributes'][:255] if row['attributes'] != '\\N' else None,
                    row['isOriginalTitle'] if row['types'] != '\\N' else bool(0)
                ))

                print(f"{rows_processed} Loaded alternate title nr. {title_id} [{row['ordering']}] named: {row['title'].rstrip()[:20]}...")
                result = cursor.fetchone()
                if result is not None:
                    rows_added += 1

                commit_count += 1
                if commit_count == 100:
                    conn.commit()
                    commit_count = 0

    except KeyboardInterrupt:
        print("Process interrupted by keyboard")
    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        conn.commit()

    print("Data bevat: " + str(len(language_with_ids)) + " talen.")
    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data ingeladen via stream in" + str(duration))


def execute():
    connection = db.get_connection()
    load_titles(connection)


if __name__ == "__main__":
    execute()
