<h1> Install guide </h1>

> composer install

> npm install

> npm run build

> copy .env-example to .env

> php artisan key:generate

> Setup .env variables

    APP_NAME=BirdsEyeView
    APP_URL=https://birds-eye-view.test/

    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432 (or personal port)
    DB_DATABASE=birds_eye_view
    DB_USERNAME= < username >
    DB_PASSWORD= < password >

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

Graag een kleinere dataset met alleen films? Skip naar tmdb kopje

### check op updates / database wijzigingen via terminal

> git pull

> php artisan migrate

### Installeer dependencies voor tsv inladen in de terminal

> pip install psycopg2

> pip install load_dotenv

> pip install requests

> pip install truncate

### Data op de juiste plek

> php artisan app:download-imdb-files

### voer het import script uit

> python app/Python/importMovies.py

# Tmdb

### Queue driver instellen

> in .ENV de setting QUEUE_CONNECTION=database

> in terminal: php artisan queue:work

### data importeren

#### Gelimiteerd aantal film data
> php artisan app:import-movies-from-tmdb --recordsToImport= < aantal records te importeren >, moet groter zijn dan 100

#### Alle data
> php artisan app:import-movies-from-tmdb-files


Alleen geïnteresseerd in films of production companies?

> php artisan app:import-movies-from-tmdb-files --only=movie

> php artisan app:import-movies-from-tmdb-files --only=company


### Alle gewenste data geïmporteerd?

> php artisan app:set-database-indexes

### Toch niet? Even indexes weghalen voor snellere import.

> php artisan app:drop-database-indexes

### Grafieken alvast cachen?

> php artisan cache:charts
