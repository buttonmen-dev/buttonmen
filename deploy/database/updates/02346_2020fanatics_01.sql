INSERT INTO buttonset (id, name, sort_order)
 VALUES (85, '2020 Fanatics', 100100);

INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES
(788, 'AnnoDomini',       '(W) (4) (R) s(4) (W)',                          0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(789, 'blackshadowshade', 'mkht(T)! Bt(T)& `fg(4) bHt^(0/10) F%(1,10) rbHt^(0/10) rnDt^(1,8)', 1, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(790, 'Blargh',           'Hop(4) hop(10) Mh(8) Mf(8) MF(13)',             0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(791, 'devious',          'dv(S) (16) (16) pqr(S,S) Jm`(0) Jm`(0) Jm`(0)', 0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(792, 'jimmosk',          '(4) %(8) g(12) JIMmo(S) k(2)',                  0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(793, 'jl8e',             'f(7) f(7) p(16) (16) ^#(R,R)',                  0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(794, 'Nala',             'Mkw(1) Mk(1) Mk(3) Mk(9) Mk(27)',               0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(795, 'Nonce Equitaur',   '(S) (3)q (U)It (U) (R)!',                       0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics")),
(796, 'tavella',          '(5)t^ (23)v# (19)I (6)I^ (7)!',                 0, 0, (SELECT id FROM buttonset WHERE name="2020 Fanatics"));

UPDATE button SET flavor_text="AnnoDomini likes cycling, inventing new games, creating his own buttons and beating people up." WHERE name="AnnoDomini";
UPDATE button SET flavor_text="blackshadowshade is a mathematician and a stalwart Button Men programmer. Each bug that he vanquishes makes him stronger, which is good because there's always another bug out there to face. He likes discovering and retelling odd stories, saving the world, and bringing people together to beat each other up." WHERE name="blackshadowshade";
UPDATE button SET flavor_text="Blargh likes to sail the seven seas, raiding merchants, digging up treasure, keelhauling landlubbers, frequenting taverns, and beating people up. Argh!" WHERE name="Blargh";
UPDATE button SET flavor_text="Devious likes to play board games, grow flowers, listen to music, and wander in a twisty maze of passages, all alike, in order to beat people up." WHERE name="devious";
UPDATE button SET flavor_text="Jimmosk solves puzzles, sings rounds, and avoids games that feature dice – with one significant exception." WHERE name="jimmosk";
UPDATE button SET flavor_text="jl8e is an itinerant baker, coder, and game designer who is probably trapped under a very floofy cat right this moment. He likes 7-sided dice, Oddish, pineapple on pizza, and beating people up." WHERE name="jl8e";
UPDATE button SET flavor_text="""nobody ever laughs at my puns. i guess because it's not a great button. a little past half way home got caught in the rising dawn after long and fruitless debates. they handed over a tightly wrapped scroll, bowed and left again, without saying a word. funny that he now did. maybe his partner asked him to stand up to scrutiny. you are better than one chance in 256 of that happening are only 1/40. i thought about going smaller with my y swing, but i think you're doing quite well for you anyway"" -Nala" WHERE name="Nala";
UPDATE button SET flavor_text="Nonce Equitaur enjoys non sequiturs, puns, probability theory, symmetry groups, breaking into geodesic homes, and beating people up." WHERE name="Nonce Equitaur";
UPDATE button SET flavor_text="Tavella enjoys cooking, reading, and of course ... beating people up." WHERE name="tavella";
