ALTER TABLE button
ADD COLUMN flavor_text VARCHAR(500) NULL AFTER set_id,

DROP VIEW IF EXISTS button_view;
CREATE VIEW button_view
AS SELECT b.name, b.recipe, b.tourn_legal, b.btn_special,
          b.flavor_text, s.name AS set_name
FROM button AS b
LEFT JOIN buttonset AS s
ON b.set_id = s.id
ORDER BY b.set_id, b.id;
