# Table definitions for button-related tables

DROP TABLE IF EXISTS button_sets;
CREATE TABLE button_sets (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    /* 'Chicagoland Games Enclave' has 27 characters */
    name        VARCHAR(40) NOT NULL
);

DROP TABLE IF EXISTS button_definitions;
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
