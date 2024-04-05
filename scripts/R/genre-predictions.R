# source('init.R')

# library('dbplyr')
# library('dplyr')

# x <- c(1, 2, 3, 4, 5)
# y <- c(2, 3, 5, 4, 6)

# plot(x, y)

# png("scatter_plot.png")
# Sys.sleep(2)
# dev.off()

x <- rnorm(100)
y <- rnorm(100)
png(file = "randomtest.png")
plot(x, y, main = "Scatter Plot", xlab = "X-axis", ylab = "Y-axis")
dev.off()