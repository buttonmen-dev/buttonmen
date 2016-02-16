

DELETE FROM game;

DELETE FROM game_player_map;

DELETE FROM game_action_log;

DELETE FROM game_chat_log;

DELETE FROM die;

DELETE FROM open_game_possible_buttons;

DELETE FROM open_game_possible_buttonsets;

DELETE FROM tournament;

DELETE FROM game_status;
INSERT INTO game_status (name) VALUES
('OPEN'),
('ACTIVE'),
('COMPLETE'),
('CANCELLED'),
('BROKEN');

DELETE FROM die_status;
INSERT INTO die_status (name) VALUES
('NORMAL'),
('CAPTURED'),
('DISABLED'),
('OUT_OF_PLAY'),
('DELETED'),
('SELECTED'),
('DIZZY');
