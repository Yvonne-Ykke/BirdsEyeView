import csv
import psycopg2
import requests
import zlib


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
        else:
            print("Er is een fout opgetreden bij het downloaden van het bestand.")


def stream_gzip_content(url, conn, start_row=0):
    with conn.cursor() as cursor:
        cursor.execute("""
        select COUNT(*) from titles
        """)
        start_row = cursor.fetchone()[0] + 1
        print("start_row is: ", start_row)

    with requests.get(url, stream=True) as response:
        if response.status_code == 200:
            decompressor = zlib.decompressobj(zlib.MAX_WBITS | 16)
            buffer = b""  # Buffer om gedeeltelijke rijen op te slaan
            line_count = 0
            byte_offset = 0  # Houd de byte-offset bij
            for chunk in response.iter_content(chunk_size=1024):
                decompressed_chunk = decompressor.decompress(chunk)
                try:
                    decoded_chunk = decompressed_chunk.decode('utf-8')
                except UnicodeDecodeError:
                    decoded_chunk = decompressed_chunk.decode('latin-1', errors='ignore')
                lines = (buffer + decoded_chunk.encode()).split(b'\n')
                buffer = lines.pop()  # Laatste element is mogelijk een gedeeltelijke rij
                for line in lines:
                    if line_count >= start_row:
                        yield line.decode()  # Stuur de gedecodeerde rij
                    line_count += 1
                byte_offset += len(chunk)
            if buffer:  # Verwerk het resterende buffer als er nog gedeeltelijke rijen zijn
                if line_count >= start_row:
                    yield buffer.decode()
        else:
            print("Er is een fout opgetreden bij het downloaden van het bestand.")

def get_header(url):
    with requests.get(url, stream=True) as response:
        if response.status_code == 200:
            decompressor = zlib.decompressobj(zlib.MAX_WBITS | 16)
            buffer = b""  # Buffer om gedeeltelijke rijen op te slaan
            for chunk in response.iter_content(chunk_size=1024):
                decompressed_chunk = decompressor.decompress(chunk)
                try:
                    decoded_chunk = decompressed_chunk.decode('utf-8')
                except UnicodeDecodeError:
                    decoded_chunk = decompressed_chunk.decode('latin-1', errors='ignore')
                lines = (buffer + decoded_chunk.encode()).split(b'\n')
                buffer = lines.pop(0)  # Eerste element is de header
                if buffer.strip():

                    return buffer.decode()  # Retourneer de header
        else:
            print("Er is een fout opgetreden bij het downloaden van het bestand.")

