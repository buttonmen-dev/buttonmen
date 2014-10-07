# ORDER OF THE DOLLS              INTRODUCES Assassin(a); Twin
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(673, 'Chaka',     'a(4) (8) (8) (12) a(30)',             0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls")),
(674, 'Strotzie',  '(4) (6) a(10) (12) a(X)',             0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls")),
(675, 'Fuma',      'a(8) (16) (20) (V) (Z)',              0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls")),
(676, 'Vox',       '(6) a(6) (12) (12) a(V,V)',           0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls"));
