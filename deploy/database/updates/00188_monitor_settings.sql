ALTER TABLE player
    ADD COLUMN `monitor_redirects_to_game` BOOLEAN DEFAULT 0 NOT NULL AFTER autopass,
    ADD COLUMN `monitor_redirects_to_forum` BOOLEAN DEFAULT 0 NOT NULL AFTER monitor_redirects_to_game;
