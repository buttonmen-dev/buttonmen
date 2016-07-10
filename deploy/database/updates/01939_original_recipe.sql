ALTER TABLE game_player_map
ADD original_recipe VARCHAR(100)
AFTER button_id;

DROP VIEW IF EXISTS game_player_view;
CREATE VIEW game_player_view
AS SELECT
    m.game_id,
    m.player_id,
    m.button_id,
    m.original_recipe,
    m.alt_recipe,
    m.position,
    m.did_win_initiative,
    m.is_awaiting_action,
    m.n_rounds_won,
    m.n_rounds_lost,
    m.n_rounds_drawn,
    m.handicap,
    m.is_player_hidden,
    m.last_action_time,
    m.was_game_dismissed,
    m.is_button_random,
    m.has_player_accepted,
    p.name_ingame AS player_name,
    p.autopass,
    p.fire_overshooting,
    (length(p.vacation_message) > 0) as is_on_vacation,
    b.name AS button_name,
    g.n_target_wins
FROM game_player_map AS m
LEFT JOIN player AS p
ON m.player_id = p.id
LEFT JOIN button AS b
ON m.button_id = b.id
LEFT JOIN game AS g
ON m.game_id = g.id;

-- first copy the canonical recipes directly from the button table
UPDATE game_player_map
SET original_recipe = (SELECT recipe
  FROM button
  WHERE button.id = game_player_map.button_id
)
WHERE original_recipe IS NULL;

-- for Echo and Zero, explicitly copy the opponent's original recipe
--
-- note that the subselect on the FROM line is necessary because MySQL
-- doesn't currently allow the table to be updated to be directly
-- in the FROM condition, see also
--   http://www.xaprb.com/blog/2006/06/23/how-to-select-from-an-update-target-in-mysql/
UPDATE game_player_map AS m1
SET m1.original_recipe = (SELECT m2.original_recipe
  FROM (SELECT game_id, player_id, original_recipe FROM game_player_map) AS m2
  WHERE m1.game_id = m2.game_id
  AND m1.player_id <> m2.player_id
)
WHERE button_id IN (SELECT b.id
  FROM button AS b
  WHERE b.name IN ('Echo', 'Zero')
);

-- now deal with original recipes that remain empty by directly copying the recipes
UPDATE game_player_map
SET original_recipe = alt_recipe
WHERE original_recipe = '';

-- the last step will be to manually fix RandomBMAnime recipes
