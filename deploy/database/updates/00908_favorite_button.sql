ALTER TABLE player
    ADD COLUMN favorite_button_id SMALLINT UNSIGNED NULL AFTER comment,
    ADD COLUMN favorite_buttonset_id SMALLINT UNSIGNED NULL AFTER favorite_button_id,
    ADD FOREIGN KEY (favorite_button_id) REFERENCES button(id),
    ADD FOREIGN KEY (favorite_buttonset_id) REFERENCES buttonset(id);
