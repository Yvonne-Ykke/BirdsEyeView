import import_scripts.import_titles as import_titles
import import_scripts.import_name_basics as import_names
import import_scripts.import_crew as import_crew

def main():

    import_titles.execute()
    import_names.execute()
    import_crew.execute()

if __name__ == "__main__":
    main()
