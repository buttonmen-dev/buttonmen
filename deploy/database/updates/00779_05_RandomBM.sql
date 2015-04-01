START TRANSACTION;

UPDATE game_player_map SET button_id = 20000 WHERE button_id=(SELECT id FROM button WHERE name = "RandomBMFixed");
UPDATE game_player_map SET button_id = 20001 WHERE button_id=(SELECT id FROM button WHERE name = "RandomBMAnime");

DELETE FROM button WHERE name="RandomBMFixed";
DELETE FROM button WHERE name="RandomBMAnime";

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(10002, 'RandomBMAnime', '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM")),
(10004, 'RandomBMFixed', '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"));

UPDATE game_player_map SET button_id = (SELECT id FROM button WHERE name = "RandomBMFixed") WHERE button_id=20000;
UPDATE game_player_map SET button_id = (SELECT id FROM button WHERE name = "RandomBMAnime") WHERE button_id=20001;

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed random formula: 5 dice, no swing dice, two of them having a single skill chosen from c, f, and d (the same skill on both)'
WHERE name='RandomBMFixed';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed random formula: 5 dice, no swing dice, three skills chosen from {cdfgkMnopqstvz}, with each skill dealt out twice randomly and independently over all dice)'
WHERE name='RandomBMMixed';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed anime formula: 4 normal dice and 4 reserve dice, chosen from standard die sizes'
WHERE name='RandomBMAnime';

COMMIT;
