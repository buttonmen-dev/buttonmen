# Table view definitions for button-related tables

DROP VIEW IF EXISTS button_view;
CREATE VIEW button_view
AS SELECT b.id, b.name, b.recipe, b.tourn_legal, b.btn_special,
          s.name AS set_name
FROM button AS b
LEFT JOIN buttonset AS s
ON b.set_id = s.id
ORDER BY b.set_id, b.id;
