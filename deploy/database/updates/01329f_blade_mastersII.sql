#BLADE MASTERS 2                             INTRODUCES Attacker(-), Defender(|), Cross(x); 
#                                                       Option, Reserve, Turbo, Twin, Fire, Poison, Shadow, Speed, Trip
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(648, 'Paris',    '(4/8)! (6) (10) (10) (12/20)!',           0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(649, 'Gideon',   '(4) (8) (12) (X) r(4) r(6) r(10) r(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(650, 'Spider',   't(4) p(6) s(8) z(10) (R)',                0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(651, 'Painter',  'x(2) (4) (8) (12) (X)',                   0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(652, 'Regina',   '(1) (6) t(4,4) (12) (Y)',                 0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(653, 'Damian',   '-(10) |(10) F(10) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot"));
