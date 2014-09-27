# Table definitions for button-related tables

DROP TABLE IF EXISTS buttonset;
CREATE TABLE buttonset (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    # 'Chicagoland Games Enclave' has 27 characters
    name        VARCHAR(40) NOT NULL
);

DROP TABLE IF EXISTS button;
CREATE TABLE button (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    # 'The Fictitious Alan Clark' has 25 characters
    name        VARCHAR(40) UNIQUE NOT NULL,
    # 'Gryphon' has a recipe of:
    # P{g,sF}10 P{f,z}12 P{f,z}12 X! +`R! ro@Z? rz(V,V) rP{m,D}8 grP{h,o,n}Y
    # which has 70 characters
    recipe      VARCHAR(100) NOT NULL,
    btn_special BOOLEAN NOT NULL,
    tourn_legal BOOLEAN NOT NULL,
    set_id      SMALLINT UNSIGNED,
    INDEX (name)
);
