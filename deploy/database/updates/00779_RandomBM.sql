INSERT INTO buttonset (id, name, sort_order) VALUES
(10000, 'Special', 1);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(10001, 'RandomBMVanilla', '', 1, 0, (SELECT id FROM buttonset WHERE name="Special"));
