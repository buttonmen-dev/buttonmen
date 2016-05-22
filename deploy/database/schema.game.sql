# Table schemas for game-related tables

CREATE TABLE game (
    id                 MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    start_time         TIMESTAMP DEFAULT 0,
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
    chat               TEXT,
    previous_game_id   MEDIUMINT UNSIGNED,
    FOREIGN KEY (previous_game_id) REFERENCES game(id)
);

CREATE TABLE game_status (
    id                 TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(20) NOT NULL
);

CREATE TABLE game_player_map (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    player_id          SMALLINT UNSIGNED,
    button_id          SMALLINT UNSIGNED,
    alt_recipe         VARCHAR(100),
    position           TINYINT UNSIGNED NOT NULL,
    did_win_initiative BOOLEAN DEFAULT FALSE,
    is_awaiting_action BOOLEAN DEFAULT FALSE,
    n_rounds_won       TINYINT UNSIGNED DEFAULT 0,
    n_rounds_lost      TINYINT UNSIGNED DEFAULT 0,
    n_rounds_drawn     TINYINT UNSIGNED DEFAULT 0,
    handicap           TINYINT UNSIGNED DEFAULT 0,
    is_player_hidden   BOOLEAN DEFAULT FALSE,
    last_action_time   TIMESTAMP DEFAULT 0,
    was_game_dismissed BOOLEAN DEFAULT FALSE NOT NULL,
    is_button_random   BOOLEAN DEFAULT FALSE NOT NULL,
    has_player_accepted BOOLEAN DEFAULT TRUE NOT NULL,
    INDEX (game_id, player_id)
);

CREATE TABLE game_swing_map (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    player_id          SMALLINT UNSIGNED NOT NULL,
    swing_type         CHAR NOT NULL,
    swing_value        TINYINT UNSIGNED,
    is_expired         BOOLEAN DEFAULT FALSE
);

CREATE TABLE game_option_map (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    player_id          SMALLINT UNSIGNED NOT NULL,
    die_idx            INT UNSIGNED NOT NULL,
    option_value       TINYINT UNSIGNED,
    is_expired         BOOLEAN DEFAULT FALSE
);

CREATE TABLE game_action_log (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    action_time        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    game_state         TINYINT UNSIGNED DEFAULT 10,
    action_type        VARCHAR(20),
    acting_player      SMALLINT UNSIGNED NOT NULL,
    message            TEXT,
    INDEX (game_id)
);

CREATE TABLE game_action_log_type_end_draw (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    round_number       TINYINT UNSIGNED NOT NULL,
    round_score        VARCHAR(10) NOT NULL,
    INDEX (action_log_id)
);

CREATE TABLE game_action_log_type_end_winner (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    round_number        TINYINT UNSIGNED NOT NULL,
    winning_round_score VARCHAR(10) NOT NULL,
    losing_round_score  VARCHAR(10) NOT NULL,
    surrendered         BOOLEAN DEFAULT FALSE,
    INDEX (action_log_id)
);

CREATE TABLE game_chat_log (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    chat_time          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    chatting_player    SMALLINT UNSIGNED NOT NULL,
    -- A chat message is limited to 2000 Unicode characters (see
    -- GAME_CHAT_MAX_LENGTH in ApiSpec.php and Game.js). Since a Unicode
    -- character can be up to four bytes, this requires a varchar(8000).
    message            VARCHAR(8000),
    INDEX (game_id)
);

CREATE TABLE die (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id           SMALLINT UNSIGNED NOT NULL,
    original_owner_id  SMALLINT UNSIGNED NOT NULL,
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    status_id          TINYINT UNSIGNED NOT NULL,
    recipe             VARCHAR(20) NOT NULL,
    position           TINYINT UNSIGNED NOT NULL,
    value              SMALLINT,
    actual_max         TINYINT UNSIGNED,
    flags              VARCHAR(253)
);

CREATE TABLE die_status (
    id                 TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(20) NOT NULL
);

CREATE TABLE open_game_possible_buttons (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    button_id          SMALLINT UNSIGNED NOT NULL
);

CREATE TABLE open_game_possible_buttonsets (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    set_id             SMALLINT UNSIGNED NOT NULL
);

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
