INSERT INTO buttonset (id, name, sort_order)
 VALUES (87, '2021 Fanatics', 100200);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(805, 'Bosco312',         'z^(20) z^(10) d(1) d(1)',                       0, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics")),
(806, 'Cycozar',          '(T) (4)g (Y) o(U)rIt! ^%(3) c(Y) coz(4)r #(1)', 0, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics")),
(807, 'GamerXYZ',         'Ho(4) dk(6) sz(X) qt(Y) `M(Z)',                 0, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics")),
(808, 'irilyth',          'Gg(8) Gg(16) Gg(32) `sptH(1) `sptH(2) `sptH(3) `sptH(5) `sptH(8) spohr(4,9) spohr(9,16) spohr(16,25)', 1, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics")),
(809, 'Scipio',           'bt(1) s(8) (3/19)! (5/23)! #(X)',               0, 0, (SELECT id FROM buttonset WHERE name="2021 Fanatics"));

UPDATE button SET flavor_text="Bosco312, a master of time and space, an evil prince, and a part time theatrical critic, needs to be stealthy in order to beat people up without coming apart at high speed." WHERE name="Bosco312";
UPDATE button SET flavor_text="Cycozar's 3 rules to live by:<br>1. Fun first - winning is a bonus.<br>2. When life gives you obstacles, run them over like a speed bump - thump thump.<br>3. Love & hate are both contagious - choose your disease." WHERE name="Cycozar";
UPDATE button SET flavor_text="GamerXYZ likes graham crackers, electric blankets, and beating people up in games." WHERE name="GamerXYZ";
UPDATE button SET flavor_text="irilyth likes pretty dice with swirly colors, and especially likes using them to beat people up." WHERE name="irilyth";
UPDATE button SET flavor_text="Scipio is a broadly interested heretic from a mysterious country. He likes cheese, chocolate, watches, and the rule of three. Also beating people up." WHERE name="Scipio";
