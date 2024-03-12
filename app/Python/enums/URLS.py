from enum import Enum

class URLS(Enum):
    BASE_URL = "https://datasets.imdbws.com/"
    NAME_BASICS = BASE_URL + "name.basics.tsv.gz"
    TITLE_AKAS = BASE_URL + "title.akas.tsv.gz"
    TITLE_BASICS = BASE_URL + "title.basics.tsv.gz"
    TITLE_CREW = BASE_URL + "title.crew.tsv.gz"
    TITLE_EPISODE = BASE_URL + "title.episode.tsv.gz"
    TITLE_PRINCIPALS = BASE_URL + "title.principals.tsv.gz"
    TITLE_RATINGS = BASE_URL + "title.ratings.tsv.gz"
