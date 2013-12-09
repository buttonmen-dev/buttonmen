DELETE FROM buttonset;
INSERT INTO buttonset (name) VALUES
('Soldiers'),
('Sanctum'),
('Lunch Money'),
('Vampyres'),
('Legend of the Five Rings'),
('BROM'),
('1999'),
('BRAWL'),
('Buttonlords'),
('Studio Foglio'),
('Sailor Moon 1'),
('Freaks'),
('Fantasy'),
('Dork Victory'),
('Sailor Moon 2'),
('Bruno'),
('2000'),
('Sluggy Freelance'),
('Samurai'),
('2001'),
('Diceland')
('2000 ShoreCon')
('2002 Anime Expo')
('2002 Origins')
('2004 Origins')
('Balticon 34')
('Bar Mitzvah')
('Bridge and Tunnel')
('Button Brains')
('Chicagoland Gamers Conclave')
('Metamorphers')
('Renaissance')
('SFR')
('Space Girlz')
('SydCon 10')
('Yoyodyne');

#('Vegas')
#('Wonderland')
#('Order of the Dolls')
#('Tenchi Muyou!')
#('Bull') This is a button that doesn't seem attached to a set
#('7 deadly sins')
#('Chicago Crew')
#('Four Horsemen')
#('Free Radicals')
#('Geekz')
#('Hodge Podge')
#('Iron Chef')
#('Victorian Horror')

