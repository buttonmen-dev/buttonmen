CREATE TABLE game_action_log_type_turndown_focus_die (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    recipe             VARCHAR(20) NOT NULL,
    orig_value         SMALLINT,
    turndown_value     SMALLINT,
    INDEX (action_log_id)
);
