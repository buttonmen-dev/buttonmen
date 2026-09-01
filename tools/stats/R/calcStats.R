# Import mandatory dependencies at the top, so we can fail fast if
# they are not available
box::use(
  DBI,
  RMySQL,
  xtable,
  data.table,
  Matrix,
  jsonlite,
  htmltools
)

save_dir <- function() {
  # Choose target file save directory
  if (('unix' == .Platform$OS.type) && ('X11' == .Platform$GUI)) {
    # This is for R on our Ubuntu instances
    dir_path <- '/var/www/ui/stats/'
  } else {
    # Otherwise, use the local directory
    dir_path <- './'
  }

  return(dir_path)
}

connectToDatabase <- function() {
  if (('unix' == .Platform$OS.type) && ('RStudio' == .Platform$GUI)) {
    # Connect to database via socket on a Mac running MAMP
    db <- RMySQL$dbConnect(
      RMySQL$MySQL(),
      user = 'stats',
      password = '',
      dbname = 'buttonmen-stats',
      host = 'localhost',
      unix.sock = "/Applications/MAMP/tmp/mysql/mysql.sock"
    )
  } else {
    # Otherwise connect via Unix socket
    db <- RMySQL$dbConnect(
      RMySQL$MySQL(),
      user = 'root',
      dbname = 'buttonmen',
      unix.sock = "/var/run/mysqld/mysqld.sock"
    )
  }

  return(db)
}

