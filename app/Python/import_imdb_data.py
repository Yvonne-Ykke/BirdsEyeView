from datetime import datetime
import os
import csv
import psycopg2
import db_connector as db
import requests
import zlib
from enum import Enum
import enums.URLS as URLS
import import_scripts.import_titles as import_titles



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
    url = URLS.NAME_BASICS.value
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

    import_titles.execute()

    # Load data via stream
    #load_name_basics(connection)


    # Load data via bestand (let op: 3x langzamer!)
    # load_titles(connection, nr_of_rows, movie_data_path)

if __name__ == "__main__":
    main()
