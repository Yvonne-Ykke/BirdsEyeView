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

def stream_gzip_content(url):
    with requests.get(url, stream=True) as response:
        if response.status_code == 200:
            decompressor = zlib.decompressobj(zlib.MAX_WBITS | 16)
            # line_count = 0
            buffer = ""  # Buffer om gedeeltelijke rijen op te slaan
            for chunk in response.iter_content(chunk_size=1024):
                decompressed_chunk = decompressor.decompress(chunk)
                try:
                    decoded_chunk = decompressed_chunk.decode('utf-8')
                except UnicodeDecodeError:
                    decoded_chunk = decompressed_chunk.decode('latin-1', errors='ignore')
                lines = (buffer + decoded_chunk).split('\n')
                buffer = lines.pop()  # Laatste element is mogelijk een gedeeltelijke rij
                for line in lines:
                    yield line
#                     line_count += 1
#                     if line_count == 1000:
#                         return
        else:
            print("Er is een fout opgetreden bij het downloaden van het bestand.")


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
    url = URLs.TITLE_BASICS.value
    data_source = stream_gzip_content(url)

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

            # Check if the film already exists in the database
            with conn.cursor() as cursor:
               cursor.execute("SELECT imdb_externid FROM titles WHERE imdb_externid = %s;", (row['tconst'],))
               result = cursor.fetchone()
               if result:
                   print(f"Skipping already imported film: {row['primaryTitle']}")
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
                """, (row['tconst'], row['primaryTitle'], row['titleType'], row['isAdult'] if row['isAdult'] != '\\N' else None, row['startYear'] if row['startYear'] != '\\N' else None, row['endYear'] if row['endYear'] != '\\N' else None, row['runtimeMinutes'] if row['runtimeMinutes'] != '\\N' else None, row['originalTitle']))

                print("Loaded " + str(rows_processed) + row['primaryTitle'])
                result = cursor.fetchone()
                if result is not None:
                    rows_added += 1

                commit_count += 1
                if commit_count == 1000:
                    conn.commit()
                    print("1000 films imported")
                    commit_count = 0

    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        conn.commit()

    print("Data bevat: " + str(len(genres_with_ids)) + " genres.")
    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    print(str(rows_processed) + " rijen totaal in database")
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

def load_name_basics(conn):
    """
    Load and process TSV data from a stream.

    Args:
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    None
    """
    start_time = datetime.now()
    known_for_titles = {}
    professions = {}
    COLUMN_NAMES = None

    # set data source
    url = URLs.NAME_BASICS.value
    data_source = stream_gzip_content(url)

    try:
        rows_added = 0

        for rows_processed, line in enumerate(data_source):

            row = line.rstrip('\n').split('\t')  # Split de regel in velden

            # If it's the first row, extract column names
            if rows_processed == 0:
                COLUMN_NAMES = row
                continue  # Skip processing the first row

            row = dict(zip(COLUMN_NAMES, row))

            for profession in row['primaryProfession'].split(','):
                if profession not in professions:
                    profession_id = create_and_get_profession_id(profession, conn)
                    professions[profession] = profession_id

            for title_id in row['knownForTitles'].split(','):
                with conn.cursor() as cursor:
                    # Query om de people_id op te halen aan de hand van de title_id
                    cursor.execute("SELECT imdb_externid FROM titles WHERE imdb_externid = %s", (title_id,))
                    result = cursor.fetchone()
                    if result:
                        db_title_id = result[0]
                        cursor.execute("""
                        INSERT INTO model_has_crew (model_id, people_id, person_is_known_for_model)
                        VALUES (%s, %s, %s);
                        """, (db_title_id, row['people_id'], TRUE))
                    else:
                        print("title_id is nog niet bekend: ", title_id)

                    cursor.execute("""
                        INSERT INTO people (imdb_externid, name, birth_year, death_year)
                        VALUES (%s, %s, %s, %s)
                        ON CONFLICT (imdb_externid) DO NOTHING
                        RETURNING id;
                    """, (row['nconst'], row['primaryName'], row['birthYear'] if row['birthYear'] != '\\N' else None, row['deathYear'] if row['deathYear'] != '\\N' else None))

                    result = cursor.fetchone()
                    if result is not None:
                        rows_added += 1

                    # Check if title is already imported
                    if result is None:
                        continue

                    people_id = result[0]
                    for profession, profession_id in professions.items():
                        with conn.cursor() as cursor:
                            cursor.execute("""
                                INSERT INTO people_professions (people_id, profession_id)
                                VALUES (%s, %s);
                            """, (people_id, profession_id))


    except psycopg2.Error as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        conn.commit()

    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    print(str(rows_processed) + " rijen totaal in database")
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data ingalden via stream in " +  str(duration))

def create_and_get_profession_id(profession, conn):
    with conn.cursor() as cursor:
        cursor.execute("""
           INSERT INTO professions (name)
           VALUES (%s)
           RETURNING id;
        """, (profession,))
        profession_id = cursor.fetchone()
        if profession_id:
            return profession_id[0]
        else:
            cursor.execute("SELECT id FROM professions WHERE name = %s;", (profession,))
            return cursor.fetchone()[0]

def get_total_rows(data_source):
    total_rows = 0
    for line in data_source:
        total_rows += 1
    return total_rows

def main():

    project_folder = os.getcwd()
    relative_path_to_movie_data = 'storage/app/public/title.basics.tsv/title.basics.tsv'
    movie_data_path = os.path.join(project_folder, relative_path_to_movie_data)

    connection = db.get_connection()

    # Load data via stream
    load_titles(connection)
    load_name_basics(connection)



    # Load data via bestand (let op: 3x langzamer!)
    # load_titles(connection, nr_of_rows, movie_data_path)

if __name__ == "__main__":
    main()
