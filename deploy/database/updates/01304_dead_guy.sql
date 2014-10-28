INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(687, 'Dead Guy',    '(0) (0) (0) (0) (0)',                0, 0, (SELECT id FROM buttonset WHERE name="Fantasy"));
