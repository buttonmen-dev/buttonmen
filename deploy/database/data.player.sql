# Prepopulated data for player-related tables

DELETE FROM player;

DELETE FROM player_auth;

DELETE FROM player_verification;

DELETE FROM player_reset_verification;

DELETE FROM player_status;

INSERT INTO player_status (name) VALUES
('ACTIVE'),
('UNVERIFIED'),
('DISABLED');
