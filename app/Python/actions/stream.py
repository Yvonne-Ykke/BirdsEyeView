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
#                     line_count += 1
#                     if line_count == 1000:
#                         return
        else:
            print("Er is een fout opgetreden bij het downloaden van het bestand.")

