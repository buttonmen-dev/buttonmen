CREATE TABLE game_action_log_type_create_game (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    creator_id         SMALLINT UNSIGNED NOT NULL,
    INDEX (action_log_id)
);
