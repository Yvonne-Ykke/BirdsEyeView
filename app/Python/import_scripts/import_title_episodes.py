from datetime import datetime
import psycopg2
import db_connector as db
import actions.stream as stream
from enums.URLS import URLS
from enums.PATHS import PATHS

def get_parent_title_id(imdb_extern_id, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s;", (imdb_extern_id,))
        result = cursor.fetchone()
        if result:
            return result[0]
        return None


def check_episode_exists(episode_imdb_externid, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM title_episodes WHERE imdb_externid = %s;",
                       (episode_imdb_externid,))
        result = cursor.fetchone()
        if result:
            print(f"Skipping already imported episode: {episode_imdb_externid}")
            return True
        return False


def load_episodes(conn):
    start_time = datetime.now()

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

            insert_episode(conn, row)

            with conn.cursor() as cursor:
                cursor.execute("""
                    INSERT INTO title_episodes (title_id, imdb_externid, season_number, episode_number)
                    VALUES (%s, %s, %s, %s)
                    ON CONFLICT (title_id) DO NOTHING
                    RETURNING id;
                """, (
                    title_id,
                    row['tconst'],
                    row['seasonNumber'] if row['seasonNumber'] != '\\N' else None,
                    row['episodeNumber'] if row['episodeNumber'] != '\\N' else None,
                ))

            title_id = get_parent_title_id(row['parentTconst'], conn)
            if title_id is None:
                print(f"Titel niet gevonden voor rij {rows_processed}: {row['parentTconst']}, probeer eerst de titles tabel in te laden")
                continue

            if check_episode_exists(row['tconst'], conn):
                print(f"Aflevering reeds geïmporteerd: {row['tconst']}")
                continue


                print(f"Nieuwe aflevering geladen: {row['tconst']}")
                result = cursor.fetchone()
                if result is not None:
                    rows_added += 1

                commit_count += 1
                if commit_count == 5000:
                    conn.commit()
                    print("5000 afleveringen geïmporteerd")
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
