UPDATE game_player_map AS m
SET is_awaiting_action = 0
WHERE game_id IN (SELECT id FROM game WHERE game_state = 251);
