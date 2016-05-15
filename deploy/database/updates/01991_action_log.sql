ALTER TABLE game_action_log ADD type_log_id INTEGER UNSIGNED DEFAULT NULL AFTER action_type;

CREATE TABLE game_action_log_type_end_draw (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    round_number       TINYINT UNSIGNED NOT NULL,
    round_score        VARCHAR(10) NOT NULL
);
