def get_person_id(connection, person_id):
    """
    Get people ID from the database based on person ID.

    Args:
    connection (psycopg2.extensions.connection): A connection to the database.
    person_id (str): Person ID.

    Returns:
    int: Internal people ID.
    """
    with connection.cursor() as cursor:
        # Query to get people id based on the nmconst
        cursor.execute("SELECT id FROM people WHERE imdb_externid = %s", (person_id,))
        result = cursor.fetchone()

        if result:
            people_id = result[0]
            return people_id
        else:
            print("Person is not inserted in people table")

def insert_person(conn, row):
      with conn.cursor() as cursor:
            # Query to get the people_id based on the title_id
            cursor.execute("""
                INSERT INTO people (imdb_externid, name, birth_year, death_year)
                VALUES (%s, %s, %s, %s)
                ON CONFLICT (imdb_externid) DO UPDATE
                SET death_year = EXCLUDED.death_year
                RETURNING id;
            """, (
            row['nconst'],
            row['primaryName'],
            row['birthYear'] if row['birthYear'] != '\\N' and row['birthYear'].isdigit() else None,
            row['deathYear'] if row['deathYear'] != '\\N' and row['deathYear'].isdigit() else None
            ))

            person_data = cursor.fetchone()
            if person_data:
                person_id = person_data[0]
                return person_id
            else:
                return None
