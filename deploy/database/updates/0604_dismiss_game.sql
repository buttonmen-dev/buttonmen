ALTER TABLE game_player_map
    ADD COLUMN was_game_dismissed BOOLEAN DEFAULT 0 NOT NULL AFTER `last_action_time`;

-- This needs to be recreated for it to recognize the new column
DROP VIEW IF EXISTS game_player_view;
CREATE VIEW game_player_view
AS SELECT m.*,
          p.name_ingame AS player_name, p.autopass,
          b.name AS button_name,
          g.n_target_wins
FROM game_player_map AS m
LEFT JOIN player AS p
ON m.player_id = p.id
LEFT JOIN button AS b
ON m.button_id = b.id
LEFT JOIN game AS g
ON m.game_id = g.id;

-- All pre-existing completed games should be considered dismissed already, so
-- the user doesn't have to manually go through every game they've ever played.
UPDATE game_player_map AS gpm
    INNER JOIN game AS g ON g.id = gpm.game_id
    INNER JOIN game_status AS s ON s.id = g.status_id
SET was_game_dismissed = 1
WHERE s.name = 'COMPLETE'
