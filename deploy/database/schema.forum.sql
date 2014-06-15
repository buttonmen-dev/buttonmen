# Table schemas for forum-related tables

# Drop order and create order are the reverse of each other because of the
# foreign keys.

DROP TABLE IF EXISTS forum_thread_player_map;
DROP TABLE IF EXISTS forum_board_player_map;
DROP TABLE IF EXISTS forum_post;
DROP TABLE IF EXISTS forum_thread;
DROP TABLE IF EXISTS forum_board;

CREATE TABLE forum_board (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    board_color VARCHAR(7) NOT NULL,
    thread_color VARCHAR(7) NOT NULL,
    description VARCHAR(255),
    sort_order TINYINT UNSIGNED NOT NULL
);

CREATE TABLE forum_thread (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    board_id SMALLINT UNSIGNED NOT NULL,
    -- The title of a forum thread is limited to 100 Unicode characters (see
    -- FORUM_TITLE_MAX_LENGTH in BMInterface and Forum.js). Since a Unicode
    -- character can be up to four bytes, this requires a varchar(400).
    title VARCHAR(400) NOT NULL,
    deleted BIT NOT NULL DEFAULT 0,
    INDEX (board_id),
    FOREIGN KEY (board_id) REFERENCES forum_board(id)
);

CREATE TABLE forum_post(
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    thread_id MEDIUMINT UNSIGNED NOT NULL,
    poster_player_id SMALLINT UNSIGNED NOT NULL,
    creation_time TIMESTAMP NOT NULL DEFAULT '0000-00-00',
    last_update_time TIMESTAMP NOT NULL DEFAULT '0000-00-00',
    -- The body of a forum thread is limited to 16000 Unicode characters (see
    -- FORUM_BODY_MAX_LENGTH in BMInterface and Forum.js). Since a Unicode
    -- character can be up to four bytes, this requires at least 64000 bytes of
    -- storage. A TEXT field provides 65535 bytes.
    body TEXT NOT NULL,
    deleted BIT NOT NULL DEFAULT 0,
    INDEX (thread_id),
    FOREIGN KEY (thread_id) REFERENCES forum_thread(id),
    FOREIGN KEY (poster_player_id) REFERENCES player(id)
);

CREATE TABLE forum_board_player_map(
    board_id SMALLINT UNSIGNED NOT NULL,
    player_id SMALLINT UNSIGNED NOT NULL,
    read_time TIMESTAMP DEFAULT '0000-00-00',
    PRIMARY KEY (board_id, player_id),
    FOREIGN KEY (board_id) REFERENCES forum_board(id),
    FOREIGN KEY (player_id) REFERENCES player(id)
);

CREATE TABLE forum_thread_player_map(
    thread_id MEDIUMINT UNSIGNED NOT NULL,
    player_id SMALLINT UNSIGNED NOT NULL,
    read_time TIMESTAMP DEFAULT '0000-00-00',
    PRIMARY KEY (thread_id, player_id),
    FOREIGN KEY (thread_id) REFERENCES forum_thread(id),
    FOREIGN KEY (player_id) REFERENCES player(id)
);
