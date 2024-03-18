from datetime import datetime
import os
import csv
import psycopg2
import db_connector as db
import requests
import zlib
from enums.URLS import URLS
import actions.stream as stream
def load_titles(conn):
    """
    Load and process TSV data from a stream.

    Args:
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    None
    """
    start_time = datetime.now()
    genres_with_ids = {}
    COLUMN_NAMES = None

    # Set data source
    url = URLS.TITLE_BASICS.value
    data_source = stream.stream_gzip_content(url, conn)

    try:
        rows_added = 0
        commit_count = 0

        for rows_processed, line in enumerate(data_source):
            row = line.rstrip('\n').split('\t')  # Split de regel in velden

            # If it's the first row, extract column names
            if rows_processed == 0:
                COLUMN_NAMES = stream.get_header(url).rstrip('\n').split('\t')
                continue  # Skip processing the first row

            row = dict(zip(COLUMN_NAMES, row))

            # Check if the film already exists in the database
            with conn.cursor() as cursor:
               cursor.execute("SELECT imdb_externid FROM titles WHERE imdb_externid = %s;", (row['tconst'],))
               result = cursor.fetchone()
               if result:
                   print(f"{str(rows_processed)} Skipping already imported film: {row['primaryTitle'][:255]}")
                   continue

            for genre_name in row['genres'].split(','):
                if genre_name not in genres_with_ids:
                    genre_id = create_and_get_genre_id(genre_name, conn)
                    genres_with_ids[genre_name] = genre_id

            with conn.cursor() as cursor:
                cursor.execute("""
                    INSERT INTO titles (imdb_externid, primary_title, type, is_adult, start_year, end_year, runtime_minutes, original_title)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                    ON CONFLICT (imdb_externid) DO NOTHING
                    RETURNING id;
                """, (row['tconst'], row['primaryTitle'][:255], row['titleType'], row['isAdult'] if row['isAdult'] != '\\N' else None, row['startYear'] if row['startYear'] != '\\N' else None, row['endYear'] if row['endYear'] != '\\N' else None, row['runtimeMinutes'] if row['runtimeMinutes'] != '\\N' else None, row['originalTitle'][:255]))

                print("Loaded " + str(rows_processed) + " new movies ")

                result = cursor.fetchone()
                if result is not None:
                    rows_added += 1

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
        print('\a') # make a sound
    else:
        conn.commit()

    print("Data bevat: " + str(len(genres_with_ids)) + " genres.")
    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data ingeladen via stream in" + str(duration))

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

def execute():

    connection = db.get_connection()
    load_titles(connection)

if __name__ == "__main__":
    execute()
