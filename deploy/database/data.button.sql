DELETE FROM buttonset;
INSERT INTO buttonset (name) VALUES
('Soldiers'),
('The Big Cheese'),
('Sanctum'),
('Lunch Money'),
('1999 Rare-Promo'),
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
('Tenchi Muyou!'),
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
('SFR'),
('Space Girlz'),
('Bridge and Tunnel'),
('2005 Rare Promo'),
#
('Geekz'),
('Iron Chef'),
('7 deadly sins'),
('Chicago Crew'),
('Four Horsemen'),
('Free Radicals'),
('Hodge Podge'),
('Victorian Horror');

DELETE FROM button;
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
# 1999 RARE-PROMO                       INTRODUCES Turbo(!) Swing Dice
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
# ('Carson(p)',      '(4) (5) (6) (7) (X)',        ?, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
# 2000 RARE-PROMO
# Gordo from ButtonMen Poster 1999 (Cheapass Games)
('Gordo',               'u(V) u(W) u(X) u(Y) u(Z)',           0, 0, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo")),
# Me am Ork! from Orc! (Green Ronin)
('Me am ORK',           '(8) (8) (8) p(8) (X)',               0, 1, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo")),
# Rikachu Origins 2000 (Origins)
('Rikachu',             '(1) (1) (1) (1) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="2000 Rare / Promo")),
# 2000 SHORECON (ShoreCon)            NO SPECIAL DIE SKILLS
('ConMan',     '(4) (6) p(20)',      0,  1, (SELECT id FROM buttonset WHERE name="2000 ShoreCon")),
# 2002 ANIME EXPO                                NO SPECIAL DICE SKILLS
('MAX(p)',          '(4) (6) (18) (20 (X)',           0, 1, (SELECT id FROM buttonset WHERE name="2002 Anime Expo")),
# 2002 Origins (Cheapass Games)                       SKILLS: Stinger(g) on old site)
('Apples',       '(8) (8) (2/12) (8/16) (20/24)',       0, 1, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave")),
('Green Apple',  '(8) (10) (1/8) (6/12) (12/20)',       0, 1, (SELECT id FROM buttonset WHERE name="Chicagoland Gamers Conclave")),
# 2004 ORIGINS (Flying Buffalo) 
#    INTRODUCES Sleep(Z) dice AND Game(#) dice; Fire(F); Poison(p); Shadow(s); Slow(w); Speed(z); Value(v); Option
('Amara Wintersword',     '(4) (8) (12) (12) (X)?',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Beatnik Turtle',        'wHF(4) (8) (10) vz(20) vz(20)',                   0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Captain Bingo',         '(8 6/12 6/12 12/20 X',                            0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Oni',                   '(4) (10) f(12) f(12) (V)',                        0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Spite',                 'p(6) (6) (6) s(X) (X)',                           0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Super Germ',            'p(10) p(12) p(16) p(20) p(24) p(30) p(30) p(X)',  0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Cheese Weasel',         '(6) (8) (12) (16) (20)',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# ('Killer Christmas Tree', '(6) Z(6) (10) Z(12) (X)',                       0, ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
# The old site used raGe dice instead of Game dice in the following recipes.
('Delt',                  '#(4) #(4) (10) (12) #(X)',                        0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Reggie',                '(8) (10) #(12) (20) #(20)',                       0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Rold',                  '(6) (6) #(6) #(6) #(6)',                          0, 1, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#? just wondering why these four were not included in the original site.  
#?('Billy Shakespear',      '(15) (64) (16) (16)',                             0, ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#?('Drew's Truck',          '(5) (10) (15) (20) (X)',                          0, ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#?('Igor(p)',               '(8) (8) z(12) (20) (X)',                          0, ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#?('Mike Young',            '(X) (X) (Y) (Y)',                                 0, ?, (SELECT id FROM buttonset WHERE name="2004 Origins")),
#('2005 Rare Promo')                         NO SPECIAL SKILLS
('Kitty Cat Seven',     '(4) (6) (8) (10) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare Promo")),
('Sylvia Branham',      '(6) (6) (6) (X) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare Promo")),
# BALTICON 34 (Balticon)                             INTRODUCES Option(/)       (2000 Rare / Promo on old site)
('Social Class',   '(4/6) (6/8) (8/10) (10/12) (12/20)',        0, 1, (SELECT id FROM buttonset WHERE name="Balticon 34")),
# BAR MITZVAH (Theodore Alper)                         SKILLS: Speed (z); Ornery (o)
('Bar Mitzvah Boy', '(6/13) (8) (10) (f13) (f30)',             0, 0, (SELECT id FROM buttonset WHERE name="Bar Mitzvah")),
('Judah Maccabee',  '(8) z(12) H(4) o(12) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="Bar Mitzvah")),
# THE BIG CHEESE (Cheapass Games) 
('Bunnies',     '(1) (1) (1) (1) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="The Big Cheese")),
('Lab Rat',     '(2) (2) (2) (2) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="The Big Cheese")),
# BRAWL (Cheapass Games)              INTRODUCES Speed(z) dice
('Bennett',     '(6) (8) z(20) z(20) (S)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Chris',       'z(6) z(8) (10) z(12) (S)',            0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Darwin',      '(4) (6) z(10) (20) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Hale',        'z(8) (12) (20) (20) (S)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Morgan',      'z(10) z(12) z(12) z(X)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
# Morgan: z10 z12 z12 zX +X  This was in the list by glassonion - is it a typo, or is this how it was implemented on the old site?
('Pearl',       '(6) (8) (12) (X) z(X)',               0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Sydney',      'z(4) z(6) z(8) (X) z(X)',             0, 0, (SELECT id FROM buttonset WHERE name="")),
# Brawl: Catfight Girls from 2001 Origins
('Angora',      'z(4) z(6) z(8) z(10) z(X)',           0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Nickie',      'z(4) (10) (10) (12) z(12)',           0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Sonia',       '(6) (6) z(12) (20) (20)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
('Tamiya',      '(4) (8) (8) (12) z(20)',              0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
# TESS: from  Origins 2000 / Club Foglio;   INTRODUCES Null(n) Dice
('Tess',        'n(4) (8) (12) n(20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="BRAWL")),
# BROM (Cheapass Games)               INTRODUCES Poison(p)and Slow(w) dice; Shadow(s) dice; special rules for Echo
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
('Giant',       'w(20) w(20) w(20) w(20) w(20) w(20)', 0, 1, (SELECT id FROM buttonset WHERE name="BROM")),
# Echo does not have it's own recipe - it copies the recipe of the opposing button
# ('Echo',      'none',                                1, 1, (SELECT id FROM buttonset WHERE name="BROM")),
# BRIDGE AND TUNNEL (Bridge and Tunnel Software)  INTRODUCES Reboud(=) dice (not implemented); poison(p); shadow(s); option
('Agent Orange',     '(6) p(6) =(10) (4/12) (4/20)',         0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Huitzilopochtli',  '(6) (8) =(10) (12) (X)',               0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Lisa',             '(4) (4) (30) (30)',                    0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Nethershadow',     '(6) (10) s(10) (10/20) (6/30)',        0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Phuong',           '(4) (10) (10) (20) (X)',               0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Uncle Scratchy',   '(2) (4) (6) (10) (X)',                 0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
# BRUNO (Hunter Johnson)             INTRODUCES Berserk(B) dice; *requires special rules
# Bruno gains (X) when facing Pappy
('Bruno',       'B(4) B(8) B(20) B(20) B(X)',   1, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
# Pappy gains B(X) when facing Bruno
('Pappy',       '(4) (4) (10) B(20) (X)',       1, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
('Synthia',     'B(4) (12) (12) (T) B(T)',      0, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
('The GM',      '(4) (8) (12) (16) B(U)',       0, 1, (SELECT id FROM buttonset WHERE name="Bruno")),
# BUTTON BRAINS (LinguaPlay)                         introduces Konstant(k) dice; option; twin
('Al-Khwarizmi',           '(4) k(6) (8) (12) (20)',            0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Carl Friedrich Gauss',   'k(6) (8) (8) (12,12) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Fan Chung Graham',       '(4) k(6) (8) (10/20) (X)?',         0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Ron Graham',             'k(6) (10) (12) (20) (V)?',          0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Leonard Eugene Dickson', '(3) k(6) (10) (20) (W)',            0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Paul Erdos',             '(3) (4) k(6) (12) (U)',             0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Pythagoras',             'k(6) (8) (10) (12) (Z)',            0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
('Theano',                 '(4) k(6) (8,8) (10) (S)',           0, 1, (SELECT id FROM buttonset WHERE name="Button Brains")),
# BUTTONBROCCOLI (Looney Labs)                     INTRODUCES Time & Space(^) Dice; Turbo Swing
# Tirade from Wunderland (Wunderland)
('Tirade',              '(6) ^(6) ^(10) (V)!',                0, 0, (SELECT id FROM buttonset WHERE name="ButtonBroccoli")),
# BUTTONLORDS (Green Knight)             INTRODUCES Auxilary(+) dice; Shadow(s)
('Arthur',      '(8) (8) (10) (20) (X) +(20)',      0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Mordred',     's(6) (10) (10) s(20) s(X) +(4)',   0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Lancelot',    '(10) (12) (20) (20) (X) +(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Gawaine',     '(4) (4) (12) (20) (X) +(6)',       0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Morgan Le Fay','s(4) (12) s(12) (20) (X) +(12)',  0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Guenever',    '(6) s(8) (10) (12) (X) +(8)',      0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Nimue',       '(4) (6) s(12) (20) (X) +s(10)',    0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
('Merlin',      '(2) (4) s(10) s(20) (X) +s(X)',    0, 1, (SELECT id FROM buttonset WHERE name="Buttonlords")),
# DEMICON THE 13TH (DemiCon)                           SKILLS Shadow; Option
('The Effster',               's(4) (8) (8) s(12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Demicon the 13th")),
('The Fictitious Alan Clark', 's(8) s(8) (3/12) (20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="Demicon the 13th")),
# DICELAND                            INTRODUCES Stinger(g) dice
('Buck Godot',  'g(8) g(10) (12) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Cass',        '(4) g(4) g(6) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Z-Don',       'g(6) g(8) p(16) (X) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Crysis',      'g(8) (10) (10) (X) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
('Golo',        'g(10) g(12) g(20) g(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Diceland")),
# MICRO from 2002 Origins
('Micro',       'g(4) g(4) (12) p(12) g(X)',     0, 0, (SELECT id FROM buttonset WHERE name="Diceland")),
# DORK VICTORY (Cheapass Games)       INTRODUCES Mood Swing(?); Speed(z); Twin 
('Bill',        '(20) (20) (20) (V,V)',          0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Carson',      '(4,4) (8) (10) (12) (V)',       0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Gilly',       '(6) (8) z(8) (20) (X)?',        0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Igor',        '(3) (12) (20) (20) (X)?',       0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Ken',         '(8) (10) z(12) (20) (V)?',      0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
('Matt',        'z(8) (10) (10) z(10) (V)?',     0, 1, (SELECT id FROM buttonset WHERE name="Dork Victory")),
# FAIRIES (Cool Tuna)
('Sven',                'q(20) q(20) (8/12) (6/12) (4)',      0, 1, (SELECT id FROM buttonset WHERE name="Fairies")),
('Yseulte',             'p(20) q(10) q(8) (X) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="Fairies")),
# FANTASY (Cheapass Games)                 INTRODUCES Socrates(S) Dice; Option; Special Rules for Nightmare
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
('Socrates',    'S(4) S(10) S(12) S(Y)',              0, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
# When Nightmare loses a round he may change his opponent's variable dice.
('Nightmare',   '(4) (8) (10) (20) (20)',             1, 1, (SELECT id FROM buttonset WHERE name="Fantasy")),
# ('Dead Guy',    '(0) (0) (0) (0) (0)',                0, 0, (SELECT id FROM buttonset WHERE name="Fantasy")),
# FREAKS (Cheapass Games)              INTRODUCES Queer(q) dice; Poison(p); Shadow(s); Speed(z)
('Max',  'p(12) p(12) p(20) p(20) p(30) p(30) p(X) p(X)',  0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
('Mister Peach','(6) z(8,8) ps(12,12) (V,V)!',    0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
('Simon',       'q(4) q(6) q(12) q(20) q(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
('Werner',      '(8) (10) (10) (12) pzs(V)!',     0, 1, (SELECT id FROM buttonset WHERE name="Freaks")),
# The Japanese Beetle! (The Japanese Beetle)
# The Flying Squirrel cannot make skill attacks
# ('The Flying Squirrel', 's(6) s(12) s(12) s(20)',             1, 1, (SELECT id FROM buttonset WHERE name="Japanese Beetle")),
# The Japanese Beetle: Skill attacks do not work on Japanese Beetle
# ('The Japanese Beetle', '(6) (8) (12) (X)',                   1, 1, (SELECT id FROM buttonset WHERE name="Japanese Beetle")),
# GUARDIANS OF ORDER sets include Sailor Moon 1, Sailor Moon 2, and Tenchi Muyou!
#     INTRODUCES Iconic Abilities which are button specials that only work against other Guardians of Order buttons.
# SAILOR MOON 1 (Guardians of Order)                          INTRODUCES Reserve(r) dice; AND Warrior(`) dice
# ICONIC ABILITIES: SAILOR MOON: TM(1), QUEEN BERYL: CB(2), SAILOR MERCURY: TM(1), ZOYCITE: NV(4), SAILOR MARS: TM(1), JEDITE: NV(4),
#                   SAILOR JUPITER: TM(1), NEPHLITE: NV(3), SAILOR VENUS: TM(1), MALACHITE: NV(2), TUXEDO MASK: TMDF
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
('Shadow Warriors',     '(1) (2) `(4) `(6) `(8) `(10) `(12)',            1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 1")),
# SAILOR MOON 2 (Guardians of Order)                             SKILLS: Reserve(r)
# ICONIC ABILITIES: LUNA & ARTEMIS: Cat(2), WICKED LADY: DC(2), QUEEN SERENITY: IS(1), RUBEUS: DC(1), PRINCESS SERENA: IS(1), 
#  SAPPHIRE: DC(2), RINI: IS(1), WISEMAN: Skull, NEO-QUEEN SERENITY: SM(2), PRINCE DIAMOND: DC(2), KING ENDYMION: KC(1), EMERALD: DC(1) 
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
('King Endymion',       '(6) (10) (10) (20) r(6) r(10) r(12) r(20)',     1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
('Emerald',             '(6) (8) (12) (20) r(4) r(6) r(10) r(20)',       1, 1, (SELECT id FROM buttonset WHERE name="Sailor Moon 2")),
# TENCHI MUYOU! (Guardians of Order)                              SKILLS Reserve
# ICONIC ABILITIES: TENCHI: Jur(2), KAGATO: Com(1), AYEKA: Jur(1), RYOKO: Com(1), MIHOSHI: Com(1), SOJA'S GUARDIANS: Alt(1),
#                   KIYONE: Com(1), RYO-OHKI: Morph, WASHU: Alt(2), DR. CLAY: Con(2), SASAMI: Jur(3)
('Tenchi',              '(4) (10) (12) (20) r(4) r(12) r(20) r(20)',     1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Kagato',              '(4) (6) (20) (20) r(10) r(12) r(12) r(20)',     1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Ayeka',               '(6) (8) (10) (10) r(4) r(4) r(10) r(20)',       1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Ryoko',               '(8) (10) (12) (12) r(4) r(10) r(20) r(20)',     1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Mihoshi',             '(4) (8) (12) (12) r(8) r(10) r(12) r(20)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Soja\'s Guardians',   '(4) (4) (4) (4) r(4) r(10) r(10) r(12)',        1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Kiyone',              '(4) (4) (10) (12) r(6) r(10) r(10) r(20)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Ryo-Ohki',            '(4) (4) (4) (12) r(10) r(12) r(20) r30',        1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Washu',               '(4) (6) (12) (X) r(6) r(8) r(10) r(20)',        1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Dr. Clay',            '(6) (10) (10) (10) r(4) r(4) r(12) r(12)',      1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
('Sasami',             '(4) (4) (6) (8) r(12) r(12) r(20) r(20)',        1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
# Zero works just like Echo - it doesn't have it's own recipe, but instead copies its important
# ('Zero',             'none',                                            1, 0, (SELECT id FROM buttonset WHERE name="Tenchi Muyou!")),
# HOWLING WOLF (Howling Wolf Studios)              INTRODUCES Stealth(d) Dice
('Howling Wolf',        'd(4) (8) (12) (20) d(20)',           0, 1, (SELECT id FROM buttonset WHERE name="Howling Wolf")),
('White Tiger',         '(6) d(6) (10) (12) d(12)',           0, 1, (SELECT id FROM buttonset WHERE name="Howling Wolf")),
# LAS VEGAS                                INTRODUCES Wildcard(C) AND Pai Gow(:); Option; Twin; Turbo
('Frankie',       '(2,3) (3,4) (4,5) (10) (T)!',      0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('Lola',          '(6) (6) (8) (T) (U)',              0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('Sly',           '(12) (12) (20) (20) (U)',          0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('WildCard',      '(C) (C) (C) (C) (C)',              0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('PitBoss',       '(12) (12) (20) (20) (U)',          0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('Showgirl',      '(6) (6) (8) (T) (U)',              0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
('Professor',     '(2,3) (3,4) (4,5) (10) (T)!',      0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# Black Jack's T swing cannot be a d12.
('Black Jack',    '(1,1) 11 (8,8) (10,11) (T)',       1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
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
('Magician',      '(6) (8) (10) (12) (T)',            1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
# LEGEND OF THE FIVE RINGS (Wizards of the Coast)  INTRODUCES Focus(f) dice
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
('Kakita',      '(6) f(6) (10) f(12) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Legend of the Five Rings")),
# LUNCH MONEY (Atlas Games)           INTRODUCES Trip(t) dice
('Charity',     't(4) (4) (8) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Prudence',    '(1) t(4) (6) (12) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Hope',        't(1) (2) t(4) (6) (Y)',         0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Chastity',    't(6) (6) (10) (10) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Faith',       't(2) (6) (10) (12) (Y)',        0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Temperance',  't(2) (8) (12) (20) (Y)',        0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
('Patience',    '(2) (2) (8) (12) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Lunch Money")),
# MAJESTY & MAJESTY - NORTHERN EXPANSION (Cyberlore)                     NO SPECIAL SKILLS
('Dirgo',       '(20) (20) (20) (X)',            0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
('Flaire',      '(6) (10) (10) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
('Nerni',       '(4) (4) (12) (X) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
('Yeti',        '(10) (20) (30) (30) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Majesty")),
# METAMORPHERS (IMGames)          INTRODUCES Morphing(m) dice
('Daisy',     'm(6) (10) (10) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Jingjing',  'm(4) (8) (10) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Mau',       '(6) (6) (8) (12) m(X)',       0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Spencer',   '(8) (8) (12) m(20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
('Talus',     '(4) m(12) (20) (20) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Metamorphers")),
# POLYCON (Polycon)                   INTRODUCES Fire(F)
('Poly',        '(4) (6) F(8) (20) (X)',         0, 1, (SELECT id FROM buttonset WHERE name="Polycon")),
('Adam Spam',   'F(4) F(6) (6) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Polycon")),
# PRESIDENTIAL BUTTONMEN                              SKILLS: Option; Poison; Shadow
# Cthulhu from Cthulhu (Chaosium)
# Gore & Bush from GenCon 2000 (Cheapass Games)
('Bush',                '(4) (20) (4/8) (6/12) (6/20)',       0, 1, (SELECT id FROM buttonset WHERE name="Presidential")),
('Gore',                '(4) (20) (4/8) (6/12) (6/20)',       0, 1, (SELECT id FROM buttonset WHERE name="Presidential")),
('Cthulhu',             '(4) (20) s(4,8) s(6,12) ps(6,20)',   0, 1, (SELECT id FROM buttonset WHERE name="Presidential")),
# RENAISSANCE (Stone Press)                 SKILLS: Mood swing(?); Ornery(o); Poison(p); Speed(z); Option; Unique(u)
('Dr. Speculo',    '(6) (8) (12) o(Y) o(Y)',       0, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
('Guillermo',      '(6) (10) (20) u(X) u(Y)',      0, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
('Harry Pudding',  '(4) (7) (2/20) (2/20) (10)',   0, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
('Lascivia',       '(4) zp(12) (20) p(X)',         0, 0, (SELECT id FROM buttonset WHERE name="Renaissance")),
('MothMan',        '(8) (8) (12) (16) (Y)?',       0, 1, (SELECT id FROM buttonset WHERE name="Renaissance")),
# SAMURAI (Cheapass Games)               SKILLS: Focus(f)
# Tsusuko from GenCon 2001 (Cheapass Games)
('Honzo',       '(10) (12) f(20) (V) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Konami',      '(6) (8) f(10) f(10) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Okaru',       '(4) f(4) (6) (12) (V)',         0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Sadakuro',    'f(4) f(6) f(8) f(10) (12)',     0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Tonase',      '(4) (4) (8) (20) f(X)',         0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Yuranosuke',  '(4) (8) (12) f(12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
('Tsusuko',     'f(6) (10) (10) (16) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Samurai")),
# SANCTUM (Digital Addiction)         NO SPECIAL SKILLS
('Dracha',      '(4) (10) (20) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
('Ogi',         '(2) (4) (10) (X) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
('Fingle',      '(6) (7) (8) (12) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
('Ngozi',       '(4) (6) (8) (10) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Sanctum")),
# SAVE THE OGRES (Save the Ogres)                      SKILLS Shadow; Twin
# Ginzu & Gratch from GenCon 2000
('Ginzu',               '(8) (8) s(12,12) (20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Save The Ogres")),
('Gratch',              '(6) s(8,8) (20) s(20) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Save The Ogres")),
# SFR (SFR)                                   SKILLS: Option           (2001 Rare / Promo on old site)
('Assailer',       '(12) (12) (20) (2/20) (X)',    0, 1, (SELECT id FROM buttonset WHERE name="SFR")),
('Harbinger',      '(4) (4) (4/12) (10/20) (V)',   0, 1, (SELECT id FROM buttonset WHERE name="SFR")),
# SLUGGY FREELANCE (Sluggy)               ITNRODUCES: Option (/) dice
('Aylee',       '(8) (10/20) (12) (12/20) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Bun-Bun',     '(4/10) (4/12) (6/12) (20) (20)',     0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('KiKi',        '(3/20) (4) (8/12) (10) (10/20)',     0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Riff',        '(4/20) (6) (6/8) (10/12) (20)',      0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Torg',        '(6) (6/20) (8) (10/12) (12/20)',     0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
('Zoë',         '(4/12) (6/10) (8) (10/20) (12/20)',  0, 1, (SELECT id FROM buttonset WHERE name="Sluggy Freelance")),
# SOLDIERS (Cheapass Games) NOTE: original Buttonmen set, no special die skills
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
('Changeling',  '(X) (X) (X) (X) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Soldiers")),
# SPACE GIRLZ (buttonmen.com)   INTRODUCES Plasma(P){} dice; Mighty(H); Ornery(o); Poison(p); Shadow(s); Weak(h); twin
('Maya',       'o(6) (12) pP{hs,H}(12) (20) (S)',             0, 0, (SELECT id FROM buttonset WHERE name="Space Girlz")),
('Zeno',       'P{h,H}(6) P{h,H}(8) P{h,H}(4,4) (20) (X)',    0, 0, (SELECT id FROM buttonset WHERE name="Space Girlz")),
# STUDIO FOGLIO (Studio Foglio)       INTRODUCES Twin dice; Poison(p)
# What's New
('Phil',             '(8) (8) (10,10) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Dixie',            '(4) (6) (10) (12,12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Growf',            '(4,4) (6) (8) (12) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# Buck Godot    NOTE: Winslow from this set is a die, not a button.
('Buck',             '(6,6) (10) (12) (20) (W,W)',    0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# Girl Genius
# Jorgi from GenCon 2001 (Cheapass Games)
('Agatha',           '(4) (6) (8,8) (20) (X)',        0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Krosp',            '(4) (6,6) (12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Klaus',            '(4) (10,10) (20) (20) (W)',     0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Von Pinn',         '(4) p(6,6) (10) (20) (W)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Bang',             'p(4,4) (6) (12) (12) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Gil',              '(8) (8) p(8,8) (20) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('Jorgi',            '(4) (6) (8) (20) p(X)',         0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('The James Beast',  '(4) (8,8) (10,10) (12) (W,W)',  0, 0, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# XXXenophile
('Brigid',           '(8) (8) (X) (X) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
('O-Lass',           '(6) (12) (X) (X) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Studio Foglio")),
# SYDCON 10 (SydCon)               INTRODUCES Rage(G)    (2001 Rare / Promo on old site)
('Gripen',     '(4) (6) (8) G(12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="SydCon 10")),
# WONDERLAND                                        SKILLS Null; Option; Poison; Queer; Stinger; Turbo; Twin
('Alice',             '(6) (8) (8) (10) (1/30)!',          0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('Mad Hatter',        'q(6) q(6) q(10) q(20) q(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('Queen Of Hearts',   '(6) (8) p(16) (20) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('The Jabberwock',    '(20) (20) (30) ng(30) (U)',         0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('Tweedledum+dee',    '(2,2) (4,4) (6,6) (10,10) (T,T)',   0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
('White Rabbit',      '(4) (6) (8) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Wonderland")),
#VAMPYRES (Cheapass Games)            INTRODUCES Shadow(s) dice
('Angel',       's(4) (6) s(12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Buddy',       's(6) (10) s(20) (20) (X)',      0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Dunkirk',     '(6) (6) (10) (20) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Starchylde',  's(6) (8) s(10) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('McGinty',     '(4) s(10) (12) (12) (X)',       0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
('Tiffany',     '(4) s(8) (8) (10) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="Vampyres")),
# YOYODYNE (Fuzzface)                   INTRODUCES Chance(c) dice
('Fuzzface',      '(4) (8) (10) c(10) c(12)',    0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
('John Kovalic',  '(6) c(6) (10) (12) c(20)',    0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
('Pikathulhu',    '(6) c(6) (10) (12) c(X)',     0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
('Ulthar',        '(4) (8) (10) c(10) c(T)',     0, 1, (SELECT id FROM buttonset WHERE name="Yoyodyne")),
#########
# I haven't found any info regarding the rest of these sets, and suspect many to fan / vanity sets
# GEEKZ                                                        SKILLS Poison; Shadow; Reserve; Twin
('Caine',         'ps(4) ps(4) s(20) s(20) s(X)',                     0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
('Cammy Neko',    '(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)',    0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
('Sailor Man',    '(4,4) (8) (20) (12,12) r(10,10) r(6,6) r(8)',      0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
('Xylene',        's(6) s(8) s(10) s(X) s(Y)',                        0, 1, (SELECT id FROM buttonset WHERE name="Geekz")),
# IRON CHEF                                                   SKILLS Option
('Chairman Kaga',         '(5/7) (7/9) (9/11) (11/17) (17/23)',      0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
('Iron Chef Chinese',     '(7) (9) (11) (11) (13/29)',               0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
('Iron Chef French',      '(5/27) (7) (13) (17) (23)',               0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
('Iron Chef Italian',     '(3) (5) (9) (9) (11/21)',                 0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
('Iron Chef Japanese',    '(9/17) (17) (17) (21) (29)',              0, 0, (SELECT id FROM buttonset WHERE name="Iron Chef")),
# BUTTONMEN.DHS.ORG: the following sets make use of die types designed specifically for the old buttonmen online
# 7 DEADLY SINS                                  SKILLS Doppleganger; Mighty; Morphing; Option; Posion; Queer; Rage; Speed; Turbo
('Anger',        '(10) (10) (10) (12/20) (20/30)!',     0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Envy',         'D(4) D(6) D(10) D(12) D(X)',          0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Gluttony',     'H(1,2) Hp(1,3) H(1,4) m(1,5) (W)',    0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Greed',        '(8) (12) (12) z(12) z(4/20)',         0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Lust',         '(6) (6) m(12) D(20) q(X)',            0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Pride',        '(8) (10) G(12) G(20) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
('Sloth',        '(4,4) (6,6) (8,8) (10,10) (V,V)',     0, 0, (SELECT id FROM buttonset WHERE name="7 deadly sins")),
# CHICAGO CREW                                   SKILLS Berserk; Mighty; Option; Ornery; Shadow; Speed; Trip
('Audrey',        't(2) (3/6) o(8) (12) (Y)',           0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Cheathem',      'Ho(1) s(6) o(10) o14 s(X)',          0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Flint',         'o(4,4) (12) o(16) (20) o(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Lizzie',        't(6) s(6) B(10) o(12) o(X)',         0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Monty Brown',   't(1) z(6) o(10) (20) (Y)',           0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Octavia',       'st(4) s(4) so(10) s(10) o(X)',       0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
('Spooky',        'ot(8) o(6) o(10) B(15) o(Z)?',       0, 0, (SELECT id FROM buttonset WHERE name="Chicago Crew")),
# FOUR HORSEMAN                                  SKILLS Berserk; Mighty; Poison; Shadow; Speed; Twin; Weak 
('Death',         '(2,2) p(8) s(10) (16) (X)?',         0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
('Famine',        '(6) (8) (10) (12,12) h(X)',          0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
('Pestilence',    '(4) pH(6) (12) (20) (S)',            0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
('War',           'z(4) (6) z(10) B(20) (W,W)!',        0, 0, (SELECT id FROM buttonset WHERE name="Four Horsemen")),
# FREE RADICALS                                SKILLS Doppleganger; Ornery
('CynCyn',       '(6) D(10) (20) D(X) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Loren',        '(4) (6) Do(6) D(20) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Lorrie',       'D(6) (12) (20) (20) D(X)',          0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Maskin',       'D(4) (4) (8) D(16) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Randy',        '(4) D(6) D(8) (30) (S)',            0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
('Tony',         'D(6) (8) (10) (12) D(X)',           0, 0, (SELECT id FROM buttonset WHERE name="Free Radicals")),
# HODGE PODGE             SKILLS Chance; Chaotic; Focus; Mood Swing; Option; Ornery; Reserve; Slow; Twin
#       SKILLS Berserk; Doppleganger; Fire; Jolt; Konstant; Maximum; Mighty; Rage; Shadow; Speed; Stealth; Time and Space; Trip
('BabyBM',              '(7) o(8,8) (11) (21) HG(V)',                0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Bull',                '(6) (8) (12) (X) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Butthead',            'B(U) t(T) H(3) @d(5)',                      0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Button of Loathing',  'Jk(13) (6) (6) (20) (R,R)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Craptacualr',         'c(6) c(8) (12) (20) (T,T)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Crosswords',          'c(R) o(S) s(W) o(R) d(S)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Da Pimp',         't(4) (8) (12) (20) wHor(4) wHor(4) whor(20) whor(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Darth Maul',          '(8) f(10) (12) f(20) (30)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Evil Robot Luke',     '(4/20) (6/12) p(10) (12) (12/20)',          0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Ferretboy',           '(6) (6) @(20) @(20) @o(X)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Holiday',             '(2/14) (3/17) (7/4) (10/31) (12/25)',       0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Horde O Ninjas',      'd(6) d(6) d(10) d(12) d(20) d(20) dr(6) dr(8)',     0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Jose',                'M(3) M(4) M(6) M(8) M(T)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Loki',                'Ho(2,2) Ho(2,2) Ho(2,2) Ho(2,2) (T)',       0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Marilyn Monroe',      'D(36) (24) (36)',                           0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Qui-Gon Jinn',        'f(4) (6) f(8) (10) f(12)',                  0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Skomp',               'wm(1) wm(2) wm(4) m(8) m(10)',              0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('The Tick',            'H(1,10) H(12) H(20) H(20)',                 0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Thor',                'G(6) G(10) G(12) G(6)/(20)',                0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Tilili',              'oz(4) @(10,10) ^(12) z(12) (Y)!',           0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Trogdor',             'sGF(20) sGF(20) sGF(20) sGF(20)',           0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
('Tyler Durden',        'o(2,2) o(6) s(20) B(20) o(X)?',             0, 0, (SELECT id FROM buttonset WHERE name="Hodge Podge")),
# VICTORIAN HORROR       SKILLS Option; Reserve; Mood Swing; Ornery; Plazma; Null; Value;
#   SKILLS Focus; Konstant; Poison; Shadow; Speed; Stealth; Stinger; Time and Space; Trip; Weak   
('Count Dracula',            'sf(4) (8) s(10) (16) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
('Dr. Jekyll',       '(X)? (X)? r^(20) rvkt(20) rsfp(20) rhop(20)',   0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
('Frankenstein\'s Monster',  '(10/16) (16/20) o(20/30) P{G,B}(30)',   0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
('Invisible Man',            'n(4) d(6) d(10) ng(10) d(20)',          0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror")),
('Wolfman',                  '(6) p(10) (12) z(16) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Victorian Horror"));
