# modify the type of game_action_log.message so we can put more data in it
# this is non-destructive and should retain existing entries
ALTER TABLE game_action_log MODIFY message TEXT;
