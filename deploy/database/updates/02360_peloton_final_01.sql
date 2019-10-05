INSERT INTO buttonset (id, name, sort_order) VALUES
(84, 'Peloton', 8400);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(779, 'Antonio',         'c(4) d(8) %(10) h(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(780, 'Doyle',           'c(4) M(8) ^(10) k(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(781, 'Floriano',        'n(4) f(8) s(10) z(20) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(782, 'Julia',           'H(4) %(8) f(10) d(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(783, 'Mariusz',         'f(4) k(8) H(10) G(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(784, 'Orlando',         'k(4) s(8) g(10) M(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(785, 'Roger',           'g(4) G(8) c(10) H(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton")),
(786, 'Timea',           'd(4) s(8) z(10) h(10) (X)',                      0, 0, (SELECT id FROM buttonset WHERE name="Peloton"));

UPDATE button SET flavor_text="Antonio is a breakaway specialist. He always looks ahead and leaves the beaten up behind." WHERE name="Antonio";
UPDATE button SET flavor_text="Doyle is a time trial specialist. He likes to ride alone and does not need anybody's help to beat people up." WHERE name="Doyle";
UPDATE button SET flavor_text="Floriano is a sprinter. He likes fast pace, fast cars and beating people up. Fast." WHERE name="Floriano";
UPDATE button SET flavor_text="Julia is a hiller. She likes to go up and down, up and down, and beat people up and down, up and down …" WHERE name="Julia";
UPDATE button SET flavor_text="Mariusz is a domestique rider. He helps his captain to save energy and beats people up for him." WHERE name="Mariusz";
UPDATE button SET flavor_text="Orlando is an allrounder. He feels strong in any terrain and is always ready to beat people up." WHERE name="Orlando";
UPDATE button SET flavor_text="Roger is a climber. He likes high mountains and needs little oxygen to beat people up." WHERE name="Roger";
UPDATE button SET flavor_text="Timea is the lead-out rider. She sets the scene for her captain before the final beating up takes place." WHERE name="Timea";
