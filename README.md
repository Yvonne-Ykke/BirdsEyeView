### Voor laravel directory structuur, zie einde bestand

<h1> Install guide </h1>

#### Requirements

* PHP 8.1 or higher
* Composer
* Nodejs 18 or higher
* Pgsql
* R 4.3.3
* Python 3
* Localhost like Laravel Valet

### setup

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
 
> php artisan storage:link

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

//TODO: Deze werkt nog niet
> php artisan cache:widget-charts

### Tabellen alvast cachen?

> php artisan cache:widget-tables


## Laravel directory guide

Lieve lezer, omdat we gebruik maken van de frameworks Laravel en Filamentphp hebben we nogal wat folders met code in dit project. Hieronder even een kort overzicht waar wat staat.
Hier staan alleen de folders in de relevant zijn tot dit project.

### app
De belangrijkste folder met alle business logica. Hieronder sub-directories

#### Api
Alle code die relevant is voor het gebruiken van api's om data mee op te halen

#### Console
Alle door ons gemaakte artisan commands <br>
Kernel staat een scheduler (soort Crontab) in. Hier worden import scripts maandelijks uitgevoerd.

#### Filament
Alles wat te maken heeft met Dashboard interface, hier staan alle dashboards, widgets en resources (overzichts paginas)

#### Jobs
Alle asynchroon uitvoerbare jobs die door de queue driver afgehandeld kunnen worden.

#### Models
Models zijn objecten die te zien zijn als 1 record van een database tabel. 
Voorbeeld: genres db tabel heeft de Model Genre. In deze models staan onderlinge relaties met andere models aangegeven
en zijn ze te gebruiken om de database aan te spreken via Eloquent ORM vanuit code.

#### Policies
Policies controleren of een gebruiker de rechten heeft om een model event (bijvoorbeeld: create, update, read delete) uit te voeren.

#### Python
Alle python scripts die we gebruiken om imdb data te importeren

#### Support
Algemene support modules zoals applicatie wijde Enums of Action classes.

### Bootstrap
Niet relevant

### config
Niet relevant

### database
Migraties: code om database aan te maken / aan te passen <br>
Factories: niet relevant
Seeders: niet relevant

### lang
Vertaling bestanden, dit zijn veelal gepublishte bestanden vanuit packages. Niet relevant

### node_modules
Alle javascript packages, niet relevant

### public
Niet relevant

### resources
Frontend gerelateerde code

### routes
Niet relevant

### scripts
Alle externe scripts die we gebruiken, in dit geval R

### storage
Alle bestanden die gegenereerd worden zoals imdb en tmdb import bestanden of afbeeldingen die gegenereerd worden door R

### tests
Niet relevant

### vendor
Alle door composer geïnstalleerde packages 
