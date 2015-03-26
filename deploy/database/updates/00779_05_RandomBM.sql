DELETE FROM button
WHERE set_id = (SELECT id FROM buttonset WHERE name="RandomBM");

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(10001, 'RandomBMVanilla', '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM")),
(10002, 'RandomBMAnime',   '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM")),
(10003, 'RandomBMMixed',   '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM")),
(10004, 'RandomBMFixed',   '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"));

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a vanilla random formula: 5 dice, no swing dice, no skills'
WHERE name='RandomBMVanilla';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed random formula: 5 dice, no swing dice, two of them having a single skill chosen from c, f, and d (the same skill on both)'
WHERE name='RandomBMFixed';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed random formula: 5 dice, no swing dice, three skills chosen from {cdfgkMnopqstvz}, with each skill dealt out twice randomly and independently over all dice)'
WHERE name='RandomBMMixed';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed anime formula: 4 normal dice and 4 reserve dice, chosen from standard die sizes'
WHERE name='RandomBMAnime';