# KUBLA CON (Kubla Con)             INTRODUCES Chase's Giant Sized(O), Kubla Treasure(X), Hoskins(Y), (K) 
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(572, 'Space Kubla',       '(6) (8) O(12) X(12) (20)',   0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con")),
(573, 'Pirate Kubla',      '(6) (8) (20) H(12) (K)',     0, 1, (SELECT id FROM buttonset WHERE name="Kubla Con"));