<?php

require_once 'BMInterfaceTestAbstract.php';

class BMInterfacePlayerTest extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterfacePlayer(TRUE);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfacePlayer::get_player_info
     */
    public function test_get_player_info() {
        $data = $this->object->get_player_info(self::$userId3WithAutopass);
        $resultArray = $data['user_prefs'];
        $this->assertTrue(is_array($resultArray));

        $this->assertArrayHasKey('id', $resultArray);
        $this->assertArrayHasKey('name_ingame', $resultArray);
        $this->assertArrayNotHasKey('password_hashed', $resultArray);
        $this->assertArrayHasKey('name_irl', $resultArray);
        $this->assertArrayHasKey('email', $resultArray);
        $this->assertArrayHasKey('is_email_public', $resultArray);
        $this->assertArrayHasKey('dob_month', $resultArray);
        $this->assertArrayHasKey('dob_day', $resultArray);
        $this->assertArrayHasKey('gender', $resultArray);
        $this->assertArrayHasKey('image_size', $resultArray);
        $this->assertArrayHasKey('image_size', $resultArray);
        $this->assertArrayHasKey('uses_gravatar', $resultArray);
        $this->assertArrayHasKey('monitor_redirects_to_game', $resultArray);
        $this->assertArrayHasKey('monitor_redirects_to_forum', $resultArray);
        $this->assertArrayHasKey('automatically_monitor', $resultArray);
        $this->assertArrayHasKey('comment', $resultArray);
        $this->assertArrayHasKey('vacation_message', $resultArray);
        $this->assertArrayHasKey('player_color', $resultArray);
        $this->assertArrayHasKey('opponent_color', $resultArray);
        $this->assertArrayHasKey('neutral_color_a', $resultArray);
        $this->assertArrayHasKey('neutral_color_b', $resultArray);
        $this->assertArrayHasKey('homepage', $resultArray);
        $this->assertArrayHasKey('favorite_button', $resultArray);
        $this->assertArrayHasKey('favorite_buttonset', $resultArray);
        $this->assertArrayHasKey('last_action_time', $resultArray);
        $this->assertArrayHasKey('creation_time', $resultArray);
        $this->assertArrayHasKey('fanatic_button_id', $resultArray);
        $this->assertArrayHasKey('n_games_won', $resultArray);
        $this->assertArrayHasKey('n_games_lost', $resultArray);

        $this->assertTrue(is_int($resultArray['id']));
        $this->assertEquals(self::$userId3WithAutopass, $resultArray['id']);

        $this->assertTrue(is_bool($resultArray['autopass']));
        $this->assertTrue(is_bool($resultArray['monitor_redirects_to_game']));
        $this->assertTrue(is_bool($resultArray['monitor_redirects_to_forum']));
        $this->assertTrue(is_bool($resultArray['automatically_monitor']));

        $this->assertTrue(is_int($resultArray['fanatic_button_id']));
        $this->assertEquals(0, $resultArray['fanatic_button_id']);
        $this->assertTrue(is_int($resultArray['n_games_won']));
        $this->assertTrue(is_int($resultArray['n_games_lost']));
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfacePlayer::get_player_info
     * @covers BMInterfacePlayer::set_player_info
     */
    public function test_set_player_info() {
        $infoArray = array(
            'name_irl' => '',
            'is_email_public' => FALSE,
            'dob_month' => 0,
            'dob_day' => 0,
            'gender' => '',
            'comment' => '',
            'vacation_message' => '',
            'autopass' => 1,
            'fire_overshooting' => 0,
            'player_color' => '#dd99dd',
            'opponent_color' => '#ddffdd',
            'neutral_color_a' => '#cccccc',
            'neutral_color_b' => '#dddddd',
            'monitor_redirects_to_game' => 1,
            'monitor_redirects_to_forum' => 1,
            'automatically_monitor' => 1,
        );
        $addlInfo = array('dob_month' => 0, 'dob_day' => 0, 'homepage' => 'google.com');

        $this->object->set_player_info(self::$userId1WithoutAutopass,
                                       $infoArray,
                                       $addlInfo);
        $data = $this->object->get_player_info(self::$userId1WithoutAutopass);
        $playerInfoArray = $data['user_prefs'];
        $this->assertEquals(TRUE, $playerInfoArray['autopass']);
        $this->assertEquals(FALSE, $playerInfoArray['fire_overshooting']);
        $this->assertEquals(TRUE, $playerInfoArray['monitor_redirects_to_game']);
        $this->assertEquals(TRUE, $playerInfoArray['monitor_redirects_to_forum']);
        $this->assertEquals(TRUE, $playerInfoArray['automatically_monitor']);
        $this->assertEquals('http://google.com', $playerInfoArray['homepage']);

        $infoArray['autopass'] = 0;
        $infoArray['fire_overshooting'] = 1;
        $infoArray['monitor_redirects_to_game'] = 0;
        $infoArray['monitor_redirects_to_forum'] = 0;
        $infoArray['automatically_monitor'] = 0;
        $this->object->set_player_info(self::$userId1WithoutAutopass,
                                       $infoArray,
                                       $addlInfo);
        $data = $this->object->get_player_info(self::$userId1WithoutAutopass);
        $playerInfoArray = $data['user_prefs'];
        $this->assertEquals(FALSE, $playerInfoArray['autopass']);
        $this->assertEquals(TRUE, $playerInfoArray['fire_overshooting']);
        $this->assertEquals(FALSE, $playerInfoArray['monitor_redirects_to_game']);
        $this->assertEquals(FALSE, $playerInfoArray['monitor_redirects_to_forum']);
        $this->assertEquals(FALSE, $playerInfoArray['automatically_monitor']);

        $addlInfo['homepage'] = 'javascript:alert(\"Evil\");';
        $response =
            $this->object->set_player_info(
                self::$userId1WithoutAutopass,
                $infoArray,
                $addlInfo
            );
        $this->assertEquals(NULL, $response);
        $data = $this->object->get_player_info(self::$userId1WithoutAutopass);
        $this->assertEquals('http://google.com', $playerInfoArray['homepage']);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfacePlayer::update_last_action_time
     */
    public function test_update_last_action_time() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Avis', 'Hammer'), 4);
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertEquals(array(0, 0), $game->lastActionTimeArray);

        $this->object->update_last_action_time(self::$userId1WithoutAutopass);
        $game = self::load_game($gameId);
        $this->assertEquals(array(0, 0), $game->lastActionTimeArray);

        $this->object->update_last_action_time(self::$userId1WithoutAutopass, $gameId);
        $game = self::load_game($gameId);
        $this->assertNotEquals(array(0, 0), $game->lastActionTimeArray);
        $this->assertGreaterThan(0, $game->lastActionTimeArray[0]);
        $this->assertEquals(0, $game->lastActionTimeArray[1]);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfacePlayer::update_last_access_time
     */
    public function test_update_last_access_time() {
        $retval =  $this->object->get_player_info(self::$userId1WithoutAutopass);
        $playerInfoArray = $retval['user_prefs'];
        $preTime = $playerInfoArray['last_access_time'];

        $this->object->update_last_access_time(self::$userId1WithoutAutopass);

        $retval =  $this->object->get_player_info(self::$userId1WithoutAutopass);
        $playerInfoArray = $retval['user_prefs'];
        $postTime = $playerInfoArray['last_access_time'];

        $this->assertGreaterThan($preTime, $postTime);
    }
}
