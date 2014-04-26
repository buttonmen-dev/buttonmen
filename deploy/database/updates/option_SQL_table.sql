DROP TABLE IF EXISTS game_option_map;
CREATE TABLE game_option_map (
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    player_id          SMALLINT UNSIGNED NOT NULL,
    die_id             INT UNSIGNED NOT NULL,
    option_value       TINYINT UNSIGNED
);

ALTER TABLE die DROP COLUMN chosen_max;

DROP VIEW IF EXISTS die_view;
CREATE VIEW die_view
AS SELECT d.*,
          o.option_value
FROM die as d
LEFT JOIN game_option_map AS o
ON d.id = o.die_id;