INSERT INTO buttonset (name) VALUES

# NEW official (?) ButtonMen sets not on old site
('Kubla Con'),
('ZOECon'),

# unofficial ButtonMen sets not on old site
('Japanese Beetle (unofficial)'),
('Blademasters'),
('Order of the Dolls'),
('Blademasters: The Second Shot'),
('Blademasters: Third Attack'),
('MegaTokyo'),

# Fan sets from old site players
('Cowboy Bebop'),
('50 States');

# BUTTONS THAT WERE PREVIOUSLY COMMENTED OUT
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Buzzing Weasel','F R P G S',                   1, 1, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
('James Ernest','(pi) (inf) (sqrt(-2)) (X)',     1, 0, (SELECT id FROM buttonset WHERE name="1999 Rare / Promo")),
('Killer Christmas Tree',  '(6) Z(6) (10) Z(12) (X)',                        0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),
('Dead Guy',    '(0) (0) (0) (0) (0)',                0, 0, (SELECT id FROM buttonset WHERE name="Fantasy"));

# FIGHTBALL (PREVISOULY COMMENTED OUT)
# Echo and Gordo had their name changed, Pepper and Zal are new additions
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Brie',              '(4) (6) (8) (10) (12) (12) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Domino',            '(4) (4) (8) (8) (8) (10) (12)',           0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Echo(Fightball)',   '(4) (6) (6) (6) (12) (12) (12) (20)',     0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Georgia',           '(6) (6) (8) (8) (12) (20) (20)',          0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Gordo(Fightball)',  '(4) (6) (6) (8) (8) (10) (20)',           0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Mel',               '(4) (4) (8) (10) (10) (20) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Pavel',             '(6) (10) (10) (12) (12) (20) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Rocq',              '(6) (10) (10) (12) (20) (20) (20)',       0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Skinny',            '(4) (4) (4) (6) (8) (8) (10)',            0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Tex',               '(4) (4) (6) (8) (10) (10) (12) (12)',     0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
# Pepper and Zal are from the BM Trading Cards
('Pepper',            '(6) (10) (10) (10) (12) (12) (12) (16)',  0, 1, (SELECT id FROM buttonset WHERE name="Fightball")),
('Zal',               '(4) (6) (6) (16) (16) (20) (20)',         0, 1, (SELECT id FROM buttonset WHERE name="Fightball"));

# ADD TO 2005 RARE PROMO
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Magical Witch Critical Miss',  '(6) (10) (10) (20) (X)?',  0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare Promo"));

