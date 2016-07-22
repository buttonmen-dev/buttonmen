INSERT INTO tag (name) VALUES ('exclude_from_random');

INSERT INTO button_tag_map (button_id, tag_id)
VALUES (
  (SELECT id FROM button WHERE name = 'Dead Guy'),
  (SELECT id FROM tag WHERE name = 'exclude_from_random')
);


