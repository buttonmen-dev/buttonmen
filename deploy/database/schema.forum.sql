# Table schemas for forum-related tables

DROP TABLE IF EXISTS forum_board;
CREATE TABLE forum_board (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    sort_order TINYINT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS forum_thread;
CREATE TABLE forum_thread (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    board_id TINYINT UNSIGNED NOT NULL,
    title VARCHAR(100) NOT NULL,
    deleted BIT NOT NULL DEFAULT 0,
    INDEX (board_id),
    FOREIGN KEY (board_id) REFERENCES forum_board(id)
);

DROP TABLE IF EXISTS forum_post;
CREATE TABLE forum_post(
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    thread_id SMALLINT UNSIGNED NOT NULL,
    poster_player_id SMALLINT UNSIGNED NOT NULL,
    creation_time TIMESTAMP NOT NULL,
    last_update_time TIMESTAMP NOT NULL,
    body TEXT NOT NULL,
    deleted BIT NOT NULL DEFAULT 0,
    INDEX (thread_id),
    FOREIGN KEY (thread_id) REFERENCES forum_thread(id),
    FOREIGN KEY (poster_player_id) REFERENCES player(id)
);

DROP TABLE IF EXISTS forum_board_player_map;
CREATE TABLE forum_board_player_map(
    board_id TINYINT UNSIGNED NOT NULL,
    player_id SMALLINT UNSIGNED NOT NULL,
    read_time TIMESTAMP,
    PRIMARY KEY (board_id, player_id),
    FOREIGN KEY (board_id) REFERENCES forum_board(id),
    FOREIGN KEY (player_id) REFERENCES player(id)
);

DROP TABLE IF EXISTS forum_thread_player_map;
CREATE TABLE forum_thread_player_map(
    thread_id SMALLINT UNSIGNED NOT NULL,
    player_id SMALLINT UNSIGNED NOT NULL,
    read_time TIMESTAMP NOT NULL,
    PRIMARY KEY (thread_id, player_id),
    FOREIGN KEY (thread_id) REFERENCES forum_thread(id),
    FOREIGN KEY (player_id) REFERENCES player(id)
);
