CREATE TABLE player_status (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL
);

CREATE TABLE player (
    id                  SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_ingame         VARCHAR(25) UNIQUE NOT NULL,
    password_hashed     VARCHAR(255),
    name_irl            VARCHAR(40) NOT NULL,
    email               VARCHAR(254),
    is_email_public     BOOLEAN DEFAULT 0 NOT NULL,
    status_id           TINYINT UNSIGNED NOT NULL,
    dob_month           INT DEFAULT 0 NOT NULL,
    dob_day             INT DEFAULT 0 NOT NULL,
    gender              VARCHAR(100) DEFAULT '' NOT NULL,
    autoaccept          BOOLEAN DEFAULT 0,
    autopass            BOOLEAN DEFAULT 0,
    fire_overshooting   BOOLEAN DEFAULT 0,
    monitor_redirects_to_game   BOOLEAN DEFAULT 0 NOT NULL,
    monitor_redirects_to_forum  BOOLEAN DEFAULT 0 NOT NULL,
    automatically_monitor       BOOLEAN DEFAULT 0 NOT NULL,
    image_path          VARCHAR(100),
    image_size          SMALLINT,
    uses_gravatar       BOOLEAN DEFAULT 0 NOT NULL,
    comment             VARCHAR(255),
    homepage            VARCHAR(100),
    favorite_button_id      SMALLINT UNSIGNED,
    favorite_buttonset_id   SMALLINT UNSIGNED,
    die_background      VARCHAR(10) DEFAULT 'circle',
    player_color        VARCHAR(7),
    opponent_color      VARCHAR(7),
    neutral_color_a     VARCHAR(7),
    neutral_color_b     VARCHAR(7),
    last_action_time    TIMESTAMP DEFAULT 0,
    last_access_time    TIMESTAMP DEFAULT 0,
    creation_time       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fanatic_button_id   SMALLINT UNSIGNED,
    n_games_won         SMALLINT UNSIGNED DEFAULT 0,
    n_games_lost        SMALLINT UNSIGNED DEFAULT 0,
    vacation_message	VARCHAR(255),
    INDEX (name_ingame),
    CONSTRAINT fk_fav_button_id
      FOREIGN KEY  (favorite_button_id)    REFERENCES button(id),
    CONSTRAINT fk_fav_set_id
      FOREIGN KEY  (favorite_buttonset_id) REFERENCES buttonset(id),
    CONSTRAINT fk_player_status_id
      FOREIGN KEY  (status_id)             REFERENCES player_status(id)
);

CREATE TABLE player_auth (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id  SMALLINT UNSIGNED,
    auth_key   VARCHAR(255) UNIQUE NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE player_verification (
    player_id        SMALLINT UNSIGNED PRIMARY KEY,
    verification_key VARCHAR(253) UNIQUE NOT NULL,
    ipaddr           VARCHAR(40),
    generation_time  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
