DROP TABLE IF EXISTS game_option_map;
CREATE TABLE game_option_map (
    game_id        MEDIUMINT UNSIGNED NOT NULL,
    player_id      SMALLINT UNSIGNED NOT NULL,
    die_idx        INT UNSIGNED NOT NULL,
    option_value   TINYINT UNSIGNED,
    is_expired     BOOLEAN DEFAULT FALSE
);

ALTER TABLE die DROP COLUMN chosen_max;

# add is_expired columns to specify whether swing/option values are expired

ALTER TABLE game_swing_map ADD is_expired BOOLEAN DEFAULT FALSE;

