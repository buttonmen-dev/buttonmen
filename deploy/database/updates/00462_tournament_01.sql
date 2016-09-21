DROP TABLE IF EXISTS tournament;
DROP TABLE IF EXISTS tournament_status;
DROP TABLE IF EXISTS tourn_player_map;
DROP TABLE IF EXISTS tourn_player_watch_map;

CREATE TABLE tournament (
    id                 SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    start_time         TIMESTAMP DEFAULT 0,
    status_id          TINYINT UNSIGNED NOT NULL,
    tournament_state   TINYINT UNSIGNED DEFAULT 10,
    round_number       TINYINT UNSIGNED DEFAULT 1,
    n_players          TINYINT UNSIGNED NOT NULL,
    n_target_wins      TINYINT UNSIGNED NOT NULL,
    tournament_type    VARCHAR(50) NOT NULL,
    creator_id         SMALLINT UNSIGNED NOT NULL,
    description        VARCHAR(255) NOT NULL
);

CREATE TABLE tournament_status (
    id                 TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(20) NOT NULL
);

INSERT INTO tournament_status (name) VALUES
('OPEN'),
('ACTIVE'),
('COMPLETE'),
('CANCELLED'),
('BROKEN');

ALTER TABLE game
ADD tournament_round_number TINYINT UNSIGNED
AFTER tournament_id;

CREATE TABLE tourn_player_map (
    tourn_id            MEDIUMINT UNSIGNED NOT NULL,
    player_id           SMALLINT UNSIGNED NOT NULL,
    button_id           SMALLINT UNSIGNED,
    position            TINYINT UNSIGNED NOT NULL,
    remain_count TINYINT UNSIGNED DEFAULT 0 NOT NULL,
    INDEX (tourn_id, player_id)
);

CREATE TABLE tourn_player_watch_map (
    tourn_id            MEDIUMINT UNSIGNED NOT NULL,
    player_id           SMALLINT UNSIGNED NOT NULL
);
