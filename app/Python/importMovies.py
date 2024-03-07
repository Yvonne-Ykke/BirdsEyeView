from datetime import datetime
import os
import csv
import psycopg2
import db_connector as db
import requests
import zlib
from enum import Enum

class URLs(Enum):
    BASE_URL = "https://datasets.imdbws.com/"
    NAME_BASICS = BASE_URL + "name.basics.tsv.gz"
    TITLE_AKAS = BASE_URL + "title.akas.tsv.gz"
    TITLE_BASICS = BASE_URL + "title.basics.tsv.gz"
    TITLE_CREW = BASE_URL + "title.crew.tsv.gz"
    TITLE_EPISODE = BASE_URL + "title.episode.tsv.gz"
    TITLE_PRINCIPALS = BASE_URL + "title.principals.tsv.gz"
    TITLE_RATINGS = BASE_URL + "title.ratings.tsv.gz"

def stream_gzip_content(url, nr_of_rows):
    with requests.get(url, stream=True) as response:
        if response.status_code == 200:
            decompressor = zlib.decompressobj(zlib.MAX_WBITS | 16)
            line_count = 0
            buffer = ""  # Buffer om gedeeltelijke rijen op te slaan
            for chunk in response.iter_content(chunk_size=1024):
                decompressed_chunk = decompressor.decompress(chunk).decode('utf-8')
                lines = (buffer + decompressed_chunk).split('\n')
                buffer = lines.pop()  # Laatste element is mogelijk een gedeeltelijke rij
                for line in lines:
                    yield line
                    line_count += 1
                    if line_count == nr_of_rows:
                        return
        else:
            print("Er is een fout opgetreden bij het downloaden van het bestand.")


def load_titles(conn, nr_of_rows, file = None):
    """
    Load and process TSV data from either a stream or a local file.

    Args:
    conn (psycopg2.extensions.connection): A connection to the database.
    nr_of_rows (int): Number of rows to process.
    file(string): File to read data from. If None, data will be streamed.

    Returns:
    None
    """
    start_time = datetime.now()
    genres_with_ids = {}
    COLUMN_NAMES = None

    # set data source
    if file is None:
        url = URLs.TITLE_BASICS.value
        data_source = stream_gzip_content(url, nr_of_rows)
    else:
        # Read from local file
        with open(file, 'r', encoding='utf-8') as f:
            data_source = f.readlines()[:nr_of_rows]

    try:
        rows_added = 0

        for rows_processed, line in enumerate(data_source):
            if rows_processed >= nr_of_rows:
                break

            row = line.rstrip('\n').split('\t')  # Split de regel in velden

            # If it's the first row, extract column names
            if rows_processed == 0:
                COLUMN_NAMES = row
                continue  # Skip processing the first row

            row = dict(zip(COLUMN_NAMES, row))

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
                """, (row['tconst'], row['primaryTitle'], row['titleType'], row['isAdult'] if row['isAdult'] != '\\N' else None, row['startYear'] if row['startYear'] != '\\N' else None, row['endYear'] if row['endYear'] != '\\N' else None, row['runtimeMinutes'] if row['runtimeMinutes'] != '\\N' else None, row['originalTitle']))

                result = cursor.fetchone()
                if result is not None:
                    rows_added += 1

                # Check if title is already imported
                if result is None:
                    continue

                title_id = result[0]
                for genre_name, genre_id in genres_with_ids.items():
                    cursor.execute("""
                        INSERT INTO title_genres (title_id, genre_id)
                        VALUES (%s, %s);
                    """, (title_id, genre_id))


    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        conn.commit()

    print("Data bevat: " + str(len(genres_with_ids)) + " genres.")
    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    print(str(rows_processed) + " rijen totaal in database")
    method =  "stream" if file is None else "bestand"
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data ingeladen via " + method + " in " + str(duration))

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



def main():

    project_folder = os.getcwd()
    relative_path_to_movie_data = 'storage/app/public/title.basics.tsv/title.basics.tsv'
    movie_data_path = os.path.join(project_folder, relative_path_to_movie_data)

    nr_of_rows = 500

    connection = db.get_connection()

    # Load data via stream
    load_titles(connection, nr_of_rows)

    # Load data via bestand (let op: 3x langzamer!)
    # load_and_process_partial_tsv_data(connection, nr_of_rows, movie_data_path)

if __name__ == "__main__":
    main()
