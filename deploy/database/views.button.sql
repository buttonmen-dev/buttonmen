# Table view definitions for button-related tables

DROP VIEW IF EXISTS button_view;
CREATE VIEW button_view
AS SELECT b.name, b.recipe, b.tourn_legal, b.btn_special,
          b.flavor_text, s.name AS set_name, s.id AS set_id
FROM button AS b
LEFT JOIN buttonset AS s
ON b.set_id = s.id
ORDER BY b.set_id, b.id;
