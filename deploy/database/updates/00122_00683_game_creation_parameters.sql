ALTER TABLE game
    ADD COLUMN previous_game_id MEDIUMINT UNSIGNED NULL AFTER chat,
    ADD FOREIGN KEY (previous_game_id) REFERENCES game(id);