DELETE FROM button;
INSERT INTO button (name, recipe, tourn_legal, set_id) VALUES
# 1999 old site only includes Eiko, Wu Lan, and Zeppo in this set
# Brigid and O-Lass from XXXenophile (Studio Foglio)
# Buzzing Weasel & James Earnest (missing Carson) from In-House (In-House)
# Dirgo & Flaire from Majesty (Cyberlore)
# Adam Spam & Poly from Polycon (Polycon)
('Bunnies',     '(1) (1) (1) (1) (X)',           0, (SELECT id FROM buttonset WHERE name="1999")),
('Lab Rat',     '(2) (2) (2) (2) (X)',           0, (SELECT id FROM buttonset WHERE name="1999")),
('Brigid',      '(8) (8) (X) (X) (X)',           1, (SELECT id FROM buttonset WHERE name="1999")),
('O-Lass',      '(6) (12) (X) (X) (X)',          1, (SELECT id FROM buttonset WHERE name="1999")),
('Zeppo',       '(4) (12) (20) (X)!',            1, (SELECT id FROM buttonset WHERE name="1999")),
('Eiko',        '(4) (6) (6) (12) (X)',          1, (SELECT id FROM buttonset WHERE name="1999")),
('Wu Lan',      '(4) (10) (20) (20) (X)',        1, (SELECT id FROM buttonset WHERE name="1999")),
# ('Buzzing Weasel','(Fudge) (Regular) (Prestige) (Gamer) (Screw)',         1, (SELECT id FROM buttonset WHERE name="1999")),
# ('James Ernest','(pi) (inf) (sqrt(-2)) (X)',       0, (SELECT id FROM buttonset WHERE name="1999")),
('Dirgo',       '(20) (20) (20) (X)',            1, (SELECT id FROM buttonset WHERE name="1999")),
('Flaire',      '(6) (10) (10) (12) (X)',        1, (SELECT id FROM buttonset WHERE name="1999")),
('Poly',        '(4) (6) F(8) (20) (X)',         1, (SELECT id FROM buttonset WHERE name="1999")),
('Adam Spam',   'F(4) F(6) (6) (12) (X)',        1, (SELECT id FROM buttonset WHERE name="1999")),
# 2000
# Tirade from Wunderland (Wunderland)
# Flying Squirrel & Japanese Beetle from Japanese Beetle! (The Japanese Beetle)
# Gordo from ButtonMen Poster 1999 (Cheapass Games)
# Cthulhu from Cthulhu (Chaosium)
# Sven & Yseulte from Fairies (Cool Tuna)
# Gore & Bush from GenCon 2000 (Cheapass Games)
# Howling Wolf & White Tiger from Howling Wolf (Howling Wolf Studios)
# Nerni & Yeti from Majesty - Northern Expansion (Cyberlore)
# Me am Ork! from Orc! (Green Ronin)
# Rikachu & Tess from Origins 2000 (Origins)
# Ginzu & Gratch from Save The Ogres (Save the Ogres)
# Is this Guillermo the same Guillermo from Rennaissance?  I added the (p) to this one, since I recall the other from the old site.
# ('Gordo',       unique swing values   '(V) (W) (X) (Y) (Z)',                 0, (SELECT id FROM buttonset WHERE name="2000")),
('Tirade',              '(6) ^(6) ^(10) (V)!',                0, (SELECT id FROM buttonset WHERE name="2000")),
('Tess',                'n(4) (8) (12) n(20) (X)',            1, (SELECT id FROM buttonset WHERE name="2000")),
('Ginzu',               '(8) (8) s(12,12) (20) (X)',          1, (SELECT id FROM buttonset WHERE name="2000")),
('Gratch',              '(6) s(8,8) (20) s(20) (X)',          1, (SELECT id FROM buttonset WHERE name="2000")),
('Bush',                '(4) (20) (4|8) (6|12) (6|20)',       1, (SELECT id FROM buttonset WHERE name="2000")),
('Gore',                '(4) (20) (4|8) (6|12) (6|20)',       1, (SELECT id FROM buttonset WHERE name="2000")),
('Cthulhu',             '(4) (20) s(4,8) s(6,12) ps(6,20)',   1, (SELECT id FROM buttonset WHERE name="2000")),
('Nerni',               '(4) (4) (12) (X) (X)',               1, (SELECT id FROM buttonset WHERE name="2000")),
('Yeti',                '(10) (20) (30) (30) (X)',            1, (SELECT id FROM buttonset WHERE name="2000")),
('Me am ORK',           '(8) (8) (8) p(8) (X)',               1, (SELECT id FROM buttonset WHERE name="2000")),
('Sven',                'q(20) q(20) (8|12) (6|12) (4)',      1, (SELECT id FROM buttonset WHERE name="2000")),
('Yseulte',             'p(20) q(10) q(8) (X) (X)',           1, (SELECT id FROM buttonset WHERE name="2000")),
# ('The Flying Squirrel', 's(6) s(12) s(12) s(20)',             1, (SELECT id FROM buttonset WHERE name="2000")),
# ('The Japanese Beetle', '(6) (8) (12) (X)',                   1, (SELECT id FROM buttonset WHERE name="2000")),
('Howling Wolf',        'd(4) (8) (12) (20) d(20)',           1, (SELECT id FROM buttonset WHERE name="2000")),
('White Tiger',         '(6) d(6) (10) (12) d(12)',           1, (SELECT id FROM buttonset WHERE name="2000")),
('Rikachu',             '(1) (1) (1) (1) (Y)',                0, (SELECT id FROM buttonset WHERE name="2000")),
# BALTICON 34 (Balticon)                         NO SPECIAL DICE SKILLS       (2000 Rare-Promo on old site)
('Social Class',   '(4/6) (6/8) (8/10) (10/12) (12/20)',        1, (SELECT id FROM buttonset WHERE name="Balticon 34")),
# 2000 SHORECON (ShoreCon)            NO SPECIAL DIE SKILLS
('ConMan',     '(4) (6) p(20)',       1, (SELECT id FROM buttonset WHERE name="2000 ShoreCon")),
# 2001
# Effster & Alan Clark from Demicon the 13th (DemiCon)
# Jorgi and Tsusuko from GenCon 2001 (Cheapass Games)
# Nickie, Sonia, Tamiya (Angora missing) from Origins 2001 (Cheapass Games) - Angora: z4 z6 z8 z10 zX
('Nickie',              'z(4) (10) (10) (12) z(12)',          1, (SELECT id FROM buttonset WHERE name="2001")),
('Sonia',               '(6) (6) z(12) (20) (20)',            1, (SELECT id FROM buttonset WHERE name="2001")),
('Tamiya',              '(4) (8) (8) (12) z(20)',             1, (SELECT id FROM buttonset WHERE name="2001")),
('Jorgi',               '(4) (6) (8) (20) p(X)',              1, (SELECT id FROM buttonset WHERE name="2001")),
('The Effster',         's(4) (8) (8) s(12) (X)',             1, (SELECT id FROM buttonset WHERE name="2001")),
('The Fictitious Alan Clark', 's(8) s(8) (3|12) (20) (X)',    1, (SELECT id FROM buttonset WHERE name="2001")),
# SFR (SFR)                                   SKILLS: Option           (2001 Rare-Promo on old site)
('Assailer',       '(12) (12) (20) (2/20) (X)',    1, (SELECT id FROM buttonset WHERE name="SFR")),
('Harbinger',      '(4) (4) (4/12) (10/20) (V)',   1, (SELECT id FROM buttonset WHERE name="SFR")),
# SYDCON 10 (SydCon)                          NO SPECIAL DIE SKILLS    (2001 Rare-Promo on old site)
('Gripen',     '(4) (6) (8) G(12) (X)',       1, (SELECT id FROM buttonset WHERE name="SydCon 10")),
# 2002 ANIME EXPO                                NO SPECIAL DICE SKILLS
('MAX(p)',          '(4) (6) (18) (20 (X)',                     1, (SELECT id FROM buttonset WHERE name="2002 Anime Expo")),
# 2002 Origins (Cheapass Games)         SKILLS: Stinger(g)
('Micro',       '(g4 g4 12 p12 gX)',                  0, (SELECT id FROM buttonset WHERE name="2002 Origins")),
# CHICAGOLAND GAMERS CONCLAVE (Chicagoland Gamers Conclave)     Skills: option       (2003 Rare-Promo on old site)
('Apples',       '(8) (8) (2/12) (8/16) (20/24)',       1, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave")),
('Green Apple',  '(8) (10) (1/8) (6/12) (12/20)',       1, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave")),
# 2004 ORIGINS (Flying Buffalo) 
#    INTRODUCES Sleep dice AND Game dice; Fire(F); Poison(p); Rage(G); Shadow(s); Slow(w); Speed(z); Value(v); Option
('Amara Wintersword',     '(4) (8) (12) (12) (X)?',                          1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Beatnik Turtle',        'wHF(4) (8) (10) vz(20) vz(20)',                   0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Captain Bingo',         '(8 6/12 6/12 12/20 X',                            1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Delt',                  'G(4) G(4) (10) (12) G(X)',                        1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Oni',                   '(4) (10) f(12) f(12) (V)',                        1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Reggie',                '(8) (10) G(12) (20) G(20)',                       1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# ('Rold',                  '(6) (6) game(6) game(6) game(6)',                          1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Spite',                 'p(6) (6) (6) s(X) (X)',                           1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Super Germ',            'p(10) p(12) p(16) p(20) p(24) p(30) p(30) p(X)',  1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Cheese Weasel',         '(6) (8) (12) (16) (20)',                          1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#? just wondering why these four were not included in the original site.  
#?('Billy Shakespear',      '(15) (64) (16) (16)',                             ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#?('Drew's Truck',          '(5) (10) (15) (20) (X)',                          ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#?('Igor(p)',               '(8) (8) z(12) (20) (X)',                          ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#?('Mike Young',            '(X) (X) (Y) (Y)',                                 ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#('Killer Christmas Tree', '(6) sleep(6) (10) sleep(12) (X)',                         ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
########
# BAR MITZVAH (Theodore Alper)                         SKILLS: Speed (z); Ornery (o)
('Bar Mitzvah Boy', '(6/13) (8) (10) (f13) (f30)',              0, (SELECT id FROM buttonset WHERE name="Bar Mitzvah")),
('Judah Maccabee',  '(8) z(12) H(4) o(12) (Y)',                 0, (SELECT id FROM buttonset WHERE name="Bar Mitzvah")),
# BRAWL (Cheapass Games)              INTRODUCES Speed(z) dice
('Bennett',     '(6) (8) z(20) z(20) (S)',       1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Chris',       'z(6) z(8) (10) z(12) (S)',      1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Darwin',      '(4) (6) z(10) (20) (X)',        1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Hale',        'z(8) (12) (20) (20) (S)',       1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Morgan',      'z(10) z(12) z(12) z(X)',        1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Pearl',       '(6) (8) (12) (X) z(X)',         1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Sydney',      'z(4) z(6) z(8) (X) z(X)',       0, (SELECT id FROM buttonset WHERE name="BRAWL")),
# BROM (Cheapass Games)               INTRODUCES Poison(p) dice; Shadow(s) Slow(w) dice; special rules for Echo
('Coil',        'p(4) (12) p(20) (20) (V)',          1, (SELECT id FROM buttonset WHERE name="BROM")),
('Bane',        'p(2) p(4) (12) (12) (V)',           1, (SELECT id FROM buttonset WHERE name="BROM")),
('Lucky',       '(6) (10) p(12) (20) (X)',           1, (SELECT id FROM buttonset WHERE name="BROM")),
('Shepherd',    '(8) (8) p(16) (20) (X)',            1, (SELECT id FROM buttonset WHERE name="BROM")),
('Peace',       's(10) s(12) s(20) s(X) s(X)',       1, (SELECT id FROM buttonset WHERE name="BROM")),
('Crusher',     '(10) p(20) (20) (20) (X)',          1, (SELECT id FROM buttonset WHERE name="BROM")),
('Grist',       'p(4) (8) (10) (12) (X)',            1, (SELECT id FROM buttonset WHERE name="BROM")),
('Wastenott',   's(4) s(8) s(10) s(20) s(X)',        1, (SELECT id FROM buttonset WHERE name="BROM")),
('Reaver',      '(4) (10) (10) (12) p(X)',           1, (SELECT id FROM buttonset WHERE name="BROM")),
('Jellybean',   'p(20) s(20) (V) (X)',               1, (SELECT id FROM buttonset WHERE name="BROM")),
('Bluff',       'ps(6) ps(12) (16) (20) (X)',        1, (SELECT id FROM buttonset WHERE name="BROM")),
('Strik',       '(8) p(10) s(16) (16) (X)',          1, (SELECT id FROM buttonset WHERE name="BROM")),
#('Giant',     'w(20) w(20) w(20) w(20) w(20) w(20)', 1, (SELECT id FROM buttonset WHERE name="BROM")),
# ('Echo',       deal with Echo vs Echo '(?) (?) (?) (?) (?)',         1, (SELECT id FROM buttonset WHERE name="BROM")),
# BRIDGE AND TUNNEL (Bridge and Tunnel Software)  INTRODUCES Reboud(=) dice (not implemented); poison(p); shadow(s); option
#('Agent Orange',    '(6) p(6) =(10) (4/12) (4/20)',          1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
#('Huitzilopochtli', '(6) (8) =(10) (12) (X)',                1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Lisa',             '(4) (4) (30) (30)',                    1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Nethershadow',     '(6) (10) s(10) (10/20) (6/30)',        1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Phuong',           '(4) (10) (10) (20) (X)',               1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Uncle Scratchy',   '(2) (4) (6) (10) (X)',                 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
# BRUNO (Hunter Johnson)              INTRODUCES Berserk(B) dice; *requires special rules
# ('Bruno',       'B(4) B(8) B(20) B(20) B(X) (special 1, (X) when facing Pappy)',    1, (SELECT id FROM buttonset WHERE name="Bruno")),
# ('Pappy',       '(4) (4) (10) B(20) (X) (special 2, B(X) when facing Bruno)',    1, (SELECT id FROM buttonset WHERE name="Bruno")),
('Synthia',     'B(4) (12) (12) (T) B(T)',       1, (SELECT id FROM buttonset WHERE name="Bruno")),
('The GM',      '(4) (8) (12) (16) B(U)',        1, (SELECT id FROM buttonset WHERE name="Bruno")),
# BUTTON BRAINS (LinguaPlay)                         introduces Konstant(k) dice; option; twin
('Al-Khwarizmi',           '(4) k(6) (8) (12) (20)',            1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Carl Friedrich Gauss',   'k(6) (8) (8) (12,12) (20)',         1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Fan Chung Graham',       '(4) k(6) (8) (10/20) (X)?',         1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Ron Graham',             'k(6) (10) (12) (20) (V)?',          1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Leonard Eugene Dickson', '(3) k(6) (10) (20) (W)',            1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Paul Erdos',             '(3) (4) k(6) (12) (U)',             1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Pythagoras',             'k(6) (8) (10) (12) (Z)',            1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Theano',                 '(4) k(6) (8,8) (10) (S)',           1, (SELECT id FROM buttonset WHERE name="Button Brains")),
# BUTTONLORDS (Green Knight)          INTRODUCES Auxilary(+) dice; Shadow(s)
('Arthur',      '(8) (8) (10) (20) (X) +(20)',   1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Mordred',     's(6) (10) (10) s(20) s(X) +(4)',1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Lancelot',    '(10) (12) (20) (20) (X) +(X)',  1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Gawaine',     '(4) (4) (12) (20) (X) +(6)',    1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Morgan Le Fay','s(4) (12) s(12) (20) (X) +(12)',1,(SELECT id FROM buttonset WHERE name="Buttonlords")),
('Guenever',    '(6) s(8) (10) (12) (X) +(8)',   1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Nimue',       '(4) (6) s(12) (20) (X) +s(10)', 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Merlin',      '(2) (4) s(10) s(20) (X) +s(X)', 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
# DICELAND note:                      INTRODUCES Stinger(g) dice
('Buck Godot',  'g(8) g(10) (12) (20) (X)',      1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Cass',        '(4) g(4) g(6) (12) (X)',        1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Z-Don',       'g(6) g(8) p(16) (X) (X)',       1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Crysis',      'g(8) (10) (10) (X) (X)',        1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Golo',        'g(10) g(12) g(20) g(X)',        1, (SELECT id FROM buttonset WHERE name="Diceland")),
# DORK VICTORY (Cheapass Games)       INTRODUCES Mood Swing(?); Speed(z); Twin 
('Bill',        '(20) (20) (20) (V,V)',          1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Carson',      '(4,4) (8) (10) (12) (V)',       1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Gilly',       '(6) (8) z(8) (20) (X)?',        1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Igor',        '(3) (12) (20) (20) (X)?',       1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Ken',         '(8) (10) z(12) (20) (V)?',      1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Matt',        'z(8) (10) (10) z(10) (V)?',     1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
# FANTASY (Cheapass Games)            INTRODUCES Option Dice; *Special Rules (& Die Skill) for Socrates
('Luce',        '(8) (10) (20) (4|20) (8|20)',   1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Frasquito',   '(4) (6) (8) (12) (2|20)',       1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Lark',        '(6) (20) (2|8) (4|12) (6|10)',  1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Theophilus',  '(8) (10) (12) (10|20) (20|30)', 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Mischa',      '(10) (12) (12) (4|12) (6|12)',  1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Chang',       '(2|20) (2|20) (4|20) (8|20)',   1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Aldric',      '(8) (8) (4|12) (8|20) (12|20)', 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Elihu',       '(4|6) (4|8) (6|10) (6|12) (8|20)', 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Farrell',     '(10) (12) (6|20) (6|20) (8|12)',1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Nikolai',     '(20) (4|10) (4|12) (6|10) (6|20)', 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Cesare',      '(10) (4|10) (6|10) (10|12) (10|20)', 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Vincent',     '(30) (30) (30) (6|30)',         1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Nightmare',   '(4) (8) (10) (20) (20)',        1, (SELECT id FROM buttonset WHERE name="Fantasy")),
# ('Socrates',    '(4) (10) (12) (Y)',             1, (SELECT id FROM buttonset WHERE name="Fantasy")),
# ('Dead Guy',    '(0) (0) (0) (0) (0)',           1, (SELECT id FROM buttonset WHERE name="Fantasy")),
# FREAKS (Cheapass Games)              INTRODUCES Queer(q) dice; Poison(p); Shadow(s); Speed(z)
('Max',         'p(12) p(12) p(20) p(20) p(30) p(30) p(X) p(X)', 1, (SELECT id FROM buttonset WHERE name="Freaks")),
('Mister Peach','(6) z(8,8) ps(12,12) (V,V)!',   1, (SELECT id FROM buttonset WHERE name="Freaks")),
('Simon',       'q(4) q(6) q(12) q(20) q(X)',    1, (SELECT id FROM buttonset WHERE name="Freaks")),
('Werner',      '(8) (10) (10) (12) pzs(V)!',    1, (SELECT id FROM buttonset WHERE name="Freaks")),
# LEGEND OF THE FIVE RINGS (Wizards of the Coast)  INTRODUCES Focus(f) dice
('Crab',        '(8) (10) (12) f(20) f(20)',     1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Crane',       '(4) f(6) f(8) (10) (12)',       1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Dragon',      '(4) (8) f(8) f(12) (20)',       1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Lion',        '(4) f(6) (10) f(20) (20)',      1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Mantis',      '(6) f(8) f(10) (20) (20)',      1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Naga',        'f(6) (8) (8) (8) f(20)',        1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Phoenix',     '(4) (6) f(8) (10) f(20)',       1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Ronin',       '(6) f(6) (8) f(12) (12)',       1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Scorpion',    '(4) (4) f(4) f(12) (20)',       1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Unicorn',     '(4) (4) f(6) f(12) (20)',       1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Mirumoto',    'f(6) (10) f(10) (12) (20)',     1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Kakita',      '(6) f(6) (10) f(12) (20)',      1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
# LUNCH MONEY (Atlas Games)           INTRODUCES Trip(t) dice
('Charity',     't(4) (4) (8) (12) (X)',         1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Prudence',    '(1) t(4) (6) (12) (X)',         1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Hope',        't(1) (2) t(4) (6) (Y)',         1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Chastity',    't(6) (6) (10) (10) (X)',        1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Faith',       't(2) (6) (10) (12) (Y)',        1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Temperance',  't(2) (8) (12) (20) (Y)',        1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Patience',    '(2) (2) (8) (12) (X)',          1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
# METAMORPHERS (IMGames)          INTRODUCES Morphing(m) dice
('Daisy',     'm(6) (10) (10) (20) (X)',     1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Jingjing',  'm(4) (8) (10) (20) (X)',      1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Mau',       '(6) (6) (8) (12) m(X)',       1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Spencer',   '(8) (8) (12) m(20) (X)',      1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Talus',     '(4) m(12) (20) (20) (X)',     1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
# RENAISSANCE (Stone Press)                 SKILLS: Mood swing(?); Ornery(o); Poison(p); Speed(z); Option; Unique(u)
('Dr. Speculo',    '(6) (8) (12) o(Y) o(Y)',       1, (SELECT id FROM buttonset WHERE name="Renaissance")),
('Guillermo',      '(6) (10) (20) u(X) u(Y)',      1, (SELECT id FROM buttonset WHERE name="Renaissance")),
('Harry Pudding',  '(4) (7) (2/20) (2/20) (10)',   1, (SELECT id FROM buttonset WHERE name="Renaissance")),
('Lascivia',       '(4) zp(12) (20) p(X)',         0, (SELECT id FROM buttonset WHERE name="Renaissance")),
('MothMan',        '(8) (8) (12) (16) (Y)?',       1, (SELECT id FROM buttonset WHERE name="Renaissance")),
# SAILOR MOON 1 (Guardians of Order)        INTRODUCES Reserve(r) dice; AND Warrior(`) dice
('Sailor Moon',         '(8) (8) (10) (20) r(6) r(10) r(20) r(20)',      1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Queen Beryl',         '(4) (8) (12) (20) r(4) r(12) r(20) r(20)',      1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Sailor Mercury',      '(4) (8) (8) (12) r(4) r(4) r(8) r(10)',         1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Zoycite',             '(4) (10) (10) (10) r(6) r(6) r(8) r(8)',        1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Sailor Mars',         '(4) (6) (10) (20) r(6) r(10) r(10) r(20)',      1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Jedite',              '(6) (6) (12) (12) r(4) r(6) r(6) r(8)',         1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Sailor Jupiter',      '(6) (10) (12) (20) r(6) r(12) r(12) r(20)',     1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Nephlite',            '(4) (6) (12) (12) r(8) r(10) r(10) r(12)',      1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Sailor Venus',        '(4) (6) (10) (12) r(4) r(8) r(8) r(12)',        1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Malachite',           '(8) (12) (20) (20) r(10) r(12) r(12) r(20)',    1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Tuxedo Mask',         '(6) (6) (10) (20) r(4) r(8) r(10) r(12) r(20)', 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Shadow Warriors',     '(1) (2) `(4) `(6) `(8) `(10) `(12)',            1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
# SAILOR MOON 2 (Guardians of Order)           SKILLS: Reserve(r)
('Luna & Artemis',      '(1) (4) (10) (20) r(2) r(2) r(8) r(8)',         1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Wicked Lady',         '(6) (6) (10) (12) r(4) r(8) r(10) r(20)',       1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Queen Serenity',      '(4) (6) (10) (12) r(6) r(10) r(12) r(20)',      1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Rubeus',              '(4) (4) (12) (12) r(6) r(10) r(20) r(20)',      1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Princess Serena',     '(6) (8) (12) (20) r(4) r(10) r(12) r(20)',      1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Sapphire',            '(6) (10) (12) (12) r(8) r(8) r(10) r(12)',      1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Rini',                '(2) (4) (6) (6) r(4) r(10) r(12) r(12)',        1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Wiseman',             '(20) (20) (20) (20)',                           1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Neo-Queen Serenity',  '(12) (20) (20) r(4) r(6) r(8) r(10) r(12)',     1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Prince Diamond',      '(4) (6) (12) (20) r(8) r(10) r(10) r(20)',      1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('King Endymion',       '(6) (10) (10) (20) r(6) r(10) r(12) r(20)',     1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Emerald',             '(6) (8) (12) (20) r(4) r(6) r(10) r(20)',       1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
# SAMURAI (Cheapass Games)               SKILLS: Focus(f)
('Honzo',       '(10) (12) f(20) (V) (X)',       1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Konami',      '(6) (8) f(10) f(10) (X)',       1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Okaru',       '(4) f(4) (6) (12) (V)',         1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Sadakuro',    'f(4) f(6) f(8) f(10) (12)',     1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Tonase',      '(4) (4) (8) (20) f(X)',         1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Yuranosuke',  '(4) (8) (12) f(12) (X)',        1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Tsusuko',     'f(6) (10) (10) (16) (X)',       1, (SELECT id FROM buttonset WHERE name="Samurai")),
# SANCTUM (Digital Addiction)         NO SPECIAL SKILLS
('Dracha',      '(4) (10) (20) (20) (X)',        1, (SELECT id FROM buttonset WHERE name="Sanctum")),
('Ogi',         '(2) (4) (10) (X) (X)',          1, (SELECT id FROM buttonset WHERE name="Sanctum")),
('Fingle',      '(6) (7) (8) (12) (X)',          1, (SELECT id FROM buttonset WHERE name="Sanctum")),
('Ngozi',       '(4) (6) (8) (10) (X)',          1, (SELECT id FROM buttonset WHERE name="Sanctum")),
# SLUGGY FREELANCE (Sluggy)          ITNRODUCES: option dice
('Aylee',       '(8) (10|20) (12) (12|20) (20)', 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Bun-Bun',     '(4|10) (4|12) (6|12) (20) (20)',1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('KiKi',        '(3|20) (4) (8|12) (10) (10|20)',1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Riff',        '(4|20) (6) (6|8) (10|12) (20)', 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Torg',        '(6) (6|20) (8) (10|12) (12|20)',1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Zoë',         '(4|12) (6|10) (8) (10|20) (12|20)', 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
# SOLDIERS (Cheapass Games) NOTE: original Buttonmen set, no special die skills
('Avis',        '(4) (4) (10) (12) (X)',         1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Hammer',      '(6) (12) (20) (20) (X)',        1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Bauer',       '(8) (10) (12) (20) (X)',        1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Stark',       '(4) (6) (8) (X) (X)',           1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Clare',       '(6) (8) (8) (20) (X)',          1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Kith',        '(6) (8) (12) (12) (X)',         1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Karl',        '(4) (6) (6) (20) (X)',          1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Iago',        '(20) (20) (20) (X)',            1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Niles',       '(6) (10) (10) (12) (X)',        1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Shore',       '(4) (4) (20) (20) (X)',         1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Hannah',      '(8) (10) (10) (10) (X)',        1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Kublai',      '(4) (8) (12) (20) (X)',         1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Changeling',  '(X) (X) (X) (X) (X)',           0, (SELECT id FROM buttonset WHERE name="Soldiers")),
# SPACE GIRLZ (buttonmen.com)   INTRODUCES Plasma(P){} dice; Mighty(H); Ornery(o); Poison(p); Shadow(s); Weak(h); twin
('Maya',       'o(6) (12) pP{hs,H}(12) (20) (S)',             0, (SELECT id FROM buttonset WHERE name="Space Girlz")),
('Zeno',       'P{h,H}(6) P{h,H}(8) P{h,H}(4,4) (20) (X)',    0, (SELECT id FROM buttonset WHERE name="Space Girlz")),
# STUDIO FOGLIO (Studio Foglio)       INTRODUCES Twin dice; Poison(p)
('Phil',        '(8) (8) (10,10) (20) (X)',      1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Dixie',       '(4) (6) (10) (12,12) (X)',      1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Growf',       '(4,4) (6) (8) (12) (X)',        1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Buck',        '(6,6) (10) (12) (20) (W,W)',    1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# ('Winslow',     '(30)',                          0, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Agatha',      '(4) (6) (8,8) (20) (X)',        1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Krosp',       '(4) (6,6) (12) (12) (X)',       1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Klaus',       '(4) (10,10) (20) (20) (W)',     1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Von Pinn',    '(4) p(6,6) (10) (20) (W)',      1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Bang',        'p(4,4) (6) (12) (12) (X)',      1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Gil',         '(8) (8) p(8,8) (20) (X)',       1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('James',       '(4) (8,8) (10,10) (12) (W,W)',  0, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# The James Beast: 4 (8,8) (10,10) 12 (W,W)
#VAMPYRES (Cheapass Games)            INTRODUCES Shadow(s) dice
('Angel',       's(4) (6) s(12) (12) (X)',       1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Buddy',       's(6) (10) s(20) (20) (X)',      1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Dunkirk',     '(6) (6) (10) (20) s(X)',        1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Starchylde',  's(6) (8) s(10) (12) (X)',       1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('McGinty',     '(4) s(10) (12) (12) (X)',       1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Tiffany',     '(4) s(8) (8) (10) s(X)',        1, (SELECT id FROM buttonset WHERE name="Vampyres"))
# YOYODYNE (Fuzzface)                   INTRODUCES Chance(c) dice
('Fuzzface',      '(4) (8) (10) c(10) c(12)',      1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
('John Kovalic',  '(6) c(6) (10) (12) c(20)',      1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
('Pikathulhu',    '(6) c(6) (10) (12) c(X)',       1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
('Ulthar',        '(4) (8) (10) c(10) c(T)',       1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),;

