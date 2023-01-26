# Reset data in all buttonmen databases

# Pagoda defaults to auto_increment of 4, which isn't what we want
set auto_increment_increment=1;

source data.config.sql;
source data.button.sql;
source data.game.sql;
source data.player.sql;
source data.tournament.sql;
source data.stats.sql;
source data.forum.sql;
