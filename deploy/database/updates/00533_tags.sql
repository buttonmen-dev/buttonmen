CREATE TABLE tag (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE button_tag_map(
    button_id SMALLINT UNSIGNED NOT NULL,
    tag_id SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (button_id, tag_id),
    FOREIGN KEY (button_id) REFERENCES button(id),
    FOREIGN KEY (tag_id) REFERENCES tag(id)
);
