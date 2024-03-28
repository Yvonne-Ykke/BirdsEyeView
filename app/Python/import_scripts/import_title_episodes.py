from datetime import datetime
import psycopg2
import db_connector as db
import actions.stream as stream
from enums.URLS import URLS
from enums.PATHS import PATHS
import os, sys

current_dir = os.path.dirname(os.path.abspath(__file__))
parent_dir = os.path.dirname(current_dir)
sys.path.append(parent_dir)
import repositories.people_repository as people_repository
import repositories.title_repository as title_repository

def import_serie_with_episodes(conn, row, serie_id, episode_id, rows_processed):
    with conn.cursor() as cursor:
        cursor.execute("""
            INSERT INTO title_episodes (parent_title_id, title_id, season_number, episode_number)
            VALUES (%s, %s, %s, %s)
            ON CONFLICT (parent_title_id, title_id) DO NOTHING
            RETURNING id;
        """, (
            serie_id,
            episode_id,
            row['seasonNumber'] if row['seasonNumber'] != '\\N' else None,
            row['episodeNumber'] if row['episodeNumber'] != '\\N' else None,
        ))
        if cursor.fetchone and row['seasonNumber'] != '\\N':
            print(f"{rows_processed} Loaded episode {serie_id}: S{row['seasonNumber']}E{row['episodeNumber']}")


def load_episodes(conn):
    start_time = datetime.now()
    rows_added = 0
    commit_count = 0
    genres_with_ids = {}
    # Set data source
    url = URLS.TITLE_EPISODE.value
    path = PATHS.TITLE_EPISODE.value

    data_source = stream.fetch_source(path, url)

    print("Start laden van episodedata...")
    try:
        for rows_processed, line in enumerate(data_source):
            if rows_processed == 0:
                COLUMN_NAMES = line
                continue

            row = dict(zip(COLUMN_NAMES, line))

            # serie en episode id ophalen
            serie_id = title_repository.get_title_id(row['parentTconst'], conn)
            episode_id = title_repository.get_title_id(row['tconst'], conn)

            if serie_id and episode_id:
                import_serie_with_episodes(conn, row, serie_id, episode_id, rows_processed)
            else:
                print("Serie bestaat niet en is overgeslagen.")

            commit_count += 1
            if commit_count == 1000:
                conn.commit()
                print("1000 afleveringen geïmporteerd")
                commit_count = 0
    except KeyboardInterrupt:
        print("Proces onderbroken door gebruiker")
    except psycopg2.Error as e:
        conn.rollback()
        print("Er is een fout opgetreden tijdens het verwerken van de gegevens:", e)
    else:
        conn.commit()

    print(f"Totaal {rows_added} nieuwe rijen toegevoegd.")



def execute():
    connection = db.get_connection()
    load_episodes(connection)

if __name__ == "__main__":
    execute()
