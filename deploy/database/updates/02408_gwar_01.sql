INSERT INTO buttonset (id, name, sort_order) VALUES
(9000, 'GWAR', 90000);

# I've reserved the IDs for the four excluded buttons in case we add them again later
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(9001, 'Balsac the Jaws O'' Death', 'M(8) M(12) (20) (P)', 0, 0, 9000),
(9002, 'Beefcake the Mighty', 'H(6) H(12) (20) (V,V)', 0, 0, 9000),
(9003, 'Blothar (Berserker Warrior)', '(6) (20) (30) (4/20) `(8) `(12)', 0, 0, 9000),
(9004, 'Bonesnapper (Cave Troll)', '(4) (6) (8) (20) `(X) `(Y)', 0, 0, 9000),
#(9005, 'Bozo Destructo', 'p(20) p(20) p(20) p(X)', 0, 0, 9000),
(9006, 'Cardinal Syn', 'g(8) g(8) (8,8) (12) (X)', 0, 0, 9000),
(9007, 'Dickie Duncan', '(4,4) (6,6) (8,8) (Y,Y)', 0, 0, 9000),
(9008, 'Dr. Skullheadface', '(X) (X) p(Y) g(Y)', 0, 0, 9000),
(9009, 'Estrogina', '(6) (8) (T) M(T) `(4) `(8)', 0, 0, 9000),
(9010, 'Flattus Maximus', '(6) (12) (12) (20) p(X)', 0, 0, 9000),
#(9011, 'Flesh Column', '(Y) (Y) (Y) (Y)', 0, 0, 9000),
(9012, 'Gor-Gor', '(8) (6,6) (20) p(30) (U)', 0, 0, 9000),
(9013, 'Jizmac Da''Gusha', 'M(4,4) g(12) (20) (P)', 0, 0, 9000),
(9014, 'The Master', '(8) (30) (30) (30) (U)', 0, 0, 9000),
(9015, 'Morality Squad', 'g(4) H(6) (8) (V,V) `(4) `(6)', 0, 0, 9000),
(9016, 'Mr. Perfect', '(1) (1) M(2) M(3) (V)', 0, 0, 9000),
(9017, 'Oderus Urungus', '(6) (8) g(8) (12,12) (4/20)', 0, 0, 9000),
#(9018, 'Porcelon (Portal Potty)', '(6) (12) (12) (12)', 0, 0, 9000),
(9019, 'Pustulus Maximus', 'g(4) (12) g(12) (20) (4/20)', 0, 0, 9000),
(9020, 'Sawborg Destructo', '(12) (12) (20) (20) p(P)', 0, 0, 9000),
#(9021, 'Scroda Moon', 'b(4) b(4) b(12) b(P)', 0, 0, 9000),
(9022, 'Sexecutioner', '(6,6) g(12) (X) (X) (X)', 0, 0, 9000),
(9023, 'Sleazy P. Martini', 'g(4) (20) (X) (X) (X)', 0, 0, 9000),
(9024, 'Slyminstra Hymen', '(4) (6) (8,8) g(12) o(Y,Y)', 0, 0, 9000),
(9025, 'Techno Destructo', '(12) (8,8) p(30) p(30) g(4/20)', 0, 0, 9000),
(9026, 'World Maggot', '(20) (20) (20) (30) (20/30)', 0, 0, 9000);

UPDATE button SET flavor_text="Any Poison dice Oderus captures are scored as standard dice, not as poison dice." WHERE name="Oderus Urungus";
#UPDATE button SET flavor_text="After determining who goes first, Porcelon may \"flush\" one of his opponent's dice, removing it from the game for the round." WHERE name="Porcelon (Portal Potty)";
#UPDATE button SET flavor_text="You may remove one die from your dice pool this round to force your opponent to re-roll one of their dice on your turn before you attack." WHERE name="Scroda Moon";
#UPDATE button SET flavor_text="The Flesh Column's starting dice are based on your opponent's, but one available die size lower. If this cannot be determined, flesh column's starting dice are four \"Y\" swing dice. Swing dice are unaffected and you choose their size." WHERE name="Flesh Column";

