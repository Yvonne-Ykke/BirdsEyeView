# chooseCRANmirror(ind=34)
# install.packages(c("DBI", "RODBC", "odbc", "dplyr", "dbplyr", "dotenv", "RPostgreSQL"))

library("RPostgreSQL")
library("dotenv")
library("dbplyr")
library("dplyr")
library("RODBC")
library("odbc")
library("DBI")

dotenv::load_dot_env("../../.env")

## Include additional environment variables into PATH
## PDFLATEX_PATH should be declared in the .env file, and should point to the installed location of pdflatex (found using `which pdflatex` on a unix server)
#Sys.setenv(PATH=paste0(
# Sys.getenv('PDFLATEX_PATH'),
#':',
#     Sys.getenv('PATH')
#)
#)

get_db <- function() {
  return(dbConnect(RPostgreSQL::PostgreSQL(),
    host = Sys.getenv("DB_HOST"),
    dbname = Sys.getenv("DB_DATABASE"),
    user = Sys.getenv("DB_USERNAME"),
    password = Sys.getenv("DB_PASSWORD"),
    port = as.integer(Sys.getenv("DB_PORT"))
  ))
}
