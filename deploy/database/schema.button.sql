# Table definitions for button-related tables

CREATE TABLE buttonset (
    id          SMALLINT UNSIGNED PRIMARY KEY,
    # 'Chicagoland Games Enclave' has 27 characters
    name        VARCHAR(40) NOT NULL,
    # We may as well sort sets without a sort order to the end
    sort_order  INT NOT NULL DEFAULT 999999
);

CREATE TABLE button (
    id          SMALLINT UNSIGNED PRIMARY KEY,
    # 'The Fictitious Alan Clark' has 25 characters
    name        VARCHAR(40) UNIQUE NOT NULL,
    # 'Gryphon' has a recipe of:
    # P{g,sF}10 P{f,z}12 P{f,z}12 X! +`R! ro@Z? rz(V,V) rP{m,D}8 grP{h,o,n}Y
    # which has 70 characters
    recipe      VARCHAR(100) NOT NULL,
    btn_special BOOLEAN NOT NULL,
    tourn_legal BOOLEAN NOT NULL,
    set_id      SMALLINT UNSIGNED,
    flavor_text VARCHAR(500),
    sort_order  INT NOT NULL DEFAULT 0,
    INDEX (name)
);

CREATE TABLE tag (
    id SMALLINT UNSIGNED PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE button_tag_map(
    button_id SMALLINT UNSIGNED NOT NULL,
    tag_id SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (button_id, tag_id),
    CONSTRAINT fk_tagged_button_id
                         FOREIGN KEY (button_id) REFERENCES button(id),
    CONSTRAINT fk_tag_id FOREIGN KEY (tag_id)    REFERENCES tag(id)
);
