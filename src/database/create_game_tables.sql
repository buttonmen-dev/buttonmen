# CREATE DATABASE buttonmen CHARACTER SET utf8;
# USE buttonmen;

DROP VIEW IF EXISTS game_view;
DROP TABLE IF EXISTS game_details;
DROP TABLE IF EXISTS die_details;

CREATE TABLE game_details (
    id                 MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    last_action_time   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status             ENUM ('OPEN', 'ACTIVE', 'COMPLETE') NOT NULL,
    n_players          TINYINT UNSIGNED DEFAULT 2,
    n_target_wins      TINYINT UNSIGNED,
    n_recent_draws     TINYINT UNSIGNED DEFAULT 0,
    n_recent_passes    TINYINT UNSIGNED DEFAULT 0,
    current_player_id  SMALLINT UNSIGNED,
    last_winner_id     SMALLINT UNSIGNED,
    tournament_id      SMALLINT UNSIGNED,
    description        VARCHAR(255),
    chat               TEXT
);

CREATE TABLE game_player_map (
    game_id            MEDIUMINT UNSIGNED,
    player_id          SMALLINT UNSIGNED,
    button_id          SMALLINT UNSIGNED,
    position           TINYINT UNSIGNED NOT NULL,
    is_awaiting_action BOOLEAN,
    n_rounds_won       TINYINT UNSIGNED,
    n_rounds_drawn     TINYINT UNSIGNED,
    handicap           TINYINT UNSIGNED,
    is_player_hidden   BOOLEAN
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

-- CREATE TABLE button_definitions (
--     id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     /* 'The Fictitious Alan Clark' has 25 characters */
--     name        VARCHAR(40) UNIQUE NOT NULL,
--     /* 'Gryphon' has a recipe of:
--        P{g,sF}10 P{f,z}12 P{f,z}12 X! +`R! ro@Z? rz(V,V) rP{m,D}8 grP{h,o,n}Y
--        which has 70 characters */
--     recipe      VARCHAR(100) NOT NULL,
--     tourn_legal BOOLEAN NOT NULL,
--     image_path  VARCHAR(100),
--     set_id      SMALLINT UNSIGNED,
--     INDEX (name)
-- );
--
-- CREATE VIEW button_view
-- AS SELECT d.name, d.recipe, d.tourn_legal, d.image_path, s.name AS set_name
-- FROM button_definitions AS d
-- LEFT JOIN button_sets AS s
-- ON d.set_id = s.id;
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
