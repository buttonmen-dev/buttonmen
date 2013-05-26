# CREATE DATABASE buttonmen CHARACTER SET utf8;
# USE buttonmen;

DROP VIEW  IF EXISTS game_player_view;
DROP TABLE IF EXISTS game_details,
                     game_player_map,
                     die_details,
                     open_game_possible_buttons,
                     open_game_possible_button_sets,
                     tournament_details;

CREATE TABLE game_details (
    id                 MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    last_action_time   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status             ENUM ('OPEN', 'ACTIVE', 'COMPLETE') NOT NULL,
    n_players          TINYINT UNSIGNED DEFAULT 2,
    n_target_wins      TINYINT UNSIGNED NOT NULL,
    n_recent_draws     TINYINT UNSIGNED DEFAULT 0,
    n_recent_passes    TINYINT UNSIGNED DEFAULT 0,
    creator_id         SMALLINT UNSIGNED NOT NULL,
    current_player_id  SMALLINT UNSIGNED,
    last_winner_id     SMALLINT UNSIGNED,
    tournament_id      SMALLINT UNSIGNED,
    description        VARCHAR(255) NOT NULL,
    chat               TEXT
);

CREATE TABLE game_player_map (
    game_id            MEDIUMINT UNSIGNED PRIMARY KEY,
    player_id          SMALLINT UNSIGNED NOT NULL,
    button_id          SMALLINT UNSIGNED,
    position           TINYINT UNSIGNED NOT NULL,
    is_awaiting_action BOOLEAN DEFAULT FALSE,
    n_rounds_won       TINYINT UNSIGNED DEFAULT 0,
    n_rounds_drawn     TINYINT UNSIGNED DEFAULT 0,
    handicap           TINYINT UNSIGNED DEFAULT 0,
    is_player_hidden   BOOLEAN DEFAULT FALSE
);

CREATE TABLE die_details (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id           TINYINT UNSIGNED NOT NULL,
    game_id            MEDIUMINT UNSIGNED NOT NULL,
    status             ENUM ('NORMAL', 'CAPTURED', 'DISABLED', 'OUT_OF_GAME') DEFAULT 'NORMAL',
    recipe             VARCHAR(20) NOT NULL,
    position           TINYINT UNSIGNED NOT NULL,
    value              SMALLINT
);

CREATE TABLE open_game_possible_buttons (
    game_id            MEDIUMINT UNSIGNED PRIMARY KEY,
    button_id          SMALLINT UNSIGNED NOT NULL
);

CREATE TABLE open_game_possible_button_sets (
    game_id            MEDIUMINT UNSIGNED PRIMARY KEY,
    set_id             SMALLINT UNSIGNED NOT NULL
);

CREATE TABLE tournament_details (
    id                 SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status             ENUM ('OPEN', 'ACTIVE', 'COMPLETE') NOT NULL,
    current_round      TINYINT UNSIGNED DEFAULT 1,
    n_players          TINYINT UNSIGNED NOT NULL,
    n_target_wins      TINYINT UNSIGNED NOT NULL,
    is_double_elim     BOOLEAN NOT NULL,
    creator_id         SMALLINT UNSIGNED NOT NULL,
    description        VARCHAR(255) NOT NULL
);

CREATE VIEW game_player_view
AS SELECT m.*, p.name_ingame AS player_name, b.name AS button_name
FROM game_player_map AS m
LEFT JOIN player_info AS p
ON m.player_id = p.id
LEFT JOIN button_definitions AS b
ON m.button_id = b.id;

CREATE VIEW open_game_possible_button_view
AS SELECT g.id, pb.button_id, ps.set_id, b.name AS button_name, s.name AS set_name
FROM game_details AS g
LEFT JOIN open_game_possible_buttons AS pb
ON g.id = pb.game_id
LEFT JOIN open_game_possible_button_sets AS ps
ON g.id = ps.game_id
LEFT JOIN button_definitions AS b
ON pb.button_id = b.id
LEFT JOIN button_sets AS s
ON ps.set_id = s.id
WHERE g.status = "OPEN";


--
-- INSERT INTO button_sets (name) VALUES
-- ('Soldiers'),
-- ('Brom');
--
-- INSERT INTO button_definitions (name, recipe, tourn_legal, set_id) VALUES
-- ('Avis', '4 4 10 12 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Hammer', '6 12 20 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Bauer', '8 10 12 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Stark', '4 6 8 X X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Clare', '6 8 8 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Kith', '6 8 12 12 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Karl', '4 6 6 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Iago', '20 20 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Niles', '6 10 10 12 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Shore', '4 4 20 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Hannah', '8 10 10 10 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Kublai', '4 8 12 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
-- ('Changeling', 'X X X X X', 0, (SELECT id FROM button_sets WHERE name="Soldiers"));