# ADD TO BnT
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Phantom Zero',      'g(8) s(10) (12) (2/12) (X)',          0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('Pinball Wizard',    '=(6) =(6) (20) (20)',                 0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
('The Grotch',        'z(4) =(20) (V) (V) (V)',              0, 1, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel"));

# ADD TO YOYODYNE
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
# WT???
('Bob',  '(pX+Y+Z) (t0]) (sA!) (zA!) (cA!) (X+Y+Z)',  1, 0, (SELECT id FROM buttonset WHERE name="Yoyodyne"));


# NEW OFFICIAL SETS

# KUBLA CON (Kubla Con)             INTRODUCES Chase's Giant Sized(O), Kubla Treasure(X), Hoskins(Y), (K) 
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Space Kubla',       '(6) (8) O(12) X(12) (20)',   0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con")),
('Pirate Kubla',      '(6) (8) (20) H(12) (K)',     0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con"));

# ZOECon (ZOECon.net)                                    Shadow
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Carrow',          's(4) s(8) s(12) s(20) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
('Zara',            '(6) (8) (12) (20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
('Peri',            '(6) (6) (10) (X) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
('Glorion',         '(10) (12) (16) (20) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
('The Assassin',    '(6) (10) p(10) (12) s(X)',          0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
('Wren',            '(4) (8) (12) (12) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="ZOECon"));



# UNOFFICIAL SETS

# JAPANESE BEETLE (unofficial)                 INTRODUCES Dodge(_)
# unlicensed fan set designed by Bayani Caes long before official JB set was created
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('The Japanese Beetle(u)',  '(6) (12) (12) _(V) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('The Flying Squirrel(u)',  '(4) (6) (12) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('Joe McCarthy',            '(10) (12) (12) (20) (X)',    0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('Kremlina',                '(6) (8) (10) (12) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('Max Factor',              '(6) (8) (12) (X) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('Frenchman',               '(8) (10) (10) (12) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)"));

# BLADE MASTERS (Bayani Caes)            INTRODUCES Parry (I); Focus, Poison, Speed, Trip
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Arashi',    '(4) (6)  I(10)  f(12) (20)',          0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Michie',    '(4) (8) (12)  z(12) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Johnny',    '(6) t(6) I(8) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Renee',     '(2) (2) (6)  I(10) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Danny',     '(6) t(8) t(8) (20) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Danielle',  '(6) (12)  I(12) (20) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Scott',     '(8) I(8) (10) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Macky',     '(4) (6) (10) (X) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Magistra',  'I(6)  I(10)  I(10) I(X)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Horace',    '(8) (10)  p(20) (20) (Z)',            0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Kainar',    '(4) (4)  z(10) z(V) (V)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
('Inez',      '(6) (6) I(6) (20) (X)',               0, 0, (SELECT id FROM buttonset WHERE name="Blademasters"));

# ORDER OF THE DOLLS              INTRODUCES Assassin(a); Twin
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Chaka',     'a(4) (8) (8) (12) a(30)',             0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls")),
('Strotzie',  '(4) (6) a(10) (12) a(X)',             0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls")),
('Fuma',      'a(8) (16) (20) (V) (Z)',              0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls")),
('Vox',       '(6) a(6) (12) (12) a(V,V)',           0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls"));

#BLADE MASTERS 2                             INTRODUCES Attacker(-), Defender(|), Cross(x); 
#                                                       Option, Reserve, Turbo, Twin, Fire, Poison, Shadow, Speed, Trip
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Paris',    '(4/8)! (6) (10) (10) (12/20)!',           0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
('Gideon',   '(4) (8) (12) (X) r(4) r(6) r(10) r(20)',  0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
('Spider',   't(4) p(6) s(8) z(10) (R)',                0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
('Painter',  'x(2) (4) (8) (12) (X)',                   0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
('Regina',   '(1) (6) t(4,4) (12) (Y)',                 0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot")),
('Damian',   '-(10) |(10) F(10) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: The Second Shot"));

#BLADE MASTERS 3      Attacker, Defender, Cross, Focus, Turbo, Poison, Shadow, Queer, Null, Parry(I), Speed, Twin, Auxiliary
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Rico',         '(6) (8) |(10) |(20) (S)',          0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Seiji',        '(4) (4) F(10) f(12) (16)',         0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Yamaichi',     '(2) (16) (20) f(X)!',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Tommy',        '-(4) (8) -(8) (20) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Ace',          '(4) (6) p(16) (16) (X)',           0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Poison',       '(6) (10) -p(V) |p(V) p(X)',        0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Irene',        'x(3) x(4) s(8) (12) (Z)',          0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Fuyuko',       'q(4) n(6) q(10) (20) (X)',         0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Montgomery',   '(6) n(8) n(12) (20) (X)',          0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Jean-Paul',    '(4) I(8) z(12) Iz(W)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Montague(u)',  '(2,2) (4) (10,10) (20) (X) +(V)',  0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
('Chantal',      'pI(4) pI(8) (20) (20) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
# Silly Self Promo (yes, that's a fudge die)  INTRODUCES Sustaind Fire Die (SFD)
('Bayani',       '(F) (SFD) (16) (16) (12/20)',      1, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack"));

# MEGATOKYO (Dreamshade - MegaTokyo forums)  INTRODUCES Full Auto Dice(P); Turbo, Speed, Mood Swing, Poison, Shadow, Option
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
# Largo cannot use skill attacks.
('Largo',            '(12) (20) (20) (X)',              1, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
('Ping',             '(4) (8) (X)! (X)!',               0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
('Piro',             '(4) (8) (8) z(X)? z(X)?',         0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
('\"Darkly Cute\"',  '(4) p(8) s(10) p(12) s(X)',       0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
('Dom',              '(10) (10) P(20) P(20) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
('Erika',            'z(10) z(12) z(12) z(4/20)!',      0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo"));


## ButtonMen Online fan sets !! :D

#COWBOY BEBOP (Jota)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Ed',        '(4) f(8) (8) (12) (Y)? +t(10)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
('Ein',       '(8) (8) f(8) t(8) (X) +(Y)',              0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
('Faye',      '(6) (6) p(10) (12) (X)! +g(8)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
('Jet',       '(10) s(10) d(12) (30) (X) +n(20)',        0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
('Spike',     '(4) d(6) (8) F(10) (V) +z(12)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
('Vicious',   '(4) (6) s(12) p(12) (X) +B(20)',          0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop"));


#50 States   (Kaufman)
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Alabama',         'D(6) ^(10) o(14) S (X)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Alaska',          'd(6) s(20) s(30) w(30) (T)',                    0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Arizona',         'k(7) g(9) (12) F(15) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Arkansas',        'p(4) H(9) w(12) h(20) (V)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('California',      '@(10) @(20) $p(20) $q(12) (Y)? (Z)',            0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Colorado',        '(4) (6) z(14) (U)? (U)?',                       0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Connecticut',     'H(4) v(11) h(20) (4/20)? (R)',                  0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Delaware',        '(1) (4) h(6) f(8) (T)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Florida',       'g(6) F(10) p(12) (U) r(4) r(6) hr(12) @whr(20)',  0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Georgia(US)',     'oz(10) (4/20) B(X) B(X) q(X)',                  0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Hawaii',          'n(4) m(5) k(8)F(13) d(Y)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Idaho',           'B(4) s(6) (8) (Y) (Y)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Illinois',        '(10/20) G(12) (8,8) (R) rsd(4) rsd(6)',         0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Indiana',         '(4) (8) (12) z(20) (W)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Iowa',            'n^(6) f(8) D(9) (11) k(T)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Kansas',          '(9) c(9) F(9) Gz(9) t(9)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Kentucky',        'z(5) (1/4) (9/8) (20) (R)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Louisiana',       'p(2) @(6) G(12) o(12) (X)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Maine',           'f(4) g(6) s(6) (V) (X)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Maryland',        'g(4) m(8) o(10) (W) (X) +@(8) ...',             0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Massachusetts',   'f(6) k(8) o(10) (X) (Y)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Michigan',        '(8) s(9) p(10) (Z)! `(2) `(3) +(6)',            0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Minnesota',       'o(6) o(6) (7,7) s(20) (X)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Mississippi',     '@(4) H(6) (8) w(13) (W)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Missouri',        'f(4) (10) v(10) H(8,12) (Z)',                   0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Montana',         'B(4) H(8) (12) (S) z(Z)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Nebraska',        '(11) h(U) (S) k(R) fB(11)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Nevada',          'H(3) C (21) c(36) %(V)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('New Hampshire',   'f(4) os(6) Gh(9) (V) (W)?',                     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('New Jersey',      'c(4) B(15) z(18) p(20) s(S)',                   0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('New Mexico',      '^(4) (8) %(10) s(12) (X)?',                     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('New York',        '(12) p(16) z(30) o(X)? rq(6) r(8)',             0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('North Carolina',  'pF(10) o(10) (V)! gt(V) h(V)',                  0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('North Dakota',    '(4,4) (8) s(12) n(12) (W)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Ohio',            'H(6) F(7) p(8) (X)? (X)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Oklahoma',        'f(6) f(10) f(12) f(X)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Oregon',          'z(6) (12) u(R) u(W) u(X)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Pennsylvania',    '$f(2) $s(6) %(4) (12) t(20) (Y)?',              0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Rhode Island',    '(4) (4) d(6) d(10) (R)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('South Carolina',  '(6) (9) fB(10) G(12) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('South Dakota',    '(1) (3) (16) (26) @o(Z)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Tennessee',       '@(1/5) %(6) F(8) (5/25) rpm(3,3) rpm(4,5)',     0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Texas',           '^(10) (10,10) (30) `(8) r(6) r(8) r(10) r(12)', 0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Utah',            '(6) (8) w(12) H(S) (X,X)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Virginia',        '(4) oBs(8) Fp(12) (20) (W)',                    0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Vermont',         '(4) G(6) os(8) g(10) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Washington',      'n(4) z(6) (7) F(13) mso(S)',                    0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('West Virginia',   'q(Y) w(X) B(Y) o(X) Bowq(Z,Z)?',                0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Wisconsin',       'co(W) co(W) cow(S) cow(S) cow(S)',              0, 0, (SELECT id FROM buttonset WHERE name="50 State")),
('Wyoming',         '(4) z(12) kp(20) n(20) (S)',                    0, 0, (SELECT id FROM buttonset WHERE name="50 State"));
