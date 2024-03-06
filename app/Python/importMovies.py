import os
import psycopg2
import csv
from dotenv import load_dotenv



def load_and_process_partial_tsv_data(file_path, conn):
    """
    Load and process the first 10 rows of TSV data from the file.

    Args:
    file_path (str): The file path of the TSV file.
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    None
    """

    genres_with_ids = {}

    try:
        with conn, open(file_path, 'r', encoding='utf-8') as tsvfile:
            reader = csv.DictReader(tsvfile, delimiter='\t')
            for i, row in enumerate(reader):
                if i >= 2010:
                    break

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
                        print(result[0])
                    else:
                        print("Geen resultaat gevonden")


                    # Check if title is already imported
                    if result is None:
                        continue

                    title_id = result[0]



                with conn.cursor() as cursor:
                    for genre_name, genre_id in genres_with_ids.items():
                        cursor.execute("""
                            INSERT INTO title_genres (title_id, genre_id)
                            VALUES (%s, %s);
                        """, (title_id, genre_id))


    except Exception as e:
        conn.rollback()
        print("An error occurred during data processing:", e)
    else:
        conn.commit()

    print("Data bevat: " + str(len(genres_with_ids)) + " genres.")

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

def get_db_connection():
    """
    Connects to the database using environment variables.

    Returns:
    psycopg2.extensions.connection: A connection to the database.
    """
    # Get the environment variables
    db_host = os.getenv('DB_HOST')
    db_database = os.getenv('DB_DATABASE')
    db_username = os.getenv('DB_USERNAME')
    db_password = os.getenv('DB_PASSWORD')

    try:
        # Establish a connection to the database
        connection = psycopg2.connect(
            host=db_host,
            database=db_database,
            user=db_username,
            password=db_password
        )
        print("Connection established.")

        return connection
    except Exception as e:
        print("Error connecting to the database:", e)




def main():
    # Load environment variables from the .env file
    load_dotenv()
    connection = get_db_connection()

    project_folder = os.getcwd()
    relative_path_to_movie_data = 'storage/app/public/title.basics.tsv/title.basics.tsv'
    movie_data_folder = os.path.join(project_folder, relative_path_to_movie_data)

    load_and_process_partial_tsv_data(movie_data_folder, connection)



if __name__ == "__main__":
    main()
