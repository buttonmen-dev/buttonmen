DELETE FROM tournament;

DELETE FROM tournament_status;
INSERT INTO tournament_status (name) VALUES
('OPEN'),
('ACTIVE'),
('COMPLETE'),
('CANCELLED'),
('BROKEN');

DELETE FROM tournament_player_map;

DELETE FROM tournament_player_watch_map;
