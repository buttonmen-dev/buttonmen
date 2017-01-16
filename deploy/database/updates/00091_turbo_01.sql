CREATE TABLE game_turbo_cache (
    game_id      MEDIUMINT UNSIGNED NOT NULL,
    die_idx      TINYINT UNSIGNED NOT NULL,
    turbo_size   TINYINT UNSIGNED NOT NULL,
    INDEX (game_id, die_idx)
);
