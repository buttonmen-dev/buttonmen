ALTER TABLE player ADD last_access_time TIMESTAMP DEFAULT 0 AFTER last_action_time;

ALTER TABLE game_player_map ADD last_action_time TIMESTAMP DEFAULT 0;
