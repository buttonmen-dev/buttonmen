INSERT INTO buttonset (name) VALUES
#official sets
('Big Top'); 


#BIGTOP (APE games - Cassandra) 
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('Firebreather',       '(4) F(6) F(6) (12) (S)',          0, 0, (SELECT id FROM buttonset WHERE name="Big Top")),
('Monkeys',            'z(6) z(6) z(6) z(10) z(T)',       0, 0, (SELECT id FROM buttonset WHERE name="Big Top")),
('Ringmaster',         'f(6) f(6) f(8) f(8) (12)',        0, 0, (SELECT id FROM buttonset WHERE name="Big Top")),
('Stumbling Clowns',   '(8) t(8) (10) t(10) (X)',         0, 0, (SELECT id FROM buttonset WHERE name="Big Top"));

