DROP TABLE IF EXISTS player;
CREATE TABLE player (
    id                  SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_ingame         VARCHAR(25) UNIQUE NOT NULL,
    password_hashed     VARCHAR(128),
    name_irl            VARCHAR(40) NOT NULL,
    email               VARCHAR(254),
    is_email_public     BOOLEAN DEFAULT 0 NOT NULL,
    status              ENUM('active', 'unverified', 'disabled'),
    dob_month           INT DEFAULT 0 NOT NULL,
    dob_day             INT DEFAULT 0 NOT NULL,
    gender              VARCHAR(20),
    autopass            BOOLEAN DEFAULT 0,
    image_path          VARCHAR(100),
    image_size          SMALLINT,
    comment             VARCHAR(255),
    last_action_time    TIMESTAMP DEFAULT 0,
    last_access_time    TIMESTAMP DEFAULT 0,
    creation_time       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fanatic_button_id   SMALLINT UNSIGNED,
    n_games_won         SMALLINT UNSIGNED DEFAULT 0,
    n_games_lost        SMALLINT UNSIGNED DEFAULT 0,
    INDEX (name_ingame)
);

DROP TABLE IF EXISTS player_auth;
CREATE TABLE player_auth (
    id         SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id  SMALLINT UNSIGNED,
    auth_key   VARCHAR(253) UNIQUE NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS player_verification;
CREATE TABLE player_verification (
    player_id        SMALLINT UNSIGNED PRIMARY KEY,
    verification_key VARCHAR(253) UNIQUE NOT NULL,
    ipaddr           VARCHAR(40),
    generation_time  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
