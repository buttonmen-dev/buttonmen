# Prepopulated data for forum-related tables

# Delete order and insert order are the reverse of each other because of the
# foreign keys.

DELETE FROM forum_thread_player_map;
DELETE FROM forum_board_player_map;
DELETE FROM forum_post;
DELETE FROM forum_thread;
DELETE FROM forum_board;

INSERT INTO forum_board
    (id, name, short_name, description, sort_order)
VALUES
    ('1', 'Miscellaneous Chatting', 'miscChat', 'Any topic that doesn\'t fit anywhere else.', '20'),
    ('2', 'Gameplay', 'gameplay', 'Button Men itself: sharing strategies, comparing buttons and skills, etc.', '40'),
    ('3', 'Features and Bugs', 'featureBug', 'Feedback on new features that have been added, features you\'d like to see or bugs you\'ve discovered.', '60');
