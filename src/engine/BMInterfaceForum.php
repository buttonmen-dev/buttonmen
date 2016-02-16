<?php

/**
 * BMInterfaceForum: interface between GUI and BMGame for forum-related requests
 *
 * @author james
 */

/**
 * This class deals with communication between the UI, the game code, and the database
 * pertaining to forum-related information
 */

class BMInterfaceForum extends BMInterface {
    /**
     * Retrieves an overview of all of the boards available on the forum
     *
     * @param int $currentPlayerId
     * @return array|NULL
     */
    public function load_forum_overview($currentPlayerId) {
        try {
            $results = array();

            // Get the list of all boards, identifying the first new post on each
            $query =
                'SELECT ' .
                    'b_plus.*, ' .
                    'COUNT(t.id) AS number_of_threads, ' .
                    'first_new_post.thread_id AS first_new_post_thread_id ' .
                'FROM ' .
                    '(SELECT ' .
                        'b.*, ' .
                        '(SELECT v.id FROM forum_player_post_view AS v ' .
                        'WHERE v.board_id = b.id AND v.reader_player_id = :current_player_id AND v.is_new = 1 ' .
                        'ORDER BY v.creation_time ASC LIMIT 1) AS first_new_post_id ' .
                    'FROM forum_board AS b) AS b_plus ' .
                    'LEFT JOIN forum_thread AS t ' .
                        'ON t.board_id = b_plus.id AND t.deleted = 0 ' .
                    'LEFT JOIN forum_post AS first_new_post ' .
                        'ON first_new_post.id = b_plus.first_new_post_id ' .
                'GROUP BY b_plus.id ' .
                'ORDER BY b_plus.sort_order ASC;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':current_player_id' => $currentPlayerId));

            $boards = array();
            while ($row = $statement->fetch()) {
                $boards[] = array(
                    'boardId' => (int)$row['id'],
                    'boardName' => $row['name'],
                    'boardColor' => $row['board_color'],
                    'threadColor' => $row['thread_color'],
                    'description' => $row['description'],
                    'numberOfThreads' => (int)$row['number_of_threads'],
                    'firstNewPostId' => (int)$row['first_new_post_id'],
                    'firstNewPostThreadId' => (int)$row['first_new_post_thread_id'],
                );
            }

            $results['boards'] = $boards;
            $results['timestamp'] = strtotime('now');

            if ($results) {
                $this->set_message('Forum overview loading succeeded');
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_forum_overview: ' .
                $e->getMessage()
            );
            $this->set_message('Forum overview loading failed');
            return NULL;
        }
    }

