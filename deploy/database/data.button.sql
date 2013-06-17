DELETE FROM button_sets;
INSERT INTO button_sets (name) VALUES
('Soldiers'),
('Brom');

DELETE FROM button_definitions;
INSERT INTO button_definitions (name, recipe, tourn_legal, set_id) VALUES
('Avis', '4 4 10 12 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Hammer', '6 12 20 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Bauer', '8 10 12 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Stark', '4 6 8 X X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Clare', '6 8 8 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Kith', '6 8 12 12 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Karl', '4 6 6 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Iago', '20 20 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Niles', '6 10 10 12 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Shore', '4 4 20 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Hannah', '8 10 10 10 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Kublai', '4 8 12 20 X', 1, (SELECT id FROM button_sets WHERE name="Soldiers")),
('Changeling', 'X X X X X', 0, (SELECT id FROM button_sets WHERE name="Soldiers"));
