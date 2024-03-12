from datetime import datetime
import os
import db_connector as db

from enums.URLS import URLS
import actions.stream as stream
import psycopg2

def load_crew(connection):
    """
    Load and process TSV data from a stream.

    Args:
    conn (psycopg2.extensions.connection): A connection to the database.

    Returns:
    None
    """
    start_time = datetime.now()
    COLUMN_NAMES = None

    # set data source
    url = URLS.TITLE_CREW.value
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

            # Controleer of de directors of writers leeg zijn

            movie_has_directors = '\\N' != row['directors']
            movie_has_writers = '\\N' != row['writers']

            if not movie_has_directors and not movie_has_writers:
               continue

            # Haal interne id for tconst / film op
            movie_id = get_movie_id(connection, row['tconst'])

            # directors toevoegen aan database
            if movie_has_directors:
                for director in row['directors'].split(','):
                    # Haal people_id op voor director
                    people_id = get_people_id(connection, director)
                    if people_id and movie_id:
                        add_crew_to_movie(connection, movie_id, people_id, commit_count)
                        commit_count += 1
                        rows_added += 1
                        if commit_count == 1000:
                            connection.commit()
                            print(f"\n{commit_count} crew members added to movies\n")
                            commit_count = 0

            # writers toevoegen aan database
            if movie_has_writers:
                for writer in row['writers'].split(','):
                    people_id = get_people_id( connection, writer)
                    if people_id and movie_id:
                        add_crew_to_movie(connection, movie_id, people_id, commit_count)
                        commit_count += 1
                        rows_added += 1
                        if commit_count == 1000:
                            connection.commit()
                            print(f"{commit_count} crew members added to movies")
                            commit_count = 0


    except psycopg2.Error as e:
        connection.rollback()
        print("An error occurred during data processing:", e)
    else:
        connection.commit()

    print(str(rows_added) + " nieuwe rijen toegevoegd.")
    print(str(rows_processed) + " rijen totaal in database")
    end_time = datetime.now()
    duration = end_time - start_time
    print("Data ingalden via stream in " +  str(duration))

def add_crew_to_movie(connection, movie_id, people_id, commit_count):

    # Model Type instellen
    model_type = 'App\Models\Title'

    with connection.cursor() as cursor:
        cursor.execute("""
            INSERT INTO model_has_crew (model_type, model_id, people_id)
            VALUES (%s, %s, %s);
            """, (model_type, movie_id, people_id))

    print(f"{commit_count} added crew " + str(people_id) + " to movie " + str(movie_id))

def get_movie_id(connection, tconst):
   with connection.cursor() as cursor:
       # Query om film id op te halen aan de hand van de tconst
       cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s", (tconst,))
       result = cursor.fetchone()

       if result:
           db_title_id = result[0]
           return db_title_id

def get_people_id(connection, person_id):
   with connection.cursor() as cursor:
       # Query om people id op te halen aan de hand van de nmconst
       cursor.execute("SELECT id FROM people WHERE imdb_externid = %s", (person_id,))
       result = cursor.fetchone()

       if result:
           people_id = result[0]
           return people_id


def execute():
    connection = db.get_connection()
    load_crew(connection)

if __name__ == "__main__":
    execute()