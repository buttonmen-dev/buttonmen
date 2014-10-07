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
(45, '2003 Rare-Promos',              4600),
(43, 'Button Brains',                 4700),
(61, 'Fightball',                     4800),
(62, 'Nodwick',                       4900),
(47, '2004 Origins',                  5000),
(51, 'Geekz',                         5100),
(66, 'Kubla Con',                     5200),
(48, 'Space Girlz',                   5300),
(49, 'Bridge and Tunnel',             5400),
(50, '2005 Rare Promo',               5500),
(76, 'Big Top',                       5550),
(63, 'High School Drama!',            5600),
(64, 'Unexploded Cow',                5700),
(67, 'ZOECon',                        5800),

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

# Unofficial Sets - fan and vanity sets
(70, 'Japanese Beetle (unofficial)',  6800),
(71, 'Blademasters',                  6900),
(72, 'Order of the Dolls',            7000),
(73, 'Blademasters: The Second Shot', 7100),
(74, 'Blademasters: Third Attack',    7200),
(60, 'Gaming Guardians',              7300),
(75, 'MegaTokyo',                     7400),

# Fanatics
(65, 'Classic Fanatics', 100000);


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
# ('Buzzing Weasel','F R P G S',                   1, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# JAMES ERNEST is mathematically impossible to implement (though it might be fun to try to work around this, changing sizes to die skills)
# ('James Ernest','(pi) (inf) (sqrt(-2)) (X)',     1, 0, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# Carson from 1999 GenCon
(4, 'Carson(p)',      '(4) (5) (6) (7) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo"));

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
(8, 'ConMan',     '(4) (6) p(20)',      0,  1, (SELECT id FROM buttonset WHERE name="2000 ShoreCon"));

# 2002 ANIME EXPO                                NO SPECIAL DICE SKILLS
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(9, 'MAX(p)',          '(4) (6) (18) (20) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="2002 Anime Expo"));

# 2002 Origins (Cheapass Games)                       SKILLS: Stinger(g) on old site)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(10, 'Apples',       '(8) (8) (2/12) (8/16) (20/24)',       0, 1, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave")),
(11, 'Green Apple',  '(8) (10) (1/8) (6/12) (12/20)',       0, 1, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave"));

# 2003 Rare-Promos (lacking information about this set except for one button, Apples and Green Apples were once here)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(12, 'Abe Caine',    'p(4,4) p(8,8) (10) ps(24) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="2003 Rare-Promos"));

# 2004 ORIGINS (Flying Buffalo)
#    INTRODUCES Sleep(Z) dice AND Game(#) dice; Fire(F); Poison(p); Shadow(s); Slow(w); Speed(z); Value(v); Option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(13, 'Amara Wintersword',     '(4) (8) (12) (12) (X)?',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(14, 'Beatnik Turtle',        'wHF(4) (8) (10) vz(20) vz(20)',                   0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(15, 'Captain Bingo',         '(8) (6/12) (6/12) (12/20) (X)',                   0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(16, 'Oni',                   '(4) (10) f(12) f(12) (V)',                        0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(17, 'Spite',                 'p(6) (6) (6) s(X) (X)',                           0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(18, 'Super Germ',            'p(10) p(12) p(16) p(20) p(24) p(30) p(30) p(X)',  0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(19, 'Cheese Weasel',         '(6) (8) (12) (16) (20)',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# The old site used raGe dice instead of Game dice in the following recipes.
(20, 'Delt',                  'R(4) R(4) (10) (12) R(X)',                        0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(21, 'Reggie',                '(8) (10) R(12) (20) R(20)',                       0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(22, 'Rold',                  '(6) (6) R(6) R(6) R(6)',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# since old site users are used to playing these with Rage . . .
(23, 'Delt Rage',              'G(4) G(4) (10) (12) G(X)',                       0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(24, 'Reggie Rage',            '(8) (10) G(12) (20) G(20)',                      0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(25, 'Rold Rage',              '(6) (6) G(6) G(6) G(6)',                         0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# not included in the original site - assumed not TL
# ('Killer Christmas Tree',    '(6) Z(6) (10) Z(12) (X)',                        0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(26, 'Billy Shakespeare',      '(15) (64) (16) (16)',                            0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(27, 'Drew\'s Truck',          '(5) (10) (15) (20) (X)',                         0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(28, 'Igor(p)',                '(8) (8) z(12) (20) (X)',                         0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
(29, 'Mike Young',             '(X) (X) (Y) (Y)',                                0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins"));

#('2005 Rare Promo')                         NO SPECIAL SKILLS
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(30, 'Kitty Cat Seven',     '(4) (6) (8) (10) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare Promo")),
(31, 'Sylvia Branham',      '(6) (6) (6) (X) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare Promo"));

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
(44, 'Angora',      'z(4) z(6) z(8) z(10) z(X)',           0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(45, 'Nickie',      'z(4) (10) (10) (12) z(12)',           0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(46, 'Sonia',       '(6) (6) z(12) (20) (20)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
(47, 'Tamiya',      '(4) (8) (8) (12) z(20)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
# TESS: from Origins 2000 / Club Foglio;   INTRODUCES Null(n) Dice
(48, 'Tess',        'n(4) (8) (12) n(20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL"));

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

# BRIDGE AND TUNNEL (Bridge and Tunnel Software)  INTRODUCES Reboud(=) dice (not implemented); poison(p); shadow(s); option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(63, 'Agent Orange',     '(6) p(6) =(10) (4/12) (4/20)',         0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(64, 'Huitzilopochtli',  '(6) (8) =(10) (12) (X)',               0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(65, 'Lisa',             '(4) (4) (30) (30)',                    0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(66, 'Nethershadow',     '(6) (10) s(10) (10/20) (6/30)',        0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(67, 'Phuong',           '(4) (10) (10) (20) (X)',               0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(68, 'Uncle Scratchy',   '(2) (4) (6) (10) (X)',                 0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel"));

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
(73, 'Al-Khwarizmi',           '(4) k(6) (8) (12) (20)',            0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
(74, 'Carl Friedrich Gauss',   'k(6) (8) (8) (12,12) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
(75, 'Fan Chung Graham',       '(4) k(6) (8) (10/20) (X)?',         0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
(76, 'Ron Graham',             'k(6) (10) (12) (20) (V)?',          0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
(77, 'Leonard Eugene Dickson', '(3) k(6) (10) (20) (W)',            0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
(78, 'Paul Erdos',             '(3) (4) k(6) (12) (U)',             0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
(79, 'Pythagoras',             'k(6) (8) (10) (12) (Z)',            0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
(80, 'Theano',                 '(4) k(6) (8,8) (10) (S)',           0, 1, (SELECT id FROM buttonset WHERE name="Button Brains"));

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

# DEMICON THE 13TH (DemiCon)                           SKILLS Shadow; Option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(90, 'The Effster',               's(4) (8) (8) s(12) s(X)',      0, 1, (SELECT id FROM buttonset WHERE name="Demicon the 13th")),
(91, 'The Fictitious Alan Clark', 's(8) s(8) (3/12) (20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="Demicon the 13th"));

# DICELAND                            INTRODUCES Stinger(g) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(92, 'Buck',        'g(8) g(10) (12) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
(93, 'Cass',        '(4) g(4) g(6) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
(94, 'Z-Don',       'g(6) g(8) p(16) (X) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
(95, 'Crysis',      'g(8) (10) (10) (X) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
(96, 'Golo',        'g(10) g(12) g(20) g(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
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
(122, 'Nightmare',   '(4) (8) (10) (20) (20)',             1, 1, (SELECT id FROM buttonset WHERE name="Fantasy"));
# ('Dead Guy',        '(0) (0) (0) (0) (0)',                0, 0, (SELECT id FROM buttonset WHERE name="Fantasy")),

# FIGHTBALL (Cheapass Games) NOTE: special die selection rules - choose 5 dice out of all available (not implemented)
# ASSUMED ALL TO BE TL
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(550, 'Brie',              '(4) (6) (8) (10) (12) (12) (20)',         1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(551, 'Domino',            '(4) (4) (8) (8) (8) (10) (12)',           1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(552, 'Echo(Fightball)',   '(4) (6) (6) (6) (12) (12) (12) (20)',     1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(553, 'Georgia',           '(6) (6) (8) (8) (12) (20) (20)',          1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
(554, 'Gordo(Fightball)',  '(4) (6) (6) (8) (8) (10) (20)',           1, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
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
(123, 'Max',  'p(12) p(12) p(20) p(20) p(30) p(30) p(X) p(X)',  0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
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

# KUBLA CON (Kubla Con)             INTRODUCES Chase's Giant Sized(O), Kubla Treasure(X), Hoskins(Y), (K) 
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(572, 'Space Kubla',       '(6) (8) O(12) X(12) (20)',   0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con")),
(573, 'Pirate Kubla',      '(6) (8) (20) H(12) (K)',     0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con"));

# LAS VEGAS                                INTRODUCES Wildcard(C) AND Pai Gow(:); Option; Twin; Turbo
# Frankie aka Professor, Lola aka Showgirl, Sly aka Pit Boss, Crypto aka Magician
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(183, 'Frankie',       '(2,3) (3,4) (4,5) (10) (T)!',      0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(184, 'Lola',          '(6) (6) (8) (T) (U)',              0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(185, 'Sly',           '(12) (12) (20) (20) (U)',          0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(186, 'WildCard',      '(C) (C) (C) (C) (C)',              0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# Black Jack's T swing cannot be a d12.
(187, 'Black Jack',    '(1,1) (11) (8,8) (10,11) (T)',     1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
#CRAPS: Any twin die that rolls a 7 may be reset by the player to any value between 2 and 12 (includes after a Trip attack.)
#At the start of a round, this decision must be made before seeing the opponent's starting roll.
(188, 'Craps',         '(6,6) (6,6) (6,6) (6,6) (6,6)',    1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# SHAMROCK: The d13s score as normal d13s, but are represented by a d12 for which a 7 counts as a 13
(189, 'Shamrock',      '(2) (7/13) (7/13) (7/13) (7/13)',  1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(190, 'PaiGow',        '(6) :(8) :(8) (10) (12)',          0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# Crypto can use one of the following once per round, and cannot use any of them twice until he has used all four:
# * Rabbit from hat: Extra d1.
# * Prestidigitation: Change any die to a Trip die.
# * Bisect with Saw: Change any die (own or opponent's) to half of its originally-rolled value on the opening roll.
# * Vanishing Act: First die captured by opponent is scored as zero points.
# * Mind Reading: Opponent must state all sizes of all option and/or swing dice.
(191, 'Crypto',        '(6) (8) (10) (12) (T)',            1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas"));

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
(215, 'Daisy',     'm(6) (10) (10) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
(216, 'Jingjing',  'm(4) (8) (10) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
(217, 'Mau',       '(6) (6) (8) (12) m(X)',       0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
(218, 'Spencer',   '(8) (8) (12) m(20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
(219, 'Talus',     '(4) m(12) (20) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers"));

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
(230, 'Dr. Speculo',    '(6) (8) (12) o(Y) o(Y)',       0, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
# Guillermo: X and Y cannot be the same size
(231, 'Guillermo',      '(6) (10) (20) (X) (Y)',        1, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
(232, 'Harry Pudding',  '(4) (7) (2/20) (2/20) (10)',   0, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
(233, 'Lascivia',       '(4) zp(12) (20) p(X)',         0, 0, (SELECT id FROM buttonset WHERE name="Renaissance")),
(234, 'MothMan',        '(8) (8) (12) (16) (Y)?',       0, 1, (SELECT id FROM buttonset WHERE name="Renaissance"));

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
(248, 'Assailer',       '(12) (12) (20) (2/20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="SFR")),
(249, 'Harbinger',      '(4) (4) (4/12) (10/20) (V)',   0, 1, (SELECT id FROM buttonset WHERE name="SFR"));

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
(286, 'George',     '(4) (6) b(6) b(20) &(Y)',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(287, 'Violette',   '(8) (8) b(10) b(12) &(Y)',     0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(288, 'Elsie',      '(4) b(4) (10) b(12) &(Y)',     0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(289, 'Kasper',     '(6) b(8) (12) b(20) &(Y)',     0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(290, 'Montague',   'b(4) b(10) (12) (20) &(Y)',    0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(291, 'Neville',    'b(4) (8) b(8) (20) &(Y)',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(292, 'Thaddeus',   '(10) (14) (14) (18) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
(293, 'Buckley',    '(4) (4) (18) (X) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow"));

# VAMPYRES (Cheapass Games)            INTRODUCES Shadow(s) dice
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(300, 'Angel',       's(4) (6) s(12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(301, 'Buddy',       's(6) (10) s(20) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(302, 'Dunkirk',     '(6) (6) (10) (20) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(303, 'Starchylde',  's(6) (8) s(10) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(304, 'McGinty',     '(4) s(10) (12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
(305, 'Tiffany',     '(4) s(8) (8) (10) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Vampyres"));

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
(308, 'Pikathulhu',    '(6) c(6) (10) (12) c(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
(309, 'Ulthar',        '(4) (8) (10) c(10) c(T)',     0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne"));

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


###############################################
##### UNOFFICIAL SETS - FAN AND VANITY SETS

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
(562, 'The Japanese Beetle(u)',  '(6) (12) (12) _(V) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(563, 'The Flying Squirrel(u)',  '(4) (6) (12) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(564, 'Joe McCarthy',            '(10) (12) (12) (20) (X)',    0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(565, 'Kremlina',                '(6) (8) (10) (12) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(566, 'Max Factor',              '(6) (8) (12) (X) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
(567, 'The Frenchman',           '(8) (10) (10) (12) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)"));



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
(510, 'SC(The Deuce)',   'z(4) t(6) p(8) s(10) (12/16)! +(20)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
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
