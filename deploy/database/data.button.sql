DELETE FROM button_set;
INSERT INTO button_set (name) VALUES
('Soldiers'),
('Brom');

DELETE FROM button;
INSERT INTO button (name, recipe, tourn_legal, set_id) VALUES
('Avis', '4 4 10 12 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Hammer', '6 12 20 20 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Bauer', '8 10 12 20 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Stark', '4 6 8 X X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Clare', '6 8 8 20 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Kith', '6 8 12 12 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Karl', '4 6 6 20 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Iago', '20 20 20 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Niles', '6 10 10 12 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Shore', '4 4 20 20 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Hannah', '8 10 10 10 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Kublai', '4 8 12 20 X', 1, (SELECT id FROM button_set WHERE name="Soldiers")),
('Changeling', 'X X X X X', 0, (SELECT id FROM button_set WHERE name="Soldiers"));
