save_dir <- function() {
  # Choose target file save directory
  if (('unix' == .Platform$OS.type) && ('X11' == .Platform$GUI)) {
    # This is for R on our Ubuntu instances
    dir_path <- '/var/www/ui/stats/'
  } else {
    # Otherwise, use the local directory
    dir_path <- './'
  }

  return(dir_path);
}

connectToDatabase <- function() {
  library(RMySQL)

  if (('unix' == .Platform$OS.type) && ('RStudio' == .Platform$GUI)) {
    # Connect to database via socket on a Mac running MAMP
    db <- dbConnect(
      MySQL(),
      user='stats',
      password='',
      dbname='buttonmen-stats',
      host='localhost',
      unix.sock = "/Applications/MAMP/tmp/mysql/mysql.sock"
    )
  } else {
    # Otherwise connect via TCP/IP
    db <- dbConnect(
      MySQL(),
      user = 'stats',
      password = '',
      dbname = 'buttonmen-stats',
      host = '127.0.0.1'
    )
  }

  return(db)
}

queryButtonNames <- function(db) {
  # Submit query database for button names and IDs
  suppressWarnings(
    button.name.df <- dbGetQuery(
      db,
      '
        SELECT
          b.id AS button_id,
          b.name AS button_name
        FROM button b
        LEFT JOIN buttonset bs
        ON bs.id = b.set_id
        ORDER BY bs.id, button_name
      '
    )
  )

  # Add compressed alternate button IDs
  button.name.df$alt_button_id <- 1:nrow(button.name.df)

  return(button.name.df)
}

queryButtonStats <- function(db) {
  # Submit query for button vs button data, ignoring mirror matches
  suppressWarnings(
    data.df <- dbGetQuery(
      db,
      '
        SELECT
          g.id AS game_id,
          g.n_target_wins,
          m.button_id,
          m.n_rounds_won,
          m.n_rounds_lost,
          b.name AS button_name,
          b.tourn_legal,
          (m.n_rounds_won = g.n_target_wins) AS did_win,
          bs.name AS button_set_name,
          (
            1 = (
              SELECT COUNT(DISTINCT m2.button_id)
              FROM game_player_map AS m2
              WHERE m2.game_id = g.id
            )
          ) AS is_mirror_match
        FROM game AS g
        LEFT JOIN game_player_map AS m
          ON g.id = m.game_id
        LEFT JOIN button AS b
          ON b.id = m.button_id
        LEFT JOIN buttonset AS bs
          ON bs.id = b.set_id
        WHERE (
          (g.n_target_wins = m.n_rounds_won) OR
          (g.n_target_wins = m.n_rounds_lost)
        )
        ORDER BY game_id, bs.id, button_name
      '
    )
  )

  # Recode certain columns
  data.df$did_win <- (1 == data.df$did_win)
  data.df$tourn_legal[1 == data.df$tourn_legal] <- 'Y'
  data.df$tourn_legal[0 == data.df$tourn_legal] <- '-'
  data.df$is_mirror_match <- (1 == data.df$is_mirror_match)

  return(data.df)
}

calcSingleButtonStats <- function(data.df) {
  data.df.no.mirror <- data.df[!data.df$is_mirror_match,]

  # Calculate summary statistics
  button.summary.df <- do.call(
    data.frame,
    aggregate(
      did_win ~ button_id + button_name + button_set_name + tourn_legal,
      FUN = function(y) {
        c(
          win.percentage = 100 * sum(y) / length(y),
          n_games_completed = length(y)
        )
      },
      data = data.df.no.mirror
    )
  )
  names(button.summary.df) <- c('button_id', 'button_name', 'button_set_name', 'tourn_legal', 'win_percentage', 'n_games_completed')

  # Sort data frame by win percentage and number of completed games in descending order
  button.summary.df.sorted <- button.summary.df[order(-button.summary.df$win_percentage, -button.summary.df$n_games_completed), ]

  # Remove button ID column
  button.summary.df.sorted$button_id <- NULL
  
  # Create HTML table of button stats
  library(xtable)
  stats.table <- xtable(
    button.summary.df.sorted,
    display = c('s', 's', 's', 's', 'f', 'd'),
    caption = 'Button stats generated on 2017-07-07'
  )
  names(stats.table) <- c('Button Name', 'Button Set Name', 'TL', 'Win %', '# Games Completed')

  return(stats.table)
}

