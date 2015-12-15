UPDATE button
SET btn_special=0
WHERE set_id=(SELECT id FROM buttonset WHERE name='50 States');
