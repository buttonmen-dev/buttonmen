#COWBOY BEBOP (Jota)
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(580, 'Ed',        '(4) f(8) (8) (12) (Y)? +t(10)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(581, 'Ein',       '(8) (8) f(8) t(8) (X) +(Y)',              0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(582, 'Faye',      '(6) (6) p(10) (12) (X)! +g(8)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(583, 'Jet',       '(10) s(10) d(12) (30) (X) +n(20)',        0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(584, 'Spike',     '(4) d(6) (8) F(10) (V) +z(12)',           0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop")),
(585, 'Vicious',   '(4) (6) s(12) p(12) (X) +B(20)',          0, 0, (SELECT id FROM buttonset WHERE name="Cowboy Bebop"));
