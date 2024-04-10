setwd("../scripts/R")

library('dbplyr')
library('dplyr')

suppressPackageStartupMessages(library(dplyr))
suppressPackageStartupMessages(library(dbplyr))
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

max_year <- dbGetQuery(con, "SELECT MAX(start_year) FROM titles WHERE budget > 0 AND revenue > 0")
year <- as.numeric(max_year[[1]])
future_years <- data.frame(x = seq(year + 1, year + 5))

result <- dbGetQuery(con, sqlInterpolate(con, sql_query, genre = genre))

filtered_result <- result[result$x >= year_from, ]

lm_model <- lm(y ~ x, data = filtered_result)
predicted_values <- predict(lm_model, newdata = future_years)

x_min <- min(filtered_result$x)
x_max <- max(c(filtered_result$x, future_years$x))
xlim <- c(x_min, x_max)

y_min <- min(c(filtered_result$y, predicted_values))
y_max <- max(c(filtered_result$y, predicted_values))
ylim <- c(y_min, y_max)

png(file = paste0(path, "/profit_over_time-",genre,"-",year_from,".png"), width = 800, height = 600)

plot(filtered_result$x, filtered_result$y, type = "l", xlab = "Jaar", ylab = "Winst (in milioenen)",
     main = paste("Genre winst tijdlijn"), xlim = xlim, ylim = ylim)

abline(lm_model, col = "red")

legend("topright", legend = c("Originele Data", "Trendlijn"),
       col = c("black", "red"), lty = c(1, 1), pch = c(NA, NA))

dev.off()

break
