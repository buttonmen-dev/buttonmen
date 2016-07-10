CREATE TABLE game_action_log_type_reroll_chance (
    id                INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_log_id     INTEGER UNSIGNED NOT NULL,
    orig_recipe       VARCHAR(20) NOT NULL,
    orig_value        SMALLINT,
    reroll_recipe     VARCHAR(20) NOT NULL,
    reroll_value      SMALLINT,
    gained_initiative BOOLEAN DEFAULT FALSE,
    INDEX (action_log_id)
);
