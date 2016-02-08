INSERT INTO buttonset (id, name, sort_order) VALUES
(77, 'Zodiac', 7500);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(695, 'Aquarius',    '(6) p(6) (8) s(12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(696, 'Aries',       '(6) s(8) z(10) (20) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(697, 'Cancer',      'p(6) g(8) (8) (12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(698, 'Capricorn',   '(6) g(6) (10) s(12) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(699, 'Gemini',      '(4,4) (12) (12) z(V) s(V)',           0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(700, 'Leo',         '(4) (6) p(10) z(20) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(701, 'Libra',       '(4) g(4) (8,8) z(8,8) (V)',           0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(702, 'Pisces',      '(4) p(8) z(12) (12) (X)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(703, 'Sagittarius', '(4) (4) s(8) p(12) (X)',              0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(704, 'Scorpio',     'g(6) (10) (12) p(20) (V)',            0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(705, 'Taurus',      'g(8) (10) (20) s(20) (V)',            0, 0, (SELECT id FROM buttonset WHERE name="Zodiac")),
(706, 'Virgo',       '(4) g(8) z(12) (20) (V)',             0, 0, (SELECT id FROM buttonset WHERE name="Zodiac"));
