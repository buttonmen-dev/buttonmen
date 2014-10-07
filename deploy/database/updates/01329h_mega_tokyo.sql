# MEGATOKYO (Dreamshade - MegaTokyo forums)  INTRODUCES Full Auto Dice(P); Turbo, Speed, Mood Swing, Poison, Shadow, Option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
# Largo cannot use skill attacks.
(667, 'Largo',            '(12) (20) (20) (X)',              1, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(668, 'Ping',             '(4) (8) (X)! (X)!',               0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(669, 'Piro',             '(4) (8) (8) z(X)? z(X)?',         0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(670, '\"Darkly Cute\"',  '(4) p(8) s(10) p(12) s(X)',       0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(671, 'Dom',              '(10) (10) P(20) P(20) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(672, 'Erika',            'z(10) z(12) z(12) z(4/20)!',      0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo"));
