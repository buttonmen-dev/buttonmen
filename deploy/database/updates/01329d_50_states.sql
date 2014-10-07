#50 States   (Kaufman)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(586, 'Alabama',         'D(6) ^(10) o(14) S (X)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(587, 'Alaska',          'd(6) s(20) s(30) w(30) (T)',                     0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(588, 'Arizona',         'k(7) g(9) (12) F(15) (X)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(589, 'Arkansas',        'p(4) H(9) w(12) h(20) (V)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(590, 'California',      '@(10) @(20) $p(20) $q(12) (Y)? (Z)',             0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(591, 'Colorado',        '(4) (6) z(14) (U)? (U)?',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(592, 'Connecticut',     'H(4) v(11) h(20) (4/20)? (R)',                   0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(593, 'Delaware',        '(1) (4) h(6) f(8) (T)',                          0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(594, 'Florida',       'g(6) F(10) p(12) (U) r(4) r(6) hr(12) @whr(20)',   0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(595, 'Georgia(US)',     'oz(10) (4/20) B(X) B(X) q(X)',                   0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(596, 'Hawaii',          'n(4) m(5) k(8)F(13) d(Y)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(597, 'Idaho',           'B(4) s(6) (8) (Y) (Y)',                          0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(598, 'Illinois',        '(10/20) G(12) (8,8) (R) rsd(4) rsd(6)',          0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(599, 'Indiana',         '(4) (8) (12) z(20) (W)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(600, 'Iowa',            'n^(6) f(8) D(9) (11) k(T)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(601, 'Kansas',          '(9) c(9) F(9) Gz(9) t(9)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(602, 'Kentucky',        'z(5) (1/4) (9/8) (20) (R)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(603, 'Louisiana',       'p(2) @(6) G(12) o(12) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(604, 'Maine',           'f(4) g(6) s(6) (V) (X)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(605, 'Maryland',        'g(4) m(8) o(10) (W) (X) +@(8) ...',              0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(606, 'Massachusetts',   'f(6) k(8) o(10) (X) (Y)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(607, 'Michigan',        '(8) s(9) p(10) (Z)! `(2) `(3) +(6)',             0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(608, 'Minnesota',       'o(6) o(6) (7,7) s(20) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(609, 'Mississippi',     '@(4) H(6) (8) w(13) (W)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(610, 'Missouri',        'f(4) (10) v(10) H(8,12) (Z)',                    0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(611, 'Montana',         'B(4) H(8) (12) (S) z(Z)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(612, 'Nebraska',        '(11) h(U) (S) k(R) fB(11)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(613, 'Nevada',          'H(3) C (21) c(36) %(V)',                         0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(614, 'New Hampshire',   'f(4) os(6) Gh(9) (V) (W)?',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(615, 'New Jersey',      'c(4) B(15) z(18) p(20) s(S)',                    0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(616, 'New Mexico',      '^(4) (8) %(10) s(12) (X)?',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(617, 'New York',        '(12) p(16) z(30) o(X)? rq(6) r(8)',              0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(618, 'North Carolina',  'pF(10) o(10) (V)! gt(V) h(V)',                   0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(619, 'North Dakota',    '(4,4) (8) s(12) n(12) (W)',                      0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(620, 'Ohio',            'H(6) F(7) p(8) (X)? (X)',                        0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(621, 'Oklahoma',        'f(6) f(10) f(12) f(X)',                          0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(622, 'Oregon',          'z(6) (12) u(R) u(W) u(X)',                       0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
(623, 'Pennsylvania',    '$f(2) $s(6) %(4) (12) t(20) (Y)?',               0, 0, (SELECT id FROM buttonset WHERE name="50 States")),
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
