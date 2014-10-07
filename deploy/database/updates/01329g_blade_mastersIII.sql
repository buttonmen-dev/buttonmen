#BLADE MASTERS 3
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(654, 'Rico',         '(6) (8) |(10) |(20) (S)',          0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(655, 'Seiji',        '(4) (4) F(10) f(12) (16)',         0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(656, 'Yamaichi',     '(2) (16) (20) f(X)!',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(657, 'Tommy',        '-(4) (8) -(8) (20) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(658, 'Ace',          '(4) (6) p(16) (16) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(659, 'Poison',       '(6) (10) -p(V) |p(V) p(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(660, 'Irene',        'x(3) x(4) s(8) (12) (Z)',          0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(661, 'Fuyuko',       'q(4) n(6) q(10) (20) (X)',         0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(662, 'Montgomery',   '(6) n(8) n(12) (20) (X)',          0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(663, 'Jean-Paul',    '(4) I(8) z(12) Iz(W)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(664, 'Montague(u)',  '(2,2) (4) (10,10) (20) (X) +(V)',  0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(665, 'Chantal',      'pI(4) pI(8) (20) (20) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
# Silly Self Promo (yes, that's a fudge die)  INTRODUCES Sustaind Fire Die (SFD)
(666, 'Bayani',       '(F) (SFD) (16) (16) (12/20)',      1, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack"));
