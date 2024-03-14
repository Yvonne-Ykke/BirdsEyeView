from datetime import datetime
import psycopg2
import db_connector as db
import actions.stream as stream
from enums.URLS import URLS


def get_title_id(imdb_extern_id, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s;", (imdb_extern_id,))
        result = cursor.fetchone()
        if result:
            return result[0]
        return None


def check_title_exists(title, title_id, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM alternate_titles WHERE title = %s AND title_id = %s;",
                       (title, title_id))
        result = cursor.fetchone()
        if result:
            print(f"Skipping already imported alternate title: {title}")
            return 1
        return 0


def create_and_get_language_id(language_iso_6391, conn):
    with conn.cursor() as cursor:
        cursor.execute("""
               INSERT INTO languages (iso_6391)
               VALUES (%s)
               ON CONFLICT (iso_6391) DO NOTHING
               RETURNING id;
            """, (language_iso_6391,))
        genre_id = cursor.fetchone()
        if genre_id:
            return genre_id[0]
        else:
            cursor.execute("SELECT id FROM languages WHERE iso_6391 = %s;", (language_iso_6391,))
            return cursor.fetchone()[0]


def load_titles(conn):
    start_time = datetime.now()
    language_with_ids = {}
    COLUMN_NAMES = None

    # Set data source
    url = URLS.TITLE_AKAS.value
    data_source = stream.stream_gzip_content(url)

    try:
        rows_added = 0
        commit_count = 0

        for rows_processed, line in enumerate(data_source):
            row = line.rstrip('\n').split('\t')  # Split de regel in velden

            # If it's the first row, extract column names
            if rows_processed == 0:
                COLUMN_NAMES = row
                continue  # Skip processing the first row

            row = dict(zip(COLUMN_NAMES, row))

            title_id = get_title_id(row['titleId'], conn)
            # Check if the film already exists in the database
            if check_title_exists(row['title'], title_id, conn):
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
                    RETURNING id;
                """, (
                    title_id,
                    language_with_ids[row['language']],
                    row['title'],
                    row['ordering'] if row['types'] != '\\N' else None,
                    row['region'] if row['region'] != '\\N' else None,
                    row['types'] if row['types'] != '\\N' else None,
                    row['attributes'] if row['attributes'] != '\\N' else None,
                    row['isOriginalTitle'] if row['types'] != '\\N' else bool(0)
                ))

                print("Loaded " + str(rows_processed) + row['title'])
                result = cursor.fetchone()
                if result is not None:
                    rows_added += 1

                commit_count += 1
                if commit_count == 5000:
                    conn.commit()
                    print("1000 films imported")
                    commit_count = 0

    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        conn.commit()

    print("Data bevat: " + str(len(language_with_ids)) + " talen.")
    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    print(str(rows_processed) + " rijen totaal in database")
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data ingeladen via stream in" + str(duration))


def execute():
    connection = db.get_connection()
    load_titles(connection)


if __name__ == "__main__":
    execute()
