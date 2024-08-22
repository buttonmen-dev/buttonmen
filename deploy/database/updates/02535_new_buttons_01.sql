INSERT INTO buttonset (id, name, sort_order) VALUES
(89,  '2010 Rare / Promo', 8900),
(90,  'Standard Action',   9000),
(91,  'Button Ponies',     9100),
(92,  'Scott Pilgrim',     9200),
(93,  'Cryptonom',         9300);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(837,  'Julz',         '(4) (4) (8) (12) z(20)',        0, 0, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo")),
(838,  'Eberk',        '(2) (3) (6) (7) (10/16)',       0, 0, (SELECT id FROM buttonset WHERE name="2010 Rare / Promo")),
(839,  'Bernie',       'D(4) (20) (4/8) (6/8) D(S)',    0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
(840,  'GG',           '(6) (6) D(12) D(24) (S)',       0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
(841,  'Wumpus',       '(4) (8) (8) (X) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
(842,  'Edda',         '(4) (8) (10) (16) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Standard Action")),
(843,  'Fernando',     '(6) (12) (12) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Standard Action")),
(844,  'Applejack',    'p(6) p(10) g(12) (20) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(845,  'Chrysalis',    'm(4) m(6) m(8) m(10) m(12)',    0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(846,  'Fluttershy',   '(6) G(8) =(10) (X) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(847,  'Pinkie Pie',   '(4) z(6) (12) o(20) q(X)',      0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(848,  'Rainbow Dash', '(8) z(8) z(10) sp(20) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(849,  'Rarity',       '(4) I(8) I(8) o(12) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(850,  'Twilight Sparkle', '(4) G(8)  F(10) T(12) (X)', 0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(851,  'Changeling (BP)', '(X) (X) (X) (X) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(852,  'Angel (BP)',    '(1) (1) (1) (1) (X)',          0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(853,  'Parasprites',   '(2) (2) (2) (2) (X)',          0, 0, (SELECT id FROM buttonset WHERE name="Button Ponies")),
(854,  'Envy Adams',     '(6) (10) (12) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(855,  'Final Form GGG', '(20) (20) (20) (X) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(856,  'Gideon G Graves','(4) (6) (10) (X) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(857,  'Lucas Lee',      '(6) (10) (12) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(858,  'Matthew Patel',  '(4) (4) (10) (10) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(859,  'Nega Scott',     '(4) (8) (10) (20) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(860,  'Ramona Flowers', '(4) (8) (8) (10) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(861,  'Scott Pilgrim',  '(4) (10) (12) (12) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(862,  'Roxy Richter',   '(6) (8) (10) (20) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(863,  'Todd Ingram',    '(8) (10) (10) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(864,  'Katayanagi Twins', '(10) (10) (10) (10) (X)',   0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(865,  'Knives Chau',    '(4) (6) (12) (12) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Scott Pilgrim")),
(866,  'Enjileon',       'Hv(2,2) v(R)! v(T,T)? vp(X,X)',0,0, (SELECT id FROM buttonset WHERE name="Cryptonom"));

UPDATE button SET flavor_text="Twilight Sparkle is a unicorn scholar of all things magic and the various ways of friendship. A natural leader, she likes her little brother Spike, getting lost in a good book, and beating people up." WHERE name="Twilight Sparkle";
UPDATE button SET flavor_text="Applejack is an honest, hard working Earth pony who spends most of her time bucking apple trees on her family apple orchard. She likes her family, her pet dog Winona, and beating people up." WHERE name="Applejack";
UPDATE button SET flavor_text="Changelings are a race of equine creatures who possess insect-like characteristics. Before the Great Metamorphosis, changelings enjoyed feeding on love, obeying their Queen, and beating people up." WHERE name="Changeling (BP)";
UPDATE button SET flavor_text="Chrysalis is the queen of the Changelings. She is taller than her subjects, but just as ruthless. Before being dethroned, Chrysalis enjoyed manipulating her enemies, commanding her swarm, and beating people up." WHERE name="Chrysalis";
UPDATE button SET flavor_text="Fluttershy is a kind pegasus who is known for her tender, nurturing nature. While mild-mannered, she has the respect of the creatures under her care. She enjoys her pet rabbit Angel, being in nature, and beating people up." WHERE name="Fluttershy";
UPDATE button SET flavor_text="Angel is the temperamental pet rabbit of Fluttershy. He is prone to conniving and willful behavior and is very particular about how his tail is fluffed. He doesn't hate carrots, Fluttershy, and beating people up." WHERE name="Angel (BP)";
UPDATE button SET flavor_text="Parasprites are ravenous little flying creatures who possess the strange ability to reproduce quickly. While they were in Ponyville, they enjoyed eating everything in sight, one-pony band music, and beating people up." WHERE name="Parasprites";
UPDATE button SET flavor_text="Pinkie Pie is a bouncy, fun-loving Earth pony who is known for being able to produce laughter and smiles from even the crankiest creatures. She enjoys planning parties, her pet alligator Gummy, and beating people up." WHERE name="Pinkie Pie";
UPDATE button SET flavor_text="Rainbow Dash is a daring Pegasus whose competitive nature is almost as great as her loyalty to her friends. When not managing the weather, she likes the Wonderbolts, her pet tortoise Tank, and beating people up." WHERE name="Rainbow Dash";
UPDATE button SET flavor_text="Rarity is a sophisticated unicorn who can often be found in her haute couture studio designing a fabulous new lineup. Known for her generosity, Rarity likes her pet cat Opal, producing fashion shows, and beating people up." WHERE name="Rarity";
