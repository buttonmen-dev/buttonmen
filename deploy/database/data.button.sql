DELETE FROM button_tag_map;

DELETE FROM tag;

DELETE FROM buttonset;
INSERT INTO buttonset (id, name, sort_order) VALUES
# Official Sets (in order of release)
(1,  'Soldiers',                      100),
(2,  'The Big Cheese',                200),
(3,  'Sanctum',                       300),
(4,  'Lunch Money',                   400),
(5,  '1999 Rare / Promo',             500),
(6,  'Vampyres',                      600),
(7,  'Legend of the Five Rings',      700),
(8,  'Polycon',                       800),
(9,  'BROM',                          900),
(10, '2000 Rare / Promo',             1000),
(11, 'BRAWL',                         1100),
(12, 'Buttonlords',                   1200),
(13, 'Studio Foglio',                 1300),
(14, 'Sailor Moon 1',                 1400),
(17, 'ButtonBroccoli',                1500),
(15, 'Freaks',                        1600),
(16, 'Balticon 34',                   1700),
(26, '2000 ShoreCon',                 1800),
(18, 'Las Vegas',                     1900),
(19, 'Fantasy',                       2000),
(21, 'Save The Ogres',                2100),
(23, 'Presidential',                  2200),
(24, 'Majesty',                       2300),
(25, 'Wonderland',                    2400),
(27, 'Fairies',                       2500),
(28, 'Dork Victory',                  2600),
(29, 'Japanese Beetle',               2700),
(30, 'Howling Wolf',                  2800),
(31, 'Metamorphers',                  2900),
(32, 'Sailor Moon 2',                 3000),
(33, 'Bruno',                         3100),
(34, 'Tenchi Muyo!',                  3200),
(35, 'Sluggy Freelance',              3300),
(59, 'Everything to Gain',            3400),
(22, 'Yoyodyne',                      3500),
(36, 'Samurai',                       3600),
(37, 'SydCon 10',                     3700),
(38, 'Demicon the 13th',              3800),
(46, 'SFR',                           3900),
(44, 'Chicagoland Gamers Conclave',   4000),
(39, 'Diceland',                      4100),
(20, 'Renaissance',                   4200),
(40, '2002 Anime Expo',               4300),
(41, '2002 Origins',                  4400),
(42, 'Bar Mitzvah',                   4500),
(45, '2003 Rare / Promo',             4600),
(43, 'Button Brains',                 4700),
(61, 'Fightball',                     4800),
(62, 'Nodwick',                       4900),
(47, '2004 Origins',                  5000),
(51, 'Geekz',                         5100),
(66, 'Kubla Con',                     5200),
(48, 'Space Girlz',                   5300),
(49, 'Bridge and Tunnel',             5400),
(50, '2005 Rare / Promo',             5500),
(76, 'Big Top',                       5550),
(63, 'High School Drama!',            5600),
(64, 'Unexploded Cow',                5700),
(67, 'ZOECon (JourneyQuest)',         5800),
(79, 'The Core',                        50),
(80, 'West Side',                       51),
(81, 'The Delta',                       52),
(82, 'Uptown',                          53),
(83, '2017 Rare / Promo',               54),

# Button Men Online sets - virtual fan and vanity sets
(52, 'Iron Chef',                     5900),
(53, '7 deadly sins',                 6000),
(54, 'Chicago Crew',                  6100),
(55, 'Four Horsemen',                 6200),
(56, 'Free Radicals',                 6300),
(57, 'Hodge Podge',                   6400),
(58, 'Victorian Horror',              6500),
(68, 'Cowboy Bebop',                  6600),
(69, '50 States',                     6700),
(78, 'Steven Universe',               7600),
(77, 'Zodiac',                        7500),
(84, 'Peloton',                       8400),
(86, 'CyberSuit Corp',                8600),

# Unofficial Sets - fan and vanity sets
(70, 'Japanese Beetle (unofficial)',  6800),
(71, 'Blademasters',                  6900),
(72, 'Order of Dolls',                7000),
(73, 'Blademasters: The Second Shot', 7100),
(74, 'Blademasters: Third Attack',    7200),
(60, 'Gaming Guardians',              7300),
(75, 'MegaTokyo',                     7400),

# Fanatics
(65, 'Classic Fanatics', 100000),
(85, '2020 Fanatics',    100100),
(87, '2021 Fanatics',    100200),

# Special
(10000, 'RandomBM', 200000),
(20000, 'CustomBM', 20);


DELETE FROM button;

