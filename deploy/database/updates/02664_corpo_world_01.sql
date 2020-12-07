INSERT INTO buttonset (id, name, sort_order) VALUES
(86, 'CyberSuit Corp', 8600);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(797, 'Andrew',          'ks(6) fI(8) (12) (12) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(798, 'Chad',            '(4) ks(4) It(12) (20) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(799, 'Fiona',           'st(4) fM(8) (12) (12) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(800, 'Gavin',           'fM(6) Is(8) (12) (20) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(801, 'Isabel',          '(6) It(8) fs(10) (12) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(802, 'Monique',         '(8) kI(8) fs(12) (12) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(803, 'Nadia',           'fI(6) st(10) (10) (12) (V)',                     0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(804, 'Sachin',          '(6) kt(6) Is(10) (20) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp"));
