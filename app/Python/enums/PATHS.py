import os
from enum import Enum

current_working_dir = os.getcwd()
print("Current working directory:", current_working_dir)

DATA_FOLDER = current_working_dir + "\\storage\\app\public\\"

class PATHS(Enum):
    NAME_BASICS = DATA_FOLDER + "name.basics.tsv"
    TITLE_AKAS = DATA_FOLDER + "title.akas.tsv"
    TITLE_BASICS = DATA_FOLDER + "title.basics.tsv"
    TITLE_CREW = DATA_FOLDER + "title.crew.tsv"
    TITLE_EPISODE = DATA_FOLDER + "title.episode.tsv"
    TITLE_PRINCIPALS = DATA_FOLDER + "title.principals.tsv"
    TITLE_RATINGS = DATA_FOLDER + "title.ratings.tsv"
