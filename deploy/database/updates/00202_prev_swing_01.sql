# add is_expired columns to specify whether swing/option values are expired

ALTER TABLE game_swing_map  ADD is_expired BOOLEAN DEFAULT FALSE;

ALTER TABLE game_option_map ADD is_expired BOOLEAN DEFAULT FALSE;
