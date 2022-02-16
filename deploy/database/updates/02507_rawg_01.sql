INSERT INTO buttonset (id, name, sort_order) VALUES
(88, 'Durer Rawg', 8800);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(810, 'Anna Lee',        '(8) (6,6) (20) p(30) (U)',                       0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(811, 'Ashley Jay',      '',                                               1, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(812, 'Bishop',          '(6) (20) (30) (X) `(8) `(12)',                   0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(813, 'Bozo',            'p(8,8) p(12,12) p(20,20) p(30,30) p(X,X)',       0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(814, 'Brad',            'M(8) g(12) (20) (P)',                            0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(815, 'Brenda',          '(1) (1) M(2) M(3) (V)',                          0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(816, 'Brent',           'g(4) (12) g(12) (20) (X)',                       0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(817, 'Cardie',          'g(8) g(8) (8,8) (12) (X)',                       0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(818, 'Cherry',          '(6) (8) (T) M(T) `(4) `(8)',                     0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(819, 'Chuck',           '(6,6) g(12) (X) (X) (X)',                        0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(820, 'Cuddy',           '(4) (6) (8) (12) `(1) `(2)',                     0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(821, 'Dave',            '(6) (8) g(8) (12,12) (X)',                       0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(822, 'Derks',           'M(8) M(12) (20) (P)',                            0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(823, 'Dickie',          '(4,4) (6,6) (8,8) (Y,Y)',                        0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(824, 'Don',             'g(4) (20) (X) (X) (X)',                          0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(825, 'Gorman',          '(4) (6) (12) (20) `(X) `(Y)',                    0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(826, 'Hedy',            '(X) (X) p(Y) g(Y)',                              0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(827, 'Hunter',          '(12) (8,8) p(30) p(30) g(X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(828, 'Jamie',           'H(6) H(12) (20) (V,V)',                          0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(829, 'Maggie',          '(20) (20) (20) (30) (20/30)',                    0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(830, 'Matt Ron',        '(12) (12) (20) (20) p(P)',                       0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(831, 'Orry',            'b(4) b(4) b(12) b(P)',                           1, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(832, 'Portia',          'w(6) w(12) w(12) w(12)',                         1, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(833, 'Reilly',          'g(4) H(6) (8) (V,V) `(4) `(6)',                  0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(834, 'Smoot',           '(6) (12) g(12) (20) p(X)',                       0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(835, 'Stamps',          '(4) (6) (8,8) g(12) o(Y,Y)',                     0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg")),
(836, 'Zedna',           '(8) (30) (30) (30) (U)',                         0, 0, (SELECT id FROM buttonset WHERE name="Durer Rawg"));

UPDATE button SET flavor_text="Anna Lee is an undead tyrant with a rotten streak. She likes long arms, sharp teeth, and beating people up." WHERE name="Anna Lee";
UPDATE button SET flavor_text="Ashley Jay is almost whatever you want them to be, but not quite. They like tartar sauce, roasted puppies, and beating people up." WHERE name="Ashley Jay";
UPDATE button SET flavor_text="Bishop is an academic heavy metal singer. He likes civil war, wrestling, and beating people up." WHERE name="Bishop";
UPDATE button SET flavor_text="Bozo is a band manager and weekly call-in podcast host. He likes big hair, big chins, and beating people up." WHERE name="Bozo";
UPDATE button SET flavor_text="Brad plays with dogs and sticks all day, sometimes at the same time. He likes drums, flatulence, and beating people up." WHERE name="Brad";
UPDATE button SET flavor_text="Brenda is a fiery woman who was vegetarian for a month. She likes rare steak, the number five, and beating people up." WHERE name="Brenda";
UPDATE button SET flavor_text="Brent is a musician who cannot find his glasses. He likes his country, novelty nail files, and beating people up." WHERE name="Brent";
UPDATE button SET flavor_text="Cardie is a former nun who likes airplanes, outer space, and beating people up." WHERE name="Cardie";
UPDATE button SET flavor_text="Cherry is a burlesque performer and gymnast who likes eating non-foods, non-dairy desserts, and beating people up." WHERE name="Cherry";
UPDATE button SET flavor_text="Chuck is a leather making enthusiast. He likes whips, seafood, and beating people up." WHERE name="Chuck";
UPDATE button SET flavor_text="Albrecht Cuddy is a dwarf with excess salivation. They like court dramas, cherry pie, and beating people up." WHERE name="Cuddy";
UPDATE button SET flavor_text="Dave is an offensive cosplay enthusiast who likes yelling at crowds, being protested, and beating people up." WHERE name="Dave";
UPDATE button SET flavor_text="Derks is a master chef who enjoys the challenges of fixing food for vegans, bear trap headgear, and beating people up." WHERE name="Derks";
UPDATE button SET flavor_text="Dickie works as a fast food mascot and enjoys banana splits, MC Chris, and beating people up." WHERE name="Dickie";
UPDATE button SET flavor_text="Don has the sickest last name of anyone you will ever meet. He likes coke, shotguns, and beating people up." WHERE name="Don";
UPDATE button SET flavor_text="Gorman is a puppeteer with no sense of direction. He likes turtles, soft sculpture, and beating people up." WHERE name="Gorman";
UPDATE button SET flavor_text="Hedy is a doctor specializing in flesh reconstruction. They like ambiguity, classic pin-ups, and beating people up." WHERE name="Hedy";
UPDATE button SET flavor_text="Hunter is a cannabis activist and comic book artist. He likes cosplay, gigantic novelty wrenches, and beating people up." WHERE name="Hunter";
UPDATE button SET flavor_text="Jamie is a former star and current bus driver. He likes peanut butter and mayonnaise sandwiches, slightly burned french fries, and beating people up." WHERE name="Jamie";
UPDATE button SET flavor_text="Maggie is a chiropodist who specializes in maggot therapy. She likes lost souls, telethons, and beating people up." WHERE name="Maggie";
UPDATE button SET flavor_text="Matt Ron is the lost son of Anna Lee. He and his alternate personality Deborah like chainsaws, Pascal's pressure principle, and beating people up." WHERE name="Matt Ron";
UPDATE button SET flavor_text="Orry is an artist with a heart of mud. He likes Roman military history, the Grand Canyon, and beating people up." WHERE name="Orry";
UPDATE button SET flavor_text="Portia is a professional plumber who really enjoys her work. She likes clean toilets, monsters based on common household objects, and beating people up." WHERE name="Portia";
UPDATE button SET flavor_text="Reilly is a copyright hawk with a heart of hair. He likes ceasing, desisting, and beating people up." WHERE name="Reilly";
UPDATE button SET flavor_text="Smoot is giant with major flatulence problems. He enjoys poutine, colorful hair brushes, and beating people up." WHERE name="Smoot";
UPDATE button SET flavor_text="Stamps is a pyromaniac singer. She likes fire, electricity, and beating people up." WHERE name="Stamps";
UPDATE button SET flavor_text="Zedna easily learns anything within two and 17/18ths of a month. She likes big butts, defenestration, and beating people up." WHERE name="Zedna";
