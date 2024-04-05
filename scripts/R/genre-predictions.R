# source('init.R')

# library('dbplyr')
# library('dplyr')


args <- commandArgs(trailingOnly = TRUE)

path <- args[1]
genre <- args[2]

setwd(path)

x <- rnorm(100)
y <- rnorm(100)
png(file = "randomtest.png")
plot(x, y, main = "Scatter Plot", xlab = "X-axis", ylab = "Y-axis")
dev.off()