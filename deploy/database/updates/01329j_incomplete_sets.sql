# INCOMPLETE SETS
INSERT INTO button (id, name, recipe, btn_special, tourn_legal, set_id) VALUES

# 2004 ORIGINS (Flying Buffalo)
(679, 'Killer Christmas Tree',  '(6) Z(6) (10) Z(12) (X)',          0, 0, (SELECT id FROM buttonset WHERE name="2004 Origins")),

# '2005 Rare Promo'
(680, 'Magical Witch Critical Miss',  '(6) (10) (10) (20) (X)?',    0, 0, (SELECT id FROM buttonset WHERE name="2005 Rare Promo")),

# BRIDGE AND TUNNEL (Bridge and Tunnel Software)
(681, 'Phantom Zero',      'g(8) s(10) (12) (2/12) (X)',          0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(682, 'Pinball Wizard',    '=(6) =(6) (20) (20)',                 0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(683, 'The Gratch',        'z(4) =(20) (V) (V) (V)',              0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(684, 'Steve',             '=(4) =(4) =(8) =(20) =(X)',           0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(685, 'The Fog',           's(4) s(4) (30) (30)',                 0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),
(686, 'G',                 'g(6) =(6) z(8) (16) (10/20)',         0, 0, (SELECT id FROM buttonset WHERE name="Bridge and Tunnel")),

# LAS VEGAS
# Clones of Black Jack, Craps, Shamrock, and Crypto - sans button specials
# Black Jack, Shamrock, and Pai Gow have alternate recipes
(688, 'Black Jack II',     '(1,1) (11) (8,8) (10,10,1) (T)',      1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(689, 'Twenty-One',        '(1,1) (11) (8,8) (10,11) (T)',        0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(690, 'Them Bones',        '(6,6) (6,6) (6,6) (6,6) (6,6)',       0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(691, 'Shamrock II',       '(2) (9) (7/13) (7/13) (7/13)',        1, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(692, 'Lady Luck',         '(2) (7/13) (7/13) (7/13) (7/13)',     0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(693, 'Pai Gow II',        '(4) :(10) :(10) (12) (12)',           0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas")),
(694, 'Magician',          '(6) (8) (10) (12) (T)',               0, 0, (SELECT id FROM buttonset WHERE name="Las Vegas"));
# Pai Gow is two words.  Wildcard is one word in this set. 
# checked against http://web.archive.org/web/20080706172140/http://home.att.net/~kerry_and_ryan/LasVegas.html
UPDATE button SET name='Pai Gow'   WHERE name='PaiGow';
UPDATE button SET name='Wildcard'  WHERE name='WildCard';

# ZOECon
# Nara's name didn't match the button image.  The button image was currect. 
UPDATE button SET name='Nara'   WHERE name='Zara';

