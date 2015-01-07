# VIEWS
DROP VIEW IF EXISTS forum_player_post_view;
DROP VIEW IF EXISTS open_game_possible_button_view;
DROP VIEW IF EXISTS game_player_view;
DROP VIEW IF EXISTS player_view;
DROP VIEW IF EXISTS button_view;

# FORUM SCHEMA
DROP TABLE IF EXISTS forum_thread_player_map;
DROP TABLE IF EXISTS forum_board_player_map;
DROP TABLE IF EXISTS forum_post;
DROP TABLE IF EXISTS forum_thread;
DROP TABLE IF EXISTS forum_board;

# GAME SCHEMA
DROP TABLE IF EXISTS last_attack;
DROP TABLE IF EXISTS last_attack_die_map;
DROP TABLE IF EXISTS tournament;
DROP TABLE IF EXISTS open_game_possible_buttonsets;
DROP TABLE IF EXISTS open_game_possible_buttons;
DROP TABLE IF EXISTS die_status;
DROP TABLE IF EXISTS die;
DROP TABLE IF EXISTS game_chat_log;
DROP TABLE IF EXISTS game_action_log;
DROP TABLE IF EXISTS game_option_map;
DROP TABLE IF EXISTS game_swing_map;
DROP TABLE IF EXISTS game_player_map;
DROP TABLE IF EXISTS game_status;
DROP TABLE IF EXISTS game;

# PLAYER SCHEMA
DROP TABLE IF EXISTS player_verification;
DROP TABLE IF EXISTS player_auth;
DROP TABLE IF EXISTS player;
DROP TABLE IF EXISTS player_status;

# BUTTON SCHEMA
DROP TABLE IF EXISTS button_tag_map;
DROP TABLE IF EXISTS tag;
DROP TABLE IF EXISTS button;
DROP TABLE IF EXISTS buttonset;

# CONFIG SCHEMA
DROP TABLE IF EXISTS config;