# 1999 RARE-PROMO                       INTRODUCES Turbo(!) Swing Dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
# ZEPPO from 1999 Origins
(1, 'Zeppo',       '(4) (12) (20) (X)!',              0, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# Eiko & Wu Lan from Furthest North Crew / Toivo Rovainen / Cheapass
(2, 'Eiko',        '(4) (6) (6) (12) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
(3, 'Wu Lan',      '(4) (10) (20) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# BUZZING WEASEL's recipe stands for(Fudge) (Regular) (Prestige) (Gamer) (Screw), which are all dice particular to this button
# rather than any standard coding for swing sizes or die skills.  IMO these dice should not be made avialable for other buttons.
# F: "Fudge" die. Rolls -1, 0, and 1 in a linear distribution. Is worth either 0, 1, or 3 points depending on who you talk to.
#    Zero, because that's twice its average roll; One, because that's its highest value; Three because it can vary between
#    3 different values.
# R: "Regular" die. Equivalent to an "X" Swing Die.
# P: "Prestige" die. A variable die which can be any size between 30 and 100 sides, the Prestige Die cannot make any Attacks
#     and is not worth any points.
# G: "Gamer" die. A variable die which can be any size between 3 and 21, except for the common die sizes of 4, 6, 8, 10, 12, and 20.
# S: "Screw" or "Suck" die. A 7-sided die. If the S die makes an attack and rolls odd, its owner gets another turn. If it rolls even,
#     its owner loses.
# ('Buzzing Weasel','F R P G S',                   1, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# JAMES ERNEST is mathematically impossible to implement (though it might be fun to try to work around this, changing sizes to die skills)
# ('James Ernest','(pi) (inf) (sqrt(-2)) (X)',     1, 0, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# Carson from 1999 GenCon
(4, 'Carson (promo)', '(4) (5) (6) (7) (X)',          0, 0, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo"));

# 2000 RARE-PROMO
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
# Gordo from Button Men Poster 1999 (Cheapass Games)
# None of Gordo's dice can be the same size
(5, 'Gordo',               '(V) (W) (X) (Y) (Z)',                1, 0, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo")),
# Me am ORK! from Orc! (Green Ronin)
(6, 'Me am ORK!',           '(8) (8) (8) p(8) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo")),
# Rikachu Origins 2000 (Origins)
(7, 'Rikachu',             '(1) (1) (1) (1) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo"));

# 2000 SHORECON (ShoreCon)            NO SPECIAL DIE SKILLS
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(8, 'ConMan',     '(4) (6) p(20)',      0,  0, (SELECT id FROM buttonset WHERE name="2000 ShoreCon"));

# 2002 ANIME EXPO                                NO SPECIAL DICE SKILLS
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(9, 'MAX (promo)',     '(4) (6) (18) (20) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="2002 Anime Expo"));

# 2002 Origins (Cheapass Games)                       SKILLS: Stinger(g) on old site)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(10, 'Apples',       '(8) (8) (2/12) (8/16) (20/24)',       0, 0, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave")),
(11, 'Green Apple',  '(8) (10) (1/8) (6/12) (12/20)',       0, 0, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave"));

# 2003 Rare / Promo (lacking information about this set except for one button, Apples and Green Apples were once here)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(12, 'Abe Caine',    'p(4,4) p(8,8) (10) ps(24) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="2003 Rare / Promo"));

# 2004 ORIGINS (Flying Buffalo)
#    INTRODUCES Sleep(Z) dice AND Game(#) dice; Fire(F); Poison(p); Shadow(s); Slow(w); Speed(z); Value(v); Option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(13, 'Amara Wintersword',      '(4) (8) (12) (12) (X)?',                          0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(14, 'Beatnik Turtle',         'wHF(4) (8) (10) vz(20) vz(20)',                   0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(15, 'Captain Bingo',          '(8) (6/12) (6/12) (12/20) (X)',                   0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(16, 'Oni',                    '(4) (10) f(12) f(12) (V)',                        0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(17, 'Spite',                  'p(6) (6) (6) s(X) (X)',                           0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(18, 'Super Germ',             'p(10) p(12) p(16) p(20) p(24) p(30) p(30) p(X)',  0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(19, 'Cheese Weasel',          '(6) (8) (12) (16) (20)',                          1, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# The old site used raGe dice instead of Game dice in the following recipes.
(20, 'Delt',                    'R(4) R(4) (10) (12) R(X)',                        0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(21, 'Reggie',                  '(8) (10) R(12) (20) R(20)',                       0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(22, 'Rold',                    '(6) (6) R(6) R(6) R(6)',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# since old site users are used to playing these with Rage . . .
(23, 'Delt Rage',               'G(4) G(4) (10) (12) G(X)',                       0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(24, 'Reggie Rage',             '(8) (10) G(12) (20) G(20)',                      0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(25, 'Rold Rage',               '(6) (6) G(6) G(6) G(6)',                         0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# not included on the old site - assumed not TL
(679, 'Killer Christmas Tree',  '(6) Z(6) (10) Z(12) (X)',                        0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(26, 'Billy Shakespeare',       '(15) (64) (16) (16)',                            0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(27, 'Drew\'s Truck',           '(5) (10) (15) (20) (X)',                         0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(28, 'Igor (promo)',            '(8) (8) z(12) (20) (X)',                         0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(29, 'Mike Young',              '(X) (X) (Y) (Y)',                                0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins"));

# 2005 Rare / Promo                         NO SPECIAL SKILLS
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(30, 'Kitty Cat Seven',               '(4) (6) (8) (10) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare / Promo")),
(31, 'Sylvia Branham',                '(6) (6) (6) (X) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare / Promo")),
(680, 'Magical Witch Critical Miss',  '(6) (10) (10) (20) (X)?',    0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare / Promo"));

# 2017 Rare / Promo
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(778, 'Mutton Ben', 'p(8) #(12) s(20) (X) (X)', 0, 0, (SELECT id FROM buttonset WHERE name="2017 Rare / Promo")),
(787, 'Jolene',     '(6) (6) (10) (18) (Z)',    0, 0, (SELECT id FROM buttonset WHERE name="2017 Rare / Promo"));

# BALTICON 34 (Balticon)                             INTRODUCES Option(/)       (2000 Rare / Promo on old site)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(32, 'Social Class',   '(4/6) (6/8) (8/10) (10/12) (12/20)',        0, 1, (SELECT id FROM buttonset WHERE name="Balticon 34"));

# BAR MITZVAH (Theodore Alper)                         SKILLS: Speed (z); Ornery (o)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(33, 'Bar Mitzvah Boy', '(6/13) (8) (10) f(13) f(30)',             0, 0, (SELECT id FROM buttonset WHERE name="Bar Mitzvah")),
(34, 'Judah Maccabee',  '(8) z(12) H(4) o(12) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="Bar Mitzvah"));

# THE BIG CHEESE (Cheapass Games)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(35, 'Bunnies',     '(1) (1) (1) (1) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="The Big Cheese")),
(36, 'Lab Rat',     '(2) (2) (2) (2) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="The Big Cheese"));

#BIGTOP (APE games - Cassandra)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(568, 'Firebreather',       '(4) F(6) F(6) (12) (S)',          0, 0, (SELECT id FROM buttonset WHERE name="Big Top")),
(569, 'Monkeys',            'z(6) z(6) z(6) z(10) z(T)',       0, 0, (SELECT id FROM buttonset WHERE name="Big Top")),
(570, 'Ringmaster',         'f(6) f(6) f(8) f(8) (12)',        0, 0, (SELECT id FROM buttonset WHERE name="Big Top")),
(571, 'Stumbling Clowns',   '(8) t(8) (10) t(10) (X)',         0, 0, (SELECT id FROM buttonset WHERE name="Big Top"));

# BRAWL (Cheapass Games)              INTRODUCES Speed(z) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(37, 'Bennett',     '(6) (8) z(20) z(20) (S)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(38, 'Chris',       'z(6) z(8) (10) z(12) (S)',            0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(39, 'Darwin',      '(4) (6) z(10) (20) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(40, 'Hale',        'z(8) (12) (20) (20) (S)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(41, 'Morgan',      'z(10) z(12) z(12) z(X)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(42, 'Pearl',       '(6) (8) (12) (X) z(X)',               0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(43, 'Sydney',      'z(4) z(6) z(8) z(10) z(X)',           0, 0, (SELECT id FROM buttonset WHERE name="BRAWL")),
# Brawl: Catfight Girls from 2001 Origins
(44, 'Angora',      'z(4) z(6) z(8) z(10) z(X)',           0, 0, (SELECT id FROM buttonset WHERE name="BRAWL")),
(45, 'Nickie',      'z(4) (10) (10) (12) z(12)',           0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(46, 'Sonia',       '(6) (6) z(12) (20) (20)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(47, 'Tamiya',      '(4) (8) (8) (12) z(20)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
# TESS: from Origins 2000 / Club Foglio;   INTRODUCES Null(n) Dice
(48, 'Tess',        'n(4) (8) (12) n(20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL"));

# BRIDGE AND TUNNEL (Bridge and Tunnel Software)  INTRODUCES Rebound(=) dice (not implemented); poison(p); shadow(s); option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(63,  'Agent Orange',     '(6) p(6) =(10) (4/12) (4/20)',        0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(64,  'Huitzilopochtli',  '(6) (8) =(10) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(65,  'Lisa',             '(4) (4) (30) (30)',                   0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(66,  'Nethershadow',     '(6) (10) s(10) (10/20) (6/30)',       0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(67,  'Phuong',           '(4) (10) (10) (20) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(68,  'Uncle Scratchy',   '(2) (4) (6) (10) (X)',                0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(681, 'Phantom Zero',     'g(8) s(10) (12) (2/12) (X)',          0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(682, 'Pinball Wizard',   '=(6) =(6) (20) (20)',                 0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(683, 'The Grotch',       'z(4) =(20) (V) (V) (V)',              0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(684, 'Steve',            '=(4) =(4) =(8) =(20) =(X)',           0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(685, 'The Fog',          's(4) s(4) (30) (30)',                 0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(686, 'G',                'g(6) =(6) z(8) (16) (10/20)',         0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel"));

# BROM (Cheapass Games)               INTRODUCES Poison(p)and Slow(w) dice; Shadow(s) dice; special rules for Echo
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(49, 'Coil',        'p(4) (12) p(20) (20) (V)',            0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(50, 'Bane',        'p(2) p(4) (12) (12) (V)',             0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(51, 'Lucky',       '(6) (10) p(12) (20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(52, 'Shepherd',    '(8) (8) p(16) (20) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(53, 'Peace',       's(10) s(12) s(20) s(X) s(X)',         0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(54, 'Crusher',     '(10) p(20) (20) (20) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(55, 'Grist',       'p(4) (8) (10) (12) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(56, 'Wastenott',   's(4) s(8) s(10) s(20) s(X)',          0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(57, 'Reaver',      '(4) (10) (10) (12) p(X)',             0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(58, 'Jellybean',   'p(20) s(20) (V) (X)',                 0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(59, 'Bluff',       'ps(6) ps(12) (16) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
(60, 'Strik',       '(8) p(10) s(16) (16) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
# Giant never goes first
(61, 'Giant',       '(20) (20) (20) (20) (20) (20)',       1, 1, (SELECT id FROM buttonset WHERE name="BROM")),
# Echo does not have it's own recipe - it copies the recipe of the opposing button
(62, 'Echo',        '',                                    1, 1, (SELECT id FROM buttonset WHERE name="BROM"));

# BRUNO (Hunter Johnson)             INTRODUCES Berserk(B) dice; *requires special rules
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
# Bruno gains (X) when facing Pappy
(69, 'Bruno',       'B(8) B(8) B(20) B(20) B(X)',   1, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
# Pappy gains B(X) when facing Bruno
(70, 'Pappy',       '(4) (4) (10) B(20) (X)',       1, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
(71, 'Synthia',     'B(4) (12) (12) (T) B(T)',      0, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
(72, 'The GM',      '(4) (8) (12) (16) B(U)',       0, 1, (SELECT id FROM buttonset WHERE name="Bruno"));

# BUTTON BRAINS (LinguaPlay)                         introduces Konstant(k) dice; option; twin
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(73, 'Al-Khwarizmi',           '(4) k(6) (8) (12) (20)',            0, 0, (SELECT id FROM buttonset WHERE name="Button Brains")),
(74, 'Carl Friedrich Gauss',   'k(6) (8) (8) (12,12) (20)',         0, 0, (SELECT id FROM buttonset WHERE name="Button Brains")),
(75, 'Fan Chung Graham',       '(4) k(6) (8) (10/20) (X)?',         0, 0, (SELECT id FROM buttonset WHERE name="Button Brains")),
(76, 'Ron Graham',             'k(6) (10) (12) (20) (V)?',          0, 0, (SELECT id FROM buttonset WHERE name="Button Brains")),
(77, 'Leonard Eugene Dickson', '(3) k(6) (10) (20) (W)',            0, 0, (SELECT id FROM buttonset WHERE name="Button Brains")),
(78, 'Paul Erdos',             '(3) (4) k(6) (12) (U)',             0, 0, (SELECT id FROM buttonset WHERE name="Button Brains")),
(79, 'Pythagoras',             'k(6) (8) (10) (12) (Z)',            0, 0, (SELECT id FROM buttonset WHERE name="Button Brains")),
(80, 'Theano',                 '(4) k(6) (8,8) (10) (S)',           0, 0, (SELECT id FROM buttonset WHERE name="Button Brains"));

# BUTTONBROCCOLI (Looney Labs)                     INTRODUCES Time & Space(^) Dice; Turbo Swing
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
# Tirade from Wunderland (Wunderland)
(81, 'Tirade',              '(6) ^(6) ^(10) (V)!',                0, 0, (SELECT id FROM buttonset WHERE name="ButtonBroccoli"));

# BUTTONLORDS (Green Knight)             INTRODUCES Auxilary(+) dice; Shadow(s)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(82, 'King Arthur',    '(8) (8) (10) (20) (X) +(20)',      0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
(83, 'Mordred',        's(6) (10) (10) s(20) s(X) +(4)',   0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
(84, 'Lancelot',       '(10) (12) (20) (20) (X) +(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
(85, 'Gawaine',        '(4) (4) (12) (20) (X) +(6)',       0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
(86, 'Morgan Le Fay',  's(4) (12) s(12) (20) (X) +(12)',   0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
(87, 'Guenever',       '(6) s(8) (10) (12) (X) +(8)',      0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
(88, 'Nimue',          '(4) (6) s(12) (20) (X) +s(10)',    0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
(89, 'Merlin',         '(2) (4) s(10) s(20) (X) +s(X)',    0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords"));

# THE CORE (Cheapass Games)
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
(732, 'Steve (The Core)','(6) (6) (12) (12) (X)',  0, 1, (SELECT id FROM buttonset WHERE name="The Core"));

# THE DELTA (Cheapass Games)                               SKILLS Shadow
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
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
(762, 'Cerise',             's(8) (12) (12) (20) s(X)',   0, 1, (SELECT id FROM buttonset WHERE name="The Delta"));

# DEMICON THE 13TH (DemiCon)                           SKILLS Shadow; Option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(90, 'The Effster',               's(4) (8) (8) s(12) s(X)',      0, 1, (SELECT id FROM buttonset WHERE name="Demicon the 13th")),
(91, 'The Fictitious Alan Clark', 's(8) s(8) (3/12) (20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="Demicon the 13th"));

# DICELAND                            INTRODUCES Stinger(g) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(92, 'Buck',        'g(8) g(10) (12) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Diceland")),
(93, 'Cass',        '(4) g(4) g(6) (12) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Diceland")),
(94, 'Z-Don',       'g(6) g(8) p(16) (X) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Diceland")),
(95, 'Crysis',      'g(8) (10) (10) (X) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Diceland")),
(96, 'Golo',        'g(10) g(12) g(20) g(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Diceland")),
# MICRO from 2002 Origins
(97, 'Micro',       'g(4) g(4) (12) p(12) g(X)',     0, 0, (SELECT id FROM buttonset WHERE name="Diceland"));

# DORK VICTORY (Cheapass Games)       INTRODUCES Mood Swing(?); Speed(z); Twin
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(98, 'Bill',        '(20) (20) (20) (V,V)',          0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
(99, 'Carson',      '(4,4) (8) (10) (12) z(V)',      0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
(100, 'Gilly',       '(6) (8) z(8) (20) (X)?',        0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
(101, 'Igor',        '(3) (12) (20) (20) (X)?',       0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
(102, 'Ken',         '(8) (10) z(12) (20) (V)',       0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
(103, 'Matt',        'z(8) (10) (10) z(10) (V)?',     0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory"));

# EVERYTHING TO GAIN (2001 Seattle Kotei) introduces Thief Dice; poison(p); shadow(s); speed(z)
# ASSUMED NOT TL
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(104, 'Kolat',        '(20) $(10) (8) (6) ps(4)',         0, 0, (SELECT id FROM buttonset WHERE name="Everything to Gain")),
(105, 'Ninja',        '$(12) (10) (6) z(4) (1)',          0, 0, (SELECT id FROM buttonset WHERE name="Everything to Gain"));

# FAIRIES (Cool Tuna)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
# Sven's recipe had a slight error on the old site - Little Sven is added to keep the old site recipe
(106, 'Little Sven',         'q(20) q(20) (8/12) (6/10) (4)',      0, 0, (SELECT id FROM buttonset WHERE name="Fairies")),
(107, 'Sven',                'q(20) q(20) (8/12) (6/12) (4)',      0, 1, (SELECT id FROM buttonset WHERE name="Fairies")),
(108, 'Yseulte',             'p(20) q(10) q(8) (X) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="Fairies"));

# FANTASY (Cheapass Games)                 INTRODUCES Socrates(S) Dice; Option; Special Rules for Nightmare
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(109, 'Luce',        '(8) (10) (20) (4/20) (8/20)',        0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(110, 'Frasquito',   '(4) (6) (8) (12) (2/20)',            0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(111, 'Lark',        '(6) (20) (2/8) (4/12) (6/10)',       0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(112, 'Theophilus',  '(8) (10) (12) (10/20) (20/30)',      0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(113, 'Mischa',      '(10) (12) (12) (4/12) (6/12)',       0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(114, 'Chang',       '(2/20) (2/20) (4/20) (8/20)',        0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(115, 'Aldric',      '(8) (8) (4/12) (8/20) (12/20)',      0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(116, 'Elihu',       '(4/6) (4/8) (6/10) (6/12) (8/20)',   0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(117, 'Farrell',     '(10) (12) (6/20) (6/20) (8/12)',     0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(118, 'Nikolai',     '(20) (4/10) (4/12) (6/10) (6/20)',   0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(119, 'Cesare',      '(10) (4/10) (6/10) (10/12) (10/20)', 0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(120, 'Vincent',     '(30) (30) (30) (6/30)',              0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
# Socrates may use one of his dice and one of his opponents for a two-dice skill attack
(121, 'Socrates',    '(4) (10) (12) (Y)',                  1, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
# When Nightmare loses a round he may change his opponent's variable dice.
(122, 'Nightmare',   '(4) (8) (10) (20) (20)',             1, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
(687, 'Dead Guy',    '(0) (0) (0) (0) (0)',                0, 1, (SELECT id FROM buttonset WHERE name="Fantasy"));

# FIGHTBALL (Cheapass Games) NOTE: special die selection rules - choose 5 dice out of all available (not implemented)
# ASSUMED ALL TO BE TL
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(550, 'Brie',              '(4) (6) (8) (10) (12) (12) (20)',         1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(551, 'Domino',            '(4) (4) (8) (8) (8) (10) (12)',           1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(552, 'Echo (Fightball)',  '(4) (6) (6) (6) (12) (12) (12) (20)',     1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(553, 'Georgia',           '(6) (6) (8) (8) (12) (20) (20)',          1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(554, 'Gordo (Fightball)', '(4) (6) (6) (8) (8) (10) (20)',           1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(555, 'Mel',               '(4) (4) (8) (10) (10) (20) (20)',         1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(556, 'Pavel',             '(6) (10) (10) (12) (12) (20) (20)',       1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(557, 'Rocq',              '(6) (10) (10) (12) (20) (20) (20)',       1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(558, 'Skinny',            '(4) (4) (4) (6) (8) (8) (10)',            1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(559, 'Tex',               '(4) (4) (6) (8) (10) (10) (12) (12)',     1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
# Pepper and Zal are from the BM Trading Cards
(560, 'Pepper',            '(6) (10) (10) (10) (12) (12) (12) (16)',  1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(561, 'Zal',               '(4) (6) (6) (16) (16) (20) (20)',         1, 1, (SELECT id FROM buttonset WHERE name="Fightball"));

# FREAKS (Cheapass Games)              INTRODUCES Queer(q) dice; Poison(p); Shadow(s); Speed(z)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(123, 'Max',  'p(12) p(12) p(20) p(20) p(30) p(30) p(X) p(X)',  0, 0, (SELECT id FROM buttonset WHERE name="Freaks")),
(124, 'Mister Peach','(6) z(8,8) ps(12,12) (V,V)!',    0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
(125, 'Simon',       'q(4) q(6) q(12) q(20) q(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
(126, 'Werner',      '(8) (10) (10) (12) pzs(V)!',     0, 1, (SELECT id FROM buttonset WHERE name="Freaks"));

# GEEKZ                                                        SKILLS Poison; Shadow; Reserve; Twin
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(310, 'Caine',         'ps(4) ps(4) s(20) s(20) s(X)',                     0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
(311, 'Cammy Neko',    '(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)',    0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
(312, 'Sailor Man',    '(4,4) (8) (20) (12,12) r(10,10) r(6,6) r(8)',      0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
(313, 'Xylene',        's(6) s(8) s(10) s(X) s(Y)',                        0, 1, (SELECT id FROM buttonset WHERE name="Geekz"));

# GUARDIANS OF ORDER sets include Sailor Moon 1, Sailor Moon 2, and Tenchi Muyo!
#     INTRODUCES Iconic Abilities which are button specials that only work against other Guardians of Order buttons.
# SAILOR MOON 1 (Guardians of Order)                          INTRODUCES Reserve(r) dice; AND Warrior(`) dice
# ICONIC ABILITIES: SAILOR MOON: TM(1), QUEEN BERYL: CB(2), SAILOR MERCURY: TM(1), ZOYCITE: NV(4), SAILOR MARS: TM(1), JEDITE: NV(4),
#                   SAILOR JUPITER: TM(1), NEPHLITE: NV(3), SAILOR VENUS: TM(1), MALACHITE: NV(2), TUXEDO MASK: TMDF
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(145, 'Sailor Moon',         '(8) (8) (10) (20) r(6) r(10) r(20) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(146, 'Queen Beryl',         '(4) (8) (12) (20) r(4) r(12) r(20) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(147, 'Sailor Mercury',      '(4) (8) (8) (12) r(4) r(4) r(8) r(10)',         1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(148, 'Zoycite',             '(4) (10) (10) (10) r(6) r(6) r(8) r(8)',        1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(149, 'Sailor Mars',         '(4) (6) (10) (20) r(6) r(10) r(10) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(150, 'Jedite',              '(6) (6) (12) (12) r(4) r(6) r(6) r(8)',         1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(151, 'Sailor Jupiter',      '(6) (10) (12) (20) r(6) r(12) r(12) r(20)',     1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(152, 'Nephlite',            '(4) (6) (12) (12) r(8) r(10) r(10) r(12)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(153, 'Sailor Venus',        '(4) (6) (10) (12) r(4) r(8) r(8) r(12)',        1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(154, 'Malachite',           '(8) (12) (20) (20) r(10) r(12) r(12) r(20)',    1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(155, 'Tuxedo Mask',         '(6) (6) (10) (20) r(4) r(8) r(10) r(12) r(20)', 1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
(156, 'Shadow Warriors',     '(1) (2) `(4) `(6) `(8) `(10) `(12)',            1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1"));

# SAILOR MOON 2 (Guardians of Order)                             SKILLS: Reserve(r)
# ICONIC ABILITIES: LUNA & ARTEMIS: Cat(2), WICKED LADY: DC(2), QUEEN SERENITY: IS(1), RUBEUS: DC(1), PRINCESS SERENA: IS(1),
#  SAPPHIRE: DC(2), RINI: IS(1), WISEMAN: Skull, NEO-QUEEN SERENITY: SM(2), PRINCE DIAMOND: DC(2), KING ENDYMION: KC(1), EMERALD: DC(1)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(157, 'Luna & Artemis',      '(1) (4) (10) (20) r(2) r(2) r(8) r(8)',         1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(158, 'Wicked Lady',         '(6) (6) (10) (12) r(4) r(8) r(10) r(20)',       1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(159, 'Queen Serenity',      '(4) (6) (10) (12) r(6) r(10) r(12) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(160, 'Rubeus',              '(4) (4) (12) (12) r(6) r(10) r(20) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(161, 'Princess Serena',     '(6) (8) (12) (20) r(4) r(10) r(12) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(162, 'Sapphire',            '(6) (10) (12) (12) r(8) r(8) r(10) r(12)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(163, 'Rini',                '(2) (4) (6) (6) r(4) r(10) r(12) r(12)',        1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(164, 'Wiseman',             '(20) (20) (20) (20)',                           1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(165, 'Neo-Queen Serenity',  '(12) (20) (20) r(4) r(6) r(8) r(10) r(12)',     1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(166, 'Prince Diamond',      '(4) (6) (12) (20) r(8) r(10) r(10) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(167, 'King Endymion',       '(6) (10) (20) (20) r(6) r(10) r(12) r(20)',     1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
(168, 'Emerald',             '(6) (8) (12) (20) r(4) r(6) r(10) r(20)',       1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2"));

# TENCHI MUYO! (Guardians of Order)                              SKILLS Reserve
# ICONIC ABILITIES: TENCHI: Jur(2), KAGATO: Com(1), AYEKA: Jur(1), RYOKO: Com(1), MIHOSHI: Com(1), SOJA'S GUARDIANS: Alt (1),
#                   KIYONE: Com(1), RYO-OHKI: Morph, WASHU: Alt(2), DR. CLAY: Con(2), SASAMI: Jur(3)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(169, 'Tenchi',              '(4) (10) (12) (20) r(4) r(12) r(20) r(20)',     1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(170, 'Kagato',              '(4) (6) (20) (20) r(10) r(12) r(12) r(20)',     1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(171, 'Ayeka',               '(6) (8) (10) (10) r(4) r(4) r(10) r(20)',       1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(172, 'Ryoko',               '(8) (10) (12) (12) r(4) r(10) r(20) r(20)',     1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(173, 'Mihoshi',             '(4) (8) (12) (12) r(8) r(10) r(12) r(20)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(174, 'Soja\'s Guardians',   '(4) (4) (4) (4) r(4) r(10) r(10) r(12)',        1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(175, 'Kiyone',              '(4) (4) (10) (12) r(6) r(10) r(10) r(20)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(176, 'Ryo-Ohki',            '(4) (4) (4) (12) r(10) r(12) r(20) r(30)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(177, 'Washu',               '(4) (6) (12) (X) r(6) r(8) r(10) r(20)',        1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(178, 'Dr. Clay',            '(6) (10) (10) (10) r(4) r(4) r(12) r(12)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
(179, 'Sasami',              '(4) (4) (6) (8) r(12) r(12) r(20) r(20)',       1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
# Zero works just like Echo - it doesn't have it's own recipe, but instead copies its opponent's
(180, 'Zero',                '',                                              1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!"));

# HIGH SCHOOL DRAMA!  (Shifting Skies)     INTRODUCES Sideboard(S) dice
# ASSUMED ALL TO BE TL
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(135, 'cheerleader',    '(4) (4) (6) (8) (12) S(10)',      0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
(136, 'dumb blonde',    '(6) (6) (8) (10) (12) S(20)',     0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
(137, 'meathead',       '(8) (10) (12) (20) (20) S(6)',    0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
(138, 'tennis star',    '(4) (6) (10) (12) (20) S(8)',     0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
(139, '1337 h4Xx0r',    '(4) (4) (12) (12) (20) S(6)',     0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
(140, 'game master',    '(8) (8) (10) (12) (20) S(20)',    0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
(141, 'emo boy',        '(4) (8) (8) (10) (20) S(12)',     0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
(142, 'goth chick',     '(6) (6) (6) (10) (20) S(4)',      0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!"));

# HOWLING WOLF (Howling Wolf Studios)              INTRODUCES Stealth(d) Dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(181, 'Howling Wolf',        'd(4) (8) (12) (20) d(20)',           0, 1, (SELECT id FROM buttonset WHERE name="Howling Wolf")),
(182, 'White Tiger',         '(6) d(6) (10) (12) d(12)',           0, 1, (SELECT id FROM buttonset WHERE name="Howling Wolf"));

# The Japanese Beetle! (The Japanese Beetle)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
# The Flying Squirrel cannot make skill attacks
(143, 'The Flying Squirrel', 'z(6) z(12) z(12) z(20)',             1, 1, (SELECT id FROM buttonset WHERE name="Japanese Beetle")),
# The Japanese Beetle: Skill attacks do not work on Japanese Beetle
(144, 'The Japanese Beetle', '(6) (8) (12) (X)',                   1, 1, (SELECT id FROM buttonset WHERE name="Japanese Beetle"));

# KUBLA CON (Kubla Con)             INTRODUCES Chase's Giant Sized(O), Kubla Treasure(X), Hoskins(Y)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(572, 'Space Kubla',       '(6) (8) O(12) X(12) (20)',   0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con")),
# Pirate Kubla's X is actually a Kubla die. Every time you reroll it, shout "Kubla!"
(573, 'Pirate Kubla',      '(6) (8) (20) Y(12) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con"));

# LAS VEGAS                                INTRODUCES Wildcard(C), Pai Gow(:) AND Triplet; Option; Twin; Turbo
# Frankie aka Professor, Lola aka Showgirl, Sly aka Pit Boss, Crypto aka Magician
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(183, 'Frankie',        '(2,3) (3,4) (4,5) (10) (T)!',      0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(184, 'Lola',           '(6) (6) (8) (T) (U)',              0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(185, 'Sly',            '(12) (12) (20) (20) (U)',          0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(186, 'Wildcard',       '(C) (C) (C) (C) (C)',              0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# Black Jack's T swing cannot be a d12.
# Black Jack, Craps, Shamrock, and Crypto have copies sans button specials so they can be played like on old site
# Black Jack, Shamrock, and Pai Gow have alternate recipes
(187, 'Black Jack',     '(1,1) (11) (8,8) (10,11) (T)',     1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# (688, 'Black Jack II',  '(1,1) (11) (8,8) (10,10,1) (T)',   1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(689, 'Twenty-One',     '(1,1) (11) (8,8) (10,11) (T)',     0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
#CRAPS: Any twin die that rolls a 7 may be reset by the player to any value between 2 and 12 (includes after a Trip attack.)
#At the start of a round, this decision must be made before seeing the opponent's starting roll.
(188, 'Craps',          '(6,6) (6,6) (6,6) (6,6) (6,6)',    1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(690, 'Them Bones',     '(6,6) (6,6) (6,6) (6,6) (6,6)',    0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# SHAMROCK: The d13s score as normal d13s, but are represented by a d12 for which a 7 counts as a 13
(189, 'Shamrock',       '(2) (7/13) (7/13) (7/13) (7/13)',  1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(691, 'Shamrock II',    '(2) (9) (7/13) (7/13) (7/13)',     1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(692, 'Lady Luck',      '(2) (7/13) (7/13) (7/13) (7/13)',  0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(190, 'Pai Gow',        '(6) :(8) :(8) (10) (12)',          0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(693, 'Pai Gow II',     '(4) :(10) :(10) (12) (12)',        0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# Crypto can use one of the following once per round, and cannot use any of them twice until he has used all four:
# * Rabbit from hat: Extra d1.
# * Prestidigitation: Change any die to a Trip die.
# * Bisect with Saw: Change any die (own or opponent's) to half of its originally-rolled value on the opening roll.
# * Vanishing Act: First die captured by opponent is scored as zero points.
# * Mind Reading: Opponent must state all sizes of all option and/or swing dice.
(191, 'Crypto',         '(6) (8) (10) (12) (T)',            1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(694, 'Magician',       '(6) (8) (10) (12) (T)',            0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas"));

# LEGEND OF THE FIVE RINGS (Wizards of the Coast)  INTRODUCES Focus(f) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(192, 'Crab',        '(8) (10) (12) f(20) f(20)',     0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(193, 'Crane',       '(4) f(6) f(8) (10) (12)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(194, 'Dragon',      '(4) (8) f(8) f(12) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(195, 'Lion',        '(4) f(6) (10) f(20) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(196, 'Mantis',      '(6) f(8) f(10) (20) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(197, 'Naga',        'f(6) (8) (8) (8) f(20)',        0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(198, 'Phoenix',     '(4) (6) f(8) (10) f(20)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(199, 'Ronin',       '(6) f(6) (8) f(12) (12)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(200, 'Scorpion',    '(4) (4) f(4) f(12) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(201, 'Unicorn',     '(4) (4) f(6) f(12) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(202, 'Mirumoto',    'f(6) (10) f(10) (12) (20)',     0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
(203, 'Kakita',      '(6) f(6) (10) f(12) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings"));

# LUNCH MONEY (Atlas Games)           INTRODUCES Trip(t) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(204, 'Charity',     't(4) (4) (8) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
(205, 'Prudence',    '(1) t(4) (6) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
(206, 'Hope',        't(1) (2) t(4) (6) (Y)',         0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
(207, 'Chastity',    't(6) (6) (10) (10) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
(208, 'Faith',       't(2) (6) (10) (12) (Y)',        0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
(209, 'Temperance',  't(2) (8) (12) (20) (Y)',        0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
(210, 'Patience',    '(2) (2) (8) (12) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money"));

# MAJESTY & MAJESTY - NORTHERN EXPANSION (Cyberlore)                     NO SPECIAL SKILLS
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(211, 'Dirgo',       '(20) (20) (20) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
(212, 'Flaire',      '(6) (10) (10) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
(213, 'Nerni',       '(4) (4) (12) (X) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
(214, 'Yeti',        '(10) (20) (30) (30) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Majesty"));

# METAMORPHERS (IMGames)          INTRODUCES Morphing(m) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(215, 'Daisy',     'm(6) (10) (10) (20) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Metamorphers")),
(216, 'Jingjing',  'm(4) (8) (10) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Metamorphers")),
(217, 'Mau',       '(6) (6) (8) (12) m(X)',       0, 0, (SELECT id FROM buttonset WHERE name="Metamorphers")),
(218, 'Spencer',   '(8) (8) (12) m(20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Metamorphers")),
(219, 'Talus',     '(4) m(12) (20) (20) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Metamorphers"));

# NODWICK (4th Blade)                     INTRODUCES Armor(A) dice
# ASSUMED ALL TO BE NON-TL
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(220, 'Artax',            'A(4) (8) (8) (12) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Nodwick")),
(221, 'Count Repugsive',  'A(4) A(4) (10) (10) s(12)',    0, 0, (SELECT id FROM buttonset WHERE name="Nodwick")),
(222, 'Nodwick',          '(4) (4) (10) (10) A(W)',       0, 0, (SELECT id FROM buttonset WHERE name="Nodwick")),
(223, 'Piffany',          'A(6) A(6) (6) (8) (W)',        0, 0, (SELECT id FROM buttonset WHERE name="Nodwick")),
(224, 'Yeagar',           'A(6) (10) (20) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Nodwick"));

# POLYCON (Polycon)                   INTRODUCES Fire(F)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(225, 'Poly',        '(4) (6) F(8) (20) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Polycon")),
(226, 'Adam Spam',   'F(4) F(6) (6) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Polycon"));

# PRESIDENTIAL BUTTONMEN                              SKILLS: Option; Poison; Shadow
# Cthulhu from Cthulhu (Chaosium)
# Gore & Bush from GenCon 2000 (Cheapass Games)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(227, 'Bush',                '(4) (20) (4/8) (6/12) (6/20)',       0, 1, (SELECT id FROM buttonset WHERE name="Presidential")),
(228, 'Gore',                '(4) (20) (4/8) (6/12) (6/20)',       0, 1, (SELECT id FROM buttonset WHERE name="Presidential")),
(229, 'Cthulhu',             '(4) (20) s(4,8) s(6,12) ps(6,20)',   0, 1, (SELECT id FROM buttonset WHERE name="Presidential"));

# RENAISSANCE (Stone Press)                 SKILLS: Mood swing(?); Ornery(o); Poison(p); Speed(z); Option; Unique(u)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(230, 'Dr. Speculo',    '(6) (8) (12) o(Y) o(Y)',       0, 0, (SELECT id FROM buttonset WHERE name="Renaissance")),
# Guillermo: X and Y cannot be the same size
(231, 'Guillermo',      '(6) (10) (20) (X) (Y)',        1, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
(232, 'Harry Pudding',  '(4) (7) (2/20) (2/20) (10)',   0, 0, (SELECT id FROM buttonset WHERE name="Renaissance")),
(233, 'Lascivia',       '(4) zp(12) (20) p(X)',         0, 0, (SELECT id FROM buttonset WHERE name="Renaissance")),
(234, 'MothMan',        '(8) (8) (12) (16) (Y)?',       0, 0, (SELECT id FROM buttonset WHERE name="Renaissance"));

# SAMURAI (Cheapass Games)               SKILLS: Focus(f)
# Tsusuko from GenCon 2001 (Cheapass Games)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(235, 'Honzo',       '(10) (12) f(20) (V) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
(236, 'Konami',      '(6) (8) f(10) f(10) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
(237, 'Okaru',       '(4) f(4) (6) (12) (V)',         0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
(238, 'Sadakuro',    'f(4) f(6) f(8) f(10) (12)',     0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
(239, 'Tonase',      '(4) (4) (8) (20) f(X)',         0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
(240, 'Yuranosuke',  '(4) (8) (12) f(12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
(241, 'Tsusuko',     'f(6) (10) (10) (16) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Samurai"));

# SANCTUM (Digital Addiction)         NO SPECIAL SKILLS
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(242, 'Dracha',      '(4) (10) (20) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
(243, 'Ogi',         '(2) (4) (10) (X) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
(244, 'Fingle',      '(6) (7) (8) (12) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
(245, 'Ngozi',       '(4) (6) (8) (10) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Sanctum"));

# SAVE THE OGRES (Save the Ogres)                      SKILLS Shadow; Twin
# Ginzu & Gratch from GenCon 2000
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(246, 'Ginzu',               '(8) (8) s(12,12) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Save The Ogres")),
(247, 'Gratch',              '(6) s(8,8) (20) s(20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Save The Ogres"));

# SFR (SFR)                                   SKILLS: Option           (2001 Rare / Promo on old site)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(248, 'Assailer',       '(12) (12) (20) (2/20) (X)',    0, 0, (SELECT id FROM buttonset WHERE name="SFR")),
(249, 'Harbinger',      '(4) (4) (4/12) (10/20) (V)',   0, 0, (SELECT id FROM buttonset WHERE name="SFR"));

# SLUGGY FREELANCE (Sluggy)               ITNRODUCES: Option (/) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(250, 'Aylee',       '(8) (10/20) (12) (12/20) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
(251, 'Bun-Bun',     '(4/10) (4/12) (6/12) (20) (20)',     0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
(252, 'Kiki',        '(3/20) (4) (8/12) (10) (10/20)',     0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
(253, 'Riff',        '(4/20) (6) (6/8) (10/12) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
(254, 'Torg',        '(6) (6/20) (8) (10/12) (12/20)',     0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
(255, 'Zoe',         '(4/12) (6/10) (8) (10/20) (12/20)',  0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance"));

# SOLDIERS (Cheapass Games) NOTE: original Buttonmen set, no special die skills
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(256, 'Avis',        '(4) (4) (10) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(257, 'Hammer',      '(6) (12) (20) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(258, 'Bauer',       '(8) (10) (12) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(259, 'Stark',       '(4) (6) (8) (X) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(260, 'Clare',       '(6) (8) (8) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(261, 'Kith',        '(6) (8) (12) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(262, 'Karl',        '(4) (6) (6) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(263, 'Iago',        '(20) (20) (20) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(264, 'Niles',       '(6) (10) (10) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(265, 'Shore',       '(4) (4) (20) (20) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(266, 'Hannah',      '(8) (10) (10) (10) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(267, 'Kublai',      '(4) (8) (12) (20) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
(268, 'Changeling',  '(X) (X) (X) (X) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Soldiers"));

# SPACE GIRLZ (buttonmen.com)   INTRODUCES Plasma{} dice; Mighty(H); Ornery(o); Poison(p); Shadow(s); Weak(h); twin
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(269, 'Maya',       'o(6) (12) p{hs,H}(12) (20) (S)',           0, 0, (SELECT id FROM buttonset WHERE name="Space Girlz")),
(270, 'Zeno',       '{h,H}(6) {h,H}(8) {h,H}(4,4) (20) (X)',    0, 0, (SELECT id FROM buttonset WHERE name="Space Girlz"));

# STUDIO FOGLIO (Studio Foglio)       INTRODUCES Twin dice(,); Poison(p)
# What's New
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(271, 'Phil',             '(8) (8) (10,10) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(272, 'Dixie',            '(4) (6) (10) (12,12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(273, 'Growf',            '(4,4) (6) (8) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# Buck Godot    NOTE: Winslow from this set is a die, not a button.
(274, 'Buck Godot',       '(6,6) (10) (12) (20) (W,W)',    0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# Girl Genius
# Jorgi from GenCon 2001 (Cheapass Games)
(275, 'Agatha',           '(4) (6) (8,8) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(276, 'Krosp',            '(4) (6,6) (12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(277, 'Klaus',            '(4) p(10,10) (20) (20) (W)',    0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(278, 'Von Pinn',         '(4) p(6,6) (10) (20) (W)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(279, 'Bang',             'p(4,4) (6) (12) (12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(280, 'Gil',              '(8) (8) p(8,8) (20) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(281, 'Jorgi',            '(4) (6) (8) (20) p(X)',         0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(282, 'The James Beast',  '(4) (8,8) (10,10) (12) (W,W)',  0, 0, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# XXXenophile
(283, 'Brigid',           '(8) (8) (X) (X) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
(284, 'O-Lass',           '(6) (12) (X) (X) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio"));

# SYDCON 10 (SydCon)               INTRODUCES Rage(G)    (2001 Rare / Promo on old site)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(285, 'Gripen',     '(4) (6) (8) G(12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="SydCon 10"));

# UNEXPLODED COW (Cheapass Games)   INTRODUCES Boom(b) Dice AND Mad Swing(&) Dice
# ASSUMED ALL TO BE TL
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(286, 'George',     '(4) (6) b(6) b(20) (Y)&',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(287, 'Violette',   '(8) (8) b(10) b(12) (Y)&',     0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(288, 'Elsie',      '(4) b(4) (10) b(12) (Y)&',     0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(289, 'Kasper',     '(6) b(8) (12) b(20) (Y)&',     0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(290, 'Montague',   'b(4) b(10) (12) (20) (Y)&',    0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(291, 'Neville',    'b(4) (8) b(8) (20) (Y)&',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(292, 'Thaddeus',   '(10) (14) (14) (18) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(293, 'Buckley',    '(4) (4) (18) (X) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow"));

# UPTOWN (Cheapass Games)              INTRODUCES Rush(#) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
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

# VAMPYRES (Cheapass Games)            INTRODUCES Shadow(s) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(300, 'Angel',       's(4) (6) s(12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(301, 'Buddy',       's(6) (10) s(20) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(302, 'Dunkirk',     '(6) (6) (10) (20) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(303, 'Starchylde',  's(6) (8) s(10) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(304, 'McGinty',     '(4) s(10) (12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(305, 'Tiffany',     '(4) s(8) (8) (10) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Vampyres"));

# WEST SIDE (Cheapass Games)                        SKILLS Poison
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
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
(747, 'Mondo',              '(4) (12) (20) p(20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="West Side"));

# WONDERLAND                                        SKILLS Null; Option; Poison; Queer; Stinger; Turbo; Twin
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(294, 'Alice',             '(6) (8) (8) (10) (1/30)!',          0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
(295, 'Mad Hatter',        'q(6) q(6) q(10) q(20) q(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
(296, 'Queen Of Hearts',   '(6) (8) p(16) (20) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
(297, 'The Jabberwock',    '(20) (20) (30) ng(30) (U)',         0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
(298, 'Tweedledum+dee',    '(2,2) (4,4) (6,6) (10,10) (T,T)',   0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
(299, 'White Rabbit',      '(4) (6) (8) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Wonderland"));

# YOYODYNE (Fuzzface)                   INTRODUCES Chance(c) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(306, 'FuzzFace',      '(4) (8) (10) c(10) c(12)',    0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
(307, 'John Kovalic',  '(6) c(6) (10) (12) c(20)',    0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
(308, 'Pikathulhu',    '(6) c(6) (10) (12) c(X)',     0, 0, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
(309, 'Ulthar',        '(4) (8) (10) c(10) c(T)',     0, 0, (SELECT id FROM buttonset WHERE name="Yoyodyne"));
# WT???
# Seriously!  I have no idea what this recipe is meant to be
# (695, 'Bob',    '(pX+Y+Z) (t0]) (sA!) (zA!) (cA!) (X+Y+Z)',     1, 0, (SELECT id FROM buttonset WHERE name="Yoyodyne"));

# ZOECon (ZOECon.net)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(574, 'Carrow',          's(4) s(8) s(12) s(20) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="ZOECon (JourneyQuest)")),
(575, 'Nara',            '(6) (8) (12) (20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="ZOECon (JourneyQuest)")),
(576, 'Perf',            '(6) (6) (10) (X) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="ZOECon (JourneyQuest)")),
(577, 'Glorion',         '(10) (12) (16) (20) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="ZOECon (JourneyQuest)")),
(578, 'The Assassin',    '(6) (10) p(10) (12) s(X)',          0, 1, (SELECT id FROM buttonset WHERE name="ZOECon (JourneyQuest)")),
(579, 'Wren',            '(4) (8) (12) (12) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="ZOECon (JourneyQuest)"));

###############################################
##### SETS MADE FOR BUTTONMEN ONLINE OR DESIGNED BY ITS PLAYERS

# IRON CHEF                                                   SKILLS Option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(314, 'Chairman Kaga',         '(5/7) (7/9) (9/11) (11/17) (17/23)',      0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
(315, 'Iron Chef Chinese',     '(7) (9) (11) (11) (13/29)',               0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
(316, 'Iron Chef French',      '(5/27) (7) (13) (17) (23)',               0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
(317, 'Iron Chef Italian',     '(3) (5) (9) (9) (11/21)',                 0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
(318, 'Iron Chef Japanese',    '(9/17) (17) (17) (21) (29)',              0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef"));

# BUTTONMEN.DHS.ORG: the following sets make use of die types designed specifically for the old buttonmen online
# 7 DEADLY SINS                                  SKILLS Doppelganger; Mighty; Morphing; Option; Posion; Queer; Rage; Speed; Turbo
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(319, 'Anger',        '(10) (10) (10) (12/20) (20/30)!',     0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
(320, 'Envy',         'D(4) D(6) D(10) D(12) D(X)',          0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
(321, 'Gluttony',     'H(1,2) Hp(1,3) H(1,4) m(1,5) (W)',    0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
(322, 'Greed',        '(8) (12) (12) z(12) z(4/20)',         0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
(323, 'Lust',         '(6) (6) m(12) D(20) q(X)',            0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
(324, 'Pride',        '(8) (10) G(12) G(20) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
(325, 'Sloth',        '(4,4) (6,6) (8,8) (10,10) (V,V)',     0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins"));

# CHICAGO CREW                                   SKILLS Berserk; Mighty; Option; Ornery; Shadow; Speed; Trip
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(326, 'Audrey',        't(2) (3/6) o(8) (12) (Y)',           0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
(327, 'Cheathem',      'Ho(1) s(6) o(10) o(14) s(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
(328, 'Flint',         'o(4,4) (12) o(16) (20) o(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
(329, 'Lizzie',        't(6) s(6) B(10) o(12) o(X)',         0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
(330, 'Monty Brown',   't(1) z(6) o(10) (20) (Y)',           0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
(331, 'Octavia',       'st(4) s(4) so(10) s(10) o(X)',       0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
(332, 'Spooky',        'ot(8) o(6) o(10) B(15) o(Z)?',       0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew"));

# FOUR HORSEMAN                                  SKILLS Berserk; Mighty; Poison; Shadow; Speed; Twin; Weak
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(333, 'Death',         '(2,2) p(8) s(10) (16) (X)?',         0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
(334, 'Famine',        '(6) (8) (10) (12,12) h(X)',          0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
(335, 'Pestilence',    '(4) pH(6) (12) (20) (S)',            0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
(336, 'War',           'z(4) (6) z(10) B(20) (W,W)!',        0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen"));

#COWBOY BEBOP (Jota)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(580, 'Ed',        '(4) f(8) (8) (12) (Y)? +t(10)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(581, 'Ein',       '(8) (8) f(8) t(8) (X) +(Y)',              0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(582, 'Faye',      '(6) (6) p(10) (12) (X)! +g(8)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(583, 'Jet',       '(10) s(10) d(12) (30) (X) +n(20)',        0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(584, 'Spike',     '(4) d(6) (8) F(10) (V) +z(12)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(585, 'Vicious',   '(4) (6) s(12) p(12) (X) +B(20)',          0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop"));

# FREE RADICALS                                SKILLS Doppelganger; Ornery
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(337, 'CynCyn',       '(6) D(10) (20) D(X) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
(338, 'Loren',        '(4) (6) Do(6) D(20) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
(339, 'Lorrie',       'D(6) (12) (20) (20) D(X)',          0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
(340, 'Maskin',       'D(4) (4) (8) D(16) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
(341, 'Randy',        '(4) D(6) D(8) (30) (S)',            0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
(342, 'Tony',         'D(6) (8) (10) (12) D(X)',           0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals"));

# HODGE PODGE             SKILLS Chance; Chaotic; Focus; Mood Swing; Option; Ornery; Reserve; Slow; Twin
#       SKILLS Berserk; Doppelganger; Fire; Jolt; Konstant; Maximum; Mighty; Rage; Shadow; Speed; Stealth; Time and Space; Trip
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(343, 'BabyBM',              '(7) o(8,8) (11) (21) HG(V)',                0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(344, 'Bull',                '(6) (8) (12) (X) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(345, 'Butthead',            'B(U) t(T) H(3) @d(5)',                      0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(346, 'Button of Loathing',  'Jk(13) (6) (6) (20) (R,R)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(347, 'Craptacualr',         'c(6) c(8) (12) (20) (T,T)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(348, 'Crosswords',          'c(R) o(S) s(W) o(R) d(S)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(349, 'Da Pimp',         't(4) (8) (12) (20) wHor(4) wHor(4) whor(20) whor(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(350, 'Darth Maul',          '(8) f(10) (12) f(20) (30)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
# late edition to Hodge Podge: Eeyore designed by Elliot Evens, found on boardgamesgeek list
(351, 'Eeyore',              '(6) (10) (10) (12) (X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(352, 'Evil Robot Luke',     '(4/20) (6/12) p(10) (12) (12/20)',          0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(353, 'Ferretboy',           '(6) (6) @(20) @(20) @o(X)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(354, 'Holiday',             '(2/14) (3/17) (7/4) (10/31) (12/25)',       0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(355, 'Horde O Ninjas',      'd(6) d(6) d(10) d(12) d(20) d(20) dr(6) dr(8)',     0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(356, 'Jose',                'M(3) M(4) M(6) M(8) M(T)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(357, 'Loki',                'Ho(2,2) Ho(2,2) Ho(2,2) Ho(2,2) (T)',       0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(358, 'Marilyn Monroe',      'D(36) (24) (36)',                           0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(359, 'Miser',               'v(20) v(20) v(20) v(20) v(20)',             0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(360, 'Qui-Gon Jinn',        'f(4) (6) f(8) (10) f(12)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(361, 'Skomp',               'wm(1) wm(2) wm(4) m(8) m(10)',              0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(362, 'The Tick',            'H(1,10) H(12) H(20) H(20)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(363, 'Thor',                'G(6) G(10) G(12) G(6/20)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(364, 'Tilili',              'oz(4) @(10,10) ^(12) z(12) (Y)!',           0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(365, 'Trogdor',             'sGF(20) sGF(20) sGF(20) sGF(20)',           0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
(366, 'Tyler Durden',        'o(2,2) o(6) s(20) B(20) o(X)?',             0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge"));

# VICTORIAN HORROR       SKILLS Option; Reserve; Mood Swing; Ornery; Plazma; Null; Value;
#   SKILLS Focus; Konstant; Poison; Shadow; Speed; Stealth; Stinger; Time and Space; Trip; Weak
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(367, 'Count Dracula',            'sf(4) (8) s(10) (16) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
(368, 'Dr. Jekyll',       '(X)? (X)? r^(20) rvkt(20) rsfp(20) rhop(20)',   0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
(369, 'Frankenstein\'s Monster',  '(10/16) (16/20) o(20/30) {G,B}(30)',    0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
(370, 'Invisible Man',            'n(4) d(6) d(10) ng(10) d(20)',          0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
(371, 'Wolfman',                  '(6) p(10) (12) z(16) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror"));

#50 States   (Kaufman)
#NOTE: CA, PA, are meant to have Select Dice. I've given them the unused code 'U' until we figure what to do about that.
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(586, 'Alabama',         'D(6) ^(10) o(14) (S) (X)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(587, 'Alaska',          'd(6) s(20) s(30) w(30) (T)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(588, 'Arizona',         'k(7) g(9) (12) F(15) (X)',                       1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(589, 'Arkansas',        'p(4) H(9) w(12) h(20) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
# Replaced $p(20) with Up(20) and $q(12) with Uq(12)
(590, 'California',      '@(10) @(20) Up(20) Uq(12) (Y)? (Z)',             0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(591, 'Colorado',        '(4) (6) z(14) (U)? (U)?',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(592, 'Connecticut',     'H(4) v(11) h(20) (4/20)? (R)',                   1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(593, 'Delaware',        '(1) (4) h(6) f(8) (T)',                          0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(594, 'Florida',       'g(6) F(10) p(12) (U) r(4) r(6) hr(12) @whr(20)',   0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(595, 'Georgia (US)',    'oz(10) (4/20) B(X) B(X) q(X)',                   0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(596, 'Hawaii',          'n(4) m(5) k(8) F(13) d(Y)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(597, 'Idaho',           'B(4) s(6) (8) (Y) (Y)',                          0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(598, 'Illinois',        '(10/20) G(12) (8,8) (R) rsd(4) rsd(6)',          0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(599, 'Indiana',         '(4) (8) (12) z(20) (W)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(600, 'Iowa',            'n^(6) f(8) D(9) (11) k(T)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(601, 'Kansas',          '(9) c(9) F(9) Gz(9) t(9)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(602, 'Kentucky',        'z(5) (1/4) (9/8) (20) (R)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(603, 'Louisiana',       'p(2) @(6) G(12) o(12) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(604, 'Maine',           'f(4) g(6) s(6) (V) (X)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(605, 'Maryland',        'g(4) m(8) o(10) (W) (X) +@(8)',                  0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(606, 'Massachusetts',   'f(6) k(8) o(10) (X) (Y)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(607, 'Michigan',        '(8) s(9) p(10) (Z)! `(2) `(3) +(6)',             0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(608, 'Minnesota',       'o(6) o(6) (7,7) s(20) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(609, 'Mississippi',     '@(4) H(6) (8) w(13) (W)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(610, 'Missouri',        'f(4) (10) v(10) H(8,12) (Z)',                    0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(611, 'Montana',         'B(4) H(8) (12) (S) z(Z)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(612, 'Nebraska',        '(11) h(U) (S) k(R) fB(11)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(613, 'Nevada',          'H(3) (C) (21) c(36) %(V)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(614, 'New Hampshire',   'f(4) os(6) Gh(9) (V) (W)?',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(615, 'New Jersey',      'c(4) B(15) z(18) p(20) s(S)',                    0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(616, 'New Mexico',      '^(4) (8) %(10) s(12) (X)?',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(617, 'New York',        '(12) p(16) z(30) o(X)? rq(6) r(8)',              0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(618, 'North Carolina',  'pF(10) o(10) (V)! gt(V) h(V)',                   0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(619, 'North Dakota',    '(4,4) (8) s(12) n(12) (W)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(620, 'Ohio',            'H(6) F(7) p(8) (X)? (X)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(621, 'Oklahoma',        'f(6) f(10) f(12) f(X)',                          0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(622, 'Oregon',          'z(6) (12) (R) (W) (X)',                          1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
# Replaced $f(2) and $s(6) with Uf(2) and Us(6)
(623, 'Pennsylvania',    'Uf(2) Us(6) %(4) (12) t(20) (Y)?',               0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(624, 'Rhode Island',    '(4) (4) d(6) d(10) (R)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(625, 'South Carolina',  '(6) (9) fB(10) G(12) (X)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(626, 'South Dakota',    '(1) (3) (16) (26) @o(Z)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(627, 'Tennessee',       '@(1/5) %(6) F(8) (5/25) rpm(3,3) rpm(4,5)',      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(628, 'Texas',           '^(10) (10,10) (30) `(8) r(6) r(8) r(10) r(12)',  0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(629, 'Utah',            '(6) (8) w(12) H(S) (X,X)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(630, 'Virginia',        '(4) oBs(8) Fp(12) (20) (W)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(631, 'Vermont',         '(4) G(6) os(8) g(10) (V)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(632, 'Washington',      'n(4) z(6) (7) F(13) mso(S)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(633, 'West Virginia',   'q(Y) w(X) B(Y) o(X) Bowq(Z,Z)?',                 0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(634, 'Wisconsin',       'co(W) co(W) cow(S) cow(S) cow(S)',               0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(635, 'Wyoming',         '(4) z(12) kp(20) n(20) (S)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 States"));

# Peloton (AnnoDomini)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(779, 'Antonio',         'c(4) d(8) %(10) h(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(780, 'Doyle',           'c(4) M(8) ^(10) k(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(781, 'Floriano',        'n(4) f(8) s(10) z(20) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(782, 'Julia',           'H(4) %(8) f(10) d(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(783, 'Mariusz',         'f(4) k(8) H(10) G(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(784, 'Orlando',         'k(4) s(8) g(10) M(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(785, 'Roger',           'g(4) G(8) c(10) H(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(786, 'Timea',           'd(4) s(8) z(10) h(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton"));

# Cybersuit Corp (AnnoDomini)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(797, 'Andrew',          'ks(6) fI(8) (12) (12) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(798, 'Chad',            '(4) ks(4) It(12) (20) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(799, 'Fiona',           'st(4) fM(8) (12) (12) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(800, 'Gavin',           'fM(6) Is(8) (12) (20) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(801, 'Isabel',          '(6) It(8) fs(10) (12) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(802, 'Monique',         '(8) kI(8) fs(12) (12) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(803, 'Nadia',           'fI(6) st(10) (10) (12) (V)',                     0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp")),
(804, 'Sachin',          '(6) kt(6) Is(10) (20) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="CyberSuit Corp"));

###############################################
##### UNOFFICIAL SETS - FAN AND VANITY SETS

# BLADE MASTERS (Bayani Caes)            INTRODUCES Parry (I); Focus, Poison, Speed, Trip
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(636, 'Arashi',    '(4) (6) I(10) f(12) (20)',            0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(637, 'Michie',    '(4) (8) (12) z(12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(638, 'Johnny',    't(6) (6) I(8) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(639, 'Renee',     '(2) (2) (6) I(10) (X)',               0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(640, 'Danny',     '(6) t(8) t(8) (20) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(641, 'Danielle',  '(6) (12) I(12) (20) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(642, 'Scott',     '(8) I(8) (10) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(643, 'Macky',     '(4) (6) (10) (X) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(644, 'Magistra',  'I(6) I(10) I(10) I(X)',               0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(645, 'Horace',    '(8) (10) p(20) (20) (Z)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(646, 'Kainar',    '(4) (4) z(10) z(V) (V)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(647, 'Inez',      '(6) (6) I(6) (20) (X)',               0, 0, (SELECT id FROM buttonset WHERE name="Blademasters"));

#BLADE MASTERS 2                             INTRODUCES Attacker(-), Defender(|), Cross(x);
#                                                       Option, Reserve, Turbo, Twin, Fire, Poison, Shadow, Speed, Trip
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(648, 'Paris',    '(4/8)! (6) (10) (10) (12/20)!',           0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(649, 'Gideon',   '(4) (8) (12) (X) r(4) r(6) r(10) r(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(650, 'Spider',   't(4) p(6) s(8) z(10) (R)',                0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(651, 'Painter',  'x(2) (4) (8) (12) (X)',                   0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(652, 'Regina',   '(1) (6) t(4,4) (12) (Y)',                 0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
(653, 'Damian',   '-(10) |(10) F(10) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot"));

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
(664, 'Montague (Blademasters)', '(2,2) (4) (10,10) (20) (X) +(V)',  0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(665, 'Chantal',      'pI(4) pI(8) (20) (20) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack"));
# Silly Self Promo (yes, that's a fudge die)  INTRODUCES Sustaind Fire Die (SFD)
# The Sustained Fire Die rolls 0, 1, 1, 2, 2, 3 in linear distribution.  If a zero is rolled, the only attack Bayani
# can make afterwards is a skill attack.  Alternatively, he can just reroll it without making an attack.  It's worth 3 points.
# (666, 'Bayani',       '(F) (SFD) (16) (16) (12/20)',      1, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack"));

# GAMING GUARDIANS (XIG Games) Dice Skills unique to this set: Teleport(T), Insult(I), Deception(~), Specialty, Loaded (M), Evil(E)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(127, 'Dream Wyrm',      'T(8) (20) (20) (20,8) (U)',                  0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
(128, 'EDG',             'I(6) I(8) I(10) (20) (X)',                   0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
(129, 'Graveyard Greg',  '(6) (8) {I,M,p,s,z,t}(10) {I,M,p,s,z,t}(10) (X)',  0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
(130, 'Memento-Mori',    '(6) (10) (10) ~(12) ~(20)',                  0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
(131, 'Radical',         'T(4) (20) (12,12) (20,8) (Z)',               0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
(132, 'Randarch',        'M(6) M(6) (10) (10) (X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
(133, 'Scarlet Jester',  'n(4) E(10) n(12) E(20) E(20)',               0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
(134, 'Shane Hensley',   'E(6) E(6) E(6) E(6) E(6) E(6)',              0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians"));

# JAPANESE BEETLE (unofficial)                 INTRODUCES Dodge(_)
# unlicensed fan set designed by Bayani Caes long before official JB set was created
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(562, 'The Japanese Beetle (unofficial)',  '(6) (12) (12) _(V) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(563, 'The Flying Squirrel (unofficial)',  '(4) (6) (12) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(564, 'Joe McCarthy',            '(10) (12) (12) (20) (X)',    0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(565, 'Kremlina',                '(6) (8) (10) (12) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(566, 'Max Factor',              '(6) (8) (12) (X) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(567, 'The Frenchman',           '(8) (10) (10) (12) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)"));

# MEGATOKYO (Dreamshade - MegaTokyo forums)  INTRODUCES Full Auto Dice(P); Turbo, Speed, Mood Swing, Poison, Shadow, Option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
# Largo cannot use skill attacks.
(667, 'Largo',            '(12) (20) (20) (X) (X)',          1, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(668, 'Ping',             '(4) (8) (X)! (X)!',               0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(669, 'Piro',             '(4) (8) (8) z(X)? z(X)?',         0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(670, 'Darkly Cute',      '(4) p(8) s(10) p(12) s(X)',       0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(671, 'Dom',              '(10) (10) P(20) P(20) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(672, 'Erika',            'z(10) z(12) z(12) z(4/20)!',      0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo"));

# ORDER OF DOLLS              INTRODUCES Assassin(a); Twin
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(673, 'Chaka',     'a(4) (8) (8) (12) a(30)',             0, 0, (SELECT id FROM buttonset WHERE name="Order of Dolls")),
(674, 'Strotzie',  '(4) (6) a(10) (12) a(X)',             0, 0, (SELECT id FROM buttonset WHERE name="Order of Dolls")),
(675, 'Fuma',      'a(8) (16) (20) (V) (Z)',              0, 0, (SELECT id FROM buttonset WHERE name="Order of Dolls")),
(676, 'Vox',       '(6) a(6) (12) (12) a(V,V)',           0, 0, (SELECT id FROM buttonset WHERE name="Order of Dolls"));

# STEVEN UNIVERSE (Jota)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id, flavor_text) VALUES
(707, 'Garnet', '(4) (8) (12) (20) r(2,2) r(4,4) r(6,6) r(10,10)', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Garnet is current leader of the Crystal Gems which she originally joined in order to live in a place where she was free to be herself. Garnet''s weapon is a pair of massive gauntlets ideal for beating people up.'),
(708, 'Amethyst', '(4) (6) (10) (16) rt(4) rt(6) rt(10) rt(16)', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Amethyst is a member of the Crystal Gems who was created in the Kindergarten on Earth. She has never been to the Gem Homeworld and considers the Earth to be her home. Amethyst can summon a multi-tailed whip with which to beat people up.'),
(709, 'Pearl (SU)', '(4) (6) (8) (12) rf(4) rf(6) rf(8) rf(12)', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Pearl is a member of the Crystal Gems. She was one of Rose Quartz''s closest followers in the rebellion against the Gem Homeworld and her sole confidant. Pearl wields a magic spear with a spiral blade that she uses to beat people up.'),
(710, 'Rose Quartz', '(8) (10) (12) (16) rM(8) rM(10) rM(12) rM(16)', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Rose Quartz was the founder and original leader of the Crystal Gems before she gave up her physical form to become Steven''s mother. She led her friends and allies in a rebellion against the Gem Homeworld over 5,000 years ago to protect the Earth from invasion. Rose Quartz was a natural leader, inspiring those around her to beat people up.'),
(711, 'Peridot', '(4) (8) (8) (12) r(4/6) r(8/10) r(8/12) r(12/16)', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Peridot is a Homeworld Gem technician and certified Kindergartener who became stranded on Earth and was forced to cooperate with the Crystal Gems. She prefers to wear limb enhancers to augment her height and reach, all the better to beat people up.'),
(712, 'Lapis Lazuli', '(4) (10) (10) (12) rD(4) rD(10) rD(10) rD(12)', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Lapis Lazuli is a Homeworld Gem who was trapped in a mirror for thousands of years until being freed by Steven. She does not trust the Crystal Gems or the Homeworld Gems. She has immense power over water, easily controlling it, reshaping it and using it to beat people up.'),
(713, 'Jasper', '(8) (12) (16) (20) rG(8) rG(12) rG(16) rG(20)', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Jasper is a Homeworld Gem who was a veteran of the rebellion and fought against Rose Quartz. She returned to Earth to retake it from the Crystal Gems. Jasper''s crash helmet is designed to be used for headbutting her enemies, her preferred way of beating people up.'),
(714, 'Steven Universe', '(6) (6) M(10) (16) (X)!', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Steven Universe is a member of the Crystal Gems and the son of Rose Quartz and Greg Universe. He is the only human/gem hybrid in existence, and his full abilities and potential are yet to be discovered. Steven prefers to use his powers to defend and protect his friends with magical shields while they beat people up.'),
(715, 'Greg Universe', '(6) (8) (16) (20) (X)!', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Greg Universe, a.k.a. "Mr. Universe", is a human being, a retired rock musician and the father of Steven Universe. He owns the local car wash and lives out of a van because all of his money goes to support Steven and the Crystal Gems. He''s wary about "Gem business" but would do almost anything to support Steven, even beating people up.'),
(716, 'Connie', '(4) (6) (10) (12) z(X)', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Connie Maheswaran is a human being and the best friend of Steven Universe. She is introverted, curious and intelligent. Connie has become adept at fighting with Rose Quartz''s sword, pairing with Steven in a complementary combination of defense and offense to beat people up.'),
(717, 'Lion (SU)', '(4) ^(8) (16) ^(U)', 0, 0, (SELECT id FROM buttonset WHERE name="Steven Universe"), 'Lion is a giant magical pink lion. Presumed to have originally been a creation of Rose Quartz, Steven now considers him to be his pet. Lion can create portals to travel across vast distances, has a pocket dimension hidden in his mane, and can use his roar to shatter objects and beat people up.');

# ZODIAC (Kaori)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(695, 'Aquarius',    '(6) p(6) (8) s(12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(696, 'Aries',       '(6) s(8) z(10) (20) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(697, 'Cancer',      'p(6) g(8) (8) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(698, 'Capricorn',   '(4) s(6) (10) g(12) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(699, 'Gemini',      '(4,4) (12) (12) z(X) s(X)',           0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(700, 'Leo',         '(4) (6) p(10) z(20) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(701, 'Libra',       '(4) g(4) z(8,8) (8,8) (V)',           0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(702, 'Pisces',      '(4) p(8) z(12) (12) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(703, 'Sagittarius', '(4) (4) s(8) p(10) (V)',              0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(704, 'Scorpio',     'g(6) (10) (12) p(20) (V)',            0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(705, 'Taurus',      'g(8) (10) s(12) (20) (V)',            0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(706, 'Virgo',       '(4) g(8) z(12) (20) (V)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac"));

################################################
#####   C L A S S I C    F A N A T I C S   #####
################################################
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(372, 'ABCGi',           '%Ho(1) pdw(7) mhv(13) zkt(23) (X,X)!',       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(373, 'albertel',        'n^(20) t(20) z(20) q(S)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(374, 'Alljazzedup80',   'z(8) B(8) p(10) %(12) (10/20)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(375, 'amica',           't(4) p(6) o(8) (7,7) o(Y)?',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(376, 'Anders',          '(8) (8) (8) (8) (8) o(24)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(377, 'antherem',        'fH(4) opH^(8) f(10) @(12) (U) r(10) r(12)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(378, 'anthony',       'v?(X,X) vst(16) stomp(10) {zf,Bp}(V) fF(15)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(379, 'Anti-Llama',      '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(380, 'Anvil',           's(4) pz(10) pz(12) zs(30) spz(V)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(381, 'Aysez',           's(U) z(U) %(U) n(U) g(U) t(U)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(382, 'barswanian',      'n(4) mg(6) mg(6) %(20) (X)! r(13,13)',       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(383, 'bigevildan',  'hdp(6) hdp(8) hdp(8) hdp(10) hdp(12) hdp(16) hdp(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(384, 'Binder',          '(4/12) (17) sp(4,20) (4/30)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(385, 'birdman',         'z(3) (4/30) (13) (13) (13)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(386, 'BlackOmega',      'tm(6) f(8) g(10) z(10) sF(20)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(387, 'bluebec',         't(7) z(13) (R)? g(4/16) o(6,15) +(Y)',       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(388, 'bobby 5150',  '^(3) fsp(R) ftz(14) tz!(1/30) r^(4) rz(12) r!(1/30)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(389, 'Bodie',           '(4) ^(5) ^(5) z(9,9)',                       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(390, 'bonefish',        'z(10) z(12) z(20) z(30)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(391, 'Boot2daHead',     'd(1) Gz(Z) !p(V) !n(Y,Y) oF(C)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(392, 'bowler190',       'z(4) z(4) z(20) z(20) z(20)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(393, 'braincraft',      'z(6) z(6) z(10) f(20) f(20)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(394, 'BrashTech',       '(4) f(6) p^(6) pz(12) (20)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(395, 'BugRoger',        '(4) (4) zg(18) zg(18) (10,10)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(396, 'CactusJack',      'z(8/12) (4/16) s(6/10) z(X) s(U)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(397, 'Caligari',        'f(4) fd(6) zn(12) m(12) ^q(S)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(398, 'Calmon',          '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(399, 'Cassandra',       'D(10) D(20) oH(T) @(Z)? (V,V)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(400, 'CestWhat',        'p(2) sf(12) sz(20) pho(8,8) t(T,T)!',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(401, 'chase',           '(4) (6) p%(10) (12) (X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(402, 'Conspyre',        '(1) t(2) t(8) z(12) t(X)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(403, 'Coyote',          'f(1/4) (4/20) z(10,10) ^(U)? s(X)!',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(404, 'Cristofore',      '^(9) f(12) g(12) z(20) (12/30)! rf(12)',     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(405, 'Darrin',          '(6) (6) z(12) Fq(R) Fq(R)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(406, 'Darthcliff',      'z(8,8) f(16)! sg(12) tzH(4) B(X)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(407, 'dexx',            'k(7) p?(X) o!(Z) G(3,17) t(5) g`(2)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(408, 'Discordia',       'df(12) df(12) Gp(16) Gp(16) (Z)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(409, 'dmm',             '(4) (6) z(C) (10,10) f(X)!',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(410, 'DocBlue',         'd(4) s(20) ^(4) (20) Ho(4)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(411, 'Downen',          'f(2) (6) p(13) z(14) z(X)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(412, 'Durak666',        'swz(30) swz(30) swz(30) swz(30)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(413, 'dwelsh',          '(3) (13) (37) H@(X) o(R)!',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(414, 'echopapa',        '(C) z^(20) @o(10) n(8) D(X)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(415, 'ElihuRoot',       '(3) h(10) h(10) n(20) n(30)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(416, 'eon',             't(4) n(6) d(8) B(16) B(X)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(417, 'Famous',          't(6) B(6) f(6) n(6) (10)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(418, 'Fanaka66',        'm(2) Ho(6) c(12) z(16) (6,6)',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(419, 'fendrin',         'f(3) nD(R) (1) n(2) Bp(U)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(420, 'fernworthy',      '(1) (2) z(12) z(20) `(4) `(6) `(8) `(10)',   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(421, 'Finngall',        'f(5) f(5) z(7,7) (23) B(X)?',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(422, 'flesh99',         '^(C) ^(C) zg(Z) p!(Y) z(W) gd(W)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(423, 'fnord',           'cz(10) ^(16)! t(X)? @o(V)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(424, 'fog',             'o@(S)? o@(T)? o@(R)? o@(U)? o@(T)?',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(425, 'Foxlady',         'fo(X) t(4) z(6,6) m(12) (Y)?',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(426, 'fxdirect',        't(1) (6) zp(12) s(12) p(X,X)!',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(427, 'gbrume',          '{^,t}(12) B(30) (30) (V)?',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(428, 'ghost walker',    'B(20) h(20) %(20) hp(20) hs(20)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(429, 'gman97216',       'Hog%(4) Hog%(4) Hog%(4) Hog%(4)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(430, 'GoldenOtter',     '^(2) v(6) vz(12) vz(X) v(W,W)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(431, 'GorgorBey',       'ft(5) ds(1/15) `G(5/10) !p(Y) wHz(12)',      0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(432, 'grayhawk',        'H(6) f(8) s(12) B(Y) g(Z)!',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(433, 'GreatWolf',    '^(3) B(10) s(Y) rws(20) rt(8) rG(X) rGp(Y,Y)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(434, 'grend',           '%(4) sp(8) z(8,8)? B(16) `(4) +z(8)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(435, 'GripTiger',       '^(10) s(11) d(12) z(13) (X)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(436, 'Grivan',          'GHo(6,6) (3) f(30) %Ho(2) r!(Y)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(437, 'Grym',            '(4) t(6) d(10) z(12) (X,X)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(438, 'Gryphon', '{g,sF}(10) {f,z}(12) {f,z}(12) (X)! +`(R)! ro@(Z)? rz(V,V) r{m,D}(8) gr(Y){p,h,o,n}', 0, 0, (SELECT id  FROM buttonset WHERE name="Classic Fanatics")),
(439, 'gslamm',          'Hn(6) Hn(6) hn(20) p(Y)? p(Z)?',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(440, 'hairlesswonder',  '(4) (7) B(20) B(20) B(20)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(441, 'hansolav',        'p^(8) z(4,6) q(12) z(Y) (X)!',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(442, 'haruspex',        '(99)',                                       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(443, 'Heath',           '(1/13)! z(3,13) H(13) md(13) (X)!',          0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(444, 'Heather',  'd(1) D(2) ^c(4) tz(14) rz(2) rz(4) rz(10) rz(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(445, 'Hooloovoo',     'q(T) q(W) q(X) q(Z) rn(R) rz(S) rp(U) rf(V)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(446, 'Hrodgar',         '(Z)? (T)? (X)? (X)? (X)?',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(447, 'icarus',          '^(3) szf(12) szf(20) t(Y,Y) G(Z)!',          0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(448, 'ifni',            'oHz(Y) oHz(1/2) oHz(2/4) oHz(20/30)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(449, 'IIconfused', 'fn(X) fn(X) gn(X) gn(X) `H(Y,Y) `d(Y)! `mp(Y) `^(1,Y)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(450, 'inundator', '{c,d}(12) {f,z}(12) {t,g}(12) {p,n}(12) {m,?}(X)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(451, 'itachi',          'fnz(4) (8) (12) fnz(20) (X)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(452, 'Jasyeman',        'B(8) H(8) p(8) z(8) z(U)?',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(453, 'jeffboyardy',     'o(10) (6/12) o(X)? (Y)? Bo(Z)?',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(454, 'Jennie',          'H(6) @(8) @(10) z(12) (U)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(455, 'Jenniegirl',      'Gst(S) Gst(S)? Gst(S)^ cor(V) cor@(X)!',     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(456, 'jgenzano',        '(4) (8) z(16) z(X) p(X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(457, 'Jordan',          'oH(10) (1/20)! (1/20)! (20) o^(11)',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(458, 'Jota',            'F(4/8) (6) (6/10) s(8) (30) +(12)',          0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(459, 'jrbrown78',       'z(8) z(12) z(15) z(X) ^(X)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(460, 'juelki',          '(6) (10) (12) p(X)? (X,X)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(461, 'kaddar', 'vpzf(20) {p,v,ht}(R) d(4/8) nF(4/16) z(8,8) `(2,1)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(462, 'Kaeriol',         '(4) (6) d(6) s(10) B(X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(463, 'KaijuGamer',      'Hz(4) Hz(6) Hz(8) Hz(10) Hz(20)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(464, 'kaufman',         'k(6) g(8) (10) z(10) (4,10)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(465, 'kestrel', 'df(3) %q(7,7) zs(17) ^t(3/23) p(Y)! rG(S) rg(U) r(V)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(466, 'kleric',          'Hwz(6) dc(6) m(7) oh(10) (X)?',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(467, 'Kurosuke',        'o(12,13) z(20,12) q(31) of(4/22) oz(22)',    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(468, 'lackey',          'd(4) d(6) d(8) `(4) `(6) `(8)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(469, 'LadyJ',           'dG(17) Ho(W)? q(X) ^B(T,T) (5)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(470, 'Limax',           '(W) om(34) (T)',                             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(471, 'Linnea',          '(4) B(8/17) G(8) o(12) (U)?',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(472, 'llippen5',        'd(4) d(6) d(8) d(10) d(12) d(20)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(473, 'luke ii',         'd(6) @(8) hz(12) c(20) (C)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(474, 'lunatic',         'p(3) d(6) t(8/12) (40) (40)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(475, 'maga',            'fz(X)? f(10) zqG(20) s(10) (8)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(476, 'malarson',        'oH(4) (5,5) z(20) %(20) (U)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(477, 'mgatten',         '(Y) (Y) ^(Y)! ^gst(Z,Z)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(478, 'mlvanbie',        'H(3) d(1) H(4) H(1) H(5)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(479, 'mneme',           'dg(20) dgH(4,4) fn(30) tB(Y) tB(Y)',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(480, 'moekon',          's(3) (7) (7) n(6,6) p(17)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(481, 'Moodster',        's(X) p(X) (X)? z(X) o(X)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(482, 'moussambani',     'n(2) f(4) (5,6) (20) wh(Y) (C)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(483, 'MrWhite',  'm(4) @(8) @(14) GF(9,9) rc(4) rz(8) r(14) rh(18)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(484, 'Mushu',           'm(U) sH(U) po(R) k(10)',                     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(485, 'myxozoa',         '(3) z(11) (21) c(Y) z(Z)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(486, 'nelde',           'td(Y)! td(Y)! td(Y)! td(Y)! td(Y)!',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(487, 'NeoVid',          'fs(6) pz(12) gd(10) {m,D}(T) `(Z)! `(R)!',   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(488, 'Nihlathak',       'wd(4) wd(4) (6) z(17) c(20)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(489, 'Noeh',            'z(15,15) tg(6) n(Y) f(4/20) sgv(17) `(1)',   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(490, 'NoopMan',         '%t(1,3) D(4) ns(20) z(X) c(Z)!',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(491, 'Notorious', 'G(2/12)! g(4/16)! G(8/20)! g(10/24)! rH(7) rH(11) rh(13) rh(17)', 0, 0, (SELECT id FROM buttonset  WHERE name="Classic Fanatics")),
(492, 'Oaktree',         'o@(11) (T) (R) (3) (3)',                     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(493, 'opedog',          'z(20) z(16) (6) p(X)',                       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(494, 'Pain',            't(4) (8) sz(16) (20) (V)? p(X)!',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(495, 'perlmunkee',      't(6) p(12) ^(12) z(20) z(20)',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(496, 'pgolo',           '(2) D(4) ^z(14) f(X)? rz(6) rz(10)',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(497, 'Pjack',           'q(2) z(3) (5) s(23) t(T,T)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(498, 'Polonius',        '(4) (4) s(10) z(12) (X)?',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(499, 'professorbooty',  'Ho(6) ^z(20) (V) fp(X)',                     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(500, 'randomlife',      '@(6) @(10) @(12) @(20) @(X)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(501, 'Raupe',           'm(4) GF(8) z(10) sf(20) (20)',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(502, 'RavenBlack', 'fs(10) zs(8) d(4) h(10) r(1) r(2) r(4) r(8) r(16)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(503, 'relsqui',         '^(1) (R) (31) (S) q(U)!',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(504, 'roujin27',        'p(6) n(8) p(8) n(12) n(20)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(505, 'Rumbles',         'o(R) o(T) of(10) om(10)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(506, 'Sabathia',        'f(4) ^(5) H(6) m(7) z(22)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(507, 'SailorMur',       'g(10) sp(12) t(4) (10/20) ^(X)!',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(508, 'sanny',           't(2) (4) z(4) p(X) s(X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(509, 'santiago',        'D(2) p(4/14) t(5,5) zs(18) (6/16)!',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(510, 'SC (The Deuce)',  'z(4) t(6) p(8) s(10) (12/16)! +(20)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(511, 'schwa',           'f(V) t(W) d(X) v(Y) B(Z)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(512, 'seeker',          'oH(2) o@(6) o@(8) o@(12) o@(Y)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(513, 'Shadowkeeper',    's(4) s(4) s(4) s(4) s(4) s(U)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(514, 'skapheles', 'df^(6) z(20) zp(12) spg(X) df(8) rz(12) rg(10) r(12) r(20)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(515, 'Skeeve',          'o(V)? o(W)? o(X)? o(Y)? o(Z)?',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(516, 'slamkrypare',     't(1) (10) (10) z(12) (Y)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(517, 'smallfrogge',     'H(4) m(8) B(12) (X)? (X)!',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(518, 'Snuff',           'f(4) f(4) ^p(Y)! gns(Z,Z) gs(Z,Z)',          0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(519, 'spindisc',        'd(2) p(6/20)! zp(12) n(T,T) B(X)? +(Y)',     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(520, 'Squiddhartha',    'f(4) f(6) g(10) pz(X)! (Z) r(4,4) rz(6,6)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(521, 'Stick',           'p(3) (6,6) d(8) tzn(16) (S)!',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(522, 'stoooooooo',      'c(8) H(10) n(4) o(2)',                       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(523, 'SyberCat',        '(6) {g,t}(6) (10) {f,z}(12) (V)? +d(12)',    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(524, 'Tasslehoff B', 't(6) (4/20)! {fz,gz}(12) svG(20) (T) `(1) `(2) rp`(4) rp`(6)', 0, 0, (SELECT id FROM buttonset  WHERE name="Classic Fanatics")),
(525, 'TheFool',         'v(5) v(10) vq(10) vs(15) s(R,R)?',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(526, 'TheMachine',      'dp(1) B(1/30)! tD(4) q(7,11) rcz(20)',       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(527, 'Torch',           'p(6) p(8) z(10) z(10) zsp(V)!',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(528, 'Totoro',          'fsvG(13) (20) (20) (20) gvz(30)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(529, 'trifecta',        'm(3) m(3) m(3) m(3) p(3/33)! rn(3) rD(3)',   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(530, 'Trouble',         'd(6) ^(7) @(12) B(20) @o(Z)?',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(531, 'Tweedledum',      't(4,4) @(8) ^(12) z(20) fp!(Y)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(532, 'TwistedRich',     'z(1,1) z(4) z^(4) z(6) (X)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(533, 'TYFTFB',          'H(2) d(8) z(8) tp(10) G(R)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(534, 'UncleMilo',       '(6) p(8) (12) %(16) B(20)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(535, 'Urbanmyth',       'z(8) g(10) z(10) (15) (X)?',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(536, 'vhoodoo',         '(5) ^s(9) (C) B(17) (Z)?',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(537, 'Vysion',          'o(9) p(9) s(9) z(9) (9,9) r^(9)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(538, 'weirdbal',        '@(V)? @(V)? @(V)? @(X)? @(X)?',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(539, 'wembley-fraggle', 'spf(10) spf(10) (16) (S) (S)',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(540, 'Weylan',          '(4) ^(3) f(6) m(8) sg(12) (R)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(541, 'wranklepig',      'pB(17) Fo(13) q(11) gc(7) nt(5)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(542, 'wtrollkin',       'p(4) pG%(7) s(15) sB(S) s%(S)! worm(Y)',     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(543, 'Yagharek',        'p(3) p(5) (7) (11) cz(13)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(544, 'yakboy',          'o@(5) ms(6) p%(W) hot(20) (X)?',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(545, 'zaph',            '(4) z(4,4) s(8,8) p(16) (1/24)!',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(546, 'Zegota',    'z(8) z(12) z(20) z(30) p(1) rz(10) rz(20) rp(1)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(547, 'Zomulgustar', 't(4) p(5/23)! t(9) t(13) rdD(1) rsz(1) r^(1,1) rBqn(Z)?', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(548, 'Zophiel',         'k(1) (6) z(8,8) H(12) (Y)?',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
(549, 'Zotmeister',      'd(1) d(1) d(2,2) d(8,8) rd(1) rd(26,26)',    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics"));

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(788, 'AnnoDomini',       '(W) (4) (R) s(4) (W)',                          0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(789, 'blackshadowshade', 'mkht(T)! Bt(T)& `fg(4) bHt^(0/10) F%(1,10) rbHt^(0/10) rnDt^(1,8)', 1, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(790, 'Blargh',           'Hop(4) hop(10) Mh(8) Mf(8) MF(13)',             0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(791, 'devious',          'dv(S) (16) (16) pqr(S,S) Jm`(0) Jm`(0) Jm`(0)', 0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(792, 'jimmosk',          '(4) %(8) g(12) JIMmo(S) k(2)',                  0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(793, 'jl8e',             'f(7) f(7) p(16) (16) ^#(R,R)',                  0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(794, 'Nala',             'Mkw(1) Mk(1) Mk(3) Mk(9) Mk(27)',               0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(795, 'Nonce Equitaur',   '(S) (3)q (U)It (U) (R)!',                       0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(796, 'tavella',          '(5)t^ (23)v# (19)I (6)I^ (7)!',                 0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics"));

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(805, 'Bosco312',         'z^(20) z^(10) d(1) d(1)',                       0, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics")),
(806, 'Cycozar',          '(T) (4)g (Y) o(U)rIt! ^%(3) c(Y) coz(4)r #(1)', 0, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics")),
(807, 'GamerXYZ',         'Ho(4) dk(6) sz(X) qt(Y) `M(Z)',                 0, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics")),
(808, 'irilyth',          'Gg(8) Gg(16) Gg(32) `sptH(1) `sptH(2) `sptH(3) `sptH(5) `sptH(8) spohr(4,9) spohr(9,16) spohr(16,25)', 1, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics")),
(809, 'Scipio',           'bt(1) s(8) (3/19)! (5/23)! #(X)',               0, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics"));

#############################################
#####   S P E C I A L   B U T T O N S   #####
#############################################
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id, sort_order) VALUES
(10001, 'RandomBMVanilla',    '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 10),
(10002, 'RandomBMAnime',      '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 20),
(10003, 'RandomBMMixed',      '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 30),
(10004, 'RandomBMFixed',      '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 40),
(10005, 'RandomBMMonoskill',  '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 50),
(10006, 'RandomBMDuoskill',   '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 60),
(10007, 'RandomBMTriskill',   '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 70),
(10008, 'RandomBMTetraskill', '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 80),
(10009, 'RandomBMPentaskill', '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 90),
(10010, 'RandomBMSoldiers',   '', 1, 0, (SELECT id FROM buttonset WHERE name="RandomBM"), 100),
(11000, 'CustomBM',           '', 1, 0, (SELECT id FROM buttonset WHERE name="CustomBM"), 0);




#####################################################
#####   B U T T O N   D E S C R I P T I O N S   #####
#####################################################

UPDATE button
SET flavor_text='This button has a custom recipe'
WHERE name='CustomBM';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a vanilla random formula'
WHERE name='RandomBMVanilla';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a fixed random formula'
WHERE name='RandomBMFixed';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with a mixed random formula'
WHERE name='RandomBMMixed';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with an anime formula'
WHERE name='RandomBMAnime';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with four regular dice and one swing die, and 1 skill appearing a total of 2 times on various dice.'
WHERE name='RandomBMMonoskill';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with four regular dice and one swing die, and 2 skills each appearing a total of 2 times on various dice.'
WHERE name='RandomBMDuoskill';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with four regular dice and one swing die, and 3 skills each appearing a total of 2 times on various dice.'
WHERE name='RandomBMTriskill';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with four regular dice and one swing die, and 4 skills each appearing a total of 2 times on various dice.'
WHERE name='RandomBMTetraskill';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, with four regular dice and one swing die, and 5 skills each appearing a total of 2 times on various dice.'
WHERE name='RandomBMPentaskill';

UPDATE button
SET flavor_text='This button gets a different random recipe in each game, similar to buttons in the Soldiers set: Four regular dice plus one X swing die, no skills on any dice.'
WHERE name='RandomBMSoldiers';

UPDATE button SET flavor_text="Rikachu is a bootleg Button Man created at Origins. He's a Pokedog - from the Pokeman ad opposite the Tess page." WHERE name="Rikachu";
UPDATE button SET flavor_text="ConMan plays games at conventions until (and sometimes past) dawn, drinks coffee like it's water, and likes to beat people up." WHERE name="ConMan";
UPDATE button SET flavor_text="The Cheese Weasel is a clever and cunning rodent, bent on quiet world domination. He likes cheese (duh!), any opportunity for eating cheese, stealing cheese...and beating people up if they attempt to come between him and his cheese." WHERE name="Cheese Weasel";
UPDATE button SET flavor_text="Bennett is a big strong boy who loves to take his time. He enjoys math, videotapes, and beating people up." WHERE name="Bennett";
UPDATE button SET flavor_text="Chris knows what she likes and she knows how to get it. She likes chemistry, going to the park, and beating people up." WHERE name="Chris";
UPDATE button SET flavor_text="Darwin is young entrepreneur who enjoys politics, French, and beating people up." WHERE name="Darwin";
UPDATE button SET flavor_text="Hale is a considerate fellow and excels in every class. He is never too busy to stop and beat people up." WHERE name="Hale";
UPDATE button SET flavor_text="Morgan knows the rules. She never does anything without a reason, and she loves to beat people up." WHERE name="Morgan";
UPDATE button SET flavor_text="Pearl can lift almost anyone over her head. She likes sports, theater, and beating people up." WHERE name="Pearl";
UPDATE button SET flavor_text="Agent Orange is the first piece of citrus to ever pursue a career in international espionage. He is licensed not only to kill, but also to play ping-pong and beat people up." WHERE name="Agent Orange";
UPDATE button SET flavor_text="This warrior deity of the ancient Aztec religion enjoys playing ping-pong, being worshipped, and beating people up." WHERE name="Huitzilopochtli";
UPDATE button SET flavor_text="Lisa is a friend of the programmers who is easily talked into appearing in their games. She likes her stuffed Cthulhu doll, heavy metal, fuzzy animals, game shows, and beating people up." WHERE name="Lisa";
UPDATE button SET flavor_text="Once one of the most feared of all wizard duelists, this evil magic user now spends most of his time beating people up." WHERE name="Nethershadow";
UPDATE button SET flavor_text="Phuong is another friend of the programmers. She enjoys dancing, appearing on game shows, and practicing Tae Kwon Do so that she may beat people up." WHERE name="Phuong";
UPDATE button SET flavor_text="Uncle Scratchy runs a flea-racing track. Tired of being stepped on, he has gotten quite good at beating people up." WHERE name="Uncle Scratchy";
UPDATE button SET flavor_text="Bane needlessly worries about things he can never change. He likes cowboys, empty paint cans, and beating people up." WHERE name="Bane";
UPDATE button SET flavor_text="Bluff can not be described by a flippant sound bite. She likes Crusher, bobsled racing, and beating people up." WHERE name="Bluff";
UPDATE button SET flavor_text="Coil can say the alphabet backwards and sometimes does it without noticing. She likes to be told things she already knows, and she likes to beat people up." WHERE name="Coil";
UPDATE button SET flavor_text="Crusher is a mean-spirited bruiser with a heart of stone. He likes ikebana, enforcing the whims of callous superiors, and beating people up." WHERE name="Crusher";
UPDATE button SET flavor_text="Grist is an avid student of Sun Tzu who can move objects with his arms and legs. He likes fast cars, manicures, and beating people up." WHERE name="Grist";
UPDATE button SET flavor_text="Jellybean is a prominent patent attorney who can think with his spines. He likes collie-spaniel crosses, red ink, and beating people up." WHERE name="Jellybean";
UPDATE button SET flavor_text="Lucky's father was murdered by an inconsiderate house guest. She likes road movies, vengeance, and beating people up." WHERE name="Lucky";
UPDATE button SET flavor_text="Peace lives in exile on the island of St Helena, where she enjoys swing dance, solitaire, and beating people up." WHERE name="Peace";
UPDATE button SET flavor_text="Reaver takes unparalleled pleasure in bringing joy to her fellow beings. She enjoys surprise gift giving, platonic love, and beating people up." WHERE name="Reaver";
UPDATE button SET flavor_text="Shepherd has made ignorance of obvious things his lifelong ambition. He likes spoons, forgetting how to do stuff, and beating people up." WHERE name="Shepherd";
UPDATE button SET flavor_text="Strik becomes more accustomed to his new lifestyle with each passing day. He likes quiet walks in the moonlight, bashing things with his head, and beating people up." WHERE name="Strik";
UPDATE button SET flavor_text="Wastenott is dead, dead, dead, dead. He likes getting out once in a while, talking to old friends and beating people up." WHERE name="Wastenott";
UPDATE button SET flavor_text="Al-Khwarizmi wrote an amazing book about solving everyday problems in life and business with mathematics. Our word \"algebra\" came from the title of his book, and the term \"algorithm\" is from a Latin translation of his name." WHERE name="Al-Khwarizmi";
UPDATE button SET flavor_text="Best known for his Theory of Numbers, Carl Friedrich Gauss startled his grade school teacher by deducing that the sum of the integers from 1 to 100 was really the sum of 50 pairs of integers which all add to 101." WHERE name="Carl Friedrich Gauss";
UPDATE button SET flavor_text="Before becoming the Akamai Professor of Internet Mathematics at UC San Diego, Dr. Fan Chung Graham directed math research labs at Bellcore. She developed semi-random routing theories that keep the Internet running." WHERE name="Fan Chung Graham";
UPDATE button SET flavor_text="Leonard Eugene Dickson loved pure mathematics. He wrote 267 research papers and 18 books, including the three volume \"History of the Theory of Numbers.\" His favorite quote: \"Thank God that number theory is unsullied by any applications.\"" WHERE name="Leonard Eugene Dickson";
UPDATE button SET flavor_text=CONCAT("One of the best known and most respected mathematicians of the 20th century, Paul Erd&#337", CHAR(59), "s (air-dish) wrote or co-authored 1,475 papers. As he put it, \"A mathematician is a machine for turning coffee into theorems.\"") WHERE name="Paul Erdos";
UPDATE button SET flavor_text="Pythagoras and his team in Italy, called the mathematikoi, developed cool triangle theorems, described the five regular solids, and thought that numbers had personal characteristics like male, female, beautiful, and ugly." WHERE name="Pythagoras";
UPDATE button SET flavor_text="Known informally as \"The Mathemagician,\" Dr. Ron Graham was Chief Scientist at AT&T Labs. He was treasurer of the National Academy of Sciences, and also the president of the International Jugglers Association." WHERE name="Ron Graham";
UPDATE button SET flavor_text="Theano wrote about mathematics, physics, medicine, and child psychology. She's best known for her work on the \"Golden Mean\" and the \"Golden Rectangle.\"" WHERE name="Theano";
UPDATE button SET flavor_text="Tirade is a sentient broccoli. He is a master of time, space and dimension, and has 50 dollars to spend on candy and gum. He also likes to beat people up." WHERE name="Tirade";
UPDATE button SET flavor_text="The king's loyal nephew, he fights with the strength of three men." WHERE name="Gawaine";
UPDATE button SET flavor_text="The Queen of the Court of Love, torn between her husband and her lover." WHERE name="Guenever";
UPDATE button SET flavor_text="King of the Britons, the embodiment of Glory and Honor, and betrayed husband." WHERE name="King Arthur";
UPDATE button SET flavor_text="The best knight in the world, torn between his love for the queen and duty to his king." WHERE name="Lancelot";
UPDATE button SET flavor_text="The great magician who advises the king, but who always has his own agenda." WHERE name="Merlin";
UPDATE button SET flavor_text="The king's bastard son, he has had villainy thrust upon him and revels in it." WHERE name="Mordred";
UPDATE button SET flavor_text="The dark enchantress who beguiles all with mysterious faerie magic." WHERE name="Morgan Le Fay";
UPDATE button SET flavor_text="The Lady of the Lake, she holds the powers of Excalibur and Avalon." WHERE name="Nimue";
UPDATE button SET flavor_text="Bill sells \"items\" and \"objects\" at a game store of which he is the proprietor. His many powers include \"discounting\" and \"print advertising\" and he likes to beat people up. (dressed as Iago)" WHERE name="Bill";
UPDATE button SET flavor_text="Carson is a muskrat whose secret origins are steeped in mystery. He enjoys mopping the floor all day, and he likes to beat people up. (dressed as Kublai)" WHERE name="Carson";
UPDATE button SET flavor_text="Gilly is a perky goth who likes to play games. She enjoys just about everything, just about everything else, and beating people up. (dressed as Clare)" WHERE name="Gilly";
UPDATE button SET flavor_text="Igor is a vicious, sweaty man with a garbage can on his head. He likes long walks in the park, red rhubarb jelly and beating people up. (dressed as Hammer)" WHERE name="Igor";
UPDATE button SET flavor_text="Ken is a hard-riding hobbyist with a heart of gold. He likes frivolity, jumping up and down, running with his tongue hanging out, and beating people up. (dressed as Bauer)" WHERE name="Ken";
UPDATE button SET flavor_text="Matt is as loveable as the day is long. Just look at that cute widdow nose. He likes role-playing, the unexpected, and beating people up. (dressed as Hannah)" WHERE name="Matt";
UPDATE button SET flavor_text="Sven is a Trow (troll) who likes to trade recipes with the other queens at the leather bar, swap housekeeping tips with his neighbors, prune roses, and beat people up." WHERE name="Sven";
UPDATE button SET flavor_text="Yseulte is a Fuah (bad water faery) who lives by the Maine Avenue fish market. She likes to party with the Grrls, ride vintage Harleys, smear women's makeup, collect scallop shells, and beat people up." WHERE name="Yseulte";
UPDATE button SET flavor_text="Aldric can belch the anthem of his home town and once drank an entire tankard of beer. He likes damsels, horses, and beating people up." WHERE name="Aldric";
UPDATE button SET flavor_text="Cesare is the banished heir to the throne of a large kingdom. He likes plotting revenge, procrastinating, and beating people up." WHERE name="Cesare";
UPDATE button SET flavor_text="Chang is far less intelligent than most people think he is. He likes long naps, pieces of string, and beating people up." WHERE name="Chang";
UPDATE button SET flavor_text="Elihu senses danger at all times, which makes him tense and irritable. His mind is filled with trivial detail, and he loves to beat people up." WHERE name="Elihu";
UPDATE button SET flavor_text="Farrell spends his weekends by the seaside, watching re-runs and knitting sweaters. But during the week, he likes to beat people up." WHERE name="Farrell";
UPDATE button SET flavor_text="Frasquito is a very short person and the self-proclaimed inventor of lye. He likes fruit trees and oysters, and he likes to beat people up." WHERE name="Frasquito";
UPDATE button SET flavor_text="Lark is a triple Mud Baron on the Planet of the Bird People. He likes waffling, collecting shiny objects, and beating people up." WHERE name="Lark";
UPDATE button SET flavor_text="Luce is a healer and a clever prestidigitator. She can keep children amused for hours using only a pencil, and she likes to beat people up." WHERE name="Luce";
UPDATE button SET flavor_text="Mischa is a tracker whose well-honed nature skills often lead her to the mall. She likes culottes, vegetarian pizza, and beating people up." WHERE name="Mischa";
UPDATE button SET flavor_text="Nikolai eat cow, Nikolai smash house with club. Nikolai hate loud noise. Nikolai love beat people up." WHERE name="Nikolai";
UPDATE button SET flavor_text="Theophilus is a one-eyed giant who knows the future. He is sometimes morose, often melancholy, and always beating people up." WHERE name="Theophilus";
UPDATE button SET flavor_text="Vincent has the cunning of a fox and the table manners of a six-year old boy. He likes tofu, library paste, and beating people up." WHERE name="Vincent";
UPDATE button SET flavor_text="What is love? Only the guy with the big green brain in his hand knows for sure." WHERE name="Max";
UPDATE button SET flavor_text="Mister Peach teaches old ladies how to dance. He is also creepy." WHERE name="Mister Peach";
UPDATE button SET flavor_text="The Doctor is in." WHERE name="Simon";
UPDATE button SET flavor_text="Werner lives underwater and is a crustacean with a helmet. Understandably, people do not trust him." WHERE name="Werner";
UPDATE button SET flavor_text="Dream Wyrm is a dragon (duh!). His hobbies include terrorizing, arson, and trying to defeat the Gaming Guardians." WHERE name="Dream Wyrm";
UPDATE button SET flavor_text="EDG is the 'second in command'. His hobbies include punching people he doesn't like where it hurts, insulting Graveyard Greg, and guarding the gaming systems." WHERE name="EDG";
UPDATE button SET flavor_text="Graveyard Greg is a Gaming Geek. His hobbies include collecting games, playing games, and guarding the gaming systems." WHERE name="Graveyard Greg";
UPDATE button SET flavor_text="Memento-Mori is an evil goth. His hobbies include not playing games, designing unplayable game systems, and trying to defeat the Gaming Guardians." WHERE name="Memento-Mori";
UPDATE button SET flavor_text="Radical is a deus ex machina. Her hobbies include reading, 80s trivia, and guarding the gaming systems." WHERE name="Radical";
UPDATE button SET flavor_text="Randarch is the self-proclaimed 'Savior of the Gaming Industry'. His hobbies include washing his hair, bossing people around, and letting other people guard the gaming systems." WHERE name="Randarch";
UPDATE button SET flavor_text="Scarlet Jester is evil incarnate. His hobbies include destroying gaming systems, manipulating events in his favor, and trying to annihilate the Gaming Guardians." WHERE name="Scarlet Jester";
UPDATE button SET flavor_text="Shane Hensley is the most evil game designer in existence. His hobbies include making alternate history RPGs, tricking people into thinking he's a nice guy, and trying to defeat the Gaming Guardians." WHERE name="Shane Hensley";
UPDATE button SET flavor_text="The Flying Squirrel has the proportionate strength, speed, and cheek space of a North American gray squirrel. He likes breaking the law, cavorting with squirrels, hibernating all winter, and beating people up." WHERE name="The Flying Squirrel";
UPDATE button SET flavor_text="The Japanese Beetle fights for truth, justice, and the American way - as long as it doesn't get in the way of his quest for fame and fortune. He likes tall blondes, sexy communists, and beating people up." WHERE name="The Japanese Beetle";
UPDATE button SET flavor_text=CONCAT("One Clan stands between the Jade Empire and the fell demons of the Shadowlands: the Crab. Unlike the other clans, the Crab have never known peace. They have no respite, no rest, no hope for mercy, and no remorse. For a thousand years they have manned the Kaiu Walls, fighting ruthlessly against endless assaults. Without the Crab, Rokugan would be in flames.<br><br>You are a young samurai of the Crab Clan, already battle-hardened by fending off massed Shadowlands assaults upon the Kaiu Walls. You have no time for niceties or etiquette", CHAR(59), " you guzzle water from a bucket and have no interest in sipping tea from a tiny porcelain cup.<br><br>Since the self-sacrifice of your heroic daimyo Hida Yakamo, his younger sister, Hida O-Ushi, has become leader of the Crab. She is a worthy samurai, dark and beautiful, and is never seen without her hammer slung casually over her shoulder. Though feared far and wide as \"the bully,\" you know she has a quick (if dark) sense of humor and is a very capable leader and expert fighter.<br><br>It is for her sake that you have entered the Test of the Topaz Champion. This annual event pits the best and brightest of the young samurai against each other. The winner gains glory for the clan and wins an invitation to the prestigious Kakita Dueling Academy.") WHERE name="Crab";
UPDATE button SET flavor_text="Where there is grace, there is the Crane, beautiful, refined, and honorable. To watch a Crane is to watch poetry in motion, be it a courtier delicately turning the eyes of the Empire to look upon you in scorn, or an iaijutsu duelist from the Kakita Academy drawing a blade and slicing your head from your shoulders in a fluid motion so fast that it passes in the blink of an eye.<br><br>You are a young Crane samurai, whose honorable clan is called \"the left hand of the Emperor.\" Yours is the grace of the courtier, the elegance of the peacock, the honor of the heavens, and the deadly dueling strike of a bolt of lightning. You uphold the highest ideals of the image of the warrior-poet.<br><br>Some think the Crane are soft. You know better. Doji Kuwanan has led your clan since the death of your previous daimyo at the Day of Thunder. Resplendent in his flowing kimono with twin banners on his back, he is a fearsome sight on the battlefield, and the speed with which he wields his spear is the ultimate expression of the Crane dueling motto: \"You blink, you die.\"<br><br>This is the Test of the Topaz Champion, an annual event that pits the best and brightest of the young samurai against each other. The winner gains glory for the clan and wins an invitation to your clan's dueling academy. As a Crane, you can attend, but you will earn the right to prove yourself truly worthy." WHERE name="Crane";
UPDATE button SET flavor_text="Shrouded in mystery for centuries, the Dragon embrace magical secrets the Phoenix do not understand, and use a two-sword technique that flusters even the Crane. But most mysterious of all are the ise zumi, the tattooed monks. Stories abound of ise zumi breathing fire, flying through the air, or withstanding sword blows to their naked chest. No one understands the Dragon.<br><br>You are a young Dragon samurai come down from the mysterious Dragon Clan strongholds deep in the mountains. You are aloof, raised in relative isolation with the mysterious tattooed monks and trained in the deadly Mirumoto two-blade technique. You need no friends.<br><br>The Dragon thrive on isolation. Consider Hitomi, the daimyo of the Dragon Clan. Her vendetta against a Crab samurai caused her to be ostracized even before her Obsidian Hand began to cover her body in cold, black glass. Now she sits alone upon the Dragon throne, and while others consider her an outcast, you know she is meditating in her solitude, preparing to fight with a god.<br><br>It is for her sake that you have entered the Test of the Topaz Champion. This annual event pits the best and brightest of the young samurai against each other. The winner gains glory for the clan and wins an invitation to the prestigious Kakita Dueling Academy. Even if they only teach the use of one sword." WHERE name="Dragon";
UPDATE button SET flavor_text="The Lion Clan is indisputably the dominant military force in the Jade Empire. They are \"the right hand of the Emperor.\" In the entire history of the Empire, an army led by an Akodo general has never been defeated. The weight of this long history of success is what drives the Lion armies forward in roaring ranks of warriors fanatically charging and destroying all opposition.<br><br>You are a fearless young soldier of the Lion Clan. Your mind is filled with Bushido, the way of the warrior. Your heart is filled with the courage of a hundred generations of your ancestors, urging you forward. Your training is as sharp as a lion's claws, honed by endless hours of practice.<br><br>The history of the Lion Clan sets a high standard. As the right hand of the Emperor, the Lion's duty is to crush those who oppose the will of the Jade Throne. Matsu Ketsui, the head of the Matsu family, is such a warrior. Her close-cropped hair, light armor, and face paint give her the look of a predator, and hundreds of the dead have been taught her lesson of \"one strike, one kill.\"<br><br>It is for the honor of your ancestors that you have entered the Test of the Topaz Champion. This annual event pits the best and brightest of the young samurai against each other. The winner gains glory for the clan and wins an invitation to the prestigious Kakita Dueling Academy. You know only one action: to win." WHERE name="Lion";
UPDATE button SET flavor_text="The Yoritomo Alliance is a confederation of minor clans united under the Mantis Clan. Led by Yoritomo, the Son of Storms, the Alliance has gained a place among the Great Clans. But Mantis plans have not stopped there. Once mercenaries, your clan will use their new-found power to gain greater power, for in this age you must forge your own destiny on the bodies of your enemies.<br><br>You are a young samurai of the Mantis Clan, the dominant clan of the Yoritomo Alliance. Though others dismiss you as a petty mercenary, you are one of the most ruthless samurai in the Jade Empire. You take the Mantis emblem very seriously and emulate the insect: direct, ruthless, and remorseless.<br><br>You are here by virtue of the daring of your daimyo, Yoritomo. He forged the Alliance, and he forced the Great Clans to deal with the Mantis and its allies as equals. He has taught the Mantis to take what they want, seize it in their claws and keep it, be it territory or gold or recognition. You have learned your lesson well. You have decided that you will take the Topaz Championship.<br><br>The Test of the Topaz Champion is an annual event that pits the best and brightest of the young samurai against each other. The winner gains glory for the clan and wins an invitation to the prestigious Kakita Dueling Academy. The Championship will be yours. Let no one stand in your way." WHERE name="Mantis";
UPDATE button SET flavor_text="Serpent people from an ancient time before human history, the Naga are as dangerous as they are alien. They are experts in ambush and evasion, powerful in strength and number, and the finest archers in all of Rokugan. They often speak of the Akasha, the soul of their race, but as yet no human truly understands their meaning. Human perspective is so pitifully short.<br><br>You are an Ashamana, a young one of the Naga. Your name is not important, for you are a part of the Naga's collective racial memory, the Akasha, and your soul is but a part of the whole. You have been born a hundred times before, and you will be born a thousand times again. Death is not important.<br><br>But life is. You live to serve your people, and you will do it as best you can. Be worthy, and you will receive new responsibilities, perhaps even take the title of the Shahadet or the Qamar. Or, perhaps, if you can prove yourself a better warrior than all of these small people with legs, the humans will revere your kind, and you will become an emissary. Then you would create a new title.<br><br>You have entered the Test of the Topaz Champion to study as much as to win. This annual event pits the best and brightest of the young samurai against each other. The winner gains glory for the clan and wins an invitation to the prestigious Kakita Dueling Academy. You are curious. Very curious." WHERE name="Naga";
UPDATE button SET flavor_text="A clan devoted to the mastery of magic, the Phoenix Clan was destroyed when, seeking to understand the power of the Shadowlands, the Elemental Masters fell to its grotesque corruption. The clan is now rebuilding, and noviciates are again exploring the mysteries of Earth, Air, Fire, Water, and the Void. The Phoenix Clan is rising from the ashes, bringing the voice of peace to Rokugan.<br><br>You are a samurai of the Phoenix Clan, though, unlike the others around you, you are not trained in Bushido, the way of the warrior. You are a shugenja, a practitioner of magic. Your endless training has given you insights into the spiritual nature of the world that no bushi could ever understand.<br><br>You hope someday to master an element, following in the footsteps of Isawa Hochiu, the Acolyte of Fire. He is a gentle soul, and while he is as fast as the flame, he spares the lives of those he fights. He taught you that the sword is the way of war, while magic and its attendant prayers are the way of peace. Still, you must learn to fight, for if you know how to fight, you will never need to.<br><br>It is to prove the power of a calm and peaceful mind that you have entered the Test of the Topaz Champion. This annual event pits the best and brightest of the young samurai against each other. The winner gains glory for the clan and wins an invitation to the prestigious Kakita Dueling Academy." WHERE name="Phoenix";
UPDATE button SET flavor_text="In an Empire where samurai are known by their clan, you have chosen your own path. You will not bow to tradition forced by parentage, nor will you kill whomever your so-called superior orders you to. You are a ronin, a \"wave man,\" moving where you will. Granted, the clans give you no respect, but you know in your heart you do what's right, for the right reason.<br><br>You are a young samurai warrior. You owe your allegiance to no one. You have learned your skills mostly through practice against bandits and arrogant samurai, with a lesson or two provided by a sensei who recognized your true potential. Though it has its drawbacks, the free life is a good one.<br><br>Take Dairya, for example, the famous ronin. Yes, a Crane took his eye once, but that Crane now lies dead, and no other Crane seems inclined to continue the feud. Dairya wanders the Empire, living life as he wishes. And although he has worked with Emperor Toturi lately, he does not work for him. As he says, \"Some call me a masterless man. They are wrong. I am my own master.\"<br><br>You have entered the Test of the Topaz Champion to prove that honor and skill are not determined by clan. This annual event pits the best and brightest young samurai against each other. Should you win an invitation to the prestigious Kakita Dueling Academy, the presence of a ronin will embarrass them." WHERE name="Ronin";
UPDATE button SET flavor_text="Charged with the task of being the under-hand of the Emperor, the Scorpion dutifully execute vile and tasteless deeds beneath the honor of the one who sits on the throne. Consorts of ninja and scholars of poison are the Scorpion. There is said to be no secret but a Scorpion knows it, no trick but a Scorpion sees through it, no mystery but a Scorpion lies in wait two layers deeper.<br><br>You are a young samurai of the Scorpion Clan, also called \"the under-hand of the Emperor.\" You wear a mask, so that those who stand against you know not to trust you. In combat as in life, you will use any trick, no matter how low. Better to succeed dishonorably than to fail with dignity.<br><br>Bayushi Togai, one of the most famous of the Scorpion, is your ideal. Proudly arrayed in the blood-red and black armor of the Scorpion, he brags of his mastery of poisons. Only a Scorpion could be so brazenly dishonorable and yet survive, for the fear of Togai's poisons ensures he has no enemies. You will follow him, for true Scorpions will tread the darkest paths without hesitation.<br><br>You've entered the Test of the Topaz Champion to better understand the enemy. This annual event pits the best and brightest of the young samurai against each other. The winner wins glory for the clan and an invitation to the prestigious Kakita Dueling Academy, where you will learn more of their secrets." WHERE name="Scorpion";
UPDATE button SET flavor_text="The Unicorn Clan are expert riders hardened by centuries spent exploring the Burning Sands and other strange lands beyond the borders of the Empire. While many dismiss them as uncivilized boors using \"barbarian tricks,\" they are the finest cavalry in the Empire, and their great steeds the swiftest and most powerful. Few samurai witness a Unicorn charge and live to tell the tale.<br><br>You are a young Unicorn samurai, born in a tent and raised in the saddle. The famous speed and size of the Unicorn steeds has infected you with a sense of adventure and an unquenchable thrill for speed. Of those gathered here, only you have been beyond the Empire, and you know many \"barbarian tricks.\"<br><br>Otaku Kamoko, once leader of the Battle Maidens, is now the daimyo of your clan and the epitome of what it means to be a Unicorn samurai. She is fearless on the attack, charging headlong into massed formations of the enemy, splitting them open with her sword or crushing them beneath the hooves of her horse. She taught you that every battle can be won with speed and daring.<br><br>You will prove this with your victory at the Test of the Topaz Champion. This annual event pits the best and brightest of the young samurai against each other. The winner gains glory for the clan and wins an invitation to the prestigious Kakita Dueling Academy." WHERE name="Unicorn";
UPDATE button SET flavor_text="Gratch steals from the rich to give to himself. He likes bonsai gardens, redistributing the wealth, and beating people up." WHERE name="Gratch";
UPDATE button SET flavor_text="Ginzu thinks samurai taste like chicken. He enjoys senseless destruction, contemplating the Tao, and beating people up." WHERE name="Ginzu";
UPDATE button SET flavor_text="Charity wants to be a social worker when she grows up. When not making grouchy faces, she likes to beat people up." WHERE name="Charity";
UPDATE button SET flavor_text="Chastity wants to be an out-of-work actor when she grows up. She likes her ballet lessons and beating people up." WHERE name="Chastity";
UPDATE button SET flavor_text="Faith wants to be an eschatologist when she grows up. She likes to watch violent cartoons and beat people up." WHERE name="Faith";
UPDATE button SET flavor_text="Hope wants to be a navy seal when she grows up. Besides eating her broccoli, she likes to beat people up." WHERE name="Hope";
UPDATE button SET flavor_text="Prudence wants to be an entomologist when she grows up. She likes chloroforming butterflies and beating people up." WHERE name="Prudence";
UPDATE button SET flavor_text="Temperance wants to be a postal worker when she grows up. She likes small defenseless animals and beating people up." WHERE name="Temperance";
UPDATE button SET flavor_text="Spells and mystic zaps are what Artax does best. His two best words of power are Abracadabra and beatpeopleup." WHERE name="Artax";
UPDATE button SET flavor_text="Nodwick is a henchman extraordinaire! He can pull his weight and everyone else's while getting beaten up." WHERE name="Nodwick";
UPDATE button SET flavor_text="Divine might and duct tape let Piffany put Nodwick back together as she helps beat naughty people up." WHERE name="Piffany";
UPDATE button SET flavor_text="Yeagar is a warrior of renown. He likes ale kegs and using henchmen as catapult ammunition to beat people up." WHERE name="Yeagar";
UPDATE button SET flavor_text="A twisted scientist and practitioner of Pre-Colombian Neurosurgery, the good Doctor would love to beat people up, but his plans never come out as planned." WHERE name="Dr. Speculo";
UPDATE button SET flavor_text="Guillermo is a deadly, cowardly, left-handed swordsman who hates to beat people up." WHERE name="Guillermo";
UPDATE button SET flavor_text="You ask for war, he says \"Desist.\" / Contrarian, he will resist. / Instead of peace, a paci-fist!" WHERE name="Harry Pudding";
UPDATE button SET flavor_text="A naughty tavern wench. Not a big believer in outright force, she would much prefer to beat you up when you are not looking." WHERE name="Lascivia";
UPDATE button SET flavor_text="He'll make you laugh, he'll make you cry. He'll make you kiss your teeth goodbye." WHERE name="MothMan";
UPDATE button SET flavor_text="Jedite is the first Negaverse General, who was sent to the Earth to harvest energy from humans. Jedite likes taking advantage of gullible humans, wearing cool disguises, and trying to take over the world." WHERE name="Jedite";
UPDATE button SET flavor_text="Malachite is the fourth negaverse General, who was sent to Earth to reveal Sailor Moon's identity. Malachite likes taunting the Scouts, arguing with Prince Darien, and trying to take over the world." WHERE name="Malachite";
UPDATE button SET flavor_text="Nephlite is the second Negaverse General, who was sent to Earth to locate the Imperium Silver Crystal. Nephlite likes communicating with the stars, driving fast cars, and trying to take over the world." WHERE name="Nephlite";
UPDATE button SET flavor_text="Queen Beryl is the vicious ruler of the Negaverse, and the loyal servant of the Negaforce. Beryl likes controlling her monster servants, seeking revenge on her enemies, and trying to take over the world." WHERE name="Queen Beryl";
UPDATE button SET flavor_text="Lita is the reincarnation of the Princess of Jupiter, who transforms into the Energetic Fighter, Sailor Jupiter. Sailor Jupiter likes cooking, brawling, chasing boys, and saving the world from the Negaverse." WHERE name="Sailor Jupiter";
UPDATE button SET flavor_text="Raye is the reincarnation of the Princess of Mars, who transforms into the Passionate Fighter, Sailor Mars. Sailor Mars likes shopping, telling fortunes, reading comics, and saving the world from the Negaverse." WHERE name="Sailor Mars";
UPDATE button SET flavor_text="Amy is the reincarnation of the Princess of Mercury, who transforms into the Brilliant Fighter Sailor Mercury. Sailor Mercury likes reading, computers, playing chess, and saving the world from the Negaverse." WHERE name="Sailor Mercury";
UPDATE button SET flavor_text="Serena is the reincarnation of Princess of the Moon, who transforms into the Justice Fighter, Sailor Moon. Sailor Moon likes eating, shopping, playing video games, and saving the world from the Negaverse." WHERE name="Sailor Moon";
UPDATE button SET flavor_text="Mina is the reincarnation of the Princess of Venus, who transforms into the Dynamic Fighter Sailor Venus. Sailor Venus likes shopping, acting, chasing movie stars, and saving the world from the Negaverse." WHERE name="Sailor Venus";
UPDATE button SET flavor_text="The Seven Shadow Warriors are powerful Negaverse monsters, who were all trapped inside Rainbow Crystals. They like fighting, breaking things, being mean, and trying to take over the world." WHERE name="Shadow Warriors";
UPDATE button SET flavor_text="Darien is the reincarnation of the Prince of Earth, who transforms into the Dashing Hero, Tuxedo Mask. Tuxedo Mask likes jogging, reading, studying science, and saving the world from the Negaverse." WHERE name="Tuxedo Mask";
UPDATE button SET flavor_text="Zoycite is the third Negaverse General, who was sent to Earth to collect the seven Rainbow Crystals. Zoycite likes spoiling Nephlite's plans, hugging Malachite, and trying to take over the world." WHERE name="Zoycite";
UPDATE button SET flavor_text="Emerald is the most sinister, mean-spirited, and nasty member of the dark Moon family. Emerald likes being close to those with great power, mocking Rubeus's constant failures, and trying to destroy the universe." WHERE name="Emerald";
UPDATE button SET flavor_text="King Endymion is the future version of Prince Darien and Tuxedo Mask, combined into one man. King Endymion likes invading his own dreams, making holographic projections of himself, and saving the universe from destruction." WHERE name="King Endymion";
UPDATE button SET flavor_text="Luna and Artemis are feline guardians and companions of the Sailor Scouts. They both like working with computers, guiding the Champions of Justice to do what is right, and saving the universe from destruction." WHERE name="Luna & Artemis";
UPDATE button SET flavor_text="Neo-Queen Serenity is the powerful ruler of Crystal Tokyo and the adult form of Sailor Moon. Neo-Queen Serenity likes playing with her daughter, going on walks with her husband, and saving the universe from destruction." WHERE name="Neo-Queen Serenity";
UPDATE button SET flavor_text="Prince Diamond is a passionate man who is the leader of the royal family from the Dark Moon, Nemesis. Prince Diamond likes kidnapping Sailor Moon, being close to his brother Sapphire, and trying to destroy the universe" WHERE name="Prince Diamond";
UPDATE button SET flavor_text="Princess Serena is the alter ego of Sailor Moon who was first born on the Moon during the Silver Millennium. Princess Serena likes dancing with Prince Darien, blasting enemies with her wand, and saving the universe from destruction." WHERE name="Princess Serena";
UPDATE button SET flavor_text="Queen Serenity is the kind and gentle ruler that held court during the Silver Millennium one thousand years ago. Queen Serenity likes beautiful dresses, capturing Shadow Warriors and saving the universe from destruction." WHERE name="Queen Serenity";
UPDATE button SET flavor_text="Rini is a saucy, young, pink-haired child who will someday be the Queen of the Universe. Rini likes eating ice-cream and other goodies, playing with her mommy's shiny crystals, and saving the universe from destruction." WHERE name="Rini";
UPDATE button SET flavor_text="Rubeus travelled one thousand years into the past on his spaceship to present-day Tokyo to capture young Rini. Rubeus likes flirting with the Four Sisters, riding around in his spaceship, and trying to destroy the universe." WHERE name="Rubeus";
UPDATE button SET flavor_text="Sapphire is Prince Diamond's handsome young brother but he was destroyed by Wiseman. Sapphire likes spying on his enemies, standing up for what he believes is right, and trying to destroy the universe." WHERE name="Sapphire";
UPDATE button SET flavor_text="Wicked Lady is an artificial adult form of young Rini, who was twisted and manipulated by Wiseman. Wicked Lady likes hanging out with Prince Diamond, laughing maniacally, and trying to destroy the universe." WHERE name="Wicked Lady";
UPDATE button SET flavor_text="Wiseman is a cloaked frightening being of pure evil that refers to himself in the third person as the \"Doom Phantom\". Wiseman likes floating in the air, making cryptic comments, and trying to destroy the universe." WHERE name="Wiseman";
UPDATE button SET flavor_text="Honzo is a traveling stick salesman with a funnel on his head. He relishes the simplicity of life on the road, and he likes to beat people up." WHERE name="Honzo";
UPDATE button SET flavor_text="Konami is a funny little girl with nothing to say. She likes to stick people with her knife, and she likes to beat people up." WHERE name="Konami";
UPDATE button SET flavor_text="Okaru is fascinated by candy dispensers, and is a world champion on the mechanical bull. She always thinks that she is being followed, and she likes to beat people up." WHERE name="Okaru";
UPDATE button SET flavor_text="Sadakuro owns nothing but a good book and the deadliest fighting tools. He likes long walks in the dark, remaining perfectly still, and beating people up." WHERE name="Sadakuro";
UPDATE button SET flavor_text="Tonase picks a fight with everyone she meets. She is opinionated and naive, she adores small dogs and furniture, and she likes to beat people up." WHERE name="Tonase";
UPDATE button SET flavor_text="Yuranosuke is a fierce warrior who takes nothing for granted. His highest aspiration is to serve the forces of good, and he likes to beat people up." WHERE name="Yuranosuke";
UPDATE button SET flavor_text="Aylee thinks she looks like a pretty flower. She attended Cannibals Anonymous, and she likes to eat people up." WHERE name="Aylee";
UPDATE button SET flavor_text="Bun-Bun isn't your typical mini-lop rabbit. He hates telemarketers with a passion and frequently beats people up." WHERE name="Bun-Bun";
UPDATE button SET flavor_text="Kiki is the friendliest ferret around. She loves lights, and games, and candy, and never means to beat people up." WHERE name="Kiki";
UPDATE button SET flavor_text="Riff is a freelance inventor whose desire to build nifty toys is exceeded only by his joy in building big guns (which sometimes beat people up)." WHERE name="Riff";
UPDATE button SET flavor_text="Torg is a freelance webpage designer. He makes occasional trips to alternate dimensions or past history, where he sometimes beats people up." WHERE name="Torg";
UPDATE button SET flavor_text=CONCAT("Zo&euml", CHAR(59), " is a college co-ed, but spends most of her time trying to survive her friends' adventures. Luckily, Zo&euml", CHAR(59), " can take care of herself and beat people up.") WHERE name="Zoe";
UPDATE button SET flavor_text="Avis is an expert chainsaw dueler and ice sculptor, and she likes to beat people up." WHERE name="Avis";
UPDATE button SET flavor_text="Bauer is an enthusiastic supporter of global human rights and a first-class sushi chef. And he likes to beat people up." WHERE name="Bauer";
UPDATE button SET flavor_text="Clare is an international spelling and thumb wrestling champion. She enjoys hiking, fishing, and beating people up." WHERE name="Clare";
UPDATE button SET flavor_text="Hammer is a vicious, sweaty man with a garbage can on his head, and he likes to beat people up." WHERE name="Hammer";
UPDATE button SET flavor_text="Hannah clings to a deep and abiding faith in the fundamental goodness of all human beings, and she likes to beat people up." WHERE name="Hannah";
UPDATE button SET flavor_text="Iago is a sorcerer with mystic powers and is available for bar mitzvahs. He also likes to beat people up." WHERE name="Iago";
UPDATE button SET flavor_text="Karl is a world champion kickboxer and a pathological liar. He likes playing drums, and beating people up." WHERE name="Karl";
UPDATE button SET flavor_text="Kith enjoys travel and nature photography. She binds books in her spare time, and she likes to beat people up." WHERE name="Kith";
UPDATE button SET flavor_text="Kublai is an embittered fast-food worker whose time is never his own. He likes malts, orangutans, and beating people up." WHERE name="Kublai";
UPDATE button SET flavor_text="Niles is a three-time winner of the secret Tijuana kitchen utensils freestyle, and he likes to beat people up." WHERE name="Niles";
UPDATE button SET flavor_text="Shore is a stay-at-home dad who likes long walks in the park, etymology, and beating people up." WHERE name="Shore";
UPDATE button SET flavor_text="Stark once thought she was a train for three days, and she likes to beat people up." WHERE name="Stark";
UPDATE button SET flavor_text="Agatha Heterodyne is a fledgling mad scientist. She means well, but tends to blow things up." WHERE name="Agatha";
UPDATE button SET flavor_text="Bangladesh Dupree likes to beat people up." WHERE name="Bang";
UPDATE button SET flavor_text="Brigid the witch is in tune with the ancient eldritch mysteries. She helps people with her spells and potions and if they laugh at her hat, she beats them up." WHERE name="Brigid";
UPDATE button SET flavor_text="Buck Godot is a zap-gun for hire on New Hong Kong, the planet where the only law is that there is no law. He loves this game." WHERE name="Buck Godot";
UPDATE button SET flavor_text="Gil is home from school and learning to run the family business - world domination." WHERE name="Gil";
UPDATE button SET flavor_text="Klaus likes to find out what makes people tick. Usually once he's found out, they don't tick anymore." WHERE name="Klaus";
UPDATE button SET flavor_text="Krosp is a failed experiment. This makes him cranky." WHERE name="Krosp";
UPDATE button SET flavor_text="Orgasm Lass is an X-rated Super Hero. She champions the power of sexual healing in her battle against villains, monsters and such. Occasionally that healing takes the form of S&M, and she gets to beat people up." WHERE name="O-Lass";
UPDATE button SET flavor_text="Von Pinn is ruthless, efficient and amoral. This makes her an excellent housekeeper." WHERE name="Von Pinn";
UPDATE button SET flavor_text="Angel repairs vintage motorcycles and enjoys picnics by moonlight. She is a staunch advocate of women's suffrage, and she likes to beat people up." WHERE name="Angel";
UPDATE button SET flavor_text="Buddy is a heartless brute without soul or conscience. It likes cabbages and howling, and it likes to beat people up." WHERE name="Buddy";
UPDATE button SET flavor_text="Dunkirk is a mysterious Balkan folk dancer. He often taunts his victims with hours of tuneless humming, and he likes to beat people up." WHERE name="Dunkirk";
UPDATE button SET flavor_text="Durban McGinty sleeps with one eye open and is easily startled when awake. He is never without something to chew on, and he likes to beat people up." WHERE name="McGinty";
UPDATE button SET flavor_text="Starchylde is a ferocious vampire hunter and a prolific poet. She likes garlic, mice and ultraviolet light, and she likes to beat people up." WHERE name="Starchylde";
UPDATE button SET flavor_text="Tiffany is a robotics expert and gourmet chef. Her travels often take her to remote locations in Tibet and Pakistan, and she likes to beat people up." WHERE name="Tiffany";
UPDATE button SET flavor_text="Aries is a strong-willed warrior and a bundle of energy. He is creative and spontaneous, quick to anger, but doesn't take it personally. Although he works best alone, he likes parties, humor, and beating people up." WHERE name="Aries";
UPDATE button SET flavor_text="Aquarius is an honest and loyal humanitarian. He is easy going and makes friendships naturally. He accomplishes his goals in a quiet, unorthodox but surprisingly effective way. He loves arts, poetry, and beating people up." WHERE name="Aquarius";
UPDATE button SET flavor_text="Cancer is a loyal, sympathetic friend with a special interest in history. He is known to be a conservative traditionalist who occasionally needs solitude. He loves his home, his family, and beating people up." WHERE name="Cancer";
UPDATE button SET flavor_text="Capricorn is a good organizer with broad shoulders for other people's problems. He is highly intelligent and shrugs off any negative comment about his personality. He loves stability and order, and beating people up." WHERE name="Capricorn";
UPDATE button SET flavor_text="Gemini is an adaptable and flexible duo. They may turn from hot to cold in no time since their attention easily drifts to something new. They love to inspire others, talking and playing with their mobile phones, and beating people up." WHERE name="Gemini";
UPDATE button SET flavor_text="Leo is a natural born leader with a tendency to be high-minded about his opinion. He is usually correct in his statements, though. He is brave, generous, and stubborn. He loves power and hides his sensitive nature by beating people up." WHERE name="Leo";
UPDATE button SET flavor_text="Libra is the caring friend of the underdog. Although she can be quiet and shy, she makes an excellent debater and has a strong sense of justice. She loves beauty, keeping everything in balance, and beating people up." WHERE name="Libra";
UPDATE button SET flavor_text="Pisces acquires vast amounts of knowledge but keeps an extremely low profile. She is honest, unselfish and trustworthy. Others might take advantage of her because of her gentleness and generosity. She loves her friends and beating people up." WHERE name="Pisces";
UPDATE button SET flavor_text="Sagittarius is a philosopher with a tendency to wear herself out, going in too many directions at once. She is not very patient and expects quick results. She loves her freedom, abstract ideas and new points of view, and beating people up." WHERE name="Sagittarius";
UPDATE button SET flavor_text="Scorpio understands deeper layers of the psyche but is unable to communicate this so she is often misjudged. She is passionate and sometimes intrigued by the dark side of life. She likes the hero, secrets, being in opposition, and beating people up." WHERE name="Scorpio";
UPDATE button SET flavor_text="Taurus is stubborn by nature and will stand her ground to the bitter end. She is full of strength, stamina and will. She loves to teach others patiently, is endlessly generous with her time, and seeks pleasure in beating people up." WHERE name="Taurus";
UPDATE button SET flavor_text="Virgo is delightful to chat with due to her charm and her remarkable memory. She is an excellent teammate and works well with others. She needs balance in her life, otherwise she becomes short-tempered and impatient, and starts beating people up." WHERE name="Virgo";
UPDATE button SET flavor_text="Alice is a precocious schoolgirl who nevertheless doesn't know why a raven is like a writing desk. She likes eating food that has labels on it and beating people up." WHERE name="Alice";
UPDATE button SET flavor_text="The Mad Hatter suffers from chronic mercury poisoning, an occupational hazard--otherwise, he's a most convivial host. He's equally fond of riddles and beating people up." WHERE name="Mad Hatter";
UPDATE button SET flavor_text="The Queen of Hearts is a megalomaniac who enjoys playing croquet with flamingoes and ordering decapitations. In her free time she makes tarts and beats people up." WHERE name="Queen Of Hearts";
UPDATE button SET flavor_text="(The Jabberwock has no flavor text. The Jabberwock needs no flavor text. You don't taste The Jabberwock, The Jabberwock tastes you.)" WHERE name="The Jabberwock";
UPDATE button SET flavor_text="Tweedledum takes out his jealousy over his brother's way with the ladies by picking fights over the little things. He enjoys listening to poetry and, of course, beating people up. Tweedledee takes out his jealousy over his brother's superior intellect by breaking Tweedledum's toys. He enjoys reciting poetry and, of course, beating people up." WHERE name="Tweedledum+dee";
UPDATE button SET flavor_text="The White Rabbit dresses like a dandy and harbors a thinly-veiled animosity towards the opposite sex. He likes cucumbers, kid gloves, and beating people up." WHERE name="White Rabbit";
UPDATE button SET flavor_text="Carrow may be a corporeal revenant, but is also a surprisingly deep thinker, who most happily assists the party in beating people up." WHERE name="Carrow";
UPDATE button SET flavor_text="Nara is an elven archer with a lunar birthmark on her right cheek. She refuses to talk about the left cheek. She aims high but shoots true to beat people up." WHERE name="Nara";
UPDATE button SET flavor_text="Perf is a low-ranking wizard who can mend your drawers, conjure milk from thin air, and vaguely do... something else. Yet even these spells are enough to beat people up." WHERE name="Perf";
UPDATE button SET flavor_text="Glorion believes killing orcs equals honor, and looks for them in every tavern he visits. He lives to beat people up." WHERE name="Glorion";
UPDATE button SET flavor_text="The Assassin is a hired killer for the Wicked Kings. She enjoys darts, kicking people in the face, and beating people up." WHERE name="The Assassin";
UPDATE button SET flavor_text="Wren is a collegiate bard who resorts to stalking adventurers. On weekends, she writes term papers and sings about beating people up." WHERE name="Wren";
UPDATE button SET flavor_text="\"Thick\" Tony DeLuca: Thick Tony prowls the moonlit streets of Fight City with a chip on his shoulder and an impossibly tiny pork pie on his head. He likes dinner theater, black coffee, stakeouts, and beating people up." WHERE name="Tony (The Core)";
UPDATE button SET flavor_text="Captain Stephen Send: \"Three-Fingered Steve\" actually has six fingers and two thumbs. He likes explosives, sharks, exploding sharks, and sharks that explode. And he likes to beat people up." WHERE name="Steve (The Core)";
UPDATE button SET flavor_text="David Ferrer: David \"Honey\" Ferrer is a cruel man with a stick and a gun. He enjoys the solace of a quiet evening with Schubert and Armagnac, the calming hum of a thousand bees, and beating people up." WHERE name="Ferrer";
UPDATE button SET flavor_text="Donna Chase: Donna is a hard-nosed, fast-talking stunt driver who smuggles exotic animals and unusual auto parts. She likes matchsticks, binturongs, and beating people up." WHERE name="Donna";
UPDATE button SET flavor_text="Hollis Henry: Hollis is a fatuous turncoat who would trade his pet rabbit for a warm pair of socks. He is never found within ten feet of the truth, and he likes to beat people up." WHERE name="Hollis";
UPDATE button SET flavor_text="Janet Rialto: In a jam? Call the law offices of Janet Rialto. No job too small. Most solutions safe and legal. Divorce, Custody, Injury, Wrongful Prosecution, Arson, Beating People Up." WHERE name="Janet";
UPDATE button SET flavor_text="Julian Smith: Julian \"Calendar\" Smith can spend a week in a blind. Julian likes taking out the garbage, elaborate disguises, learning intimate details of high-profile targets, and beating people up." WHERE name="Smith";
UPDATE button SET flavor_text="Mayor Delia Culvert: Delia \"La Rata\" Culvert is a ruthless taskmaster with a penchant for exotic cheeses. She likes earthworms, menthols, wielding power through a vast network of spies, and beating people up." WHERE name="Delia";
UPDATE button SET flavor_text="Polly Flowers: Polly is a strong-willed historical revisionist whose deepest regret is that she despises long walks on the beach. She likes ikebana, territorial collectives, and beating people up." WHERE name="Polly";
UPDATE button SET flavor_text="Porter Stratos: \"Downtown\" Porter Stratos is a small-minded killer who will do anything because he believes in nothing. He prefers argan oil to aloe, Hong Kong to Savile Row, and he likes to beat people up." WHERE name="Porter";
UPDATE button SET flavor_text="Stefano Serilio: Stefano is a meticulous obsessive who spends hours each morning separating beach sand into piles of different colors. He likes ink, a sense of purpose in the universe, and beating people up." WHERE name="Stefano";
UPDATE button SET flavor_text="Tanya Love: Tanya Love has an abiding love of firearms and wields power and status far higher than her tender age would suggest. She likes scrimshaw, herd animals, brewery mold, and beating people up." WHERE name="Tanya";
UPDATE button SET flavor_text="Hamilton Weeks: Charlie \"Hamilton\" Weeks is a deeply thoughtful and engaged human being whose delights in the simple things cannot be overestimated. He likes dodgeball, high-speed photography, and beating people up." WHERE name="Hamilton";
UPDATE button SET flavor_text="Emerita \"Lady B\" Brighton: Emerita Brighton is an eminent restaurateur and porcelain doll collector who lives in an unusually small apartment and likes literally everything, including beating people up." WHERE name="Lady B";
UPDATE button SET flavor_text="Wallace Weber: Wallace Weber is a beat cop in every sense. He likes robbing dead drops, hassling his boss for a raise, and beating people up." WHERE name="Wallace";
UPDATE button SET flavor_text="Ho Min-Szu: Ho Min-Szu is a talented car thief with a secret garage and a bright future. He services all makes and models, he always has the part you need, and he likes to beat people up." WHERE name="Min-Szu";
UPDATE button SET flavor_text="Keegan Howels: Keegan Howels is a meticulous floorman who often struggles to distinguish fantasy from reality. He collects exotic spices and avoids daylight at all costs, and he likes to beat people up." WHERE name="Keegan";
UPDATE button SET flavor_text="Jerry Corson: \"Waterline\" Jerry Corson is a water taxi pilot who inevitably breaks the ice with tales of his nefarious side projects. He collects doorknobs and decorative soaps, and he likes to beat people up." WHERE name="Jerry";
UPDATE button SET flavor_text="Gilroy Grimes: Gilroy is a drunken goofball who will cut you like a pack of cards. He likes the wee hours, cheating at pinochle, and beating people up." WHERE name="Gilroy";
UPDATE button SET flavor_text="Brand Golver: Brand Golver would rather spend his days on the links, but there is always someone who wants something done properly, and he is the man to do that. He likes fireflies, ceramic roof tiles, and beating people up." WHERE name="Brand";
UPDATE button SET flavor_text="Benvenito Buonasera: Benvenito Buonasera owns the Mighty Malt Club on Pelican Island. He will be onstage in five minutes, he is currently accepting requests, and he likes to beat people up." WHERE name="Benvenito";
UPDATE button SET flavor_text="\"Pilot\" Joe Cambry: \"Pilot\" Joe Cambry once had that Frank Sinatra in his cab. He is affable, moderately articulate, and possessed of myriad indefensible political opinions, and he likes to beat people up." WHERE name="Pilot Joe";
UPDATE button SET flavor_text="Szechuan Pierce: Szechuan Pierce is not as delicate as she looks, and she is so tired of your crap. She likes deals without hitches, getting along whenever practical, and beating people up." WHERE name="Szechuan";
UPDATE button SET flavor_text="Karen \"Lady K\" Wollerry: Karen \"Lady K\" Wollerry is even closer to the edge than she looks. She is fastidious, capricious, and unforgiving, and sleeps less than an hour a day. And she likes - no, loves - to beat people up." WHERE name="Lady K";
UPDATE button SET flavor_text="Jocasta Pierce-Wilson: Jocasta owns you and you don't even know it. She likes managing casinos, barbed wire, and the muffled snap of a professionally broken bone. And she likes to beat people up." WHERE name="Jocasta";
UPDATE button SET flavor_text="Fiorina Pierce: \"Pyramid Casino Group, please hold.\"" WHERE name="Fiorina";
UPDATE button SET flavor_text="Cerise Collins: Cerise is a deep-sea diver and licensed pulmonologist. She collects decorative erasers and once went for six months without sneezing, and she likes to beat people up." WHERE name="Cerise";
UPDATE button SET flavor_text="Doctor Fret: Doctor William J. Fret has a bag of tools that you do not want to see. He likes patience, surprises, and getting to the truth, and he likes to beat people up." WHERE name="Doctor Fret";
UPDATE button SET flavor_text="Felicia Honora: Felicia Honora believes in the immutable tenets of objectivist capitalism and can make ten million dollars disappear with a pencil. She likes whales, absolute silence, and beating people up." WHERE name="Felicia";
UPDATE button SET flavor_text="Windsor Castle: Windsor Castle can tell you the news before it happens. He has seen all this before, and he is frankly embarrassed that no one else saw this coming. He likes Bertrand Russell, moonglow on the Rio Vinareto, and beating people up." WHERE name="Windsor";
UPDATE button SET flavor_text=CONCAT("Sal B&auml", CHAR(59), "cker: Hansel \"California Sal\" B&auml", CHAR(59), "cker is an acolyte of Poseidon and a master of the sea. He earned his nickname by having no idea where he was or where he was going, and he likes to beat people up.") WHERE name="Sal";
UPDATE button SET flavor_text="Marin Reed: Marin Fleet Reed is a precise and unforgiving employer who wastes countless hours negotiating for the slightest advantages. He enjoys staring contests, Wadjet, and beating people up." WHERE name="Marin";
UPDATE button SET flavor_text="Dietrich Weiss: Dietrich Weiss would like to see some identification. He has a black belt in a martial art of his own devising, which pretty much only works if you are over seven feet tall, and likes to beat people up." WHERE name="Dietrich";
UPDATE button SET flavor_text="Carson Bell: Carson Bell liked Water Town before it was all built up. Time was you could see Lee Point from the Kennerick Yards and everything north of Ather Street was just swampland. He likes hay, wine, and beating people up." WHERE name="Carson (West Side)";
UPDATE button SET flavor_text="Bijay Purinam: Bijay is a tavern owner and a strict constitutionalist. He enjoys teleology, Canadian porters, and table games with little or no strategy. And he likes to beat people up." WHERE name="Bijay";
UPDATE button SET flavor_text="Stacie Jones: Stacie \"Candy Pop\" Jones has one thousand absurd yet unfalsifiable theories. He likes surprises, ditching work, golf balls, and beating people up." WHERE name="Stacie";
UPDATE button SET flavor_text="Sally LaSombre: Sally LaSombre is an orphan from Corton City. She can strip a car into its component parts in fifteen minutes, and she accepts only cash. She likes disaster movies, anonymity, and fishing, and she likes to beat people up." WHERE name="Sally";
UPDATE button SET flavor_text="Prentice Reed: Prentice Reed takes some getting used to. She is not one to varnish her opinion, and will get into a fight about literally anything that comes out of your mouth. She likes Langton Ale, prisms, and beating people up." WHERE name="Prentice";
UPDATE button SET flavor_text="Marta Cosgrove: Marta Cosgrove will take the starch right out of that smart lip, young man. She is a steadfast advocate for the lifestyle of an independent woman, and she likes to beat people up." WHERE name="Marta";
UPDATE button SET flavor_text="Jackie Waters: Jackie is a respected charcutier and aerial tour guide. She likes mothballs, terrines, the soft caress of the cold winter sea, and beating people up." WHERE name="Jackie";
UPDATE button SET flavor_text="Hazel Tenant: Hazel Tenant keeps the wires from crossing at the West Side 504. She is a compulsive diarist whose annals log the secret history of the city. She likes gossip, the ring of fine porcelain, and beating people up." WHERE name="Hazel";
UPDATE button SET flavor_text="Beatrice Reed-Wheeler: Beatrice Reed-Wheeler has a radiant beauty that belies her advanced years. She is an accomplished campanologist and trauma surgeon, and she likes red grapes, old snapshots, and beating people up." WHERE name="Beatrice";
UPDATE button SET flavor_text="Rider Henry: Rider Henry once shot a 66 at Palatka and he never shuts up about it. He enjoys precision in all things, the Bugatti Type 23 Brescia Tourer, Inglenook Cab '41, and beating people up." WHERE name="Rider";
UPDATE button SET flavor_text="Edmondo Rivera: Do. Not. Push me." WHERE name="Mondo";
UPDATE button SET flavor_text="Mickael Mezzo: Mickael Mezzo is an imbecile who surfs an endless wave of his father's generosity and protection. He likes stacking up rocks, and that is pretty much it, aside from occasionally beating people up." WHERE name="Mickael";
UPDATE button SET flavor_text="Ricky Pietrasanta: Ricardo \"Ricky\" Pietrasanta runs a ship as tight as his necktie. He likes alibis, the recent string of unrelated kidnappings, multiple vacancies in municipal government, and beating people up." WHERE name="Ricky";
UPDATE button SET flavor_text="Petey Petri: No car engine in the world is running so well that Petey Petri can leave it alone. He likes visits to the zoo, knowing where his tools are, and beating people up." WHERE name="Petey";
UPDATE button SET flavor_text="Mississippi Max: \"Mississippi\" Max Storey is a former circus clown with an assassin's cold stare and a respectable .324. He hates thunderstorms and the sound of chewing, and he likes to beat people up." WHERE name="Mississippi (Uptown)";
UPDATE button SET flavor_text=CONCAT("Ken Ochoa: Ken Ochoa can buy and sell the people who could buy and sell you. He prefers Partag&aacute", CHAR(59), "s Perfectos Finos, Le Corbusier, the Patek Gyromax, and beating people up.") WHERE name="Ken (Uptown)";
UPDATE button SET flavor_text="Henry White: \"Pawtucket\" Henry White doesn't care who are you, where you came from, or how long you are staying. He also doesn't need your opinion on how to get there. Pretty much all he cares about is beating people up." WHERE name="Henry";
UPDATE button SET flavor_text="Giuseppe Stone: Giuseppe Stone can eat fifty eggs. He is a model citizen with several successful and well-respected legitimate businesses. He likes garlic, red wine, outliving his enemies, and beating people up." WHERE name="Giuseppe";
UPDATE button SET flavor_text="Clyde Fischer: Harold \"Clyde\" Fischer is a monstrous horse trainer with a short temper and a big gun. His every adult decision has been the result of a preadolescent misconception, and he likes to beat people up." WHERE name="Clyde";
UPDATE button SET flavor_text="Basil Tyron: Basil \"The Basilisk\" Tyron can think of about a hundred places he would rather be right now. He likes doing things the easy way, working this out like adults, and beating people up." WHERE name="Basil";
UPDATE button SET flavor_text=CONCAT("Montserrat P&eacute", CHAR(59), "rez: Montserrat P&eacute", CHAR(59), "rez is a calculating hustler who wants to know if you'd like to make this interesting. She likes nine-ball, humidity, the idea of clowns, and beating people up.") WHERE name="Montserrat";
UPDATE button SET flavor_text="Jesse Millions: Jesse Millions can lift a car. She knows where the bodies are buried, and she knows who buried them. She likes mice, cats, and sea urchins, all for different reasons, and she likes to beat people up." WHERE name="Jesse";
UPDATE button SET flavor_text="Heather Gwinn: Heather Dobro Gwinn takes no pleasure in what she is about to do to you. She likes screenwriting, steel mills, and a team of well-trained bodyguards. And she likes to beat people up." WHERE name="Heather (Uptown)";
UPDATE button SET flavor_text="Amelia Farnese: Amelia Farnese can listen to every conversation in the room. She likes leaping, the sour taste of envelope glue, and the sound of distant church bells. And she likes to beat people up." WHERE name="Amelia";
UPDATE button SET flavor_text="Bobby McGinn: \"Texas\" Bobby McGinn knows every hoodlum on the streets of Leriston, Providence, and Shepherd City. He likes carnauba wax, a strong handshake, and watching things burn, and he likes to beat people up. " WHERE name="Bobby";
UPDATE button SET flavor_text="Tasha Rudenko: Natasha Rudenko is a wicked sorceress sent from another world to bark strange commands and to withhold delicious treats. She likes it when no one gets bitten, and she likes to beat people up." WHERE name="Tasha";
UPDATE button SET flavor_text="Johnny Stubbs: When Johnny Stubbs mentions \"the cigar trick,\" you should change the subject quickly but carefully. He likes gritty tales of wrongdoing, birdsong at first light, and beating people up." WHERE name="Stubbs";
UPDATE button SET flavor_text="Caine struggles with a soul that is salved by darkness but addicted to the heat of the limelight. He composes poetry in engrish and delights in the ironic cruelty of beating people up." WHERE name="Caine";
UPDATE button SET flavor_text="Xylene is colorless and sweet-smelling. When subjected to fanboys, she will exercise her right to beat people up." WHERE name="Xylene";
UPDATE button SET flavor_text="Cammy Neko is a cosplayer who likes gashapon, nuigurumi, yaoi, and many other Japanese words. She often cosplays with friends as fighting game characters, who are always looking to beat people up." WHERE name="Cammy Neko";
UPDATE button SET flavor_text="Sailor Man likes eating, shopping, and playing video games. He also likes crossplaying while beating people up." WHERE name="Sailor Man";
UPDATE button SET flavor_text="Perpetually winking, this druid-monk-ranger reads, solves puzzles, plays video games, and consumes massive amounts of popular culture yet considerately makes time to beat people up." WHERE name="Vysion";
UPDATE button SET flavor_text="Antonio is a breakaway specialist. He always looks ahead and leaves the beaten up behind." WHERE name="Antonio";
UPDATE button SET flavor_text="Doyle is a time trial specialist. He likes to ride alone and does not need anybody's help to beat people up." WHERE name="Doyle";
UPDATE button SET flavor_text="Floriano is a sprinter. He likes fast pace, fast cars and beating people up. Fast." WHERE name="Floriano";
UPDATE button SET flavor_text="Julia is a hiller. She likes to go up and down, up and down, and beat people up and down, up and down ..." WHERE name="Julia";
UPDATE button SET flavor_text="Mariusz is a domestique rider. He helps his captain to save energy and beats people up for him." WHERE name="Mariusz";
UPDATE button SET flavor_text="Orlando is an allrounder. He feels strong in any terrain and is always ready to beat people up." WHERE name="Orlando";
UPDATE button SET flavor_text="Roger is a climber. He likes high mountains and needs little oxygen to beat people up." WHERE name="Roger";
UPDATE button SET flavor_text="Timea is the lead-out rider. She sets the scene for her captain before the final beating up takes place." WHERE name="Timea";
UPDATE button SET flavor_text="AnnoDomini likes cycling, inventing new games, creating his own buttons and beating people up." WHERE name="AnnoDomini";
UPDATE button SET flavor_text="blackshadowshade is a mathematician and a stalwart Button Men programmer. Each bug that he vanquishes makes him stronger, which is good because there's always another bug out there to face. He likes discovering and retelling odd stories, saving the world, and bringing people together to beat each other up." WHERE name="blackshadowshade";
UPDATE button SET flavor_text="Blargh likes to sail the seven seas, raiding merchants, digging up treasure, keelhauling landlubbers, frequenting taverns, and beating people up. Argh!" WHERE name="Blargh";
UPDATE button SET flavor_text="Devious likes to play board games, grow flowers, listen to music, and wander in a twisty maze of passages, all alike, in order to beat people up." WHERE name="devious";
UPDATE button SET flavor_text="Jimmosk solves puzzles, sings rounds, and avoids games that feature dice - with one significant exception." WHERE name="jimmosk";
UPDATE button SET flavor_text="jl8e is an itinerant baker, coder, and game designer who is probably trapped under a very floofy cat right this moment. He likes 7-sided dice, Oddish, pineapple on pizza, and beating people up." WHERE name="jl8e";
UPDATE button SET flavor_text="""nobody ever laughs at my puns. i guess because it's not a great button. a little past half way home got caught in the rising dawn after long and fruitless debates. they handed over a tightly wrapped scroll, bowed and left again, without saying a word. funny that he now did. maybe his partner asked him to stand up to scrutiny. you are better than one chance in 256 of that happening are only 1/40. i thought about going smaller with my y swing, but i think you're doing quite well for you anyway"" -Nala" WHERE name="Nala";
UPDATE button SET flavor_text="Nonce Equitaur enjoys non sequiturs, puns, probability theory, symmetry groups, breaking into geodesic homes, and beating people up." WHERE name="Nonce Equitaur";
UPDATE button SET flavor_text="Tavella enjoys cooking, reading, and of course ... beating people up." WHERE name="tavella";
UPDATE button SET flavor_text="Bosco312, a master of time and space, an evil prince, and a part time theatrical critic, needs to be stealthy in order to beat people up without coming apart at high speed." WHERE name="Bosco312";
UPDATE button SET flavor_text="Cycozar's 3 rules to live by:<br>1. Fun first - winning is a bonus.<br>2. When life gives you obstacles, run them over like a speed bump - thump thump.<br>3. Love & hate are both contagious - choose your disease." WHERE name="Cycozar";
UPDATE button SET flavor_text="GamerXYZ likes graham crackers, electric blankets, and beating people up in games." WHERE name="GamerXYZ";
UPDATE button SET flavor_text="irilyth likes pretty dice with swirly colors, and especially likes using them to beat people up." WHERE name="irilyth";
UPDATE button SET flavor_text="Scipio is a broadly interested heretic from a mysterious country. He likes cheese, chocolate, watches, and the rule of three. Also beating people up." WHERE name="Scipio";
UPDATE button SET flavor_text="Gluttony enjoys eating, eating and the Fat Albert Cartoon. When not eating, or thinking about eating, he spends his time beating people up with his Double-Drumstick Fu." WHERE name="Gluttony";
UPDATE button SET flavor_text="Pride is a diva of the nose harmonica. When not appearing on Live TV news casts, she enjoys flinging cats, radish carvings and beating people up." WHERE name="Pride";
UPDATE button SET flavor_text="Lust is an out-of-work actress who is rather top-heavy. While not trying to get bit parts on VIP, Baywatch or any USA shows, she enjoys humming and the color red. She also beats people up." WHERE name="Lust";
UPDATE button SET flavor_text="Envy wants to be just like you. When not imitating everything around him, he enjoys the periodic table, prime numbers and beating people up." WHERE name="Envy";
UPDATE button SET flavor_text="Sloth is the ultimate armchair warrior. When not watching sports, he enjoys thinking about sports and encouraging others to talk about yams in loud voices. He beats people up with his special move 'Super Slow Motion Slam'. (It may take a while to get there, but it sure packs a wallop!)" WHERE name="Sloth";
UPDATE button SET flavor_text="Anger spends most of his time in useless debates with his sock puppet collection. When not playing in his punk band, The Rancid Spleen, he enjoys beating people up." WHERE name="Anger";
UPDATE button SET flavor_text="Greed is a fanatical collector of soup labels. While not surfing the net, or in her pimp-mobile searching flea markets, she enjoys beating people up and then running them over." WHERE name="Greed";
UPDATE button SET flavor_text="Maskin enjoys running, freaking out and bum-rushing telephones. Maskin has no occupation other than being alive and beating people up." WHERE name="Maskin";
UPDATE button SET flavor_text="Tony spends his time plotting for worldwide mass transit. He has 10,000 llamas, and has a masters in Christian Ethics. While not purchasing things on Ebay, he beats people up." WHERE name="Tony";
UPDATE button SET flavor_text="Loren is the sensei of two-fisted-game-controller fu. His skills of TV recording and if-then-else statements are unparalleled. While not beating people up, he enjoys donuts." WHERE name="Loren";
UPDATE button SET flavor_text="CynCyn is immune to the \"Lao Wai\" Stare. She works for the Great Googly Moogly, and likes glue guns. In her free time she taunts oak trees, palms state quarters and beats people up." WHERE name="CynCyn";
UPDATE button SET flavor_text="Lorrie really is the power behind Hoover Dam. During her freetime she collects batteries, books and new ways to beat people up." WHERE name="Lorrie";
UPDATE button SET flavor_text="Randy purchases things only in cash and enjoys sorting 3x5 cards by length. He has been known to consume an entire plate of sushi with only a chopstick, while beating people up." WHERE name="Randy";
UPDATE button SET flavor_text="Ayeka is the First Princess of the Jurai Empire. She likes acting regal, trying to be an ideal wife for Tenchi, and beating people up." WHERE name="Ayeka";
UPDATE button SET flavor_text="Dr. Clay was a wannabe mad scientist, now he's just mad. He likes being obscure, taunting people into attacking him, and beating people up." WHERE name="Dr. Clay";
UPDATE button SET flavor_text="Kagato is the penultimate evil scientist. He likes committing crimes, torturing the innocent, and beating people up." WHERE name="Kagato";
UPDATE button SET flavor_text="Kiyone Makibi is a Detective (First-Class) in the Galaxy Police. She is also Mihoshi's partner, this annoys her to no end. She likes screaming at Mihoshi, fighting crime, and beating criminals up." WHERE name="Kiyone";
UPDATE button SET flavor_text="Mihoshi Kuramitsu is a Detective (First-Class) in the Galaxy Police. She is incompetent but manages to keep her job due to nepotism. She likes junk food, arresting criminals, and beating people up." WHERE name="Mihoshi";
UPDATE button SET flavor_text="Ryo-Ohki is a spaceship that can turn into a small furry creature. It has destroyed 28 planets and 69 colonies (and counting). It likes to eat carrots, and being cute." WHERE name="Ryo-Ohki";
UPDATE button SET flavor_text="Ryoko is a space pirate who loves her job. She is also Washu's daughter. She likes sleeping, drinking, irritating Ayeka, and beating people up." WHERE name="Ryoko";
UPDATE button SET flavor_text="Sasami is the Second Princess of the Jurai Empire, and Ayeka's little sister. She has the spirit of the Space Tree, Tsunami. She likes cooking, cleaning, and cheering people up." WHERE name="Sasami";
UPDATE button SET flavor_text="The Guardians just like to beat people up." WHERE name="Soja's Guardians";
UPDATE button SET flavor_text="Tenchi Masaki is the Crown Prince of the Jurai Empire, something he does not want to be. He likes carrot farming, sword training, and he beats people up." WHERE name="Tenchi";
UPDATE button SET flavor_text="Washu Hakubi is the greatest genius scientist in the universe. She likes experimenting, proving herself right, and beating people up to prove scientific theory." WHERE name="Washu";
UPDATE button SET flavor_text="Zero is a robotic creation of Dr. Clay. It can mimic individuals simply by touching them. It likes wrecking people's lives, and beating people up." WHERE name="Zero";
UPDATE button SET flavor_text="Famine is a drummer who often plays until she passes out. Her turn-ons include back massages and lycanthropy. Turn-offs include foul language and Kenny G." WHERE name="Famine";
UPDATE button SET flavor_text="In his spare time Death plays bass in a band with his friends Famine, War, and Pestilence. He enjoys firecrackers, vanilla ice cream, and beating people up." WHERE name="Death";
UPDATE button SET flavor_text="After viewing the seedier elements of life from stage for years as a casino showgirl, Lola has become cynical, slightly paranoid, and often downright hostile. She has devised ingenious ways to hide numerous lethal weapons in nothing but her thong, push-up bra and audacious headdress. She often uses these to beat people up." WHERE name="Lola";
UPDATE button SET flavor_text="Nobody knows his real name -- rather Pai Gow refers to his favorite pasttime, a gambling game played with Chinese dominoes. Before emigrating here, he owned a gambling house and opium den. Now he runs a service that helps local casinos with clients with past due accounts when other, more legal methods of collection fail. He practices with a number of ancient paired weapons, which he uses for that 'special' touch when he beats people up." WHERE name="Pai Gow";
UPDATE button SET flavor_text="Not content with merely beating the casinos at their own games, Black Jack is always searching for another method of raking in the big bucks. Be sure not to call attention to the extra cards up his sleeve, because he can split heads as easily as a pair of aces or eights. In addition to card games he likes to beat people up." WHERE name="Black Jack";
UPDATE button SET flavor_text="Some say Craps made it here the hard way -- some say he's a natural. He often repeats himself to make his point. He hates snakes and trains but enjoys sevens and beating people up." WHERE name="Craps";
UPDATE button SET flavor_text="As the casino's pit boss, Sly is always on the look out for 'irregularities' at the gaming tables. When he catches someone he can be a cheater's worst nightmare. He looks just a little uncomfortable in the suit, but he's happy because he has a job that allows him to beat people up (especially if they call him Sylvester)." WHERE name="Sly";
UPDATE button SET flavor_text="A passable stage magician, The Great Crypto is as dishonest as he is skilled. With his expertise in manual dexterity, misdirection, and crowd psychology, he has supplemented his income as a forger, pickpocket, and small time con artist. While not on stage impressing the crowds or involved in some scam, he likes to beat people up." WHERE name="Crypto";
UPDATE button SET flavor_text="Frankie has had so many obnoxious drunks cause trouble in his bar, a quiet oasis in a desert of neon, that he can tell what moves the troublemakers will use before they even know themselves. Despite his unremarkable stature, when trouble is unavoidable, Frankie is more than capable of beating people up." WHERE name="Frankie";
UPDATE button SET flavor_text="Shamrock is a walking loophole in the laws of probability. When she's on a lucky streak she's only a heartbeat away from \"breaking the house\". When her luck turns, however, look out because she gets a little nasty and tends to beat people up." WHERE name="Shamrock";
UPDATE button SET flavor_text="Wildcard rode into town a young man with money in his pocket and dreams in his head. In mere hours he had lost them both and wondered off, dazed, into the desert, only to return months later a calm, collected master of the Poker table. Some believe that in that wasteland he traded his very soul for the abilities to control the cards as they're dealt, to read people's faces as easily as the menu at a cheap buffet, and to beat people up." WHERE name="Wildcard";
UPDATE button SET flavor_text="Flaire is a follower of Helia. She likes summer days, long walks in the desert and beating people up." WHERE name="Flaire";
UPDATE button SET flavor_text="Dirgo is a visually impaired cyclops. He likes knocking down buildings and beating people up." WHERE name="Dirgo";
UPDATE button SET flavor_text="Nerni is the rarest of heroes...a Gnome Champion. He enjoys laboring in filth and beating people up." WHERE name="Nerni";
UPDATE button SET flavor_text="The Yeti is really, really big, and really, really mean. He enjoys eating heroes and beating people up." WHERE name="Yeti";
UPDATE button SET flavor_text="Zaph is the two-headed, three-armed ex-head honcho of the universe. He likes cherry vanilla ice cream, yo-yo's and beating people up." WHERE name="zaph";
UPDATE button SET flavor_text="Anvil is a Shrugging Atlas with a flair for the obtuse. He likes his caviar over easy and to get beat up." WHERE name="Anvil";
UPDATE button SET flavor_text="fnord is a baker / sales creature who enjoys comic books, tasting zillions of new beers and beating people up. Normally in that particular order." WHERE name="fnord";
UPDATE button SET flavor_text="Guy is an enigma wrapped in the mystery of being beat up by people." WHERE name="albertel";
UPDATE button SET flavor_text="Skeeve's recipe changes on a whim." WHERE name="Skeeve";
UPDATE button SET flavor_text="Ben tells other people what to do all day long. In his spare time, he runs a computer college basketball ratings system and enjoys beer, broads, and beating people up!" WHERE name="fxdirect";
UPDATE button SET flavor_text="GripTiger is the quiet type who likes chocolate-peanutbutter ice cream and is normally not seen unless, of course, she's beating people up." WHERE name="GripTiger";
UPDATE button SET flavor_text="Pjack is the spitting image of Mad King Ludwig. He likes mochas, cats, world domination, and not getting beaten up." WHERE name="Pjack";
UPDATE button SET flavor_text="Santiago is a college student who enjoys In Nomine, painting, artificial life research, and beating people up." WHERE name="santiago";
UPDATE button SET flavor_text="A six-celled metazoan, he hates being called a cnidarian. He likes collecting bottle caps, raising bonsai Chia Pets, and taking long swims in the moonlight. Oh yeah, he also likes to beat people up." WHERE name="myxozoa";
UPDATE button SET flavor_text="Kestrel likes to watch old movies, whine about lost relationships, contemplate absurdity, and, in his spare time, beat people up." WHERE name="kestrel";
UPDATE button SET flavor_text="I'm an anti superhero with the power to talk before I think. I hurl my magic dashboard at my enemies or strangle them with my steel mouse cord. I enjoy Beating People Up!" WHERE name="NoopMan";
UPDATE button SET flavor_text="Gavin is a project manager. He believes nine women can deliver a baby in one month. He enjoys safaris, weddings, and beating people up." WHERE name="Gavin";
UPDATE button SET flavor_text="Andrew is an intern. He says an internship is a great way to learn how people at companies pretend to work. He likes skating, playing guitar, and beating people up." WHERE name="Andrew";
UPDATE button SET flavor_text="Chad is an IT specialist. He knows there are 10 types of people: those who understand binary code and those who don't. He likes to eat with sticks, swim in the ocean, and beat people up." WHERE name="Chad";
UPDATE button SET flavor_text="Isabel works in the HR department. Her job is to find employees in their mid 20s with 15 years of experience. She likes cold soda, cats, and beating people up." WHERE name="Isabel";
UPDATE button SET flavor_text="Monique works in the accounting department. She gets excited about weekends because she can wear casual clothes to work. She likes big numbers with a lot of commas and beating people up." WHERE name="Monique";
UPDATE button SET flavor_text="Sachin is a business analyst. He solves problems based on unreliable data provided by people with questionable knowledge. He likes eggs, keeping things in perfect order, and beating people up." WHERE name="Sachin";
UPDATE button SET flavor_text="Fiona is the CEO of the company. She is also a mom, so nothing scares her. She likes cabbage flavor, bathtubs, and beating people up." WHERE name="Fiona";
UPDATE button SET flavor_text="Nadia is a secretary. Everyone apart from the CEO knows it is actually she who runs the place. She likes Spring, baking her own bread, and beating people up." WHERE name="Nadia";
#UPDATE button SET flavor_text="Vanilla is simple, yet complex." WHERE name="Vanilla";
#UPDATE button SET flavor_text="One mean mother (shut your mouth!) I was only talking about Chocolate Chip! (we can dig it!)" WHERE name="Chocolate Chip";
#UPDATE button SET flavor_text="The meanest Ice Cream of the bunch. Avoid it like the green plague!" WHERE name="Mint Chip";
#UPDATE button SET flavor_text="Sometimes it's got nuts, sometimes fudge, sometimes marshmallows!" WHERE name="Rocky Road";
#UPDATE button SET flavor_text="Vanilla's evil cousin." WHERE name="Swirl";

INSERT INTO tag (name) VALUES ('exclude_from_random');

INSERT INTO button_tag_map (button_id, tag_id)
VALUES (
  (SELECT id FROM button WHERE name = 'Dead Guy'),
  (SELECT id FROM tag WHERE name = 'exclude_from_random')
);
