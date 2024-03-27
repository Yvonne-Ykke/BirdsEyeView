def get_title_id(imdb_extern_id, conn):
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM titles WHERE imdb_externid = %s;", (imdb_extern_id,))
        result = cursor.fetchone()
        if result:
            return result[0]
        return None
