CREATE TABLE game_action_log_type_end_winner (
    id                  INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    round_number        TINYINT UNSIGNED NOT NULL,
    winning_round_score VARCHAR(10) NOT NULL,
    losing_round_score  VARCHAR(10) NOT NULL,
    surrendered         BOOLEAN DEFAULT FALSE
);
