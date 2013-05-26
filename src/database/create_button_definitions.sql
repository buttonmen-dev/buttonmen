# CREATE DATABASE buttonmen CHARACTER SET utf8;
# USE buttonmen;

DROP VIEW  IF EXISTS button_view;
DROP TABLE IF EXISTS button_definitions,
                     button_sets;

CREATE TABLE button_sets (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    /* 'Chicagoland Games Enclave' has 27 characters */
    name        VARCHAR(40) NOT NULL
);

CREATE TABLE button_definitions (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    /* 'The Fictitious Alan Clark' has 25 characters */
    name        VARCHAR(40) UNIQUE NOT NULL,
    /* 'Gryphon' has a recipe of:
       P{g,sF}10 P{f,z}12 P{f,z}12 X! +`R! ro@Z? rz(V,V) rP{m,D}8 grP{h,o,n}Y
       which has 70 characters */
    recipe      VARCHAR(100) NOT NULL,
    tourn_legal BOOLEAN NOT NULL,
    image_path  VARCHAR(100),
    set_id      SMALLINT UNSIGNED,
    INDEX (name)
);

CREATE VIEW button_view
AS SELECT d.name, d.recipe, d.tourn_legal, d.image_path, s.name AS set_name
FROM button_definitions AS d
LEFT JOIN button_sets AS s
ON d.set_id = s.id;

INSERT INTO button_sets (name) VALUES
('Soldiers'),
('Brom');

INSERT INTO button_definitions (name, recipe, tourn_legal, set_id) VALUES
('Avis', '4 4 10 12 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Hammer', '6 12 20 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Bauer', '8 10 12 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Stark', '4 6 8 X X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Clare', '6 8 8 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Kith', '6 8 12 12 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Karl', '4 6 6 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Iago', '20 20 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Niles', '6 10 10 12 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Shore', '4 4 20 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Hannah', '8 10 10 10 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Kublai', '4 8 12 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Changeling', 'X X X X X', 0, (SELECT id FROM button_sets WHERE name="Soldiers"));
