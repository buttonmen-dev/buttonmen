DROP TABLE IF EXISTS player;
CREATE TABLE player (
    id                  SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_ingame         VARCHAR(25) UNIQUE NOT NULL,
    password_hashed     VARCHAR(128),
    name_irl            VARCHAR(40) NOT NULL,
    email               VARCHAR(254),
    status              ENUM('active', 'unverified', 'disabled'),
    dob                 DATE,
    autopass            BOOLEAN DEFAULT 0,
    image_path          VARCHAR(100),
    comment             VARCHAR(255),
    last_action_time    TIMESTAMP DEFAULT 0,
    creation_time       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fanatic_button_id   SMALLINT UNSIGNED,
    n_games_won         SMALLINT UNSIGNED DEFAULT 0,
    n_games_lost        SMALLINT UNSIGNED DEFAULT 0,
    INDEX (name_ingame)
);

DROP TABLE IF EXISTS player_auth;
CREATE TABLE player_auth (
    id        SMALLINT UNSIGNED PRIMARY KEY,
    auth_key  VARCHAR(253) UNIQUE NOT NULL
);
