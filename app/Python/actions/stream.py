import csv
import psycopg2
import requests
import zlib
import db_connector as db

def get_titles_start_row():
    conn = db.get_connection()
    with conn.cursor() as cursor:
        cursor.execute(f"""
        select COUNT(*) from titles
        """)
        start_row = cursor.fetchone()[0] + 1
        print("start_row is: ", start_row)
        return start_row

def get_start_row_name_basics():
    conn = db.get_connection()
    with conn.cursor() as cursor:
        cursor.execute(f"""
        SELECT COUNT(DISTINCT model_id) FROM model_has_crew;
        """)
        start_row = cursor.fetchone()[0]
        print("start_row is: ", start_row)
        return start_row


def fetch_header(path):
    try:
        if path:
            with open(path, 'rb') as file:
                header = file.readline().decode().strip()
                if header:
                    return header
                else:
                    print("Header not found in the file.")
                    return None
    except FileNotFoundError:
        print(f"File not found at path: {path}")
    except PermissionError:
        print(f"No permission to read the file at path: {path}")
    except Exception as e:
        print("Error occurred while fetching header:", e)
    return None


def fetch_file(file_path):
    try:
        with open(file_path, 'r', newline='', encoding='utf-8') as tsvfile:
            reader = csv.reader(tsvfile, delimiter='\t')
            for row in reader:
                yield row
    except Exception as e:
        print("Error occurred while reading TSV file:", e)

def fetch_file_from_row(file_path, start_from_row, conn):
    try:
        with open(file_path, 'r', newline='', encoding='utf-8') as tsvfile:
            reader = csv.reader(tsvfile, delimiter='\t')

            # Eerste rij ophalen en teruggeven
            first_row = next(reader)
            yield first_row

            # Doorgaan vanaf het opgegeven startpunt
            for _ in range(start_from_row - 1):
                next(reader)

            # Rijen ophalen vanaf het startpunt en teruggeven
            for row in reader:
                print(row)
                yield row
    except Exception as e:
        print("Error occurred while reading TSV file:", e)

def fetch_source(path, url):
    return fetch_file(path)

# def stream_all_gzip_content(url):
#     try:
#         with requests.get(url, stream=True) as response:
#             response.raise_for_status()  # Controleer op fouten bij het verkrijgen van de inhoud
#             decompressor = zlib.decompressobj(zlib.MAX_WBITS | 16)
#             buffer = ""  # Buffer om gedeeltelijke rijen op te slaan
#             for chunk in response.iter_content(chunk_size=1024):
#                 decompressed_chunk = decompressor.decompress(chunk)
#                 try:
#                     decoded_chunk = decompressed_chunk.decode('utf-8')
#                 except UnicodeDecodeError:
#                     decoded_chunk = decompressed_chunk.decode('latin-1', errors='ignore')
#                 lines = (buffer + decoded_chunk).split('\n')
#                 buffer = lines.pop()  # Laatste element is mogelijk een gedeeltelijke rij
#                 for line in lines:
#                     yield line
#     except requests.exceptions.RequestException as e:
#         print(f"Fout bij het streamen van de inhoud: {e}")

#
# def get_header_from_url(url):
#     with requests.get(url, stream=True) as response:
#         if response.status_code == 200:
#             decompressor = zlib.decompressobj(zlib.MAX_WBITS | 16)
#             buffer = b""  # Buffer om gedeeltelijke rijen op te slaan
#             for chunk in response.iter_content(chunk_size=1024):
#                 decompressed_chunk = decompressor.decompress(chunk)
#                 try:
#                     decoded_chunk = decompressed_chunk.decode('utf-8')
#                 except UnicodeDecodeError:
#                     decoded_chunk = decompressed_chunk.decode('latin-1', errors='ignore')
#                 lines = (buffer + decoded_chunk.encode()).split(b'\n')
#                 buffer = lines.pop(0)  # Eerste element is de header
#                 if buffer.strip():
#                     return buffer.decode()  # Retourneer de header
#         else:
#             print("Er is een fout opgetreden bij het downloaden van het bestand.")
#
#
# def fetch_from_url(url, start_row=0):
#       with requests.get(url, stream=True) as response:
#         if response.status_code == 200:
#             decompressor = zlib.decompressobj(zlib.MAX_WBITS | 16)
#             buffer = b""  # Buffer om gedeeltelijke rijen op te slaan
#             line_count = 0
#             byte_offset = 0  # Houd de byte-offset bij
#             for chunk in response.iter_content(chunk_size=1024):
#                 decompressed_chunk = decompressor.decompress(chunk)
#                 try:
#                     decoded_chunk = decompressed_chunk.decode('utf-8')
#                 except UnicodeDecodeError:
#                     decoded_chunk = decompressed_chunk.decode('latin-1', errors='ignore')
#                 lines = (buffer + decoded_chunk.encode()).split(b'\n')
#                 buffer = lines.pop()  # Laatste element is mogelijk een gedeeltelijke rij
#                 for line in lines:
#                     if line_count >= start_row:
#                         yield line.decode()  # Stuur de gedecodeerde rij
#                     line_count += 1
#                 byte_offset += len(chunk)
#             if buffer:  # Verwerk het resterende buffer als er nog gedeeltelijke rijen zijn
#                 if line_count >= start_row:
#                     yield buffer.decode()
#         else:
#             print("Er is een fout opgetreden bij het downloaden van het bestand.")
