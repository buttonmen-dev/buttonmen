CREATE TABLE game_action_log_type_end_draw (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    round_number       TINYINT UNSIGNED NOT NULL,
    round_score        VARCHAR(10) NOT NULL,
    INDEX (action_log_id)
);
