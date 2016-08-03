DROP TABLE IF EXISTS dhs_player;
CREATE TABLE dhs_player (
    dhs_player_id   SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    dhs_player_name VARCHAR(40) NOT NULL,
    bw_player_id    SMALLINT UNSIGNED,
    INDEX (bw_player_id)
);

DROP TABLE IF EXISTS dhs_button;
CREATE TABLE dhs_button (
    dhs_button_id   SMALLINT UNSIGNED NOT NULL,
    dhs_button_name VARCHAR(40) NOT NULL,
    bw_button_id    SMALLINT UNSIGNED,
    INDEX (bw_button_id)
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
CREATE TABLE dhs_site_button_player_stats (
    dhs_button_id         SMALLINT UNSIGNED NOT NULL,
    dhs_player_id         SMALLINT UNSIGNED NOT NULL,
    games_won_using       MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    games_lost_using      MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    games_won_against     MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    games_lost_against    MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    date_compiled         TIMESTAMP
);
