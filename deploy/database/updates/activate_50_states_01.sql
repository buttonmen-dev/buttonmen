UPDATE button
SET btn_special=0
WHERE set_id=(SELECT id FROM buttonset WHERE name='50 States');

UPDATE button
SET btn_special=1
WHERE name='Arizona';

UPDATE button
SET btn_special=1
WHERE name='Hawaii';

UPDATE button
SET btn_special=1
WHERE name='Iowa';

