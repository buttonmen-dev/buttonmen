# Alter table definitions for button-related tables
# to remove auto-increment

LOCK TABLES
    button WRITE,
    buttonset WRITE,
    tag WRITE,
    button_tag_map WRITE,
    player WRITE;

ALTER TABLE button_tag_map
     DROP FOREIGN KEY button_tag_map_ibfk_2,
     DROP FOREIGN KEY button_tag_map_ibfk_1;

ALTER TABLE player
     DROP FOREIGN KEY player_ibfk_3,
     DROP FOREIGN KEY player_ibfk_2,
     DROP FOREIGN KEY player_ibfk_1;

ALTER TABLE buttonset
  MODIFY COLUMN
    id          SMALLINT UNSIGNED;

ALTER TABLE button
  MODIFY COLUMN
    id          SMALLINT UNSIGNED;

ALTER TABLE tag
  MODIFY COLUMN
    id          SMALLINT UNSIGNED;
 
ALTER TABLE button_tag_map
     ADD CONSTRAINT   fk_tagged_button_id 
         FOREIGN KEY  (button_id)
         REFERENCES   button(id),
     ADD CONSTRAINT   fk_tag_id 
         FOREIGN KEY  (tag_id)
         REFERENCES   tag(id);

ALTER TABLE player
     ADD CONSTRAINT   fk_fav_button_id 
         FOREIGN KEY  (favorite_button_id)
         REFERENCES   button(id),
     ADD CONSTRAINT   fk_fav_set_id 
         FOREIGN KEY  (favorite_buttonset_id)
         REFERENCES   buttonset(id),
     ADD CONSTRAINT   fk_player_status_id 
         FOREIGN KEY  (status_id)
         REFERENCES   player_status(id);

UNLOCK TABLES;