queryButtonNames <- function(db) {
  # Submit query database for button names and IDs
  suppressWarnings(
    button.name.df <- DBI$dbGetQuery(
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

queryPlayerNames <- function(db) {
  # Submit query database for player names and IDs
  suppressWarnings(
    player.name.df <- DBI$dbGetQuery(
      db,
      '
      SELECT
      p.id AS player_id,
      p.name_ingame AS player_name
      FROM player p
      ORDER BY player_id
      '
    )
  )

  return(player.name.df)
}

queryButtonStats <- function(db) {
  # Submit query for button vs button data; mirror matches are flagged but not filtered here
  suppressWarnings(
    data.df <- DBI$dbGetQuery(
      db,
      '
        SELECT
          g.id AS game_id,
          g.n_target_wins,
          g.last_action_time,
          m.player_id,
          m.button_id,
          m.n_rounds_won,
          m.n_rounds_lost,
          p.name_ingame AS player_name,
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
        LEFT JOIN player AS p
          ON p.id = m.player_id
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
  stats.table <- xtable$xtable(
    button.summary.df.sorted,
    display = c('s', 's', 's', 's', 'f', 'd'),
    caption = paste0('Button stats generated on ', as.character(as.Date(max(data.df$last_action_time))))
  )
  names(stats.table) <- c('Button Name', 'Button Set Name', 'TL', 'Win %', '# Games Completed')

  return(stats.table)
}

calcButtonMatchupsPlayed <- function(data.df, button.names.df) {
  ngame <- nrow(data.df) / 2
  max.button <- max(button.names.df$alt_button_id)

  # Create matchup frequency matrix of games played
  freq.matrix <- buildFrequencyMatrix(
    data.df$alt_button_id[2*(1:ngame) - 1],
    data.df$alt_button_id[2*(1:ngame)],
    max.button
  )
  
  freq.matrix.df <- as.data.frame(freq.matrix, row.names = button.names.df$button_name)
  colnames(freq.matrix.df) <- button.names.df$button_name
  # Note that the col.names = NA is necessary to force an extra blank column name for the column of row names
  write.table(
    freq.matrix.df, 
    file = 'matchup_frequency.csv',
    col.names = NA,
    sep = ',', 
    quote = FALSE
  )
  
  freq.matrix[0 == freq.matrix] <- NA

  # Take log of frequency matrix and increase dynamic range
  log.freq.matrix.limited <- pmin(log2(freq.matrix), 5)

  # Create colour palette
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

buildFrequencyMatrix <- function(row_ids, col_ids, n) {
  dt <- data.table$data.table(row_id = row_ids, col_id = col_ids,
                               key = c('row_id', 'col_id'))
  freq.dt <- dt[, .N, by = eval(data.table$key(dt))]
  as.matrix(Matrix$sparseMatrix(
    i = freq.dt$row_id,
    j = freq.dt$col_id,
    x = freq.dt$N,
    dims = c(n, n)
  ))
}

buildColouredHtmlTable <- function(df, caption) {
  # Compute cell background colours from 'Win %' and '# games played' columns
  output.colour <- pmin(4, floor(df$'Win %' / 20))
  # use a special colour for fewer than 5 matchups
  output.colour[df$'# games played' < 5] <- 5

  colour.map <- c('0' = '#ff8888', '1' = '#ffcccc', '2' = '#ffffcc',
                  '3' = '#ccffcc', '4' = '#88ff88', '5' = '#8888ff')
  bg <- colour.map[as.character(output.colour)]

  col.names <- names(df)
  header <- paste0(
    '<thead><tr>',
    paste0('<th>', col.names, '</th>', collapse = ''),
    '</tr></thead>'
  )

  rows <- paste0(
    '<tr>',
    '<td>', htmltools$htmlEscape(as.character(df[[1]])), '</td>',
    '<td>', htmltools$htmlEscape(as.character(df[[2]])), '</td>',
    '<td style="background-color:', bg, '">', df[[3]], '</td>',
    '<td>', df[[4]], '</td>',
    '</tr>'
  )

  paste0(
    '<table border="1"><caption>', htmltools$htmlEscape(caption), '</caption>',
    header,
    '<tbody>', paste(rows, collapse = ''), '</tbody>',
    '</table>'
  )
}

calcButtonMatchupWinStats <- function(data.df, button.names.df) {
  # Populate a button matchup frequency matrix
  freq.matrix <- buildFrequencyMatrix(
    data.df$alt_button_id[data.df$did_win],
    data.df$alt_button_id[!data.df$did_win],
    max(button.names.df$alt_button_id)
  )

  # Calculate the total number of games played for each matchup
  n.games.matrix <- freq.matrix + t(freq.matrix)
  diag(n.games.matrix) <- diag(n.games.matrix) / 2

  # Create a data frame with unplayed matchups
  zero.idx <- which(n.games.matrix == 0, arr.ind = TRUE)
  unplayed.df <- data.frame(
    button1 = button.names.df$button_name[zero.idx[, 1]],
    button2 = button.names.df$button_name[zero.idx[, 2]]
  )
  write.csv(unplayed.df, file = 'unplayed_button_matchups.csv', row.names = FALSE)
  
  # Calculate the win percentage for each matchup
  win.percentage.matrix <- round(100 * freq.matrix / n.games.matrix, 2)
  diag(win.percentage.matrix) <- NA

  # Flatten the matrix out into a data frame with one matchup per row
  win.percentage.df <- data.frame(
    button.name = rep(button.names.df$button_name, each = nrow(button.names.df)),
    opponent.button.name = button.names.df$button_name,
    win.percentage = c(t(win.percentage.matrix)),
    n.games = c(t(n.games.matrix))
  )

  # Save data as CSV and JSON object
  write.table(
    setNames(win.percentage.df, c('b1', 'b2', 'wp', 'ng')),
    file = 'win_percentage_stats.csv',
    col.names = c('button_1', 'button_2', 'win_percentage', 'number_of_games'),
    row.names = FALSE,
    sep = ','
  )

  writeLines(
    jsonlite$toJSON(setNames(win.percentage.df, c('b1', 'b2', 'wp', 'ng')), pretty = TRUE, digits = 2),
    paste0(save_dir(), 'win_percentage_stats.json')
  )

  # Remove empty rows
  win.percentage.df <- win.percentage.df[!is.na(win.percentage.df$win.percentage),]
  names(win.percentage.df) <- c('Button Name', 'Opponent Button Name', 'Win %', '# games played')

  
  # Create HTML table of button matchup stats
  caption <- paste0('Button stats generated on ',
                    as.character(as.Date(max(data.df$last_action_time))),
                    ', only contains played matchups')

  return(buildColouredHtmlTable(win.percentage.df, caption))
}

calcPlayerMatchupWinStats <- function(data.df, player.names.df) {
  # Populate a player matchup frequency matrix
  freq.matrix <- buildFrequencyMatrix(
    data.df$player_id[data.df$did_win],
    data.df$player_id[!data.df$did_win],
    max(player.names.df$player_id)
  )

  # Calculate the total number of games played for each matchup
  n.games.matrix <- freq.matrix + t(freq.matrix)
  diag(n.games.matrix) <- diag(n.games.matrix) / 2

  # Calculate the win percentage for each matchup
  win.percentage.matrix <- 100 * freq.matrix / n.games.matrix
  diag(win.percentage.matrix) <- NA

  # Flatten the matrix out into a data frame with one matchup per row
  win.percentage.df <- data.frame(
    player.name = rep(player.names.df$player_name, each = nrow(player.names.df)),
    opponent.name = player.names.df$player_name,
    win.percentage = c(t(win.percentage.matrix)),
    n.games = c(t(n.games.matrix))
  )

  # Save data as JSON object
  # win.percentage.df.short <- win.percentage.df
  # colnames(win.percentage.df.short) <- c('b1', 'b2', 'wp', 'ng')
  # df.json <- jsonlite$toJSON(win.percentage.df.short, pretty = TRUE, digits = 2)
  # writeLines(df.json, paste0(save_dir(), 'win_percentage_stats.json'))

  # Remove empty rows
  win.percentage.df <- win.percentage.df[!is.na(win.percentage.df$win.percentage),]

  win.percentage.sorted.df <- win.percentage.df[order(win.percentage.df$player.name,
                                                      -win.percentage.df$n.games,
                                                      win.percentage.df$opponent.name),]
  win.percentage.sorted.df$win.percentage <- round(win.percentage.sorted.df$win.percentage, 2)
  names(win.percentage.sorted.df) <- c('Player Name', 'Opponent Name', 'Win %', '# games played')
  
  # Create HTML table of player matchup stats
  caption <- paste0('Player stats generated on ',
                    as.character(as.Date(max(data.df$last_action_time))),
                    ', only contains played matchups')

  return(buildColouredHtmlTable(win.percentage.sorted.df, caption))
}

generateHtmlFile <- function(html.table, fname) {
  # Save HTML table to file
  path <- paste0(save_dir(), fname)
  if (is.character(html.table)) {
    writeLines(html.table, path)
  } else {
    print(html.table, type = 'html', include.rownames = FALSE, file = path)
  }
}

runAll <- function() {
  db <- connectToDatabase()
  on.exit(RMySQL$dbDisconnect(db))
  button.names.df <- queryButtonNames(db)
  player.names.df <- queryPlayerNames(db)
  data.df <- queryButtonStats(db)

  data.df$alt_button_id <- button.names.df$alt_button_id[match(data.df$button_id, button.names.df$button_id)]

  generateHtmlFile(calcSingleButtonStats(data.df), 'button_stats.html')
  calcButtonMatchupsPlayed(data.df, button.names.df)
  generateHtmlFile(calcPlayerMatchupWinStats(data.df, player.names.df), 'player_matchup_stats.html')
  generateHtmlFile(calcButtonMatchupWinStats(data.df, button.names.df), 'button_matchup_stats.html')
}

runAll()
