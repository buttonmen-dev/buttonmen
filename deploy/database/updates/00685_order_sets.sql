ALTER TABLE buttonset
    ADD COLUMN sort_order INT DEFAULT 999999  NOT NULL AFTER name;

DROP VIEW IF EXISTS button_view;
CREATE VIEW button_view
AS SELECT
    b.id, b.name, b.recipe, b.tourn_legal, b.btn_special,
    b.flavor_text, s.name AS set_name, s.sort_order AS set_sort_order
FROM button AS b
    LEFT JOIN buttonset AS s ON b.set_id = s.id
ORDER BY s.sort_order ASC, b.name ASC;

UPDATE buttonset SET sort_order = 100 WHERE NAME = 'Soldiers';
UPDATE buttonset SET sort_order = 200 WHERE NAME = 'The Big Cheese';
UPDATE buttonset SET sort_order = 300 WHERE NAME = 'Sanctum';
UPDATE buttonset SET sort_order = 400 WHERE NAME = 'Lunch Money';
UPDATE buttonset SET sort_order = 500 WHERE NAME = '1999 Rare / Promo';
UPDATE buttonset SET sort_order = 600 WHERE NAME = 'Vampyres';
UPDATE buttonset SET sort_order = 700 WHERE NAME = 'Legend of the Five Rings';
UPDATE buttonset SET sort_order = 800 WHERE NAME = 'Polycon';
UPDATE buttonset SET sort_order = 900 WHERE NAME = 'BROM';
UPDATE buttonset SET sort_order = 1000 WHERE NAME = '2000 Rare / Promo';
UPDATE buttonset SET sort_order = 1100 WHERE NAME = 'BRAWL';
UPDATE buttonset SET sort_order = 1200 WHERE NAME = 'Buttonlords';
UPDATE buttonset SET sort_order = 1300 WHERE NAME = 'Studio Foglio';
UPDATE buttonset SET sort_order = 1400 WHERE NAME = 'Sailor Moon 1';
UPDATE buttonset SET sort_order = 1500 WHERE NAME = 'ButtonBroccoli';
UPDATE buttonset SET sort_order = 1600 WHERE NAME = 'Freaks';
UPDATE buttonset SET sort_order = 1700 WHERE NAME = 'Balticon 34';
UPDATE buttonset SET sort_order = 1800 WHERE NAME = '2000 ShoreCon';
UPDATE buttonset SET sort_order = 1900 WHERE NAME = 'Las Vegas';
UPDATE buttonset SET sort_order = 2000 WHERE NAME = 'Fantasy';
UPDATE buttonset SET sort_order = 2100 WHERE NAME = 'Save The Ogres';
UPDATE buttonset SET sort_order = 2200 WHERE NAME = 'Presidential';
UPDATE buttonset SET sort_order = 2300 WHERE NAME = 'Majesty';
UPDATE buttonset SET sort_order = 2400 WHERE NAME = 'Wonderland';
UPDATE buttonset SET sort_order = 2500 WHERE NAME = 'Fairies';
UPDATE buttonset SET sort_order = 2600 WHERE NAME = 'Dork Victory';
UPDATE buttonset SET sort_order = 2700 WHERE NAME = 'Japanese Beetle';
UPDATE buttonset SET sort_order = 2800 WHERE NAME = 'Howling Wolf';
UPDATE buttonset SET sort_order = 2900 WHERE NAME = 'Metamorphers';
UPDATE buttonset SET sort_order = 3000 WHERE NAME = 'Sailor Moon 2';
UPDATE buttonset SET sort_order = 3100 WHERE NAME = 'Bruno';
UPDATE buttonset SET sort_order = 3200 WHERE NAME = 'Tenchi Muyo!';
UPDATE buttonset SET sort_order = 3300 WHERE NAME = 'Sluggy Freelance';
UPDATE buttonset SET sort_order = 3400 WHERE NAME = 'Everything to Gain';
UPDATE buttonset SET sort_order = 3500 WHERE NAME = 'Yoyodyne';
UPDATE buttonset SET sort_order = 3600 WHERE NAME = 'Samurai';
UPDATE buttonset SET sort_order = 3700 WHERE NAME = 'SydCon 10';
UPDATE buttonset SET sort_order = 3800 WHERE NAME = 'Demicon the 13th';
UPDATE buttonset SET sort_order = 3900 WHERE NAME = 'SFR';
UPDATE buttonset SET sort_order = 4000 WHERE NAME = 'Chicagoland Gamers Conclave';
UPDATE buttonset SET sort_order = 4100 WHERE NAME = 'Diceland';
UPDATE buttonset SET sort_order = 4200 WHERE NAME = 'Renaissance';
UPDATE buttonset SET sort_order = 4300 WHERE NAME = '2002 Anime Expo';
UPDATE buttonset SET sort_order = 4400 WHERE NAME = '2002 Origins';
UPDATE buttonset SET sort_order = 4500 WHERE NAME = 'Bar Mitzvah';
UPDATE buttonset SET sort_order = 4600 WHERE NAME = '2003 Rare-Promos';
UPDATE buttonset SET sort_order = 4700 WHERE NAME = 'Button Brains';
UPDATE buttonset SET sort_order = 4800 WHERE NAME = 'Fightball';
UPDATE buttonset SET sort_order = 4900 WHERE NAME = 'Nodwick';
UPDATE buttonset SET sort_order = 5000 WHERE NAME = '2004 Origins';
UPDATE buttonset SET sort_order = 5100 WHERE NAME = 'Geekz';
UPDATE buttonset SET sort_order = 5200 WHERE NAME = 'Kubla Con';
UPDATE buttonset SET sort_order = 5300 WHERE NAME = 'Space Girlz';
UPDATE buttonset SET sort_order = 5400 WHERE NAME = 'Bridge and Tunnel';
UPDATE buttonset SET sort_order = 5500 WHERE NAME = '2005 Rare Promo';
UPDATE buttonset SET sort_order = 5600 WHERE NAME = 'High School Drama!';
UPDATE buttonset SET sort_order = 5700 WHERE NAME = 'Unexploded Cow';
UPDATE buttonset SET sort_order = 5800 WHERE NAME = 'ZOECon';
UPDATE buttonset SET sort_order = 5900 WHERE NAME = 'Iron Chef';
UPDATE buttonset SET sort_order = 6000 WHERE NAME = '7 deadly sins';
UPDATE buttonset SET sort_order = 6100 WHERE NAME = 'Chicago Crew';
UPDATE buttonset SET sort_order = 6200 WHERE NAME = 'Four Horsemen';
UPDATE buttonset SET sort_order = 6300 WHERE NAME = 'Free Radicals';
UPDATE buttonset SET sort_order = 6400 WHERE NAME = 'Hodge Podge';
UPDATE buttonset SET sort_order = 6500 WHERE NAME = 'Victorian Horror';
UPDATE buttonset SET sort_order = 6600 WHERE NAME = 'Cowboy Bebop';
UPDATE buttonset SET sort_order = 6700 WHERE NAME = '50 States';
UPDATE buttonset SET sort_order = 6800 WHERE NAME = 'Japanese Beetle (unofficial)';
UPDATE buttonset SET sort_order = 6900 WHERE NAME = 'Blademasters';
UPDATE buttonset SET sort_order = 7000 WHERE NAME = 'Order of the Dolls';
UPDATE buttonset SET sort_order = 7100 WHERE NAME = 'Blademasters: The Second Shot';
UPDATE buttonset SET sort_order = 7200 WHERE NAME = 'Blademasters: Third Attack';
UPDATE buttonset SET sort_order = 7300 WHERE NAME = 'Gaming Guardians';
UPDATE buttonset SET sort_order = 7400 WHERE NAME = 'MegaTokyo';
UPDATE buttonset SET sort_order = 100000 WHERE NAME = 'Classic Fanatics';
