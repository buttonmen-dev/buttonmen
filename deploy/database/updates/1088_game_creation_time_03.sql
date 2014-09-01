-- For games that never had any turns taken, their last action time should be
-- a reasonable approximation of the game start time.
UPDATE game AS g
SET g.start_time = g.last_action_time
WHERE g.start_time = 0
