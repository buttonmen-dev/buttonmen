CREATE TABLE game_action_log_type_add_auxiliary (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    round_number       TINYINT UNSIGNED NOT NULL,
    die_recipe         VARCHAR(20) NOT NULL,
    INDEX (action_log_id)
);
