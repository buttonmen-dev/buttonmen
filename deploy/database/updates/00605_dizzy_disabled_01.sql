INSERT INTO die_status (name) VALUES ('DIZZY');

UPDATE die SET status_id = (SELECT id FROM die_status WHERE name = 'DIZZY')
WHERE status_id = (SELECT id FROM die_status WHERE name = 'DISABLED')
AND recipe LIKE '%f%(%)%';

UPDATE die SET status_id = (SELECT id FROM die_status WHERE name = 'DIZZY')
WHERE status_id = (SELECT id FROM die_status WHERE name = 'DISABLED')
AND recipe LIKE '%(%)%f%';
