# Views for forum-related tables

DROP VIEW IF EXISTS forum_player_post_view;
CREATE VIEW forum_player_post_view
AS SELECT
    post.*,
    poster.name_ingame AS poster_name,
    thread.board_id AS board_id,
    reader.id AS reader_player_id,
    (post.last_update_time > GREATEST(COALESCE(tpm.read_time, 0), COALESCE(bpm.read_time, 0))) AS is_new
FROM forum_post AS post
    INNER JOIN player AS poster ON poster.id = post.poster_player_id
    INNER JOIN forum_thread AS thread ON thread.id = post.thread_id AND thread.deleted = 0
    INNER JOIN player AS reader
    LEFT JOIN forum_thread_player_map AS tpm ON reader.id = tpm.player_id AND tpm.thread_id = post.thread_id
    LEFT JOIN forum_board_player_map AS bpm ON reader.id = bpm.player_id AND bpm.board_id = thread.board_id
