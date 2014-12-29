ALTER TABLE button ADD dhs_id SMALLINT UNSIGNED;

DROP TABLE IF EXISTS dhs_site_button_vs_button_stats;
CREATE TABLE dhs_site_button_vs_button_stats (
    dhs_button_id_a     SMALLINT UNSIGNED NOT NULL,
    dhs_button_id_b     SMALLINT UNSIGNED NOT NULL,
    games_button_a_won  MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    games_button_b_won  MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    date_compiled       TIMESTAMP
);
