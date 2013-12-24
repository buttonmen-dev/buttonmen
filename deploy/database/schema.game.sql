# Table schemas for game-related tables

DROP TABLE IF EXISTS game;
CREATE TABLE game (
    id                 MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    last_action_time   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_id          TINYINT UNSIGNED NOT NULL,
    game_state         TINYINT UNSIGNED DEFAULT 10,
    n_players          TINYINT UNSIGNED DEFAULT 2,
    round_number       TINYINT UNSIGNED DEFAULT 0,
    turn_number_in_round TINYINT UNSIGNED DEFAULT 0,
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

DROP TABLE IF EXISTS game_status;
CREATE TABLE game_status (
    id                 TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(20) NOT NULL
);

DROP TABLE IF EXISTS game_player_map;
CREATE TABLE game_player_map (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    player_id          SMALLINT UNSIGNED NOT NULL,
    button_id          SMALLINT UNSIGNED,
    position           TINYINT UNSIGNED NOT NULL,
    did_win_initiative BOOLEAN DEFAULT FALSE,
    is_awaiting_action BOOLEAN DEFAULT FALSE,
    n_rounds_won       TINYINT UNSIGNED DEFAULT 0,
    n_rounds_lost      TINYINT UNSIGNED DEFAULT 0,
    n_rounds_drawn     TINYINT UNSIGNED DEFAULT 0,
    handicap           TINYINT UNSIGNED DEFAULT 0,
    is_player_hidden   BOOLEAN DEFAULT FALSE
);

DROP TABLE IF EXISTS game_swing_map;
CREATE TABLE game_swing_map (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    player_id          SMALLINT UNSIGNED NOT NULL,
    swing_type         CHAR NOT NULL,
    swing_value        TINYINT UNSIGNED
);

DROP TABLE IF EXISTS game_action_log;
CREATE TABLE game_action_log (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    action_time        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    game_state         TINYINT UNSIGNED DEFAULT 10,
    action_type        VARCHAR(20),
    acting_player      SMALLINT UNSIGNED NOT NULL,
    message            VARCHAR(512)
);

DROP TABLE IF EXISTS game_chat_log;
CREATE TABLE game_chat_log (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    chat_time          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    chatting_player    SMALLINT UNSIGNED NOT NULL,
    message            VARCHAR(1024)
);

DROP TABLE IF EXISTS die;
CREATE TABLE die (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id           TINYINT UNSIGNED NOT NULL,
    original_owner_id  TINYINT UNSIGNED NOT NULL,
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    status_id          TINYINT UNSIGNED NOT NULL,
    recipe             VARCHAR(20) NOT NULL,
    swing_value        TINYINT UNSIGNED,
    position           TINYINT UNSIGNED NOT NULL,
    value              SMALLINT
);

DROP TABLE IF EXISTS die_status;
CREATE TABLE die_status (
    id                 TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(20) NOT NULL
);

DROP TABLE IF EXISTS open_game_possible_buttons;
CREATE TABLE open_game_possible_buttons (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    button_id          SMALLINT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS open_game_possible_buttonsets;
CREATE TABLE open_game_possible_buttonsets (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    set_id             SMALLINT UNSIGNED NOT NULL
);

# remove legacy tables that have been replaced by game_action_log
DROP TABLE IF EXISTS last_attack;
DROP TABLE IF EXISTS last_attack_die_map;

DROP TABLE IF EXISTS tournament;
CREATE TABLE tournament (
    id                 SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status_id          TINYINT UNSIGNED NOT NULL,
    current_round      TINYINT UNSIGNED DEFAULT 1,
    n_players          TINYINT UNSIGNED NOT NULL,
    n_target_wins      TINYINT UNSIGNED NOT NULL,
    is_double_elim     BOOLEAN NOT NULL,
    creator_id         SMALLINT UNSIGNED NOT NULL,
    description        VARCHAR(255) NOT NULL
);
