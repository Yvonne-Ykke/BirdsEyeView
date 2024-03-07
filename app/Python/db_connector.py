import psycopg2
from dotenv import load_dotenv
import os

def get_connection():
    """
    Connects to the database using environment variables.

    Returns:
    psycopg2.extensions.connection: A connection to the database.
    """
    # Get the environment variables
    load_dotenv()
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
        print("Connectie met database.")

        return connection
    except Exception as e:
        print("Error connecting to the database:", e)
