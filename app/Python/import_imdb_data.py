import import_scripts.import_titles as import_titles
import import_scripts.import_name_basics as import_names
import import_scripts.import_alternate_titles as import_alternate_titles
import import_scripts.import_title_episodes as import_episodes
import import_scripts.import_crew as import_crew

def main():

    import_titles.execute()
    import_names.execute()
    import_alternate_titles.execute()
    import_episodes.execute()
    import_crew.execute()

if __name__ == "__main__":
    main()
