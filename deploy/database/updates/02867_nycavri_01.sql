INSERT into buttonset (id, name, sort_order) VALUES
(94, "Modern Fanatics", 110000);

UPDATE button
SET set_id = (SELECT id FROM buttonset WHERE name="Modern Fanatics")
WHERE set_id = (SELECT id FROM buttonset WHERE name="2020 Fanatics");

UPDATE button
SET set_id = (SELECT id FROM buttonset WHERE name="Modern Fanatics")
WHERE set_id = (SELECT id FROM buttonset WHERE name="2021 Fanatics");

DELETE FROM buttonset WHERE name="2020 Fanatics";
DELETE FROM buttonset WHERE name="2021 Fanatics";

INSERT into button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(867, 'nycavri', '(1) (3) (5) (14) Hs^(14)', 0, 0, (SELECT id FROM buttonset WHERE name="Modern Fanatics"));

UPDATE button SET flavor_text="Described as ""subtle and engaging"" by Lawrence Block, when not beating people up nycavri is reading/writing novels, playing/designing games, and living/loving life.<br><br>Art by Lauren Rogers" WHERE name="nycavri";
