# Table view definitions for button-related tables

DROP VIEW IF EXISTS button_view;
CREATE VIEW button_view
AS SELECT d.name, d.recipe, d.tourn_legal, d.image_path, s.name AS set_name
FROM button AS d
LEFT JOIN button_set AS s
ON d.set_id = s.id;
