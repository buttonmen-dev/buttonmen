-- Older games won't have a start date set, which is suboptimal, so let's
-- estimate one for them now, based on their first action.
UPDATE game AS g
      INNER JOIN (
              SELECT gal.game_id, MIN(gal.action_time) AS action_time
              FROM game_action_log AS gal
              GROUP BY gal.game_id
      ) AS first_gal ON first_gal.game_id = g.id
SET g.start_time = first_gal.action_time
WHERE g.start_time = 0
