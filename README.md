<h1> Install guide </h1>

> composer install

> npm install

> npm run build

> php artisan key:generate

> Setup env variables

> php artisan migrate

> php artisan shield:generate --all

> php artisan make:filament-user

> php artisan shield:super-admin --user=1

> cd scripts/R

> Rscript -e "renv::restore()"

# R database verbinding

> R versie 4.3.3 installeren en PATH aanpassen in de system variables

> In je lokale postgresql.conf bestand password_encryption veranderen naar md5

> In je lokale pg_hba.conf bestand alle METHOD aanpassen naar md5

> cmd --> psql -U postgres --> ALTER USER postgres WITH PASSWORD 'new-password';
                (Hierna kan je je wachtwoord weer terug veranderen als je wil)


# Python script gebruiken

## check op updates / database wijzigingen via terminal

> git pull

> php artisan migrate

## Installeer dependencies voor tsv inladen in de terminal

> pip install psycopg2

> pip install load_dotenv

> pip install requests

> pip install truncate

## Data op de juiste plek

Verplaats je tsv bestanden naar 'storage/app/public' in dit BirdEyeView project

## Path aanpassen

Pas onderin het importMovies.py-script het relatieve path aan naar jou eventuele folder en bestands naam:
relative_path_to_movie_data = 'storage/app/public/title.basics.tsv/data.tsv'

## voer het import script uit

> python app/Python/importMovies.py

