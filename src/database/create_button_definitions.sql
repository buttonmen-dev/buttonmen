# CREATE DATABASE buttonmen CHARACTER SET utf8;
# USE buttonmen;

CREATE TABLE button_definitions (
  button_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  /* 'The Fictitious Alan Clark' has 25 characters */
  button_name VARCHAR(40) NOT NULL,
  /* 'Gryphon' has a recipe of:
     P{g,sF}10 P{f,z}12 P{f,z}12 X! +`R! ro@Z? rz(V,V) rP{m,D}8 grP{h,o,n}Y
     which has 70 characters */
  button_recipe VARCHAR(100) NOT NULL,
  button_image_path VARCHAR(100),
  UNIQUE (button_name),
  INDEX (button_name)
);

INSERT INTO button_definitions (button_name, button_recipe) VALUES
('Avis', '4 4 10 12 X'),
('Bauer', '8 10 12 20 X'),
('Stark', '4 6 8 X X');