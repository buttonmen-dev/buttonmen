DROP TABLE IF EXISTS dhs_player;
CREATE TABLE dhs_player (
    dhs_player_id   TINYINT UNSIGNED NOT NULL,
    dhs_player_name VARCHAR(40) NOT NULL,
    bw_player_id    TINYINT UNSIGNED
);

DROP TABLE IF EXISTS dhs_button;
CREATE TABLE dhs_button (
    dhs_button_id   TINYINT UNSIGNED NOT NULL,
    dhs_button_name VARCHAR(40) NOT NULL,
    bw_button_id    TINYINT UNSIGNED
);


DROP TABLE IF EXISTS dhs_site_button_vs_button_stats;
CREATE TABLE dhs_site_button_vs_button_stats (
    dhs_button_id_a     SMALLINT UNSIGNED NOT NULL,
    dhs_button_id_b     SMALLINT UNSIGNED NOT NULL,
    games_button_a_won  MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    games_button_b_won  MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    date_compiled       TIMESTAMP
);


DROP TABLE IF EXISTS dhs_site_button_player_stats;
CREATE TABLE dhs_site_button_vs_button_stats (
    dhs_button_id         TINYINT UNSIGNED NOT NULL,
    dhs_player_id         TINYINT UNSIGNED NOT NULL,
    games_won_using       MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    games_lost_using      MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    games_won_against     MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    games_lost_against    MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    date_compiled         TIMESTAMP
);

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
    i.autopass,
    i.monitor_redirects_to_game,
    i.monitor_redirects_to_forum,
    i.automatically_monitor,
    i.image_path,
    i.image_size,
    i.uses_gravatar,
    i.comment,
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

DROP VIEW IF EXISTS button_view;
CREATE VIEW button_view
AS SELECT
    b.id,
    b.name,
    b.recipe,
    b.tourn_legal,
    b.btn_special,
    b.flavor_text,
    s.name AS set_name,
    s.sort_order AS set_sort_order,
    d.dhs_button_id,
    d.dhs_button_name
FROM button AS b
    LEFT JOIN buttonset AS s ON b.set_id = s.id
    LEFT JOIN dhs_button AS d ON d.bw_button_id = b.id
ORDER BY s.sort_order ASC, b.name ASC;
