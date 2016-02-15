# Database views for game-related tables

DROP VIEW IF EXISTS game_player_view;
CREATE VIEW game_player_view
AS SELECT
    m.game_id,
    m.player_id,
    m.button_id,
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
    (length(p.vacation_message)>0) as on_vacation,
    b.name AS button_name,
    g.n_target_wins
FROM game_player_map AS m
LEFT JOIN player AS p
ON m.player_id = p.id
LEFT JOIN button AS b
ON m.button_id = b.id
LEFT JOIN game AS g
ON m.game_id = g.id;

DROP VIEW IF EXISTS open_game_possible_button_view;
CREATE VIEW open_game_possible_button_view
AS SELECT
    g.id,
    pb.button_id,
    ps.set_id,
    b.name AS button_name,
    s.name AS set_name
FROM game AS g
LEFT JOIN open_game_possible_buttons AS pb
ON g.id = pb.game_id
LEFT JOIN open_game_possible_buttonsets AS ps
ON g.id = ps.game_id
LEFT JOIN button AS b
ON pb.button_id = b.id
LEFT JOIN buttonset AS s
ON ps.set_id = s.id
WHERE g.status_id = (SELECT id FROM game_status WHERE name = "OPEN");

