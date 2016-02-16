ALTER TABLE player ADD vacation_message VARCHAR(255);

# Copied directly from views.player.sql

DROP VIEW IF EXISTS player_view;
CREATE VIEW player_view
AS SELECT
    a.auth_key,
    i.id,
    i.name_ingame,
    i.password_hashed,
    i.name_irl,
    i.email,
    i.is_email_public,
    ps.name AS status,
    i.dob_month,
    i.dob_day,
    i.gender,
    i.autoaccept,
    i.autopass,
    i.fire_overshooting,
    i.monitor_redirects_to_game,
    i.monitor_redirects_to_forum,
    i.automatically_monitor,
    i.image_path,
    i.image_size,
    i.uses_gravatar,
    i.comment,
    i.vacation_message,
    i.homepage,
    i.favorite_button_id,
    i.favorite_buttonset_id,
    i.player_color,
    i.opponent_color,
    i.neutral_color_a,
    i.neutral_color_b,
    i.last_action_time,
    i.last_access_time,
    i.creation_time,
    i.fanatic_button_id,
    i.n_games_won,
    i.n_games_lost,
    d.dhs_player_id,
    d.dhs_player_name
FROM player AS i
    LEFT JOIN player_auth AS a ON i.id = a.id
    LEFT JOIN player_status AS ps ON ps.id = i.status_id
    LEFT JOIN dhs_player AS d ON d.bw_player_id = i.id;

# Copied directly from views.game.sql

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
    (length(p.vacation_message)>0) as is_on_vacation,
    b.name AS button_name,
    g.n_target_wins
FROM game_player_map AS m
LEFT JOIN player AS p
ON m.player_id = p.id
LEFT JOIN button AS b
ON m.button_id = b.id
LEFT JOIN game AS g
ON m.game_id = g.id;
