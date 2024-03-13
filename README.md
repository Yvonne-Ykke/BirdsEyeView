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

# Python script gebruiken

## check op updates / database wijzigingen via terminal
git pull
php artisan migrate

## Installeer dependencies voor tsv inladen in de terminal

pip install psycopg2
pip install load_dotenv
pip install requests

## Data op de juiste plek
verplaats je tsv bestanden naar 'storage/app/public' in dit BirdEyeView project

## Path aanpassen
pas onderin het importMovies.py-script het relatieve path aan naar jou eventuele folder en bestands naam:
relative_path_to_movie_data = 'storage/app/public/title.basics.tsv/data.tsv'

## voer het import script uit
python app/Python/importMovies.py
