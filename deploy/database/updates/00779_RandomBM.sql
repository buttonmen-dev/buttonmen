INSERT INTO buttonset (id, name, sort_order) VALUES
(10000, 'RandomBM', 200000);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(10001, 'RandomBMVanilla', '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"));

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a vanilla random formula (5 dice, no swing dice, no skills)'
WHERE name='RandomBMVanilla';