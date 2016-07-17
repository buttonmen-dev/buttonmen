ALTER TABLE player ADD COLUMN die_background VARCHAR(10) DEFAULT 'circle' AFTER favorite_buttonset_id;

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
    i.die_background,
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
