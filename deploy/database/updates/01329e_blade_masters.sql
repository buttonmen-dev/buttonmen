# BLADE MASTERS (Bayani Caes)            INTRODUCES Parry (I); Focus, Poison, Speed, Trip
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(636, 'Arashi',    '(4) (6)  I(10)  f(12) (20)',          0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(637, 'Michie',    '(4) (8) (12)  z(12) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(638, 'Johnny',    '(6) t(6) I(8) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(639, 'Renee',     '(2) (2) (6)  I(10) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(640, 'Danny',     '(6) t(8) t(8) (20) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(641, 'Danielle',  '(6) (12)  I(12) (20) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(642, 'Scott',     '(8) I(8) (10) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(643, 'Macky',     '(4) (6) (10) (X) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(644, 'Magistra',  'I(6)  I(10)  I(10) I(X)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(645, 'Horace',    '(8) (10)  p(20) (20) (Z)',            0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(646, 'Kainar',    '(4) (4)  z(10) z(V) (V)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(647, 'Inez',      '(6) (6) I(6) (20) (X)',               0, 0, (SELECT id FROM buttonset WHERE name="Blademasters"));
