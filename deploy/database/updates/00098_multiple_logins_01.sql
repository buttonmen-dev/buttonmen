# recreate the player_auth table with more fields
DROP TABLE IF EXISTS player_auth;
CREATE TABLE player_auth (
    id         SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id  SMALLINT UNSIGNED,
    auth_key   VARCHAR(253) UNIQUE NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