calcButtonMatchupsPlayed <- function(data.df, button.names.df) {
  ngame <- nrow(data.df) / 2
  max.button <- max(button.names.df$alt_button_id)

  # Create data frame with button involved in each game
  games.played.df <- data.frame(
    first_button_id = data.df$alt_button_id[2*(1:ngame) - 1],
    second_button_id = data.df$alt_button_id[2*(1:ngame)]
  )

  # Create matchup frequency matrix of games played
  library(data.table)
  library(Matrix)
  dt <- data.table(games.played.df, key = c('first_button_id', 'second_button_id'))
  freq.dt <- dt[, .N, by = key(dt)]
  freq.matrix <- as.matrix(with(freq.dt, sparseMatrix(i = first_button_id, j = second_button_id, x = N, dims = c(max.button, max.button))))
  freq.matrix[0 == freq.matrix] <- NA

  # Take log of frequency matrix and increase dynamic range
  log.freq.matrix <- log2(freq.matrix)
  log.freq.matrix.limited <- log.freq.matrix
  upper.limit <- 5
  log.freq.matrix.limited[log.freq.matrix.limited > upper.limit] <- upper.limit

  # Create colour palette
  library(colorRamps)
  color.palette <- colorRampPalette(c('grey', 'red'))

  # Generate graphical representation of matchup matrix
  png(
    filename = paste0(save_dir(), 'games_played.png'),
    units = "cm",
    res = 300,
    height = 100,
    width = 100,
    pointsize = 12
  )
  image(
    x = 1:max.button,
    y = 1:max.button,
    z = t(apply(log.freq.matrix.limited, 2, rev)),
    col = color.palette(256),
    axes = FALSE,
    xlab = '',
    ylab = ''
  )
  # add text to top edge
  text(cex = 0.3, x = 1:max.button, y = max.button + 1, labels = button.names.df$button_name, xpd = TRUE, srt = 90, adj = 0)
  # add text to bottom edge
  text(cex = 0.3, x = 1:max.button, y = -1, labels = button.names.df$button_name, xpd = TRUE, srt = 90, adj = 1)
  # add text to right edge
  text(cex = 0.3, x = max.button + 1, y = max.button:1, labels = button.names.df$button_name, xpd = TRUE, adj = 0)
  # add text to left edge
  text(cex = 0.3, x = -1, y = max.button:1, labels = button.names.df$button_name, xpd = TRUE, adj = 1)
  dev.off()
}

calcButtonMatchupWinStats <- function(data.df, button.names.df) {
  # Create matchup matrix with win/loss info
  game.winner.df <- data.frame(
    winner_button_id = data.df$alt_button_id[data.df$did_win],
    winner_button_name = data.df$button_name[data.df$did_win],
    loser_button_id = data.df$alt_button_id[!data.df$did_win],
    loser_button_name = data.df$button_name[!data.df$did_win]
  )

  library(data.table)
  library(Matrix)

  # Populate a button matchup frequency matrix
  dt <- data.table(game.winner.df, key = c('winner_button_id', 'loser_button_id'))
  freq.dt <- dt[, .N, by = key(dt)]
  freq.matrix <- as.matrix(with(freq.dt, sparseMatrix(i = winner_button_id, j = loser_button_id, x = N)))

  # Calculate the total number of games played for each matchup
  n.games.matrix <- freq.matrix + t(freq.matrix)
  n.games.matrix[row(n.games.matrix) == col(n.games.matrix)] <- n.games.matrix[row(n.games.matrix) == col(n.games.matrix)] / 2

  # Calculate the win percentage for each matchup
  win.percentage.matrix <- 100 * freq.matrix / n.games.matrix
  win.percentage.matrix[row(win.percentage.matrix) == col(win.percentage.matrix)] <- NA

  # Flatten the matrix out into a data frame with one matchup per row
  win.percentage.df <- data.frame(
    button.name = rep(button.names.df$button_name, each = nrow(button.names.df)),
    opponent.button.name = button.names.df$button_name,
    win.percentage = c(t(win.percentage.matrix)),
    n.games = c(t(n.games.matrix))
  )

  # Save data as JSON object
  library(jsonlite)
  win.percentage.df.short <- win.percentage.df
  colnames(win.percentage.df.short) <- c('b1', 'b2', 'wp', 'ng')
  df.json <- toJSON(win.percentage.df.short, pretty = TRUE, digits = 2)
  writeLines(df.json, paste0(save_dir(), 'win_percentage_stats.json'))
  
  # Remove empty rows
  win.percentage.df <- win.percentage.df[!is.na(win.percentage.df$win.percentage),]
  
  # Create HTML table of button matchup stats
  library(xtable)
  stats.table <- xtable(
    win.percentage.df,
    display = c('s', 's', 's', 'f', 'd'),
    caption = 'Button stats generated on 2017-07-07, only contains matchups that have been played'
  )
  names(stats.table) <- c('Button Name', 'Opponent Button Name', 'Win %', '# games played')

  return(stats.table)
}

generateHtmlFile <- function(html.table, fname) {
  # Save HTML table to file
  print(
    html.table,
    type = 'html',
    include.rownames = FALSE,
    file = paste0(save_dir(), fname)
  )
}

runAll <- function() {
  db <- connectToDatabase()
  button.names.df <- queryButtonNames(db)
  data.df <- queryButtonStats(db)
  dbDisconnect(db)

  data.df$alt_button_id <- button.names.df$alt_button_id[match(data.df$button_id, button.names.df$button_id)]

  generateHtmlFile(calcSingleButtonStats(data.df), 'button_stats.html')
  calcButtonMatchupsPlayed(data.df, button.names.df)
  generateHtmlFile(calcButtonMatchupWinStats(data.df, button.names.df), 'button_matchup_stats.html')
}

# runAll()
