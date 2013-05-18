# CREATE DATABASE buttonmen CHARACTER SET utf8;
# USE buttonmen;

CREATE TABLE button_sets (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    /* 'Chicagoland Games Enclave' has 27 characters */
    name VARCHAR(40) NOT NULL
);

CREATE TABLE button_definitions (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    /* 'The Fictitious Alan Clark' has 25 characters */
    name VARCHAR(40) NOT NULL,
    /* 'Gryphon' has a recipe of:
       P{g,sF}10 P{f,z}12 P{f,z}12 X! +`R! ro@Z? rz(V,V) rP{m,D}8 grP{h,o,n}Y
       which has 70 characters */
    recipe VARCHAR(100) NOT NULL,
    image_path VARCHAR(100),
    set_id SMALLINT,
    UNIQUE (name),
    INDEX (name)
);

CREATE VIEW button_view
AS SELECT d.name, d.recipe, d.image_path, s.name AS set_name
FROM button_definitions AS d, button_sets AS s
WHERE d.set_id = s.id;

INSERT INTO button_sets (name) VALUES
('Soldiers'),
('Brom');

INSERT INTO button_definitions (name, recipe, set_id) VALUES
('Avis', '4 4 10 12 X', (SELECT id FROM button_sets WHERE name="Soldiers")),
('Bauer', '8 10 12 20 X', (SELECT id FROM button_sets WHERE name="Soldiers")),
('Stark', '4 6 8 X X', (SELECT id FROM button_sets WHERE name="Soldiers"));
