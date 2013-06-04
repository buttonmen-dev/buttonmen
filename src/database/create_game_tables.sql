DROP VIEW  IF EXISTS game_player_view, open_game_possible_button_view;
DROP TABLE IF EXISTS game_details,
                     game_player_map,
                     die_details,
                     open_game_possible_buttons,
                     open_game_possible_button_sets,
                     tournament_details;

CREATE TABLE game_details (
    id                 MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    last_action_time   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status             ENUM ('OPEN', 'ACTIVE', 'COMPLETE') NOT NULL,
    game_state         TINYINT UNSIGNED DEFAULT 10,
    n_players          TINYINT UNSIGNED DEFAULT 2,
    round_number       TINYINT UNSIGNED DEFAULT 0,
    n_target_wins      TINYINT UNSIGNED NOT NULL,
    n_recent_draws     TINYINT UNSIGNED DEFAULT 0,
    n_recent_passes    TINYINT UNSIGNED DEFAULT 0,
    creator_id         SMALLINT UNSIGNED NOT NULL,
    current_player_id  SMALLINT UNSIGNED,
    last_winner_id     SMALLINT UNSIGNED,
    tournament_id      SMALLINT UNSIGNED,
    description        VARCHAR(255) NOT NULL,
    chat               TEXT
);

CREATE TABLE game_player_map (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    player_id          SMALLINT UNSIGNED NOT NULL,
    button_id          SMALLINT UNSIGNED,
    position           TINYINT UNSIGNED NOT NULL,
    did_win_initiative BOOLEAN DEFAULT FALSE,
    is_awaiting_action BOOLEAN DEFAULT FALSE,
    n_rounds_won       TINYINT UNSIGNED DEFAULT 0,
    n_rounds_drawn     TINYINT UNSIGNED DEFAULT 0,
    handicap           TINYINT UNSIGNED DEFAULT 0,
    is_player_hidden   BOOLEAN DEFAULT FALSE
);

CREATE TABLE die_details (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id           TINYINT UNSIGNED NOT NULL,
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    status             ENUM ('NORMAL', 'CAPTURED', 'DISABLED', 'OUT_OF_GAME') DEFAULT 'NORMAL',
    recipe             VARCHAR(20) NOT NULL,
    swing_value        TINYINT UNSIGNED,
    position           TINYINT UNSIGNED NOT NULL,
    value              SMALLINT
);

CREATE TABLE open_game_possible_buttons (
    game_id            MEDIUMINT UNSIGNED PRIMARY KEY,
    button_id          SMALLINT UNSIGNED NOT NULL
);

CREATE TABLE open_game_possible_button_sets (
    game_id            MEDIUMINT UNSIGNED PRIMARY KEY,
    set_id             SMALLINT UNSIGNED NOT NULL
);

CREATE TABLE last_attack (
    game_id            MEDIUMINT UNSIGNED PRIMARY KEY,
    attacker_id        SMALLINT UNSIGNED NOT NULL,
    defender_id        SMALLINT UNSIGNED,
    /* "Japanese Beetle" has 15 characters */
    attack_type        VARCHAR(20) NOT NULL
);

CREATE TABLE last_attack_die_map (
   game_id             MEDIUMINT UNSIGNED PRIMARY KEY,
   die_id              INT UNSIGNED NOT NULL,
   is_attacker         BOOLEAN NOT NULL,
   did_reroll          BOOLEAN NOT NULL,
   was_captured        BOOLEAN NOT NULL
);

CREATE TABLE tournament_details (
    id                 SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status             ENUM ('OPEN', 'ACTIVE', 'COMPLETE') NOT NULL,
    current_round      TINYINT UNSIGNED DEFAULT 1,
    n_players          TINYINT UNSIGNED NOT NULL,
    n_target_wins      TINYINT UNSIGNED NOT NULL,
    is_double_elim     BOOLEAN NOT NULL,
    creator_id         SMALLINT UNSIGNED NOT NULL,
    description        VARCHAR(255) NOT NULL
);

CREATE VIEW game_player_view
AS SELECT m.*, p.name_ingame AS player_name, b.name AS button_name
FROM game_player_map AS m
LEFT JOIN player_info AS p
ON m.player_id = p.id
LEFT JOIN button_definitions AS b
ON m.button_id = b.id;

CREATE VIEW open_game_possible_button_view
AS SELECT g.id, pb.button_id, ps.set_id, b.name AS button_name, s.name AS set_name
FROM game_details AS g
LEFT JOIN open_game_possible_buttons AS pb
ON g.id = pb.game_id
LEFT JOIN open_game_possible_button_sets AS ps
ON g.id = ps.game_id
LEFT JOIN button_definitions AS b
ON pb.button_id = b.id
LEFT JOIN button_sets AS s
ON ps.set_id = s.id
WHERE g.status = "OPEN";