INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id, sort_order) VALUES
(10010, 'RandomBMSoldiers',   '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 100);

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, similar to buttons in the Soldiers set: Four regular dice plus one X swing die, no skills on any dice.'
WHERE name='RandomBMSoldiers';
