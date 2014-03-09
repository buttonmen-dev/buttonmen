DELETE FROM buttonset;
INSERT INTO buttonset (name) VALUES
('Soldiers'),
('The Big Cheese'),
('Sanctum'),
('Lunch Money'),
('1999 Rare / Promo'),
('Vampyres'),
('Legend of the Five Rings'),
('Polycon'),
('BROM'),
('2000 Rare / Promo'),
('BRAWL'),
('Buttonlords'),
('Studio Foglio'),
('Sailor Moon 1'),
('Freaks'),
('Balticon 34'),
('ButtonBroccoli'),
('Las Vegas'),
('Fantasy'),
('Renaissance'),
('Save The Ogres'),
('Yoyodyne'),
('Presidential'),
('Majesty'),
('Wonderland'),
('2000 ShoreCon'),
('Fairies'),
('Dork Victory'),
('Japanese Beetle'),
('Howling Wolf'),
('Metamorphers'),
('Sailor Moon 2'),
('Bruno'),
('Tenchi Muyo!'),
('Sluggy Freelance'),
('Samurai'),
('SydCon 10'),
('Demicon the 13th'),
('Diceland'),
('2002 Anime Expo'),
('2002 Origins'),
('Bar Mitzvah'),
('Button Brains'),
('Chicagoland Gamers Conclave'),
('2003 Rare-Promos'),
('SFR'),
('2004 Origins'),
('Space Girlz'),
('Bridge and Tunnel'),
('2005 Rare Promo'),
# fan sets, vanity sets, sets made for the old site (?)
('Geekz'),
('Iron Chef'),
('7 deadly sins'),
('Chicago Crew'),
('Four Horsemen'),
('Free Radicals'),
('Hodge Podge'),
('Victorian Horror'),

# official (?) ButtonMen sets not on old site
('Everything to Gain'),
('Gaming Guardians'),
('Fightball'),
('Nodwick'),
('High School Drama!'),
('Unexploded Cow'),
# unofficial ButtonMen sets not on old site
#('Japanese Beetle (unofficial)'),
#('Blademasters'),
#('Blademasters: The Second Shot'),
#('Blademasters 3'),
#('Order of the Dolls'),

#('50 States'),

#
('Classic Fanatics');

DELETE FROM button;

