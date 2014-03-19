# FIGHTBALL (PREVISOULY COMMENTED OUT)
# Echo and Gordo had their name changed, Pepper and Zal are new additions
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Brie',              '(4) (6) (8) (10) (12) (12) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Domino',            '(4) (4) (8) (8) (8) (10) (12)',           0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Echo(Fightball)',   '(4) (6) (6) (6) (12) (12) (12) (20)',     0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Georgia',           '(6) (6) (8) (8) (12) (20) (20)',          0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Gordo(Fightball)',  '(4) (6) (6) (8) (8) (10) (20)',           0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Mel',               '(4) (4) (8) (10) (10) (20) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Pavel',             '(6) (10) (10) (12) (12) (20) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Rocq',              '(6) (10) (10) (12) (20) (20) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Skinny',            '(4) (4) (4) (6) (8) (8) (10)',            0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Tex',               '(4) (4) (6) (8) (10) (10) (12) (12)',     0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
# Pepper and Zal are from the BM Trading Cards
('Pepper',            '(6) (10) (10) (10) (12) (12) (12) (16)',  0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Zal',               '(4) (6) (6) (16) (16) (20) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Fightball"));
