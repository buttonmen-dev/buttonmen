# Views for player-related tables

DROP VIEW IF EXISTS player_view;
CREATE VIEW player_view
AS SELECT a.auth_key, i.*
FROM player AS i
LEFT JOIN player_auth AS a
ON i.id = a.id;