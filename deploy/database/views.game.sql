# Database views for game-related tables

DROP VIEW IF EXISTS game_player_view;
CREATE VIEW game_player_view
AS SELECT m.*, p.name_ingame AS player_name, b.name AS button_name
FROM game_player_map AS m
LEFT JOIN player AS p
ON m.player_id = p.id
LEFT JOIN button AS b
ON m.button_id = b.id;

DROP VIEW IF EXISTS open_game_possible_button_view;
CREATE VIEW open_game_possible_button_view
AS SELECT g.id, pb.button_id, ps.set_id, b.name AS button_name, s.name AS set_name
FROM game AS g
LEFT JOIN open_game_possible_buttons AS pb
ON g.id = pb.game_id
LEFT JOIN open_game_possible_button_sets AS ps
ON g.id = ps.game_id
LEFT JOIN button AS b
ON pb.button_id = b.id
LEFT JOIN button_set AS s
ON ps.set_id = s.id
WHERE g.status = "OPEN";
