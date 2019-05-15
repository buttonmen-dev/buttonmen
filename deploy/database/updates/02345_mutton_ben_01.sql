UPDATE buttonset SET name="2003 Rare / Promo" WHERE name="2003 Rare-Promos";
UPDATE buttonset SET name="2005 Rare / Promo" WHERE name="2005 Rare Promo";

INSERT INTO buttonset (id, name, sort_order) VALUES
(83, '2017 Rare / Promo', 54);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(778, 'Mutton Ben', 'p(8) #(12) s(20) (X) (X)', 0, 0, (SELECT id FROM buttonset WHERE name="2017 Rare / Promo"));
