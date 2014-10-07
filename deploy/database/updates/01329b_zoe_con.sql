# ZOECon (ZOECon.net)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(574, 'Carrow',          's(4) s(8) s(12) s(20) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(575, 'Zara',            '(6) (8) (12) (20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(576, 'Peri',            '(6) (6) (10) (X) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(577, 'Glorion',         '(10) (12) (16) (20) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(578, 'The Assassin',    '(6) (10) p(10) (12) s(X)',          0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(579, 'Wren',            '(4) (8) (12) (12) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="ZOECon"));
