CREATE TABLE game_action_log_type_play_another_turn (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    cause              VARCHAR(20) NOT NULL,
    INDEX (action_log_id)
);