    /**
     * Retrieves an overview of a specific forum board, plus information on
     * all the threads on that board
     *
     * @param int $currentPlayerId
     * @param int $boardId
     * @return array|NULL
     */
    public function load_forum_board($currentPlayerId, $boardId) {
        try {
            $results = array();

            // Get the details about the board itself
            $query =
                'SELECT b.* ' .
                'FROM forum_board AS b ' .
                'WHERE b.id = :board_id';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':board_id' => $boardId));

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) != 1) {
                $this->set_message('Forum board loading failed');
                error_log('Wrong number of records returned for forum_board.id = ' . $boardId);
                return NULL;
            }
            $results['boardId'] = (int)$fetchResult[0]['id'];
            $results['boardName'] = $fetchResult[0]['name'];
            $results['boardColor'] = $fetchResult[0]['board_color'];
            $results['threadColor'] = $fetchResult[0]['thread_color'];
            $results['description'] = $fetchResult[0]['description'];

            // Get a list of threads on this board, with info on their old and new posts
            $query =
                'SELECT ' .
                    't_plus.*, ' .
                    'COUNT(all_posts.id) AS number_of_posts, ' .
                    'first_post_poster.name_ingame AS original_poster_name, ' .
                    'UNIX_TIMESTAMP(first_post.creation_time) AS original_creation_timestamp, ' .
                    'lastest_post_poster.name_ingame AS latest_poster_name, ' .
                    'UNIX_TIMESTAMP(lastest_post.last_update_time) AS latest_update_timestamp, ' .
                    't_plus.first_new_post_id ' .
                'FROM ' .
                    '(SELECT ' .
                        't.*, ' .
                        '(SELECT post.id FROM forum_post AS post ' .
                        'WHERE post.thread_id = t.id ' .
                        'ORDER BY post.creation_time ASC LIMIT 1) AS first_post_id, ' .
                        '(SELECT post.id FROM forum_post AS post ' .
                        'WHERE post.thread_id = t.id ' .
                        'ORDER BY post.last_update_time DESC LIMIT 1) AS lastest_post_id, ' .
                        '(SELECT v.id FROM forum_player_post_view AS v ' .
                        'WHERE v.thread_id = t.id AND v.reader_player_id = :current_player_id AND v.is_new = 1 ' .
                        'ORDER BY v.creation_time ASC LIMIT 1) AS first_new_post_id ' .
                    'FROM forum_thread AS t ' .
                    'WHERE t.board_id = :board_id AND t.deleted = 0) AS t_plus ' .
                    'LEFT JOIN forum_post AS all_posts ' .
                        'ON all_posts.thread_id = t_plus.id ' .
                    'LEFT JOIN forum_post AS first_post ' .
                        'ON first_post.id = t_plus.first_post_id ' .
                    'LEFT JOIN player AS first_post_poster ' .
                        'ON first_post_poster.id = first_post.poster_player_id ' .
                    'LEFT JOIN forum_post AS lastest_post ' .
                        'ON lastest_post.id = t_plus.lastest_post_id ' .
                    'LEFT JOIN player AS lastest_post_poster ' .
                        'ON lastest_post_poster.id = lastest_post.poster_player_id ' .
                'GROUP BY t_plus.id ' .
                'ORDER BY lastest_post.last_update_time DESC';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':current_player_id' => $currentPlayerId,
                ':board_id' => $boardId,
            ));

            $threads = array();
            while ($row = $statement->fetch()) {
                $threads[] = array(
                    'threadId' => $row['id'],
                    'threadTitle' => $row['title'],
                    'numberOfPosts' => (int)$row['number_of_posts'],
                    'originalPosterName' => $row['original_poster_name'],
                    'originalCreationTime' => (int)$row['original_creation_timestamp'],
                    'latestPosterName' => $row['latest_poster_name'],
                    'latestLastUpdateTime' => (int)$row['latest_update_timestamp'],
                    'firstNewPostId' => (int)$row['first_new_post_id'],
                );
            }

            $results['threads'] = $threads;
            $results['timestamp'] = strtotime('now');

            if ($results) {
                $this->set_message('Forum board loading succeeded');
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_forum_board: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Retrieves an overview of a specific forum thread, plus information on
     * the posts in that thread
     *
     * @param int $currentPlayerId
     * @param int $threadId
     * @param int $currentPostId
     * @return array|NULL
     */
    public function load_forum_thread($currentPlayerId, $threadId, $currentPostId) {
        try {
            $results = array();

            $playerColors = $this->load_player_colors($currentPlayerId);

            // Get the details about the thread itself
            $query =
                'SELECT t.*, b.name AS board_name, b.board_color, b.thread_color AS board_thread_color ' .
                'FROM forum_thread AS t ' .
                    'INNER JOIN forum_board AS b ON b.id = t.board_id ' .
                'WHERE t.id = :thread_id AND t.deleted = 0;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':thread_id' => $threadId));

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) != 1) {
                $this->set_message('Forum thread loading failed');
                error_log('Wrong number of records returned for forum_thread.id = ' . $threadId);
                return NULL;
            }
            $results['threadId'] = (int)$fetchResult[0]['id'];
            $results['threadTitle'] = $fetchResult[0]['title'];
            $results['boardId'] = (int)$fetchResult[0]['board_id'];
            $results['boardName'] = $fetchResult[0]['board_name'];
            $results['boardColor'] = $fetchResult[0]['board_color'];
            $results['boardThreadColor'] = $fetchResult[0]['board_thread_color'];
            $results['currentPostId'] = $currentPostId;

            // Get a list of posts in this thread
            $query =
                'SELECT ' .
                    'v.*, ' .
                    'UNIX_TIMESTAMP(v.creation_time) AS creation_timestamp, ' .
                    'UNIX_TIMESTAMP(v.last_update_time) AS last_update_timestamp ' .
                'FROM forum_player_post_view v ' .
                'WHERE v.thread_id = :thread_id AND v.reader_player_id = :current_player_id ' .
                'ORDER BY v.creation_time ASC;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':current_player_id' => $currentPlayerId,
                ':thread_id' => $threadId,
            ));

            $posts = array();
            while ($row = $statement->fetch()) {
                $posterColor =
                    $this->determine_game_colors(
                        $currentPlayerId,
                        $playerColors,
                        (int)$row['poster_player_id'],
                        NULL
                    );
                $posts[] = array(
                    'postId' => (int)$row['id'],
                    'posterName' => $row['poster_name'],
                    'posterColor' => $posterColor['playerA'],
                    'creationTime' => (int)$row['creation_timestamp'],
                    'lastUpdateTime' => (int)$row['last_update_timestamp'],
                    'isNew' => ($row['is_new'] == 1),
                    'body' => (($row['deleted'] == 1) ? '[DELETED POST]' : $row['body']),
                    'deleted' => ($row['deleted'] == 1),
                );
            }

            $results['posts'] = $posts;
            $results['timestamp'] = strtotime('now');

            if ($results) {
                $this->set_message('Forum thread loading succeeded');
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_forum_thread: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Load the ID's of the next new post and its thread
     *
     * @param int $currentPlayerId
     * @return array|NULL
     */
    public function get_next_new_post($currentPlayerId) {
        try {
            $results = array();

            // Get the list of all boards, identifying the first new post on each
            $query =
                'SELECT v.id, v.thread_id ' .
                'FROM forum_player_post_view AS v ' .
                'WHERE v.reader_player_id = :current_player_id AND v.is_new = 1 ' .
                'ORDER BY v.creation_time ASC ' .
                'LIMIT 1;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':current_player_id' => $currentPlayerId));

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) != 1) {
                $results['nextNewPostId'] = NULL;
                $results['nextNewPostThreadId'] = NULL;
                $this->set_message('No new forum posts');
                return $results;
            }

            $results['nextNewPostId'] = (int)$fetchResult[0]['id'];
            $results['nextNewPostThreadId'] = (int)$fetchResult[0]['thread_id'];

            if ($results) {
                $this->set_message('Checked new forum posts successfully');
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_next_new_post: ' .
                $e->getMessage()
            );
            $this->set_message('New forum post check failed');
            return NULL;
        }
    }

    /**
     * Indicates that the reader has finished reading all of the posts on every
     * board which they care to read
     *
     * @param int $currentPlayerId
     * @param int $timestamp
     * @return array|NULL
     */
    public function mark_forum_read($currentPlayerId, $timestamp) {
        try {
            $query = 'SELECT b.id FROM forum_board AS b;';

            $statement = self::$conn->prepare($query);
            $statement->execute();

            while ($row = $statement->fetch()) {
                $boardId = (int)$row['id'];
                $results = $this->mark_forum_board_read($currentPlayerId, $boardId, $timestamp, TRUE);
                if (!$results || !$results['success']) {
                    $this->set_message('Marking board ' . $boardId . ' read failed: ' . $this->message);
                    return NULL;
                }
            }

            $this->set_message('Entire forum marked read successfully');
            return $this->load_forum_overview($currentPlayerId);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::mark_forum_read: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Indicates that the reader has finished reading all of the posts on this
     * board which they care to read
     *
     * @param int $currentPlayerId
     * @param int $boardId
     * @param int $timestamp
     * @param bool $suppressResults
     * @return array|NULL
     */
    public function mark_forum_board_read($currentPlayerId, $boardId, $timestamp, $suppressResults = FALSE) {
        try {
            $query =
                'INSERT INTO forum_board_player_map ' .
                    '(board_id, player_id, read_time) ' .
                'VALUES ' .
                    '(:board_id, :current_player_id, FROM_UNIXTIME(:timestamp_insert)) ' .
                'ON DUPLICATE KEY UPDATE read_time = FROM_UNIXTIME(:timestamp_update);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':board_id' => $boardId,
                ':current_player_id' => $currentPlayerId,
                ':timestamp_insert' => $timestamp,
                ':timestamp_update' => $timestamp,
            ));

            $this->set_message('Forum board marked read successfully');
            if ($suppressResults) {
                return array('success' => TRUE);
            } else {
                return $this->load_forum_overview($currentPlayerId);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::mark_forum_board_read: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Indicates that the reader has finished reading all of the posts in this
     * thread which they care to read
     *
     * @param int $currentPlayerId
     * @param int $threadId
     * @param int $boardId
     * @param int $timestamp
     * @return array|NULL
     */
    public function mark_forum_thread_read($currentPlayerId, $threadId, $boardId, $timestamp) {
        try {
            $query =
                'INSERT INTO forum_thread_player_map ' .
                    '(thread_id, player_id, read_time) ' .
                'VALUES (:thread_id, :current_player_id, FROM_UNIXTIME(:timestamp_insert)) ' .
                'ON DUPLICATE KEY UPDATE read_time = FROM_UNIXTIME(:timestamp_update);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':thread_id' => $threadId,
                ':current_player_id' => $currentPlayerId,
                ':timestamp_insert' => $timestamp,
                ':timestamp_update' => $timestamp,
            ));

            $this->set_message('Forum thread marked read successfully');
            return $this->load_forum_board($currentPlayerId, $boardId);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::mark_forum_thread_read: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Adds a new thread to the specified board
     *
     * @param int $currentPlayerId
     * @param int $boardId
     * @param string $title
     * @param string $body
     * @return array|NULL
     */
    public function create_forum_thread($currentPlayerId, $boardId, $title, $body) {
        try {
            $query =
                'INSERT INTO forum_thread (board_id, title, deleted) ' .
                'VALUES (:board_id, :title, 0);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':board_id' => $boardId,
                ':title' => $title,
            ));

            $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
            $statement->execute();
            $fetchData = $statement->fetch();
            $threadId = (int)$fetchData[0];

            $query =
                'INSERT INTO forum_post ' .
                    '(thread_id, poster_player_id, creation_time, last_update_time, body, deleted) ' .
                'VALUES ' .
                    '(:thread_id, :current_player_id, NOW(), NOW(), :body, 0);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':thread_id' => $threadId,
                ':current_player_id' => $currentPlayerId,
                ':body' => $body,
            ));

            $this->set_message('Forum thread created successfully');
            return $this->load_forum_thread($currentPlayerId, $threadId, NULL);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::create_forum_thread: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Adds a new post to the specified thread
     *
     * @param int $currentPlayerId
     * @param int $threadId
     * @param string $body
     * @return array|NULL
     */
    public function create_forum_post($currentPlayerId, $threadId, $body) {
        try {
            $query =
                'INSERT INTO forum_post ' .
                    '(thread_id, poster_player_id, creation_time, last_update_time, body, deleted) ' .
                'VALUES ' .
                    '(:thread_id, :current_player_id, NOW(), NOW(), :body, 0);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':thread_id' => $threadId,
                ':current_player_id' => $currentPlayerId,
                ':body' => $body,
            ));

            $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
            $statement->execute();
            $fetchData = $statement->fetch();
            $postId = (int)$fetchData[0];

            $results = $this->load_forum_thread($currentPlayerId, $threadId, $postId);

            if ($results) {
                $this->set_message('Forum post created successfully');
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::create_forum_post: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Changes the body of the specified post
     *
     * @param int $currentPlayerId
     * @param int $postId
     * @param string $body
     * @return array|NULL
     */
    public function edit_forum_post($currentPlayerId, $postId, $body) {
        try {
            $query =
                'SELECT p.poster_player_id, p.deleted, p.thread_id ' .
                'FROM forum_post p ' .
                'WHERE p.id = :post_id;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':post_id' => $postId));

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) != 1) {
                $this->set_message('Post not found');
                return NULL;
            }
            if ((int)$fetchResult[0]['poster_player_id'] != $currentPlayerId) {
                $this->set_message('Post does not belong to you');
                return NULL;
            }
            if ((int)$fetchResult[0]['deleted'] == 1) {
                $this->set_message('Post was already deleted');
                return NULL;
            }
            $threadId = (int)$fetchResult[0]['thread_id'];

            $query =
                'UPDATE forum_post ' .
                'SET body = :body, last_update_time = NOW() ' .
                'WHERE id = :post_id;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':post_id' => $postId,
                ':body' => $body,
            ));

            $results = $this->load_forum_thread($currentPlayerId, $threadId, $postId);

            if ($results) {
                $this->set_message('Forum post edited successfully');
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::edit_forum_post: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }
}
