# Drop and reinitialize schemas for all database tables

use buttonmen;

# Pagoda defaults to auto_increment of 4, which isn't what we want
set auto_increment_increment=1;

# Initialize schemas
source schema.button.sql;
source schema.player.sql;
source schema.game.sql;
