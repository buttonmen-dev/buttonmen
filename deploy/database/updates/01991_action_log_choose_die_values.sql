CREATE TABLE game_action_log_type_choose_die_values (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    round_number       TINYINT UNSIGNED NOT NULL,
    INDEX (action_log_id)
);

CREATE TABLE game_action_log_type_choose_die_values_swing (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    swing_type         CHAR NOT NULL,  
    swing_value        TINYINT UNSIGNED,
    INDEX (action_log_id)
);
 
CREATE TABLE game_action_log_type_choose_die_values_option (
    id                 INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id      INTEGER UNSIGNED NOT NULL,
    recipe             VARCHAR(20) NOT NULL,
    option_value       TINYINT UNSIGNED,
    INDEX (action_log_id)
);
