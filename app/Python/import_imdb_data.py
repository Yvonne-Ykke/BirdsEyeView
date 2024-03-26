import import_scripts.import_alternate_titles as alternate_titles
import import_scripts.import_crew as crew
import import_scripts.import_name_basics as names
import import_scripts.import_ratings as ratings
import import_scripts.import_title_episodes as episodes
import import_scripts.import_title_principals as principals
import import_scripts.import_titles as titles


def main():
    # Eerst Titles draain
    # titles.execute()

    # Als Titles klaar is de volgende draaien eventueel in meerdere terminals
    # alternate_titles.execute()
    # ratings.execute()

    # Names moet je draaien voordat je crew of principals draait
    # names.execute()
    # crew.execute()
    principals.execute()
    ### episodes.execute()


if __name__ == "__main__":
    main()
