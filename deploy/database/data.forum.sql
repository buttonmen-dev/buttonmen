# Prepopulated data for forum-related tables

DELETE FROM forum_board;
INSERT INTO forum_board
    (id, name, description, sort_order)
VALUES
    ('1', 'Miscellaneous Chatting', 'Any topic that doesn\'t fit anywhere else.', '20'),
    ('2', 'Features and Bugs', 'Feedback on new features that have been added, features you\'d like to see or bugs you\'ve discovered.', '40');

DELETE FROM forum_thread;

DELETE FROM forum_post;

DELETE FROM forum_board_player_map;

DELETE FROM forum_thread_player_map;
