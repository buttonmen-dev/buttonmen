ALTER TABLE player
    ADD COLUMN image_size SMALLINT NULL AFTER image_path,
    ADD COLUMN uses_gravatar BOOLEAN DEFAULT 0 NOT NULL AFTER image_size;
