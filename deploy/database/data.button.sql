DELETE FROM buttonset;
INSERT INTO buttonset (name) VALUES
('Soldiers'),
('Brom');

DELETE FROM button;
INSERT INTO button (name, recipe, tourn_legal, set_id) VALUES
('Bane',        'p(2) p(4) (12) (12) (V)',       1, (SELECT id FROM buttonset WHERE name="Brom")),
('Bluff',       'ps(6) ps(12) (16) (20) (X)',    1, (SELECT id FROM buttonset WHERE name="Brom")),
('Coil',        'p(4) (12) p(20) (20) (V)',      1, (SELECT id FROM buttonset WHERE name="Brom")),
('Crusher',     '(10) p(20) (20) (20) (X)',      1, (SELECT id FROM buttonset WHERE name="Brom")),
# ('Echo',        '(?) (?) (?) (?) (?)',         1, (SELECT id FROM buttonset WHERE name="Brom")),
('Giant',       '(20) (20) (20) (20) (20) (20)', 1, (SELECT id FROM buttonset WHERE name="Brom")),
('Grist',       'p(4) (8) (10) (12) (X)',        1, (SELECT id FROM buttonset WHERE name="Brom")),
('Jellybean',   'p(20) s(20) (V) (X)',           1, (SELECT id FROM buttonset WHERE name="Brom")),
('Lucky',       '(6) (10) p(12) (20) (X)',       1, (SELECT id FROM buttonset WHERE name="Brom")),
('Peace',       's(10) s(12) s(20) s(X) s(X)',   1, (SELECT id FROM buttonset WHERE name="Brom")),
('Reaver',      '(4) (10) (10) (12) p(X)',       1, (SELECT id FROM buttonset WHERE name="Brom")),
('Shepherd',    '(8) (8) p(16) (20) (X)',        1, (SELECT id FROM buttonset WHERE name="Brom")),
('Strik',       '(8) p(10) s(16) (16) (X)',      1, (SELECT id FROM buttonset WHERE name="Brom")),
('Wastenott',   's(4) s(8) s(10) s(20) s(X)',    1, (SELECT id FROM buttonset WHERE name="Brom")),
('Avis',        '(4) (4) (10) (12) (X)',    1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Hammer',      '(6) (12) (20) (20) (X)',   1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Bauer',       '(8) (10) (12) (20) (X)',   1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Stark',       '(4) (6) (8) (X) (X)',      1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Clare',       '(6) (8) (8) (20) (X)',     1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Kith',        '(6) (8) (12) (12) (X)',    1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Karl',        '(4) (6) (6) (20) (X)',     1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Iago',        '(20) (20) (20) (X)',       1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Niles',       '(6) (10) (10) (12) (X)',   1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Shore',       '(4) (4) (20) (20) (X)',    1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Hannah',      '(8) (10) (10) (10) (X)',   1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Kublai',      '(4) (8) (12) (20) (X)',    1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Changeling',  '(X) (X) (X) (X) (X)',      0, (SELECT id FROM buttonset WHERE name="Soldiers"));