# 1999 RARE-PROMO                       INTRODUCES Turbo(!) Swing Dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
# ZEPPO from 1999 Origins
('Zeppo',       '(4) (12) (20) (X)!',              0, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# Eiko & Wu Lan from Furthest North Crew / Toivo Rovainen / Cheapass
('Eiko',        '(4) (6) (6) (12) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
('Wu Lan',      '(4) (10) (20) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# BUZZING WEASEL's recipe stands for(Fudge) (Regular) (Prestige) (Gamer) (Screw), which are all dice particular to this button
# rather than any standard coding for swing sizes or die skills.  IMO these dice should not be made avialable for other buttons.
# ('Buzzing Weasel','F R P G S',                   1, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# JAMES ERNEST is mathematically impossible to implement (though it might be fun to try to work around this, changing sizes to die skills)
# ('James Ernest','(pi) (inf) (sqrt(-2)) (X)',     1, 0, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# Carson from 1999 GenCon
('Carson(p)',      '(4) (5) (6) (7) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo"));

# 2000 RARE-PROMO
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
# Gordo from ButtonMen Poster 1999 (Cheapass Games)
('Gordo',               'u(V) u(W) u(X) u(Y) u(Z)',           0, 0, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo")),
# Me am ORK! from Orc! (Green Ronin)
('Me am ORK!',           '(8) (8) (8) p(8) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo")),
# Rikachu Origins 2000 (Origins)
('Rikachu',             '(1) (1) (1) (1) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo"));

# 2000 SHORECON (ShoreCon)            NO SPECIAL DIE SKILLS
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('ConMan',     '(4) (6) p(20)',      0,  1, (SELECT id FROM buttonset WHERE name="2000 ShoreCon"));

# 2002 ANIME EXPO                                NO SPECIAL DICE SKILLS
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('MAX(p)',          '(4) (6) (18) (20) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="2002 Anime Expo"));

# 2002 Origins (Cheapass Games)                       SKILLS: Stinger(g) on old site)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Apples',       '(8) (8) (2/12) (8/16) (20/24)',       0, 1, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave")),
('Green Apple',  '(8) (10) (1/8) (6/12) (12/20)',       0, 1, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave"));

# 2003 Rare-Promos  (lacking information about this set except for one button, Apples and Green Apples were once here)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Abe Caine',    'p(4,4) p(8,8) (10) ps(24) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="2003 Rare-Promos"));

# 2004 ORIGINS (Flying Buffalo)
#    INTRODUCES Sleep(Z) dice AND Game(#) dice; Fire(F); Poison(p); Shadow(s); Slow(w); Speed(z); Value(v); Option
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Amara Wintersword',     '(4) (8) (12) (12) (X)?',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Beatnik Turtle',        'wHF(4) (8) (10) vz(20) vz(20)',                   0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Captain Bingo',         '(8) (6/12) (6/12) (12/20) (X)',                   0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Oni',                   '(4) (10) f(12) f(12) (V)',                        0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Spite',                 'p(6) (6) (6) s(X) (X)',                           0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Super Germ',            'p(10) p(12) p(16) p(20) p(24) p(30) p(30) p(X)',  0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Cheese Weasel',         '(6) (8) (12) (16) (20)',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# The old site used raGe dice instead of Game dice in the following recipes.
('Delt',                  'R(4) R(4) (10) (12) R(X)',                        0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Reggie',                '(8) (10) R(12) (20) R(20)',                       0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Rold',                  '(6) (6) R(6) R(6) R(6)',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# since old site users are used to playing these with Rage . . .
('Delt Rage',              'G(4) G(4) (10) (12) G(X)',                       0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Reggie Rage',            '(8) (10) G(12) (20) G(20)',                      0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Rold Rage',              '(6) (6) G(6) G(6) G(6)',                         0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# not included in the original site - assumed not TL
# ('Killer Christmas Tree',  '(6) Z(6) (10) Z(12) (X)',                        0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Billy Shakespear',       '(15) (64) (16) (16)',                            0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Drew\'s Truck',          '(5) (10) (15) (20) (X)',                         0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Igor(p)',                '(8) (8) z(12) (20) (X)',                         0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Mike Young',             '(X) (X) (Y) (Y)',                                0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins"));

#('2005 Rare Promo')                         NO SPECIAL SKILLS
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Kitty Cat Seven',     '(4) (6) (8) (10) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare Promo")),
('Sylvia Branham',      '(6) (6) (6) (X) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare Promo"));

# BALTICON 34 (Balticon)                             INTRODUCES Option(/)       (2000 Rare / Promo on old site)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Social Class',   '(4/6) (6/8) (8/10) (10/12) (12/20)',        0, 1, (SELECT id FROM buttonset WHERE name="Balticon 34"));

# BAR MITZVAH (Theodore Alper)                         SKILLS: Speed (z); Ornery (o)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Bar Mitzvah Boy', '(6/13) (8) (10) f(13) f(30)',             0, 0, (SELECT id FROM buttonset WHERE name="Bar Mitzvah")),
('Judah Maccabee',  '(8) z(12) H(4) o(12) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="Bar Mitzvah"));

# THE BIG CHEESE (Cheapass Games)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Bunnies',     '(1) (1) (1) (1) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="The Big Cheese")),
('Lab Rat',     '(2) (2) (2) (2) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="The Big Cheese"));

# BRAWL (Cheapass Games)              INTRODUCES Speed(z) dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Bennett',     '(6) (8) z(20) z(20) (S)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Chris',       'z(6) z(8) (10) z(12) (S)',            0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Darwin',      '(4) (6) z(10) (20) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Hale',        'z(8) (12) (20) (20) (S)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Morgan',      'z(10) z(12) z(12) z(X)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Pearl',       '(6) (8) (12) (X) z(X)',               0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Sydney',      'z(4) z(6) z(8) z(10) z(X)',           0, 0, (SELECT id FROM buttonset WHERE name="BRAWL")),
# Brawl: Catfight Girls from 2001 Origins
('Angora',      'z(4) z(6) z(8) z(10) z(X)',           0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Nickie',      'z(4) (10) (10) (12) z(12)',           0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Sonia',       '(6) (6) z(12) (20) (20)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Tamiya',      '(4) (8) (8) (12) z(20)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
# TESS: from  Origins 2000 / Club Foglio;   INTRODUCES Null(n) Dice
('Tess',        'n(4) (8) (12) n(20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL"));

# BROM (Cheapass Games)               INTRODUCES Poison(p)and Slow(w) dice; Shadow(s) dice; special rules for Echo
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Coil',        'p(4) (12) p(20) (20) (V)',            0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Bane',        'p(2) p(4) (12) (12) (V)',             0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Lucky',       '(6) (10) p(12) (20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Shepherd',    '(8) (8) p(16) (20) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Peace',       's(10) s(12) s(20) s(X) s(X)',         0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Crusher',     '(10) p(20) (20) (20) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Grist',       'p(4) (8) (10) (12) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Wastenott',   's(4) s(8) s(10) s(20) s(X)',          0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Reaver',      '(4) (10) (10) (12) p(X)',             0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Jellybean',   'p(20) s(20) (V) (X)',                 0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Bluff',       'ps(6) ps(12) (16) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
('Strik',       '(8) p(10) s(16) (16) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
# Giant never goes first
('Giant',       '(20) (20) (20) (20) (20) (20)',       1, 1, (SELECT id FROM buttonset WHERE name="BROM")),
# Echo does not have it's own recipe - it copies the recipe of the opposing button
('Echo',        '',                                    1, 1, (SELECT id FROM buttonset WHERE name="BROM"));

# BRIDGE AND TUNNEL (Bridge and Tunnel Software)  INTRODUCES Reboud(=) dice (not implemented); poison(p); shadow(s); option
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Agent Orange',     '(6) p(6) =(10) (4/12) (4/20)',         0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Huitzilopochtli',  '(6) (8) =(10) (12) (X)',               0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Lisa',             '(4) (4) (30) (30)',                    0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Nethershadow',     '(6) (10) s(10) (10/20) (6/30)',        0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Phuong',           '(4) (10) (10) (20) (X)',               0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Uncle Scratchy',   '(2) (4) (6) (10) (X)',                 0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel"));

# BRUNO (Hunter Johnson)             INTRODUCES Berserk(B) dice; *requires special rules
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
# Bruno gains (X) when facing Pappy
('Bruno',       'B(8) B(8) B(20) B(20) B(X)',   1, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
# Pappy gains B(X) when facing Bruno
('Pappy',       '(4) (4) (10) B(20) (X)',       1, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
('Synthia',     'B(4) (12) (12) (T) B(T)',      0, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
('The GM',      '(4) (8) (12) (16) B(U)',       0, 1, (SELECT id FROM buttonset WHERE name="Bruno"));

# BUTTON BRAINS (LinguaPlay)                         introduces Konstant(k) dice; option; twin
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Al-Khwarizmi',           '(4) k(6) (8) (12) (20)',            0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Carl Friedrich Gauss',   'k(6) (8) (8) (12,12) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Fan Chung Graham',       '(4) k(6) (8) (10/20) (X)?',         0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Ron Graham',             'k(6) (10) (12) (20) (V)?',          0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Leonard Eugene Dickson', '(3) k(6) (10) (20) (W)',            0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Paul Erdos',             '(3) (4) k(6) (12) (U)',             0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Pythagoras',             'k(6) (8) (10) (12) (Z)',            0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Theano',                 '(4) k(6) (8,8) (10) (S)',           0, 1, (SELECT id FROM buttonset WHERE name="Button Brains"));

# BUTTONBROCCOLI (Looney Labs)                     INTRODUCES Time & Space(^) Dice; Turbo Swing
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
# Tirade from Wunderland (Wunderland)
('Tirade',              '(6) ^(6) ^(10) (V)!',                0, 0, (SELECT id FROM buttonset WHERE name="ButtonBroccoli"));

# BUTTONLORDS (Green Knight)             INTRODUCES Auxilary(+) dice; Shadow(s)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('King Arthur',    '(8) (8) (10) (20) (X) +(20)',      0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Mordred',        's(6) (10) (10) s(20) s(X) +(4)',   0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Lancelot',       '(10) (12) (20) (20) (X) +(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Gawaine',        '(4) (4) (12) (20) (X) +(6)',       0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Morgan Le Fay',  's(4) (12) s(12) (20) (X) +(12)',   0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Guenever',       '(6) s(8) (10) (12) (X) +(8)',      0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Nimue',          '(4) (6) s(12) (20) (X) +s(10)',    0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Merlin',         '(2) (4) s(10) s(20) (X) +s(X)',    0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords"));

# DEMICON THE 13TH (DemiCon)                           SKILLS Shadow; Option
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('The Effster',               's(4) (8) (8) s(12) s(X)',      0, 1, (SELECT id FROM buttonset WHERE name="Demicon the 13th")),
('The Fictitious Alan Clark', 's(8) s(8) (3/12) (20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="Demicon the 13th"));

# DICELAND                            INTRODUCES Stinger(g) dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Buck',        'g(8) g(10) (12) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Cass',        '(4) g(4) g(6) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Z-Don',       'g(6) g(8) p(16) (X) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Crysis',      'g(8) (10) (10) (X) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Golo',        'g(10) g(12) g(20) g(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
# MICRO from 2002 Origins
('Micro',       'g(4) g(4) (12) p(12) g(X)',     0, 0, (SELECT id FROM buttonset WHERE name="Diceland"));

# DORK VICTORY (Cheapass Games)       INTRODUCES Mood Swing(?); Speed(z); Twin
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Bill',        '(20) (20) (20) (V,V)',          0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Carson',      '(4,4) (8) (10) (12) z(V)',      0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Gilly',       '(6) (8) z(8) (20) (X)?',        0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Igor',        '(3) (12) (20) (20) (X)?',       0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Ken',         '(8) (10) z(12) (20) (V)',       0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Matt',        'z(8) (10) (10) z(10) (V)?',     0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory"));

# EVERYTHING TO GAIN (2001 Seattle Kotei) introduces Thief Dice; poison(p); shadow(s); speed(z)
# ASSUMED NOT TL
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Kolat',        '(20) $(10) (8) (6) ps(4)',         0, 0, (SELECT id FROM buttonset WHERE name="Everything to Gain")),
('Ninja',        '$(12) (10) (6) z(4) (1)',          0, 0, (SELECT id FROM buttonset WHERE name="Everything to Gain"));

# FAIRIES (Cool Tuna)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
# Sven's recipe had a slight error on the old site - Little Sven is added to keep the old site recipe
('Little Sven',         'q(20) q(20) (8/12) (6/10) (4)',      0, 0, (SELECT id FROM buttonset WHERE name="Fairies")),
('Sven',                'q(20) q(20) (8/12) (6/12) (4)',      0, 1, (SELECT id FROM buttonset WHERE name="Fairies")),
('Yseulte',             'p(20) q(10) q(8) (X) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="Fairies"));

# FANTASY (Cheapass Games)                 INTRODUCES Socrates(S) Dice; Option; Special Rules for Nightmare
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Luce',        '(8) (10) (20) (4/20) (8/20)',        0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Frasquito',   '(4) (6) (8) (12) (2/20)',            0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Lark',        '(6) (20) (2/8) (4/12) (6/10)',       0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Theophilus',  '(8) (10) (12) (10/20) (20/30)',      0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Mischa',      '(10) (12) (12) (4/12) (6/12)',       0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Chang',       '(2/20) (2/20) (4/20) (8/20)',        0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Aldric',      '(8) (8) (4/12) (8/20) (12/20)',      0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Elihu',       '(4/6) (4/8) (6/10) (6/12) (8/20)',   0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Farrell',     '(10) (12) (6/20) (6/20) (8/12)',     0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Nikolai',     '(20) (4/10) (4/12) (6/10) (6/20)',   0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Cesare',      '(10) (4/10) (6/10) (10/12) (10/20)', 0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
('Vincent',     '(30) (30) (30) (6/30)',              0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
# Socrates may use one of his dice and one of his opponents for a two-dice skill attack
('Socrates',    '(4) (10) (12) (Y)',                  1, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
# When Nightmare loses a round he may change his opponent's variable dice.
('Nightmare',   '(4) (8) (10) (20) (20)',             1, 1, (SELECT id FROM buttonset WHERE name="Fantasy"));
# ('Dead Guy',    '(0) (0) (0) (0) (0)',                0, 0, (SELECT id FROM buttonset WHERE name="Fantasy")),

# FIGHTBALL (Cheapass Games) NOTE: special die selection rules - choose 5 dice out of all available (not implemented)
# ASSUMED ALL TO BE TL
#INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
#('Brie',      '(4) (6) (8) (10) (12) (12) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
#('Domino',    '(4) (4) (8) (8) (8) (10) (12)',         0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
#('Echo(p)',   '(4) (6) (6) (6) (12) (12) (12) (20)',   0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
#('Georgia',   '(6) (6) (8) (8) (12) (20) (20)',        0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
#('Gordo(p)',  '(4) (6) (6) (8) (8) (10) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
#('Mel',       '(4) (4) (8) (10) (10) (20) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
#('Pavel',     '(6) (10) (10) (12) (12) (20) (20)',     0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
#('Rocq',      '(6) (10) (10) (12) (20) (20) (20)',     0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
#('Skinny',    '(4) (4) (4) (6) (8) (8) (10)',          0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
#('Tex',       '(4) (4) (6) (8) (10) (10) (12) (12)',   0, 1, (SELECT id FROM buttonset WHERE name="Fightball"));

# FREAKS (Cheapass Games)              INTRODUCES Queer(q) dice; Poison(p); Shadow(s); Speed(z)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Max',  'p(12) p(12) p(20) p(20) p(30) p(30) p(X) p(X)',  0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
('Mister Peach','(6) z(8,8) ps(12,12) (V,V)!',    0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
('Simon',       'q(4) q(6) q(12) q(20) q(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
('Werner',      '(8) (10) (10) (12) pzs(V)!',     0, 1, (SELECT id FROM buttonset WHERE name="Freaks"));

# GAMING GUARDIANS (XIG Games) Dice Skills unique to this set: Teleport(T), Insult(I), Deception(~), Specialty, Loaded(M), Evil(E)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Dream Wyrm',      'T(8) (20) (20) (20,8) (U)',                  0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
('EDG',             'I(6) I(8) I(10) (20) (X)',                   0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
('Graveyard Greg',  '(6) (8) {I,M,p,s,z,t}(10) {I,M,p,s,z,t}(10) (X)',  0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
('Memento-Mori',    '(6) (10) (10) ~(12) ~(20)',                  0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
('Radical',         'T(4) (20) (12,12) (20,8) (Z)',               0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
('Randarch',        'M(6) M(6) (10) (10) (X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
('Scarlet Jester',  'n(4) E(10) n(12) E(20) E(20)',               0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians")),
('Shane Hensley',   'E(6) E(6) E(6) E(6) E(6) E(6)',              0, 0, (SELECT id FROM buttonset WHERE name="Gaming Guardians"));

# HIGH SCHOOL DRAMA!  (Shifting Skies)     INTRODUCES Sideboard(S) dice
# ASSUMED ALL TO BE TL
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('cheerleader',    '(4) (4) (6) (8) (12) S(10)',      0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
('dumb blonde',    '(6) (6) (8) (10) (12) S(20)',     0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
('meathead',       '(8) (10) (12) (20) (20) S(6)',    0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
('tennis star',    '(4) (6) (10) (12) (20) S(8)',     0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
('1337 h4Xx0r',    '(4) (4) (12) (12) (20) S(6)',     0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
('game master',    '(8) (8) (10) (12) (20) S(20)',    0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
('emo boy',        '(4) (8) (8) (10) (20) S(12)',     0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!")),
('goth chick',     '(6) (6) (6) (10) (20) S(4)',      0, 1, (SELECT id FROM buttonset WHERE name="High School Drama!"));

# The Japanese Beetle! (The Japanese Beetle)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
# The Flying Squirrel cannot make skill attacks
('The Flying Squirrel', 'z(6) z(12) z(12) z(20)',             1, 1, (SELECT id FROM buttonset WHERE name="Japanese Beetle")),
# The Japanese Beetle: Skill attacks do not work on Japanese Beetle
('The Japanese Beetle', '(6) (8) (12) (X)',                   1, 1, (SELECT id FROM buttonset WHERE name="Japanese Beetle"));

# GUARDIANS OF ORDER sets include Sailor Moon 1, Sailor Moon 2, and Tenchi Muyo!
#     INTRODUCES Iconic Abilities which are button specials that only work against other Guardians of Order buttons.
# SAILOR MOON 1 (Guardians of Order)                          INTRODUCES Reserve(r) dice; AND Warrior(`) dice
# ICONIC ABILITIES: SAILOR MOON: TM(1), QUEEN BERYL: CB(2), SAILOR MERCURY: TM(1), ZOYCITE: NV(4), SAILOR MARS: TM(1), JEDITE: NV(4),
#                   SAILOR JUPITER: TM(1), NEPHLITE: NV(3), SAILOR VENUS: TM(1), MALACHITE: NV(2), TUXEDO MASK: TMDF
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Sailor Moon',         '(8) (8) (10) (20) r(6) r(10) r(20) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Queen Beryl',         '(4) (8) (12) (20) r(4) r(12) r(20) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Sailor Mercury',      '(4) (8) (8) (12) r(4) r(4) r(8) r(10)',         1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Zoycite',             '(4) (10) (10) (10) r(6) r(6) r(8) r(8)',        1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Sailor Mars',         '(4) (6) (10) (20) r(6) r(10) r(10) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Jedite',              '(6) (6) (12) (12) r(4) r(6) r(6) r(8)',         1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Sailor Jupiter',      '(6) (10) (12) (20) r(6) r(12) r(12) r(20)',     1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Nephlite',            '(4) (6) (12) (12) r(8) r(10) r(10) r(12)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Sailor Venus',        '(4) (6) (10) (12) r(4) r(8) r(8) r(12)',        1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Malachite',           '(8) (12) (20) (20) r(10) r(12) r(12) r(20)',    1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Tuxedo Mask',         '(6) (6) (10) (20) r(4) r(8) r(10) r(12) r(20)', 1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
('Shadow Warriors',     '(1) (2) `(4) `(6) `(8) `(10) `(12)',            1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1"));

# SAILOR MOON 2 (Guardians of Order)                             SKILLS: Reserve(r)
# ICONIC ABILITIES: LUNA & ARTEMIS: Cat(2), WICKED LADY: DC(2), QUEEN SERENITY: IS(1), RUBEUS: DC(1), PRINCESS SERENA: IS(1),
#  SAPPHIRE: DC(2), RINI: IS(1), WISEMAN: Skull, NEO-QUEEN SERENITY: SM(2), PRINCE DIAMOND: DC(2), KING ENDYMION: KC(1), EMERALD: DC(1)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Luna & Artemis',      '(1) (4) (10) (20) r(2) r(2) r(8) r(8)',         1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Wicked Lady',         '(6) (6) (10) (12) r(4) r(8) r(10) r(20)',       1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Queen Serenity',      '(4) (6) (10) (12) r(6) r(10) r(12) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Rubeus',              '(4) (4) (12) (12) r(6) r(10) r(20) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Princess Serena',     '(6) (8) (12) (20) r(4) r(10) r(12) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Sapphire',            '(6) (10) (12) (12) r(8) r(8) r(10) r(12)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Rini',                '(2) (4) (6) (6) r(4) r(10) r(12) r(12)',        1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Wiseman',             '(20) (20) (20) (20)',                           1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Neo-Queen Serenity',  '(12) (20) (20) r(4) r(6) r(8) r(10) r(12)',     1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Prince Diamond',      '(4) (6) (12) (20) r(8) r(10) r(10) r(20)',      1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('King Endymion',       '(6) (10) (20) (20) r(6) r(10) r(12) r(20)',     1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Emerald',             '(6) (8) (12) (20) r(4) r(6) r(10) r(20)',       1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2"));

# TENCHI MUYO! (Guardians of Order)                              SKILLS Reserve
# ICONIC ABILITIES: TENCHI: Jur(2), KAGATO: Com(1), AYEKA: Jur(1), RYOKO: Com(1), MIHOSHI: Com(1), SOJA'S GUARDIANS: Alt(1),
#                   KIYONE: Com(1), RYO-OHKI: Morph, WASHU: Alt(2), DR. CLAY: Con(2), SASAMI: Jur(3)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Tenchi',              '(4) (10) (12) (20) r(4) r(12) r(20) r(20)',     1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Kagato',              '(4) (6) (20) (20) r(10) r(12) r(12) r(20)',     1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Ayeka',               '(6) (8) (10) (10) r(4) r(4) r(10) r(20)',       1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Ryoko',               '(8) (10) (12) (12) r(4) r(10) r(20) r(20)',     1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Mihoshi',             '(4) (8) (12) (12) r(8) r(10) r(12) r(20)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Soja\'s Guardians',   '(4) (4) (4) (4) r(4) r(10) r(10) r(12)',        1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Kiyone',              '(4) (4) (10) (12) r(6) r(10) r(10) r(20)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Ryo-Ohki',            '(4) (4) (4) (12) r(10) r(12) r(20) r(30)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Washu',               '(4) (6) (12) (X) r(6) r(8) r(10) r(20)',        1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Dr. Clay',            '(6) (10) (10) (10) r(4) r(4) r(12) r(12)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
('Sasami',              '(4) (4) (6) (8) r(12) r(12) r(20) r(20)',       1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!")),
# Zero works just like Echo - it doesn't have it's own recipe, but instead copies its opponent's
('Zero',                '',                                              1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyo!"));

# HOWLING WOLF (Howling Wolf Studios)              INTRODUCES Stealth(d) Dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Howling Wolf',        'd(4) (8) (12) (20) d(20)',           0, 1, (SELECT id FROM buttonset WHERE name="Howling Wolf")),
('White Tiger',         '(6) d(6) (10) (12) d(12)',           0, 1, (SELECT id FROM buttonset WHERE name="Howling Wolf"));

# LAS VEGAS                                INTRODUCES Wildcard(C) AND Pai Gow(:); Option; Twin; Turbo
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Frankie',       '(2,3) (3,4) (4,5) (10) (T)!',      0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('Lola',          '(6) (6) (8) (T) (U)',              0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('Sly',           '(12) (12) (20) (20) (U)',          0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('WildCard',      '(C) (C) (C) (C) (C)',              0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# Black Jack's T swing cannot be a d12.
('Black Jack',    '(1,1) (11) (8,8) (10,11) (T)',     1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
#CRAPS: Any twin die that rolls a 7 may be reset by the player to any value between 2 and 12 (includes after a Trip attack.)
#At the start of a round, this decision must be made before seeing the opponent's starting roll.
('Craps',         '(6,6) (6,6) (6,6) (6,6) (6,6)',    1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# SHAMROCK: The d13s score as normal d13s, but are represented by a d12 for which a 7 counts as a 13
('Shamrock',      '(2) (7/13) (7/13) (7/13) (7/13)',  1,  0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('PaiGow',        '(6) :(8) :(8) (10) (12)',          0,  0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# The Magican can use one of the following once per round, and cannot use any of them twice until he has used all four:
# * Rabbit from hat: Extra d1.
# * Prestidigitation: Change any die to a Trip die.
# * Bisect with Saw: Change any die (own or opponent's) to half of its originally-rolled value on the opening roll.
# * Vanishing Act: First die captured by opponent is scored as zero points.
# * Mind Reading: Opponent must state all sizes of all option and/or swing dice.
('Magician',      '(6) (8) (10) (12) (T)',            1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas"));

# LEGEND OF THE FIVE RINGS (Wizards of the Coast)  INTRODUCES Focus(f) dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Crab',        '(8) (10) (12) f(20) f(20)',     0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Crane',       '(4) f(6) f(8) (10) (12)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Dragon',      '(4) (8) f(8) f(12) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Lion',        '(4) f(6) (10) f(20) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Mantis',      '(6) f(8) f(10) (20) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Naga',        'f(6) (8) (8) (8) f(20)',        0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Phoenix',     '(4) (6) f(8) (10) f(20)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Ronin',       '(6) f(6) (8) f(12) (12)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Scorpion',    '(4) (4) f(4) f(12) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Unicorn',     '(4) (4) f(6) f(12) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Mirumoto',    'f(6) (10) f(10) (12) (20)',     0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
('Kakita',      '(6) f(6) (10) f(12) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings"));

# LUNCH MONEY (Atlas Games)           INTRODUCES Trip(t) dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Charity',     't(4) (4) (8) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Prudence',    '(1) t(4) (6) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Hope',        't(1) (2) t(4) (6) (Y)',         0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Chastity',    't(6) (6) (10) (10) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Faith',       't(2) (6) (10) (12) (Y)',        0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Temperance',  't(2) (8) (12) (20) (Y)',        0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Patience',    '(2) (2) (8) (12) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money"));

# MAJESTY & MAJESTY - NORTHERN EXPANSION (Cyberlore)                     NO SPECIAL SKILLS
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Dirgo',       '(20) (20) (20) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
('Flaire',      '(6) (10) (10) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
('Nerni',       '(4) (4) (12) (X) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
('Yeti',        '(10) (20) (30) (30) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Majesty"));

# METAMORPHERS (IMGames)          INTRODUCES Morphing(m) dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Daisy',     'm(6) (10) (10) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Jingjing',  'm(4) (8) (10) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Mau',       '(6) (6) (8) (12) m(X)',       0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Spencer',   '(8) (8) (12) m(20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Talus',     '(4) m(12) (20) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers"));

# NODWICK (4th Blade)                     INTRODUCES Armor(A) dice
# ASSUMED ALL TO BE NON-TL
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Artax',            'A(4) (8) (8) (12) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Nodwick")),
('Count Repugsive',  'A(4) A(4) (10) (10) s(12)',    0, 0, (SELECT id FROM buttonset WHERE name="Nodwick")),
('Nodwick',          '(4) (4) (10) (10) A(W)',       0, 0, (SELECT id FROM buttonset WHERE name="Nodwick")),
('Piffany',          'A(6) A(6) (6) (8) (W)',        0, 0, (SELECT id FROM buttonset WHERE name="Nodwick")),
('Yeagar',           'A(6) (10) (20) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Nodwick"));

# POLYCON (Polycon)                   INTRODUCES Fire(F)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Poly',        '(4) (6) F(8) (20) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Polycon")),
('Adam Spam',   'F(4) F(6) (6) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Polycon"));

# PRESIDENTIAL BUTTONMEN                              SKILLS: Option; Poison; Shadow
# Cthulhu from Cthulhu (Chaosium)
# Gore & Bush from GenCon 2000 (Cheapass Games)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Bush',                '(4) (20) (4/8) (6/12) (6/20)',       0, 1, (SELECT id FROM buttonset WHERE name="Presidential")),
('Gore',                '(4) (20) (4/8) (6/12) (6/20)',       0, 1, (SELECT id FROM buttonset WHERE name="Presidential")),
('Cthulhu',             '(4) (20) s(4,8) s(6,12) ps(6,20)',   0, 1, (SELECT id FROM buttonset WHERE name="Presidential"));

# RENAISSANCE (Stone Press)                 SKILLS: Mood swing(?); Ornery(o); Poison(p); Speed(z); Option; Unique(u)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Dr. Speculo',    '(6) (8) (12) o(Y) o(Y)',       0, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
# Guillermo: X and Y cannot be the same size
('Guillermo',      '(6) (10) (20) (X) (Y)',        1, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
('Harry Pudding',  '(4) (7) (2/20) (2/20) (10)',   0, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
('Lascivia',       '(4) zp(12) (20) p(X)',         0, 0, (SELECT id FROM buttonset WHERE name="Renaissance")),
('MothMan',        '(8) (8) (12) (16) (Y)?',       0, 1, (SELECT id FROM buttonset WHERE name="Renaissance"));

# SAMURAI (Cheapass Games)               SKILLS: Focus(f)
# Tsusuko from GenCon 2001 (Cheapass Games)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Honzo',       '(10) (12) f(20) (V) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Konami',      '(6) (8) f(10) f(10) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Okaru',       '(4) f(4) (6) (12) (V)',         0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Sadakuro',    'f(4) f(6) f(8) f(10) (12)',     0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Tonase',      '(4) (4) (8) (20) f(X)',         0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Yuranosuke',  '(4) (8) (12) f(12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Tsusuko',     'f(6) (10) (10) (16) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Samurai"));

# SANCTUM (Digital Addiction)         NO SPECIAL SKILLS
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Dracha',      '(4) (10) (20) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
('Ogi',         '(2) (4) (10) (X) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
('Fingle',      '(6) (7) (8) (12) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
('Ngozi',       '(4) (6) (8) (10) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Sanctum"));

# SAVE THE OGRES (Save the Ogres)                      SKILLS Shadow; Twin
# Ginzu & Gratch from GenCon 2000
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Ginzu',               '(8) (8) s(12,12) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Save The Ogres")),
('Gratch',              '(6) s(8,8) (20) s(20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Save The Ogres"));

# SFR (SFR)                                   SKILLS: Option           (2001 Rare / Promo on old site)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Assailer',       '(12) (12) (20) (2/20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="SFR")),
('Harbinger',      '(4) (4) (4/12) (10/20) (V)',   0, 1, (SELECT id FROM buttonset WHERE name="SFR"));

# SLUGGY FREELANCE (Sluggy)               ITNRODUCES: Option (/) dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Aylee',       '(8) (10/20) (12) (12/20) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Bun-Bun',     '(4/10) (4/12) (6/12) (20) (20)',     0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Kiki',        '(3/20) (4) (8/12) (10) (10/20)',     0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Riff',        '(4/20) (6) (6/8) (10/12) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Torg',        '(6) (6/20) (8) (10/12) (12/20)',     0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Zoë',         '(4/12) (6/10) (8) (10/20) (12/20)',  0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance"));

# SOLDIERS (Cheapass Games) NOTE: original Buttonmen set, no special die skills
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Avis',        '(4) (4) (10) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Hammer',      '(6) (12) (20) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Bauer',       '(8) (10) (12) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Stark',       '(4) (6) (8) (X) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Clare',       '(6) (8) (8) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Kith',        '(6) (8) (12) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Karl',        '(4) (6) (6) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Iago',        '(20) (20) (20) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Niles',       '(6) (10) (10) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Shore',       '(4) (4) (20) (20) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Hannah',      '(8) (10) (10) (10) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Kublai',      '(4) (8) (12) (20) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Soldiers")),
('Changeling',  '(X) (X) (X) (X) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Soldiers"));

# SPACE GIRLZ (buttonmen.com)   INTRODUCES Plasma{} dice; Mighty(H); Ornery(o); Poison(p); Shadow(s); Weak(h); twin
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Maya',       'o(6) (12) p{hs,H}(12) (20) (S)',           0, 0, (SELECT id FROM buttonset WHERE name="Space Girlz")),
('Zeno',       '{h,H}(6) {h,H}(8) {h,H}(4,4) (20) (X)',    0, 0, (SELECT id FROM buttonset WHERE name="Space Girlz"));

# STUDIO FOGLIO (Studio Foglio)       INTRODUCES Twin dice(,); Poison(p)
# What's New
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Phil',             '(8) (8) (10,10) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Dixie',            '(4) (6) (10) (12,12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Growf',            '(4,4) (6) (8) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# Buck Godot    NOTE: Winslow from this set is a die, not a button.
('Buck Godot',       '(6,6) (10) (12) (20) (W,W)',    0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# Girl Genius
# Jorgi from GenCon 2001 (Cheapass Games)
('Agatha',           '(4) (6) (8,8) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Krosp',            '(4) (6,6) (12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Klaus',            '(4) p(10,10) (20) (20) (W)',    0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Von Pinn',         '(4) p(6,6) (10) (20) (W)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Bang',             'p(4,4) (6) (12) (12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Gil',              '(8) (8) p(8,8) (20) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Jorgi',            '(4) (6) (8) (20) p(X)',         0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('The James Beast',  '(4) (8,8) (10,10) (12) (W,W)',  0, 0, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# XXXenophile
('Brigid',           '(8) (8) (X) (X) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('O-Lass',           '(6) (12) (X) (X) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio"));

# SYDCON 10 (SydCon)               INTRODUCES Rage(G)    (2001 Rare / Promo on old site)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Gripen',     '(4) (6) (8) G(12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="SydCon 10"));

# UNEXPLODED COW (Cheapass Games)   INTRODUCES Boom(b) Dice AND Mad Swing(&) Dice
# ASSUMED ALL TO BE TL
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('George',     '(4) (6) b(6) b(20) (&)',       0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
('Violette',   '(8) (8)  b(10) b(12) (&)',     0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
('Elsie',      '(4) b(4) (10) b(12) (&)',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
('Kasper',     '(6) b(8) (12) b(20) (&)',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
('Montague',   'b(4) b(10) (12) (20) (&)',     0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
('Neville',    ' b(4) (8) b(8) (20) (&)',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
('Thaddeus',   '(10) (14) (14) (18) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow")),
('Buckley',    '(4) (4) (18) (X) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Unexploded Cow"));

# WONDERLAND                                        SKILLS Null; Option; Poison; Queer; Stinger; Turbo; Twin
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Alice',             '(6) (8) (8) (10) (1/30)!',          0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('Mad Hatter',        'q(6) q(6) q(10) q(20) q(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('Queen Of Hearts',   '(6) (8) p(16) (20) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('The Jabberwock',    '(20) (20) (30) ng(30) (U)',         0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('Tweedledum+dee',    '(2,2) (4,4) (6,6) (10,10) (T,T)',   0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('White Rabbit',      '(4) (6) (8) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Wonderland"));

# VAMPYRES (Cheapass Games)            INTRODUCES Shadow(s) dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Angel',       's(4) (6) s(12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Buddy',       's(6) (10) s(20) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Dunkirk',     '(6) (6) (10) (20) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Starchylde',  's(6) (8) s(10) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('McGinty',     '(4) s(10) (12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Tiffany',     '(4) s(8) (8) (10) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Vampyres"));

# YOYODYNE (Fuzzface)                   INTRODUCES Chance(c) dice
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('FuzzFace',      '(4) (8) (10) c(10) c(12)',    0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
('John Kovalic',  '(6) c(6) (10) (12) c(20)',    0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
('Pikathulhu',    '(6) c(6) (10) (12) c(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
('Ulthar',        '(4) (8) (10) c(10) c(T)',     0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne"));

#########
# I haven't found any info regarding the rest of these sets, and suspect many to fan / vanity sets

# GEEKZ                                                        SKILLS Poison; Shadow; Reserve; Twin
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Caine',         'ps(4) ps(4) s(20) s(20) s(X)',                     0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
('Cammy Neko',    '(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)',    0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
('Sailor Man',    '(4,4) (8) (20) (12,12) r(10,10) r(6,6) r(8)',      0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
('Xylene',        's(6) s(8) s(10) s(X) s(Y)',                        0, 1, (SELECT id FROM buttonset WHERE name="Geekz"));

# IRON CHEF                                                   SKILLS Option
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Chairman Kaga',         '(5/7) (7/9) (9/11) (11/17) (17/23)',      0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
('Iron Chef Chinese',     '(7) (9) (11) (11) (13/29)',               0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
('Iron Chef French',      '(5/27) (7) (13) (17) (23)',               0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
('Iron Chef Italian',     '(3) (5) (9) (9) (11/21)',                 0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
('Iron Chef Japanese',    '(9/17) (17) (17) (21) (29)',              0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef"));

# BUTTONMEN.DHS.ORG: the following sets make use of die types designed specifically for the old buttonmen online
# 7 DEADLY SINS                                  SKILLS Doppleganger; Mighty; Morphing; Option; Posion; Queer; Rage; Speed; Turbo
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Anger',        '(10) (10) (10) (12/20) (20/30)!',     0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Envy',         'D(4) D(6) D(10) D(12) D(X)',          0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Gluttony',     'H(1,2) Hp(1,3) H(1,4) m(1,5) (W)',    0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Greed',        '(8) (12) (12) z(12) z(4/20)',         0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Lust',         '(6) (6) m(12) D(20) q(X)',            0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Pride',        '(8) (10) G(12) G(20) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Sloth',        '(4,4) (6,6) (8,8) (10,10) (V,V)',     0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins"));

# CHICAGO CREW                                   SKILLS Berserk; Mighty; Option; Ornery; Shadow; Speed; Trip
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Audrey',        't(2) (3/6) o(8) (12) (Y)',           0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Cheathem',      'Ho(1) s(6) o(10) o(14) s(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Flint',         'o(4,4) (12) o(16) (20) o(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Lizzie',        't(6) s(6) B(10) o(12) o(X)',         0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Monty Brown',   't(1) z(6) o(10) (20) (Y)',           0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Octavia',       'st(4) s(4) so(10) s(10) o(X)',       0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Spooky',        'ot(8) o(6) o(10) B(15) o(Z)?',       0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew"));

# FOUR HORSEMAN                                  SKILLS Berserk; Mighty; Poison; Shadow; Speed; Twin; Weak
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Death',         '(2,2) p(8) s(10) (16) (X)?',         0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
('Famine',        '(6) (8) (10) (12,12) h(X)',          0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
('Pestilence',    '(4) pH(6) (12) (20) (S)',            0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
('War',           'z(4) (6) z(10) B(20) (W,W)!',        0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen"));

# FREE RADICALS                                SKILLS Doppleganger; Ornery
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('CynCyn',       '(6) D(10) (20) D(X) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Loren',        '(4) (6) Do(6) D(20) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Lorrie',       'D(6) (12) (20) (20) D(X)',          0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Maskin',       'D(4) (4) (8) D(16) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Randy',        '(4) D(6) D(8) (30) (S)',            0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Tony',         'D(6) (8) (10) (12) D(X)',           0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals"));

# HODGE PODGE             SKILLS Chance; Chaotic; Focus; Mood Swing; Option; Ornery; Reserve; Slow; Twin
#       SKILLS Berserk; Doppleganger; Fire; Jolt; Konstant; Maximum; Mighty; Rage; Shadow; Speed; Stealth; Time and Space; Trip
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('BabyBM',              '(7) o(8,8) (11) (21) HG(V)',                0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Bull',                '(6) (8) (12) (X) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Butthead',            'B(U) t(T) H(3) @d(5)',                      0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Button of Loathing',  'Jk(13) (6) (6) (20) (R,R)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Craptacualr',         'c(6) c(8) (12) (20) (T,T)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Crosswords',          'c(R) o(S) s(W) o(R) d(S)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Da Pimp',         't(4) (8) (12) (20) wHor(4) wHor(4) whor(20) whor(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Darth Maul',          '(8) f(10) (12) f(20) (30)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
# late edition to Hodge Podge: Eeyore designed by Elliot Evens, found on boardgamesgeek list
('Eeyore',              '(6) (10) (10) (12) (X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Evil Robot Luke',     '(4/20) (6/12) p(10) (12) (12/20)',          0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Ferretboy',           '(6) (6) @(20) @(20) @o(X)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Holiday',             '(2/14) (3/17) (7/4) (10/31) (12/25)',       0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Horde O Ninjas',      'd(6) d(6) d(10) d(12) d(20) d(20) dr(6) dr(8)',     0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Jose',                'M(3) M(4) M(6) M(8) M(T)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Loki',                'Ho(2,2) Ho(2,2) Ho(2,2) Ho(2,2) (T)',       0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Marilyn Monroe',      'D(36) (24) (36)',                           0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Miser',               'v(20) v(20) v(20) v(20) v(20)',             0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Qui-Gon Jinn',        'f(4) (6) f(8) (10) f(12)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Skomp',               'wm(1) wm(2) wm(4) m(8) m(10)',              0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('The Tick',            'H(1,10) H(12) H(20) H(20)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Thor',                'G(6) G(10) G(12) G(6/20)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Tilili',              'oz(4) @(10,10) ^(12) z(12) (Y)!',           0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Trogdor',             'sGF(20) sGF(20) sGF(20) sGF(20)',           0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Tyler Durden',        'o(2,2) o(6) s(20) B(20) o(X)?',             0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge"));

# VICTORIAN HORROR       SKILLS Option; Reserve; Mood Swing; Ornery; Plazma; Null; Value;
#   SKILLS Focus; Konstant; Poison; Shadow; Speed; Stealth; Stinger; Time and Space; Trip; Weak
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Count Dracula',            'sf(4) (8) s(10) (16) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
('Dr. Jekyll',       '(X)? (X)? r^(20) rvkt(20) rsfp(20) rhop(20)',   0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
('Frankenstein\'s Monster',  '(10/16) (16/20) o(20/30) {G,B}(30)',    0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
('Invisible Man',            'n(4) d(6) d(10) ng(10) d(20)',          0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
('Wolfman',                  '(6) p(10) (12) z(16) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror"));

################################################
#####   C L A S S I C    F A N A T I C S   #####
################################################
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('ABCGi',           '%Ho(1) pdw(7) mhv(13) zkt(23) (X,X)!',       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('albertel',        'n^(20) t(20) z(20) q(S)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Alljazzedup80',   'z(8) B(8) p(10) %(12) (10/20)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('amica',           't(4) p(6) o(8) (7,7) o(Y)?',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Anders',          '(8) (8) (8) (8) (8) o(24)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('antherem',        'fH(4) opH^(8) f(10) @(12) (U) r(10) r(12)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('anthony',       'v?(X,X) vst(16) stomp(10) {zf,Bp}(V) fF(15)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Anti-Llama',      '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Anvil',           's(4) pz(10) pz(12) zs(30) spz(V)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Aysez',           's(U) z(U) %(U) n(U) g(U) t(U)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('barswanian',      'n(4) mg(6) mg(6) %(20) (X)! r(13,13)',       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('bigevildan',  'hdp(6) hdp(8) hdp(8) hdp(10) hdp(12) hdp(16) hdp(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Binder',          '(4/12) (17) sp(4,20) (4/30)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('birdman',         'z(3) (4/30) (13) (13) (13)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('BlackOmega',      'tm(6) f(8) g(10) z(10) sF(20)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('bluebec',         't(7) z(13) (R)? g(4/16) o(6,15) +(Y)',       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('bobby 5150',  '^(3) fsp(R) ftz(14) tz!(1/30) r^(4) rz(12) r!(1/30)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Bodie',           '(4) ^(5) ^(5) z(9,9)',                       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('bonefish',        'z(10) z(12) z(20) z(30)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Boot2daHead',     'd(1) Gz(Z) !p(V) !n(Y,Y) oF(C)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('bowler190',       'z(4) z(4) z(20) z(20) z(20)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('braincraft',      'z(6) z(6) z(10) f(20) f(20)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('BrashTech',       '(4) f(6) p^(6) pz(12) (20)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('BugRoger',        '(4) (4) zg(18) zg(18) (10,10)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('CactusJack',      'z(8/12) (4/16) s(6/10) z(X) s(U)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Caligari',        'f(4) fd(6) zn(12) m(12) ^q(S)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Calmon',          '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Cassandra',       'D(10) D(20) oH(T) @(Z)? (V,V)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('CestWhat',        'p(2) sf(12) sz(20) pho(8,8) t(T,T)!',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('chase',           '(4) (6) p%(10) (12) (X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Conspyre',        '(1) t(2) t(8) z(12) t(X)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Coyote',          'f(1/4) (4/20) z(10,10) ^(U)? s(X)!',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Cristofore',      '^(9) f(12) g(12) z(20) (12/30)! rf(12)',     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Darrin',          '(6) (6) z(12) Fq(R) Fq(R)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Darthcliff',      'z(8,8) f(16)! sg(12) tzH(4) B(X)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('dexx',            'k(7) p?(X) o!(Z) G(3,17) t(5) g`(2)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Discordia',       'df(12) df(12) Gp(16) Gp(16) (Z)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('dmm',             '(4) (6) z(C) (10,10) f(X)!',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('DocBlue',         'd(4) s(20) ^(4) (20) Ho(4)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Downen',          'f(2) (6) p(13) z(14) z(X)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Durak666',        'swz(30) swz(30) swz(30) swz(30)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('dwelsh',          '(3) (13) (37) H@(X) o(R)!',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('echopapa',        '(C) z^(20) @o(10) n(8) D(X)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('ElihuRoot',       '(3) h(10) h(10) n(20) n(30)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('eon',             't(4) n(6) d(8) B(16) B(X)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Famous',          't(6) B(6) f(6) n(6) (10)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Fanaka66',        'm(2) Ho(6) c(12) z(16) (6,6)',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('fendrin',         'f(3) nD(R) (1) n(2) Bp(U)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('fernworthy',      '(1) (2) z(12) z(20) `(4) `(6) `(8) `(10)',   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Finngall',        'f(5) f(5) z(7,7) (23) B(X)?',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('flesh99',         '^(C) ^(C) zg(Z) p!(Y) z(W) gd(W)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('fnord',           'cz(10) ^(16)! t(X)? @o(V)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('fog',             'o@(S)? o@(T)? o@(R)? o@(U)? o@(T)?',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Foxlady',         'fo(X) t(4) z(6,6) m(12) (Y)?',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('fxdirect',        't(1) (6) zp(12) s(12) p(X,X)!',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('gbrume',          '{^,t}(12) B(30) (30) (V)?',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('ghost walker',    'B(20) h(20) %(20) hp(20) hs(20)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('gman97216',       'Hog%(4) Hog%(4) Hog%(4) Hog%(4)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('GoldenOtter',     '^(2) v(6) vz(12) vz(X) v(W,W)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('GorgorBey',       'ft(5) ds(1/15) `G(5/10) !p(Y) wHz(12)',      0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('grayhawk',        'H(6) f(8) s(12) B(Y) g(Z)!',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('GreatWolf',    '^(3) B(10) s(Y) rws(20) rt(8) rG(X) rGp(Y,Y)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('grend',           '%(4) sp(8) z(8,8)? B(16) `(4) +z(8)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('GripTiger',       '^(10) s(11) d(12) z(13) (X)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Grivan',          'GHo(6,6) (3) f(30) %Ho(2) r!(Y)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Grym',            '(4) t(6) d(10) z(12) (X,X)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Gryphon', '{g,sF}(10) {f,z}(12) {f,z}(12) (X)! +`(R)! ro@(Z)? rz(V,V) r{m,D}(8) gr(Y){p,h,o,n}', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('gslamm',          'Hn(6) Hn(6) hn(20) p(Y)? p(Z)?',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('hairlesswonder',  '(4) (7) B(20) B(20) B(20)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('hansolav',        'p^(8) z(4,6) q(12) z(Y) (X)!',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('haruspex',        '(99)',                                       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Heath',           '(1/13)! z(3,13) H(13) md(13) (X)!',          0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Heather',  'd(1) D(2) ^c(4) tz(14) rz(2) rz(4) rz(10) rz(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Hooloovoo',     'q(T) q(W) q(X) q(Z) rn(R) rz(S) rp(U) rf(V)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Hrodgar',         '(Z)? (T)? (X)? (X)? (X)?',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('icarus',          '^(3) szf(12) szf(20) t(Y,Y) G(Z)!',          0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('ifni',            'oHz(Y) oHz(1/2) oHz(2/4) oHz(20/30)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('IIconfused', 'fn(X) fn(X) gn(X) gn(X) `H(Y,Y) `d(Y)! `mp(Y) `^(1,Y)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('inundator', '{c,d}(12) {f,z}(12) {t,g}(12) {p,n}(12) {m,?}(X)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('itachi',          'fnz(4) (8) (12) fnz(20) (X)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Jasyeman',        'B(8) H(8) p(8) z(8) z(U)?',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('jeffboyardy',     'o(10) (6/12) o(X)? (Y)? Bo(Z)?',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Jennie',          'H(6) @(8) @(10) z(12) (U)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Jenniegirl',      'Gst(S) Gst(S)? Gst(S)^ cor(V) cor@(X)!',     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('jgenzano',        '(4) (8) z(16) z(X) p(X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Jordan',          'oH(10) (1/20)! (1/20)! (20) o^(11)',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Jota',            'F(4/8) (6) (6/10) s(8) (30) +(12)',          0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('jrbrown78',       'z(8) z(12) z(15) z(X) ^(X)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('juelki',          '(6) (10) (12) p(X)? (X,X)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('kaddar', 'vpzf(20) {p,v,ht}(R) d(4/8) nF(4/16) z(8,8) `(2,1)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Kaeriol',         '(4) (6) d(6) s(10) B(X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('KaijuGamer',      'Hz(4) Hz(6) Hz(8) Hz(10) Hz(20)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('kaufman',         'k(6) g(8) (10) z(10) (4,10)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('kestrel', 'df(3) %q(7,7) zs(17) ^t(3/23) p(Y)! rG(S) rg(U) r(V)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('kleric',          'Hwz(6) dc(6) m(7) oh(10) (X)?',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Kurosuke',        'o(12,13) z(20,12) q(31) of(4/22) oz(22)',    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('lackey',          'd(4) d(6) d(8) `(4) `(6) `(8)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('LadyJ',           'dG(17) Ho(W)? q(X) ^B(T,T) (5)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Limax',           '(W) om(34) (T)',                             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Linnea',          '(4) B(8/17) G(8) o(12) (U)?',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('llippen5',        'd(4) d(6) d(8) d(10) d(12) d(20)',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('luke ii',         'd(6) @(8) hz(12) c(20) (C)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('lunatic',         'p(3) d(6) t(8/12) (40) (40)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('maga',            'fz(X)? f(10) zqG(20) s(10) (8)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('malarson',        'oH(4) (5,5) z(20) %(20) (U)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('mgatten',         '(Y) (Y) ^(Y)! ^gst(Z,Z)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('mlvanbie',        'H(3) d(1) H(4) H(1) H(5)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('mneme',           'dg(20) dgH(4,4) fn(30) tB(Y) tB(Y)',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('moekon',          's(3) (7) (7) n(6,6) p(17)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Moodster',        's(X) p(X) (X)? z(X) o(X)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('moussambani',     'n(2) f(4) (5,6) (20) wh(Y) (C)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('MrWhite',  'm(4) @(8) @(14) GF(9,9) rc(4) rz(8) r(14) rh(18)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Mushu',           'm(U) sH(U) po(R) k(10)',                     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('myxozoa',         '(3) z(11) (21) c(Y) z(Z)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('nelde',           'td(Y)! td(Y)! td(Y)! td(Y)! td(Y)!',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('NeoVid',          'fs(6) pz(12) gd(10) {m,D}(T) `(Z)! `(R)!',   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Nihlathak',       'wd(4) wd(4) (6) z(17) c(20)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Noeh',            'z(15,15) tg(6) n(Y) f(4/20) sgv(17) `(1)',   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('NoopMan',         '%t(1,3) D(4) ns(20) z(X) c(Z)!',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Notorious', 'G(2/12)! g(4/16)! G(8/20)! g(10/24)! rH(7) rH(11) rh(13) rh(17)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Oaktree',         'o@(11) (T) (R) (3) (3)',                     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('opedog',          'z(20) z(16) (6) p(X)',                       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Pain',            't(4) (8) sz(16) (20) (V)? p(X)!',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('perlmunkee',      't(6) p(12) ^(12) z(20) z(20)',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('pgolo',           '(2) D(4) ^z(14) f(X)? rz(6) rz(10)',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Pjack',           'q(2) z(3) (5) s(23) t(T,T)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Polonius',        '(4) (4) s(10) z(12) (X)?',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('professorbooty',  'Ho(6) ^z(20) (V) fp(X)',                     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('randomlife',      '@(6) @(10) @(12) @(20) @(X)',                0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Raupe',           'm(4) GF(8) z(10) sf(20) (20)',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('RavenBlack', 'fs(10) zs(8) d(4) h(10) r(1) r(2) r(4) r(8) r(16)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('relsqui',         '^(1) (R) (31) (S) q(U)!',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('roujin27',        'p(6) n(8) p(8) n(12) n(20)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Rumbles',         'o(R) o(T) of(10) om(10)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Sabathia',        'f(4) ^(5) H(6) m(7) z(22)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('SailorMur',       'g(10) sp(12) t(4) (10/20) ^(X)!',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('sanny',           't(2) (4) z(4) p(X) s(X)',                    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('santiago',        'D(2) p(4/14) t(5,5) zs(18) (6/16)!',         0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('SC(The Deuce)',   'z(4) t(6) p(8) s(10) (12/16)! +(20)',        0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('schwa',           'f(V) t(W) d(X) v(Y) B(Z)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('seeker',          'oH(2) o@(6) o@(8) o@(12) o@(Y)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Shadowkeeper',    's(4) s(4) s(4) s(4) s(4) s(U)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('skapheles', 'df^(6) z(20) zp(12) spg(X) df(8) rz(12) rg(10) r(12) r(20)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Skeeve',          'o(V)? o(W)? o(X)? o(Y)? o(Z)?',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('slamkrypare',     't(1) (10) (10) z(12) (Y)',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('smallfrogge',     'H(4) m(8) B(12) (X)? (X)!',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Snuff',           'f(4) f(4) ^p(Y)! gns(Z,Z) gs(Z,Z)',          0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('spindisc',        'd(2) p(6/20)! zp(12) n(T,T) B(X)? +(Y)',     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Squiddhartha',    'f(4) f(6) g(10) pz(X)! (Z) r(4,4) rz(6,6)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Stick',           'p(3) (6,6) d(8) tzn(16) (S)!',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('stoooooooo',      'c(8) H(10) n(4) o(2)',                       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('SyberCat',        '(6) {g,t}(6) (10) {f,z}(12) (V)? +d(12)',    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Tasslehoff B', 't(6) (4/20)! {fz,gz}(12) svG(20) (T) `(1) `(2) rp`(4) rp`(6)', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('TheFool',         'v(5) v(10) vq(10) vs(15) s(R,R)?',           0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('TheMachine',      'dp(1) B(1/30)! tD(4) q(7,11) rcz(20)',       0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Torch',           'p(6) p(8) z(10) z(10) zsp(V)!',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Totoro',          'fsvG(13) (20) (20) (20) gvz(30)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('trifecta',        'm(3) m(3) m(3) m(3) p(3/33)! rn(3) rD(3)',   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Trouble',         'd(6) ^(7) @(12) B(20) @o(Z)?',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Tweedledum',      't(4,4) @(8) ^(12) z(20) fp!(Y)',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('TwistedRich',     'z(1,1) z(4) z^(4) z(6) (X)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('TYFTFB',          'H(2) d(8) z(8) tp(10) G(R)',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('UncleMilo',       '(6) p(8) (12) %(16) B(20)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Urbanmyth',       'z(8) g(10) z(10) (15) (X)?',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('vhoodoo',         '(5) ^s(9) (C) B(17) (Z)?',                   0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Vysion',          'o(9) p(9) s(9) z(9) (9,9) r^(9)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('weirdbal',        '@(V)? @(V)? @(V)? @(X)? @(X)?',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('wembley-fraggle', 'spf(10) spf(10) (16) (S) (S)',               0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Weylan',          '(4) ^(3) f(6) m(8) sg(12) (R)',              0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('wranklepig',      'pB(17) Fo(13) q(11) gc(7) nt(5)',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('wtrollkin',       'p(4) pG%(7) s(15) sB(S) s%(S)! worm(Y)',     0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Yagharek',        'p(3) p(5) (7) (11) cz(13)',                  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('yakboy',          'o@(5) ms(6) p%(W) hot(20) (X)?',             0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('zaph',            '(4) z(4,4) s(8,8) p(16) (1/24)!',            0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Zegota',    'z(8) z(12) z(20) z(30) p(1) rz(10) rz(20) rp(1)',  0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Zomulgustar', 't(4) p(5/23)! t(9) t(13) rdD(1) rsz(1) r^(1,1) rBqn(Z)?', 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Zophiel',         'k(1) (6) z(8,8) H(12) (Y)?',                 0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics")),
('Zotmeister',      'd(1) d(1) d(2,2) d(8,8) rd(1) rd(26,26)',    0, 0, (SELECT id FROM buttonset WHERE name="Classic Fanatics"));
