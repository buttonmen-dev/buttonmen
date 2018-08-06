CREATE TABLE game_action_log_type_determine_initiative (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    winner_id          SMALLINT UNSIGNED NOT NULL,
    round_number       TINYINT UNSIGNED NOT NULL,
    is_tie             BOOLEAN DEFAULT FALSE,
    INDEX (action_log_id)
);

CREATE TABLE game_action_log_type_determine_initiative_slow_button (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    player_id          SMALLINT UNSIGNED NOT NULL,
    INDEX (action_log_id)
);

CREATE TABLE game_action_log_type_determine_initiative_die (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    player_id          SMALLINT UNSIGNED NOT NULL,
    recipe_status      VARCHAR(20) NOT NULL,
    recipe             VARCHAR(20) NOT NULL,
    included           BOOLEAN DEFAULT TRUE,
    INDEX (action_log_id)
);
