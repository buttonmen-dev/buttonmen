<?php

require_once 'BMInterfaceTestAbstract.php';

/**
 * This test class is designed to be the first BMInterface test class to be run
 * by the test runner, thus the somewhat odd name, to ensure that it precedes
 * the other BMInterface*Test names.
 */
class BMInterface000Test extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterface(TRUE);
        $this->interfacePlayer = new BMInterfacePlayer(TRUE);
    }

    /**
     * @covers BMInterfaceNewuser::create_user
     */
    public function test_create_user() {
        $created_real = False;
        $maxtries = 999;
        $trynum = 1;

        // Tests may be run multiple times.  Find a user of the
        // form interfaceNNN which hasn't been created yet and
        // create it in the test DB.  The dummy interface will claim
        // success for any username of this form.
        while (!($created_real)) {
            $this->assertTrue($trynum < $maxtries,
                "Internal test error: too many interfaceNNN users in the test database. " .
                "Clean these out by hand.");
            $username = 'interface' . sprintf('%03d', $trynum);
            $email = $username . '@example.com';
            $createResult = $this->newuserObject->create_user($username, 't', $email);
            if (isset($createResult)) {
                $created_real = True;
            }
            $trynum++;
        }

        $this->assertTrue($created_real,
            "Creation of $username user should be reported as success");

        $infoArray = array(
            'name_irl' => '',
            'is_email_public' => FALSE,
            'dob_month' => 0,
            'dob_day' => 0,
            'gender' => '',
            'comment' => '',
            'vacation_message' => '',
            'monitor_redirects_to_game' => 0,
            'monitor_redirects_to_forum' => 0,
            'automatically_monitor' => 0,
            'autoaccept' => 1,
            'autopass' => 0,
            'fire_overshooting' => 0
        );
        $addlInfo = array('dob_month' => 0, 'dob_day' => 0, 'homepage' => '');

        $this->interfacePlayer->set_player_info(
            $createResult['playerId'],
            $infoArray,
            $addlInfo
        );
        self::$userId1WithoutAutopass = (int)$createResult['playerId'];

        $username = 'interface' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $createResult = $this->newuserObject->create_user($username, 't', $email);
        $this->interfacePlayer->set_player_info(
            $createResult['playerId'],
            $infoArray,
            $addlInfo
        );
        self::$userId2WithoutAutopass = (int)$createResult['playerId'];

        $trynum++;
        $username = 'interface' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $createResult = $this->newuserObject->create_user($username, 't', $email);

        $infoArray = array(
            'name_irl' => '',
            'is_email_public' => FALSE,
            'dob_month' => 0,
            'dob_day' => 0,
            'gender' => '',
            'comment' => '',
            'vacation_message' => '',
            'monitor_redirects_to_game' => 0,
            'monitor_redirects_to_forum' => 0,
            'automatically_monitor' => 0,
            'autoaccept' => 1,
            'autopass' => 1,
            'fire_overshooting' => 0
        );
        $addlInfo = array('dob_month' => 0, 'dob_day' => 0, 'homepage' => '');

        $this->interfacePlayer->set_player_info(
            $createResult['playerId'],
            $infoArray,
            $addlInfo
        );
        self::$userId3WithAutopass = (int)$createResult['playerId'];

        $trynum++;
        $username = 'interface' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $createResult = $this->newuserObject->create_user($username, 't', $email);
        $this->interfacePlayer->set_player_info(
            $createResult['playerId'],
            $infoArray,
            $addlInfo
        );
        self::$userId4WithAutopass = (int)$createResult['playerId'];

        $trynum++;
        $username = 'interface' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $createResult = $this->newuserObject->create_user($username, 't', $email);

        $infoArray = array(
            'name_irl' => '',
            'is_email_public' => FALSE,
            'dob_month' => 0,
            'dob_day' => 0,
            'gender' => '',
            'comment' => '',
            'vacation_message' => '',
            'monitor_redirects_to_game' => 0,
            'monitor_redirects_to_forum' => 0,
            'automatically_monitor' => 0,
            'autoaccept' => 0,
            'autopass' => 1,
            'fire_overshooting' => 0
        );
        $addlInfo = array('dob_month' => 0, 'dob_day' => 0, 'homepage' => '');

        $this->interfacePlayer->set_player_info(
            $createResult['playerId'],
            $infoArray,
            $addlInfo
        );
        self::$userId5WithoutAutoaccept = (int)$createResult['playerId'];
    }
}
