# KUBLA CON (Kubla Con)             INTRODUCES Chase's Giant Sized(O), Kubla Treasure(X), Hoskins(Y) 
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(572, 'Space Kubla',       '(6) (8) O(12) X(12) (20)',   0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con")),
# Pirate Kubla's X is actually a Kubla die. Every time you reroll it, shout "Kubla!" 
(573, 'Pirate Kubla',      '(6) (8) (20) Y(12) (X)',     0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con"));

# ZOECon (ZOECon.net)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(574, 'Carrow',          's(4) s(8) s(12) s(20) s(X)',        0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(575, 'Zara',            '(6) (8) (12) (20) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(576, 'Peri',            '(6) (6) (10) (X) (X)',              0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(577, 'Glorion',         '(10) (12) (16) (20) (X)',           0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(578, 'The Assassin',    '(6) (10) p(10) (12) s(X)',          0, 1, (SELECT id FROM buttonset WHERE name="ZOECon")),
(579, 'Wren',            '(4) (8) (12) (12) (X)',             0, 1, (SELECT id FROM buttonset WHERE name="ZOECon"));

#COWBOY BEBOP (Jota)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(580, 'Ed',        '(4) f(8) (8) (12) (Y)? +t(10)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(581, 'Ein',       '(8) (8) f(8) t(8) (X) +(Y)',              0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(582, 'Faye',      '(6) (6) p(10) (12) (X)! +g(8)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(583, 'Jet',       '(10) s(10) d(12) (30) (X) +n(20)',        0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(584, 'Spike',     '(4) d(6) (8) F(10) (V) +z(12)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(585, 'Vicious',   '(4) (6) s(12) p(12) (X) +B(20)',          0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop"));

#50 States   (Kaufman)
#NOTE: CA, PA, are meant to have Select Dice. I've given them the unused code 'U' until we figure what to do about that.  
#NOTE: These had button specials set so they are not available to play on the live site. Change all these back after players are 
#      given the option to accept only TL games and/or random challenges can be made which avoid this set. 
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(586, 'Alabama',         'D(6) ^(10) o(14) (S) (X)',                       1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(587, 'Alaska',          'd(6) s(20) s(30) w(30) (T)',                     1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(588, 'Arizona',         'k(7) g(9) (12) F(15) (X)',                       1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(589, 'Arkansas',        'p(4) H(9) w(12) h(20) (V)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
# Replaced $p(20) with Up(20) and $q(12) with Uq(12)
(590, 'California',      '@(10) @(20) Up(20) Uq(12) (Y)? (Z)',             1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(591, 'Colorado',        '(4) (6) z(14) (U)? (U)?',                        1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(592, 'Connecticut',     'H(4) v(11) h(20) (4/20)? (R)',                   1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(593, 'Delaware',        '(1) (4) h(6) f(8) (T)',                          1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(594, 'Florida',       'g(6) F(10) p(12) (U) r(4) r(6) hr(12) @whr(20)',   1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(595, 'Georgia(US)',     'oz(10) (4/20) B(X) B(X) q(X)',                   1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(596, 'Hawaii',          'n(4) m(5) k(8) F(13) d(Y)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(597, 'Idaho',           'B(4) s(6) (8) (Y) (Y)',                          1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(598, 'Illinois',        '(10/20) G(12) (8,8) (R) rsd(4) rsd(6)',          1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(599, 'Indiana',         '(4) (8) (12) z(20) (W)',                         1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(600, 'Iowa',            'n^(6) f(8) D(9) (11) k(T)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(601, 'Kansas',          '(9) c(9) F(9) Gz(9) t(9)',                       1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(602, 'Kentucky',        'z(5) (1/4) (9/8) (20) (R)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(603, 'Louisiana',       'p(2) @(6) G(12) o(12) (X)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(604, 'Maine',           'f(4) g(6) s(6) (V) (X)',                         1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(605, 'Maryland',        'g(4) m(8) o(10) (W) (X) +@(8)',                  1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(606, 'Massachusetts',   'f(6) k(8) o(10) (X) (Y)',                        1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(607, 'Michigan',        '(8) s(9) p(10) (Z)! `(2) `(3) +(6)',             1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(608, 'Minnesota',       'o(6) o(6) (7,7) s(20) (X)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(609, 'Mississippi',     '@(4) H(6) (8) w(13) (W)',                        1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(610, 'Missouri',        'f(4) (10) v(10) H(8,12) (Z)',                    1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(611, 'Montana',         'B(4) H(8) (12) (S) z(Z)',                        1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(612, 'Nebraska',        '(11) h(U) (S) k(R) fB(11)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(613, 'Nevada',          'H(3) (C) (21) c(36) %(V)',                       1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(614, 'New Hampshire',   'f(4) os(6) Gh(9) (V) (W)?',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(615, 'New Jersey',      'c(4) B(15) z(18) p(20) s(S)',                    1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(616, 'New Mexico',      '^(4) (8) %(10) s(12) (X)?',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(617, 'New York',        '(12) p(16) z(30) o(X)? rq(6) r(8)',              1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(618, 'North Carolina',  'pF(10) o(10) (V)! gt(V) h(V)',                   1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(619, 'North Dakota',    '(4,4) (8) s(12) n(12) (W)',                      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(620, 'Ohio',            'H(6) F(7) p(8) (X)? (X)',                        1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(621, 'Oklahoma',        'f(6) f(10) f(12) f(X)',                          1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(622, 'Oregon',          'z(6) (12) u(R) u(W) u(X)',                       1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
# Replaced $f(2) and $s(6) with Uf(2) and Us(6)
(623, 'Pennsylvania',    'Uf(2) Us(6) %(4) (12) t(20) (Y)?',               1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(624, 'Rhode Island',    '(4) (4) d(6) d(10) (R)',                         1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(625, 'South Carolina',  '(6) (9) fB(10) G(12) (X)',                       1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(626, 'South Dakota',    '(1) (3) (16) (26) @o(Z)',                        1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(627, 'Tennessee',       '@(1/5) %(6) F(8) (5/25) rpm(3,3) rpm(4,5)',      1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(628, 'Texas',           '^(10) (10,10) (30) `(8) r(6) r(8) r(10) r(12)',  1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(629, 'Utah',            '(6) (8) w(12) H(S) (X,X)',                       1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(630, 'Virginia',        '(4) oBs(8) Fp(12) (20) (W)',                     1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(631, 'Vermont',         '(4) G(6) os(8) g(10) (V)',                       1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(632, 'Washington',      'n(4) z(6) (7) F(13) mso(S)',                     1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(633, 'West Virginia',   'q(Y) w(X) B(Y) o(X) Bowq(Z,Z)?',                 1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(634, 'Wisconsin',       'co(W) co(W) cow(S) cow(S) cow(S)',               1, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(635, 'Wyoming',         '(4) z(12) kp(20) n(20) (S)',                     1, 0, (SELECT id FROM buttonset WHERE name="50 States"));

# BLADE MASTERS (Bayani Caes)            INTRODUCES Parry (I); Focus, Poison, Speed, Trip
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(636, 'Arashi',    '(4) (6)  I(10)  f(12) (20)',          0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(637, 'Michie',    '(4) (8) (12)  z(12) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(638, 'Johnny',    '(6) t(6) I(8) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(639, 'Renee',     '(2) (2) (6)  I(10) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(640, 'Danny',     '(6) t(8) t(8) (20) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(641, 'Danielle',  '(6) (12)  I(12) (20) (X)',            0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(642, 'Scott',     '(8) I(8) (10) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(643, 'Macky',     '(4) (6) (10) (X) (Y)',                0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(644, 'Magistra',  'I(6)  I(10)  I(10) I(X)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(645, 'Horace',    '(8) (10)  p(20) (20) (Z)',            0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
(646, 'Kainar',    '(4) (4)  z(10) z(V) (V)',             0, 0, (SELECT id FROM buttonset WHERE name="Blademasters")),
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
(664, 'Montague(u)',  '(2,2) (4) (10,10) (20) (X) +(V)',  0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack")),
(665, 'Chantal',      'pI(4) pI(8) (20) (20) (X)',        0, 0, (SELECT id FROM buttonset WHERE name="Blademasters: Third Attack"));

# MEGATOKYO (Dreamshade - MegaTokyo forums)  INTRODUCES Full Auto Dice(P); Turbo, Speed, Mood Swing, Poison, Shadow, Option
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
# Largo cannot use skill attacks.
(667, 'Largo',            '(12) (20) (20) (X)',              1, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(668, 'Ping',             '(4) (8) (X)! (X)!',               0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(669, 'Piro',             '(4) (8) (8) z(X)? z(X)?',         0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(670, 'Darkly Cute',      '(4) p(8) s(10) p(12) s(X)',       0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(671, 'Dom',              '(10) (10) P(20) P(20) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo")),
(672, 'Erika',            'z(10) z(12) z(12) z(4/20)!',      0, 0, (SELECT id FROM buttonset WHERE name="MegaTokyo"));

# ORDER OF THE DOLLS              INTRODUCES Assassin(a); Twin
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(673, 'Chaka',     'a(4) (8) (8) (12) a(30)',             0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls")),
(674, 'Strotzie',  '(4) (6) a(10) (12) a(X)',             0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls")),
(675, 'Fuma',      'a(8) (16) (20) (V) (Z)',              0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls")),
(676, 'Vox',       '(6) a(6) (12) (12) a(V,V)',           0, 0, (SELECT id FROM buttonset WHERE name="Order of the Dolls"));
