DELETE FROM tournament;

DELETE FROM tournament_status;
INSERT INTO tournament_status (name) VALUES
('OPEN'),
('ACTIVE'),
('COMPLETE'),
('CANCELLED'),
('BROKEN');

DELETE FROM tourn_player_map;

DELETE FROM tourn_player_watch_map;
