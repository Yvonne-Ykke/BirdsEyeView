import import_scripts.import_alternate_titles as alternate_titles
import import_scripts.import_crew as crew
import import_scripts.import_name_basics as names
import import_scripts.import_ratings as ratings
import import_scripts.import_title_episodes as episodes
import import_scripts.import_title_principals as principals
import import_scripts.import_titles as titles


def main():
    # titles.execute()
    names.execute()
    # alternate_titles.execute()
    # episodes.execute()  # ERROR
    # crew.execute()
    # ratings.execute()
    # principals.execute()


if __name__ == "__main__":
    main()
