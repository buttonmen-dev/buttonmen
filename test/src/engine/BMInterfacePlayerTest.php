<?php

require_once 'BMInterfaceTestAbstract.php';

class BMInterfacePlayerTest extends BMInterfaceTestAbstract {

    protected function createObject() {
        $this->object = new BMInterfacePlayer(TRUE);
    }

    /**
     * @depends BMInterfaceTest::test_create_user
     *
     * @covers BMInterfacePlayer::get_player_info
     */
    public function test_get_player_info() {
        $interfacePlayer = new BMInterfacePlayer($this->object->isTest);
        $data = $interfacePlayer->get_player_info(self::$userId3WithAutopass);
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

}
