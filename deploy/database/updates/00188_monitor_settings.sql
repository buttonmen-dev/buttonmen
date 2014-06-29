ALTER TABLE player
    ADD COLUMN `monitorRedirectsToGame` BOOLEAN DEFAULT 0 NOT NULL AFTER autopass,
    ADD COLUMN `monitorRedirectsToForum` BOOLEAN DEFAULT 0 NOT NULL AFTER monitorRedirectsToGame;
