INSERT INTO buttonset (id, name, sort_order) VALUES
(79, 'The Core', 50),
(80, 'West Side', 51),
(81, 'The Delta', 52),
(82, 'Uptown', 53);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(718, 'Delia',           '(4) (4) (12) (20) (X)',  0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(719, 'Tony (The Core)', '(8) (8) (20) (20) (X)',  0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(720, 'Hollis',          '(6) (6) (20) (X) (X)' ,  0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(721, 'Porter',          '(8) (12) (12) (20) (X)', 0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(722, 'Donna',           '(4) (8) (12) (12) (X)',  0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(723, 'Ferrer',          '(4) (8) (20) (20) (X)',  0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(724, 'Hamilton',        '(4) (6) (6) (12) (X)',   0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(725, 'Polly',           '(4) (4) (8) (20) (X)',   0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(726, 'Lady B',          '(4) (12) (12) (20) (X)', 0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(727, 'Smith',           '(6) (8) (8) (12) (X)',   0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(728, 'Wallace',         '(6) (6) (20) (20) (X)',  0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(729, 'Tanya',           '(4) (6) (12) (X) (X)',   0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(730, 'Stefano',         '(6) (8) (12) (20) (X)',  0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(731, 'Janet',           '(4) (12) (20) (20) (X)', 0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(732, 'Steve (The Core)','(6) (6) (12) (12) (X)',  0, 1, (SELECT id FROM buttonset WHERE name="The Core")),
(733, 'Marin',              '(4) p(4) (8) (12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(734, 'Carson (West Side)', '(8) (12) (20) p(20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(735, 'Jackie',             'p(4) (6) (8) (12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(736, 'Marta',              '(6) (12) (12) p(12) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(737, 'Bijay',              '(4) (8) (12) (20) p(X)',     0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(738, 'Sal',                '(8) (8) p(8) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(739, 'Dietrich',           'p(6) p(8) (20) (20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(740, 'Hazel',              '(4) (6) p(6) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(741, 'Rider',              '(8) p(8) (12) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(742, 'Mickael',            'p(4) p(6) (8) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(743, 'Beatrice',           '(4) (4) p(4) (12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(744, 'Stacie',             '(6) (8) (12) (X) p(X)',      0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(745, 'Prentice',           'p(4) p(8) (12) (20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(746, 'Sally',              '(6) (6) p(12) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(747, 'Mondo',              '(4) (12) (20) p(20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="West Side")),
(748, 'Jocasta',            '(6) (12) s(12) (20) s(X)',   0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(749, 'Lady K',             '(4) s(6) (8) s(8) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(750, 'Keegan',             '(8) s(8) s(12) (20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(751, 'Benvenito',          '(4) (8) (8) s(20) s(X)',     0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(752, 'Pilot Joe',          '(4) s(4) s(6) s(20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(753, 'Gilroy',             's(4) (6) s(8) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(754, 'Jerry',              '(6) (8) (8) s(12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(755, 'Fiorina',            '(6) s(6) (12) (12) s(X)',    0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(756, 'Doctor Fret',        '(4) (4) (12) s(X) s(X)',     0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(757, 'Min-Szu',            '(4) (6) s(8) (20) s(X)',     0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(758, 'Szechuan',           's(4) s(6) s(8) s(12) (X)',   0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(759, 'Felicia',            '(6) (8) s(8) s(20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(760, 'Brand',              's(4) (6) s(12) s(20) (X)',   0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(761, 'Windsor',            '(6) s(6) (12) s(20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(762, 'Cerise',             's(8) (12) (12) (20) s(X)',   0, 1, (SELECT id FROM buttonset WHERE name="The Delta")),
(763, 'Ricky',              '#(6) (8) #(8) (20) #(X)',    0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(764, 'Giuseppe',           '(8) (8) (12) #(12) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(765, 'Heather (Uptown)',   '(6) (6) #(8) (20) #(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(766, 'Clyde',              '(12) #(12) (20) (20) #(X)',  0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(767, 'Tasha',              '(4) (6) #(6) #(8) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(768, 'Ken (Uptown)',       '(4) #(8) #(12) (20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(769, 'Mississippi (Uptown)','#(6) #(8) #(12) #(20) #(X)', 0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(770, 'Petey',              '(4) (6) (12) #(20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(771, 'Bobby',              '(6) (8) (20) #(20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(772, 'Amelia',             '(4) (6) (6) (8) #(X)',       0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(773, 'Basil',              '(6) #(8) (12) #(12) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(774, 'Jesse',              '#(4) (4) (20) #(20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(775, 'Henry',              '(4) (8) #(12) #(20) #(X)',   0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(776, 'Stubbs',             '(12) #(12) (20) (X) #(X)',   0, 1, (SELECT id FROM buttonset WHERE name="Uptown")),
(777, 'Montserrat',         '(4) #(6) #(8) #(12) #(X)',   0, 1, (SELECT id FROM buttonset WHERE name="Uptown"));