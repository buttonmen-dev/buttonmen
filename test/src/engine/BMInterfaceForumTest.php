<?php

require_once __DIR__.'/BMInterfaceTestAbstract.php';

class BMInterfaceForumTest extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterfaceForum(TRUE);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceForum::load_forum_overview
     * @covers BMInterfaceForum::load_forum_board
     * @covers BMInterfaceForum::load_forum_thread
     * @covers BMInterfaceForum::create_forum_thread
     * @covers BMInterfaceForum::create_forum_post
     * @covers BMInterfaceForum::edit_forum_post
     */
    public function test_create_load_forum_posts() {
        $overview = $this->object->load_forum_overview(self::$userId1WithoutAutopass);
        $boardId = $overview['boards'][0]['boardId'];
        $originalThreadsOnBoard = $overview['boards'][0]['numberOfThreads'];

        $title = uniqid();
        $body1 = uniqid();
        $this->object->create_forum_thread(self::$userId1WithoutAutopass,
            $boardId, $title, $body1);

        $overview = $this->object->load_forum_overview(self::$userId1WithoutAutopass);
        $this->assertEquals(
            $originalThreadsOnBoard + 1,
            $overview['boards'][0]['numberOfThreads'],
            'Adding a new thread should increase the number of threads on the board by one.'
        );

        $board = $this->object->load_forum_board(self::$userId1WithoutAutopass,
            $boardId);
        $this->assertEquals($title, $board['threads'][0]['threadTitle'],
            'Newly-added thread should appear first on the board.');
        $threadId = $board['threads'][0]['threadId'];
        $originalPostsInThread = $board['threads'][0]['numberOfPosts'];

        $body2 = uniqid();
        $this->object->create_forum_post(self::$userId1WithoutAutopass,
            $threadId, $body2);

        $board = $this->object->load_forum_board(self::$userId1WithoutAutopass,
            $boardId);
        $this->assertEquals($title, $board['threads'][0]['threadTitle'],
            'Newly-updated thread should appear first on the board.');
        $this->assertEquals(
            $originalPostsInThread + 1,
            $board['threads'][0]['numberOfPosts'],
            'Adding a new post should increase the number of posts in the thread by one.'
        );

        $thread = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $threadId, 2);
        $this->assertEquals(2, $thread['currentPostId'],
            'Requested post ID should be returned.');
        $this->assertEquals($title, $thread['threadTitle'],
            'Thread should have the correct title.');
        $this->assertEquals($body1, $thread['posts'][0]['body'],
            'First post should have the correct body.');
        $this->assertEquals($body2, $thread['posts'][1]['body'],
            'Followup post should have the correct body.');

        $firstPostId = $thread['posts'][0]['postId'];
        $body3 = uniqid();
        $this->object->edit_forum_post(self::$userId1WithoutAutopass,
            $firstPostId, $body3);

        $thread = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $threadId, 2);
        $this->assertNotEquals($body1, $thread['posts'][0]['body'],
            'First post should not have the old body.');
        $this->assertEquals($body3, $thread['posts'][0]['body'],
            'First post should have the new body.');
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceForum::mark_forum_read
     * @covers BMInterfaceForum::mark_forum_board_read
     * @covers BMInterfaceForum::mark_forum_thread_read
     * @covers BMInterfaceForum::get_next_new_post
     */
    public function test_mark_forum_posts_read() {
        // First, the first player views the forum. Then the second player makes
        // several new threads: two on one board and one on another.
        $overview = $this->object->load_forum_overview(self::$userId1WithoutAutopass);
        $boardId1 = $overview['boards'][0]['boardId'];
        $boardId2 = $overview['boards'][1]['boardId'];

        $thread1 = $this->object->create_forum_thread(self::$userId2WithoutAutopass,
            $boardId1, 'Test Title 1', 'Test Body 1');
        // Separate the posts slightly, so that the first one is measurably first
        // (This is just because the DB timestamp granularity is only one second.)
        sleep(1);
        $thread2 = $this->object->create_forum_thread(self::$userId2WithoutAutopass,
            $boardId1, 'Test Title 2', 'Test Body 2');
        $thread3 = $this->object->create_forum_thread(self::$userId2WithoutAutopass,
            $boardId2, 'Test Title 3', 'Test Body 3');

        // Wait a moment, to ensure that the server's timestamp has a chance to
        // tick over.
        sleep(1);

        // Then the first player marks all boards as read, using the timestamp
        // from before the posts were made. Verify that, for the first player,
        // all three threads still start with a new post.
        $this->object->mark_forum_read(self::$userId1WithoutAutopass,
            $overview['timestamp']);

        $thread1 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertTrue($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertTrue($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertTrue($thread3['posts'][0]['isNew']);

        // Also, verify that none of these posts are new to the second player,
        // as they're the one who created them.
        $thread1 = $this->object->load_forum_thread(self::$userId2WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertFalse($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId2WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertFalse($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId2WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertFalse($thread3['posts'][0]['isNew']);

        // And verify that the first new post is indeed recognized as the
        // first new post
        $nextNewPost = $this->object->get_next_new_post(self::$userId1WithoutAutopass);
        $this->assertEquals($thread1['threadId'], $nextNewPost['nextNewPostThreadId']);
        $this->assertEquals($thread1['posts'][0]['postId'], $nextNewPost['nextNewPostId']);

        // The first player marks the first thread read. Verify that its first
        // post is no longer new, but that the first posts of the other two
        // threads are unaffected.
        $this->object->mark_forum_thread_read(self::$userId1WithoutAutopass,
            $thread1['threadId'], $boardId1, $thread1['timestamp']);
        $thread1 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertFalse($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertTrue($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertTrue($thread3['posts'][0]['isNew']);

        // The first player marks the first board read. Verify that the first
        // posts on both threads on that board are no longer new, but that
        // the first post of the thread on the other board is unaffected.
        $this->object->mark_forum_board_read(self::$userId1WithoutAutopass,
            $boardId1, $thread1['timestamp']);
        $thread1 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertFalse($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertFalse($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertTrue($thread3['posts'][0]['isNew']);

        // The first player marks all boards read. Verify that the first posts
        // of all three threads are no longer new.
        $this->object->mark_forum_read(self::$userId1WithoutAutopass,
            $thread1['timestamp']);
        $thread1 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertFalse($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertFalse($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertFalse($thread3['posts'][0]['isNew']);
    }
}
