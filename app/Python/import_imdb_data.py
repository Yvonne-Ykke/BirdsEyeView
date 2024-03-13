import import_scripts.import_titles as import_titles
import import_scripts.import_name_basics as import_names
import import_scripts.import_alternate_titles as import_alternate_titles

def main():
    import_titles.execute()
    import_names.execute()
    import_alternate_titles.execute()



if __name__ == "__main__":
    main()
