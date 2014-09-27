# JAPANESE BEETLE (unofficial)                 INTRODUCES Dodge(_)
# unlicensed fan set designed by Bayani Caes long before official JB set was created
INSERT INTO button (name, recipe, btn_special, tourn_legal, set_id) VALUES
('The Japanese Beetle(u)',  '(6) (12) (12) _(V) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('The Flying Squirrel(u)',  '(4) (6) (12) (20) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('Joe McCarthy',            '(10) (12) (12) (20) (X)',    0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('Kremlina',                '(6) (8) (10) (12) (X)',      0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('Max Factor',              '(6) (8) (12) (X) (X)',       0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)")),
('Frenchman',               '(8) (10) (10) (12) (X)',     0, 0, (SELECT id FROM buttonset WHERE name="Japanese Beetle (unofficial)"));
