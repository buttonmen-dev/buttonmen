ALTER TABLE player
    ADD COLUMN automatically_monitor BOOLEAN DEFAULT 0 NOT NULL AFTER monitor_redirects_to_forum;

