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
    ('3', 'Features and Bugs', '#f0d0d0', '#f7e7e7', 'Feedback on new features that have been added, features you\'d like to see or bugs you\'ve discovered.', '60'),
    ('4', 'Tournaments', '#d7f0d7', '#ebf7eb', 'Button men tournaments, both official and unofficial.', '80');
