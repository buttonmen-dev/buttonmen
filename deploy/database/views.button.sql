# Table view definitions for button-related tables

DROP VIEW IF EXISTS button_view;
CREATE VIEW button_view
AS SELECT
    b.id,
    b.name,
    b.recipe,
    b.tourn_legal,
    b.btn_special,
    b.flavor_text,
    s.name AS set_name,
    s.sort_order AS set_sort_order,
    d.dhs_button_id,
    d.dhs_button_name
FROM button AS b
    LEFT JOIN buttonset AS s ON b.set_id = s.id
    LEFT JOIN dhs_button AS d ON d.bw_button_id = b.id
ORDER BY s.sort_order ASC, b.name ASC;
