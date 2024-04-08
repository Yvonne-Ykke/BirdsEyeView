setwd("../scripts/R")

library('dbplyr')
library('dplyr')

source('init.R')

args <- commandArgs(trailingOnly = TRUE)

path <- args[1]
genre <- args[2]
year_from <- args[3]

con <- get_db()

sql_query <- "SELECT start_year AS x, CAST(SUM(revenue - budget) / 1000000 AS DECIMAL(16)) AS y
              FROM titles
              JOIN title_genres ON titles.id = title_genres.title_id
              WHERE title_genres.genre_id = ?genre
                AND revenue > 0
                AND budget > 0
                GROUP BY x;"


result <- dbGetQuery(con, sqlInterpolate(con, sql_query, genre = genre))

filtered_result <- result[result$x >= year_from, ]

lm_model <- lm(y ~ x, data = filtered_result)

png(file = paste0(path, "/profit_over_time-",genre,"-",year_from,".png"), width = 800, height = 600)

plot(filtered_result$x, filtered_result$y, type = "l", xlab = "Jaar", ylab = "Winst (in milioenen)",
     main = paste("Genre winst tijdlijn"))

abline(lm_model, col = "red")

dev.off()

break
