INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(10002, 'RandomBMFixed', '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM")),
(10003, 'RandomBMMixed', '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"));

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed random formula (5 dice, no swing dice, two of them having a single skill chosen independently from c, f, and d)'
WHERE name='RandomBMFixed';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed random formula (5 dice, no swing dice, three skills chosen from all existing skills, with each skill dealt out twice randomly and independently over all dice)'
WHERE name='RandomBMMixed';

