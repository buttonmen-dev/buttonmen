# add forum schemas, data and views


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
    title VARCHAR(100) NOT NULL,
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


# Prepopulated data for forum-related tables

# Delete order and insert order are the reverse of each other because of the
# foreign keys.

DELETE FROM forum_thread_player_map;
DELETE FROM forum_board_player_map;
DELETE FROM forum_post;
DELETE FROM forum_thread;
DELETE FROM forum_board;

INSERT INTO forum_board
    (id, name, board_color, thread_color, description, sort_order)
VALUES
    ('1', 'Miscellaneous Chatting', '#d0e0f0', '#e7f0f7', 'Any topic that doesn\'t fit anywhere else.', '20'),
    ('2', 'Gameplay', '#f0f0c0', '#f7f7e0', 'Button Men itself: sharing strategies, comparing buttons and skills, etc.', '40'),
    ('3', 'Features and Bugs', '#f0d0d0', '#f7e7e7', 'Feedback on new features that have been added, features you\'d like to see or bugs you\'ve discovered.', '60');


# Views for forum-related tables

DROP VIEW IF EXISTS forum_player_post_view;
CREATE VIEW forum_player_post_view
AS SELECT
    post.*,
    poster.name_ingame AS poster_name,
    thread.board_id AS board_id,
    reader.id AS reader_player_id,
    (post.last_update_time >= GREATEST(COALESCE(tpm.read_time, 0), COALESCE(bpm.read_time, 0)) AND
        reader.id != poster.id AND post.deleted = 0) AS is_new
FROM forum_post AS post
    INNER JOIN player AS poster ON poster.id = post.poster_player_id
    INNER JOIN forum_thread AS thread ON thread.id = post.thread_id AND thread.deleted = 0
    INNER JOIN player AS reader
    LEFT JOIN forum_thread_player_map AS tpm ON reader.id = tpm.player_id AND tpm.thread_id = post.thread_id
    LEFT JOIN forum_board_player_map AS bpm ON reader.id = bpm.player_id AND bpm.board_id = thread.board_id;
