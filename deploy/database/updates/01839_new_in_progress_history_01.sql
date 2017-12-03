INSERT INTO game_status (name) VALUES ('NEW');

UPDATE game
SET status_id = (SELECT id FROM game_status WHERE name = 'NEW')
WHERE game_state = 13;
