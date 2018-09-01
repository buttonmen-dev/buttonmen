<?php

/**
 * responder03Test: API tests of the buttonmen responder, file 03
 *
 * This file contains numbered game playback tests 41-60.
 */

require_once 'responderTestFramework.php';

class responder03Test extends responderTestFramework {

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game reproduces a bug affecting the interaction of Konstant and Fire dice
     */
    public function test_interface_game_041() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 41;
        $_SESSION = $this->mock_test_user_login('responder003');

        $gameId = $this->verify_api_createGame(
            array(1, 1, 3, 9, 4, 19, 8, 11, 2, 14),
            'responder003', 'responder004', 'Hawaii', 'Giant', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Giant', 'Fire', 'Konstant', 'Morphing', 'Null', 'Stealth'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Hawaii', 'recipe' => 'n(4) m(5) k(8) F(13) d(Y)', 'originalRecipe' => 'n(4) m(5) k(8) F(13) d(Y)', 'artFilename' => 'hawaii.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Giant', 'recipe' => '(20) (20) (20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20) (20) (20)', 'artFilename' => 'giant.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('Y' => array(1, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Null'), 'properties' => array(), 'recipe' => 'n(4)', 'description' => 'Null 4-sided die'),
            array('value' => NULL, 'sides' => 5, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(5)', 'description' => 'Morphing 5-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Konstant'), 'properties' => array(), 'recipe' => 'k(8)', 'description' => 'Konstant 8-sided die'),
            array('value' => NULL, 'sides' => 13, 'skills' => array('Fire'), 'properties' => array(), 'recipe' => 'F(13)', 'description' => 'Fire 13-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Stealth'), 'properties' => array(), 'recipe' => 'd(Y)', 'description' => 'Stealth Y Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        //////
        // Move 1: responder003 set swing values: Y=18
        $this->verify_api_submitDieValues(
            array(4),
            $gameId, 1, array('Y' => 18), NULL);

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: Y=18'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [n(4):1, m(5):1, k(8):3, F(13):9, d(Y=18):4], responder004 rolled [(20):4, (20):19, (20):8, (20):11, (20):2, (20):14]. responder004\'s button has the "slow" button special, and cannot win initiative normally.'));
        $expData['gameActionLogCount'] = 3;
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 18;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Stealth Y Swing Die (with 18 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 19;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 14;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 22;
        $expData['playerDataArray'][1]['roundScore'] = 60;
        $expData['playerDataArray'][0]['sideScore'] = -25.3;
        $expData['playerDataArray'][1]['sideScore'] = 25.3;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        /////
        // Move 2: responder003 chose to perform a Skill attack using [k(8):3,d(Y=18):4] against [(20):4]; responder003 must turn down fire dice to complete this attack.
        $this->verify_api_submitTurn(
            array(),
            'responder003 chose to perform a Skill attack using [k(8):3,d(Y=18):4] against [(20):4]; responder003 must turn down fire dice to complete this attack. ',
            $retval, array(array(0, 2), array(0, 4), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to perform a Skill attack using [k(8):3,d(Y=18):4] against [(20):4]; responder003 must turn down fire dice to complete this attack'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameState'] = 'ADJUST_FIRE_DICE';
        $expData['validAttackTypeArray'] = array('Skill');
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('IsAttacker');
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('IsAttacker');
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array('IsAttackTarget');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test is a copy of test_interface_game_008, modified to work with a player who has fire overshooting enabled.
     */
    public function test_interface_game_042() {

        // responder005 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder005 as soon as you've done so
        $this->game_number = 42;
        $_SESSION = $this->mock_test_user_login('responder005');


        ////////////////////
        // initial game setup

        // 5 of BlackOmega's dice, and 5 of Tamiya's dice, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(5, 8, 1, 3, 7, 1, 7, 1, 9, 18),
            'responder005', 'responder004', 'BlackOmega', 'Tamiya', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder005', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Fire', 'Focus', 'Morphing', 'Shadow', 'Speed', 'Stinger', 'Trip'));
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Speed');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 27;
        $expData['playerDataArray'][1]['roundScore'] = 26;
        $expData['playerDataArray'][0]['sideScore'] = 0.7;
        $expData['playerDataArray'][1]['sideScore'] = -0.7;
        $expData['playerDataArray'][0]['button'] = array('name' => 'BlackOmega', 'recipe' => 'tm(6) f(8) g(10) z(10) sF(20)', 'originalRecipe' => 'tm(6) f(8) g(10) z(10) sF(20)', 'artFilename' => 'blackomega.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Tamiya', 'recipe' => '(4) (8) (8) (12) z(20)', 'originalRecipe' => '(4) (8) (8) (12) z(20)', 'artFilename' => 'tamiya.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 5, 'sides' => 6, 'skills' => array('Trip', 'Morphing'), 'properties' => array(), 'recipe' => 'tm(6)', 'description' => 'Trip Morphing 6-sided die'),
            array('value' => 8, 'sides' => 8, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(8)', 'description' => 'Focus 8-sided die'),
            array('value' => 1, 'sides' => 10, 'skills' => array('Stinger'), 'properties' => array(), 'recipe' => 'g(10)', 'description' => 'Stinger 10-sided die'),
            array('value' => 3, 'sides' => 10, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(10)', 'description' => 'Speed 10-sided die'),
            array('value' => 7, 'sides' => 20, 'skills' => array('Shadow', 'Fire'), 'properties' => array(), 'recipe' => 'sF(20)', 'description' => 'Shadow Fire 20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => 7, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => 1, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => 9, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => 18, 'sides' => 20, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(20)', 'description' => 'Speed 20-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder005 rolled [tm(6):5, f(8):8, g(10):1, z(10):3, sF(20):7], responder004 rolled [(4):1, (8):7, (8):1, (12):9, z(20):18]. responder005 has dice which are not counted for initiative due to die skills: [tm(6), g(10)].'));
        $expData['gameActionLogCount'] = 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder004 performed Power attack using [(8):1] against [g(10):1]
        // [tm(6):5, f(8):8, g(10):1, z(10):3, sF(20):7] <= [(4):1, (8):7, (8):1, (12):9, z(20):18]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(6),
            'responder004 performed Power attack using [(8):1] against [g(10):1]; Defender g(10) was captured; Attacker (8) rerolled 1 => 6. ',
            $retval, array(array(0, 2), array(1, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder005');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow', 'Trip');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 22;
        $expData['playerDataArray'][1]['roundScore'] = 36;
        $expData['playerDataArray'][0]['sideScore'] = -9.3;
        $expData['playerDataArray'][1]['sideScore'] = 9.3;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 1, 'sides' => 10, 'properties' => array('WasJustCaptured'), 'recipe' => 'g(10)');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(8):1] against [g(10):1]; Defender g(10) was captured; Attacker (8) rerolled 1 => 6'));
        $expData['gameActionLogCount'] = 3;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder005 performed Trip attack using [tm(6):5] against [z(20):18] (unsuccessfully)
        // [tm(6):5, f(8):8, z(10):3, sF(20):7] => [(4):1, (8):7, (8):6, (12):9, z(20):18]
        $this->verify_api_submitTurn(
            array(1, 4),
            'responder005 performed Trip attack using [tm(6):5] against [z(20):18]; Attacker tm(6) rerolled 5 => 1; Defender z(20) rerolled 18 => 4, was not captured. ',
            $retval, array(array(0, 0), array(1, 4)),
            $gameId, 1, 'Trip', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Speed');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('JustPerformedTripAttack', 'JustPerformedUnsuccessfulAttack');
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 4;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005 performed Trip attack using [tm(6):5] against [z(20):18]; Attacker tm(6) rerolled 5 => 1; Defender z(20) rerolled 18 => 4, was not captured'));
        $expData['gameActionLogCount'] = 4;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 performed Power attack using [z(20):4] against [z(10):3]
        // [tm(6):1, f(8):8, z(10):3, sF(20):7] <= [(4):1, (8):7, (8):6, (12):9, z(20):4]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(17),
            'responder004 performed Power attack using [z(20):4] against [z(10):3]; Defender z(10) was captured; Attacker z(20) rerolled 4 => 17. ',
            $retval, array(array(0, 2), array(1, 4)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder005');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow', 'Trip');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 17;
        $expData['playerDataArray'][1]['roundScore'] = 46;
        $expData['playerDataArray'][0]['sideScore'] = -19.3;
        $expData['playerDataArray'][1]['sideScore'] = 19.3;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 3, 'sides' => 10, 'properties' => array('WasJustCaptured'), 'recipe' => 'z(10)');
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 17;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [z(20):4] against [z(10):3]; Defender z(10) was captured; Attacker z(20) rerolled 4 => 17'));
        $expData['gameActionLogCount'] = 5;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder005 performed Trip attack using [tm(6):4] against [(8):6] (successfully)
        // [tm(6):1, f(8):8, sF(20):7] => [(4):1, (8):7, (8):6, (12):9, z(20):17]
        $this->verify_api_submitTurn(
            array(6, 5, 3),
            'responder005 performed Trip attack using [tm(6):1] against [(8):6]; Attacker tm(6) rerolled 1 => 6; Defender (8) rerolled 6 => 5, was captured; Attacker tm(6) changed size from 6 to 8 sides, recipe changed from tm(6) to tm(8), rerolled 6 => 3. ',
            $retval, array(array(0, 0), array(1, 2)),
            $gameId, 1, 'Trip', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 26;
        $expData['playerDataArray'][1]['roundScore'] = 42;
        $expData['playerDataArray'][0]['sideScore'] = -10.7;
        $expData['playerDataArray'][1]['sideScore'] = 10.7;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 5, 'sides' => 8, 'properties' => array('WasJustCaptured'), 'recipe' => '(8)');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = 'tm(8)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = 'Trip Morphing 8-sided die';
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('JustPerformedTripAttack', 'HasJustMorphed');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005 performed Trip attack using [tm(6):1] against [(8):6]; Attacker tm(6) rerolled 1 => 6; Defender (8) rerolled 6 => 5, was captured; Attacker tm(6) changed size from 6 to 8 sides, recipe changed from tm(6) to tm(8), rerolled 6 => 3'));
        $expData['gameActionLogCount'] = 6;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder004 performed Power attack using [(12):9] against [f(8):8]; Attacker (12) rerolled 9 => 11; Defender f(8) was captured
        // [tm(8):3, f(8):8, sF(20):7] <= [(4):1, (8):7, (12):9, z(20):17]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(11),
            'responder004 performed Power attack using [(12):9] against [f(8):8]; Defender f(8) was captured; Attacker (12) rerolled 9 => 11. ',
            $retval, array(array(0, 1), array(1, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder005');

        // expected changes
        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill', 'Shadow', 'Trip'),
            array(22.0, 50.0, -18.7, 18.7),
            array(array(1, 2, array('value' => 11)),
                  array(0, 0, array('properties' => array()))),
            array(array(0, 1)),
            array(array(0, 0, array('properties' => array()))),
            array(array(1, array('value' => 8, 'sides' => 8, 'recipe' => 'f(8)', 'properties' => array('WasJustCaptured'))))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(12):9] against [f(8):8]; Defender f(8) was captured; Attacker (12) rerolled 9 => 11'));
        $expData['gameActionLogCount'] = 7;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder005 chose to perform a Skill attack using [tm(8):3] against [(8):7]; responder005 must turn down fire dice to complete this attack
        // [tm(8):3, sF(20):7] => [(4):1, (8):7, (12):11, z(20):17]
        $this->verify_api_submitTurn(
            array(),
            'responder005 chose to perform a Skill attack using [tm(8):3] against [(8):7]; responder005 must turn down fire dice to complete this attack. ',
            $retval, array(array(0, 0), array(1, 1)),
            $gameId, 1, 'Skill', 0, 1, '');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005 chose to perform a Skill attack using [tm(8):3] against [(8):7]; responder005 must turn down fire dice to complete this attack'));
        $expData['gameActionLogCount'] = 8;
        $expData['gameState'] = 'ADJUST_FIRE_DICE';
        $expData['validAttackTypeArray'] = array('Skill');
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('IsAttacker');
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array('IsAttackTarget');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // now load the game as non-participating player responder001 and check its state
        $_SESSION = $this->mock_test_user_login('responder001');
        $expData['dieBackgroundType'] = 'symmetric';
        $this->verify_api_loadGameData_as_nonparticipant($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder005');
        $expData['dieBackgroundType'] = 'realistic';

        ////////////////////
        // Move 07 - responder005 abandons the Fire-assisted Skill attack and gets another attack
        // [tm(8):3, sF(20):7] => [(4):1, (8):7, (12):11, z(20):17]
        $this->verify_api_adjustFire(
            array(),
            'responder005 chose to abandon this attack and start over. ',
            $retval, $gameId, 1, 'cancel', NULL, NULL);

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005 chose to abandon this attack and start over'));
        $expData['gameActionLogCount'] = 9;
        $expData['gameState'] = 'START_TURN';
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow', 'Trip');
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // N.B - this game and the game in test 8 diverge at this point

        ////////////////////
        // Move 08 - responder005 chose to perform a Power attack using [tm(8):3] against [(4):1]; responder005 may turn down fire dice to complete
        // [tm(8):3, sF(20):7] => [(4):1, (8):7, (12):11, z(20):17]
        $this->verify_api_submitTurn(
            array(),
            'responder005 chose to perform a Power attack using [tm(8):3] against [(4):1]; responder005 must decide whether to turn down fire dice. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005 chose to perform a Power attack using [tm(8):3] against [(4):1]; responder005 must decide whether to turn down fire dice'));
        $expData['gameActionLogCount'] = 10;
        $expData['gameState'] = 'ADJUST_FIRE_DICE';
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('IsAttacker');
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array('IsAttackTarget');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test reproduces a trip mood bug, and standardizes the
     * behavior of trip mood dice during successful trip attacks.
     */
    public function test_interface_game_043() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 43;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup

        // 0 of JennieGirl's dice, and 6 of Giant's dice, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(13, 2, 7, 4, 6, 20),
            'responder003', 'responder004', 'JennieGirl', 'Giant', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Giant', 'Chance', 'Mood', 'Ornery', 'Rage', 'Reserve', 'Shadow', 'TimeAndSpace', 'Trip', 'Turbo'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Jenniegirl', 'recipe' => 'Gst(S) Gst(S)? Gst(S)^ cor(V) cor@(X)!', 'originalRecipe' => 'Gst(S) Gst(S)? Gst(S)^ cor(V) cor@(X)!', 'artFilename' => 'jenniegirl.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Giant', 'recipe' => '(20) (20) (20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20) (20) (20)', 'artFilename' => 'giant.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('S' => array(6, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => null, 'sides' => null, 'skills' => array('Rage', 'Shadow', 'Trip'), 'properties' => array(), 'recipe' => 'Gst(S)', 'description' => 'Rage Shadow Trip S Swing Die'),
            array('value' => null, 'sides' => null, 'skills' => array('Rage', 'Shadow', 'Trip', 'Mood'), 'properties' => array(), 'recipe' => 'Gst(S)?', 'description' => 'Rage Shadow Trip S Mood Swing Die'),
            array('value' => null, 'sides' => null, 'skills' => array('Rage', 'Shadow', 'Trip', 'TimeAndSpace'), 'properties' => array(), 'recipe' => 'Gst^(S)', 'description' => 'Rage Shadow Trip TimeAndSpace S Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => null, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => null, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => null, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => null, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => null, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => null, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 specifies swing dice
        // this causes 3 of JennieGirl's dice to be rolled

        $this->verify_api_submitDieValues(
            array(2, 1, 2),
            $gameId, 1, array('S' => 6), NULL);

        $expData['validAttackTypeArray'] = array('Skill', 'Shadow', 'Trip');
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['playerDataArray'][0]['roundScore'] = 9;
        $expData['playerDataArray'][1]['roundScore'] = 60;
        $expData['playerDataArray'][0]['sideScore'] = -34.0;
        $expData['playerDataArray'][1]['sideScore'] = 34.0;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] .= ' (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] .= ' (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] .= ' (with 6 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 13;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 20;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: S=6'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [Gst(S=6):2, Gst(S=6)?:1, Gst^(S=6):2], responder004 rolled [(20):13, (20):2, (20):7, (20):4, (20):6, (20):20]. responder004\'s button has the "slow" button special, and cannot win initiative normally.'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performs a trip attack using Gst(S=6)?:1 against (20):20
        $this->verify_api_submitTurn(
            array(
                4,  // resize attacker to index 4 (20 sides)
                15, // attacker rolls 15
                4,  // defender rolls 4
            ),
            'responder003 performed Trip attack using [Gst(S=6)?:1] against [(20):20]; Attacker Gst(S=6)? remained the same size, recipe changed from Gst(S=6)? to Gst(S=20)?, rerolled 1 => 15; Defender (20) rerolled 20 => 4, was captured. ',
            $retval, array(array(0, 1), array(1, 5)),
            $gameId, 1, 'Trip', 0, 1, '');

        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 36;
        $expData['playerDataArray'][1]['roundScore'] = 50;
        $expData['playerDataArray'][0]['sideScore'] = -9.3;
        $expData['playerDataArray'][1]['sideScore'] = 9.3;
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 4, 'sides' => 20, 'recipe' => '(20)', 'properties' => array('WasJustCaptured'));
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 5, 1);
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 15;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array('Shadow', 'Trip', 'Mood');
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array('JustPerformedTripAttack');
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = 'st(S)?';
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = 'Shadow Trip S Mood Swing Die (with 20 sides)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [Gst(S=6)?:1] against [(20):20]; Attacker Gst(S=6)? remained the same size, recipe changed from Gst(S=6)? to Gst(S=20)?, rerolled 1 => 15; Defender (20) rerolled 20 => 4, was captured'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    // N.B. Fake game 44 is used by a test above

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test locks in the behavior of a Trip die unsuccessfully attacking a Radioactive die
     * The attacking trip die SHOULD split in this case (#1432, #1919)
     */
    public function test_interface_game_044() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 44;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(1, 1, 1, 7, 1, 2, 1, 3, 1, 4, 1, 7),
            'responder003', 'responder004', 'Prudence', 'Calmon', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Mighty', 'Ornery', 'Radioactive', 'Trip'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Prudence', 'recipe' => '(1) t(4) (6) (12) (X)', 'originalRecipe' => '(1) t(4) (6) (12) (X)', 'artFilename' => 'prudence.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Calmon', 'recipe' => '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)', 'originalRecipe' => '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)', 'artFilename' => 'calmon.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array(), 'properties' => array(), 'recipe' => '(1)', 'description' => '1-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(4)', 'description' => 'Trip 4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 3, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,2)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 2 sides)', 'subdieArray' => array(array('sides' => 1), array('sides' => 2))),
            array('value' => NULL, 'sides' => 5, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,4)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 4 sides)', 'subdieArray' => array(array('sides' => 1), array('sides' => 4))),
            array('value' => NULL, 'sides' => 7, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,6)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 6 sides)', 'subdieArray' => array(array('sides' => 1), array('sides' => 6))),
            array('value' => NULL, 'sides' => 9, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,8)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 8 sides)', 'subdieArray' => array(array('sides' => 1), array('sides' => 8))),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(4),
            $gameId, 1, array('X' => 6), NULL);

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=6'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(1):1, t(4):1, (6):1, (12):7, (X=6):4], responder004 rolled [%Ho(1,2):3, %Ho(1,4):4, %Ho(1,6):5, %Ho(1,8):8]. responder003 has dice which are not counted for initiative due to die skills: [t(4)].'));
        $expData['gameActionLogCount'] = 3;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "X Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['roundScore'] = 14.5;
        $expData['playerDataArray'][0]['sideScore'] = 1.7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['subdieArray'] = array(array("sides" => "1", "value" => "1"), array("sides" => "2", "value" => "2"));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['subdieArray'] = array(array("sides" => "1", "value" => "1"), array("sides" => "4", "value" => "3"));
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['subdieArray'] = array(array("sides" => "1", "value" => "1"), array("sides" => "6", "value" => "4"));
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][3]['subdieArray'] = array(array("sides" => "1", "value" => "1"), array("sides" => "8", "value" => "7"));
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 8;
        $expData['playerDataArray'][1]['roundScore'] = 12;
        $expData['playerDataArray'][1]['sideScore'] = -1.7;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(1, 1, 2, 2, 1),
            'responder003 performed Trip attack using [t(4):1] against [%Ho(1,2):3]; Attacker t(4) rerolled 1 => 1; Defender %Ho(1,2) recipe changed to %Ho(2,4), rerolled 3 => 3, was not captured; Attacker t(4) showing 1 split into: t(2) showing 2, and t(2) showing 1. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Trip', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [t(4):1] against [%Ho(1,2):3]; Attacker t(4) rerolled 1 => 1; Defender %Ho(1,2) recipe changed to %Ho(2,4), rerolled 3 => 3, was not captured; Attacker t(4) showing 1 split into: t(2) showing 2, and t(2) showing 1'));
        $expData['gameActionLogCount'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array("JustPerformedTripAttack", "JustPerformedUnsuccessfulAttack", "HasJustSplit");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = 't(2)';
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = 'Trip 2-sided die';
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 0, array(
            array('value' => 1, 'sides' => 2, 'recipe' => 't(2)', 'skills' => array('Trip'), 'properties' => array("JustPerformedTripAttack", "JustPerformedUnsuccessfulAttack", "HasJustSplit"), 'description' => 'Trip 2-sided die')));
        $expData['playerDataArray'][0]['sideScore'] = 0.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "Radioactive Mighty Ornery Twin Die (with 2 and 4 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array("HasJustGrown", "Twin");
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "%Ho(2,4)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][0]['subdieArray'] = array(array("sides" => "2", "value" => "1"), array("sides" => "4", "value" => "2"));
        $expData['playerDataArray'][1]['roundScore'] = 13.5;
        $expData['playerDataArray'][1]['sideScore'] = -0.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1, 4, 1, 2, 2, 1, 1, 7, 2, 2),
            'responder004 performed Skill attack using [%Ho(1,4):4] against [(X=6):4]; Defender (X=6) was captured; Attacker %Ho(1,4) showing 4 changed to Ho(1,4), which then split into: Ho(1,2) which grew into Ho(2,4) showing 5, and Ho(0,2) which grew into Ho(1,4) showing 3. responder004\'s idle ornery dice rerolled at end of turn: %Ho(2,4) changed size from 6 to 10 sides, recipe changed from %Ho(2,4) to %Ho(4,6), rerolled 3 => 3; %Ho(1,6) changed size from 7 to 10 sides, recipe changed from %Ho(1,6) to %Ho(2,8), rerolled 5 => 8; %Ho(1,8) changed size from 9 to 12 sides, recipe changed from %Ho(1,8) to %Ho(2,10), rerolled 8 => 4. ',
            $retval, array(array(1, 1), array(0, 5)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [%Ho(1,4):4] against [(X=6):4]; Defender (X=6) was captured; Attacker %Ho(1,4) showing 4 changed to Ho(1,4), which then split into: Ho(1,2) which grew into Ho(2,4) showing 5, and Ho(0,2) which grew into Ho(1,4) showing 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004\'s idle ornery dice rerolled at end of turn: %Ho(2,4) changed size from 6 to 10 sides, recipe changed from %Ho(2,4) to %Ho(4,6), rerolled 3 => 3; %Ho(1,6) changed size from 7 to 10 sides, recipe changed from %Ho(1,6) to %Ho(2,8), rerolled 5 => 8; %Ho(1,8) changed size from 9 to 12 sides, recipe changed from %Ho(1,8) to %Ho(2,10), rerolled 8 => 4'));
        $expData['gameActionLogCount'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['roundScore'] = 11.5;
        $expData['playerDataArray'][0]['sideScore'] = -10.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "Radioactive Mighty Ornery Twin Die (with 4 and 6 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array("HasJustGrown", "HasJustRerolledOrnery", "Twin");
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "%Ho(4,6)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][0]['subdieArray'] = array(array("sides" => "4", "value" => "2"), array("sides" => "6", "value" => "1"));
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "Mighty Ornery Twin Die (with 2 and 4 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array("HasJustSplit", "HasJustGrown", "Twin");
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "Ho(2,4)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['skills'] = array("Mighty", "Ornery");
        $expData['playerDataArray'][1]['activeDieArray'][1]['subdieArray'] = array(array("sides" => "2", "value" => "1"), array("sides" => "4", "value" => "4"));
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "Mighty Ornery Twin Die (with 1 and 4 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array("HasJustSplit", "HasJustGrown", "Twin");
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "Ho(1,4)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['skills'] = array("Mighty", "Ornery");
        $expData['playerDataArray'][1]['activeDieArray'][2]['subdieArray'] = array(array("sides" => "1", "value" => "1"), array("sides" => "4", "value" => "2"));
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "Radioactive Mighty Ornery Twin Die (with 2 and 8 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array("HasJustGrown", "HasJustRerolledOrnery", "Twin");
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "%Ho(2,8)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['subdieArray'] = array(array("sides" => "2", "value" => "1"), array("sides" => "8", "value" => "7"));
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Radioactive Mighty Ornery Twin Die (with 2 and 10 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array("HasJustGrown", "HasJustRerolledOrnery", "Twin");
        $expData['playerDataArray'][1]['activeDieArray'][4]['recipe'] = "%Ho(2,10)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][4]['skills'] = array("Radioactive", "Mighty", "Ornery");
        $expData['playerDataArray'][1]['activeDieArray'][4]['subdieArray'] = array(array("sides" => "2", "value" => "2"), array("sides" => "10", "value" => "2"));
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['roundScore'] = 27.5;
        $expData['playerDataArray'][1]['sideScore'] = 10.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }


    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test checks for regressions of option die indexing in display of previous option values.
     * This test uses bobby 5150, who has an option reserve die which is not the first reserve die.
     * * bobby 5150 loses round 1 and selects the r!(1/30) die
     * * bobby 5150 loses round 2 and selects a second reserve die with an earlier index than r!(1/30)
     * * the API must still report the correct previous option value for the r(1/30)
     * Note: because 2/3 of the game must be played in order to verify the fix, this test game is then
     * played to the end in case there is a future use for more completed test games
     */
    public function test_interface_game_045() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 45;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(3, 14, 1, 4, 1, 6),
            'responder003', 'responder004', 'bobby 5150', 'Uncle Scratchy', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Focus', 'Poison', 'Reserve', 'Shadow', 'Speed', 'TimeAndSpace', 'Trip', 'Turbo'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'bobby 5150', 'recipe' => '^(3) fsp(R) ftz(14) tz!(1/30) r^(4) rz(12) r!(1/30)', 'originalRecipe' => '^(3) fsp(R) ftz(14) tz!(1/30) r^(4) rz(12) r!(1/30)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Uncle Scratchy', 'recipe' => '(2) (4) (6) (10) (X)', 'originalRecipe' => '(2) (4) (6) (10) (X)', 'artFilename' => 'unclescratchy.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('R' => array(2, 16));
        $expData['playerDataArray'][0]['optRequestArray'] = array(3 => array(1, 30));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 3, 'skills' => array('TimeAndSpace'), 'properties' => array(), 'recipe' => '^(3)', 'description' => 'TimeAndSpace 3-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Focus', 'Shadow', 'Poison'), 'properties' => array(), 'recipe' => 'fsp(R)', 'description' => 'Focus Shadow Poison R Swing Die'),
            array('value' => NULL, 'sides' => 14, 'skills' => array('Focus', 'Trip', 'Speed'), 'properties' => array(), 'recipe' => 'ftz(14)', 'description' => 'Focus Trip Speed 14-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Trip', 'Speed', 'Turbo'), 'properties' => array(), 'recipe' => 'tz(1/30)!', 'description' => 'Trip Speed Turbo Option Die (with 1 or 30 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(11),
            $gameId, 1, array('X' => 11), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set die sizes'));
        $expData['gameActionLogCount'] = 2;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(7, 1),
            $gameId, 1, array('R' => 15), array(3 => 1));

        $expData['activePlayerIdx'] = 1;
        array_shift($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=11'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: R=15 and option dice: tz(1/30=1)!'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [^(3):3, fsp(R=15):7, ftz(14):14, tz(1/30=1)!:1], responder004 rolled [(2):1, (4):4, (6):1, (10):6, (X=11):11]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=1)!].'));
        $expData['gameActionLogCount'] = 4;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Shadow Poison R Swing Die (with 15 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 15;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Trip Speed Turbo Option Die (with 1 side)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = -6;
        $expData['playerDataArray'][0]['sideScore'] = -15;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array(3 => array(1, 30));
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 11;
        $expData['playerDataArray'][1]['roundScore'] = 16.5;
        $expData['playerDataArray'][1]['sideScore'] = 15;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array("Power", "Skill");
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2),
            'responder004 performed Power attack using [(4):4] against [^(3):3]; Defender ^(3) was captured; Attacker (4) rerolled 4 => 2. ',
            $retval, array(array(1, 1), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(4):4] against [^(3):3]; Defender ^(3) was captured; Attacker (4) rerolled 4 => 2'));
        $expData['gameActionLogCount'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Focus Shadow Poison R Swing Die (with 15 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "fsp(R)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 15;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Focus", "Shadow", "Poison");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Trip Speed 14-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Focus", "Trip", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Trip Speed Turbo Option Die (with 1 side)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['optRequestArray'] = array("2" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(2 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = -7.5;
        $expData['playerDataArray'][0]['sideScore'] = -18;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "^(3)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 3;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['roundScore'] = 19.5;
        $expData['playerDataArray'][1]['sideScore'] = 18;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Shadow", "Speed", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(1),
            'responder003 performed Speed attack using [tz(1/30=1)!:1] against [(6):1]; Defender (6) was captured; Attacker tz(1/30=1)! rerolled from 1. Turbo die tz(1/30=1)! remained the same size, rolled 1. ',
            $retval, array(array(0, 2), array(1, 2)),
            $gameId, 1, 'Speed', 0, 1, '', array(2 => 1));

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Speed attack using [tz(1/30=1)!:1] against [(6):1]; Defender (6) was captured; Attacker tz(1/30=1)! rerolled from 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=1)! remained the same size, rolled 1'));
        $expData['gameActionLogCount'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "(6)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = -1.5;
        $expData['playerDataArray'][0]['sideScore'] = -12;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "10-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "(10)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 11;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 16.5;
        $expData['playerDataArray'][1]['sideScore'] = 12;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Skill attack using [(2):1] against [tz(1/30=1)!:1]; Defender tz(1/30=1)! was captured; Attacker (2) rerolled 1 => 1. ',
            $retval, array(array(1, 0), array(0, 2)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(2):1] against [tz(1/30=1)!:1]; Defender tz(1/30=1)! was captured; Attacker (2) rerolled 1 => 1'));
        $expData['gameActionLogCount'] = 8;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = -2;
        $expData['playerDataArray'][0]['sideScore'] = -13;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 1;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 17.5;
        $expData['playerDataArray'][1]['sideScore'] = 13;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Shadow", "Speed", "Trip");
        $expData['playerDataArray'][0]['canStillWin'] = true;
        $expData['playerDataArray'][1]['canStillWin'] = true;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(2),
            'responder003 performed Power attack using [ftz(14):14] against [(X=11):11]; Defender (X=11) was captured; Attacker ftz(14) rerolled 14 => 2. ',
            $retval, array(array(0, 1), array(1, 3)),
            $gameId, 1, 'Power', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [ftz(14):14] against [(X=11):11]; Defender (X=11) was captured; Attacker ftz(14) rerolled 14 => 2'));
        $expData['gameActionLogCount'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "(X)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 11;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 11;
        $expData['playerDataArray'][0]['roundScore'] = 9;
        $expData['playerDataArray'][0]['sideScore'] = -2;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 12;
        $expData['playerDataArray'][1]['sideScore'] = 2;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2),
            'responder004 performed Skill attack using [(4):2] against [ftz(14):2]; Defender ftz(14) was captured; Attacker (4) rerolled 2 => 2. responder003 passed. ',
            $retval, array(array(1, 1), array(0, 1)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(4):2] against [ftz(14):2]; Defender ftz(14) was captured; Attacker (4) rerolled 2 => 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        $expData['gameActionLogCount'] = 11;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 2;
        $expData['playerDataArray'][0]['sideScore'] = -16;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][2]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][1]['capturedDieArray'][2]['sides'] = 14;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['value'] = 2;
        $expData['playerDataArray'][1]['roundScore'] = 26;
        $expData['playerDataArray'][1]['sideScore'] = 16;
        $expData['validAttackTypeArray'] = array("Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2, 8),
            'responder004 performed Skill attack using [(2):1,(10):6] against [fsp(R=15):7]; Defender fsp(R=15) was captured; Attacker (2) rerolled 1 => 2; Attacker (10) rerolled 6 => 8. End of round: responder004 won round 1 (18.5 vs. 17). ',
            $retval, array(array(1, 0), array(1, 2), array(0, 0)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = null;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(2):1,(10):6] against [fsp(R=15):7]; Defender fsp(R=15) was captured; Attacker (2) rerolled 1 => 2; Attacker (10) rerolled 6 => 8'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 1 (18.5 vs. 17)'));
        $expData['gameActionLogCount'] = 13;
        $expData['gameState'] = "CHOOSE_RESERVE_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "TimeAndSpace 3-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "^(3)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("TimeAndSpace");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Shadow Poison R Swing Die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "fsp(R)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Focus", "Shadow", "Poison");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Focus Trip Speed 14-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Focus", "Trip", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Trip Speed Turbo Option Die (with 1 or 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Reserve TimeAndSpace 4-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "r^(4)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array("Reserve", "TimeAndSpace");
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = "Reserve Speed 12-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][5]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][5]['recipe'] = "rz(12)";
        $expData['playerDataArray'][0]['activeDieArray'][5]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][5]['skills'] = array("Reserve", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][6]['description'] = "Reserve Turbo Option Die (with 1 or 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][6]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][6]['recipe'] = "r(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][6]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][6]['skills'] = array("Reserve", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][6]['value'] = null;
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        $expData['playerDataArray'][0]['gameScoreArray'] = array("D" => 0, "L" => 1, "W" => 0);
        $expData['playerDataArray'][0]['optRequestArray'] = array("3" => array(1, 30), "6" => array(1, 30));
        $expData['playerDataArray'][0]['prevOptValueArray'] = array("3" => 1);
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array("R" => 15);
        $expData['playerDataArray'][0]['roundScore'] = null;
        $expData['playerDataArray'][0]['sideScore'] = null;
        $expData['playerDataArray'][0]['swingRequestArray'] = array("R" => array(2, 16));
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "6-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "(6)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "10-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "(10)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][4]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][4]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = null;
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        $expData['playerDataArray'][1]['gameScoreArray'] = array("D" => 0, "L" => 0, "W" => 1);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array("X" => 11);
        $expData['playerDataArray'][1]['roundScore'] = null;
        $expData['playerDataArray'][1]['sideScore'] = null;
        $expData['playerDataArray'][1]['swingRequestArray'] = array("X" => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['roundNumber'] = 2;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_reactToReserve(
            array(1, 8, 2, 4, 3, 7, 8),
            'responder003 added a reserve die: r(1/30)!. ',
            $gameId, 'add', 6);

        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 added a reserve die: r(1/30)!'));
        $expData['gameActionLogCount'] = 14;
        $expData['gameState'] = "SPECIFY_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Turbo Option Die (with 1 or 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array("Turbo");
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['button'] = array("artFilename" => "BMdefaultRound.png", "name" => "bobby 5150", "recipe" => "^(3) fsp(R) ftz(14) tz(1/30)! r^(4) rz(12) (1/30)!", "originalRecipe" => "^(3) fsp(R) ftz(14) tz!(1/30) r^(4) rz(12) r!(1/30)");
        $expData['playerDataArray'][0]['optRequestArray'] = array("3" => array(1, 30), "4" => array(1, 30));

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(1, 1, 1),
            $gameId, 2, array('R' => 11), array(3 => 1, 4 => 1));

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: R=11 and option dice: tz(1/30=1)!, (1/30=1)!'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [^(3):1, fsp(R=11):1, ftz(14):8, tz(1/30=1)!:1, (1/30=1)!:1], responder004 rolled [(2):2, (4):4, (6):3, (10):7, (X=11):8]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=1)!].'));
        $expData['gameActionLogCount'] = 16;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Shadow Poison R Swing Die (with 11 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Trip Speed Turbo Option Die (with 1 side)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Turbo Option Die (with 1 side)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['prevOptValueArray'] = array();
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = -1.5;
        $expData['playerDataArray'][0]['sideScore'] = -12;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array(3 => array(1, 30), 4 => array(1, 30));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 16.5;
        $expData['playerDataArray'][1]['sideScore'] = 12;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Shadow", "Speed", "Trip");
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(6),
            'responder003 performed Shadow attack using [fsp(R=11):1] against [(2):2]; Defender (2) was captured; Attacker fsp(R=11) rerolled 1 => 6. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 2, 'Shadow', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Shadow attack using [fsp(R=11):1] against [(2):2]; Defender (2) was captured; Attacker fsp(R=11) rerolled 1 => 6'));
        $expData['gameActionLogCount'] = 17;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "(2)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 2;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['roundScore'] = 0.5;
        $expData['playerDataArray'][0]['sideScore'] = -10;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "4-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "(4)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "6-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "(6)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "10-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "(10)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 8;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 15.5;
        $expData['playerDataArray'][1]['sideScore'] = 10;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(9),
            'responder004 performed Skill attack using [(X=11):8] against [ftz(14):8]; Defender ftz(14) was captured; Attacker (X=11) rerolled 8 => 9. ',
            $retval, array(array(1, 3), array(0, 2)),
            $gameId, 2, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(X=11):8] against [ftz(14):8]; Defender ftz(14) was captured; Attacker (X=11) rerolled 8 => 9'));
        $expData['gameActionLogCount'] = 18;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Trip Speed Turbo Option Die (with 1 side)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Turbo Option Die (with 1 side)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Turbo");
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array("2" => array(1, 30), "3" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(2 => array(1, 30), 3 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = -6.5;
        $expData['playerDataArray'][0]['sideScore'] = -24;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 14;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 8;
        $expData['playerDataArray'][1]['roundScore'] = 29.5;
        $expData['playerDataArray'][1]['sideScore'] = 24;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Skill", "Shadow", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(3, 2),
            'responder003 performed Skill attack using [^(3):1,fsp(R=11):6] against [(10):7]; Defender (10) was captured; Attacker ^(3) rerolled 1 => 3; Attacker fsp(R=11) rerolled 6 => 2. responder003 gets another turn because a Time and Space die rolled odd. ',
            $retval, array(array(0, 0), array(0, 1), array(1, 2)),
            $gameId, 2, 'Skill', 0, 1, '');

        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [^(3):1,fsp(R=11):6] against [(10):7]; Defender (10) was captured; Attacker ^(3) rerolled 1 => 3; Attacker fsp(R=11) rerolled 6 => 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 gets another turn because a Time and Space die rolled odd'));
        $expData['gameActionLogCount'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "(10)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 10;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][0]['roundScore'] = 3.5;
        $expData['playerDataArray'][0]['sideScore'] = -14;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 9;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 24.5;
        $expData['playerDataArray'][1]['sideScore'] = 14;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Shadow", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(7, 1, 1),
            'responder003 performed Skill attack using [fsp(R=11):2,tz(1/30=1)!:1,(1/30=1)!:1] against [(4):4]; Defender (4) was captured; Attacker fsp(R=11) rerolled 2 => 7; Attacker tz(1/30=1)! rerolled from 1; Attacker (1/30=1)! rerolled from 1. Turbo die tz(1/30=1)! remained the same size, rolled 1; Turbo die (1/30=1)! remained the same size, rolled 1. ',
            $retval, array(array(0, 1), array(0, 2), array(0, 3), array(1, 0)),
            $gameId, 2, 'Skill', 0, 1, '', array(2 => 1, 3 => 1));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [fsp(R=11):2,tz(1/30=1)!:1,(1/30=1)!:1] against [(4):4]; Defender (4) was captured; Attacker fsp(R=11) rerolled 2 => 7; Attacker tz(1/30=1)! rerolled from 1; Attacker (1/30=1)! rerolled from 1'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=1)! remained the same size, rolled 1; Turbo die (1/30=1)! remained the same size, rolled 1'));
        $expData['gameActionLogCount'] = 22;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][2]['recipe'] = "(4)";
        $expData['playerDataArray'][0]['capturedDieArray'][2]['sides'] = 4;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['roundScore'] = 7.5;
        $expData['playerDataArray'][0]['sideScore'] = -10;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "6-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "(6)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 9;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 22.5;
        $expData['playerDataArray'][1]['sideScore'] = 10;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Power attack using [(6):3] against [(1/30=1)!:1]; Defender (1/30=1)! was captured; Attacker (6) rerolled 3 => 1. ',
            $retval, array(array(1, 0), array(0, 3)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(6):3] against [(1/30=1)!:1]; Defender (1/30=1)! was captured; Attacker (6) rerolled 3 => 1'));
        $expData['gameActionLogCount'] = 23;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array("2" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(2 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 7;
        $expData['playerDataArray'][0]['sideScore'] = -11;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 1;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 23.5;
        $expData['playerDataArray'][1]['sideScore'] = 11;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Shadow", "Speed", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(7),
            'responder003 performed Shadow attack using [fsp(R=11):7] against [(X=11):9]; Defender (X=11) was captured; Attacker fsp(R=11) rerolled 7 => 7. ',
            $retval, array(array(0, 1), array(1, 1)),
            $gameId, 2, 'Shadow', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Shadow attack using [fsp(R=11):7] against [(X=11):9]; Defender (X=11) was captured; Attacker fsp(R=11) rerolled 7 => 7'));
        $expData['gameActionLogCount'] = 24;
        $expData['playerDataArray'][0]['capturedDieArray'][3]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][3]['recipe'] = "(X)";
        $expData['playerDataArray'][0]['capturedDieArray'][3]['sides'] = 11;
        $expData['playerDataArray'][0]['capturedDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 18;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(6),
            'responder004 performed Skill attack using [(6):1] against [tz(1/30=1)!:1]; Defender tz(1/30=1)! was captured; Attacker (6) rerolled 1 => 6. responder003 passed. ',
            $retval, array(array(1, 0), array(0, 2)),
            $gameId, 2, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(6):1] against [tz(1/30=1)!:1]; Defender tz(1/30=1)! was captured; Attacker (6) rerolled 1 => 6'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        $expData['gameActionLogCount'] = 26;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 17.5;
        $expData['playerDataArray'][0]['sideScore'] = -1;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][2]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][1]['capturedDieArray'][2]['sides'] = 1;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 19;
        $expData['playerDataArray'][1]['sideScore'] = 1;
        $expData['validAttackTypeArray'] = array("Power");
        $expData['playerDataArray'][0]['canStillWin'] = true;
        $expData['playerDataArray'][1]['canStillWin'] = true;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3),
            'responder004 performed Power attack using [(6):6] against [^(3):3]; Defender ^(3) was captured; Attacker (6) rerolled 6 => 3. responder003 passed. responder004 passed. End of round: responder004 won round 2 (22 vs. 16). ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = null;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(6):6] against [^(3):3]; Defender ^(3) was captured; Attacker (6) rerolled 6 => 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 2 (22 vs. 16)'));
        $expData['gameActionLogCount'] = 30;
        $expData['gameState'] = "CHOOSE_RESERVE_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Shadow Poison R Swing Die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Focus Trip Speed 14-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Focus", "Trip", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Trip Speed Turbo Option Die (with 1 or 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Reserve TimeAndSpace 4-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "r^(4)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array("Reserve", "TimeAndSpace");
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = "Reserve Speed 12-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][5]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][5]['recipe'] = "rz(12)";
        $expData['playerDataArray'][0]['activeDieArray'][5]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][5]['skills'] = array("Reserve", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][6]['description'] = "Turbo Option Die (with 1 or 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][6]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][6]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][6]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][6]['skills'] = array("Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][6]['value'] = null;
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        $expData['playerDataArray'][0]['gameScoreArray'] = array("D" => 0, "L" => 2, "W" => 0);
        $expData['playerDataArray'][0]['optRequestArray'] = array("3" => array(1, 30), "6" => array(1, 30));
        $expData['playerDataArray'][0]['prevOptValueArray'] = array("3" => 1, "4" => 1);
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array("R" => 11);
        $expData['playerDataArray'][0]['roundScore'] = null;
        $expData['playerDataArray'][0]['sideScore'] = null;
        $expData['playerDataArray'][0]['swingRequestArray'] = array("R" => array(2, 16));
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "2-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "(2)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "4-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "(4)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "6-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "(6)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "10-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "(10)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][4]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][4]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = null;
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        $expData['playerDataArray'][1]['gameScoreArray'] = array("D" => 0, "L" => 0, "W" => 2);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array("X" => 11);
        $expData['playerDataArray'][1]['roundScore'] = null;
        $expData['playerDataArray'][1]['sideScore'] = null;
        $expData['playerDataArray'][1]['swingRequestArray'] = array("X" => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['roundNumber'] = 3;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_reactToReserve(
            array(1, 1, 12, 1, 2, 1, 9, 8),
            'responder003 added a reserve die: rz(12). ',
            $gameId, 'add', 5);

        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 added a reserve die: rz(12)'));
        $expData['gameActionLogCount'] = 31;
        $expData['gameState'] = "SPECIFY_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Speed 12-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "z(12)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array("Speed");
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = "Turbo Option Die (with 1 or 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][5]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][5]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][5]['skills'] = array("Turbo");
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['button'] = array("artFilename" => "BMdefaultRound.png", "name" => "bobby 5150", "recipe" => "^(3) fsp(R) ftz(14) tz(1/30)! r^(4) z(12) (1/30)!", "originalRecipe" => "^(3) fsp(R) ftz(14) tz!(1/30) r^(4) rz(12) r!(1/30)");
        $expData['playerDataArray'][0]['optRequestArray'] = array("3" => array(1, 30), "5" => array(1, 30));
        $expData['playerDataArray'][0]['prevOptValueArray'] = array("3" => 1, "5" => 1);

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(4, 30, 25),
            $gameId, 3, array('R' => 13), array(3 => 30, 5 => 30));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: R=13 and option dice: tz(1/30=30)!, (1/30=30)!'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 3. Initial die values: responder003 rolled [^(3):1, fsp(R=13):4, ftz(14):1, tz(1/30=30)!:30, z(12):12, (1/30=30)!:25], responder004 rolled [(2):1, (4):2, (6):1, (10):9, (X=11):8]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=30)!].'));
        $expData['gameActionLogCount'] = 33;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Shadow Poison R Swing Die (with 13 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 13;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = "Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][5]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 25;
        $expData['playerDataArray'][0]['prevOptValueArray'] = array();
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 31.5;
        $expData['playerDataArray'][0]['sideScore'] = 10;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array(3 => array(1, 30), 5 => array(1, 30));
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 16.5;
        $expData['playerDataArray'][1]['sideScore'] = -10;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array("Power", "Skill");
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2),
            'responder004 performed Power attack using [(2):1] against [^(3):1]; Defender ^(3) was captured; Attacker (2) rerolled 1 => 2. ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 3, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(2):1] against [^(3):1]; Defender ^(3) was captured; Attacker (2) rerolled 1 => 2'));
        $expData['gameActionLogCount'] = 34;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Focus Shadow Poison R Swing Die (with 13 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "fsp(R)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 13;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Focus", "Shadow", "Poison");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Trip Speed 14-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Focus", "Trip", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Speed 12-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "z(12)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Speed");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array("Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 25;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['optRequestArray'] = array("2" => array(1, 30), "4" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(2 => array(1, 30), 4 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 30;
        $expData['playerDataArray'][0]['sideScore'] = 7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "^(3)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 3;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 19.5;
        $expData['playerDataArray'][1]['sideScore'] = -7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Shadow", "Speed", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(24),
            'responder003 performed Power attack using [(1/30=30)!:25] against [(2):2]; Defender (2) was captured; Attacker (1/30=30)! rerolled from 25. Turbo die (1/30=30)! remained the same size, rolled 24. ',
            $retval, array(array(0, 4), array(1, 0)),
            $gameId, 3, 'Power', 0, 1, '', array(4 => 30));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(1/30=30)!:25] against [(2):2]; Defender (2) was captured; Attacker (1/30=30)! rerolled from 25'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die (1/30=30)! remained the same size, rolled 24'));
        $expData['gameActionLogCount'] = 36;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 24;
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "(2)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 2;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = 9;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "4-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "(4)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "6-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "(6)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "10-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "(10)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 8;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 18.5;
        $expData['playerDataArray'][1]['sideScore'] = -9;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Skill attack using [(6):1] against [ftz(14):1]; Defender ftz(14) was captured; Attacker (6) rerolled 1 => 1. ',
            $retval, array(array(1, 1), array(0, 1)),
            $gameId, 3, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(6):1] against [ftz(14):1]; Defender ftz(14) was captured; Attacker (6) rerolled 1 => 1'));
        $expData['gameActionLogCount'] = 37;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Speed 12-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "z(12)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Speed");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 24;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array("1" => array(1, 30), "3" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(1 => array(1, 30), 3 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][0]['sideScore'] = -5;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 14;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 32.5;
        $expData['playerDataArray'][1]['sideScore'] = 5;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Shadow", "Speed", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(23, 5),
            'Turbo die tz(1/30=30)! remained the same size. responder003 performed Trip attack using [tz(1/30=30)!] against [(6):1]; Attacker tz(1/30=30)! rolled 23; Defender (6) rerolled 1 => 5, was captured. ',
            $retval, array(array(0, 1), array(1, 1)),
            $gameId, 3, 'Trip', 0, 1, '', array(1 => 30));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tz(1/30=30)!] against [(6):1]; Attacker tz(1/30=30)! rolled 23; Defender (6) rerolled 1 => 5, was captured'));
        $expData['gameActionLogCount'] = 39;
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array("JustPerformedTripAttack");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 23;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "(6)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][0]['roundScore'] = 31;
        $expData['playerDataArray'][0]['sideScore'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "10-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "(10)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 8;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 29.5;
        $expData['playerDataArray'][1]['sideScore'] = -1;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(10),
            'responder004 performed Power attack using [(X=11):8] against [fsp(R=13):4]; Defender fsp(R=13) was captured; Attacker (X=11) rerolled 8 => 10. ',
            $retval, array(array(1, 2), array(0, 0)),
            $gameId, 3, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(X=11):8] against [fsp(R=13):4]; Defender fsp(R=13) was captured; Attacker (X=11) rerolled 8 => 10'));
        $expData['gameActionLogCount'] = 40;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 23;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Speed 12-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "z(12)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Speed");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 24;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array("0" => array(1, 30), "2" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(0 => array(1, 30), 2 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 44;
        $expData['playerDataArray'][0]['sideScore'] = 14;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][1]['canStillWin'] = null;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][2]['recipe'] = "fsp(R)";
        $expData['playerDataArray'][1]['capturedDieArray'][2]['sides'] = 13;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][1]['roundScore'] = 23;
        $expData['playerDataArray'][1]['sideScore'] = -14;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Speed", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(23, 7),
            'Turbo die tz(1/30=30)! remained the same size. responder003 performed Trip attack using [tz(1/30=30)!] against [(10):9]; Attacker tz(1/30=30)! rolled 23; Defender (10) rerolled 9 => 7, was captured. ',
            $retval, array(array(0, 0), array(1, 1)),
            $gameId, 3, 'Trip', 0, 1, '', array(0 => 30));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tz(1/30=30)!] against [(10):9]; Attacker tz(1/30=30)! rolled 23; Defender (10) rerolled 9 => 7, was captured'));
        $expData['gameActionLogCount'] = 42;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array("JustPerformedTripAttack");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 23;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][2]['recipe'] = "(10)";
        $expData['playerDataArray'][0]['capturedDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][0]['roundScore'] = 54;
        $expData['playerDataArray'][0]['sideScore'] = 24;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 10;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 18;
        $expData['playerDataArray'][1]['sideScore'] = -24;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(4, 3),
            'responder004 performed Skill attack using [(4):2,(X=11):10] against [z(12):12]; Defender z(12) was captured; Attacker (4) rerolled 2 => 4; Attacker (X=11) rerolled 10 => 3. ',
            $retval, array(array(1, 0), array(1, 1), array(0, 1)),
            $gameId, 3, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(4):2,(X=11):10] against [z(12):12]; Defender z(12) was captured; Attacker (4) rerolled 2 => 4; Attacker (X=11) rerolled 10 => 3'));
        $expData['gameActionLogCount'] = 43;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 24;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array(array(1, 30), array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(0 => array(1, 30), 1 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 48;
        $expData['playerDataArray'][0]['sideScore'] = 12;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['capturedDieArray'][3]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][3]['recipe'] = "z(12)";
        $expData['playerDataArray'][1]['capturedDieArray'][3]['sides'] = 12;
        $expData['playerDataArray'][1]['capturedDieArray'][3]['value'] = 12;
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][1]['sideScore'] = -12;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(1, 3),
            'Turbo die tz(1/30=30)! remained the same size. responder003 performed Trip attack using [tz(1/30=30)!] against [(4):4]; Attacker tz(1/30=30)! rolled 1; Defender (4) rerolled 4 => 3, was not captured. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 3, 'Trip', 0, 1, '', array(0 => 30));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tz(1/30=30)!] against [(4):4]; Attacker tz(1/30=30)! rolled 1; Defender (4) rerolled 4 => 3, was not captured'));
        $expData['gameActionLogCount'] = 45;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array("JustPerformedTripAttack", "JustPerformedUnsuccessfulAttack");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['capturedDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(4),
            'responder004 performed Power attack using [(4):3] against [tz(1/30=30)!:1]; Defender tz(1/30=30)! was captured; Attacker (4) rerolled 3 => 4. ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 3, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(4):3] against [tz(1/30=30)!:1]; Defender tz(1/30=30)! was captured; Attacker (4) rerolled 3 => 4'));
        $expData['gameActionLogCount'] = 46;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 24;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['canStillWin'] = false;
        $expData['playerDataArray'][0]['optRequestArray'] = array(array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(0 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 33;
        $expData['playerDataArray'][0]['sideScore'] = -18;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['capturedDieArray'][4]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][4]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][1]['capturedDieArray'][4]['sides'] = 30;
        $expData['playerDataArray'][1]['capturedDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 60;
        $expData['playerDataArray'][1]['sideScore'] = 18;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(16),
            'responder003 performed Power attack using [(1/30=30)!:24] against [(X=11):3]; Defender (X=11) was captured; Attacker (1/30=30)! rerolled from 24. Turbo die (1/30=30)! remained the same size, rolled 16. responder004 passed. ',
            $retval, array(array(0, 0), array(1, 1)),
            $gameId, 3, 'Power', 0, 1, '', array(0 => 30));

        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(1/30=30)!:24] against [(X=11):3]; Defender (X=11) was captured; Attacker (1/30=30)! rerolled from 24'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die (1/30=30)! remained the same size, rolled 16'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        $expData['gameActionLogCount'] = 49;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 16;
        $expData['playerDataArray'][0]['capturedDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][3]['recipe'] = "(X)";
        $expData['playerDataArray'][0]['capturedDieArray'][3]['sides'] = 11;
        $expData['playerDataArray'][0]['capturedDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][0]['roundScore'] = 44;
        $expData['playerDataArray'][0]['sideScore'] = -7;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 54.5;
        $expData['playerDataArray'][1]['sideScore'] = 7;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(6),
            'responder003 performed Power attack using [(1/30=30)!:16] against [(4):4]; Defender (4) was captured; Attacker (1/30=30)! rerolled from 16. Turbo die (1/30=30)! remained the same size, rolled 6. End of round: responder004 won round 3 (52.5 vs. 48). ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 3, 'Power', 0, 1, '', array(0 => 30));

        $expData['activePlayerIdx'] = null;
        $expData['gameActionLog'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'Game created by responder003'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=11'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: R=15 and option dice: tz(1/30=1)!'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [^(3):3, fsp(R=15):7, ftz(14):14, tz(1/30=1)!:1], responder004 rolled [(2):1, (4):4, (6):1, (10):6, (X=11):11]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=1)!].'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(4):4] against [^(3):3]; Defender ^(3) was captured; Attacker (4) rerolled 4 => 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Speed attack using [tz(1/30=1)!:1] against [(6):1]; Defender (6) was captured; Attacker tz(1/30=1)! rerolled from 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=1)! remained the same size, rolled 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(2):1] against [tz(1/30=1)!:1]; Defender tz(1/30=1)! was captured; Attacker (2) rerolled 1 => 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [ftz(14):14] against [(X=11):11]; Defender (X=11) was captured; Attacker ftz(14) rerolled 14 => 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(4):2] against [ftz(14):2]; Defender ftz(14) was captured; Attacker (4) rerolled 2 => 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(2):1,(10):6] against [fsp(R=15):7]; Defender fsp(R=15) was captured; Attacker (2) rerolled 1 => 2; Attacker (10) rerolled 6 => 8'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 1 (18.5 vs. 17)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 added a reserve die: r(1/30)!'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: R=11 and option dice: tz(1/30=1)!, (1/30=1)!'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [^(3):1, fsp(R=11):1, ftz(14):8, tz(1/30=1)!:1, (1/30=1)!:1], responder004 rolled [(2):2, (4):4, (6):3, (10):7, (X=11):8]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=1)!].'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Shadow attack using [fsp(R=11):1] against [(2):2]; Defender (2) was captured; Attacker fsp(R=11) rerolled 1 => 6'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(X=11):8] against [ftz(14):8]; Defender ftz(14) was captured; Attacker (X=11) rerolled 8 => 9'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [^(3):1,fsp(R=11):6] against [(10):7]; Defender (10) was captured; Attacker ^(3) rerolled 1 => 3; Attacker fsp(R=11) rerolled 6 => 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 gets another turn because a Time and Space die rolled odd'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [fsp(R=11):2,tz(1/30=1)!:1,(1/30=1)!:1] against [(4):4]; Defender (4) was captured; Attacker fsp(R=11) rerolled 2 => 7; Attacker tz(1/30=1)! rerolled from 1; Attacker (1/30=1)! rerolled from 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=1)! remained the same size, rolled 1; Turbo die (1/30=1)! remained the same size, rolled 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(6):3] against [(1/30=1)!:1]; Defender (1/30=1)! was captured; Attacker (6) rerolled 3 => 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Shadow attack using [fsp(R=11):7] against [(X=11):9]; Defender (X=11) was captured; Attacker fsp(R=11) rerolled 7 => 7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(6):1] against [tz(1/30=1)!:1]; Defender tz(1/30=1)! was captured; Attacker (6) rerolled 1 => 6'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(6):6] against [^(3):3]; Defender ^(3) was captured; Attacker (6) rerolled 6 => 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 2 (22 vs. 16)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 added a reserve die: rz(12)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: R=13 and option dice: tz(1/30=30)!, (1/30=30)!'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 3. Initial die values: responder003 rolled [^(3):1, fsp(R=13):4, ftz(14):1, tz(1/30=30)!:30, z(12):12, (1/30=30)!:25], responder004 rolled [(2):1, (4):2, (6):1, (10):9, (X=11):8]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=30)!].'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(2):1] against [^(3):1]; Defender ^(3) was captured; Attacker (2) rerolled 1 => 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(1/30=30)!:25] against [(2):2]; Defender (2) was captured; Attacker (1/30=30)! rerolled from 25'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die (1/30=30)! remained the same size, rolled 24'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(6):1] against [ftz(14):1]; Defender ftz(14) was captured; Attacker (6) rerolled 1 => 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tz(1/30=30)!] against [(6):1]; Attacker tz(1/30=30)! rolled 23; Defender (6) rerolled 1 => 5, was captured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(X=11):8] against [fsp(R=13):4]; Defender fsp(R=13) was captured; Attacker (X=11) rerolled 8 => 10'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tz(1/30=30)!] against [(10):9]; Attacker tz(1/30=30)! rolled 23; Defender (10) rerolled 9 => 7, was captured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(4):2,(X=11):10] against [z(12):12]; Defender z(12) was captured; Attacker (4) rerolled 2 => 4; Attacker (X=11) rerolled 10 => 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tz(1/30=30)!] against [(4):4]; Attacker tz(1/30=30)! rolled 1; Defender (4) rerolled 4 => 3, was not captured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(4):3] against [tz(1/30=30)!:1]; Defender tz(1/30=30)! was captured; Attacker (4) rerolled 3 => 4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(1/30=30)!:24] against [(X=11):3]; Defender (X=11) was captured; Attacker (1/30=30)! rerolled from 24'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die (1/30=30)! remained the same size, rolled 16'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(1/30=30)!:16] against [(4):4]; Defender (4) was captured; Attacker (1/30=30)! rerolled from 16'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die (1/30=30)! remained the same size, rolled 6'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 3 (52.5 vs. 48)'));
        $expData['gameActionLogCount'] = 52;
        $expData['gameState'] = "END_GAME";
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['canStillWin'] = true;
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        $expData['playerDataArray'][0]['gameScoreArray'] = array("D" => 0, "L" => 3, "W" => 0);
        $expData['playerDataArray'][0]['optRequestArray'] = array();
        $expData['playerDataArray'][0]['prevOptValueArray'] = array("3" => 30, "5" => 30);
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 0;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        $expData['playerDataArray'][1]['canStillWin'] = true;
        $expData['playerDataArray'][1]['gameScoreArray'] = array("D" => 0, "L" => 0, "W" => 3);
        $expData['playerDataArray'][1]['roundScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['validAttackTypeArray'] = array();

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }


    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This is a basic test of a game containing Dead Guy, to be used in
     * regression-testing Dead Guy behavior
     */
    public function test_interface_game_046() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 46;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(),
            'responder003', 'responder004', 'Dead Guy', 'Vicious', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'CHOOSE_AUXILIARY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Auxiliary', 'Berserk', 'Poison', 'Shadow'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Dead Guy', 'recipe' => '(0) (0) (0) (0) (0)', 'originalRecipe' => '(0) (0) (0) (0) (0)', 'artFilename' => 'deadguy.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Vicious', 'recipe' => '(4) (6) s(12) p(12) (X) +B(20)', 'originalRecipe' => '(4) (6) s(12) p(12) (X) +B(20)', 'artFilename' => 'vicious.png');
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Auxiliary', 'Berserk'), 'properties' => array(), 'recipe' => '+B(20)', 'description' => 'Auxiliary Berserk 20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(12)', 'description' => 'Shadow 12-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(12)', 'description' => 'Poison 12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Auxiliary', 'Berserk'), 'properties' => array(), 'recipe' => '+B(20)', 'description' => 'Auxiliary Berserk 20-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 chose to use auxiliary die
        $this->verify_api_reactToAuxiliary(
            array(),
            'Chose to add auxiliary die',
            $gameId, 'add', 5);

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][5]['properties'] = array('AddAuxiliary');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 chose to use auxiliary die
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_reactToAuxiliary(
            array(0, 0, 0, 0, 0, 13, 4, 6, 6, 5, 7),
            'responder004 chose to use auxiliary die +B(20) in this game. ',
            $gameId, 'add', 5);
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to use auxiliary die +B(20) in this game'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 chose to use auxiliary die +B(20) in this game'));
        $expData['gameActionLogCount'] += 2;
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Berserk', 'Poison', 'Shadow'));
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['playerDataArray'][0]['button']['recipe'] = '(0) (0) (0) (0) (0) B(20)';
        $expData['playerDataArray'][1]['button']['recipe'] = '(4) (6) s(12) p(12) (X) B(20)';
        $expData['playerDataArray'][0]['activeDieArray'][5]['recipe'] = 'B(20)';
        $expData['playerDataArray'][0]['activeDieArray'][5]['skills'] = array('Berserk');
        $expData['playerDataArray'][0]['activeDieArray'][5]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = 'Berserk 20-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][5]['recipe'] = 'B(20)';
        $expData['playerDataArray'][1]['activeDieArray'][5]['skills'] = array('Berserk');
        $expData['playerDataArray'][1]['activeDieArray'][5]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][5]['description'] = 'Berserk 20-sided die';

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 submits die values
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(array(15), $gameId, 1, array('X' => 20), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=20'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(0):0, (0):0, (0):0, (0):0, (0):0, B(20):13], responder004 rolled [(4):4, (6):6, s(12):6, p(12):5, (X=20):15, B(20):7].'));
        $expData['gameActionLogCount'] += 2;
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Berserk');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 10;
        $expData['playerDataArray'][0]['sideScore'] = -6.0;
        $expData['playerDataArray'][1]['roundScore'] = 19;
        $expData['playerDataArray'][1]['sideScore'] = 6.0;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 13;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = 'X Swing Die (with 20 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 7;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Berserk attack using [B(20):13] against [s(12):6,B(20):7]
        $this->verify_api_submitTurn(
            array(6),
            'responder003 performed Berserk attack using [B(20):13] against [s(12):6,B(20):7]; Defender s(12) was captured; Defender B(20) was captured; Attacker B(20) changed size from 20 to 10 sides, recipe changed from B(20) to (10), rerolled 13 => 6. ',
            $retval, array(array(0, 5), array(1, 2), array(1, 5)),
            $gameId, 1, 'Berserk', 0, 1, '');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Berserk attack using [B(20):13] against [s(12):6,B(20):7]; Defender s(12) was captured; Defender B(20) was captured; Attacker B(20) changed size from 20 to 10 sides, recipe changed from B(20) to (10), rerolled 13 => 6'));
        $expData['gameActionLogCount'] += 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 37;
        $expData['playerDataArray'][1]['roundScore'] = 3;
        $expData['playerDataArray'][0]['sideScore'] = 22.7;
        $expData['playerDataArray'][1]['sideScore'] = -22.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][5]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][5]['recipe'] = '(10)';
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = '10-sided die';
        $expData['playerDataArray'][0]['activeDieArray'][5]['properties'] = array('HasJustSplit', 'JustPerformedBerserkAttack');
        $expData['playerDataArray'][0]['activeDieArray'][5]['skills'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 5, 1);
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][0]['capturedDieArray'] = array(
            array('value' => 6, 'sides' => 12, 'recipe' => 's(12)', 'properties' => array('WasJustCaptured')),
            array('value' => 7, 'sides' => 20, 'recipe' => 'B(20)', 'properties' => array('WasJustCaptured')),
        );
        $expData['playerDataArray'][0]['canStillWin'] = true;
        $expData['playerDataArray'][1]['canStillWin'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder004 surrenders
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(0, 0, 0, 0, 0, 13, 4, 2, 6, 5, 7),
            'responder004 surrendered. End of round: responder003 won round 1 because opponent surrendered. ',
            $retval, array(),
            $gameId, 1, 'Surrender', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 surrendered'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: responder003 won round 1 because opponent surrendered'));
        $expData['gameActionLogCount'] += 2;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(20)', 'description' => 'Berserk 20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(12)', 'description' => 'Shadow 12-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(12)', 'description' => 'Poison 12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(20)', 'description' => 'Berserk 20-sided die'),
        );
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array('X' => 20);
        $expData['validAttackTypeArray'] = array();
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['activePlayerIdx'] = NULL;
        $expData['roundNumber'] = 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder004 submits die values
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(15),
            $gameId, 2, array('X' => 20), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=20'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [(0):0, (0):0, (0):0, (0):0, (0):0, B(20):13], responder004 rolled [(4):4, (6):2, s(12):6, p(12):5, (X=20):15, B(20):7].'));
        $expData['gameActionLogCount'] += 2;
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Berserk');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 10;
        $expData['playerDataArray'][0]['sideScore'] = -6.0;
        $expData['playerDataArray'][1]['roundScore'] = 19;
        $expData['playerDataArray'][1]['sideScore'] = 6.0;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 13;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = 'X Swing Die (with 20 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 7;
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - responder003 performed Berserk attack using [B(20):13] against [(4):4,(6):2,B(20):7]
        $this->verify_api_submitTurn(
            array(6),
            'responder003 performed Berserk attack using [B(20):13] against [(4):4,(6):2,B(20):7]; Defender (4) was captured; Defender (6) was captured; Defender B(20) was captured; Attacker B(20) changed size from 20 to 10 sides, recipe changed from B(20) to (10), rerolled 13 => 6. ',
            $retval, array(array(0, 5), array(1, 0), array(1, 1), array(1, 5)),
            $gameId, 2, 'Berserk', 0, 1, '');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Berserk attack using [B(20):13] against [(4):4,(6):2,B(20):7]; Defender (4) was captured; Defender (6) was captured; Defender B(20) was captured; Attacker B(20) changed size from 20 to 10 sides, recipe changed from B(20) to (10), rerolled 13 => 6'));
        $expData['gameActionLogCount'] += 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['roundScore'] = 35;
        $expData['playerDataArray'][1]['roundScore'] = 4;
        $expData['playerDataArray'][0]['sideScore'] = 20.7;
        $expData['playerDataArray'][1]['sideScore'] = -20.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][5]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][5]['recipe'] = '(10)';
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = '10-sided die';
        $expData['playerDataArray'][0]['activeDieArray'][5]['properties'] = array('HasJustSplit', 'JustPerformedBerserkAttack');
        $expData['playerDataArray'][0]['activeDieArray'][5]['skills'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 5, 1);
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 2);
        $expData['playerDataArray'][0]['capturedDieArray'] = array(
            array('value' => 4, 'sides' => 4, 'recipe' => '(4)', 'properties' => array('WasJustCaptured')),
            array('value' => 2, 'sides' => 6, 'recipe' => '(6)', 'properties' => array('WasJustCaptured')),
            array('value' => 7, 'sides' => 20, 'recipe' => 'B(20)', 'properties' => array('WasJustCaptured')),
        );
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;

        array_pop($expData['gameActionLog']);

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
     }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This is a test of a trip mighty die performing a valid trip attack
     * against a konstant die with a value larger than the current size
     */
    public function test_interface_game_047() {
        global $RANDOMBM_SKILL;

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 47;
        $_SESSION = $this->mock_test_user_login('responder003');

        $gameId = $this->verify_api_createGame(
            array(
                'bm_rand' => array(
                    0, 2, 3, 3, 5,          // die sizes for r3: 4, 8, 10, 10, 20
                    0, 3, 2, 1, 0, 1,       // distribution of skills onto dice for r3
                    0, 5, 1, 3, 0,          // die sizes for r4: 4, 4, 6, 10, 20
                    3, 4, 2, 1, 2, 1,       // distribution of skills onto dice for r4
                    1, 1, 1,                // initial die rolls for r3 (note: Maximum dice don't use random values)
                    3, 2, 5, 2, 3,          // initial die rolls for r4
                ),
                'bm_skill_rand' => array(
                    $RANDOMBM_SKILL['H'], $RANDOMBM_SKILL['M'], $RANDOMBM_SKILL['t'],
                    $RANDOMBM_SKILL['v'], $RANDOMBM_SKILL['B'], $RANDOMBM_SKILL['k'],
                ),
            ),
            'responder003', 'responder004', 'RandomBMMixed', 'RandomBMMixed', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Berserk', 'Konstant', 'Maximum', 'Mighty', 'RandomBMMixed', 'Trip', 'Value'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'RandomBMMixed', 'recipe' => 'Ht(4) Mt(8) M(10) H(10) (20)', 'originalRecipe' => 'Ht(4) Mt(8) M(10) H(10) (20)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'RandomBMMixed', 'recipe' => '(4) Bk(4) Bk(6) v(10) v(20)', 'originalRecipe' => '(4) Bk(4) Bk(6) v(10) v(20)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 4, 'skills' => array('Mighty', 'Trip'), 'properties' => array(), 'recipe' => 'Ht(4)', 'description' => 'Mighty Trip 4-sided die'),
            array('value' => 8, 'sides' => 8, 'skills' => array('Maximum', 'Trip'), 'properties' => array(), 'recipe' => 'Mt(8)', 'description' => 'Maximum Trip 8-sided die'),
            array('value' => 10, 'sides' => 10, 'skills' => array('Maximum'), 'properties' => array(), 'recipe' => 'M(10)', 'description' => 'Maximum 10-sided die'),
            array('value' => 1, 'sides' => 10, 'skills' => array('Mighty'), 'properties' => array(), 'recipe' => 'H(10)', 'description' => 'Mighty 10-sided die'),
            array('value' => 1, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 3, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => 2, 'sides' => 4, 'skills' => array('Berserk', 'Konstant'), 'properties' => array(), 'recipe' => 'Bk(4)', 'description' => 'Berserk Konstant 4-sided die'),
            array('value' => 5, 'sides' => 6, 'skills' => array('Berserk', 'Konstant'), 'properties' => array(), 'recipe' => 'Bk(6)', 'description' => 'Berserk Konstant 6-sided die'),
            array('value' => 2, 'sides' => 10, 'skills' => array('Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'v(10)', 'description' => 'Value 10-sided die'),
            array('value' => 3, 'sides' => 20, 'skills' => array('Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'v(20)', 'description' => 'Value 20-sided die'),
        );
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 26;
        $expData['playerDataArray'][1]['roundScore'] = 9.5;
        $expData['playerDataArray'][0]['sideScore'] = 11.0;
        $expData['playerDataArray'][1]['sideScore'] = -11.0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [Ht(4):1, Mt(8):8, M(10):10, H(10):1, (20):1], responder004 rolled [(4):3, Bk(4):2, Bk(6):5, v(10):2, v(20):3]. responder003 has dice which are not counted for initiative due to die skills: [Ht(4), Mt(8)].'));
        $expData['gameActionLogCount'] = 2;
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Trip');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(2),
            'responder003 performed Trip attack using [Ht(4):1] against [Bk(6):5]; Attacker Ht(4) recipe changed from Ht(4) to Ht(6), rerolled 1 => 2; Defender Bk(6) does not reroll, was not captured. ',
            $retval, array(array(0, 0), array(1, 2)),
            $gameId, 1, 'Trip', 0, 1, '');
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     */
    public function test_interface_game_048() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 48;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(6, 1, 6, 5, 12, 11),
            'responder003', 'responder004', 'North Carolina', 'Wiseman', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Fire', 'Ornery', 'Poison', 'Stinger', 'Trip', 'Turbo', 'Weak'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'North Carolina', 'recipe' => 'pF(10) o(10) (V)! gt(V) h(V)', 'originalRecipe' => 'pF(10) o(10) (V)! gt(V) h(V)', 'artFilename' => 'northcarolina.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Wiseman', 'recipe' => '(20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20)', 'artFilename' => 'wiseman.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('V' => array(6, 12));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 10, 'skills' => array('Poison', 'Fire'), 'properties' => array(), 'recipe' => 'pF(10)', 'description' => 'Poison Fire 10-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Ornery'), 'properties' => array(), 'recipe' => 'o(10)', 'description' => 'Ornery 10-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Turbo'), 'properties' => array(), 'recipe' => '(V)!', 'description' => 'Turbo V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Stinger', 'Trip'), 'properties' => array(), 'recipe' => 'gt(V)', 'description' => 'Stinger Trip V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Weak'), 'properties' => array(), 'recipe' => 'h(V)', 'description' => 'Weak V Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['gameId'] = $gameId;
        $expData['playerDataArray'][0]['playerId'] = $this->user_ids['responder003'];
        $expData['playerDataArray'][1]['playerId'] = $this->user_ids['responder004'];

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(8, 2, 1),
            $gameId, 1, array('V' => 8), NULL);

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: V=8'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [pF(10):6, o(10):1, (V=8)!:8, gt(V=8):2, h(V=8):1], responder004 rolled [(20):6, (20):5, (20):12, (20):11]. responder003 has dice which are not counted for initiative due to die skills: [gt(V=8)].'));
        $expData['gameActionLogCount'] = 3;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Turbo V Swing Die (with 8 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Stinger Trip V Swing Die (with 8 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Weak V Swing Die (with 8 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 7;
        $expData['playerDataArray'][0]['sideScore'] = -22;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array("2" => array(6, 7, 8, 9, 10, 11, 12));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 11;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 22;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(6, 8, 3, 8),
            'responder003 performed Skill attack using [o(10):1,(V=8)!:8,gt(V=8):2,h(V=8):1] against [(20):12]; Defender (20) was captured; Attacker o(10) rerolled 1 => 6; Attacker (V=8)! rerolled from 8; Attacker gt(V=8) rerolled 2 => 8; Attacker h(V=8) changed size from 8 to 6 sides, recipe changed from h(V=8) to h(V=6), rerolled 1 => 3. Turbo die (V=8)! changed size from 8 to 11 sides, recipe changed from (V=8)! to (V=11)!, rolled 8. ',
            $retval, array(array(0, 1), array(0, 2), array(0, 3), array(0, 4), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '', array(2 => 11));

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [o(10):1,(V=8)!:8,gt(V=8):2,h(V=8):1] against [(20):12]; Defender (20) was captured; Attacker o(10) rerolled 1 => 6; Attacker (V=8)! rerolled from 8; Attacker gt(V=8) rerolled 2 => 8; Attacker h(V=8) changed size from 8 to 6 sides, recipe changed from h(V=8) to h(V=6), rerolled 1 => 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die (V=8)! changed size from 8 to 11 sides, recipe changed from (V=8)! to (V=11)!, rolled 8'));
        $expData['gameActionLogCount'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Turbo V Swing Die (with 11 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("HasJustTurboed");
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Weak V Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array("HasJustShrunk");
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 3;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "(20)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 12;
        $expData['playerDataArray'][0]['roundScore'] = 27.5;
        $expData['playerDataArray'][0]['sideScore'] = -1.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 11;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][1]['sideScore'] = 1.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(11),
            'responder004 performed Power attack using [(20):11] against [gt(V=8):8]; Defender gt(V=8) was captured; Attacker (20) rerolled 11 => 11. ',
            $retval, array(array(1, 2), array(0, 3)),
            $gameId, 1, 'Power', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):11] against [gt(V=8):8]; Defender gt(V=8) was captured; Attacker (20) rerolled 11 => 11'));
        $expData['gameActionLogCount'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Weak V Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "h(V)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Weak");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 3;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 23.5;
        $expData['playerDataArray'][0]['sideScore'] = -9.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "gt(V)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 8;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 8;
        $expData['playerDataArray'][1]['roundScore'] = 38;
        $expData['playerDataArray'][1]['sideScore'] = 9.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(),
            'responder003 chose to perform a Skill attack using [(V=11)!:8] against [(20):11]; responder003 must turn down fire dice to complete this attack. ',
            $retval, array(array(0, 2), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '', array(2 => 9));

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to perform a Skill attack using [(V=11)!:8] against [(20):11]; responder003 must turn down fire dice to complete this attack'));
        $expData['gameActionLogCount'] = 7;
        $expData['gameState'] = "ADJUST_FIRE_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("IsAttacker");
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array("IsAttackTarget");
        $expData['validAttackTypeArray'] = array("Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_adjustFire(
            array(),
            'responder003 chose to abandon this attack and start over. ',
            $retval, $gameId, 1, 'cancel', array(), array());

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to abandon this attack and start over'));
        $expData['gameActionLogCount'] = 8;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array("2" => array(6, 7, 8, 9, 10, 11, 12));
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(),
            'responder003 chose to perform a Power attack using [h(V=6):3] against [(20):5]; responder003 must turn down fire dice to complete this attack. ',
            $retval, array(array(0, 3), array(1, 1)),
            $gameId, 1, 'Power', 0, 1, '', array());

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to perform a Power attack using [h(V=6):3] against [(20):5]; responder003 must turn down fire dice to complete this attack'));
        $expData['gameActionLogCount'] = 9;
        $expData['gameState'] = "ADJUST_FIRE_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array("IsAttacker");
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array("IsAttackTarget");
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_adjustFire(
            array(1, 1),
            'responder003 turned down fire dice: pF(10) from 6 to 4; Defender (20) was captured; Attacker h(V=6) changed size from 6 to 4 sides, recipe changed from h(V=6) to h(V=4), rerolled 3 => 1. responder003\'s idle ornery dice rerolled at end of turn: o(10) rerolled 6 => 1. ',
            $retval, $gameId, 1, 'turndown', array(0), array('4'));
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This is a test to make sure that fire overshooting declining works okay with turbo.
     *
     * We're using responder005 because this player has fire overshooting turned on.
     */
    public function test_interface_game_049() {

        // responder005 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder005 as soon as you've done so
        $this->game_number = 49;
        $_SESSION = $this->mock_test_user_login('responder005');


        $gameId = $this->verify_api_createGame(
            array(6, 1, 6, 5, 12, 11),
            'responder005', 'responder004', 'North Carolina', 'Wiseman', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder005', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Fire', 'Ornery', 'Poison', 'Stinger', 'Trip', 'Turbo', 'Weak'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'North Carolina', 'recipe' => 'pF(10) o(10) (V)! gt(V) h(V)', 'originalRecipe' => 'pF(10) o(10) (V)! gt(V) h(V)', 'artFilename' => 'northcarolina.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Wiseman', 'recipe' => '(20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20)', 'artFilename' => 'wiseman.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('V' => array(6, 12));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 10, 'skills' => array('Poison', 'Fire'), 'properties' => array(), 'recipe' => 'pF(10)', 'description' => 'Poison Fire 10-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Ornery'), 'properties' => array(), 'recipe' => 'o(10)', 'description' => 'Ornery 10-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Turbo'), 'properties' => array(), 'recipe' => '(V)!', 'description' => 'Turbo V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Stinger', 'Trip'), 'properties' => array(), 'recipe' => 'gt(V)', 'description' => 'Stinger Trip V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Weak'), 'properties' => array(), 'recipe' => 'h(V)', 'description' => 'Weak V Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['gameId'] = $gameId;
        $expData['playerDataArray'][0]['playerId'] = $this->user_ids['responder005'];
        $expData['playerDataArray'][1]['playerId'] = $this->user_ids['responder004'];

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(8, 2, 1),
            $gameId, 1, array('V' => 8), NULL);

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005 set swing values: V=8'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder005 won initiative for round 1. Initial die values: responder005 rolled [pF(10):6, o(10):1, (V=8)!:8, gt(V=8):2, h(V=8):1], responder004 rolled [(20):6, (20):5, (20):12, (20):11]. responder005 has dice which are not counted for initiative due to die skills: [gt(V=8)].'));
        $expData['gameActionLogCount'] = 3;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Turbo V Swing Die (with 8 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Stinger Trip V Swing Die (with 8 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Weak V Swing Die (with 8 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 7;
        $expData['playerDataArray'][0]['sideScore'] = -22;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array("2" => array(6, 7, 8, 9, 10, 11, 12));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 11;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 22;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(6, 8, 3, 5),
            'responder005 performed Skill attack using [o(10):1,(V=8)!:8,gt(V=8):2,h(V=8):1] against [(20):12]; Defender (20) was captured; Attacker o(10) rerolled 1 => 6; Attacker (V=8)! rerolled from 8; Attacker gt(V=8) rerolled 2 => 8; Attacker h(V=8) changed size from 8 to 6 sides, recipe changed from h(V=8) to h(V=6), rerolled 1 => 3. Turbo die (V=8)! changed size from 8 to 11 sides, recipe changed from (V=8)! to (V=11)!, rolled 5. ',
            $retval, array(array(0, 1), array(0, 2), array(0, 3), array(0, 4), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '', array(2 => 11));

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005 performed Skill attack using [o(10):1,(V=8)!:8,gt(V=8):2,h(V=8):1] against [(20):12]; Defender (20) was captured; Attacker o(10) rerolled 1 => 6; Attacker (V=8)! rerolled from 8; Attacker gt(V=8) rerolled 2 => 8; Attacker h(V=8) changed size from 8 to 6 sides, recipe changed from h(V=8) to h(V=6), rerolled 1 => 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'Turbo die (V=8)! changed size from 8 to 11 sides, recipe changed from (V=8)! to (V=11)!, rolled 5'));
        $expData['gameActionLogCount'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Turbo V Swing Die (with 11 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("HasJustTurboed");
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Weak V Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array("HasJustShrunk");
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 3;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "(20)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 12;
        $expData['playerDataArray'][0]['roundScore'] = 27.5;
        $expData['playerDataArray'][0]['sideScore'] = -1.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 11;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][1]['sideScore'] = 1.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(11),
            'responder004 performed Power attack using [(20):11] against [gt(V=8):8]; Defender gt(V=8) was captured; Attacker (20) rerolled 11 => 11. ',
            $retval, array(array(1, 2), array(0, 3)),
            $gameId, 1, 'Power', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder005');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):11] against [gt(V=8):8]; Defender gt(V=8) was captured; Attacker (20) rerolled 11 => 11'));
        $expData['gameActionLogCount'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Weak V Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "h(V)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Weak");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 3;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 23.5;
        $expData['playerDataArray'][0]['sideScore'] = -9.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "gt(V)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 8;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 8;
        $expData['playerDataArray'][1]['roundScore'] = 38;
        $expData['playerDataArray'][1]['sideScore'] = 9.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(),
            'responder005 chose to perform a Power attack using [(V=11)!:5] against [(20):5]; responder005 must decide whether to turn down fire dice. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 1, 'Power', 0, 1, '', array(2 => 9));

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005 chose to perform a Power attack using [(V=11)!:5] against [(20):5]; responder005 must decide whether to turn down fire dice'));
        $expData['gameActionLogCount'] = 7;
        $expData['gameState'] = "ADJUST_FIRE_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("IsAttacker");
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array("IsAttackTarget");
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_adjustFire(
            array(4, 1),
            'responder005 chose not to turn down fire dice; Defender (20) was captured; Attacker (V=11)! rerolled from 5. Turbo die (V=11)! changed size from 11 to 9 sides, recipe changed from (V=11)! to (V=9)!, rolled 4. responder005\'s idle ornery dice rerolled at end of turn: o(10) rerolled 6 => 1. ',
            $retval, $gameId, 1, 'no_turndown');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005 chose not to turn down fire dice; Defender (20) was captured; Attacker (V=11)! rerolled from 5'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' =>  'Turbo die (V=11)! changed size from 11 to 9 sides, recipe changed from (V=11)! to (V=9)!, rolled 4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder005', 'message' => 'responder005\'s idle ornery dice rerolled at end of turn: o(10) rerolled 6 => 1'));

        $expData['activePlayerIdx'] = 1;
        $expData['gameActionLogCount'] = 10;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array("HasJustRerolledOrnery");
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("IsAttacker");
        $expData['playerDataArray'][0]['turboSizeArray'] = array(2 => array(6, 7, 8, 9, 10, 11, 12));
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array("IsAttackTarget");
        $expData['validAttackTypeArray'] = array('Power', 'Skill');

        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Turbo V Swing Die (with 9 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Weak V Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "h(V)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Weak");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1] = $expData['playerDataArray'][1]['activeDieArray'][2];
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 42.5;
        $expData['playerDataArray'][0]['sideScore'] = 9.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array('WasJustCaptured');
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "(20)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "gt(V)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 8;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 8;
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][1]['sideScore'] = -9.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }


    /**
     * @depends responder00Test::test_request_savePlayerInfo
     */
    public function test_interface_game_052() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 52;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(6, 1, 3, 4, 3, 3),
            'responder003', 'responder004', 'North Carolina', 'gman97216', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Fire', 'Mighty', 'Ornery', 'Poison', 'Radioactive', 'Stinger', 'Trip', 'Turbo', 'Weak'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'North Carolina', 'recipe' => 'pF(10) o(10) (V)! gt(V) h(V)', 'originalRecipe' => 'pF(10) o(10) (V)! gt(V) h(V)', 'artFilename' => 'northcarolina.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'gman97216', 'recipe' => 'Hog%(4) Hog%(4) Hog%(4) Hog%(4)', 'originalRecipe' => 'Hog%(4) Hog%(4) Hog%(4) Hog%(4)', 'artFilename' => 'gman97216.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('V' => array(6, 12));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 10, 'skills' => array('Poison', 'Fire'), 'properties' => array(), 'recipe' => 'pF(10)', 'description' => 'Poison Fire 10-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Ornery'), 'properties' => array(), 'recipe' => 'o(10)', 'description' => 'Ornery 10-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Turbo'), 'properties' => array(), 'recipe' => '(V)!', 'description' => 'Turbo V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Stinger', 'Trip'), 'properties' => array(), 'recipe' => 'gt(V)', 'description' => 'Stinger Trip V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Weak'), 'properties' => array(), 'recipe' => 'h(V)', 'description' => 'Weak V Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
        );
        $expData['gameId'] = $gameId;
        $expData['playerDataArray'][0]['playerId'] = $this->user_ids['responder003'];
        $expData['playerDataArray'][1]['playerId'] = $this->user_ids['responder004'];

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(1, 2, 1),
            $gameId, 1, array('V' => 8), NULL);

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: V=8'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [pF(10):6, o(10):1, (V=8)!:1, gt(V=8):2, h(V=8):1], responder004 rolled [Hog%(4):3, Hog%(4):4, Hog%(4):3, Hog%(4):3]. responder003 has dice which are not counted for initiative due to die skills: [gt(V=8)]. responder004 has dice which are not counted for initiative due to die skills: [Hog%(4), Hog%(4), Hog%(4), Hog%(4)].'));
        $expData['gameActionLogCount'] = 3;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Turbo V Swing Die (with 8 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Stinger Trip V Swing Die (with 8 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Weak V Swing Die (with 8 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 7;
        $expData['playerDataArray'][0]['sideScore'] = -0.7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array("2" => array(6, 7, 8, 9, 10, 11, 12));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][1]['roundScore'] = 8;
        $expData['playerDataArray'][1]['sideScore'] = 0.7;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(),
            'responder003 chose to perform a Power attack using [(V=8)!:1] against [Hog%(4):3]; responder003 must turn down fire dice to complete this attack. ',
            $retval, array(array(0, 2), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '', array(2 => 6));

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to perform a Power attack using [(V=8)!:1] against [Hog%(4):3]; responder003 must turn down fire dice to complete this attack'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameState'] = 'ADJUST_FIRE_DICE';
        $expData['validAttackTypeArray'] = array("Power");
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('IsAttacker');
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array('IsAttackTarget');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_adjustFire(
            array(1, 2, 3),
            'responder003 turned down fire dice: pF(10) from 6 to 4; Defender Hog%(4) was captured; Attacker (V=8)! showing 1 changed to (V=8), which then split into: (V=4) showing 1, and (V=4) showing 2. responder003\'s idle ornery dice rerolled at end of turn: o(10) rerolled 1 => 3. ',
            $retval, $gameId, 1, 'turndown', array(0), array('4'));
    }


    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This is a test of a trip weak die which has no valid trip attack
     * because all possible targets are twin dice
     */
    public function test_interface_game_050() {
        global $RANDOMBM_SKILL;

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 50;
        $_SESSION = $this->mock_test_user_login('responder003');

        $gameId = $this->verify_api_createGame(
            array(
                'bm_rand' => array(
                    0, 1, 3, 3, 5,                // die sizes for r3: 4, 6, 10, 10, 20
                    0, 3, 2, 1, 0, 1,             // distribution of skills onto dice for r3
                    1, 1, 1,                      // initial die rolls for r3 (note: Maximum dice don't use random values)
                    3, 2, 5, 2, 3, 1, 1, 1, 1, 1, // initial die rolls for r4 (twin dice)
                ),
                'bm_skill_rand' => array(
                    $RANDOMBM_SKILL['h'], $RANDOMBM_SKILL['M'], $RANDOMBM_SKILL['t'],
                ),
            ),
            'responder003', 'responder004', 'RandomBMMixed', 'Craps', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Maximum', 'RandomBMMixed', 'Trip', 'Weak'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'RandomBMMixed', 'recipe' => 'ht(4) Mt(6) M(10) h(10) (20)', 'originalRecipe' => 'ht(4) Mt(6) M(10) h(10) (20)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Craps', 'recipe' => '(6,6) (6,6) (6,6) (6,6) (6,6)', 'originalRecipe' => '(6,6) (6,6) (6,6) (6,6) (6,6)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 4, 'skills' => array('Weak', 'Trip'), 'properties' => array(), 'recipe' => 'ht(4)', 'description' => 'Weak Trip 4-sided die'),
            array('value' => 6, 'sides' => 6, 'skills' => array('Maximum', 'Trip'), 'properties' => array(), 'recipe' => 'Mt(6)', 'description' => 'Maximum Trip 6-sided die'),
            array('value' => 10, 'sides' => 10, 'skills' => array('Maximum'), 'properties' => array(), 'recipe' => 'M(10)', 'description' => 'Maximum 10-sided die'),
            array('value' => 1, 'sides' => 10, 'skills' => array('Weak'), 'properties' => array(), 'recipe' => 'h(10)', 'description' => 'Weak 10-sided die'),
            array('value' => 1, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 5, 'sides' => 12, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(6,6)', 'description' => 'Twin Die (both with 6 sides)', 'subdieArray' => array(array('sides' => 6, 'value' => 3), array('sides' => 6, 'value' => 2))),
            array('value' => 7, 'sides' => 12, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(6,6)', 'description' => 'Twin Die (both with 6 sides)', 'subdieArray' => array(array('sides' => 6, 'value' => 5), array('sides' => 6, 'value' => 2))),
            array('value' => 4, 'sides' => 12, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(6,6)', 'description' => 'Twin Die (both with 6 sides)', 'subdieArray' => array(array('sides' => 6, 'value' => 3), array('sides' => 6, 'value' => 1))),
            array('value' => 2, 'sides' => 12, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(6,6)', 'description' => 'Twin Die (both with 6 sides)', 'subdieArray' => array(array('sides' => 6, 'value' => 1), array('sides' => 6, 'value' => 1))),
            array('value' => 2, 'sides' => 12, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(6,6)', 'description' => 'Twin Die (both with 6 sides)', 'subdieArray' => array(array('sides' => 6, 'value' => 1), array('sides' => 6, 'value' => 1))),
        );
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][0]['sideScore'] = -3.3;
        $expData['playerDataArray'][1]['sideScore'] = 3.3;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [ht(4):1, Mt(6):6, M(10):10, h(10):1, (20):1], responder004 rolled [(6,6):5, (6,6):7, (6,6):4, (6,6):2, (6,6):2]. responder003 has dice which are not counted for initiative due to die skills: [ht(4), Mt(6)].'));
        $expData['gameActionLogCount'] = 2;
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Trip');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        /////
        // Turn 1: responder003 performs skill attack, with the side effect of reducing ht(4) to ht(2)
        $this->verify_api_submitTurn(
            array(1, 14),
            'responder003 performed Skill attack using [ht(4):1,(20):1] against [(6,6):2]; Defender (6,6) was captured; Attacker ht(4) changed size from 4 to 2 sides, recipe changed from ht(4) to ht(2), rerolled 1 => 1; Attacker (20) rerolled 1 => 14. ',
            $retval, array(array(0, 0), array(0, 4), array(1, 4)),
            $gameId, 1, 'Skill', 0, 1, ''
        );

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [ht(4):1,(20):1] against [(6,6):2]; Defender (6,6) was captured; Attacker ht(4) changed size from 4 to 2 sides, recipe changed from ht(4) to ht(2), rerolled 1 => 1; Attacker (20) rerolled 1 => 14'));
        $expData['gameActionLogCount'] += 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = 'ht(2)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = 'Weak Trip 2-sided die';
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('HasJustShrunk');
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 14;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 1);
        $expData['playerDataArray'][0]['capturedDieArray'] = array(
            array('value' => 2, 'sides' => 12, 'recipe' => '(6,6)', 'properties' => array('WasJustCaptured', 'Twin')),
        );
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 36;
        $expData['playerDataArray'][1]['roundScore'] = 24;
        $expData['playerDataArray'][0]['sideScore'] = 8.0;
        $expData['playerDataArray'][1]['sideScore'] = -8.0;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        /////
        // Turn 1: responder004 captures Mt(6), with the side effect that ht(2) is now responder003's only trip die
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3, 2),
            'responder004 performed Power attack using [(6,6):7] against [Mt(6):6]; Defender Mt(6) was captured; Attacker (6,6) rerolled 7 => 5. ',
            $retval, array(array(0, 1), array(1, 1)),
            $gameId, 1, 'Power', 1, 0, ''
        );
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(6,6):7] against [Mt(6):6]; Defender Mt(6) was captured; Attacker (6,6) rerolled 7 => 5'));
        $expData['gameActionLogCount'] += 1;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][1]['subdieArray'][0]['value'] = 3;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array('Twin');
        $expData['playerDataArray'][1]['capturedDieArray'] = array(
            array('value' => 6, 'sides' => 6, 'recipe' => 'Mt(6)', 'properties' => array('WasJustCaptured')),
        );
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 33;
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][0]['sideScore'] = 2.0;
        $expData['playerDataArray'][1]['sideScore'] = -2.0;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }


    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * Regression-testing Dead Guy skill attack behavior
     */
    public function test_interface_game_051() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 51;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(),
            'responder003', 'responder004', 'Dead Guy', 'Gawaine', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'CHOOSE_AUXILIARY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Auxiliary'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Dead Guy', 'recipe' => '(0) (0) (0) (0) (0)', 'originalRecipe' => '(0) (0) (0) (0) (0)', 'artFilename' => 'deadguy.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Gawaine', 'recipe' => '(4) (4) (12) (20) (X) +(6)', 'originalRecipe' => '(4) (4) (12) (20) (X) +(6)', 'artFilename' => 'gawaine.png');
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 0, 'skills' => array(), 'properties' => array(), 'recipe' => '(0)', 'description' => '0-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Auxiliary'), 'properties' => array(), 'recipe' => '+(6)', 'description' => 'Auxiliary 6-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Auxiliary'), 'properties' => array(), 'recipe' => '+(6)', 'description' => 'Auxiliary 6-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 chose to use auxiliary die
        $this->verify_api_reactToAuxiliary(
            array(),
            'Chose to add auxiliary die',
            $gameId, 'add', 5);

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][5]['properties'] = array('AddAuxiliary');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 chose to use auxiliary die
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_reactToAuxiliary(
            array(0, 0, 0, 0, 0, 3, 1, 3, 4, 15, 4),
            'responder004 chose to use auxiliary die +(6) in this game. ',
            $gameId, 'add', 5);
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to use auxiliary die +(6) in this game'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 chose to use auxiliary die +(6) in this game'));
        $expData['gameActionLogCount'] = 3;
        $expData['gameSkillsInfo'] = $this->get_skill_info(array());
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['playerDataArray'][0]['button']['recipe'] = '(0) (0) (0) (0) (0) (6)';
        $expData['playerDataArray'][1]['button']['recipe'] = '(4) (4) (12) (20) (X) (6)';
        $expData['playerDataArray'][0]['activeDieArray'][5]['recipe'] = '(6)';
        $expData['playerDataArray'][0]['activeDieArray'][5]['skills'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][5]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = '6-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][5]['recipe'] = '(6)';
        $expData['playerDataArray'][1]['activeDieArray'][5]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][5]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][5]['description'] = '6-sided die';

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 submits die values
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(array(8), $gameId, 1, array('X' => 13), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=13'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(0):0, (0):0, (0):0, (0):0, (0):0, (6):3], responder004 rolled [(4):1, (4):3, (12):4, (20):15, (X=13):8, (6):4].'));
        $expData['gameActionLogCount'] = 5;
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 3;
        $expData['playerDataArray'][0]['sideScore'] = -17.7;
        $expData['playerDataArray'][1]['roundScore'] = 29.5;
        $expData['playerDataArray'][1]['sideScore'] = 17.7;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 0;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 13;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = 'X Swing Die (with 13 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 4;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Skill attack using [(0):0, (0):0, (0):0, (6):3] against [(4):3]
        $this->verify_api_submitTurn(
            array(0, 0, 0, 2),
            'responder003 performed Skill attack using [(0):0,(0):0,(0):0,(6):3] against [(4):3]; Defender (4) was captured; Attacker (0) rerolled 0 => 0; Attacker (0) rerolled 0 => 0; Attacker (0) rerolled 0 => 0; Attacker (6) rerolled 3 => 2. ',
            $retval, array(array(0, 0), array(0, 1), array(0, 3), array(0, 5), array(1, 1)),
            $gameId, 1, 'Skill', 0, 1, '');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(0):0,(0):0,(0):0,(6):3] against [(4):3]; Defender (4) was captured; Attacker (0) rerolled 0 => 0; Attacker (0) rerolled 0 => 0; Attacker (0) rerolled 0 => 0; Attacker (6) rerolled 3 => 2'));
        $expData['gameActionLogCount'] = 6;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['roundScore'] = 7;
        $expData['playerDataArray'][1]['roundScore'] = 27.5;
        $expData['playerDataArray'][0]['sideScore'] = -13.7;
        $expData['playerDataArray'][1]['sideScore'] = 13.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 2;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][0]['capturedDieArray'] = array(
            array('value' => 3, 'sides' => 4, 'recipe' => '(4)', 'properties' => array('WasJustCaptured')),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test checks for a regression in the behavior of an attack by a morphing die against a rage die
     */
    public function test_interface_game_053() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 53;
        $_SESSION = $this->mock_test_user_login('responder003');

        $gameId = $this->verify_api_createGame(
            array('bm_rand' => array(3, 5, 6, 7, 17, 5, 3, 10, 9), 'bm_skill_rand' => array()),
            'responder003', 'responder004', 'Sabathia', 'Discordia', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Focus', 'Mighty', 'Morphing', 'Poison', 'Rage', 'Speed', 'Stealth', 'TimeAndSpace'));
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'Sabathia', 'recipe' => 'f(4) ^(5) H(6) m(7) z(22)', 'originalRecipe' => 'f(4) ^(5) H(6) m(7) z(22)', 'artFilename' => 'sabathia.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Discordia', 'recipe' => 'df(12) df(12) Gp(16) Gp(16) (Z)', 'originalRecipe' => 'df(12) df(12) Gp(16) Gp(16) (Z)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['swingRequestArray'] = array('Z' => array(4, 30));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(4)', 'description' => 'Focus 4-sided die'),
            array('value' => NULL, 'sides' => 5, 'skills' => array('TimeAndSpace'), 'properties' => array(), 'recipe' => '^(5)', 'description' => 'TimeAndSpace 5-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Mighty'), 'properties' => array(), 'recipe' => 'H(6)', 'description' => 'Mighty 6-sided die'),
            array('value' => NULL, 'sides' => 7, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(7)', 'description' => 'Morphing 7-sided die'),
            array('value' => NULL, 'sides' => 22, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(22)', 'description' => 'Speed 22-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 12, 'skills' => array('Stealth', 'Focus'), 'properties' => array(), 'recipe' => 'df(12)', 'description' => 'Stealth Focus 12-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Stealth', 'Focus'), 'properties' => array(), 'recipe' => 'df(12)', 'description' => 'Stealth Focus 12-sided die'),
            array('value' => NULL, 'sides' => 16, 'skills' => array('Rage', 'Poison'), 'properties' => array(), 'recipe' => 'Gp(16)', 'description' => 'Rage Poison 16-sided die'),
            array('value' => NULL, 'sides' => 16, 'skills' => array('Rage', 'Poison'), 'properties' => array(), 'recipe' => 'Gp(16)', 'description' => 'Rage Poison 16-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(Z)', 'description' => 'Z Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(24),
            $gameId, 1, array('Z' => 27), NULL);

        $_SESSION = $this->mock_test_user_login('responder003');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: Z=27'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [f(4):3, ^(5):5, H(6):6, m(7):7, z(22):17], responder004 rolled [df(12):5, df(12):3, Gp(16):10, Gp(16):9, (Z=27):24]. responder004 has dice which are not counted for initiative due to die skills: [Gp(16), Gp(16)].'));
        $expData['gameActionLogCount'] = 3;
        $expData['gameState'] = "REACT_TO_INITIATIVE";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 17;
        $expData['playerDataArray'][0]['roundScore'] = 22;
        $expData['playerDataArray'][0]['sideScore'] = 19;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Z Swing Die (with 27 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 27;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 24;
        $expData['playerDataArray'][1]['roundScore'] = -6.5;
        $expData['playerDataArray'][1]['sideScore'] = -19;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerWithInitiativeIdx'] = 0;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_reactToInitiative(
            array(),
            'Failed to gain initiative', array('gainedInitiative' => FALSE),
            $retval, $gameId, 1, 'decline', array(), array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 chose not to try to gain initiative using chance or focus dice'));
        $expData['gameActionLogCount'] = 4;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(2, 10, 9),
            'responder003 performed Skill attack using [f(4):3,m(7):7] against [Gp(16):10]; Defender Gp(16) was captured; Defender p(16):9 was added; Attacker f(4) rerolled 3 => 2; Attacker m(7) changed size from 7 to 16 sides, recipe changed from m(7) to m(16), rerolled 7 => 10. ',
            $retval, array(array(0, 0), array(0, 3), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '', array());

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [f(4):3,m(7):7] against [Gp(16):10]; Defender Gp(16) was captured; Defender p(16):9 was added; Attacker f(4) rerolled 3 => 2; Attacker m(7) changed size from 7 to 16 sides, recipe changed from m(7) to m(16), rerolled 7 => 10'));
        $expData['gameActionLogCount'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Morphing 16-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array("HasJustMorphed");
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "m(16)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 16;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 10;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "Gp(16)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 16;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 10;
        $expData['playerDataArray'][0]['roundScore'] = 18.5;
        $expData['playerDataArray'][0]['sideScore'] = 16.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "Poison 16-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array("IsRageTargetReplacement");
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "p(16)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['skills'] = array("Poison");
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 9;
        $expData['playerDataArray'][1]['sideScore'] = -16.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * Morphing dice should not morph when attacking more than one die in a speed-like attack
     */
    public function test_interface_game_054() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 54;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array('bm_rand' => array(1, 3, 6, 11, 1, 6, 2, 1), 'bm_skill_rand' => array()),
            'responder003', 'responder004', 'Montserrat', 'Washington', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Morphing', 'Fire', 'Null', 'Ornery', 'Rush', 'Shadow', 'Speed'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Montserrat', 'recipe' => '(4) #(6) #(8) #(12) #(X)', 'originalRecipe' => '(4) #(6) #(8) #(12) #(X)', 'artFilename' => 'montserrat.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Washington', 'recipe' => 'n(4) z(6) (7) F(13) mso(S)', 'originalRecipe' => 'n(4) z(6) (7) F(13) mso(S)', 'artFilename' => 'washington.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('S' => array(6, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Rush'), 'properties' => array(), 'recipe' => '#(6)', 'description' => 'Rush 6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Rush'), 'properties' => array(), 'recipe' => '#(8)', 'description' => 'Rush 8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Rush'), 'properties' => array(), 'recipe' => '#(12)', 'description' => 'Rush 12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Rush'), 'properties' => array(), 'recipe' => '#(X)', 'description' => 'Rush X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Null'), 'properties' => array(), 'recipe' => 'n(4)', 'description' => 'Null 4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(6)', 'description' => 'Speed 6-sided die'),
            array('value' => NULL, 'sides' => 7, 'skills' => array(), 'properties' => array(), 'recipe' => '(7)', 'description' => '7-sided die'),
            array('value' => NULL, 'sides' => 13, 'skills' => array('Fire'), 'properties' => array(), 'recipe' => 'F(13)', 'description' => 'Fire 13-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Morphing', 'Shadow', 'Ornery'), 'properties' => array(), 'recipe' => 'mso(S)', 'description' => 'Morphing Shadow Ornery S Swing Die'),
        );
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        $this->verify_api_submitDieValues(
            array(3),
            $gameId, 1, array('X' => 20), NULL);

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set die sizes'));
        $expData['gameActionLogCount'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Rush X Swing Die (with 20 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 20;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(9),
            $gameId, 1, array('S' => 12), NULL);

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 1;
        $expData['gameActionLog'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'Game created by responder003'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=20'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: S=12'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [(4):1, #(6):3, #(8):6, #(12):11, #(X=20):3], responder004 rolled [n(4):1, z(6):6, (7):2, F(13):1, mso(S=12):9].'));
        $expData['gameActionLogCount'] = 4;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 3;
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][0]['sideScore'] = 4;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Morphing Shadow Ornery S Swing Die (with 12 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 9;
        $expData['playerDataArray'][1]['roundScore'] = 19;
        $expData['playerDataArray'][1]['sideScore'] = -4;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Rush", "Shadow", "Speed");
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(5),
            'responder004 performed Rush attack using [mso(S=12):9] against [#(8):6,#(X=20):3]; Defender #(8) was captured; Defender #(X=20) was captured; Attacker mso(S=12) rerolled 9 => 5. ',
            $retval, array(array(1, 4), array(0, 2), array(0, 4)),
            $gameId, 1, 'Rush', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Rush attack using [mso(S=12):9] against [#(8):6,#(X=20):3]; Defender #(8) was captured; Defender #(X=20) was captured; Attacker mso(S=12) rerolled 9 => 5'));
        $expData['gameActionLogCount'] = 5;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 4, 1);
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][0]['roundScore'] = 11;
        $expData['playerDataArray'][0]['sideScore'] = -24.0;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 5;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "#(8)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 8;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "#(X)";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['roundScore'] = 47;
        $expData['playerDataArray'][1]['sideScore'] = 24.0;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Rush");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * Regression-testing interaction between Turbo and TimeAndSpace
     */
    public function test_interface_game_055() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 55;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(1, 1, 1, 1, 1, 1, 1, 1, 1),
            'responder003', 'responder004', 'SailorMur', 'Giant', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Giant', 'Poison', 'Shadow', 'Stinger', 'TimeAndSpace', 'Trip', 'Turbo'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'SailorMur', 'recipe' => 'g(10) sp(12) t(4) (10/20) ^(X)!', 'originalRecipe' => 'g(10) sp(12) t(4) (10/20) ^(X)!', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Giant', 'recipe' => '(20) (20) (20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20) (20) (20)', 'artFilename' => 'giant.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['optRequestArray'] = array('3' => array(10, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 10, 'skills' => array('Stinger'), 'properties' => array(), 'recipe' => 'g(10)', 'description' => 'Stinger 10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Shadow', 'Poison'), 'properties' => array(), 'recipe' => 'sp(12)', 'description' => 'Shadow Poison 12-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(4)', 'description' => 'Trip 4-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(10/20)', 'description' => 'Option Die (with 10 or 20 sides)'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('TimeAndSpace', 'Turbo'), 'properties' => array(), 'recipe' => '^(X)!', 'description' => 'TimeAndSpace Turbo X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 submits die values
        $this->verify_api_submitDieValues(
            array(1, 1),
            $gameId, 1, array('X' => 5), array(3 => 10));

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=5 and option dice: (10/20=10)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [g(10):1, sp(12):1, t(4):1, (10/20=10):1, ^(X=5)!:1], responder004 rolled [(20):1, (20):1, (20):1, (20):1, (20):1, (20):1]. responder004\'s button has the "slow" button special, and cannot win initiative normally.'));
        $expData['gameActionLogCount'] = 3;
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow', 'Trip');
        $expData['playerDataArray'][0]['roundScore'] = 2.5;
        $expData['playerDataArray'][0]['sideScore'] = -38.3;
        $expData['playerDataArray'][1]['roundScore'] = 60;
        $expData['playerDataArray'][1]['sideScore'] = 38.3;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array("4" => array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20));
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'Option Die (with 10 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'TimeAndSpace Turbo X Swing Die (with 5 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 02 - responder003 Power attack using ^(X)!
        $this->verify_api_submitTurn(
            array(2),
            'responder003 performed Power attack using [^(X=5)!:1] against [(20):1]; Defender (20) was captured; Attacker ^(X=5)! rerolled from 1. Turbo die ^(X=5)! changed size from 5 to 10 sides, recipe changed from ^(X=5)! to ^(X=10)!, rolled 2. ',
            $retval, array(array(0, 4), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '', array(4 => 10));

        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][0]['sideScore'] = -16.7;
        $expData['playerDataArray'][1]['roundScore'] = 50;
        $expData['playerDataArray'][1]['sideScore'] = 16.7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'TimeAndSpace Turbo X Swing Die (with 10 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 1, 'sides' => 20, 'recipe' => '(20)', 'properties' => array('WasJustCaptured'));
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [^(X=5)!:1] against [(20):1]; Defender (20) was captured; Attacker ^(X=5)! rerolled from 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die ^(X=5)! changed size from 5 to 10 sides, recipe changed from ^(X=5)! to ^(X=10)!, rolled 2'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 03 - responder004 Power attack using (20)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(8),
            'responder004 performed Power attack using [(20):1] against [g(10):1]; Defender g(10) was captured; Attacker (20) rerolled 1 => 8. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow', 'Trip');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 20;
        $expData['playerDataArray'][0]['sideScore'] = -26.7;
        $expData['playerDataArray'][1]['roundScore'] = 60;
        $expData['playerDataArray'][1]['sideScore'] = 26.7;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 8;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['turboSizeArray'] = array("3" => array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20));
        $expData['playerDataArray'][0]['optRequestArray'] = array('2' => array(10, 20));
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 1, 'sides' => 10, 'recipe' => 'g(10)', 'properties' => array('WasJustCaptured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):1] against [g(10):1]; Defender g(10) was captured; Attacker (20) rerolled 1 => 8'));
        $expData['gameActionLogCount'] += 1;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 04 - responder003 Power attack using ^(X)! - gets extra turn
        $this->verify_api_submitTurn(
            array(3),
            'responder003 performed Power attack using [^(X=10)!:2] against [(20):1]; Defender (20) was captured; Attacker ^(X=10)! rerolled from 2. Turbo die ^(X=10)! changed size from 10 to 6 sides, recipe changed from ^(X=10)! to ^(X=6)!, rolled 3. responder003 gets another turn because a Time and Space die rolled odd. ',
            $retval, array(array(0, 3), array(1, 1)),
            $gameId, 1, 'Power', 0, 1, '', array(3 => 6));

        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow', 'Trip');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 38;
        $expData['playerDataArray'][0]['sideScore'] = -8.0;
        $expData['playerDataArray'][1]['roundScore'] = 50;
        $expData['playerDataArray'][1]['sideScore'] = 8.0;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'TimeAndSpace Turbo X Swing Die (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 1, 'sides' => 20, 'recipe' => '(20)', 'properties' => array('WasJustCaptured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [^(X=10)!:2] against [(20):1]; Defender (20) was captured; Attacker ^(X=10)! rerolled from 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die ^(X=10)! changed size from 10 to 6 sides, recipe changed from ^(X=10)! to ^(X=6)!, rolled 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 gets another turn because a Time and Space die rolled odd'));
        $expData['gameActionLogCount'] += 3;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }
}
