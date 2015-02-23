INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(10004, 'RandomBMAnime', '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"));

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed anime formula: 4 normal dice and 4 reserve dice, chosen from standard die sizes'
WHERE name='RandomBMAnime';
