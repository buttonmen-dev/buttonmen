<?php

/**
 * responder01Test: API tests of the buttonmen responder, file 01
 *
 * This file contains numbered game playback tests 1-20.
 * For historical reasons, it also contains a couple of miscellaneous
 * tests which take advantage of the data from the first numbered games.
 */

require_once __DIR__.'/responderTestFramework.php';

class responder01Test extends responderTestFramework {

    /**
     * This is the same game setup as in
     * BMInterfaceTest::test_option_reset_bug(), but tested from
     * the API point of view, and we play long enough to set option dice in two consecutive rounds.
     */
    public function test_api_game_001() {

        $this->game_number = 1;
        $_SESSION = $this->mock_test_user_login('responder001');

        // Non-option dice are initially rolled, namely:
        // (4) (6) (8) (12)   (20) (20) (20) (20)
        $gameId = $this->verify_api_createGame(
            array(4, 6, 8, 12, 1, 1, 1, 1),
            'responder001', 'responder002', 'Frasquito', 'Wiseman', 4);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder001', 'responder002', 4, 'SPECIFY_DICE');
        $expData['playerDataArray'][0]['button'] = array('name' => 'Frasquito', 'recipe' => '(4) (6) (8) (12) (2/20)', 'originalRecipe' => '(4) (6) (8) (12) (2/20)', 'originalRecipe' => '(4) (6) (8) (12) (2/20)', 'artFilename' => 'frasquito.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Wiseman', 'recipe' => '(20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20)', 'artFilename' => 'wiseman.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(2/20)', 'description' => 'Option Die (with 2 or 20 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['optRequestArray'] = array('4' => array(2, 20));

        $expData['dieBackgroundType'] = 'symmetric';

        // in test_request_savePlayerInfo() responder002 was set to be on vacation - make sure the game reflects that.
        $expData['playerDataArray'][1]['isOnVacation'] = TRUE;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // now load the game as non-participating player responder003 and check its state
        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['dieBackgroundType'] = 'realistic';
        $this->verify_api_loadGameData_as_nonparticipant($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder001');
        $expData['dieBackgroundType'] = 'symmetric';

        ////////////////////
        // Move 01 - specify option dice

        // this should cause the one option die to be rerolled
        $this->verify_api_submitDieValues(
            array(2),
            $gameId, 1, NULL, array(4 => 2));

        // expected changes to game state
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 16;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -16.0;
        $expData['playerDataArray'][1]['sideScore'] = 16.0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Option Die (with 2 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001 set option dice: (2/20=2)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder002 won initiative for round 1. Initial die values: responder001 rolled [(4):4, (6):6, (8):8, (12):12, (2/20=2):2], responder002 rolled [(20):1, (20):1, (20):1, (20):1].'));
        $expData['gameActionLogCount'] += 2;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // check the game state as a nonplayer so the UI tests have access to a game in START_TURN from a nonplayer perspective
        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['dieBackgroundType'] = 'realistic';
        $this->verify_api_loadGameData_as_nonparticipant($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder001');
        $expData['dieBackgroundType'] = 'symmetric';


        ////////////////////
        // Move 02 - player 2 captures player 1's option die

        // capture the option die - two attacking dice need to reroll
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(1, 1),
            'responder002 performed Skill attack using [(20):1,(20):1] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 1 => 1; Attacker (20) rerolled 1 => 1. ',
            $retval, array(array(1, 0), array(1, 1), array(0, 4)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 15;
        $expData['playerDataArray'][1]['roundScore'] = 42;
        $expData['playerDataArray'][0]['sideScore'] = -18.0;
        $expData['playerDataArray'][1]['sideScore'] = 18.0;
        $expData['playerDataArray'][0]['optRequestArray'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 4, 1);
        $expData['playerDataArray'][1]['capturedDieArray'] = array(
            array('value' => 2, 'sides' => 2, 'properties' => array('WasJustCaptured'), 'recipe' => '(2/20)'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder002', 'message' => 'responder002 performed Skill attack using [(20):1,(20):1] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 1 => 1; Attacker (20) rerolled 1 => 1'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - player 1 captures player 2's first 20-sider

        // 4 6 8 12 vs 1 1 1 1
        $this->verify_api_submitTurn(
            array(4),
            'responder001 performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 4. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 35;
        $expData['playerDataArray'][1]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = 2.0;
        $expData['playerDataArray'][1]['sideScore'] = -2.0;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][]=
            array('value' => 1, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001' . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 4'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - player 2 passes

        // 4 6 8 12 vs 1 1 1
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(),
            'responder002' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 35;
        $expData['playerDataArray'][1]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = 2.0;
        $expData['playerDataArray'][1]['sideScore'] = -2.0;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder002', 'message' => 'responder002' . ' passed'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - player 1 captures player 2's first remaining (20)

        // 4 6 8 12 vs 1 1 1
        $this->verify_api_submitTurn(
            array(3),
            'responder001' . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 3. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 55;
        $expData['playerDataArray'][1]['roundScore'] = 22;
        $expData['playerDataArray'][0]['sideScore'] = 22.0;
        $expData['playerDataArray'][1]['sideScore'] = -22.0;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][]=
            array('value' => 1, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001' . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 3'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - player 2 passes

        // 4 6 8 12 vs 1 1
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(),
            'responder002' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder002', 'message' => 'responder002' . ' passed'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - player 1 captures player 2's first remaining (20)

        // 4 6 8 12 vs 1 1
        $this->verify_api_submitTurn(
            array(2),
            'responder001' . ' performed Power attack using [(6):6] against [(20):1]; Defender (20) was captured; Attacker (6) rerolled 6 => 2. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 75;
        $expData['playerDataArray'][1]['roundScore'] = 12;
        $expData['playerDataArray'][0]['sideScore'] = 42.0;
        $expData['playerDataArray'][1]['sideScore'] = -42.0;
        $expData['playerDataArray'][1]['canStillWin'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][]=
            array('value' => 1, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001' . ' performed Power attack using [(6):6] against [(20):1]; Defender (20) was captured; Attacker (6) rerolled 6 => 2'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 08 - player 2 passes

        // 4 6 8 12 vs 1
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(),
            'responder002' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder002', 'message' => 'responder002' . ' passed'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 09 - player 1 captures player 2's last remaining (20)

        // 4 6 8 12 vs 1
        $this->verify_api_submitTurn(
            array(4, 1, 1, 1, 1, 2, 15, 16, 17, 18),
            'responder001' . ' performed Power attack using [(4):3] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 3 => 4. End of round: ' . 'responder001' . ' won round 1 (95 vs. 2). ' . 'responder001' . ' won initiative for round 2. Initial die values: ' . 'responder001' . ' rolled [(4):1, (6):1, (8):1, (12):1, (2/20=2):2], ' . 'responder002' . ' rolled [(20):15, (20):16, (20):17, (20):18]. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['roundNumber'] = 2;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 16;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -16.0;
        $expData['playerDataArray'][1]['sideScore'] = 16.0;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['optRequestArray'] = array('4' => array(2, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => 1, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 1, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => 1, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => 2, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2/20)', 'description' => 'Option Die (with 2 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
                        array('value' => 15, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                        array('value' => 16, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                        array('value' => 17, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                        array('value' => 18, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001' . ' performed Power attack using [(4):3] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 3 => 4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'End of round: ' . 'responder001' . ' won round 1 (95 vs. 2)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder001' . ' won initiative for round 2. Initial die values: ' . 'responder001' . ' rolled [(4):1, (6):1, (8):1, (12):1, (2/20=2):2], ' . 'responder002' . ' rolled [(20):15, (20):16, (20):17, (20):18].'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 3;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 10 - player 1 passes (round 2)

        // [(4):1, (6):1, (8):1, (12):1, (2/20=2):2] vs. [(20):15, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 11 - player 2 attacks (round 2)

        // [(4):1, (6):1, (8):1, (12):1, (2/20=2):2] vs. [(20):15, (20):16, (20):17, (20):18]
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(19),
            'responder002' . ' performed Power attack using [(20):15] against [(12):1]; Defender (12) was captured; Attacker (20) rerolled 15 => 19. ',
            $retval, array(array(0, 3), array(1, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 12 - player 1 passes (round 2)

        // [(4):1, (6):1, (8):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 13 - player 2 attacks (round 2)

        // [(4):1, (6):1, (8):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(16),
            'responder002' . ' performed Power attack using [(20):16] against [(8):1]; Defender (8) was captured; Attacker (20) rerolled 16 => 16. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 14 - player 1 passes (round 2)

        // [(4):1, (6):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 15 - player 2 attacks (round 2)

        // [(4):1, (6):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(19),
            'responder002' . ' performed Power attack using [(20):19] against [(6):1]; Defender (6) was captured; Attacker (20) rerolled 19 => 19. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 16 - player 1 passes (round 2)

        // [(4):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 17 - player 2 attacks (round 2)

        // [(4):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(19),
            'responder002' . ' performed Power attack using [(20):19] against [(4):1]; Defender (4) was captured; Attacker (20) rerolled 19 => 19. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 18 - player 1 passes (round 2)

        // [(2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 19 - player 2 attacks (round 2)

        // [(2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        // 1 value for attacker's reroll, then 4 + 4 for non-option dice for round 3
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(19, 2, 2, 2, 2, 10, 10, 10, 10),
            'responder002' . ' performed Power attack using [(20):19] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 19 => 19. End of round: ' . 'responder002' . ' won round 2 (72 vs. 0). ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // expected changes as a result of the attack
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['activePlayerIdx'] = NULL;
        $expData['roundNumber'] = 3;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['prevOptValueArray'] = array(4 => 2);
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Option Die (with 2 or 20 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = NULL;
        $expData['gameActionLog'][0]['player'] = 'responder002';
        $expData['gameActionLog'][0]['message'] = 'End of round: ' . 'responder002' . ' won round 2 (72 vs. 0)';
        $expData['gameActionLog'][1]['player'] = 'responder002';
        $expData['gameActionLog'][1]['message'] = 'responder002' . ' performed Power attack using [(20):19] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 19 => 19';
        $expData['gameActionLog'][2]['message'] = 'responder001' . ' passed';
        $expData['gameActionLog'][3]['message'] = 'responder002' . ' performed Power attack using [(20):19] against [(4):1]; Defender (4) was captured; Attacker (20) rerolled 19 => 19';
        $expData['gameActionLog'][4]['message'] = 'responder001' . ' passed';
        $expData['gameActionLog'][5]['message'] = 'responder002' . ' performed Power attack using [(20):19] against [(6):1]; Defender (6) was captured; Attacker (20) rerolled 19 => 19';
        $expData['gameActionLog'][6]['message'] = 'responder001' . ' passed';
        $expData['gameActionLog'][7]['message'] = 'responder002' . ' performed Power attack using [(20):16] against [(8):1]; Defender (8) was captured; Attacker (20) rerolled 16 => 16';
        $expData['gameActionLog'][8]['message'] = 'responder001' . ' passed';
        $expData['gameActionLog'][9]['message'] = 'responder002' . ' performed Power attack using [(20):15] against [(12):1]; Defender (12) was captured; Attacker (20) rerolled 15 => 19';
        $expData['gameActionLogCount'] += 11;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * Game scenario (both players have autopass):
     * p1 (Jellybean) vs. p2 (Dirgo):
     *  1. p1 set swing values: V=6, X=10
     *  2. p2 set swing values: X=4
     *     p1 won initiative for round 1. Initial die values: p1 rolled [p(20):2, s(20):11, (V=6):3, (X=10):1], p2 rolled [(20):5, (20):8, (20):12, (X=4):4].
     *  3. p1 performed Shadow attack using [s(20):11] against [(20):12]; Defender (20) was captured; Attacker s(20) rerolled 11 => 15
     *  4. p2 performed Power attack using [(20):5] against [(V=6):3]; Defender (V=6) was captured; Attacker (20) rerolled 5 => 12
     *     p1 passed
     *  5. p2 performed Power attack using [(20):8] against [(X=10):1]; Defender (X=10) was captured; Attacker (20) rerolled 8 => 13
     *     p1 passed
     *  6. p2 performed Power attack using [(20):12] against [p(20):2]; Defender p(20) was captured; Attacker (20) rerolled 12 => 1
     *     p1 passed
     *     p2 passed
     *     End of round: p1 won round 1 (30 vs. 28)
     *  7. p2 set swing values: X=7
     *     p1 won initiative for round 2. Initial die values: p1 rolled [p(20):8, s(20):6, (V=6):1, (X=10):1], p2 rolled [(20):7, (20):2, (20):17, (X=7):2].
     */
    public function test_api_game_002() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 2;
        $_SESSION = $this->mock_test_user_login('responder003');

        ////////////////////
        // initial game setup

        // Non-swing dice are initially rolled, namely:
        // p(20) s(20)  (20) (20) (20)
        $gameId = $this->verify_api_createGame(
            array(2, 11, 5, 8, 12),
            'responder003', 'responder004', 'Jellybean', 'Dirgo', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Poison', 'Shadow'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Jellybean', 'recipe' => 'p(20) s(20) (V) (X)', 'originalRecipe' => 'p(20) s(20) (V) (X)', 'originalRecipe' => 'p(20) s(20) (V) (X)', 'artFilename' => 'jellybean.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Dirgo', 'recipe' => '(20) (20) (20) (X)', 'originalRecipe' => '(20) (20) (20) (X)', 'originalRecipe' => '(20) (20) (20) (X)', 'artFilename' => 'dirgo.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(20)', 'description' => 'Poison 20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(20)', 'description' => 'Shadow 20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(V)', 'description' => 'V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20), 'V' => array(6, 12));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - player 1 specifies swing dice

        // this causes all newly-specified swing dice to be rolled:
        // (V) (X)
        $this->verify_api_submitDieValues(
            array(3, 1),
            $gameId, 1, array('V' => 6, 'X' => 10), NULL);

        // expected changes to game state
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = 'V Swing Die (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'X Swing Die (with 10 sides)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' set die sizes'));
        $expData['gameActionLogCount'] += 1;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - player 2 specifies swing dice

        // this causes the newly-specified swing die to be rolled:
        // (X)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(4),
            $gameId, 1, array('X' => 4), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes to game state
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Skill', 'Shadow');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = -2;
        $expData['playerDataArray'][1]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = -22.7;
        $expData['playerDataArray'][1]['sideScore'] = 22.7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = 'X Swing Die (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['gameActionLog'][0]['message'] = 'responder003' . ' set swing values: V=6, X=10';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [p(20):2, s(20):11, (V=6):3, (X=10):1], ' . 'responder004' . ' rolled [(20):5, (20):8, (20):12, (X=4):4].'));
        $expData['gameActionLogCount'] += 2;
        $expData['playerDataArray'][0]['canStillWin'] = true;
        $expData['playerDataArray'][1]['canStillWin'] = true;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - player 1 performs shadow attack

        // p(20) s(20) (V) (X)  vs.  (20) (20) (20) (X)
        $this->verify_api_submitTurn(
            array(15),
            'responder003' . ' performed Shadow attack using [s(20):11] against [(20):12]; Defender (20) was captured; Attacker s(20) rerolled 11 => 15. ',
            $retval, array(array(0, 1), array(1, 2)),
            $gameId, 1, 'Shadow', 0, 1, '');

        // expected changes to game state
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][1]['roundScore'] = 22;
        $expData['playerDataArray'][0]['sideScore'] = -2.7;
        $expData['playerDataArray'][1]['sideScore'] = 2.7;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 15;
        $expData['playerDataArray'][0]['capturedDieArray'][]=
            array('value' => 12, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' performed Shadow attack using [s(20):11] against [(20):12]; Defender (20) was captured; Attacker s(20) rerolled 11 => 15'));
        $expData['gameActionLogCount'] += 1;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - player 2 performs power attack; player 1 passes

        // p(20) s(20) (V) (X)  vs.  (20) (20) (X)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(12),
            'responder004' . ' performed Power attack using [(20):5] against [(V=6):3]; Defender (V=6) was captured; Attacker (20) rerolled 5 => 12. ' . 'responder003' . ' passed. ',
            $retval, array(array(1, 0), array(0, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes to game state
        $expData['playerDataArray'][0]['roundScore'] = 15;
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][0]['sideScore'] = -8.7;
        $expData['playerDataArray'][1]['sideScore'] = 8.7;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 12;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][]=
            array('value' => 3, 'sides' => 6, 'properties' => array(), 'recipe' => '(V)');
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Power attack using [(20):5] against [(V=6):3]; Defender (V=6) was captured; Attacker (20) rerolled 5 => 12'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));
        $expData['gameActionLogCount'] += 2;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - player 2 performs power attack; player 1 passes

        // p(20) s(20) (X)  vs.  (20) (20) (X)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(13),
            'responder004' . ' performed Power attack using [(20):8] against [(X=10):1]; Defender (X=10) was captured; Attacker (20) rerolled 8 => 13. ' . 'responder003' . ' passed. ',
            $retval, array(array(1, 1), array(0, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 06 - player 2 performs power attack; player 1 passes; player 2 passes; round ends

        // p(20) s(20)  vs.  (20) (20) (X)
        // random values needed: 1 for reroll, 7 for end of turn reroll
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1, 8, 6, 1, 1, 7, 2, 17),
            'responder004' . ' performed Power attack using [(20):12] against [p(20):2]; Defender p(20) was captured; Attacker (20) rerolled 12 => 1. ' . 'responder003' . ' passed. ' . 'responder004' . ' passed. End of round: ' . 'responder003' . ' won round 1 (30 vs. 28). ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes to game state
        $expData['roundNumber'] = 2;
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array('V' => 6, 'X' => 10);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array('X' => 4);
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(20)', 'description' => 'Poison 20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(20)', 'description' => 'Shadow 20-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(V)', 'description' => 'V Swing Die (with 6 sides)'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die (with 10 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20), 'V' => array(6, 12));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Power attack using [(20):8] against [(X=10):1]; Defender (X=10) was captured; Attacker (20) rerolled 8 => 13'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Power attack using [(20):12] against [p(20):2]; Defender p(20) was captured; Attacker (20) rerolled 12 => 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: ' . 'responder003' . ' won round 1 (30 vs. 28)'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 6;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - player 2 specifies swing dice

        // this causes the swing die to be rolled:
        // (X)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(2),
            $gameId, 2, array('X' => 7), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes to game state
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = -2;
        $expData['playerDataArray'][1]['roundScore'] = 33.5;
        $expData['playerDataArray'][0]['sideScore'] = -23.7;
        $expData['playerDataArray'][1]['sideScore'] = 23.7;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = 'X Swing Die (with 7 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 17;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 2;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['canStillWin'] = true;
        $expData['playerDataArray'][1]['canStillWin'] = true;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' set swing values: X=7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 2. Initial die values: ' . 'responder003' . ' rolled [p(20):8, s(20):6, (V=6):1, (X=10):1], ' . 'responder004' . ' rolled [(20):7, (20):2, (20):17, (X=7):2].'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * In this scenario, a 1-round Haruspex mirror battle is played,
     * letting us test a completed game, and a number of "continue
     * previous game" and game chat scenarios.  Steps:
     * * Create haruspex mirror battle game 1.
     * *   test invalid game continuation of a game in progress
     * *   game 1: p2 passes while chatting
     * *   game 1: p1 power attacks while chatting and wins
     * *   test various invalid game continuations
     * * Create haruspex mirror battle game 2, continuing game 1.
     * *   game 2: p1 passes
     * *   game 2: p1 submits chat
     * *   game 2: p1 updates chat
     * *   game 2: p1 deletes chat
     * *   game 2: p2 power attacks and wins
     * * Create haruspex mirror battle game 3, continuing game 2 (double-continuation).
     * *   game 3: p1 passes while chatting (verify chat is editable)
     * *   game 3: p2 power attacks and wins
     * * Create random game 4, continuing game 3 (verify that chat is only continued from
     * * one previous game, and reproduce bug #1285, in which continuation text is lost
     * * during random game creation)
     */
    public function test_interface_game_003() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 3;
        $_SESSION = $this->mock_test_user_login('responder003');

        ////////////////////
        // initial game setup

        // Both dice are initially rolled:
        // (99)  (99)
        $gameId = $this->verify_api_createGame(
            array(54, 42),
            'responder003', 'responder004', 'haruspex', 'haruspex', 1, 'a competitive and interesting game');

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 1, 'START_TURN');
        $expData['description'] = 'a competitive and interesting game';
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 49.5;
        $expData['playerDataArray'][1]['roundScore'] = 49.5;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 54, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 42, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [(99):54], ' . 'responder004' . ' rolled [(99):42].'));
        $expData['gameActionLogCount'] += 1;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Verify that a continuation of this game while it is still in progress fails
        $this->verify_api_failure(
            array(
                'type' => 'createGame',
                'playerInfoArray' => array(array('responder003', 'Haruspex'), array('responder004', 'Haruspex')),
                'maxWins' => 3,
                'previousGameId' => $gameId,
            ),
            'Game create failed because the previous game has not been completed yet.');


        ////////////////////
        // Move 01 - player 2 passes

        // (99)  vs  (99)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(),
            'responder004' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 1, 0, 'I think you\'ve got this one');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' passed'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'I think you\'ve got this one'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - player 1 performs power attack; game ends

        // (99)  vs  (99)
        $this->verify_api_submitTurn(
            array(10),
            'responder003' . ' performed Power attack using [(99):54] against [(99):42]; Defender (99) was captured; Attacker (99) rerolled 54 => 10. End of round: ' . 'responder003' . ' won round 1 (148.5 vs. 0). ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, 'Good game!');

        // expected changes as a result of the attack
        $expData['gameState'] = 'END_GAME';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 0;
        $expData['playerDataArray'][1]['roundScore'] = 0;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' performed Power attack using [(99):54] against [(99):42]; Defender (99) was captured; Attacker (99) rerolled 54 => 10'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: ' . 'responder003' . ' won round 1 (148.5 vs. 0)'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Good game!'));
        $expData['gameActionLogCount'] += 2;
        $expData['gameChatLogCount'] = 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Game creation failures - make sure various invalid argumentsj
        // that the public API will allow, are rejected with friendly messages

        // same player appears in the game twice
        $this->verify_api_failure(
            array(
                'type' => 'createGame',
                'playerInfoArray' => array(array('responder003', 'Haruspex'), array('responder003', 'Haruspex')),
                'maxWins' => 1,
                'previousGameId' => $gameId,
            ),
            'Game create failed because a player has been selected more than once.');

        ////////////////////
        // Verify that a continuation of this game with an invalid previous game fails
        $this->verify_api_failure(
            array(
                'type' => 'createGame',
                'playerInfoArray' => array(array('responder003', 'Haruspex'), array('responder004', 'Haruspex')),
                'maxWins' => 1,
                'previousGameId' => -3,
            ),
            'Argument (previousGameId) to function createGame is invalid');


        ////////////////////
        // Verify that a continuation of this game with different players fails
        $this->verify_api_failure(
            array(
                'type' => 'createGame',
                'playerInfoArray' => array(array('responder003', 'Haruspex'), array('responder001', 'Haruspex')),
                'maxWins' => 1,
                'previousGameId' => $gameId,
            ),
            'Game create failed because the previous game does not contain the same players.');


        ////////////////////
        // Creation of continuation game

        // Both dice are initially rolled:
        // (99)  (99)
        $oldGameId = $gameId;
        $gameId = $this->verify_api_createGame(
            array(29, 50),
            'responder003', 'responder004', 'haruspex', 'haruspex', 1, 'another competitive and interesting game', $oldGameId);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 1, 'START_TURN');
        $expData['description'] = 'another competitive and interesting game';
        $expData['previousGameId'] = $oldGameId;
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 49.5;
        $expData['playerDataArray'][1]['roundScore'] = 49.5;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 29, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 50, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [(99):29], ' . 'responder004' . ' rolled [(99):50].'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'I think you\'ve got this one'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Good game!'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $oldGameId . '][i]'));
        $expData['gameActionLogCount'] = 2;
        $expData['gameChatLogCount'] = 3;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 (game 2) - player 1 passes

        // (99)  vs  (99)
        $this->verify_api_submitTurn(
            array(),
            'responder003' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 (game 2) - player 1 submits chat

        $this->verify_api_submitChat($gameId, 'There was something i meant to say', 'Added game message');

        // expected changes as a result
        $expData['gameChatEditable'] = 'TIMESTAMP';
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'There was something i meant to say'));
        $expData['gameChatLogCount'] = 4;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 (game 2) - player 1 updates chat

        $this->verify_api_submitChat($gameId, '...but i forgot what it was', 'Updated previous game message', $retval['gameChatEditable']);

        // expected changes as a result
        $expData['gameChatLog'][0]['message'] = '...but i forgot what it was';

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 (game 2) - player 1 deletes chat

        $this->verify_api_submitChat($gameId, '', 'Deleted previous game message', $retval['gameChatEditable']);

        // expected changes as a result
        $expData['gameChatEditable'] = FALSE;
        array_shift($expData['gameChatLog']);
        $expData['gameChatLogCount'] = 3;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 (game 2) - player 2 wins game without chatting

        // (99)  vs  (99)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(11),
            'responder004' . ' performed Power attack using [(99):50] against [(99):29]; Defender (99) was captured; Attacker (99) rerolled 50 => 11. End of round: ' . 'responder004' . ' won round 1 (148.5 vs. 0). ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes as a result of the attack
        $expData['gameState'] = 'END_GAME';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 0;
        $expData['playerDataArray'][1]['roundScore'] = 0;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Power attack using [(99):50] against [(99):29]; Defender (99) was captured; Attacker (99) rerolled 50 => 11'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: ' . 'responder004' . ' won round 1 (148.5 vs. 0)'));
        // chat from previous game is no longer included in a closed continuation game
        array_pop($expData['gameChatLog']);
        array_pop($expData['gameChatLog']);
        $expData['gameActionLogCount'] += 2;
        $expData['gameChatLogCount'] = 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Creation of another continuation game

        // Both dice are initially rolled:
        // (99)  (99)
        $secondGameId = $gameId;
        $gameId = $this->verify_api_createGame(
            array(13, 64),
            'responder003', 'responder004', 'haruspex', 'haruspex', 1, 'this series is a nailbiter', $secondGameId);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 1, 'START_TURN');
        $expData['description'] = 'this series is a nailbiter';
        $expData['previousGameId'] = $secondGameId;
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 49.5;
        $expData['playerDataArray'][1]['roundScore'] = 49.5;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 13, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 64, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [(99):13], ' . 'responder004' . ' rolled [(99):64].'));
        // This behavior may change depending on the resolution of #1170
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $oldGameId . '][i]'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $secondGameId . '][i]'));
        $expData['gameActionLogCount'] = 2;
        $expData['gameChatLogCount'] = 2;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 (game 3) - player 1 passes

        // (99)  vs  (99)
        $this->verify_api_submitTurn(
            array(),
            'responder003' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 0, 1, 'Who will win?  The suspense is killing me!');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['gameChatEditable'] = 'TIMESTAMP';
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Who will win?  The suspense is killing me!'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 3;


        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // now load the game as a non-player, and verify that chat
        // text from the current game is shown, but chat from a
        // previous game is not shown
        $expData['gameChatEditable'] = FALSE;
        $expData['currentPlayerIdx'] = FALSE;
        $expData['playerDataArray'][0]['playerColor'] = '#cccccc';
        $expData['playerDataArray'][1]['playerColor'] = '#dddddd';
        $expData['dieBackgroundType'] = 'symmetric';
        $expData['gameChatLogCount'] = 2;
        $savedChat = array_splice($expData['gameChatLog'], 2, 1);
        $_SESSION = $this->mock_test_user_login('responder002');
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder003');

        // now enable chat privacy, and verify that the non-player
        // now can't see current game chat either
        $this->verify_api_setChatVisibility(
            'Set game chat to private',
            $gameId, TRUE);
        $expData['playerDataArray'][0]['isChatPrivate'] = TRUE;
        $expData['gameChatLogCount'] = 2;
        array_splice($expData['gameChatLog'], 0, 1);
        array_unshift($expData['gameChatLog'], array('timestamp' => 0, 'player' => '', 'message' => 'The chat for this game is private'));
        $_SESSION = $this->mock_test_user_login('responder002');
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder003');

        // make sure things still look right for a player after chat has been set to private
        $expData['gameChatEditable'] = 'TIMESTAMP';
        $expData['currentPlayerIdx'] = 0;
        $expData['playerDataArray'][0]['playerColor'] = '#dd99dd';
        $expData['playerDataArray'][1]['playerColor'] = '#ddffdd';
        $expData['dieBackgroundType'] = 'realistic';
        $expData['gameChatLogCount'] = 3;
        $continuedMsg = $expData['gameChatLog'][1]['message'];
        $expData['gameChatLog'] = array(
            array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Who will win?  The suspense is killing me!'),
            array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => $continuedMsg),
            $savedChat[0],
        );
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 (game 3) - player 2 wins game without chatting

        // (99)  vs  (99)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(76),
            'responder004' . ' performed Power attack using [(99):64] against [(99):13]; Defender (99) was captured; Attacker (99) rerolled 64 => 76. End of round: ' . 'responder004' . ' won round 1 (148.5 vs. 0). ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes as a result of the attack
        $expData['gameState'] = 'END_GAME';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['gameChatEditable'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 0;
        $expData['playerDataArray'][1]['roundScore'] = 0;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Power attack using [(99):64] against [(99):13]; Defender (99) was captured; Attacker (99) rerolled 64 => 76'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: ' . 'responder004' . ' won round 1 (148.5 vs. 0)'));
        // chat from previous game is no longer included in a closed continuation game
        array_pop($expData['gameChatLog']);
        $expData['gameActionLogCount'] += 2;
        $expData['gameChatLogCount'] = 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Creation of game 4
        $buttonIds = $this->find_button_random_indices(array('haruspex'));

        // Two random values to pick the buttons (see, they said they were tired of playing haruspex, that's why it's funny)
        // and two for the initial rolls
        $thirdGameId = $gameId;
        $gameId = $this->verify_api_createGame(
            array($buttonIds['haruspex'], $buttonIds['haruspex'], 12, 24),
            'responder003', 'responder004', '__random', '__random', 1, 'maybe we should try some different buttons', $thirdGameId);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 1, 'START_TURN');
        $expData['description'] = 'maybe we should try some different buttons';
        $expData['previousGameId'] = $thirdGameId;
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 49.5;
        $expData['playerDataArray'][1]['roundScore'] = 49.5;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 12, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 24, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [(99):12], ' . 'responder004' . ' rolled [(99):24].'));
        // This behavior may change depending on the resolution of #1170
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $secondGameId . '][i]'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Who will win?  The suspense is killing me!'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $thirdGameId . '][i]'));
        $expData['gameActionLogCount'] = 2;
        $expData['gameChatLogCount'] = 3;

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // Now load the game as a non-player, and verify that chat
        // text from a previous game is not shown
        $expData['gameChatEditable'] = FALSE;
        $expData['currentPlayerIdx'] = FALSE;
        $expData['playerDataArray'][0]['playerColor'] = '#cccccc';
        $expData['playerDataArray'][1]['playerColor'] = '#dddddd';
        $expData['gameChatLogCount'] = 1;
        array_splice($expData['gameChatLog'], 1, 2);
        $_SESSION = $this->mock_test_user_login('responder002');
        $expData['dieBackgroundType'] = 'symmetric';
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['dieBackgroundType'] = 'realistic';
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     * @depends responder00Test::test_request_createGame
     * @depends test_interface_game_003
     *
     * test_request_createGame creates an Active game containing Avis
     * test_interface_game_003 creates at least one Completed game
     */
    public function test_request_loadActiveGames() {
        $this->verify_login_required('loadActiveGames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadActiveGames');

        $args = array('type' => 'loadActiveGames');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'All game details retrieved successfully.');
        $akeys = array_keys($retval['data']);
        sort($akeys);
        $this->assertEquals($akeys, array('gameDescriptionArray', 'gameIdArray', 'gameStateArray', 'inactivityArray', 'inactivityRawArray', 'isAwaitingActionArray', 'isOpponentOnVacationArray', 'myButtonNameArray', 'nDrawsArray', 'nLossesArray', 'nTargetWinsArray', 'nWinsArray', 'opponentButtonNameArray', 'opponentColorArray', 'opponentIdArray', 'opponentNameArray', 'playerColorArray', 'statusArray'));

        $this->cache_json_api_output('loadActiveGames', 'noargs', $retval);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     * @depends responder00Test::test_request_createGame
     * @depends test_interface_game_003
     *
     * test_request_createGame creates an Active game containing Avis
     * test_interface_game_003 creates at least one Completed game
     */
    public function test_request_loadCompletedGames() {
        $this->verify_login_required('loadCompletedGames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadCompletedGames');

        $args = array('type' => 'loadCompletedGames');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'All game details retrieved successfully.');
        $akeys = array_keys($retval['data']);
        sort($akeys);
        $this->assertEquals($akeys, array('gameDescriptionArray', 'gameIdArray', 'gameStateArray', 'inactivityArray', 'inactivityRawArray', 'isAwaitingActionArray', 'isOpponentOnVacationArray', 'myButtonNameArray', 'nDrawsArray', 'nLossesArray', 'nTargetWinsArray', 'nWinsArray', 'opponentButtonNameArray', 'opponentColorArray', 'opponentIdArray', 'opponentNameArray', 'playerColorArray', 'statusArray'));

        $this->cache_json_api_output('loadCompletedGames', 'noargs', $retval);
    }


    /**
     * @depends responder00Test::test_request_savePlayerInfo
     * @depends responder00Test::test_request_reactToNewGameCancel
     *
     * A game is withdrawn in test_request_reactToNewGameCancel
     */
    public function test_request_loadCancelledGames() {
        $this->verify_login_required('loadCancelledGames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadCancelledGames');

        // Mock player responder006, who should be able to see the cancelled game
        $_SESSION = $this->mock_test_user_login('responder006');
        $args = array('type' => 'loadCancelledGames');
        $retval = $this->verify_api_success($args);
        $_SESSION = $this->mock_test_user_login();

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'All game details retrieved successfully.');
        $akeys = array_keys($retval['data']);
        sort($akeys);
        $this->assertEquals($akeys, array('gameDescriptionArray', 'gameIdArray', 'gameStateArray', 'inactivityArray', 'inactivityRawArray', 'isAwaitingActionArray', 'isOpponentOnVacationArray', 'myButtonNameArray', 'nDrawsArray', 'nLossesArray', 'nTargetWinsArray', 'nWinsArray', 'opponentButtonNameArray', 'opponentColorArray', 'opponentIdArray', 'opponentNameArray', 'playerColorArray', 'statusArray'));

        $this->cache_json_api_output('loadCancelledGames', 'noargs', $retval);
    }


    public function test_request_loadNewGames() {
        $this->verify_login_required('loadNewGames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadNewGames');

        $args = array('type' => 'loadNewGames');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'All game details retrieved successfully.');
        $akeys = array_keys($retval['data']);
        sort($akeys);
        $this->assertEquals($akeys, array('gameDescriptionArray', 'gameIdArray', 'gameStateArray', 'inactivityArray', 'inactivityRawArray', 'isAwaitingActionArray', 'isOpponentOnVacationArray', 'myButtonNameArray', 'nDrawsArray', 'nLossesArray', 'nTargetWinsArray', 'nWinsArray', 'opponentButtonNameArray', 'opponentColorArray', 'opponentIdArray', 'opponentNameArray', 'playerColorArray', 'statusArray'));

        $this->cache_json_api_output('loadNewGames', 'noargs', $retval);
    }


    public function test_request_loadOpenGames() {
        $this->verify_login_required('loadOpenGames');

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_invalid_arg_rejected('loadOpenGames');

        $gameId = $this->verify_api_createGame(
            array(),
            'responder004', '', 'Avis', 'Avis', '3'
        );

        $args = array(
            'type' => 'loadOpenGames',
        );
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Open games retrieved successfully.');
        $this->assertEquals(array_keys($retval['data']), array('games'));

        $this->cache_json_api_output('loadOpenGames', 'noargs', $retval);
    }


    /**
     * @depends responder00Test::test_request_savePlayerInfo
     * @depends responder00Test::test_request_createGame
     * @depends test_interface_game_003
     *
     * test_request_createGame creates an Active game containing Avis
     * test_interface_game_003 creates at least one Completed game
     */
    public function test_request_searchGameHistory() {
        $this->verify_login_required('searchGameHistory');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('searchGameHistory');

        // make sure there's at least one game
        $this->verify_api_createGame(
            array(1, 1, 1, 1, 2, 2, 2),
            'responder003', 'responder004', 'Hammer', 'Stark', 3
        );


        // Search for a game with a particular button name
        $args = array(
            'type' => 'searchGameHistory',
            'sortColumn' => 'lastMove',
            'sortDirection' => 'DESC',
            'numberOfResults' => '20',
            'page' => '1',
            'buttonNameA' => 'Avis',
        );
        $retval = $this->verify_api_success($args);

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Sought games retrieved successfully.');
        $this->assertEquals(array_keys($retval['data']), array('games', 'summary'));
        $this->assertTrue(count($retval['data']['games']) > 0);
        $this->assertEquals($retval['data']['games'][0]['buttonNameA'], 'Avis');
        $akeys = array_keys($retval['data']['summary']);
        sort($akeys);
        $this->assertEquals($akeys, array('earliestStart', 'gamesCompleted', 'gamesWonA', 'gamesWonB', 'latestMove', 'matchesFound'));

        $this->cache_json_api_output('searchGameHistory', 'Avis', $retval);


        // Search for a game in a particular state
        $args = array(
            'type' => 'searchGameHistory',
            'sortColumn' => 'lastMove',
            'sortDirection' => 'DESC',
            'numberOfResults' => '20',
            'page' => '1',
            'status' => 'COMPLETE',
        );
        $retval = $this->verify_api_success($args);

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Sought games retrieved successfully.');
        $this->assertEquals(array_keys($retval['data']), array('games', 'summary'));
        $this->assertTrue(count($retval['data']['games']) > 0);
        $this->assertEquals($retval['data']['games'][0]['status'], 'COMPLETE');
        $akeys = array_keys($retval['data']['summary']);
        sort($akeys);
        $this->assertEquals($akeys, array('earliestStart', 'gamesCompleted', 'gamesWonA', 'gamesWonB', 'latestMove', 'matchesFound'));

        $this->cache_json_api_output('searchGameHistory', 'COMPLETE', $retval);


        // Search for any games
        $args = array(
            'type' => 'searchGameHistory',
            'sortColumn' => 'lastMove',
            'sortDirection' => 'DESC',
            'numberOfResults' => '20',
            'page' => '1',
        );
        $retval = $this->verify_api_success($args);

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Sought games retrieved successfully.');
        $this->assertEquals(array_keys($retval['data']), array('games', 'summary'));
        $this->assertTrue(count($retval['data']['games']) > 0);
        $akeys = array_keys($retval['data']['summary']);
        sort($akeys);
        $this->assertEquals($akeys, array('earliestStart', 'gamesCompleted', 'gamesWonA', 'gamesWonB', 'latestMove', 'matchesFound'));

        $this->cache_json_api_output('searchGameHistory', 'noargs', $retval);
    }

    /**
     * @depends responder00Test::test_request_createGame
     * @depends test_request_searchGameHistory
     *
     * Depend on tests which create games to ensure that some games exist
     */
    public function test_request_loadNextPendingGame() {
        $this->verify_login_required('loadNextPendingGame');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadNextPendingGame');

        // loadGameData should fail if currentGameId is non-numeric
        $args = array('type' => 'loadNextPendingGame', 'currentGameId' => 'foobar');
        $this->verify_api_failure($args, 'Argument (currentGameId) to function loadNextPendingGame is invalid');

        $args = array('type' => 'loadNextPendingGame');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Next game ID retrieved successfully.');
        $this->assertEquals(array_keys($retval['data']), array('gameId'));
        $this->assertTrue(is_numeric($retval['data']['gameId']));

        // store this game ID so we can test skipping it, then normalize the gameId and save the output
        $args['currentGameId'] = $retval['data']['gameId'];
        $retval['data']['gameId'] = 7;
        $this->cache_json_api_output('loadNextPendingGame', 'noargs', $retval);

        // now skip a game and verify that this is a valid invocation
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Next game ID retrieved successfully.');
        $this->assertEquals(array_keys($retval['data']), array('gameId'));
        $this->assertTrue(is_numeric($retval['data']['gameId']));
        $this->assertTrue($retval['data']['gameId'] != $args['currentGameId']);

        // Normalize this gameId and save the output
        $retval['data']['gameId'] = 4;
        $this->cache_json_api_output('loadNextPendingGame', '7', $retval);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This scenario tests ornery mood swing dice at the BMInterface level
     */
    public function test_interface_game_004() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 4;
        $_SESSION = $this->mock_test_user_login('responder003');

        ////////////////////
        // initial game setup

        // No dice are initially rolled, since they're all swing dice
        $gameId = $this->verify_api_createGame(
            array(),
            'responder003', 'responder004', 'Skeeve', 'Skeeve', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Mood', 'Ornery'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('V' => array(6, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Y' => array(1, 20), 'Z' => array(4, 30));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('V' => array(6, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Y' => array(1, 20), 'Z' => array(4, 30));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Skeeve', 'recipe' => 'o(V)? o(W)? o(X)? o(Y)? o(Z)?', 'originalRecipe' => 'o(V)? o(W)? o(X)? o(Y)? o(Z)?', 'artFilename' => 'skeeve.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Skeeve', 'recipe' => 'o(V)? o(W)? o(X)? o(Y)? o(Z)?', 'originalRecipe' => 'o(V)? o(W)? o(X)? o(Y)? o(Z)?', 'artFilename' => 'skeeve.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(V)?', 'description' => 'Ornery V Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(W)?', 'description' => 'Ornery W Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(X)?', 'description' => 'Ornery X Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(Y)?', 'description' => 'Ornery Y Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(Z)?', 'description' => 'Ornery Z Mood Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(V)?', 'description' => 'Ornery V Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(W)?', 'description' => 'Ornery W Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(X)?', 'description' => 'Ornery X Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(Y)?', 'description' => 'Ornery Y Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(Z)?', 'description' => 'Ornery Z Mood Swing Die'),
        );

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - player 1 submits die values

        // This needs 5 random values, for player 1's swing dice
        $this->verify_api_submitDieValues(
            array(2, 2, 4, 1, 4),
            $gameId, 1, array('V' => 6, 'W' => 4, 'X' => 4, 'Y' => 1, 'Z' => 4), NULL);

        // expected changes as a result of the attack
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] .= ' (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] .= ' (with 1 side)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 4 sides)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' set die sizes'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - player 2 submits die values

        // This needs 5 random values, for each of p2's swing dice
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(9, 4, 9, 8, 1),
            $gameId, 1, array('V' => 12, 'W' => 11, 'X' => 10, 'Y' => 9, 'Z' => 8), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes as a result of the attack
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 9.5;
        $expData['playerDataArray'][1]['roundScore'] = 25;
        $expData['playerDataArray'][0]['sideScore'] = -10.3;
        $expData['playerDataArray'][1]['sideScore'] = 10.3;
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] .= ' (with 12 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] .= ' (with 11 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] .= ' (with 10 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] .= ' (with 9 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] .= ' (with 8 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['gameActionLog'][0]['message'] = 'responder003' . ' set swing values: V=6, W=4, X=4, Y=1, Z=4';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' set swing values: V=12, W=11, X=10, Y=9, Z=8'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [o(V=6)?:2, o(W=4)?:2, o(X=4)?:4, o(Y=1)?:1, o(Z=4)?:4], ' . 'responder004' .' rolled [o(V=12)?:9, o(W=11)?:4, o(X=10)?:9, o(Y=9)?:8, o(Z=8)?:1].'));
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - player 1 performs skill attack using 3 dice
        // o(X)? attacks, goes to 20 sides (idx 5) and value 10
        // o(Y)? attacks, goes to 6 sides (idx 3) and value 3
        // o(Z)? attacks, goes to 12 sides (idx 4) and value 10
        // o(V)? idle rerolls, goes to 12 sides (idx 3) and value 3
        // o(W)? idle rerolls, goes to 4 sides (idx 0) and value 2
        $this->verify_api_submitTurn(
            array(5, 10, 3, 3, 4, 10, 3, 3, 0, 2),
            'responder003' . ' performed Skill attack using [o(X=4)?:4,o(Y=1)?:1,o(Z=4)?:4] against [o(X=10)?:9]; Defender o(X=10)? was captured; Attacker o(X=4)? changed size from 4 to 20 sides, recipe changed from o(X=4)? to o(X=20)?, rerolled 4 => 10; Attacker o(Y=1)? changed size from 1 to 6 sides, recipe changed from o(Y=1)? to o(Y=6)?, rerolled 1 => 3; Attacker o(Z=4)? changed size from 4 to 12 sides, recipe changed from o(Z=4)? to o(Z=12)?, rerolled 4 => 10. ' . 'responder003' . '\'s idle ornery dice rerolled at end of turn: o(V=6)? changed size from 6 to 12 sides, recipe changed from o(V=6)? to o(V=12)?, rerolled 2 => 3; o(W=4)? remained the same size, recipe remained o(W=4)?, rerolled 2 => 2. ',
            $retval, array(array(0, 2), array(0, 3), array(0, 4), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 37;
        $expData['playerDataArray'][1]['roundScore'] = 20;
        $expData['playerDataArray'][0]['sideScore'] = 11.3;
        $expData['playerDataArray'][1]['sideScore'] = -11.3;
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = 'Ornery V Mood Swing Die (with 12 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = 'Ornery X Mood Swing Die (with 20 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'Ornery Y Mood Swing Die (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Ornery Z Mood Swing Die (with 12 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('HasJustBeenMoody', 'HasJustRerolledOrnery');
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array('HasJustBeenMoody', 'HasJustRerolledOrnery');
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustBeenMoody');
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array('HasJustBeenMoody');
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('HasJustBeenMoody');
        $expData['playerDataArray'][0]['capturedDieArray'][] =
            array('value' => 9, 'sides' => '10', 'recipe' => 'o(X)?', 'properties' => array('WasJustCaptured'));
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' performed Skill attack using [o(X=4)?:4,o(Y=1)?:1,o(Z=4)?:4] against [o(X=10)?:9]; Defender o(X=10)? was captured; Attacker o(X=4)? changed size from 4 to 20 sides, recipe changed from o(X=4)? to o(X=20)?, rerolled 4 => 10; Attacker o(Y=1)? changed size from 1 to 6 sides, recipe changed from o(Y=1)? to o(Y=6)?, rerolled 1 => 3; Attacker o(Z=4)? changed size from 4 to 12 sides, recipe changed from o(Z=4)? to o(Z=12)?, rerolled 4 => 10'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . '\'s idle ornery dice rerolled at end of turn: o(V=6)? changed size from 6 to 12 sides, recipe changed from o(V=6)? to o(V=12)?, rerolled 2 => 3; o(W=4)? remained the same size, recipe remained o(W=4)?, rerolled 2 => 2'));
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - player 2 performs skill attack using 2 dice
        // o(V)? attacks, goes to 10 sides (idx 2) and value 1
        // o(Z)? attacks, goes to 30 sides (idx 6) and value 18
        // o(W)? idle rerolls, goes to 8 sides (idx 2) and value 5
        // o(Y)? idle rerolls, goes to 12 sides (idx 6) and value 3
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2, 1, 6, 18, 2, 5, 6, 3),
            'responder004' . ' performed Skill attack using [o(V=12)?:9,o(Z=8)?:1] against [o(X=20)?:10]; Defender o(X=20)? was captured; Attacker o(V=12)? changed size from 12 to 10 sides, recipe changed from o(V=12)? to o(V=10)?, rerolled 9 => 1; Attacker o(Z=8)? changed size from 8 to 30 sides, recipe changed from o(Z=8)? to o(Z=30)?, rerolled 1 => 18. ' . 'responder004' . '\'s idle ornery dice rerolled at end of turn: o(W=11)? changed size from 11 to 8 sides, recipe changed from o(W=11)? to o(W=8)?, rerolled 4 => 5; o(Y=9)? changed size from 9 to 12 sides, recipe changed from o(Y=9)? to o(Y=12)?, rerolled 8 => 3. ',
            $retval, array(array(1, 0), array(1, 3), array(0, 2)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 27;
        $expData['playerDataArray'][1]['roundScore'] = 50;
        $expData['playerDataArray'][0]['sideScore'] = -15.3;
        $expData['playerDataArray'][1]['sideScore'] = 15.3;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 30;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = 'Ornery V Mood Swing Die (with 10 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = 'Ornery W Mood Swing Die (with 8 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = 'Ornery Y Mood Swing Die (with 12 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = 'Ornery Z Mood Swing Die (with 30 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 18;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array('HasJustBeenMoody');
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array('HasJustBeenMoody', 'HasJustRerolledOrnery');
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array('HasJustBeenMoody', 'HasJustRerolledOrnery');
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array('HasJustBeenMoody');
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] =
            array('value' => 10, 'sides' => '20', 'recipe' => 'o(X)?', 'properties' => array('WasJustCaptured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Skill attack using [o(V=12)?:9,o(Z=8)?:1] against [o(X=20)?:10]; Defender o(X=20)? was captured; Attacker o(V=12)? changed size from 12 to 10 sides, recipe changed from o(V=12)? to o(V=10)?, rerolled 9 => 1; Attacker o(Z=8)? changed size from 8 to 30 sides, recipe changed from o(Z=8)? to o(Z=30)?, rerolled 1 => 18'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . '\'s idle ornery dice rerolled at end of turn: o(W=11)? changed size from 11 to 8 sides, recipe changed from o(W=11)? to o(W=8)?, rerolled 4 => 5; o(Y=9)? changed size from 9 to 12 sides, recipe changed from o(Y=9)? to o(Y=12)?, rerolled 8 => 3'));
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - player 1 performs skill attack using all 4 dice
        // o(V)? attacks, goes to 10 sides (idx 2) and value 2
        // o(W)? attacks, goes to 12 sides (idx 4) and value 11
        // o(Y)? attacks, goes to 10 sides (idx 5) and value 7
        // o(Z)? attacks, goes to 8 sides (idx 2) and value 7
        $this->verify_api_submitTurn(
            array(2, 2, 4, 11, 5, 7, 2, 7),
            'responder003' . ' performed Skill attack using [o(V=12)?:3,o(W=4)?:2,o(Y=6)?:3,o(Z=12)?:10] against [o(Z=30)?:18]; Defender o(Z=30)? was captured; Attacker o(V=12)? changed size from 12 to 10 sides, recipe changed from o(V=12)? to o(V=10)?, rerolled 3 => 2; Attacker o(W=4)? changed size from 4 to 12 sides, recipe changed from o(W=4)? to o(W=12)?, rerolled 2 => 11; Attacker o(Y=6)? changed size from 6 to 10 sides, recipe changed from o(Y=6)? to o(Y=10)?, rerolled 3 => 7; Attacker o(Z=12)? changed size from 12 to 8 sides, recipe changed from o(Z=12)? to o(Z=8)?, rerolled 10 => 7. ',
            $retval, array(array(0, 0), array(0, 1), array(0, 2), array(0, 3), array(1, 3)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 60;
        $expData['playerDataArray'][1]['roundScore'] = 35;
        $expData['playerDataArray'][0]['sideScore'] = 16.7;
        $expData['playerDataArray'][1]['sideScore'] = -16.7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('HasJustBeenMoody');
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array('HasJustBeenMoody');
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustBeenMoody');
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array('HasJustBeenMoody');
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = 'Ornery V Mood Swing Die (with 10 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = 'Ornery W Mood Swing Die (with 12 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = 'Ornery Y Mood Swing Die (with 10 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'Ornery Z Mood Swing Die (with 8 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] =
            array('value' => 18, 'sides' => '30', 'recipe' => 'o(Z)?', 'properties' => array('WasJustCaptured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' performed Skill attack using [o(V=12)?:3,o(W=4)?:2,o(Y=6)?:3,o(Z=12)?:10] against [o(Z=30)?:18]; Defender o(Z=30)? was captured; Attacker o(V=12)? changed size from 12 to 10 sides, recipe changed from o(V=12)? to o(V=10)?, rerolled 3 => 2; Attacker o(W=4)? changed size from 4 to 12 sides, recipe changed from o(W=4)? to o(W=12)?, rerolled 2 => 11; Attacker o(Y=6)? changed size from 6 to 10 sides, recipe changed from o(Y=6)? to o(Y=10)?, rerolled 3 => 7; Attacker o(Z=12)? changed size from 12 to 8 sides, recipe changed from o(Z=12)? to o(Z=8)?, rerolled 10 => 7'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This scenario reproduces the option/swing setting bug in #1224
     * 0. Start a game with responder003 playing Mau and responder004 playing Wiseman
     * 1. responder003 set swing values: X=4
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [(6):3, (6):6, (8):7, (12):2, m(X=4):4], responder004 rolled [(20):11, (20):5, (20):8, (20):6].
     * 2. responder003 performed Skill attack using [(8):7,m(X=4):4] against [(20):11]; Defender (20) was captured; Attacker (8) rerolled 7 => 4; Attacker m(X=4) changed size from 4 to 20 sides, recipe changed from m(X=4) to m(20), rerolled 4 => 8
     * 3. responder004 performed Power attack using [(20):8] against [(12):2]; Defender (12) was captured; Attacker (20) rerolled 8 => 6
     * 4. responder003 performed Power attack using [(6):6] against [(20):6]; Defender (20) was captured; Attacker (6) rerolled 6 => 6
     * 5. responder004 performed Power attack using [(20):5] against [(6):3]; Defender (6) was captured; Attacker (20) rerolled 5 => 12
     * 6. responder003 performed Skill attack using [(8):4,m(20):8] against [(20):12]; Defender (20) was captured; Attacker (8) rerolled 4 => 3; Attacker m(20) rerolled 8 => 11
     * 7. responder004 performed Power attack using [(20):6] against [(8):3]; Defender (8) was captured; Attacker (20) rerolled 6 => 5
     * 8. responder003 performed Power attack using [(6):6] against [(20):5]; Defender (20) was captured; Attacker (6) rerolled 6 => 5
     *    End of round: responder003 won round 1 (93 vs. 26)
     * At this point, responder003 is incorrectly prompted to set swing dice
     */
    public function test_interface_game_005() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 5;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup

        // 4 of Mau's dice, and 4 of Wiseman's, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(3, 6, 7, 2, 11, 5, 8, 6),
            'responder003', 'responder004', 'Mau', 'Wiseman', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Morphing'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'Mau', 'recipe' => '(6) (6) (8) (12) m(X)', 'originalRecipe' => '(6) (6) (8) (12) m(X)', 'artFilename' => 'mau.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Wiseman', 'recipe' => '(20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20)', 'artFilename' => 'wiseman.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(X)', 'description' => 'Morphing X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - p1 sets swing values
        $this->verify_api_submitDieValues(
            array(4),
            $gameId, 1, array('X' => 4), NULL);

        // expected changes
        // note, since morphing dice are in play, canStillWin should remain null all game
        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Morphing X Swing Die (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][0]['sideScore'] = -14.7;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 14.7;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 6;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(6):3, (6):6, (8):7, (12):2, m(X=4):4], responder004 rolled [(20):11, (20):5, (20):8, (20):6].'));
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performed Skill attack using [(8):7,m(X=4):4] against [(20):11]
        //   [(6):3, (6):6, (8):7, (12):2, m(X=4):4] => [(20):11, (20):5, (20):8, (20):6]
        $this->verify_api_submitTurn(
            array(4, 8),
            'responder003 performed Skill attack using [(8):7,m(X=4):4] against [(20):11]; Defender (20) was captured; Attacker (8) rerolled 7 => 4; Attacker m(X=4) changed size from 4 to 20 sides, recipe changed from m(X=4) to m(20), rerolled 4 => 8. ',
            $retval, array(array(0, 2), array(0, 4), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 46;
        $expData['playerDataArray'][0]['sideScore'] = 10.7;
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][1]['sideScore'] = -10.7;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = 'm(20)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Morphing 20-sided die';
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][0]['capturedDieArray'][] = array( 'value' => 11, 'sides' => 20, 'recipe' => '(20)', 'properties' => array('WasJustCaptured'));
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(8):7,m(X=4):4] against [(20):11]; Defender (20) was captured; Attacker (8) rerolled 7 => 4; Attacker m(X=4) changed size from 4 to 20 sides, recipe changed from m(X=4) to m(20), rerolled 4 => 8'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 performed Power attack using [(20):8] against [(12):2]
        //   [(6):3, (6):6, (8):4, (12):2, m(20):8] <= [(20):5, (20):8, (20):6]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(6),
            'responder004 performed Power attack using [(20):8] against [(12):2]; Defender (12) was captured; Attacker (20) rerolled 8 => 6. ',
            $retval, array(array(0, 3), array(1, 1)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -1.3;
        $expData['playerDataArray'][1]['roundScore'] = 42;
        $expData['playerDataArray'][1]['sideScore'] = 1.3;
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][] = array( 'value' => 2, 'sides' => 12, 'recipe' => '(12)', 'properties' => array('WasJustCaptured'));
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 3, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):8] against [(12):2]; Defender (12) was captured; Attacker (20) rerolled 8 => 6'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Power attack using [(6):6] against [(20):6]
        //   [(6):3, (6):6, (8):4, m(20):8] => [(20):5, (20):6, (20):6]
        $this->verify_api_submitTurn(
            array(6),
            'responder003 performed Power attack using [(6):6] against [(20):6]; Defender (20) was captured; Attacker (6) rerolled 6 => 6. ',
            $retval, array(array(0, 1), array(1, 2)),
            $gameId, 1, 'Power', 0, 1, '');

        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(6):6] against [(20):6]; Defender (20) was captured; Attacker (6) rerolled 6 => 6'));
        $expData['gameActionLogCount'] += 1;

        // no changes of interest
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 05 - responder004 performed Power attack using [(20):5] against [(6):3]
        //   [(6):3, (6):6, (8):4, m(20):8] <= [(20):5, (20):6]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(12),
            'responder004 performed Power attack using [(20):5] against [(6):3]; Defender (6) was captured; Attacker (20) rerolled 5 => 12. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        array_splice($expData['playerDataArray'][0]['activeDieArray'], 0, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):5] against [(6):3]; Defender (6) was captured; Attacker (20) rerolled 5 => 12'));
        $expData['gameActionLogCount'] += 1;

        // no changes of interest
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 06 - responder003 performed Skill attack using [(8):4,m(20):8] against [(20):12]
        //   [(6):6, (8):4, m(20):8] => [(20):12, (20):6]
        $this->verify_api_submitTurn(
            array(3, 11),
            'responder003 performed Skill attack using [(8):4,m(20):8] against [(20):12]; Defender (20) was captured; Attacker (8) rerolled 4 => 3; Attacker m(20) remained the same size, rerolled 8 => 11. ',
            $retval, array(array(0, 1), array(0, 2), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes from past several rounds
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 77;
        $expData['playerDataArray'][0]['sideScore'] = 32.7;
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][1]['sideScore'] = -32.7;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustMorphed');
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array( 'value' => 6, 'sides' => 20, 'recipe' => '(20)', 'properties' => array() );
        $expData['playerDataArray'][0]['capturedDieArray'][] = array( 'value' => 12, 'sides' => 20, 'recipe' => '(20)', 'properties' => array('WasJustCaptured') );
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][] = array( 'value' => 3, 'sides' => 6, 'recipe' => '(6)', 'properties' => array() );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(8):4,m(20):8] against [(20):12]; Defender (20) was captured; Attacker (8) rerolled 4 => 3; Attacker m(20) remained the same size, rerolled 8 => 11'));
        $expData['gameActionLogCount'] += 1;

        // check here to verify that HasJustMorphed gets set on the die, even though it stayed the same size
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - responder004 performed Power attack using [(20):6] against [(8):3]
        //   [(6):6, (8):3, m(20):11] <= [(20):6]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(5),
            'responder004 performed Power attack using [(20):6] against [(8):3]; Defender (8) was captured; Attacker (20) rerolled 6 => 5. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes from past several rounds
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 73;
        $expData['playerDataArray'][0]['sideScore'] = 24.7;
        $expData['playerDataArray'][1]['roundScore'] = 36;
        $expData['playerDataArray'][1]['sideScore'] = -24.7;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['capturedDieArray'][] = array( 'value' => 3, 'sides' => 8, 'recipe' => '(8)', 'properties' => array('WasJustCaptured') );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):6] against [(8):3]; Defender (8) was captured; Attacker (20) rerolled 6 => 5'));
        $expData['gameActionLogCount'] += 1;

        // check here to verify that HasJustMorphed gets set on the die, even though it stayed the same size
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 08 - responder003 performed Power attack using [(6):6] against [(20):5]
        //   [(6):6, m(20):11] => [(20):5]
        // need 1 for attacker's reroll, and should be 9 for next round
        $this->verify_api_submitTurn(
            array(5, 2, 3, 2, 9, 2, 11, 20, 7, 3),
            'responder003 performed Power attack using [(6):6] against [(20):5]; Defender (20) was captured; Attacker (6) rerolled 6 => 5. End of round: responder003 won round 1 (93 vs. 26). responder003 won initiative for round 2. Initial die values: responder003 rolled [(6):2, (6):3, (8):2, (12):9, m(X=4):2], responder004 rolled [(20):11, (20):20, (20):7, (20):3]. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        $expData['roundNumber'] = 2;
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][0]['sideScore'] = -14.7;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 14.7;
        // all of these dice should have values
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 2, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 3, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 2, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => 9, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => 2, 'sides' => 4, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(X)', 'description' => 'Morphing X Swing Die (with 4 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 11, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => 20, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' =>  7, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' =>  3, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(6):6] against [(20):5]; Defender (20) was captured; Attacker (6) rerolled 6 => 5'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: responder003 won round 1 (93 vs. 26)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [(6):2, (6):3, (8):2, (12):9, m(X=4):2], responder004 rolled [(20):11, (20):20, (20):7, (20):3].'));
        $expData['gameActionLogCount'] += 3;

        // truncate log to 10 entries
        $expData['gameActionLog'] = array_slice($expData['gameActionLog'], 0, 10);

        // load and verify game attributes
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This scenario reproduces the morphing die bug in #1306
     * 0. Start a game with responder003 playing Mau and responder004 playing Skomp
     * 1. responder003 set swing values: X=4
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [(6):1, (6):2, (8):8, (12):7, m(X=4):4], responder004 rolled [wm(1):1, wm(2):2, wm(4):1, m(8):8, m(10):8]. responder004 has dice which are not counted for initiative due to die skills: [wm(1), wm(2), wm(4)].
     * 2. responder003 performed Skill attack using [(6):1,(12):7] against [m(10):8]; Defender m(10) was captured; Attacker (6) rerolled 1 => 4; Attacker (12) rerolled 7 => 6
     * 3. responder004 performed Skill attack using [wm(1):1,wm(2):2,wm(4):1] against [(6):4]; Defender (6) was captured; Attacker wm(1) changed size from 1 to 6 sides, recipe changed from wm(1) to wm(6), rerolled 1 => 5; Attacker wm(2) changed size from 2 to 6 sides, recipe changed from wm(2) to wm(6), rerolled 2 => 2; Attacker wm(4) changed size from 4 to 6 sides, recipe changed from wm(4) to wm(6), rerolled 1 => 6
     * 4. responder003 performed Skill attack using [(6):2,(12):6] against [m(8):8]; Defender m(8) was captured; Attacker (6) rerolled 2 => 5; Attacker (12) rerolled 6 => 8
     * 5. responder004 performed Skill attack using [wm(6):2,wm(6):6] against [(12):8]; Defender (12) was captured; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 2 => 8; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 6 => 2
     * At this point, responder004 incorrectly has dice [wm(6):5,wm(12):2:,wm(6):4], where it should be [wm(12):8,wm(12):2,wm(6):4]
     */
    public function test_interface_game_006() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder004 as soon as you've done so
        $this->game_number = 6;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup

        // 4 of Mau's dice, and 5 of Skomp's dice, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(1, 2, 8, 7, 1, 2, 1, 8, 8),
            'responder003', 'responder004', 'Mau', 'Skomp', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Morphing', 'Slow'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'Mau', 'recipe' => '(6) (6) (8) (12) m(X)', 'originalRecipe' => '(6) (6) (8) (12) m(X)', 'artFilename' => 'mau.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Skomp', 'recipe' => 'wm(1) wm(2) wm(4) m(8) m(10)', 'originalRecipe' => 'wm(1) wm(2) wm(4) m(8) m(10)', 'artFilename' => 'skomp.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(X)', 'description' => 'Morphing X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(1)', 'description' => 'Slow Morphing 1-sided die'),
            array('value' => NULL, 'sides' => 2, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(2)', 'description' => 'Slow Morphing 2-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(4)', 'description' => 'Slow Morphing 4-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(8)', 'description' => 'Morphing 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(10)', 'description' => 'Morphing 10-sided die'),
        );

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - p1 sets swing values
        $this->verify_api_submitDieValues(
            array(4),
            $gameId, 1, array('X' => 4), NULL);

        // expected changes
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][1]['roundScore'] = 12.5;
        $expData['playerDataArray'][0]['sideScore'] = 3.7;
        $expData['playerDataArray'][1]['sideScore'] = -3.7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(6):1, (6):2, (8):8, (12):7, m(X=4):4], responder004 rolled [wm(1):1, wm(2):2, wm(4):1, m(8):8, m(10):8]. responder004 has dice which are not counted for initiative due to die skills: [wm(1), wm(2), wm(4)].'));
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performed Skill attack using [(6):1,(12):7] against [m(10):8]
        // [(6):1, (6):2, (8):8, (12):7, m(X=4):4] => [wm(1):1, wm(2):2, wm(4):1, m(8):8, m(10):8]
        $this->verify_api_submitTurn(
            array(4, 6),
            'responder003 performed Skill attack using [(6):1,(12):7] against [m(10):8]; Defender m(10) was captured; Attacker (6) rerolled 1 => 4; Attacker (12) rerolled 7 => 6. ',
            $retval, array(array(0, 0), array(0, 3), array(1, 4)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 28;
        $expData['playerDataArray'][1]['roundScore'] = 7.5;
        $expData['playerDataArray'][0]['sideScore'] = 13.7;
        $expData['playerDataArray'][1]['sideScore'] = -13.7;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 8, 'sides' => 10, 'properties' => array('WasJustCaptured'), 'recipe' => 'm(10)');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 6;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(6):1,(12):7] against [m(10):8]; Defender m(10) was captured; Attacker (6) rerolled 1 => 4; Attacker (12) rerolled 7 => 6'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 performed Skill attack using [wm(1):1,wm(2):2,wm(4):1] against [(6):4]
        // [(6):4, (6):2, (8):8, (12):6, m(X=4):4] <= [wm(1):1, wm(2):2, wm(4):1, m(8):8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(5, 2, 6),
            'responder004 performed Skill attack using [wm(1):1,wm(2):2,wm(4):1] against [(6):4]; Defender (6) was captured; Attacker wm(1) changed size from 1 to 6 sides, recipe changed from wm(1) to wm(6), rerolled 1 => 5; Attacker wm(2) changed size from 2 to 6 sides, recipe changed from wm(2) to wm(6), rerolled 2 => 2; Attacker wm(4) changed size from 4 to 6 sides, recipe changed from wm(4) to wm(6), rerolled 1 => 6. ',
            $retval, array(array(0, 0), array(1, 0), array(1, 1), array(1, 2)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][1]['roundScore'] = 19;
        $expData['playerDataArray'][0]['sideScore'] = 4.0;
        $expData['playerDataArray'][1]['sideScore'] = -4.0;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 4, 'sides' => 6, 'properties' => array('WasJustCaptured'), 'recipe' => '(6)');
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = 'wm(6)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = 'Slow Morphing 6-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = 'wm(6)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = 'Slow Morphing 6-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = 'wm(6)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = 'Slow Morphing 6-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [wm(1):1,wm(2):2,wm(4):1] against [(6):4]; Defender (6) was captured; Attacker wm(1) changed size from 1 to 6 sides, recipe changed from wm(1) to wm(6), rerolled 1 => 5; Attacker wm(2) changed size from 2 to 6 sides, recipe changed from wm(2) to wm(6), rerolled 2 => 2; Attacker wm(4) changed size from 4 to 6 sides, recipe changed from wm(4) to wm(6), rerolled 1 => 6'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Skill attack using [(6):2,(12):6] against [m(8):8]
        // [(6):2, (8):8, (12):6, m(X=4):4] => [wm(6):5, wm(6):2, wm(6):6, m(8):8]
        $this->verify_api_submitTurn(
            array(5, 8),
            'responder003 performed Skill attack using [(6):2,(12):6] against [m(8):8]; Defender m(8) was captured; Attacker (6) rerolled 2 => 5; Attacker (12) rerolled 6 => 8. ',
            $retval, array(array(0, 0), array(0, 2), array(1, 3)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 33;
        $expData['playerDataArray'][1]['roundScore'] = 15;
        $expData['playerDataArray'][0]['sideScore'] = 12.0;
        $expData['playerDataArray'][1]['sideScore'] = -12.0;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 8, 'sides' => 8, 'properties' => array('WasJustCaptured'), 'recipe' => 'm(8)');
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(6):2,(12):6] against [m(8):8]; Defender m(8) was captured; Attacker (6) rerolled 2 => 5; Attacker (12) rerolled 6 => 8'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder004 performed Skill attack using [wm(6):2,wm(6):6] against [(12):8]
        // [(6):5, (8):8, (12):8, m(X=4):4] <= [wm(6):5, wm(6):2, wm(6):6]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(8, 2),
            'responder004 performed Skill attack using [wm(6):2,wm(6):6] against [(12):8]; Defender (12) was captured; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 2 => 8; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 6 => 2. ',
            $retval, array(array(0, 2), array(1, 1), array(1, 2)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 27;
        $expData['playerDataArray'][1]['roundScore'] = 33;
        $expData['playerDataArray'][0]['sideScore'] = -4.0;
        $expData['playerDataArray'][1]['sideScore'] = 4.0;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 8, 'sides' => 12, 'properties' => array('WasJustCaptured'), 'recipe' => '(12)');
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = 'wm(12)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = 'Slow Morphing 12-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = 'wm(12)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = 'Slow Morphing 12-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 2;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [wm(6):2,wm(6):6] against [(12):8]; Defender (12) was captured; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 2 => 8; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 6 => 2'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This scenario tests unsuccessful and successful trip attacks.
     * 0. Start a game with responder003 playing Hope and responder004 playing Stumbling Clowns
     * 1. responder003 set swing values: Y=1
     * 2. responder004 set swing values: X=11
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [t(1):1, (2):1, t(4):1, (6):6, (Y=1):1], responder004 rolled [(8):5, t(8):8, (10):10, t(10):10, (X=11):10]. responder003 has dice which are not counted for initiative due to die skills: [t(1), t(4)]. responder004 has dice which are not counted for initiative due to die skills: [t(8), t(10)].
     * 3. responder003 performed Trip attack using [t(4):1] against [t(10):10]; Attacker t(4) rerolled 1 => 1; Defender t(10) rerolled 10 => 4, was not captured
     * 4. responder004 performed Trip attack using [t(10):4] against [(6):6]; Attacker t(10) rerolled 4 => 10; Defender (6) rerolled 6 => 2, was captured
     */
    public function test_interface_game_007() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 7;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup

        // 4 of Hope's dice, and 4 of Stumbling Clowns' dice, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(1, 1, 1, 6, 5, 8, 10, 10),
            'responder003', 'responder004', 'Hope', 'Stumbling Clowns', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Trip'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('Y' => array(1, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Hope', 'recipe' => 't(1) (2) t(4) (6) (Y)', 'originalRecipe' => 't(1) (2) t(4) (6) (Y)', 'artFilename' => 'hope.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Stumbling Clowns', 'recipe' => '(8) t(8) (10) t(10) (X)', 'originalRecipe' => '(8) t(8) (10) t(10) (X)', 'artFilename' => 'stumblingclowns.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(1)', 'description' => 'Trip 1-sided die'),
            array('value' => NULL, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(4)', 'description' => 'Trip 4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(Y)', 'description' => 'Y Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(8)', 'description' => 'Trip 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(10)', 'description' => 'Trip 10-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - p1 sets swing values
        $this->verify_api_submitDieValues(
            array(1),
            $gameId, 1, array('Y' => 1), NULL);

        // expected changes
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Y Swing Die (with 1 side)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set die sizes'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - p2 sets swing values
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(10),
            $gameId, 1, array('X' => 11), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Trip');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 7;
        $expData['playerDataArray'][1]['roundScore'] = 23.5;
        $expData['playerDataArray'][0]['sideScore'] = -11.0;
        $expData['playerDataArray'][1]['sideScore'] = 11.0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = 'X Swing Die (with 11 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 10;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['gameActionLog'][0]['message'] = 'responder003 set swing values: Y=1';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=11'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [t(1):1, (2):1, t(4):1, (6):6, (Y=1):1], responder004 rolled [(8):5, t(8):8, (10):10, t(10):10, (X=11):10]. responder003 has dice which are not counted for initiative due to die skills: [t(1), t(4)]. responder004 has dice which are not counted for initiative due to die skills: [t(8), t(10)].'));
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder003 performed Trip attack using [t(4):1] against [t(10):10] (and it was unsuccessful)
        // [t(1):1, (2):1, t(4):1, (6):6, (Y=1):1] => [(8):5, t(8):8, (10):10, t(10):10, (X=11):10]
        $this->verify_api_submitTurn(
            array(1, 4),
            'responder003 performed Trip attack using [t(4):1] against [t(10):10]; Attacker t(4) rerolled 1 => 1; Defender t(10) rerolled 10 => 4, was not captured. ',
            $retval, array(array(0, 2), array(1, 3)),
            $gameId, 1, 'Trip', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Trip');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('JustPerformedTripAttack', 'JustPerformedUnsuccessfulAttack');
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 4;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [t(4):1] against [t(10):10]; Attacker t(4) rerolled 1 => 1; Defender t(10) rerolled 10 => 4, was not captured'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder004 performed Trip attack using [t(10):4] against [(6):6] (and it was successful)
        // [t(1):1, (2):1, t(4):1, (6):6, (Y=1):1] <= [(8):5, t(8):8, (10):10, t(10):10, (X=11):10]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(10, 2),
            'responder004 performed Trip attack using [t(10):4] against [(6):6]; Attacker t(10) rerolled 4 => 10; Defender (6) rerolled 6 => 2, was captured. ',
            $retval, array(array(0, 3), array(1, 3)),
            $gameId, 1, 'Trip', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Trip');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 4;
        $expData['playerDataArray'][1]['roundScore'] = 29.5;
        $expData['playerDataArray'][0]['sideScore'] = -17.0;
        $expData['playerDataArray'][1]['sideScore'] = 17.0;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array('JustPerformedTripAttack');
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 10;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 2, 'sides' => 6, 'properties' => array('WasJustCaptured'), 'recipe' => '(6)');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Trip attack using [t(10):4] against [(6):6]; Attacker t(10) rerolled 4 => 10; Defender (6) rerolled 6 => 2, was captured'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This scenario tests unsuccessful and successful morphing trip attacks, and also basic regression tests of adjusting fire dice
     * 0. Start a game with responder003 playing BlackOmega and responder004 playing Tamiya
     *    responder004 won initiative for round 1. Initial die values: responder003 rolled [tm(6):5, f(8):8, g(10):1, z(10):3, sF(20):7], responder004 rolled [(4):1, (8):7, (8):1, (12):9, z(20):18]. responder003 has dice which are not counted for initiative due to die skills: [tm(6), g(10)].
     * 1. responder004 performed Power attack using [(8):1] against [g(10):1]; Defender g(10) was captured; Attacker (8) rerolled 1 => 6
     * 2. responder003 performed Trip attack using [tm(6):5] against [z(20):18]; Attacker tm(6) rerolled 5 => 1; Defender z(20) rerolled 18 => 4, was not captured; Attacker tm(6) changed size from 6 to 20 sides, recipe changed from tm(6) to tm(20), rerolled 1 => 4
     * 3. responder004 performed Power attack using [z(20):4] against [z(10):3]; Defender z(10) was captured; Attacker z(20) rerolled 4 => 17
     * 4. responder003 performed Trip attack using [tm(20):4] against [(8):6]; Attacker tm(20) rerolled 4 => 20; Defender (8) rerolled 6 => 5, was captured; Attacker tm(20) changed size from 20 to 8 sides, recipe changed from tm(20) to tm(8), rerolled 20 => 5
     * 5. responder004 performed Power attack using [(12):9] against [f(8):8]; Attacker (12) rerolled 9 => 11; Defender f(8) was captured
     * 6. responder003 chose to perform a Skill attack using [tm(8):3] against [(8):7]; responder003 must turn down fire dice to complete this attack
     * N.B. this test is cloned as test_interface_game_042 to check regressions for a player who has fire_overshooting enabled
     */
    public function test_interface_game_008() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 8;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup

        // 5 of BlackOmega's dice, and 5 of Tamiya's dice, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(5, 8, 1, 3, 7, 1, 7, 1, 9, 18),
            'responder003', 'responder004', 'BlackOmega', 'Tamiya', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [tm(6):5, f(8):8, g(10):1, z(10):3, sF(20):7], responder004 rolled [(4):1, (8):7, (8):1, (12):9, z(20):18]. responder003 has dice which are not counted for initiative due to die skills: [tm(6), g(10)].'));
        $expData['gameActionLogCount'] += 1;

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
        $_SESSION = $this->mock_test_user_login('responder003');

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
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performed Trip attack using [tm(6):5] against [z(20):18] (unsuccessfully)
        // [tm(6):5, f(8):8, z(10):3, sF(20):7] => [(4):1, (8):7, (8):6, (12):9, z(20):18]
        $this->verify_api_submitTurn(
            array(1, 4),
            'responder003 performed Trip attack using [tm(6):5] against [z(20):18]; Attacker tm(6) rerolled 5 => 1; Defender z(20) rerolled 18 => 4, was not captured. ',
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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tm(6):5] against [z(20):18]; Attacker tm(6) rerolled 5 => 1; Defender z(20) rerolled 18 => 4, was not captured'));
        $expData['gameActionLogCount'] += 1;

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
        $_SESSION = $this->mock_test_user_login('responder003');

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
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Trip attack using [tm(6):4] against [(8):6] (successfully)
        // [tm(6):1, f(8):8, sF(20):7] => [(4):1, (8):7, (8):6, (12):9, z(20):17]
        $this->verify_api_submitTurn(
            array(6, 5, 3),
            'responder003 performed Trip attack using [tm(6):1] against [(8):6]; Attacker tm(6) rerolled 1 => 6; Defender (8) rerolled 6 => 5, was captured; Attacker tm(6) changed size from 6 to 8 sides, recipe changed from tm(6) to tm(8), rerolled 6 => 3. ',
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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tm(6):1] against [(8):6]; Attacker tm(6) rerolled 1 => 6; Defender (8) rerolled 6 => 5, was captured; Attacker tm(6) changed size from 6 to 8 sides, recipe changed from tm(6) to tm(8), rerolled 6 => 3'));
        $expData['gameActionLogCount'] += 1;

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
        $_SESSION = $this->mock_test_user_login('responder003');

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
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder003 chose to perform a Skill attack using [tm(8):3] against [(8):7]; responder003 must turn down fire dice to complete this attack
        // [tm(8):3, sF(20):7] => [(4):1, (8):7, (12):11, z(20):17]
        $this->verify_api_submitTurn(
            array(),
            'responder003 chose to perform a Skill attack using [tm(8):3] against [(8):7]; responder003 must turn down fire dice to complete this attack. ',
            $retval, array(array(0, 0), array(1, 1)),
            $gameId, 1, 'Skill', 0, 1, '');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to perform a Skill attack using [tm(8):3] against [(8):7]; responder003 must turn down fire dice to complete this attack'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameState'] = 'ADJUST_FIRE_DICE';
        $expData['validAttackTypeArray'] = array('Skill');
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('IsAttacker');
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array('IsAttackTarget');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // now load the game as non-participating player responder001 and check its state
        $_SESSION = $this->mock_test_user_login('responder001');
        $expData['dieBackgroundType'] = 'symmetric';
        $this->verify_api_loadGameData_as_nonparticipant($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['dieBackgroundType'] = 'realistic';

        ////////////////////
        // Move 07 - responder003 abandons the Fire-assisted Skill attack and gets another attack
        // [tm(8):3, sF(20):7] => [(4):1, (8):7, (12):11, z(20):17]
        $this->verify_api_adjustFire(
            array(),
            'responder003 chose to abandon this attack and start over. ',
            $retval, $gameId, 1, 'cancel', NULL, NULL);

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to abandon this attack and start over'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameState'] = 'START_TURN';
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow', 'Trip');
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // N.B - this game and the game in test 42 diverge at this point

        ////////////////////
        // Move 08 - responder003 chose to perform a Power attack using [tm(8):3] against [(4):1]
        // [tm(8):3, sF(20):7] => [(4):1, (8):7, (12):11, z(20):17]
        $this->verify_api_submitTurn(
            array(2),
            'responder003 performed Power attack using [tm(8):3] against [(4):1]; Defender (4) was captured; Attacker tm(8) changed size from 8 to 4 sides, recipe changed from tm(8) to tm(4), rerolled 3 => 2. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [tm(8):3] against [(4):1]; Defender (4) was captured; Attacker tm(8) changed size from 8 to 4 sides, recipe changed from tm(8) to tm(4), rerolled 3 => 2'));
        $expData['gameActionLogCount'] += 1;

        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 24;
        $expData['playerDataArray'][1]['roundScore'] = 48;
        $expData['playerDataArray'][0]['sideScore'] = -16.0;
        $expData['playerDataArray'][1]['sideScore'] = 16.0;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = 'tm(4)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = 'Trip Morphing 4-sided die';
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('HasJustMorphed');
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('sides' => 4, 'value' => 1, 'recipe' => '(4)', 'properties' => array('WasJustCaptured'));
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This scenario tests some simple auxiliary, swing, and focus functionality
     * 0. Start a game with responder003 playing Merlin and responder004 playing Crane
     * 1. responder004 chose to use auxiliary die +s(X) in this game
     * 2. responder003 chose to use auxiliary die +s(X) in this game
     * 3. responder004 set swing values: X=16
     * 4. responder003 set swing values: X=4
     *    responder004 won initiative for round 1. Initial die values: responder003 rolled [(2):1, (4):4, s(10):2, s(20):20, (X=4):4, s(X=4):3], responder004 rolled [(4):3, f(6):3, f(8):3, (10):1, (12):9, s(X=16):1].
     * 5. responder004 performed Skill attack using [f(8):3,(10):1] against [(4):4]; Defender (4) was captured; Attacker f(8) rerolled 3 => 4; Attacker (10) rerolled 1 => 5
     * 6. responder003 performed Power attack using [(2):1] against [s(X=16):1]; Defender s(X=16) was captured; Attacker (2) rerolled 1 => 2
     * 7. responder004 performed Power attack using [(10):5] against [(X=4):4]; Defender (X=4) was captured; Attacker (10) rerolled 5 => 1
     * 8. responder003 performed Shadow attack using [s(10):2] against [(12):9]; Defender (12) was captured; Attacker s(10) rerolled 2 => 4
     * 9. responder004 performed Skill attack using [f(6):3,(10):1] against [s(10):4]; Defender s(10) was captured; Attacker f(6) rerolled 3 => 3; Attacker (10) rerolled 1 => 2
     * 10. responder003 performed Power attack using [(2):2] against [(10):2]; Defender (10) was captured; Attacker (2) rerolled 2 => 1
     * 11. responder004 performed Power attack using [f(8):4] against [s(X=4):3]; Defender s(X=4) was captured; Attacker f(8) rerolled 4 => 7
     *     responder003 passed
     * 12. responder004 performed Power attack using [f(8):7] against [(2):1]; Defender (2) was captured; Attacker f(8) rerolled 7 => 8
     *     responder003 passed
     *     responder004 passed
     *     responder003 won round 1 (48 vs. 33)
     * 13. responder004 set swing values: X=15
     *     responder003 won initiative for round 2. Initial die values: responder003 rolled [(2):2, (4):2, s(10):1, s(20):9, (X=4):4, s(X=4):1], responder004 rolled [(4):4, f(6):4, f(8):8, (10):1, (12):8, s(X=15):11].
     * 14. responder004 gained initiative by turning down focus dice: f(6) from 4 to 1, f(8) from 8 to 1
     * 15. responder004 performed Skill attack using [(10):1,(12):8] against [s(20):9]; Defender s(20) was captured; Attacker (10) rerolled 1 => 2; Attacker (12) rerolled 8 => 7
     */
    public function test_interface_game_009() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 9;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // In a game with auxiliary dice, no dice are rolled until after auxiliary selection
        $gameId = $this->verify_api_createGame(
            array(),
            'responder003', 'responder004', 'Merlin', 'Crane', 3);

        // Initial expected game data objects - maintain two parallel objects until we're done
        // testing for information leakage about responder004's auxiliary choice
        $initialExpData = array(
            $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'CHOOSE_AUXILIARY_DICE'),
            $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'CHOOSE_AUXILIARY_DICE'),
        );
        foreach (array(0, 1) as $idx) {
            $initialExpData[$idx]['gameSkillsInfo'] = $this->get_skill_info(array('Auxiliary', 'Focus', 'Shadow'));
            $initialExpData[$idx]['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
            $initialExpData[$idx]['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
            $initialExpData[$idx]['playerDataArray'][0]['button'] = array('name' => 'Merlin', 'recipe' => '(2) (4) s(10) s(20) (X) +s(X)', 'originalRecipe' => '(2) (4) s(10) s(20) (X) +s(X)', 'artFilename' => 'merlin.png');
            $initialExpData[$idx]['playerDataArray'][1]['button'] = array('name' => 'Crane', 'recipe' => '(4) f(6) f(8) (10) (12)', 'originalRecipe' => '(4) f(6) f(8) (10) (12)', 'artFilename' => 'crane.png');
            $initialExpData[$idx]['playerDataArray'][0]['activeDieArray'] = array(
                array('value' => NULL, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
                array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
                array('value' => NULL, 'sides' => 10, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(10)', 'description' => 'Shadow 10-sided die'),
                array('value' => NULL, 'sides' => 20, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(20)', 'description' => 'Shadow 20-sided die'),
                array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
                array('value' => NULL, 'sides' => NULL, 'skills' => array('Auxiliary', 'Shadow'), 'properties' => array(), 'recipe' => '+s(X)', 'description' => 'Auxiliary Shadow X Swing Die'),
            );
            $initialExpData[$idx]['playerDataArray'][1]['activeDieArray'] = array(
                array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
                array('value' => NULL, 'sides' => 6, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(6)', 'description' => 'Focus 6-sided die'),
                array('value' => NULL, 'sides' => 8, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(8)', 'description' => 'Focus 8-sided die'),
                array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
                array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
                array('value' => NULL, 'sides' => NULL, 'skills' => array('Auxiliary', 'Shadow'), 'properties' => array(), 'recipe' => '+s(X)', 'description' => 'Auxiliary Shadow X Swing Die'),
            );
        }

        // now load the game from responder003's POV and check its state
        $retval = $this->verify_api_loadGameData($initialExpData[0], $gameId, 10);

        //////////
        // WARNING - this section tests what each of responder003 and responder004 sees after responder004
        // decides to keep the auxiliary die.  If you make any modifications to $initialExpData[0] between
        // this message and the "END WARNING" comment, you are probably introducing information leakage
        //////////

        ////////////////////
        // Move 01 - responder004 chose to use auxiliary die +s(X) in this game
        // no dice are rolled when the first player chooses auxiliary
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_reactToAuxiliary(
            array(),
            'Chose to add auxiliary die',
            $gameId, 'add', 5);

        $initialExpData[1]['currentPlayerIdx'] = 1;
        $initialExpData[1]['playerDataArray'][0]['playerColor'] = '#ddffdd';
        $initialExpData[1]['playerDataArray'][1]['playerColor'] = '#dd99dd';

        // the API must tell the truth about whether the active player has
        // responded to auxiliary
        $initialExpData[1]['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $initialExpData[1]['playerDataArray'][1]['activeDieArray'][5]['properties'] =
            array('AddAuxiliary');

        // the courtesy die is no longer offered after this
        $initialExpData[1]['playerDataArray'][1]['swingRequestArray'] = array();

        $retval = $this->verify_api_loadGameData($initialExpData[1], $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder003');

        // The API should lie about whether another player has responded to auxiliary
        // to avoid information leaks - load the game from responder003's point of view
        // using the unmodified $initialExpData[0] to test this
        $retval = $this->verify_api_loadGameData($initialExpData[0], $gameId, 10);

        //////////
        // END WARNING - the remainder of this test will use $expData and test only responder003's view
        //////////
        $expData = $initialExpData[0];

        ////////////////////
        // Move 02 - responder003 chose to use auxiliary die +s(X) in this game
        // 4 of Merlin's dice, and 5 of Crane's, are rolled initially
        $this->verify_api_reactToAuxiliary(
            array(1, 4, 2, 20, 3, 3, 3, 1, 9),
            'responder003 chose to use auxiliary die +s(X) in this game. ',
            $gameId, 'add', 5);

        // expected changes
        // #1216: Auxiliary shouldn't be removed from the list of game skills
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Focus', 'Shadow'));
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['button']['recipe'] = '(2) (4) s(10) s(20) (X) s(X)';
        $expData['playerDataArray'][1]['button']['recipe'] = '(4) f(6) f(8) (10) (12) s(X)';
        $expData['playerDataArray'][0]['activeDieArray'][5]['skills'] = array('Shadow');
        $expData['playerDataArray'][0]['activeDieArray'][5]['recipe'] = 's(X)';
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = 'Shadow X Swing Die';
        $expData['playerDataArray'][1]['activeDieArray'][5]['skills'] = array('Shadow');
        $expData['playerDataArray'][1]['activeDieArray'][5]['recipe'] = 's(X)';
        $expData['playerDataArray'][1]['activeDieArray'][5]['description'] = 'Shadow X Swing Die';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 chose to use auxiliary die +s(X) in this game'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to use auxiliary die +s(X) in this game'));
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 set swing values: X=16
        // responder004's X swing die is rolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(1),
            $gameId, 1, array('X' => 16), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set die sizes'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 set swing values: X=4
        // responder003's two X swing dice are rolled
        $this->verify_api_submitDieValues(
            array(4, 3),
            $gameId, 1, array('X' => 4), NULL);

        // expected changes
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['roundScore'] = 22;
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][0]['sideScore'] = -4.0;
        $expData['playerDataArray'][1]['sideScore'] = 4.0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][5]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][5]['sides'] = 16;
        $expData['playerDataArray'][1]['activeDieArray'][5]['description'] .= ' (with 16 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['gameActionLog'][0]['message'] = 'responder004 set swing values: X=16';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [(2):1, (4):4, s(10):2, s(20):20, (X=4):4, s(X=4):3], responder004 rolled [(4):3, f(6):3, f(8):3, (10):1, (12):9, s(X=16):1].'));
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder004 performed Skill attack using [f(8):3,(10):1] against [(4):4]
        // [(2):1, (4):4, s(10):2, s(20):20, (X=4):4, s(X=4):3] <= [(4):3, f(6):3, f(8):3, (10):1, (12):9, s(X=16):1]
        // verify simple Default Skill attack
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(4, 5),
            'responder004 performed Skill attack using [f(8):3,(10):1] against [(4):4]; Defender (4) was captured; Attacker f(8) rerolled 3 => 4; Attacker (10) rerolled 1 => 5. ',
            $retval, array(array(0, 1), array(1, 2), array(1, 3)),
            $gameId, 1, 'Default', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 20;
        $expData['playerDataArray'][1]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = -8.0;
        $expData['playerDataArray'][1]['sideScore'] = 8.0;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 4, 'sides' => 4, 'properties' => array('WasJustCaptured'), 'recipe' => '(4)');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 5;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [f(8):3,(10):1] against [(4):4]; Defender (4) was captured; Attacker f(8) rerolled 3 => 4; Attacker (10) rerolled 1 => 5'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder003 performed Power attack using [(2):1] against [s(X=16):1]
        // [(2):1, s(10):2, s(20):20, (X=4):4, s(X=4):3] => [(4):3, f(6):3, f(8):4, (10):5, (12):9, s(X=16):1]
        // verify Default Power attack in one-on-one Power/Skill case
        $this->verify_api_submitTurn(
            array(2),
            'responder003 performed Power attack using [(2):1] against [s(X=16):1]; Defender s(X=16) was captured; Attacker (2) rerolled 1 => 2. ',
            $retval, array(array(0, 0), array(1, 5)),
            $gameId, 1, 'Default', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 36;
        $expData['playerDataArray'][1]['roundScore'] = 24;
        $expData['playerDataArray'][0]['sideScore'] = 8.0;
        $expData['playerDataArray'][1]['sideScore'] = -8.0;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 5, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 1, 'sides' => 16, 'properties' => array('WasJustCaptured'), 'recipe' => 's(X)');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(2):1] against [s(X=16):1]; Defender s(X=16) was captured; Attacker (2) rerolled 1 => 2'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - responder004 performed Power attack using [(10):5] against [(X=4):4]
        // [(2):1, s(10):2, s(20):20, (X=4):4, s(X=4):3] <= [(4):3, f(6):3, f(8):4, (10):5, (12):9]
        // verify simple Default Power attack
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Power attack using [(10):5] against [(X=4):4]; Defender (X=4) was captured; Attacker (10) rerolled 5 => 1. ',
            $retval, array(array(0, 3), array(1, 3)),
            $gameId, 1, 'Default', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 34;
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][0]['sideScore'] = 4.0;
        $expData['playerDataArray'][1]['sideScore'] = -4.0;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 4, 'sides' => 4, 'properties' => array('WasJustCaptured'), 'recipe' => '(X)');
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(10):5] against [(X=4):4]; Defender (X=4) was captured; Attacker (10) rerolled 5 => 1'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 08 - responder003 performed Shadow attack using [s(10):2] against [(12):9]
        // [(2):1, s(10):2, s(20):20, s(X=4):3] => [(4):3, f(6):3, f(8):4, (10):1, (12):9]
        // verify simple Default Shadow attack
        $this->verify_api_submitTurn(
            array(4),
            'responder003 performed Shadow attack using [s(10):2] against [(12):9]; Defender (12) was captured; Attacker s(10) rerolled 2 => 4. ',
            $retval, array(array(0, 1), array(1, 4)),
            $gameId, 1, 'Default', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 46;
        $expData['playerDataArray'][1]['roundScore'] = 22;
        $expData['playerDataArray'][0]['sideScore'] = 16.0;
        $expData['playerDataArray'][1]['sideScore'] = -16.0;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 9, 'sides' => 12, 'properties' => array('WasJustCaptured'), 'recipe' => '(12)');
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Shadow attack using [s(10):2] against [(12):9]; Defender (12) was captured; Attacker s(10) rerolled 2 => 4'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 09 - responder004 performed Skill attack using [f(6):3,(10):1] against [s(10):4]
        // [(2):1, s(10):4, s(20):20, s(X=4):3] <= [(4):3, f(6):3, f(8):4, (10):1]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3, 2),
            'responder004 performed Skill attack using [f(6):3,(10):1] against [s(10):4]; Defender s(10) was captured; Attacker f(6) rerolled 3 => 3; Attacker (10) rerolled 1 => 2. ',
            $retval, array(array(0, 1), array(1, 1), array(1, 3)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 41;
        $expData['playerDataArray'][1]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = 6.0;
        $expData['playerDataArray'][1]['sideScore'] = -6.0;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 4, 'sides' => 10, 'properties' => array('WasJustCaptured'), 'recipe' => 's(10)');
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 2;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [f(6):3,(10):1] against [s(10):4]; Defender s(10) was captured; Attacker f(6) rerolled 3 => 3; Attacker (10) rerolled 1 => 2'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 10 - responder003 performed Power attack using [(2):2] against [(10):2]
        // [(2):2, s(20):20, s(X=4):3] => [(4):3, f(6):3, f(8):4, (10):2]
        $this->verify_api_submitTurn(
            array(1),
            'responder003 performed Power attack using [(2):2] against [(10):2]; Defender (10) was captured; Attacker (2) rerolled 2 => 1. ',
            $retval, array(array(0, 0), array(1, 3)),
            $gameId, 1, 'Power', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 51;
        $expData['playerDataArray'][1]['roundScore'] = 27;
        $expData['playerDataArray'][0]['sideScore'] = 16.0;
        $expData['playerDataArray'][1]['sideScore'] = -16.0;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 2, 'sides' => 10, 'properties' => array('WasJustCaptured'), 'recipe' => '(10)');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(2):2] against [(10):2]; Defender (10) was captured; Attacker (2) rerolled 2 => 1'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 11 - responder004 performed Power attack using [f(8):4] against [s(X=4):3] (responder003 passed)
        // [(2):1, s(20):20, s(X=4):3] <= [(4):3, f(6):3, f(8):4]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(7),
            'responder004 performed Power attack using [f(8):4] against [s(X=4):3]; Defender s(X=4) was captured; Attacker f(8) rerolled 4 => 7. responder003 passed. ',
            $retval, array(array(0, 2), array(1, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 49;
        $expData['playerDataArray'][1]['roundScore'] = 31;
        $expData['playerDataArray'][0]['sideScore'] = 12.0;
        $expData['playerDataArray'][1]['sideScore'] = -12.0;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 3, 'sides' => 4, 'properties' => array(), 'recipe' => 's(X)');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 7;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [f(8):4] against [s(X=4):3]; Defender s(X=4) was captured; Attacker f(8) rerolled 4 => 7'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 12 - responder004 performed Power attack using [f(8):7] against [(2):1] (all pass, end of round)
        // [(2):1, s(20):20] <= [(4):3, f(6):3, f(8):7]
        // First roll is for the attack, then 6 of Merlin's dice and 5 of Crane's reroll for the next round
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(8, 2, 2, 1, 9, 4, 1, 4, 4, 8, 1, 8),
            'responder004 performed Power attack using [f(8):7] against [(2):1]; Defender (2) was captured; Attacker f(8) rerolled 7 => 8. responder003 passed. responder004 passed. End of round: responder003 won round 1 (48 vs. 33). ',
            $retval, array(array(0, 0), array(1, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['roundNumber'] = 2;
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array('X' => 4);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array('X' => 16);
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(10)', 'description' => 'Shadow 10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(20)', 'description' => 'Shadow 20-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die (with 4 sides)'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(X)', 'description' => 'Shadow X Swing Die (with 4 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(6)', 'description' => 'Focus 6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(8)', 'description' => 'Focus 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(X)', 'description' => 'Shadow X Swing Die'),
        );
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [f(8):7] against [(2):1]; Defender (2) was captured; Attacker f(8) rerolled 7 => 8'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: responder003 won round 1 (48 vs. 33)'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 4;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 13 - responder004 set swing values: X=15
        // responder004's X swing die is rolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(11),
            $gameId, 2, array('X' => 15), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['gameState'] = 'REACT_TO_INITIATIVE';
        $expData['activePlayerIdx'] = NULL;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 22;
        $expData['playerDataArray'][1]['roundScore'] = 27.5;
        $expData['playerDataArray'][0]['sideScore'] = -3.7;
        $expData['playerDataArray'][1]['sideScore'] = 3.7;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][5]['sides'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][5]['description'] .= ' (with 15 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=15'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [(2):2, (4):2, s(10):1, s(20):9, (X=4):4, s(X=4):1], responder004 rolled [(4):4, f(6):4, f(8):8, (10):1, (12):8, s(X=15):11].'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 14 - responder004 gained initiative by turning down focus dice: f(6) from 4 to 1, f(8) from 8 to 1
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_reactToInitiative(
            array(),
            'Successfully gained initiative', array('gainedInitiative' => TRUE),
            $retval, $gameId, 2, 'focus', array(1, 2), array('1', '1'));
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array('Dizzy');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array('Dizzy');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 gained initiative by turning down focus dice: f(6) from 4 to 1, f(8) from 8 to 1'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 15 - responder004 performed Skill attack using [(10):1,(12):8] against [s(20):9]
        // [(2):2, (4):2, s(10):1, s(20):9, (X=4):4, s(X=4):1] <= [(4):4, f(6):1, f(8):1, (10):1, (12):8, s(X=15):11]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2, 7),
            'responder004 performed Skill attack using [(10):1,(12):8] against [s(20):9]; Defender s(20) was captured; Attacker (10) rerolled 1 => 2; Attacker (12) rerolled 8 => 7. ',
            $retval, array(array(0, 3), array(1, 3), array(1, 4)),
            $gameId, 2, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 12;
        $expData['playerDataArray'][1]['roundScore'] = 47.5;
        $expData['playerDataArray'][0]['sideScore'] = -23.7;
        $expData['playerDataArray'][1]['sideScore'] = 23.7;
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 9, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => 's(20)');
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 7;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(10):1,(12):8] against [s(20):9]; Defender s(20) was captured; Attacker (10) rerolled 1 => 2; Attacker (12) rerolled 8 => 7'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This scenario tests reserve dice, swing dice, speed attacks, "you can't win",
     * surrendering, and viewing a completed game with reserve and swing dice:
     * 0. Start a game with responder003 playing Washu and responder004 playing Hooloovoo
     * 1. responder004 set swing values: T=2, W=8, X=20, Z=28
     * 2. responder003 set swing values: X=5
     *    responder004 won initiative for round 1. Initial die values: responder003 rolled [(4):3, (6):1, (12):5, (X=5):3], responder004 rolled [q(T=2):1, q(W=8):8, q(X=20):1, q(Z=28):22].
     * 3. responder004 performed Shadow attack using [q(X=20):1] against [(12):5]; Defender (12) was captured; Attacker q(X=20) rerolled 1 => 17
     * 4. responder003 performed Power attack using [(6):1] against [q(T=2):1]; Defender q(T=2) was captured; Attacker (6) rerolled 1 => 5
     * 5. responder004 performed Power attack using [q(W=8):8] against [(6):5]; Defender (6) was captured; Attacker q(W=8) rerolled 8 => 7
     *    responder003 passed
     * 6. responder004 performed Power attack using [q(Z=28):22] against [(X=5):3]; Defender (X=5) was captured; Attacker q(Z=28) rerolled 22 => 17
     *    responder003 passed
     *    responder004 passed
     *    End of round: responder004 won round 1 (51 vs. 4)
     * 7. responder003 added a reserve die: r(20)
     * 8. responder003 set swing values: X=20
     *    responder003 won initiative for round 2. Initial die values: responder003 rolled [(4):4, (6):2, (12):4, (X=20):7, (20):8], responder004 rolled [q(T=2):2, q(W=8):5, q(X=20):20, q(Z=28):12].
     * 9. responder003 performed Skill attack using [(12):4,(20):8] against [q(Z=28):12]; Defender q(Z=28) was captured; Attacker (12) rerolled 4 => 6; Attacker (20) rerolled 8 => 5
     * 10. responder004 performed Power attack using [q(T=2):2] against [(6):2]; Defender (6) was captured; Attacker q(T=2) rerolled 2 => 2
     * 11. responder003 performed Power attack using [(20):5] against [q(W=8):5]; Defender q(W=8) was captured; Attacker (20) rerolled 5 => 18
     * 12. responder004 performed Power attack using [q(X=20):20] against [(12):6]; Defender (12) was captured; Attacker q(X=20) rerolled 20 => 16
     * 13. responder003 performed Power attack using [(20):18] against [q(X=20):16]; Defender q(X=20) was captured; Attacker (20) rerolled 18 => 8
     *     responder004 passed
     * 14. responder003 performed Power attack using [(X=20):7] against [q(T=2):2]; Defender q(T=2) was captured; Attacker (X=20) rerolled 7 => 20
     * 15. End of round: responder003 won round 2 (80 vs. 18)
     * 16. responder004 added a reserve die: rz(S)
     * 17. responder004 set swing values: S=20, T=2, W=4, X=4, Z=4
     *     responder004 won initiative for round 3. Initial die values: responder003 rolled [(4):2, (6):5, (12):1, (X=20):12, (20):8], responder004 rolled [q(T=2):2, q(W=4):4, q(X=4):1, q(Z=4):1, z(S=20):4].
     * 18. responder004 performed Skill attack using [q(T=2):2,q(W=4):4,q(X=4):1,q(Z=4):1,z(S=20):4] against [(X=20):12]; Defender (X=20) was captured; Attacker q(T=2) rerolled 2 => 1; Attacker q(W=4) rerolled 4 => 4; Attacker q(X=4) rerolled 1 => 3; Attacker q(Z=4) rerolled 1 => 1; Attacker z(S=20) rerolled 4 => 10
     * 19. responder003 performed Power attack using [(12):1] against [q(Z=4):1]; Defender q(Z=4) was captured; Attacker (12) rerolled 1 => 12
     * 20. responder004 performed Speed attack using [z(S=20):10] against [(4):2,(20):8]; Defender (4) was captured; Defender (20) was captured; Attacker z(S=20) rerolled 10 => 12
     *     responder003 surrendered
     *     End of round: responder004 won round 3 because opponent surrendered
     * 21. responder003 added a reserve die: r(10)
     * 22. responder003 set swing values: X=20
     *     responder003 won initiative for round 4. Initial die values: responder003 rolled [(4):1, (6):4, (12):4, (X=20):10, (10):5, (20):17], responder004 rolled [q(T=2):2, q(W=4):2, q(X=4):2, q(Z=4):4, z(S=20):12].
     * 23. responder003 performed Power attack using [(10):5] against [q(Z=4):4]; Defender q(Z=4) was captured; Attacker (10) rerolled 5 => 1
     * 24. responder004 performed Speed attack using [z(S=20):12] against [(4):1,(X=20):10,(10):1]; Defender (4) was captured; Defender (X=20) was captured; Defender (10) was captured; Attacker z(S=20) rerolled 12 => 1
     * 25. responder003 performed Power attack using [(20):17] against [q(T=2):2]; Defender q(T=2) was captured; Attacker (20) rerolled 17 => 14
     * 26. responder004 performed Skill attack using [q(W=4):2,q(X=4):2] against [(12):4]; Defender (12) was captured; Attacker q(W=4) rerolled 2 => 1; Attacker q(X=4) rerolled 2 => 4
     * 27. responder003 performed Power attack using [(6):4] against [q(W=4):1]; Defender q(W=4) was captured; Attacker (6) rerolled 4 => 2
     * 28. responder004 performed Power attack using [q(X=4):4] against [(6):2]; Defender (6) was captured; Attacker q(X=4) rerolled 4 => 1
     * 29. responder003 surrendered
     * 30. End of round: responder004 won round 4 because opponent surrendered
     */
    public function test_interface_game_010() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 10;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Three of Washu's dice, and none of Hooloovoo's, are rolled initially
        $gameId = $this->verify_api_createGame(
            array(3, 1, 5),
            'responder003', 'responder004', 'Washu', 'Hooloovoo', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Focus', 'Null', 'Poison', 'Queer', 'Reserve', 'Speed'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('T' => array(2, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Washu', 'recipe' => '(4) (6) (12) (X) r(6) r(8) r(10) r(20)', 'originalRecipe' => '(4) (6) (12) (X) r(6) r(8) r(10) r(20)', 'artFilename' => 'washu.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Hooloovoo', 'recipe' => 'q(T) q(W) q(X) q(Z) rn(R) rz(S) rp(U) rf(V)', 'originalRecipe' => 'q(T) q(W) q(X) q(Z) rn(R) rz(S) rp(U) rf(V)', 'artFilename' => 'hooloovoo.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(T)', 'description' => 'Queer T Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(W)', 'description' => 'Queer W Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(X)', 'description' => 'Queer X Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(Z)', 'description' => 'Queer Z Swing Die'),
        );

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder004 set swing values: T=2, W=8, X=20, Z=28
        // responder004's swing dice are rolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(1, 8, 1, 22),
            $gameId, 1, array('T' => 2, 'W' => 8, 'X' => 20, 'Z' => 28), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set die sizes'));
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 set swing values: X=5
        // responder003's swing die is rolled
        $this->verify_api_submitDieValues(
            array(3),
            $gameId, 1, array('X' => 5), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['roundScore'] = 13.5;
        $expData['playerDataArray'][1]['roundScore'] = 29;
        $expData['playerDataArray'][0]['sideScore'] = -10.3;
        $expData['playerDataArray'][1]['sideScore'] = 10.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] .= ' (with 5 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 22;
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] .= ' (with 2 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] .= ' (with 8 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] .= ' (with 20 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 28;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] .= ' (with 28 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['gameActionLog'][0]['message'] = 'responder004 set swing values: T=2, W=8, X=20, Z=28';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=5'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [(4):3, (6):1, (12):5, (X=5):3], responder004 rolled [q(T=2):1, q(W=8):8, q(X=20):1, q(Z=28):22].'));
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 performed Shadow attack using [q(X=20):1] against [(12):5]
        // [(4):3, (6):1, (12):5, (X=5):3] <= [q(T=2):1, q(W=8):8, q(X=20):1, q(Z=28):22]
        // verify simple Default Shadow attack with a die which has the Queer skill
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(17),
            'responder004 performed Shadow attack using [q(X=20):1] against [(12):5]; Defender (12) was captured; Attacker q(X=20) rerolled 1 => 17. ',
            $retval, array(array(0, 2), array(1, 2)),
            $gameId, 1, 'Default', 1, 0, "This is my first comment.\n    Ceci n'est pas une <script>tag<\/script>");
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 7.5;
        $expData['playerDataArray'][1]['roundScore'] = 41;
        $expData['playerDataArray'][0]['sideScore'] = -22.3;
        $expData['playerDataArray'][1]['sideScore'] = 22.3;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 5, 'sides' => 12, 'properties' => array('WasJustCaptured'), 'recipe' => '(12)');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 17;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Shadow attack using [q(X=20):1] against [(12):5]; Defender (12) was captured; Attacker q(X=20) rerolled 1 => 17'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => "This is my first comment.\n    Ceci n'est pas une <script>tag<\/script>"));
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Power attack using [(6):1] against [q(T=2):1]
        // [(4):3, (6):1, (X=5):3] => [q(T=2):1, q(W=8):8, q(X=20):17, q(Z=28):22]
        $this->verify_api_submitTurn(
            array(5),
            'responder003 performed Power attack using [(6):1] against [q(T=2):1]; Defender q(T=2) was captured; Attacker (6) rerolled 1 => 5. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, 'This is [b]my[/b] first comment');

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['gameChatEditable'] = 'TIMESTAMP';
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['roundScore'] = 9.5;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -20.3;
        $expData['playerDataArray'][1]['sideScore'] = 20.3;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 1, 'sides' => 2, 'properties' => array('WasJustCaptured'), 'recipe' => 'q(T)');
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 5;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(6):1] against [q(T=2):1]; Defender q(T=2) was captured; Attacker (6) rerolled 1 => 5'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'This is [b]my[/b] first comment'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder004 performed Power attack using [q(W=8):8] against [(6):5] (responder003 passed)
        // [(4):3, (6):5, (X=5):3] <= [q(W=8):8, q(X=20):17, q(Z=28):22]
        // verify simple Default Power attack using a die which has the Queer skill
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(7),
            'responder004 performed Power attack using [q(W=8):8] against [(6):5]; Defender (6) was captured; Attacker q(W=8) rerolled 8 => 7. responder003 passed. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Default', 1, 0, 'This is my second comment');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameChatEditable'] = FALSE;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['roundScore'] = 6.5;
        $expData['playerDataArray'][1]['roundScore'] = 46;
        $expData['playerDataArray'][0]['sideScore'] = -26.3;
        $expData['playerDataArray'][1]['sideScore'] = 26.3;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 5, 'sides' => 6, 'properties' => array(), 'recipe' => '(6)');
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 7;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [q(W=8):8] against [(6):5]; Defender (6) was captured; Attacker q(W=8) rerolled 8 => 7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'This is my second comment'));
        $expData['gameActionLogCount'] += 2;
        $expData['gameChatLogCount'] = 3;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder004 performed Power attack using [q(Z=28):22] against [(X=5):3] (all pass, responder004 wins round 1)
        // [(4):3, (X=5):3] <= [q(W=8):7, q(X=20):17, q(Z=28):22]
        // Initial die rolls for the next round happen after reserve dice are chosen.
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(17),
            'responder004 performed Power attack using [q(Z=28):22] against [(X=5):3]; Defender (X=5) was captured; Attacker q(Z=28) rerolled 22 => 17. responder003 passed. responder004 passed. End of round: responder004 won round 1 (51 vs. 4). ',
            $retval, array(array(0, 1), array(1, 2)),
            $gameId, 1, 'Power', 1, 0, 'This is my third comment');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'CHOOSE_RESERVE_DICE';
        $expData['roundNumber'] = 2;
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array('X' => 5);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array('T' => 2, 'W' => 8, 'X' => 20, 'Z' => 28);
        $expData['playerDataArray'][1]['swingRequestArray'] = array('T' => array(2, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30), 'R' => array(2, 16), 'S' => array(6, 20), 'U' => array(8, 30), 'V' => array(6, 12));
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(6)', 'description' => 'Reserve 6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(8)', 'description' => 'Reserve 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(10)', 'description' => 'Reserve 10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(20)', 'description' => 'Reserve 20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 2, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(T)', 'description' => 'Queer T Swing Die (with 2 sides)'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(W)', 'description' => 'Queer W Swing Die (with 8 sides)'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(X)', 'description' => 'Queer X Swing Die (with 20 sides)'),
            array('value' => NULL, 'sides' => 28, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(Z)', 'description' => 'Queer Z Swing Die (with 28 sides)'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Null'), 'properties' => array(), 'recipe' => 'rn(R)', 'description' => 'Reserve Null R Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Speed'), 'properties' => array(), 'recipe' => 'rz(S)', 'description' => 'Reserve Speed S Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Poison'), 'properties' => array(), 'recipe' => 'rp(U)', 'description' => 'Reserve Poison U Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Focus'), 'properties' => array(), 'recipe' => 'rf(V)', 'description' => 'Reserve Focus V Swing Die'),
        );
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('R' => array(2, 16), 'S' => array(6, 20), 'T' => array(2, 12), 'U' => array(8, 30), 'V' => array(6, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [q(Z=28):22] against [(X=5):3]; Defender (X=5) was captured; Attacker q(Z=28) rerolled 22 => 17'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 1 (51 vs. 4)'));
        $cachedActionLog = array();
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'This is my third comment'));
        $expData['gameActionLogCount'] += 4;
        $expData['gameChatLogCount'] = 4;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - responder003 added a reserve die: r(20)
        // 4 of Washu's dice and 4 of Hooloovoo's roll now
        $this->verify_api_reactToReserve(
            array(4, 2, 4, 8, 2, 5, 20, 12),
            'responder003 added a reserve die: r(20). ',
            $gameId, 'add', 7);

        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['playerDataArray'][0]['button']['recipe'] = '(4) (6) (12) (X) r(6) r(8) r(10) (20)';
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 4, 4);
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 4);
        $expData['playerDataArray'][0]['activeDieArray'][] =
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die');
        $expData['playerDataArray'][1]['swingRequestArray'] = array('T' => array(2, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 added a reserve die: r(20)'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 08 - responder003 set swing values: X=20
        $this->verify_api_submitDieValues(
            array(7),
            $gameId, 2, array('X' => 20), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 31;
        $expData['playerDataArray'][1]['roundScore'] = 29;
        $expData['playerDataArray'][0]['sideScore'] = 1.3;
        $expData['playerDataArray'][1]['sideScore'] = -1.3;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] .= ' (with 20 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=20'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [(4):4, (6):2, (12):4, (X=20):7, (20):8], responder004 rolled [q(T=2):2, q(W=8):5, q(X=20):20, q(Z=28):12].'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 09 - responder003 performed Skill attack using [(12):4,(20):8] against [q(Z=28):12]
        // [(4):4, (6):2, (12):4, (X=20):7, (20):8] => [q(T=2):2, q(W=8):5, q(X=20):20, q(Z=28):12]
        $this->verify_api_submitTurn(
            array(6, 5),
            'responder003 performed Skill attack using [(12):4,(20):8] against [q(Z=28):12]; Defender q(Z=28) was captured; Attacker (12) rerolled 4 => 6; Attacker (20) rerolled 8 => 5. ',
            $retval, array(array(0, 2), array(0, 4), array(1, 3)),
            $gameId, 2, 'Skill', 0, 1, 'This is [b]my[/b] second comment');

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['gameChatEditable'] = 'TIMESTAMP';
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['roundScore'] = 59;
        $expData['playerDataArray'][1]['roundScore'] = 15;
        $expData['playerDataArray'][0]['sideScore'] = 29.3;
        $expData['playerDataArray'][1]['sideScore'] = -29.3;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 12, 'sides' => 28, 'properties' => array('WasJustCaptured'), 'recipe' => 'q(Z)');
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 5;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(12):4,(20):8] against [q(Z=28):12]; Defender q(Z=28) was captured; Attacker (12) rerolled 4 => 6; Attacker (20) rerolled 8 => 5'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'This is [b]my[/b] second comment'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 5;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 10 - responder004 performed Power attack using [q(T=2):2] against [(6):2]
        // [(4):4, (6):2, (12):6, (X=20):7, (20):5] <= [q(T=2):2, q(W=8):5, q(X=20):20]
        // verify Default Power attack in a one-on-one Power/Skill scenario with a die which has the Queer skill
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2),
            'responder004 performed Power attack using [q(T=2):2] against [(6):2]; Defender (6) was captured; Attacker q(T=2) rerolled 2 => 2. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 2, 'Default', 1, 0, 'This is my fourth comment');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['gameChatEditable'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 56;
        $expData['playerDataArray'][1]['roundScore'] = 21;
        $expData['playerDataArray'][0]['sideScore'] = 23.3;
        $expData['playerDataArray'][1]['sideScore'] = -23.3;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 2, 'sides' => 6, 'properties' => array('WasJustCaptured'), 'recipe' => '(6)');
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [q(T=2):2] against [(6):2]; Defender (6) was captured; Attacker q(T=2) rerolled 2 => 2'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'This is my fourth comment'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 6;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 11 - responder003 performed Power attack using [(20):5] against [q(W=8):5]
        // [(4):4, (12):6, (X=20):7, (20):5] => [q(T=2):2, q(W=8):5, q(X=20):20]
        $this->verify_api_submitTurn(
            array(18),
            'responder003 performed Power attack using [(20):5] against [q(W=8):5]; Defender q(W=8) was captured; Attacker (20) rerolled 5 => 18. ',
            $retval, array(array(0, 3), array(1, 1)),
            $gameId, 2, 'Power', 0, 1, 'This is [b]my[/b] third comment');

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['gameChatEditable'] = 'TIMESTAMP';
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['roundScore'] = 64;
        $expData['playerDataArray'][1]['roundScore'] = 17;
        $expData['playerDataArray'][0]['sideScore'] = 31.3;
        $expData['playerDataArray'][1]['sideScore'] = -31.3;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 5, 'sides' => 8, 'properties' => array('WasJustCaptured'), 'recipe' => 'q(W)');
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 18;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(20):5] against [q(W=8):5]; Defender q(W=8) was captured; Attacker (20) rerolled 5 => 18'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'This is [b]my[/b] third comment'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 7;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 12 - responder004 performed Power attack using [q(X=20):20] against [(12):6]
        // [(4):4, (12):6, (X=20):7, (20):18] <= [q(T=2):2, q(X=20):20]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(16),
            'responder004 performed Power attack using [q(X=20):20] against [(12):6]; Defender (12) was captured; Attacker q(X=20) rerolled 20 => 16. ',
            $retval, array(array(0, 1), array(1, 1)),
            $gameId, 2, 'Power', 1, 0, 'This is my fifth comment');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['gameChatEditable'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['roundScore'] = 58;
        $expData['playerDataArray'][1]['roundScore'] = 29;
        $expData['playerDataArray'][0]['sideScore'] = 19.3;
        $expData['playerDataArray'][1]['sideScore'] = -19.3;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 6, 'sides' => 12, 'properties' => array('WasJustCaptured'), 'recipe' => '(12)');
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 16;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [q(X=20):20] against [(12):6]; Defender (12) was captured; Attacker q(X=20) rerolled 20 => 16'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'This is my fifth comment'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 8;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 13 - responder003 performed Power attack using [(20):18] against [q(X=20):16] (responder004 passed)
        // [(4):4, (X=20):7, (20):18] => [q(T=2):2, q(X=20):16]
        $this->verify_api_submitTurn(
            array(8),
            'responder003 performed Power attack using [(20):18] against [q(X=20):16]; Defender q(X=20) was captured; Attacker (20) rerolled 18 => 8. responder004 passed. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 2, 'Power', 0, 1, 'This is [b]my[/b] fourth comment');

        $expData['gameChatEditable'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 78;
        $expData['playerDataArray'][1]['roundScore'] = 19;
        $expData['playerDataArray'][0]['sideScore'] = 39.3;
        $expData['playerDataArray'][1]['sideScore'] = -39.3;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 16, 'sides' => 20, 'properties' => array(), 'recipe' => 'q(X)');
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(20):18] against [q(X=20):16]; Defender q(X=20) was captured; Attacker (20) rerolled 18 => 8'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'This is [b]my[/b] fourth comment'));
        $expData['gameActionLogCount'] += 2;
        $expData['gameChatLogCount'] = 9;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // responder004 adds some chat without taking a turn
        // #1477: responder004 chatting here is a test hack --- otherwise, the next
        // few turns will intermittently fail depending on whether the test timing crosses a second boundary
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitChat($gameId, 'This is my sixth comment', 'Added game message');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameChatEditable'] = FALSE;
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'This is my sixth comment'));
        $expData['gameChatLogCount'] = 10;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 14 - responder003 performed Power attack using [(X=20):7] against [q(T=2):2]
        // [(4):4, (X=20):7, (20):8] => [q(T=2):2]
        // FIXME: roll more dice
        $this->verify_api_submitTurn(
            array(20),
            'responder003 performed Power attack using [(X=20):7] against [q(T=2):2]; Defender q(T=2) was captured; Attacker (X=20) rerolled 7 => 20. End of round: responder003 won round 2 (80 vs. 18). ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 2, 'Power', 0, 1, '');

        $expData['gameState'] = 'CHOOSE_RESERVE_DICE';
        $expData['roundNumber'] = 3;
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array('X' => 20);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array('T' => 2, 'W' => 8, 'X' => 20, 'Z' => 28);
        $expData['playerDataArray'][1]['swingRequestArray'] = array('T' => array(2, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30), 'R' => array(2, 16), 'S' => array(6, 20), 'U' => array(8, 30), 'V' => array(6, 12));
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die (with 20 sides)'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(6)', 'description' => 'Reserve 6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(8)', 'description' => 'Reserve 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(10)', 'description' => 'Reserve 10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(T)', 'description' => 'Queer T Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(W)', 'description' => 'Queer W Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(X)', 'description' => 'Queer X Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(Z)', 'description' => 'Queer Z Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Null'), 'properties' => array(), 'recipe' => 'rn(R)', 'description' => 'Reserve Null R Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Speed'), 'properties' => array(), 'recipe' => 'rz(S)', 'description' => 'Reserve Speed S Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Poison'), 'properties' => array(), 'recipe' => 'rp(U)', 'description' => 'Reserve Poison U Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Focus'), 'properties' => array(), 'recipe' => 'rf(V)', 'description' => 'Reserve Focus V Swing Die'),
        );
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('R' => array(2, 16), 'S' => array(6, 20), 'T' => array(2, 12), 'U' => array(8, 30), 'V' => array(6, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(X=20):7] against [q(T=2):2]; Defender q(T=2) was captured; Attacker (X=20) rerolled 7 => 20'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: responder003 won round 2 (80 vs. 18)'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // now load the game as non-participating player responder001 and check its state
        $_SESSION = $this->mock_test_user_login('responder001');
        $expData['dieBackgroundType'] = 'symmetric';
        $this->verify_api_loadGameData_as_nonparticipant($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['dieBackgroundType'] = 'realistic';

        ////////////////////
        // Move 15 - responder004 added a reserve die: rz(S)
        // 5 of Washu's dice and 0 of Hooloovoo's roll
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_reactToReserve(
            array(2, 5, 1, 12, 8),
            'responder004 added a reserve die: rz(S). ',
            $gameId, 'add', 5);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['playerDataArray'][1]['button']['recipe'] = 'q(T) q(W) q(X) q(Z) rn(R) z(S) rp(U) rf(V)';
        $expData['playerDataArray'][1]['swingRequestArray'] = array('T' => array(2, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30), 'S' => array(6, 20));
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 4, 3);
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 4);
        $expData['playerDataArray'][1]['activeDieArray'][] =
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(S)', 'description' => 'Speed S Swing Die');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 added a reserve die: rz(S)'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 16 - responder004 set swing values: S=20, T=2, W=4, X=4, Z=4
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(2, 4, 1, 1, 4),
            $gameId, 3, array('T' => 2, 'W' => 4, 'X' => 4, 'Z' => 4, 'S' => 20), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['roundScore'] = 31;
        $expData['playerDataArray'][1]['roundScore'] = 17;
        $expData['playerDataArray'][0]['sideScore'] = 9.3;
        $expData['playerDataArray'][1]['sideScore'] = -9.3;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] .= ' (with 2 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] .= ' (with 20 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: S=20, T=2, W=4, X=4, Z=4'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 3. Initial die values: responder003 rolled [(4):2, (6):5, (12):1, (X=20):12, (20):8], responder004 rolled [q(T=2):2, q(W=4):4, q(X=4):1, q(Z=4):1, z(S=20):4].'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 17 - responder004 performed Skill attack using [q(T=2):2,q(W=4):4,q(X=4):1,q(Z=4):1,z(S=20):4] against [(X=20):12]
        // [(4):2, (6):5, (12):1, (X=20):12, (20):8] <= [q(T=2):2, q(W=4):4, q(X=4):1, q(Z=4):1, z(S=20):4]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1, 4, 3, 1, 10),
            'responder004 performed Skill attack using [q(T=2):2,q(W=4):4,q(X=4):1,q(Z=4):1,z(S=20):4] against [(X=20):12]; Defender (X=20) was captured; Attacker q(T=2) rerolled 2 => 1; Attacker q(W=4) rerolled 4 => 4; Attacker q(X=4) rerolled 1 => 3; Attacker q(Z=4) rerolled 1 => 1; Attacker z(S=20) rerolled 4 => 10. ',
            $retval, array(array(0, 3), array(1, 0), array(1, 1), array(1, 2), array(1, 3), array(1, 4)),
            $gameId, 3, 'Skill', 1, 0, 'This is my seventh comment');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['gameChatEditable'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 21;
        $expData['playerDataArray'][1]['roundScore'] = 37;
        $expData['playerDataArray'][0]['sideScore'] = -10.7;
        $expData['playerDataArray'][1]['sideScore'] = 10.7;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 12, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(X)');
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 10;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [q(T=2):2,q(W=4):4,q(X=4):1,q(Z=4):1,z(S=20):4] against [(X=20):12]; Defender (X=20) was captured; Attacker q(T=2) rerolled 2 => 1; Attacker q(W=4) rerolled 4 => 4; Attacker q(X=4) rerolled 1 => 3; Attacker q(Z=4) rerolled 1 => 1; Attacker z(S=20) rerolled 4 => 10'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'This is my seventh comment'));
        $cachedChatLog = array();
        $cachedChatLog[] = array_pop($expData['gameChatLog']);
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 11;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 18 - responder003 performed Power attack using [(12):1] against [q(Z=4):1]
        // [(4):2, (6):5, (12):1, (20):8] => [q(T=2):1, q(W=4):4, q(X=4):3, q(Z=4):1, z(S=20):10]
        $this->verify_api_submitTurn(
            array(12),
            'responder003 performed Power attack using [(12):1] against [q(Z=4):1]; Defender q(Z=4) was captured; Attacker (12) rerolled 1 => 12. ',
            $retval, array(array(0, 2), array(1, 3)),
            $gameId, 3, 'Power', 0, 1, 'This is [b]my[/b] sixth comment');

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['gameChatEditable'] = 'TIMESTAMP';
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow', 'Speed');
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][1]['roundScore'] = 35;
        $expData['playerDataArray'][0]['sideScore'] = -6.7;
        $expData['playerDataArray'][1]['sideScore'] = 6.7;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 1, 'sides' => 4, 'properties' => array('WasJustCaptured'), 'recipe' => 'q(Z)');
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 12;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(12):1] against [q(Z=4):1]; Defender q(Z=4) was captured; Attacker (12) rerolled 1 => 12'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'This is [b]my[/b] sixth comment'));
        $cachedChatLog[] = array_pop($expData['gameChatLog']);
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 12;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 19 - responder004 performed Speed attack using [z(S=20):10] against [(4):2,(20):8]; Defender (4) was captured; Defender (20) was captured; Attacker z(S=20) rerolled 10 => 12
        // [(4):2, (6):5, (12):12, (20):8] <= [q(T=2):1, q(W=4):4, q(X=4):3, z(S=20):10]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(12),
            'responder004 performed Speed attack using [z(S=20):10] against [(4):2,(20):8]; Defender (4) was captured; Defender (20) was captured; Attacker z(S=20) rerolled 10 => 12. ',
            $retval, array(array(0, 0), array(0, 3), array(1, 3)),
            $gameId, 3, 'Speed', 1, 0, 'This is my eighth comment');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['gameChatEditable'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 13;
        $expData['playerDataArray'][1]['roundScore'] = 59;
        $expData['playerDataArray'][0]['sideScore'] = -30.7;
        $expData['playerDataArray'][1]['sideScore'] = 30.7;
        $expData['playerDataArray'][0]['canStillWin'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 3, 1);
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 2, 'sides' => 4, 'properties' => array('WasJustCaptured'), 'recipe' => '(4)');
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 8, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 12;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Speed attack using [z(S=20):10] against [(4):2,(20):8]; Defender (4) was captured; Defender (20) was captured; Attacker z(S=20) rerolled 10 => 12'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'This is my eighth comment'));
        $cachedChatLog[] = array_pop($expData['gameChatLog']);
        $expData['gameActionLogCount'] += 1;
        $expData['gameChatLogCount'] = 13;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // load the same game without limiting action or chat logs,
        // both of which should contain over 10 entries now

        // first re-add each cached action log and chat log entry to the expected data array
        foreach(array_reverse($cachedActionLog) as $cachedEntry) {
            $expData['gameActionLog'][] = $cachedEntry;
        }
        foreach(array_reverse($cachedChatLog) as $cachedEntry) {
            $expData['gameChatLog'][] = $cachedEntry;
        }

        // now load the game with no length limit
        $retval = $this->verify_api_loadGameData($expData, $gameId, FALSE);

        // now remove each cached entry again
        foreach($cachedActionLog as $cachedEntry) {
            array_pop($expData['gameActionLog']);
        }
        foreach($cachedChatLog as $cachedEntry) {
            array_pop($expData['gameChatLog']);
        }


        ////////////////////
        // Move 20 - responder003 surrendered
        // [(6):5, (12):12] => [q(T=2):1, q(W=4):4, q(X=4):3, z(S=20):12]
        $this->verify_api_submitTurn(
            array(),
            'responder003 surrendered. End of round: responder004 won round 3 because opponent surrendered. ',
            $retval, array(),
            $gameId, 3, 'Surrender', 0, 1, '');

        $expData['gameState'] = 'CHOOSE_RESERVE_DICE';
        $expData['roundNumber'] = 4;
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 2;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 2;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array('X' => 20);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array('T' => 2, 'W' => 4, 'X' => 4, 'Z' => 4, 'S' => 20);
        $expData['playerDataArray'][1]['swingRequestArray'] = array('T' => array(2, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30), 'R' => array(2, 16), 'S' => array(6, 20), 'U' => array(8, 30), 'V' => array(6, 12));
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(6)', 'description' => 'Reserve 6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(8)', 'description' => 'Reserve 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Reserve'), 'properties' => array(), 'recipe' => 'r(10)', 'description' => 'Reserve 10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 2, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(T)', 'description' => 'Queer T Swing Die (with 2 sides)'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(W)', 'description' => 'Queer W Swing Die (with 4 sides)'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(X)', 'description' => 'Queer X Swing Die (with 4 sides)'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(Z)', 'description' => 'Queer Z Swing Die (with 4 sides)'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Null'), 'properties' => array(), 'recipe' => 'rn(R)', 'description' => 'Reserve Null R Swing Die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(S)', 'description' => 'Speed S Swing Die (with 20 sides)'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Poison'), 'properties' => array(), 'recipe' => 'rp(U)', 'description' => 'Reserve Poison U Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Reserve', 'Focus'), 'properties' => array(), 'recipe' => 'rf(V)', 'description' => 'Reserve Focus V Swing Die'),
        );
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('R' => array(2, 16), 'S' => array(6, 20), 'T' => array(2, 12), 'U' => array(8, 30), 'V' => array(6, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 surrendered'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 3 because opponent surrendered'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 21 - responder003 added a reserve die: r(10)
        // 5 of Washu's dice and 5 of Hooloovoo's roll
        $this->verify_api_reactToReserve(
            array(1, 4, 4, 5, 17, 2, 2, 2, 4, 12),
            'responder003 added a reserve die: r(10). ',
            $gameId, 'add', 6);

        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['playerDataArray'][0]['button']['recipe'] = '(4) (6) (12) (X) r(6) r(8) (10) (20)';
        $expData['playerDataArray'][1]['swingRequestArray'] = array('T' => array(2, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Z' => array(4, 30), 'S' => array(6, 20));
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 4, 3);
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 6, 2);
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 1);
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 4, 0,
            array(array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die')));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 added a reserve die: r(10)'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 22 - responder003 set swing values: X=20
        $this->verify_api_submitDieValues(
            array(10),
            $gameId, 4, array('X' => 20), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 36;
        $expData['playerDataArray'][1]['roundScore'] = 17;
        $expData['playerDataArray'][0]['sideScore'] = 12.7;
        $expData['playerDataArray'][1]['sideScore'] = -12.7;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 17;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] .= ' (with 20 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=20'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 4. Initial die values: responder003 rolled [(4):1, (6):4, (12):4, (X=20):10, (10):5, (20):17], responder004 rolled [q(T=2):2, q(W=4):2, q(X=4):2, q(Z=4):4, z(S=20):12].'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 23 - responder003 performed Power attack using [(10):5] against [q(Z=4):4]
        // [(4):1, (6):4, (12):4, (X=20):10, (10):5, (20):17] => [q(T=2):2, q(W=4):2, q(X=4):2, q(Z=4):4, z(S=20):12]
        $this->verify_api_submitTurn(
            array(1),
            'responder003 performed Power attack using [(10):5] against [q(Z=4):4]; Defender q(Z=4) was captured; Attacker (10) rerolled 5 => 1. ',
            $retval, array(array(0, 4), array(1, 3)),
            $gameId, 4, 'Power', 0, 1, '');

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Speed');
        $expData['playerDataArray'][0]['roundScore'] = 40;
        $expData['playerDataArray'][1]['roundScore'] = 15;
        $expData['playerDataArray'][0]['sideScore'] = 16.7;
        $expData['playerDataArray'][1]['sideScore'] = -16.7;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 4, 'sides' => 4, 'properties' => array('WasJustCaptured'), 'recipe' => 'q(Z)');
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(10):5] against [q(Z=4):4]; Defender q(Z=4) was captured; Attacker (10) rerolled 5 => 1'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 24 - responder004 performed Speed attack using [z(S=20):12] against [(4):1,(X=20):10,(10):1]
        // [(4):1, (6):4, (12):4, (X=20):10, (10):1, (20):17] <= [q(T=2):2, q(W=4):2, q(X=4):2, z(S=20):12]
        // verify simple Default Speed attack
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Speed attack using [z(S=20):12] against [(4):1,(X=20):10,(10):1]; Defender (4) was captured; Defender (X=20) was captured; Defender (10) was captured; Attacker z(S=20) rerolled 12 => 1. ',
            $retval, array(array(0, 0), array(0, 3), array(0, 4), array(1, 3)),
            $gameId, 4, 'Default', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['roundScore'] = 23;
        $expData['playerDataArray'][1]['roundScore'] = 49;
        $expData['playerDataArray'][0]['sideScore'] = -17.3;
        $expData['playerDataArray'][1]['sideScore'] = 17.3;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 4, 1);
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 3, 1);
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 1, 'sides' => 4, 'properties' => array('WasJustCaptured'), 'recipe' => '(4)');
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 10, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(X)');
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 1, 'sides' => 10, 'properties' => array('WasJustCaptured'), 'recipe' => '(10)');
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Speed attack using [z(S=20):12] against [(4):1,(X=20):10,(10):1]; Defender (4) was captured; Defender (X=20) was captured; Defender (10) was captured; Attacker z(S=20) rerolled 12 => 1'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 25 - responder003 performed Power attack using [(20):17] against [q(T=2):2]
        // [(6):4, (12):4, (20):17] => [q(T=2):2, q(W=4):2, q(X=4):2, z(S=20):1]
        $this->verify_api_submitTurn(
            array(14),
            'responder003 performed Power attack using [(20):17] against [q(T=2):2]; Defender q(T=2) was captured; Attacker (20) rerolled 17 => 14. ',
            $retval, array(array(0, 2), array(1, 0)),
            $gameId, 4, 'Power', 0, 1, '');

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Skill');
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][1]['roundScore'] = 48;
        $expData['playerDataArray'][0]['sideScore'] = -15.3;
        $expData['playerDataArray'][1]['sideScore'] = 15.3;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 2, 'sides' => 2, 'properties' => array('WasJustCaptured'), 'recipe' => 'q(T)');
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 14;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(20):17] against [q(T=2):2]; Defender q(T=2) was captured; Attacker (20) rerolled 17 => 14'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 26 - responder004 performed Skill attack using [q(W=4):2,q(X=4):2] against [(12):4]
        // [(6):4, (12):4, (20):14] <= [q(W=4):2, q(X=4):2, z(S=20):1]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1, 4),
            'responder004 performed Skill attack using [q(W=4):2,q(X=4):2] against [(12):4]; Defender (12) was captured; Attacker q(W=4) rerolled 2 => 1; Attacker q(X=4) rerolled 2 => 4. ',
            $retval, array(array(0, 1), array(1, 0), array(1, 1)),
            $gameId, 4, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 19;
        $expData['playerDataArray'][1]['roundScore'] = 60;
        $expData['playerDataArray'][0]['sideScore'] = -27.3;
        $expData['playerDataArray'][1]['sideScore'] = 27.3;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 4, 'sides' => 12, 'properties' => array('WasJustCaptured'), 'recipe' => '(12)');
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [q(W=4):2,q(X=4):2] against [(12):4]; Defender (12) was captured; Attacker q(W=4) rerolled 2 => 1; Attacker q(X=4) rerolled 2 => 4'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 27 - responder003 performed Power attack using [(6):4] against [q(W=4):1]
        // [(6):4, (20):14] => [q(W=4):1, q(X=4):4, z(S=20):1]
        $this->verify_api_submitTurn(
            array(2),
            'responder003 performed Power attack using [(6):4] against [q(W=4):1]; Defender q(W=4) was captured; Attacker (6) rerolled 4 => 2. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 4, 'Power', 0, 1, '');

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['roundScore'] = 23;
        $expData['playerDataArray'][1]['roundScore'] = 58;
        $expData['playerDataArray'][0]['sideScore'] = -23.3;
        $expData['playerDataArray'][1]['sideScore'] = 23.3;
        $expData['playerDataArray'][1]['capturedDieArray'][3]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 1, 'sides' => 4, 'properties' => array('WasJustCaptured'), 'recipe' => 'q(W)');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(6):4] against [q(W=4):1]; Defender q(W=4) was captured; Attacker (6) rerolled 4 => 2'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 28 - responder004 performed Power attack using [q(X=4):4] against [(6):2]
        // [(6):2, (20):14] <= [q(X=4):4, z(S=20):1]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Power attack using [q(X=4):4] against [(6):2]; Defender (6) was captured; Attacker q(X=4) rerolled 4 => 1. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 4, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['roundScore'] = 20;
        $expData['playerDataArray'][1]['roundScore'] = 64;
        $expData['playerDataArray'][0]['sideScore'] = -29.3;
        $expData['playerDataArray'][1]['sideScore'] = 29.3;
        $expData['playerDataArray'][0]['canStillWin'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 2, 'sides' => 6, 'properties' => array('WasJustCaptured'), 'recipe' => '(6)');
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [q(X=4):4] against [(6):2]; Defender (6) was captured; Attacker q(X=4) rerolled 4 => 1'));
        $cachedActionLog[] = array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 29 - responder003 surrendered (end of game)
        // [(20):14] => [q(X=4):1, z(S=20):1]
        $this->verify_api_submitTurn(
            array(),
            'responder003 surrendered. End of round: responder004 won round 4 because opponent surrendered. ',
            $retval, array(),
            $gameId, 4, 'Surrender', 0, 1, '');

        $expData['gameState'] = 'END_GAME';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 0;
        $expData['playerDataArray'][1]['roundScore'] = 0;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 3;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 3;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 surrendered'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 4 because opponent surrendered'));
        foreach(array_reverse($cachedActionLog) as $cachedEntry) {
            $expData['gameActionLog'][] = $cachedEntry;
        }
        foreach(array_reverse($cachedChatLog) as $cachedEntry) {
            $expData['gameChatLog'][] = $cachedEntry;
        }
        $expData['gameActionLogCount'] += 2;

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test tests twin swing dice, berserk swing dice, and incorrect swing die setting
     *
     * 0. Start a game with responder003 playing Buck Godot and responder004 playing The GM
     * 1. responder004 set swing values: U=30
     * 2. responder003 (unsuccessfully) sets swing values: X=4 (responder003 has W swing dice)
     * 3. responder003 (unsuccessfully) sets swing values: X=4, W=4 (responder003 has only W swing dice)
     * 4. responder003 (unsuccessfully) sets swing values: W=13 (W swing dice only go up to 12)
     * 5. responder003 set swing values: W=7
     *    responder004 won initiative for round 1. Initial die values: responder003 rolled [(6,6):7, (10):4, (12):3, (20):18, (W=7,W=7):4], responder004 rolled [(4):1, (8):7, (12):7, (16):15, B(U=30):29].
     * 6. responder004 performed Berserk attack using [B(U=30):29] against [(10):4,(12):3,(20):18,(W=7,W=7):4]; Defender (10) was captured; Defender (12) was captured; Defender (20) was captured; Defender (W=7,W=7) was captured; Attacker B(U=30) changed size from 30 to 15 sides, recipe changed from B(U=30) to (15), rerolled 29 => 5
     * 7. responder003 performed Power attack using [(6,6):7] against [(15):5]; Defender (15) was captured; Attacker (6,6) rerolled 7 => 9
     * 8. responder004 performed Power attack using [(16):15] against [(6,6):9]; Defender (6,6) was captured; Attacker (16) rerolled 15 => 7
     *    End of round: responder004 won round 1 (88 vs. 15)
     * 9. responder003 set swing values: W=6
     *    responder004 won initiative for round 2. Initial die values: responder003 rolled [(6,6):8, (10):10, (12):8, (20):1, (W=6,W=6):11], responder004 rolled [(4):3, (8):5, (12):5, (16):1, B(U=30):3].
     */
    public function test_interface_game_011() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 11;
        $_SESSION = $this->mock_test_user_login('responder003');

        ////////////////////
        // initial game setup
        // 4 of Buck Godot's dice (5 rolls, since 1 is twin), and 4 of The GM's, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(6, 1, 4, 3, 18, 1, 7, 7, 15),
            'responder003', 'responder004', 'Buck Godot', 'The GM', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Berserk'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('W' => array(4, 12));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('U' => array(8, 30));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Buck Godot', 'recipe' => '(6,6) (10) (12) (20) (W,W)', 'originalRecipe' => '(6,6) (10) (12) (20) (W,W)', 'artFilename' => 'buckgodot.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'The GM', 'recipe' => '(4) (8) (12) (16) B(U)', 'originalRecipe' => '(4) (8) (12) (16) B(U)', 'artFilename' => 'thegm.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(6,6)', 'description' => 'Twin Die (both with 6 sides)', 'subdieArray' => array(array('sides' => 6), array('sides' => 6))),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(W,W)', 'description' => 'Twin W Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => 16, 'skills' => array(), 'properties' => array(), 'recipe' => '(16)', 'description' => '16-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(U)', 'description' => 'Berserk U Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder004 set swing values: U=30
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(29),
            $gameId, 1, array('U' => 30), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set die sizes'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 (unsuccessfully) sets swing values: X=4 (responder003 has W swing dice)
        $this->verify_api_failure(
            array(
                'type' => 'submitDieValues', 'game' => $gameId, 'roundNumber' => 1,
                'swingValueArray' => array('X' => 4),
                // BUG: this argument will no longer be needed when #1275 is fixed
                'timestamp' => 1234567890,
            ), 'Wrong swing values submitted: expected W'
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder003 (unsuccessfully) sets swing values: X=4, W=4 (responder003 has only W swing dice)
        $this->verify_api_failure(
            array(
                'type' => 'submitDieValues', 'game' => $gameId, 'roundNumber' => 1,
                'swingValueArray' => array('X' => 4, 'W' => 4),
                // BUG: this argument will no longer be needed when #1275 is fixed
                'timestamp' => 1234567890,
            ), 'Wrong swing values submitted: expected W'
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 (unsuccessfully) sets swing values: W=13 (W swing dice only go up to 12)
        $this->verify_api_failure(
            array(
                'type' => 'submitDieValues', 'game' => $gameId, 'roundNumber' => 1,
                'swingValueArray' => array('W' => 13),
                // BUG: this argument will no longer be needed when #1275 is fixed
                'timestamp' => 1234567890,
            ), 'Invalid value submitted for swing die (W,W)'
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder003 set swing values: W=7
        // responder003's twin (W,W) is rolled
        $this->verify_api_submitDieValues(
            array(2, 2),
            $gameId, 1, array('W' => 7), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Berserk');
        $expData['playerDataArray'][0]['roundScore'] = 34;
        $expData['playerDataArray'][1]['roundScore'] = 35;
        $expData['playerDataArray'][0]['sideScore'] = -0.7;
        $expData['playerDataArray'][1]['sideScore'] = 0.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['subdieArray'] = array(array('sides' => 6, 'value' => 6), array('sides' => 6, 'value' => 1));
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 18;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (both with 7 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['subdieArray'] = array(array('sides' => 7, 'value' => 2), array('sides' => 7, 'value' => 2));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 29;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 30;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] .= ' (with 30 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['gameActionLog'][0]['message'] = 'responder004 set swing values: U=30';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: W=7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [(6,6):7, (10):4, (12):3, (20):18, (W=7,W=7):4], responder004 rolled [(4):1, (8):7, (12):7, (16):15, B(U=30):29].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder004 performed Berserk attack using [B(U=30):29] against [(10):4,(12):3,(20):18,(W=7,W=7):4]; Defender (10) was captured; Defender (12) was captured; Defender (20) was captured; Defender (W=7,W=7) was captured; Attacker B(U=30) changed size from 30 to 15 sides, recipe changed from B(U=30) to (15), rerolled 29 => 5
        // [(6,6):7, (10):4, (12):3, (20):18, (W=7,W=7):4] <= [(4):1, (8):7, (12):7, (16):15, B(U=30):29]
        // verify simple Default Berserk attack
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(5),
            'responder004 performed Berserk attack using [B(U=30):29] against [(10):4,(12):3,(20):18,(W=7,W=7):4]; Defender (10) was captured; Defender (12) was captured; Defender (20) was captured; Defender (W=7,W=7) was captured; Attacker B(U=30) changed size from 30 to 15 sides, recipe changed from B(U=30) to (U=15), rerolled 29 => 5. ',
            $retval, array(array(0, 1), array(0, 2), array(0, 3), array(0, 4), array(1, 4)),
            $gameId, 1, 'Default', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 6.0;
        $expData['playerDataArray'][1]['roundScore'] = 83.5;
        $expData['playerDataArray'][0]['sideScore'] = -51.7;
        $expData['playerDataArray'][1]['sideScore'] = 51.7;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 4);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 4, 'sides' => 10, 'properties' => array('WasJustCaptured'), 'recipe' => '(10)');
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 3, 'sides' => 12, 'properties' => array('WasJustCaptured'), 'recipe' => '(12)');
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 18, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 4, 'sides' => 14, 'properties' => array('WasJustCaptured', 'Twin'), 'recipe' => '(W,W)', 'subdieArray' => array(array('sides' => 7, 'value' => 2), array('sides' => 7, 'value' => 2)));        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][4]['recipe'] = '(U)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = 'U Swing Die (with 15 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array('HasJustSplit', 'JustPerformedBerserkAttack');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Berserk attack using [B(U=30):29] against [(10):4,(12):3,(20):18,(W=7,W=7):4]; Defender (10) was captured; Defender (12) was captured; Defender (20) was captured; Defender (W=7,W=7) was captured; Attacker B(U=30) changed size from 30 to 15 sides, recipe changed from B(U=30) to (U=15), rerolled 29 => 5'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - responder003 performed Power attack using [(6,6):7] against [(15):5]
        // [(6,6):7] => [(4):1, (8):7, (12):7, (16):15, (15):5]
        $this->verify_api_submitTurn(
            array(3, 6),
            'responder003 performed Power attack using [(6,6):7] against [(U=15):5]; Defender (U=15) was captured; Attacker (6,6) rerolled 7 => 9. ',
            $retval, array(array(0, 0), array(1, 4)),
            $gameId, 1, 'Power', 0, 1, '');

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['roundScore'] = 21.0;
        $expData['playerDataArray'][1]['roundScore'] = 76.0;
        $expData['playerDataArray'][0]['sideScore'] = -36.7;
        $expData['playerDataArray'][1]['sideScore'] = 36.7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['subdieArray'] = array(array('sides' => 6, 'value' => 3), array('sides' => 6, 'value' => 6));
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][3]['properties'] = array('Twin');
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 5, 'sides' => 15, 'properties' => array('WasJustCaptured'), 'recipe' => '(U)');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 9;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(6,6):7] against [(U=15):5]; Defender (U=15) was captured; Attacker (6,6) rerolled 7 => 9'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 08 - responder004 performed Power attack using [(16):15] against [(6,6):9]
        // [(6,6):9] <= [(4):1, (8):7, (12):7, (16):15]
        // attacking die rerolls, then dice for the next round are rolled, 4 of Buck Godot's (including 1 twin), and 5 of The GM's
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(7, 5, 3, 10, 8, 1, 3, 5, 5, 1, 3),
            'responder004 performed Power attack using [(16):15] against [(6,6):9]; Defender (6,6) was captured; Attacker (16) rerolled 15 => 7. End of round: responder004 won round 1 (88 vs. 15). ',
            $retval, array(array(0, 0), array(1, 3)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['roundNumber'] = 2;
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array('W' => 7);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array('U' => 30);
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(6,6)', 'description' => 'Twin Die (both with 6 sides)', 'subdieArray' => array(array('sides' => 6), array('sides' => 6))),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(W,W)', 'description' => 'Twin W Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => 16, 'skills' => array(), 'properties' => array(), 'recipe' => '(16)', 'description' => '16-sided die'),
            array('value' => NULL, 'sides' => 30, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(U)', 'description' => 'Berserk U Swing Die (with 30 sides)'),
        );
        $expData['playerDataArray'][0]['swingRequestArray'] = array('W' => array(4, 12));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('U' => array(8, 30));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(16):15] against [(6,6):9]; Defender (6,6) was captured; Attacker (16) rerolled 15 => 7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 1 (88 vs. 15)'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 09 - responder003 set swing values: W=6
        // responder003's twin (W,W) is rolled
        $this->verify_api_submitDieValues(
            array(6, 5),
            $gameId, 2, array('W' => 6), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 33;
        $expData['playerDataArray'][1]['roundScore'] = 35;
        $expData['playerDataArray'][0]['sideScore'] = -1.3;
        $expData['playerDataArray'][1]['sideScore'] = 1.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][0]['subdieArray'] = array(array('sides' => 6, 'value' => 5), array('sides' => 6, 'value' => 3));
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('Twin');
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (both with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['subdieArray'] = array(array('sides' => 6, 'value' => 6), array('sides' => 6, 'value' => 5));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 3;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: W=6'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 2. Initial die values: responder003 rolled [(6,6):8, (10):10, (12):8, (20):1, (W=6,W=6):11], responder004 rolled [(4):3, (8):5, (12):5, (16):1, B(U=30):3].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test tests mighty and weak dice
     * 0. Start a game with responder003 playing The Tick and responder004 playing Famine
     * 1. responder004 set swing values: X=7
     *    responder004 won initiative for round 1. Initial die values: responder003 rolled [H(4,16):9, H(16):5, H(30):18, H(30):13], responder004 rolled [(6):5, (8):5, (10):4, (12,12):13, h(X=6):5].
     * 2. responder004 performed Power attack using [h(X=7):5] against [H(16):5]; Defender H(16) was captured; Attacker h(X=7) changed size from 7 to 6 sides, recipe changed from h(X=7) to h(X=6), rerolled 5 => 1
     * 3. responder003 performed Power attack using [H(4,16):9] against [(10):4]; Defender (10) was captured; Attacker H(4,16) changed size from 20 to 26 sides, recipe changed from H(4,16) to H(8,30), rerolled 9 => 14
     *
     */
    public function test_interface_game_012() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 12;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // 4 of The Tick's dice (5 rolls, since 1 is twin), and 4 of Famine's (5 rolls, since 1 is twin), are initially rolled
        $gameId = $this->verify_api_createGame(
            array(1, 8, 5, 18, 13, 5, 5, 4, 12, 1),
            'responder003', 'responder004', 'The Tick', 'Famine', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Mighty', 'Weak'));
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['button'] = array('name' => 'The Tick', 'recipe' => 'H(1,10) H(12) H(20) H(20)', 'originalRecipe' => 'H(1,10) H(12) H(20) H(20)', 'artFilename' => 'thetick.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Famine', 'recipe' => '(6) (8) (10) (12,12) h(X)', 'originalRecipe' => '(6) (8) (10) (12,12) h(X)', 'artFilename' => 'famine.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 11, 'skills' => array('Mighty'), 'properties' => array('Twin'), 'recipe' => 'H(1,10)', 'description' => 'Mighty Twin Die (with 1 and 10 sides)', 'subdieArray' => array(array('sides' => 1), array('sides' => 10))),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Mighty'), 'properties' => array(), 'recipe' => 'H(12)', 'description' => 'Mighty 12-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Mighty'), 'properties' => array(), 'recipe' => 'H(20)', 'description' => 'Mighty 20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Mighty'), 'properties' => array(), 'recipe' => 'H(20)', 'description' => 'Mighty 20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 24, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(12,12)', 'description' => 'Twin Die (both with 12 sides)', 'subdieArray' => array(array('sides' => 12), array('sides' => 12))),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Weak'), 'properties' => array(), 'recipe' => 'h(X)', 'description' => 'Weak X Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder004 set swing values: X=7
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(5),
            $gameId, 1, array('X' => 7), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 31.5;
        $expData['playerDataArray'][1]['roundScore'] = 27.5;
        $expData['playerDataArray'][0]['sideScore'] = 2.7;
        $expData['playerDataArray'][1]['sideScore'] = -2.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][0]['subdieArray'] = array(array('sides' => 1, 'value' => 1), array('sides' => 10, 'value' => 8));
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 18;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 13;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 13;
        $expData['playerDataArray'][1]['activeDieArray'][3]['subdieArray'] = array(array('sides' => 12, 'value' => 12), array('sides' => 12, 'value' => 1));
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] .= ' (with 7 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [H(1,10):9, H(12):5, H(20):18, H(20):13], responder004 rolled [(6):5, (8):5, (10):4, (12,12):13, h(X=7):5].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 performed Power attack using [h(X=7):5] against [H(12):5]
        // [H(1,10):9, H(12):5, H(20):18, H(20):13] <= [(6):5, (8):5, (10):4, (12,12):13, h(X=7):5]
        // verify Default Power attack in a one-on-one Power/Skill scenario with size-changing skills in play
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Power attack using [h(X=7):5] against [H(12):5]; Defender H(12) was captured; Attacker h(X=7) changed size from 7 to 6 sides, recipe changed from h(X=7) to h(X=6), rerolled 5 => 1. ',
            $retval, array(array(0, 1), array(1, 4)),
            $gameId, 1, 'Default', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 25.5;
        $expData['playerDataArray'][1]['roundScore'] = 39.0;
        $expData['playerDataArray'][0]['sideScore'] = -9.0;
        $expData['playerDataArray'][1]['sideScore'] = 9.0;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 5, 'sides' => 12, 'properties' => array('WasJustCaptured'), 'recipe' => 'H(12)');
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = 'Weak X Swing Die (with 6 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array('HasJustShrunk');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [h(X=7):5] against [H(12):5]; Defender H(12) was captured; Attacker h(X=7) changed size from 7 to 6 sides, recipe changed from h(X=7) to h(X=6), rerolled 5 => 1'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 03 - responder003 performed Power attack using [H(1,10):9] against [(10):4]
        // [H(1,10):9, H(20):18, H(20):13] => [(6):5, (8):5, (10):4, (12,12):13, h(X=6):1]
        $this->verify_api_submitTurn(
            array(2, 12),
            'responder003 performed Power attack using [H(1,10):9] against [(10):4]; Defender (10) was captured; Attacker H(1,10) changed size from 11 to 14 sides, recipe changed from H(1,10) to H(2,12), rerolled 9 => 14. ',
            $retval, array(array(0, 0), array(1, 2)),
            $gameId, 1, 'Power', 0, 1, '');

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 37.0;
        $expData['playerDataArray'][1]['roundScore'] = 34.0;
        $expData['playerDataArray'][0]['sideScore'] = 2.0;
        $expData['playerDataArray'][1]['sideScore'] = -2.0;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 4, 'sides' => 10, 'properties' => array('WasJustCaptured'), 'recipe' => '(10)');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][0]['subdieArray'] = array(array('sides' => 2, 'value' => 2), array('sides' => 12, 'value' => 12));
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = 'H(2,12)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = 'Mighty Twin Die (with 2 and 12 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('HasJustGrown', 'Twin');
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [H(1,10):9] against [(10):4]; Defender (10) was captured; Attacker H(1,10) changed size from 11 to 14 sides, recipe changed from H(1,10) to H(2,12), rerolled 9 => 14'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test reproduces a bug in which null trip dice cause their targets to become null on unsuccessful trip attack.
     *
     * 0. Start a game with responder003 playing wranklepig and responder004 playing Wiseman
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [pB(17):9, Fo(13):12, q(11):2, gc(7):2, nt(5):3], responder004 rolled [(20):9, (20):13, (20):20, (20):15]. responder003 has dice which are not counted for initiative due to die skills: [gc(7), nt(5)].
     * 1. responder003 performed Trip attack using [nt(5):3] against [(20):9]; Attacker nt(5) rerolled 3 => 4; Defender (20) rerolled 9 => 18, recipe changed from (20) to n(20), was not captured
     *    responder003's idle ornery dice rerolled at end of turn: Fo(13) rerolled 12 => 12
     */
    public function test_interface_game_013() {
        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 13;
        $_SESSION = $this->mock_test_user_login('responder003');

        ////////////////////
        // initial game setup
        // 5 of wranklepig's dice, and 4 of Wiseman's, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(9, 12, 2, 2, 3, 9, 13, 20, 15),
            'responder003', 'responder004', 'wranklepig', 'Wiseman', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Berserk', 'Chance', 'Fire', 'Null', 'Ornery', 'Poison', 'Queer', 'Stinger', 'Trip'));
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Berserk', 'Trip');
        $expData['playerDataArray'][0]['button'] = array('name' => 'wranklepig', 'recipe' => 'pB(17) Fo(13) q(11) gc(7) nt(5)', 'originalRecipe' => 'pB(17) Fo(13) q(11) gc(7) nt(5)', 'artFilename' => 'wranklepig.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Wiseman', 'recipe' => '(20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20)', 'artFilename' => 'wiseman.png');
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = -1.5;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -27.7;
        $expData['playerDataArray'][1]['sideScore'] = 27.7;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 9, 'sides' => 17, 'skills' => array('Poison', 'Berserk'), 'properties' => array(), 'recipe' => 'pB(17)', 'description' => 'Poison Berserk 17-sided die'),
            array('value' => 12, 'sides' => 13, 'skills' => array('Fire', 'Ornery'), 'properties' => array(), 'recipe' => 'Fo(13)', 'description' => 'Fire Ornery 13-sided die'),
            array('value' => 2, 'sides' => 11, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(11)', 'description' => 'Queer 11-sided die'),
            array('value' => 2, 'sides' => 7, 'skills' => array('Stinger', 'Chance'), 'properties' => array(), 'recipe' => 'gc(7)', 'description' => 'Stinger Chance 7-sided die'),
            array('value' => 3, 'sides' => 5, 'skills' => array('Null', 'Trip'), 'properties' => array(), 'recipe' => 'nt(5)', 'description' => 'Null Trip 5-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 9, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => 13, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => 20, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => 15, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [pB(17):9, Fo(13):12, q(11):2, gc(7):2, nt(5):3], responder004 rolled [(20):9, (20):13, (20):20, (20):15]. responder003 has dice which are not counted for initiative due to die skills: [gc(7), nt(5)].'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 performed Trip attack using [nt(5):3] against [(20):9] (unsuccessfully)
        // [pB(17):9, Fo(13):12, q(11):2, gc(7):2, nt(5):3] => [(20):9, (20):13, (20):20, (20):15]
        // Trip attacker and defender rerolls, then idle ornery Fo(13) rerolls
        // verify simple Default Trip attack
        $this->verify_api_submitTurn(
            array(4, 18, 12),
            "responder003 performed Trip attack using [nt(5):3] against [(20):9]; Attacker nt(5) rerolled 3 => 4; Defender (20) rerolled 9 => 18, was not captured. responder003's idle ornery dice rerolled at end of turn: Fo(13) rerolled 12 => 12. ",
            $retval, array(array(0, 4), array(1, 0)),
            $gameId, 1, 'Default', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array('HasJustRerolledOrnery');
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('JustPerformedTripAttack', 'JustPerformedUnsuccessfulAttack');
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 18;
        // check that the defender's dice and scores stay unchanged
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -27.7;
        $expData['playerDataArray'][1]['sideScore'] = 27.7;
        $expData['playerDataArray'][1]['activeDieArray'][0]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = '(20)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = '20-sided die';
        // check that the defender's recipe does not change
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [nt(5):3] against [(20):9]; Attacker nt(5) rerolled 3 => 4; Defender (20) rerolled 9 => 18, was not captured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => "responder003's idle ornery dice rerolled at end of turn: Fo(13) rerolled 12 => 12"));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test reproduced an internal error bug affecting Doppelganger dice attacking Twin dice
     * 0. Start a game with responder003 playing Envy and responder004 playing The James Beast
     * 1. responder003 set swing values: X=4
     * 2. c2 set swing values: W=4
     *    c1 won initiative for round 1. Initial die values: c1 rolled [D(4):2, D(6):5, D(10):10, D(12):9, D(X=4):3], c2 rolled [(4):4, (8,8):15, (10,10):10, (12):9, (W=4,W=4):5].
     * 3. responder003 performed Power attack using [D(10):10] against [(10,10):10]; Defender (10,10) was captured; Attacker D(10) changed size from 10 to 20 sides, recipe changed from D(10) to (10,10), rerolled 10 => 5
     * 4. responder004 performed Power attack using [(4):4] against [D(4):2]
     * 5. responder003 performed Power attack using [D(12):9] against [(W=4,W=4):5]
     */
    public function test_interface_game_014() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 14;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // 4 of Envy's dice, and 4 of The James Beast's (6, since 2 are twin), reroll
        $gameId = $this->verify_api_createGame(
            array(2, 5, 10, 9, 4, 8, 7, 5, 5, 9),
            'responder003', 'responder004', 'Envy', 'The James Beast', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Doppelganger'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('W' => array(4, 12));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Envy', 'recipe' => 'D(4) D(6) D(10) D(12) D(X)', 'originalRecipe' => 'D(4) D(6) D(10) D(12) D(X)', 'artFilename' => 'envy.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'The James Beast', 'recipe' => '(4) (8,8) (10,10) (12) (W,W)', 'originalRecipe' => '(4) (8,8) (10,10) (12) (W,W)', 'artFilename' => 'thejamesbeast.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(4)', 'description' => 'Doppelganger 4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(6)', 'description' => 'Doppelganger 6-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(10)', 'description' => 'Doppelganger 10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(12)', 'description' => 'Doppelganger 12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(X)', 'description' => 'Doppelganger X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 16, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(8,8)', 'description' => 'Twin Die (both with 8 sides)', 'subdieArray' => array(array('sides' => 8), array('sides' => 8))),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(10,10)', 'description' => 'Twin Die (both with 10 sides)', 'subdieArray' => array(array('sides' => 10), array('sides' => 10))),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array('Twin'), 'recipe' => '(W,W)', 'description' => 'Twin W Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 set swing values: X=4
        $this->verify_api_submitDieValues(
            array(3),
            $gameId, 1, array('X' => 4), NULL);

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 02 - responder004 set swing values: W=4
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(1, 4),
            $gameId, 1, array('W' => 4), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][0]['sideScore'] = -8.0;
        $expData['playerDataArray'][1]['sideScore'] = 8.0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][1]['subdieArray'] = array(array('sides' => 8, 'value' => 8), array('sides' => 8, 'value' => 7));
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][2]['subdieArray'] = array(array('sides' => 10, 'value' => 5), array('sides' => 10, 'value' => 5));
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array('Twin');
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] .= ' (both with 4 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['subdieArray'] = array(array('sides' => 4, 'value' => 1), array('sides' => 4, 'value' => 4));
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: W=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [D(4):2, D(6):5, D(10):10, D(12):9, D(X=4):3], responder004 rolled [(4):4, (8,8):15, (10,10):10, (12):9, (W=4,W=4):5].'));
        $expData['gameActionLogCount'] += 3;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder003 performed Power attack using [D(10):10] against [(10,10):10]
        // [D(4):2, D(6):5, D(10):10, D(12):9, D(X=4):3] => [(4):4, (8,8):15, (10,10):10, (12):9, (W=4,W=4):5]

        // for code coverage, verify that a Default attack would be rejected here for being ambiguous
        $this->verify_api_submitTurn_failure(
            array(),
            'Default attack is ambiguous. A power attack will trigger the Doppelganger skill, while other attack types will not.',
            $retval, array(array(0, 2), array(1, 2)),
            $gameId, 1, 'Default', 0, 1, '');

        // this now works correctly, requiring two dice to be rolled
        $this->verify_api_submitTurn(
            array(2, 3),
            'responder003 performed Power attack using [D(10):10] against [(10,10):10]; Defender (10,10) was captured; Attacker D(10) changed size from 10 to 20 sides, recipe changed from D(10) to (10,10), rerolled 10 => 5. ',
            $retval, array(array(0, 2), array(1, 2)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(43, 20, 15.3, -15.3),
            array(array(0, 2, array('value' => 5, 'recipe' => '(10,10)', 'description' => 'Twin Die (both with 10 sides)', 'sides' => 20, 'skills' => array(), 'properties' => array('HasJustMorphed', 'Twin'), 'subdieArray' => array(array('sides' => 10, 'value' => 2), array('sides' => 10, 'value' => 3))))),
            array(array(1, 2)),
            array(),
            array(array(0, array('value' => 10, 'sides' => 20, 'recipe' => '(10,10)')))
        );
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array('WasJustCaptured', 'Twin');
        $expData['playerDataArray'][0]['capturedDieArray'][0]['subdieArray'] = array(array('sides' => 10, 'value' => 5), array('sides' => 10, 'value' => 5));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [D(10):10] against [(10,10):10]; Defender (10,10) was captured; Attacker D(10) changed size from 10 to 20 sides, recipe changed from D(10) to (10,10), rerolled 10 => 5'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 04 - responder004 performed Power attack using [(4):4] against [D(4):2]
        // [D(4):2, D(6):5, (10,10):5, D(12):9, D(X=4):3] <= [(4):4, (8,8):15, (12):9, (W=4,W=4):5]

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3),
            'responder004 performed Power attack using [(4):4] against [D(4):2]; Defender D(4) was captured; Attacker (4) rerolled 4 => 3. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(41, 24, 11.3, -11.3),
            array(array(1, 0, array('value' => 3)),
                  array(1, 3, array('properties' => array('Twin'))),
                  array(0, 2, array('properties' => array('Twin')))),
            array(array(0, 0)),
            array(array(0, 0)),
            array(array(1, array('value' => 2, 'sides' => 4, 'recipe' => 'D(4)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(4):4] against [D(4):2]; Defender D(4) was captured; Attacker (4) rerolled 4 => 3'));
        $expData['gameActionLogCount'] += 1;

        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array('Twin');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder003 performed Power attack using [D(12):9] against [(W=4,W=4):5]
        // [D(6):5, (10,10):5, (W=4,W=4):4, D(X=4):3] => [(4):3, (8,8):15, (12):9]

        $this->verify_api_submitTurn(
            array(1, 3),
            'responder003 performed Power attack using [D(12):9] against [(W=4,W=4):5]; Defender (W=4,W=4) was captured; Attacker D(12) changed size from 12 to 8 sides, recipe changed from D(12) to (W=4,W=4), rerolled 9 => 4. ',
            $retval, array(array(0, 2), array(1, 3)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(47, 20, 18, -18),
            array(array(0, 2, array('value' => 4, 'sides' => 8, 'recipe' => '(W,W)', 'properties' => array('HasJustMorphed', 'Twin'), 'description' => 'Twin W Swing Die (both with 4 sides)', 'skills' => array(), 'subdieArray' => array(array('sides' => 4, 'value' => 1), array('sides' => 4, 'value' => 3))))),
            array(array(1, 3)),
            array(array(1, 0)),
            array(array(0, array('value' => 5, 'sides' => 8, 'recipe' => '(W,W)')))
        );
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array('WasJustCaptured', 'Twin');
        $expData['playerDataArray'][0]['capturedDieArray'][1]['subdieArray'] = array(array('sides' => 4, 'value' => 1), array('sides' => 4, 'value' => 4));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [D(12):9] against [(W=4,W=4):5]; Defender (W=4,W=4) was captured; Attacker D(12) changed size from 12 to 8 sides, recipe changed from D(12) to (W=4,W=4), rerolled 9 => 4'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder004 performed Power attack using [(4):3] against [D(X=4):3]
        // [D(6):5, (10,10):5, (W=4,W=4):4, D(X=4):3] <= [(4):3, (8,8):15, (12):9]

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Power attack using [(4):3] against [D(X=4):3]; Defender D(X=4) was captured; Attacker (4) rerolled 3 => 1. ',
            $retval, array(array(0, 3), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(45, 24, 14, -14),
            array(array(1, 0, array('value' => 1)),
                  array(0, 2, array('properties' => array('Twin')))),
            array(array(0, 3)),
            array(array(0, 1)),
            array(array(1, array('value' => 3, 'sides' => 4, 'recipe' => 'D(X)')))
        );

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(4):3] against [D(X=4):3]; Defender D(X=4) was captured; Attacker (4) rerolled 3 => 1'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array('Twin');

        // this triggers the bug
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     */
    public function test_interface_game_015() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 15;
        $_SESSION = $this->mock_test_user_login('responder003');

        ////////////////////
        // initial game setup
        // Skomp: wm(1) wm(2) wm(4) m(8) m(10)
        // Loki:  Ho(2,2) Ho(2,2) Ho(2,2) Ho(2,2) (T)
        // 5 of Skomp's dice, and 4 of Loki's (8 rolls) reroll
        $gameId = $this->verify_api_createGame(
            array(1, 1, 2, 1, 5, 1, 2, 2, 1, 1, 1, 2, 2),
            'responder003', 'responder004', 'Skomp', 'Loki', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);

        // this should cause the one swing die to be rerolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(2),
            $gameId, 1, array('T' => '2'), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);

        // [wm(1):1, wm(2):1, wm(4):2, m(8):1, m(10):5] => [Ho(2,2):3, Ho(2,2):3, Ho(2,2):2, Ho(2,2):4, T=2:2]
        $this->verify_api_submitTurn(
            array(2, 2, 1, 1, 1, 2),
            'responder003 performed Skill attack using [wm(1):1,wm(2):1,m(8):1] against [Ho(2,2):3]; Defender Ho(2,2) was captured; Attacker wm(1) changed size from 1 to 4 sides, recipe changed from wm(1) to wm(2,2), rerolled 1 => 4; Attacker wm(2) changed size from 2 to 4 sides, recipe changed from wm(2) to wm(2,2), rerolled 1 => 2; Attacker m(8) changed size from 8 to 4 sides, recipe changed from m(8) to m(2,2), rerolled 1 => 3. ',
            $retval, array(array(0, 0), array(0, 1), array(0, 3), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '');

    }


    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test reproduced an internal error bug affecting Anti-Llama
     * 0. Start a game with responder003 playing Anti-Llama and responder004 playing Anti-Llama
     *    c2 won initiative for round 1. Initial die values: c1 rolled [%Ho(1,2):2, %Ho(1,4):4, %Ho(1,6):5, %Ho(1,8):7], c2 rolled [%Ho(1,2):3, %Ho(1,4):2, %Ho(1,6):3, %Ho(1,8):2].
     * 1. responder004 performed Power attack using [%Ho(1,2):3] against [%Ho(1,2):2].
     */
    public function test_interface_game_016() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 16;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Each button rolls 4 twin dice (8 rolls/button = 16 rolls total)
        $gameId = $this->verify_api_createGame(
            array(1, 1, 1, 3, 1, 4, 1, 6, 1, 2, 1, 1, 1, 2, 1, 1),
            'responder003', 'responder004', 'Anti-Llama', 'Anti-Llama', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Mighty', 'Ornery', 'Radioactive'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Anti-Llama', 'recipe' => '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)', 'originalRecipe' => '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)', 'artFilename' => 'antillama.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Anti-Llama', 'recipe' => '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)', 'originalRecipe' => '%Ho(1,2) %Ho(1,4) %Ho(1,6) %Ho(1,8)', 'artFilename' => 'antillama.png');
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 12.0;
        $expData['playerDataArray'][1]['roundScore'] = 12.0;
        $expData['playerDataArray'][0]['sideScore'] = 0.0;
        $expData['playerDataArray'][1]['sideScore'] = 0.0;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 2, 'sides' => 3, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,2)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 2 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 2, 'value' => 1))),
            array('value' => 4, 'sides' => 5, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,4)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 4 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 4, 'value' => 3))),
            array('value' => 5, 'sides' => 7, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,6)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 6 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 6, 'value' => 4))),
            array('value' => 7, 'sides' => 9, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,8)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 8 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 8, 'value' => 6))),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 3, 'sides' => 3, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,2)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 2 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 2, 'value' => 2))),
            array('value' => 2, 'sides' => 5, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,4)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 4 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 4, 'value' => 1))),
            array('value' => 3, 'sides' => 7, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,6)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 6 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 6, 'value' => 2))),
            array('value' => 2, 'sides' => 9, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,8)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 8 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 8, 'value' => 1))),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [%Ho(1,2):2, %Ho(1,4):4, %Ho(1,6):5, %Ho(1,8):7], responder004 rolled [%Ho(1,2):3, %Ho(1,4):2, %Ho(1,6):3, %Ho(1,8):2].'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder004 performed Power attack using [%Ho(1,2):3] against [%Ho(1,2):2]
        // [%Ho(1,2):2, %Ho(1,4):4, %Ho(1,6):5, %Ho(1,8):7] <= [%Ho(1,2):3, %Ho(1,4):2, %Ho(1,6):3, %Ho(1,8):2]

        // this should require 10 dice to be rolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
            'responder004 performed Power attack using [%Ho(1,2):3] against [%Ho(1,2):2]; Defender %Ho(1,2) was captured; Attacker %Ho(1,2) showing 3 changed to Ho(1,2), which then split into: Ho(1,1) which grew into Ho(2,2) showing 2, and Ho(0,1) which grew into Ho(1,2) showing 2. responder004\'s idle ornery dice rerolled at end of turn: %Ho(1,4) changed size from 5 to 8 sides, recipe changed from %Ho(1,4) to %Ho(2,6), rerolled 2 => 2; %Ho(1,6) changed size from 7 to 10 sides, recipe changed from %Ho(1,6) to %Ho(2,8), rerolled 3 => 2; %Ho(1,8) changed size from 9 to 12 sides, recipe changed from %Ho(1,8) to %Ho(2,10), rerolled 2 => 2. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power',);
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 10.5;
        $expData['playerDataArray'][1]['roundScore'] = 21.5;
        $expData['playerDataArray'][0]['sideScore'] = -7.3;
        $expData['playerDataArray'][1]['sideScore'] = 7.3;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 4, 'sides' => 5, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,4)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 4 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 4, 'value' => 3))),
            array('value' => 5, 'sides' => 7, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,6)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 6 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 6, 'value' => 4))),
            array('value' => 7, 'sides' => 9, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('Twin'), 'recipe' => '%Ho(1,8)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 1 and 8 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 8, 'value' => 6))),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 2, 'sides' => 4, 'skills' => array('Mighty', 'Ornery'), 'properties' => array('HasJustSplit', 'HasJustGrown', 'Twin'), 'recipe' => 'Ho(2,2)', 'description' => 'Mighty Ornery Twin Die (both with 2 sides)', 'subdieArray' => array(array('sides' => 2, 'value' => 1), array('sides' => 2, 'value' => 1))),
            array('value' => 2, 'sides' => 3, 'skills' => array('Mighty', 'Ornery'), 'properties' => array('HasJustSplit', 'HasJustGrown', 'Twin'), 'recipe' => 'Ho(1,2)', 'description' => 'Mighty Ornery Twin Die (with 1 and 2 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 2, 'value' => 1))),
            array('value' => 2, 'sides' => 8, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('HasJustGrown', 'HasJustRerolledOrnery', 'Twin'), 'recipe' => '%Ho(2,6)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 2 and 6 sides)', 'subdieArray' => array(array('sides' => 2, 'value' => 1), array('sides' => 6, 'value' => 1))),
            array('value' => 2, 'sides' => 10, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('HasJustGrown', 'HasJustRerolledOrnery', 'Twin'), 'recipe' => '%Ho(2,8)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 2 and 8 sides)', 'subdieArray' => array(array('sides' => 2, 'value' => 1), array('sides' => 8, 'value' => 1))),
            array('value' => 2, 'sides' => 12, 'skills' => array('Radioactive', 'Mighty', 'Ornery'), 'properties' => array('HasJustGrown', 'HasJustRerolledOrnery', 'Twin'), 'recipe' => '%Ho(2,10)', 'description' => 'Radioactive Mighty Ornery Twin Die (with 2 and 10 sides)', 'subdieArray' => array(array('sides' => 2, 'value' => 1), array('sides' => 10, 'value' => 1))),
        );
        $expData['playerDataArray'][1]['capturedDieArray'] = array(
            array('value' => 2, 'sides' => 3, 'properties' => array('WasJustCaptured', 'Twin'), 'recipe' => '%Ho(1,2)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 2, 'value' => 1))),
        );

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [%Ho(1,2):3] against [%Ho(1,2):2]; Defender %Ho(1,2) was captured; Attacker %Ho(1,2) showing 3 changed to Ho(1,2), which then split into: Ho(1,1) which grew into Ho(2,2) showing 2, and Ho(0,1) which grew into Ho(1,2) showing 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004\'s idle ornery dice rerolled at end of turn: %Ho(1,4) changed size from 5 to 8 sides, recipe changed from %Ho(1,4) to %Ho(2,6), rerolled 2 => 2; %Ho(1,6) changed size from 7 to 10 sides, recipe changed from %Ho(1,6) to %Ho(2,8), rerolled 3 => 2; %Ho(1,8) changed size from 9 to 12 sides, recipe changed from %Ho(1,8) to %Ho(2,10), rerolled 2 => 2'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test reproduces a bug in which, when a morphing die captures a swing die, that player is prompted to set swing dice at the end of the round.
     * 0. Start a game with responder003 playing Skomp and responder004 playing Fan Chung Graham
     * 1. responder004 set swing values: X=12 and option dice: (10/20=10)
     *    responder004 won initiative for round 1. Initial die values: responder003 rolled [wm(1):1, wm(2):1, wm(4):4, m(8):4, m(10):2], responder004 rolled [(4):2, k(6):4, (8):1, (10/20=10):3, (X=12)?:8]. responder003 has dice which are not counted for initiative due to die skills: [wm(1), wm(2), wm(4)].
     * 2. responder004 performed Power attack using [(8):1] against [wm(1):1]; Defender wm(1) was captured; Attacker (8) rerolled 1 => 8
     * 3. responder003 performed Skill attack using [wm(2):1,m(10):2] against [(10/20=10):3]; Defender (10/20=10) was captured; Attacker wm(2) changed size from 2 to 10 sides, recipe changed from wm(2) to wm(10/20=10), rerolled 1 => 6; Attacker m(10) recipe changed from m(10) to m(10/20=10), rerolled 2 => 2
     * 4. responder004 performed Power attack using [(4):2] against [m(10/20=10):2]; Defender m(10/20=10) was captured; Attacker (4) rerolled 2 => 1
     * 5. responder003 performed Power attack using [wm(10/20=10):6] against [(4):1]; Defender (4) was captured; Attacker wm(10/20=10) changed size from 10 to 4 sides, recipe changed from wm(10/20=10) to wm(4), rerolled 6 => 4
     * 6. responder004 performed Power attack using [(8):8] against [m(8):4]; Defender m(8) was captured; Attacker (8) rerolled 8 => 1
     * 7. responder003 performed Skill attack using [wm(4):4,wm(4):4] against [(X=12)?:8]; Defender (X=12)? was captured; Attacker wm(4) changed size from 4 to 12 sides, recipe changed from wm(4) to wm(X=12), rerolled 4 => 3; Attacker wm(4) changed size from 4 to 12 sides, recipe changed from wm(4) to wm(X=12), rerolled 4 => 9
     *    responder004 passed
     * 8. responder003 performed Power attack using [wm(X=12):9] against [k(6):4]; Defender k(6) was captured; Attacker wm(X=12) changed size from 12 to 6 sides, recipe changed from wm(X=12) to wm(6), rerolled 9 => 5
     *    responder004 passed
     * 9. responder003 performed Power attack using [wm(6):5] against [(8):1]; Defender (8) was captured; Attacker wm(6) changed size from 6 to 8 sides, recipe changed from wm(6) to wm(8), rerolled 5 => 1
     *    End of round: responder003 won round 1 (50 vs. 19)
     */
    public function test_interface_game_017() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 17;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Skomp rolls 5 dice, Fan Chung Graham rolls 3
        $gameId = $this->verify_api_createGame(
            array(1, 1, 4, 4, 2, 2, 4, 1),
            'responder003', 'responder004', 'Skomp', 'Fan Chung Graham', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Konstant', 'Mood', 'Morphing', 'Slow'));
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['optRequestArray'] = array('3' => array(10, 20));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Skomp', 'recipe' => 'wm(1) wm(2) wm(4) m(8) m(10)', 'originalRecipe' => 'wm(1) wm(2) wm(4) m(8) m(10)', 'artFilename' => 'skomp.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Fan Chung Graham', 'recipe' => '(4) k(6) (8) (10/20) (X)?', 'originalRecipe' => '(4) k(6) (8) (10/20) (X)?', 'artFilename' => 'fanchunggraham.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(1)', 'description' => 'Slow Morphing 1-sided die'),
            array('value' => NULL, 'sides' => 2, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(2)', 'description' => 'Slow Morphing 2-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(4)', 'description' => 'Slow Morphing 4-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(8)', 'description' => 'Morphing 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(10)', 'description' => 'Morphing 10-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Konstant'), 'properties' => array(), 'recipe' => 'k(6)', 'description' => 'Konstant 6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(10/20)', 'description' => 'Option Die (with 10 or 20 sides)'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Mood'), 'properties' => array(), 'recipe' => '(X)?', 'description' => 'X Mood Swing Die'),
        );

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder004 set swing values: X=12 and option dice: (10/20=10)

        // this should cause the option and swing dice to be rolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(3, 8),
            $gameId, 1, array('X' => 12), array(3 => 10));
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 12.5;
        $expData['playerDataArray'][1]['roundScore'] = 20;
        $expData['playerDataArray'][0]['sideScore'] = -5.0;
        $expData['playerDataArray'][1]['sideScore'] = 5.0;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = 'Option Die (with 10 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] .= ' (with 12 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=12 and option dice: (10/20=10)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [wm(1):1, wm(2):1, wm(4):4, m(8):4, m(10):2], responder004 rolled [(4):2, k(6):4, (8):1, (10/20=10):3, (X=12)?:8]. responder003 has dice which are not counted for initiative due to die skills: [wm(1), wm(2), wm(4)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 performed Power attack using [(8):1] against [wm(1):1]
        // [wm(1):1, wm(2):1, wm(4):4, m(8):4, m(10):2] <= [(4):2, k(6):4, (8):1, (10/20=10):3, (X=12)?:8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(8),
            'responder004 performed Power attack using [(8):1] against [wm(1):1]; Defender wm(1) was captured; Attacker (8) rerolled 1 => 8. ',
            $retval, array(array(0, 0), array(1, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(12.0, 21.0, -6.0, 6.0),
            array(array(1, 2, array('value' => 8))),
            array(array(0, 0)),
            array(),
            array(array(1, array('value' => 1, 'sides' => 1, 'recipe' => 'wm(1)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(8):1] against [wm(1):1]; Defender wm(1) was captured; Attacker (8) rerolled 1 => 8'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder003 performed Skill attack using [wm(2):1,m(10):2] against [(10/20=10):3]
        // [wm(2):1, wm(4):4, m(8):4, m(10):2] => [(4):2, k(6):4, (8):8, (10/20=10):3, (X=12)?:8]
        $this->verify_api_submitTurn(
            array(6, 2),
            'responder003 performed Skill attack using [wm(2):1,m(10):2] against [(10/20=10):3]; Defender (10/20=10) was captured; Attacker wm(2) changed size from 2 to 10 sides, recipe changed from wm(2) to wm(10/20=10), rerolled 1 => 6; Attacker m(10) remained the same size, recipe changed from m(10) to m(10/20=10), rerolled 2 => 2. ',
            $retval, array(array(0, 0), array(0, 3), array(1, 3)),
            $gameId, 1, 'Skill', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(26.0, 16.0, 6.7, -6.7),
            array(array(0, 0, array('value' => 6, 'sides' => 10, 'recipe' => 'wm(10/20)', 'description' => 'Slow Morphing Option Die (with 10 sides)', 'properties' => array('HasJustMorphed'))),
                  array(0, 3, array('value' => 2, 'sides' => 10, 'recipe' => 'm(10/20)', 'description' => 'Morphing Option Die (with 10 sides)', 'properties' => array('HasJustMorphed')))),
            array(array(1, 3)),
            array(array(1, 0)),
            array(array(0, array('value' => 3, 'sides' => 10, 'recipe' => '(10/20)')))
        );
        $expData['playerDataArray'][0]['optRequestArray'] = array(0 => array(10, 20), 3 => array(10, 20));
        $expData['playerDataArray'][1]['optRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [wm(2):1,m(10):2] against [(10/20=10):3]; Defender (10/20=10) was captured; Attacker wm(2) changed size from 2 to 10 sides, recipe changed from wm(2) to wm(10/20=10), rerolled 1 => 6; Attacker m(10) remained the same size, recipe changed from m(10) to m(10/20=10), rerolled 2 => 2'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder004 performed Power attack using [(4):2] against [m(10/20=10):2]
        // [wm(10/20=10):6, wm(4):4, m(8):4, m(10/20=10):2] <= [(4):2, k(6):4, (8):8, (X=12)?:8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Power attack using [(4):2] against [m(10/20=10):2]; Defender m(10/20=10) was captured; Attacker (4) rerolled 2 => 1. ',
            $retval, array(array(0, 3), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(21.0, 26.0, -3.3, 3.3),
            array(array(0, 0, array('properties' => array())),
                  array(1, 0, array('value' => 1))),
            array(array(0, 3)),
            array(array(0, 0)),
            array(array(1, array('value' => 2, 'sides' => 10, 'recipe' => 'm(10/20)')))
        );
        $expData['playerDataArray'][0]['optRequestArray'] = array(0 => array(10, 20));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(4):2] against [m(10/20=10):2]; Defender m(10/20=10) was captured; Attacker (4) rerolled 2 => 1'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder003 performed Power attack using [wm(10/20=10):6] against [(4):1]
        // [wm(10/20=10):6, wm(4):4, m(8):4] => [(4):1, k(6):4, (8):8, (X=12)?:8]
        $this->verify_api_submitTurn(
            array(4),
            'responder003 performed Power attack using [wm(10/20=10):6] against [(4):1]; Defender (4) was captured; Attacker wm(10/20=10) changed size from 10 to 4 sides, recipe changed from wm(10/20=10) to wm(4), rerolled 6 => 4. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(22.0, 24.0, -1.3, 1.3),
            array(array(0, 0, array('value' => 4, 'sides' => 4, 'recipe' => 'wm(4)', 'description' => 'Slow Morphing 4-sided die', 'properties' => array('HasJustMorphed')))),
            array(array(1, 0)),
            array(array(1, 1)),
            array(array(0, array('value' => 1, 'sides' => 4, 'recipe' => '(4)')))
        );
        $expData['playerDataArray'][0]['optRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [wm(10/20=10):6] against [(4):1]; Defender (4) was captured; Attacker wm(10/20=10) changed size from 10 to 4 sides, recipe changed from wm(10/20=10) to wm(4), rerolled 6 => 4'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder004 performed Power attack using [(8):8] against [m(8):4]
        // [wm(4):4, wm(4):4, m(8):4] <= [k(6):4, (8):8, (X=12)?:8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Power attack using [(8):8] against [m(8):4]; Defender m(8) was captured; Attacker (8) rerolled 8 => 1. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(18.0, 32.0, -9.3, 9.3),
            array(array(0, 0, array('properties' => array())),
                  array(1, 1, array('value' => 1))),
            array(array(0, 2)),
            array(array(0, 1)),
            array(array(1, array('value' => 4, 'sides' => 8, 'recipe' => 'm(8)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(8):8] against [m(8):4]; Defender m(8) was captured; Attacker (8) rerolled 8 => 1'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - responder003 performed Skill attack using [wm(4):4,wm(4):4] against [(X=12)?:8]
        // [wm(4):4, wm(4):4] => [k(6):4, (8):1, (X=12)?:8]
        $this->verify_api_submitTurn(
            array(3, 9),
            'responder003 performed Skill attack using [wm(4):4,wm(4):4] against [(X=12)?:8]; Defender (X=12)? was captured; Attacker wm(4) changed size from 4 to 12 sides, recipe changed from wm(4) to wm(X=12), rerolled 4 => 3; Attacker wm(4) changed size from 4 to 12 sides, recipe changed from wm(4) to wm(X=12), rerolled 4 => 9. responder004 passed. ',
            $retval, array(array(0, 0), array(0, 1), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power'),
            array(38.0, 26.0, 8.0, -8.0),
            array(array(0, 0, array('value' => 3, 'sides' => 12, 'recipe' => 'wm(X)', 'description' => 'Slow Morphing X Swing Die (with 12 sides)')),
                  array(0, 1, array('value' => 9, 'sides' => 12, 'recipe' => 'wm(X)', 'description' => 'Slow Morphing X Swing Die (with 12 sides)'))),
            array(array(1, 2)),
            array(array(0, 2), array(1, 2)),
            array(array(0, array('value' => 8, 'sides' => 12, 'recipe' => '(X)?')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [wm(4):4,wm(4):4] against [(X=12)?:8]; Defender (X=12)? was captured; Attacker wm(4) changed size from 4 to 12 sides, recipe changed from wm(4) to wm(X=12), rerolled 4 => 3; Attacker wm(4) changed size from 4 to 12 sides, recipe changed from wm(4) to wm(X=12), rerolled 4 => 9'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 08 - responder003 performed Power attack using [wm(X=12):9] against [k(6):4]
        // [wm(4):4, wm(4):4] => [k(6):4, (8):1]
        $this->verify_api_submitTurn(
            array(5),
            'responder003 performed Power attack using [wm(X=12):9] against [k(6):4]; Defender k(6) was captured; Attacker wm(X=12) changed size from 12 to 6 sides, recipe changed from wm(X=12) to wm(6), rerolled 9 => 5. responder004 passed. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power'),
            array(41.0, 23.0, 12.0, -12.0),
            array(array(0, 1, array('value' => 5, 'sides' => 6, 'recipe' => 'wm(6)', 'description' => 'Slow Morphing 6-sided die'))),
            array(array(1, 0)),
            array(array(0, 3)),
            array(array(0, array('value' => 4, 'sides' => 6, 'recipe' => 'k(6)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [wm(X=12):9] against [k(6):4]; Defender k(6) was captured; Attacker wm(X=12) changed size from 12 to 6 sides, recipe changed from wm(X=12) to wm(6), rerolled 9 => 5'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 09 - responder003 performed Power attack using [wm(6):5] against [(8):1]
        // [wm(4):4, wm(4):4] => [(8):1]
        // One roll for the end of round 1, then 5 Skomp rolls and 3 Fan Chung Graham rolls for round 2
        $this->verify_api_submitTurn(
            array(1, 1, 2, 2, 7, 3, 3, 1, 7),
            'responder003 performed Power attack using [wm(6):5] against [(8):1]; Defender (8) was captured; Attacker wm(6) changed size from 6 to 8 sides, recipe changed from wm(6) to wm(8), rerolled 5 => 1. End of round: responder003 won round 1 (50 vs. 19). ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');
        $expData['roundNumber'] = 2;
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['optRequestArray'] = array(3 => array(10, 20));
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array('X' => 12);
        $expData['playerDataArray'][1]['prevOptValueArray'] = array(3 => 10);
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(1)', 'description' => 'Slow Morphing 1-sided die'),
            array('value' => NULL, 'sides' => 2, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(2)', 'description' => 'Slow Morphing 2-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(4)', 'description' => 'Slow Morphing 4-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(8)', 'description' => 'Morphing 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(10)', 'description' => 'Morphing 10-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Konstant'), 'properties' => array(), 'recipe' => 'k(6)', 'description' => 'Konstant 6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(10/20)', 'description' => 'Option Die (with 10 or 20 sides)'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Mood'), 'properties' => array(), 'recipe' => '(X)?', 'description' => 'X Mood Swing Die'),
        );
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [wm(6):5] against [(8):1]; Defender (8) was captured; Attacker wm(6) changed size from 6 to 8 sides, recipe changed from wm(6) to wm(8), rerolled 5 => 1'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: responder003 won round 1 (50 vs. 19)'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test reproduces a bug in which a Skomp vs. Envy game leads to an internal error
     * 0. Start a game with responder003 playing Skomp and responder004 playing Envy
     * 1. responder004 set swing values: X=4
     *    responder004 won initiative for round 1. Initial die values: responder003 rolled [wm(1):1, wm(2):2, wm(4):2, m(8):4, m(10):6], responder004 rolled [D(4):2, D(6):2, D(10):10, D(12):4, D(X=4):2]. responder003 has dice which are not counted for initiative due to die skills: [wm(1), wm(2), wm(4)].
     * 2. responder004 performed Power attack using [D(12):4] against [m(8):4]; Defender m(8) was captured; Attacker D(12) changed size from 12 to 8 sides, recipe changed from D(12) to m(8), rerolled 4 => 8
     * 3. responder003 performed Power attack using [wm(4):2] against [D(X=4):2]; Defender D(X=4) was captured; Attacker wm(4) recipe changed from wm(4) to wm(X=4), rerolled 2 => 1
     * 4. responder004 performed Power attack using [D(6):2] against [wm(X=4):1]; Defender wm(X=4) was captured; Attacker D(6) changed size from 6 to 4 sides, recipe changed from D(6) to wm(X=4), rerolled 2 =>
     */
    public function test_interface_game_018() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 18;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Skomp rolls 5 dice, Envy rolls 4
        $gameId = $this->verify_api_createGame(
            array(1, 2, 2, 4, 6, 2, 2, 10, 4),
            'responder003', 'responder004', 'Skomp', 'Envy', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Doppelganger', 'Morphing', 'Slow'));
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Skomp', 'recipe' => 'wm(1) wm(2) wm(4) m(8) m(10)', 'originalRecipe' => 'wm(1) wm(2) wm(4) m(8) m(10)', 'artFilename' => 'skomp.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Envy', 'recipe' => 'D(4) D(6) D(10) D(12) D(X)', 'originalRecipe' => 'D(4) D(6) D(10) D(12) D(X)', 'artFilename' => 'envy.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(1)', 'description' => 'Slow Morphing 1-sided die'),
            array('value' => NULL, 'sides' => 2, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(2)', 'description' => 'Slow Morphing 2-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(4)', 'description' => 'Slow Morphing 4-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(8)', 'description' => 'Morphing 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(10)', 'description' => 'Morphing 10-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(4)', 'description' => 'Doppelganger 4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(6)', 'description' => 'Doppelganger 6-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(10)', 'description' => 'Doppelganger 10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(12)', 'description' => 'Doppelganger 12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(X)', 'description' => 'Doppelganger X Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder004 set swing values: X=4

        // this should cause the swing die to be rolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(2),
            $gameId, 1, array('X' => 4), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 12.5;
        $expData['playerDataArray'][1]['roundScore'] = 18;
        $expData['playerDataArray'][0]['sideScore'] = -3.7;
        $expData['playerDataArray'][1]['sideScore'] = 3.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [wm(1):1, wm(2):2, wm(4):2, m(8):4, m(10):6], responder004 rolled [D(4):2, D(6):2, D(10):10, D(12):4, D(X=4):2]. responder003 has dice which are not counted for initiative due to die skills: [wm(1), wm(2), wm(4)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 performed Power attack using [D(12):4] against [m(8):4]
        // [wm(1):1, wm(2):2, wm(4):2, m(8):4, m(10):6] <= [D(4):2, D(6):2, D(10):10, D(12):4, D(X=4):2]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(8),
            'responder004 performed Power attack using [D(12):4] against [m(8):4]; Defender m(8) was captured; Attacker D(12) changed size from 12 to 8 sides, recipe changed from D(12) to m(8), rerolled 4 => 8. ',
            $retval, array(array(0, 3), array(1, 3)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(8.5, 24, -10.3, 10.3),
            array(array(1, 3, array('value' => 8, 'sides' => 8, 'recipe' => 'm(8)', 'skills' => array('Morphing'), 'description' => 'Morphing 8-sided die', 'properties' => array('HasJustMorphed')))),
            array(array(0, 3)),
            array(),
            array(array(1, array('value' => 4, 'sides' => 8, 'recipe' => 'm(8)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [D(12):4] against [m(8):4]; Defender m(8) was captured; Attacker D(12) changed size from 12 to 8 sides, recipe changed from D(12) to m(8), rerolled 4 => 8'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder003 performed Power attack using [wm(4):2] against [D(X=4):2]
        // [wm(1):1, wm(2):2, wm(4):2, m(10):6] <= [D(4):2, D(6):2, D(10):10, m(8):8, D(X=4):2]
        $this->verify_api_submitTurn(
            array(1),
            'responder003 performed Power attack using [wm(4):2] against [D(X=4):2]; Defender D(X=4) was captured; Attacker wm(4) remained the same size, recipe changed from wm(4) to wm(X=4), rerolled 2 => 1. ',
            $retval, array(array(0, 2), array(1, 4)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(12.5, 22, -6.3, 6.3),
            array(array(0, 2, array('value' => 1, 'recipe' => 'wm(X)', 'skills' => array('Slow', 'Morphing'), 'description' => 'Slow Morphing X Swing Die (with 4 sides)', 'properties' => array('HasJustMorphed'))),
                  array(1, 3, array('properties' => array()))),
            array(array(1, 4)),
            array(array(1, 0)),
            array(array(0, array('value' => 2, 'sides' => 4, 'recipe' => 'D(X)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [wm(4):2] against [D(X=4):2]; Defender D(X=4) was captured; Attacker wm(4) remained the same size, recipe changed from wm(4) to wm(X=4), rerolled 2 => 1'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder004 performed Power attack using [D(6):2] against [wm(X=4):1]
        // [wm(1):1, wm(2):2, wm(X=4):1, m(10):6] <= [D(4):2, D(6):2, D(10):10, m(8):8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3),
            'responder004 performed Power attack using [D(6):2] against [wm(X=4):1]; Defender wm(X=4) was captured; Attacker D(6) changed size from 6 to 4 sides, recipe changed from D(6) to wm(X=4), rerolled 2 => 3. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(10.5, 25, -9.7, 9.7),
            array(array(1, 1, array('value' => 3, 'sides' => 4, 'recipe' => 'wm(X)', 'skills' => array('Slow', 'Morphing'), 'description' => 'Slow Morphing X Swing Die (with 4 sides)', 'properties' => array('HasJustMorphed')))),
            array(array(0, 2)),
            array(array(0, 0)),
            array(array(1, array('value' => 1, 'sides' => 4, 'recipe' => 'wm(X)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [D(6):2] against [wm(X=4):1]; Defender wm(X=4) was captured; Attacker D(6) changed size from 6 to 4 sides, recipe changed from D(6) to wm(X=4), rerolled 2 => 3'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     */
    public function test_interface_game_019() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 19;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Pikathulhu rolls 4 dice and Phoenix rolls 5
        $gameId = $this->verify_api_createGame(
            array(5, 3, 8, 7, 4, 3, 5, 1, 8),
            'responder003', 'responder004', 'Pikathulhu', 'Phoenix', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Chance', 'Focus'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'Pikathulhu', 'recipe' => '(6) c(6) (10) (12) c(X)', 'originalRecipe' => '(6) c(6) (10) (12) c(X)', 'artFilename' => 'pikathulhu.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Phoenix', 'recipe' => '(4) (6) f(8) (10) f(20)', 'originalRecipe' => '(4) (6) f(8) (10) f(20)', 'artFilename' => 'phoenix.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Chance'), 'properties' => array(), 'recipe' => 'c(6)', 'description' => 'Chance 6-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Chance'), 'properties' => array(), 'recipe' => 'c(X)', 'description' => 'Chance X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(8)', 'description' => 'Focus 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(20)', 'description' => 'Focus 20-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder003 set swing values: X=4
        // responder004 won initiative for round 1. Initial die values: responder003 rolled [(6):5, c(6):3, (10):8, (12):7, c(X=4):2], responder004 rolled [(4):4, (6):3, f(8):5, (10):1, f(20):8].
        $this->verify_api_submitDieValues(
            array(2),
            $gameId, 1, array('X' => '4'), NULL);

        $expData['gameState'] = 'REACT_TO_INITIATIVE';
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 19;
        $expData['playerDataArray'][1]['roundScore'] = 24;
        $expData['playerDataArray'][0]['sideScore'] = -3.3;
        $expData['playerDataArray'][1]['sideScore'] = 3.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [(6):5, c(6):3, (10):8, (12):7, c(X=4):2], responder004 rolled [(4):4, (6):3, f(8):5, (10):1, f(20):8].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // now load the game as non-participating player responder001 and check its state
        $_SESSION = $this->mock_test_user_login('responder001');
        $expData['dieBackgroundType'] = 'symmetric';
        $this->verify_api_loadGameData_as_nonparticipant($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['dieBackgroundType'] = 'realistic';


        // responder003 rerolled a chance die, but did not gain initiative: c(6) rerolled 3 => 4
        $this->verify_api_reactToInitiative(
            array(4),
            'Failed to gain initiative', array('gainedInitiative' => FALSE),
            $retval, $gameId, 1, 'chance', array(1), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 rerolled a chance die, but did not gain initiative: c(6) rerolled 3 => 4'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder004 performed Skill attack using [(4):4,(6):3,(10):1] against [(10):8]; Defender (10) was captured; Attacker (4) rerolled 4 => 3; Attacker (6) rerolled 3 => 2; Attacker (10) rerolled 1 => 9
        // [(6):5, c(6):4, (10):8, (12):7, c(X=4):2] <= [(4):4, (6):3, f(8):5, (10):1, f(20):8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3, 2, 9),
            'responder004 performed Skill attack using [(4):4,(6):3,(10):1] against [(10):8]; Defender (10) was captured; Attacker (4) rerolled 4 => 3; Attacker (6) rerolled 3 => 2; Attacker (10) rerolled 1 => 9. ',
            $retval, array(array(0, 2), array(1, 0), array(1, 1), array(1, 3)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(14, 34, -13.3, 13.3),
            array(array(1, 0, array('value' => 3)),
                  array(1, 1, array('value' => 2)),
                  array(1, 3, array('value' => 9))),
            array(array(0, 2)),
            array(),
            array(array(1, array('value' => 8, 'sides' => 10, 'recipe' => '(10)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(4):4,(6):3,(10):1] against [(10):8]; Defender (10) was captured; Attacker (4) rerolled 4 => 3; Attacker (6) rerolled 3 => 2; Attacker (10) rerolled 1 => 9'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // responder003 performed Skill attack using [(12):7,c(X=4):2] against [(10):9]; Defender (10) was captured; Attacker (12) rerolled 7 => 5; Attacker c(X=4) rerolled 2 => 1
        // [(6):5, c(6):4, (12):7, c(X=4):2] => [(4):3, (6):2, f(8):5, (10):9, f(20):8]
        $this->verify_api_submitTurn(
            array(5, 1),
            'responder003 performed Skill attack using [(12):7,c(X=4):2] against [(10):9]; Defender (10) was captured; Attacker (12) rerolled 7 => 5; Attacker c(X=4) rerolled 2 => 1. ',
            $retval, array(array(0, 2), array(0, 3), array(1, 3)),
            $gameId, 1, 'Skill', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(24, 29, -3.3, 3.3),
            array(array(0, 2, array('value' => 5)),
                  array(0, 3, array('value' => 1))),
            array(array(1, 3)),
            array(array(1, 0)),
            array(array(0, array('value' => 9, 'sides' => 10, 'recipe' => '(10)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(12):7,c(X=4):2] against [(10):9]; Defender (10) was captured; Attacker (12) rerolled 7 => 5; Attacker c(X=4) rerolled 2 => 1'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder004 performed Skill attack using [(4):3,(6):2] against [(12):5]; Defender (12) was captured; Attacker (4) rerolled 3 => 4; Attacker (6) rerolled 2 => 2
        // [(6):5, c(6):4, (12):5, c(X=4):1] <= [(4):3, (6):2, f(8):5, f(20):8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(4, 2),
            'responder004 performed Skill attack using [(4):3,(6):2] against [(12):5]; Defender (12) was captured; Attacker (4) rerolled 3 => 4; Attacker (6) rerolled 2 => 2. ',
            $retval, array(array(0, 2), array(1, 0), array(1,1)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(18, 41, -15.3, 15.3),
            array(array(1, 0, array('value' => 4)),
                  array(1, 1, array('value' => 2))),
            array(array(0, 2)),
            array(array(0, 0)),
            array(array(1, array('value' => 5, 'sides' => 12, 'recipe' => '(12)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(4):3,(6):2] against [(12):5]; Defender (12) was captured; Attacker (4) rerolled 3 => 4; Attacker (6) rerolled 2 => 2'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder003 performed Skill attack using [c(6):4,c(X=4):1] against [f(8):5]; Defender f(8) was captured; Attacker c(6) rerolled 4 => 1; Attacker c(X=4) rerolled 1 => 4
        // [(6):5, c(6):4, c(X=4):1] => [(4):4, (6):2, f(8):5, f(20):8]
        $this->verify_api_submitTurn(
            array(1, 4),
            'responder003 performed Skill attack using [c(6):4,c(X=4):1] against [f(8):5]; Defender f(8) was captured; Attacker c(6) rerolled 4 => 1; Attacker c(X=4) rerolled 1 => 4. ',
            $retval, array(array(0, 1), array(0, 2), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(26, 37, -7.3, 7.3),
            array(array(0, 1, array('value' => 1)),
                  array(0, 2, array('value' => 4))),
            array(array(1, 2)),
            array(array(1, 1)),
            array(array(0, array('value' => 5, 'sides' => 8, 'recipe' => 'f(8)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [c(6):4,c(X=4):1] against [f(8):5]; Defender f(8) was captured; Attacker c(6) rerolled 4 => 1; Attacker c(X=4) rerolled 1 => 4'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder004 performed Power attack using [(4):4] against [c(X=4):4]; Defender c(X=4) was captured; Attacker (4) rerolled 4 => 3
        // [(6):5, c(6):1, c(X=4):4] <= [(4):4, (6):2, f(20):8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3),
            'responder004 performed Power attack using [(4):4] against [c(X=4):4]; Defender c(X=4) was captured; Attacker (4) rerolled 4 => 3. ',
            $retval, array(array(0, 2), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power'),
            array(24, 41, -11.3, 11.3),
            array(array(1, 0, array('value' => 3))),
            array(array(0, 2)),
            array(array(0, 1)),
            array(array(1, array('value' => 4, 'sides' => 4, 'recipe' => 'c(X)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(4):4] against [c(X=4):4]; Defender c(X=4) was captured; Attacker (4) rerolled 4 => 3'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder003 performed Power attack using [(6):5] against [(6):2]; Defender (6) was captured; Attacker (6) rerolled 5 => 5
        // [(6):5, c(6):1] => [(4):3, (6):2, f(20):8]
        $this->verify_api_submitTurn(
            array(5),
            'responder003 performed Power attack using [(6):5] against [(6):2]; Defender (6) was captured; Attacker (6) rerolled 5 => 5. ',
            $retval, array(array(0, 0), array(1, 1)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power'),
            array(30, 38, -5.3, 5.3),
            array(array(0, 0, array('value' => 5))),
            array(array(1, 1)),
            array(array(1, 2)),
            array(array(0, array('value' => 2, 'sides' => 6, 'recipe' => '(6)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(6):5] against [(6):2]; Defender (6) was captured; Attacker (6) rerolled 5 => 5'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder004 performed Power attack using [(4):3] against [c(6):1]; Defender c(6) was captured; Attacker (4) rerolled 3 => 4
        // [(6):5, c(6):1] <= [(4):3, f(20):8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(4),
            'responder004 performed Power attack using [(4):3] against [c(6):1]; Defender c(6) was captured; Attacker (4) rerolled 3 => 4. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power'),
            array(27, 44, -11.3, 11.3),
            array(array(1, 0, array('value' => 4))),
            array(array(0, 1)),
            array(array(0, 2)),
            array(array(1, array('value' => 1, 'sides' => 6, 'recipe' => 'c(6)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(4):3] against [c(6):1]; Defender c(6) was captured; Attacker (4) rerolled 3 => 4'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder003 performed Power attack using [(6):5] against [(4):4]; Defender (4) was captured; Attacker (6) rerolled 5 => 4
        // [(6):5] => [(4):4, f(20):8]
        $this->verify_api_submitTurn(
            array(4),
            'responder003 performed Power attack using [(6):5] against [(4):4]; Defender (4) was captured; Attacker (6) rerolled 5 => 4. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power'),
            array(31, 42, -7.3, 7.3),
            array(array(0, 0, array('value' => 4))),
            array(array(1, 0)),
            array(array(1, 3)),
            array(array(0, array('value' => 4, 'sides' => 4, 'recipe' => '(4)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(6):5] against [(4):4]; Defender (4) was captured; Attacker (6) rerolled 5 => 4'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder004 performed Power attack using [f(20):8] against [(6):4]; Defender (6) was captured; Attacker f(20) rerolled 8 => 14
        // End of round: responder004 won round 1 (48 vs. 28)
        // [(6):4] => [f(20):8]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(14, 1, 4, 5, 8, 3, 5, 3, 8, 2),
            'responder004 performed Power attack using [f(20):8] against [(6):4]; Defender (6) was captured; Attacker f(20) rerolled 8 => 14. End of round: responder004 won round 1 (48 vs. 28). ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['roundNumber'] = 2;
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array('X' => 4);
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Chance'), 'properties' => array(), 'recipe' => 'c(6)', 'description' => 'Chance 6-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Chance'), 'properties' => array(), 'recipe' => 'c(X)', 'description' => 'Chance X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(8)', 'description' => 'Focus 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(20)', 'description' => 'Focus 20-sided die'),
        );
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [f(20):8] against [(6):4]; Defender (6) was captured; Attacker f(20) rerolled 8 => 14'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 1 (48 vs. 28)'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder003 set swing values: X=6
        // responder003 won initiative for round 2. Initial die values: responder003 rolled [(6):1, c(6):4, (10):5, (12):8, c(X=6):2], responder004 rolled [(4):3, (6):5, f(8):3, (10):8, f(20):2].
        $this->verify_api_submitDieValues(
            array(2),
            $gameId, 2, array('X' => '6'), NULL);

        $expData['gameState'] = 'REACT_TO_INITIATIVE';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 20;
        $expData['playerDataArray'][1]['roundScore'] = 24;
        $expData['playerDataArray'][0]['sideScore'] = -2.7;
        $expData['playerDataArray'][1]['sideScore'] = 2.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 6 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=6'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [(6):1, c(6):4, (10):5, (12):8, c(X=6):2], responder004 rolled [(4):3, (6):5, f(8):3, (10):8, f(20):2].'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder004 gained initiative by turning down focus dice: f(8) from 3 to 1
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_reactToInitiative(
            array(),
            'Successfully gained initiative', array('gainedInitiative' => TRUE),
            $retval, $gameId, 2, 'focus', array(2), array('1'));
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerWithInitiativeIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array('Dizzy');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 gained initiative by turning down focus dice: f(8) from 3 to 1'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // responder003 rerolled a chance die, but did not gain initiative: c(6) rerolled 4 => 3
        $this->verify_api_reactToInitiative(
            array(3),
            'Failed to gain initiative', array('gainedInitiative' => FALSE),
            $retval, $gameId, 2, 'chance', array(1), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 rerolled a chance die, but did not gain initiative: c(6) rerolled 4 => 3'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // Now load the game as responder004 and check its state
        $_SESSION = $this->mock_test_user_login('responder004');
        $expData['currentPlayerIdx'] = 1;
        $expData['playerDataArray'][0]['playerColor'] = '#ddffdd';
        $expData['playerDataArray'][1]['playerColor'] = '#dd99dd';
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder003');
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test guards against regressions in the behavior (including action logging) of Berserk and Doppelganger dice which target Radioactive dice.
     * 0. Start a game with responder003 playing fendrin and responder004 playing gman97216
     * 1. responder003 set swing values: R=9, U=8
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [f(3):3, nD(R=9):1, (1):1, n(2):1, Bp(U=8):5], responder004 rolled [Hog%(4):1, Hog%(4):3, Hog%(4):4, Hog%(4):1]. responder004 has dice which are not counted for initiative due to die skills: [Hog%(4), Hog%(4), Hog%(4), Hog%(4)].
     * 2. responder003 performed Power attack using [nD(R=9):1] against [Hog%(4):1]; Defender Hog%(4) recipe changed to Hog%n(4), was captured; Attacker nD(R=9) showing 1 changed to Hog(4), which then split into: Hog(2) which grew into Hog(4) showing 4, and Hog(2) which grew into Hog(4) showing 3
     * 3. responder004 performed Skill attack using [Hog%(4):3,Hog%(4):1] against [Hog(4):4]; Defender Hog(4) was captured; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 3 => 5; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 1 => 5
     *    responder004's idle ornery dice rerolled at end of turn: Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 4 => 6
     * 4. responder003 performed Berserk attack using [Bp(U=8):5] against [Hog%(6):5]; Defender Hog%(6) was captured; Attacker Bp(U=8) showing 5 changed to p(U), which then split into: p(U=2) showing 1, and p(U=2) showing 2
     *    responder003's idle ornery dice rerolled at end of turn: Hog(4) changed size from 4 to 6 sides, recipe changed from Hog(4) to Hog(6), rerolled 3 => 4
     */
    public function test_interface_game_020() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 20;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // fendrin rolls 3 dice and gman97216 rolls 4
        $gameId = $this->verify_api_createGame(
            array(3, 1, 1, 1, 3, 4, 1),
            'responder003', 'responder004', 'fendrin', 'gman97216', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Berserk', 'Doppelganger', 'Focus', 'Mighty', 'Null', 'Ornery', 'Poison', 'Radioactive', 'Stinger'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('R' => array(2, 16), 'U' => array(8, 30));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'fendrin', 'recipe' => 'f(3) nD(R) (1) n(2) Bp(U)', 'originalRecipe' => 'f(3) nD(R) (1) n(2) Bp(U)', 'artFilename' => 'fendrin.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'gman97216', 'recipe' => 'Hog%(4) Hog%(4) Hog%(4) Hog%(4)', 'originalRecipe' => 'Hog%(4) Hog%(4) Hog%(4) Hog%(4)', 'artFilename' => 'gman97216.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 3, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(3)', 'description' => 'Focus 3-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Null', 'Doppelganger'), 'properties' => array(), 'recipe' => 'nD(R)', 'description' => 'Null Doppelganger R Swing Die'),
            array('value' => NULL, 'sides' => 1, 'skills' => array(), 'properties' => array(), 'recipe' => '(1)', 'description' => '1-sided die'),
            array('value' => NULL, 'sides' => 2, 'skills' => array('Null'), 'properties' => array(), 'recipe' => 'n(2)', 'description' => 'Null 2-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Berserk', 'Poison'), 'properties' => array(), 'recipe' => 'Bp(U)', 'description' => 'Berserk Poison U Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 set swing values: R=9, U=8
        $this->verify_api_submitDieValues(
            array(1, 5),
            $gameId, 1, array('R' => '9', 'U' => '8'), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Berserk');
        $expData['playerDataArray'][0]['roundScore'] = -6;
        $expData['playerDataArray'][1]['roundScore'] = 8;
        $expData['playerDataArray'][0]['sideScore'] = -9.3;
        $expData['playerDataArray'][1]['sideScore'] = 9.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] .= ' (with 9 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 8 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: R=9, U=8'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [f(3):3, nD(R=9):1, (1):1, n(2):1, Bp(U=8):5], responder004 rolled [Hog%(4):1, Hog%(4):3, Hog%(4):4, Hog%(4):1]. responder004 has dice which are not counted for initiative due to die skills: [Hog%(4), Hog%(4), Hog%(4), Hog%(4)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performed Power attack using [nD(R=9):1] against [Hog%(4):1]
        // [f(3):3, nD(R=9):1, (1):1, n(2):1, Bp(U=8):5] => [Hog%(4):1, Hog%(4):3, Hog%(4):4, Hog%(4):1]
        $this->verify_api_submitTurn(
            array(4, 3),
            'responder003 performed Power attack using [nD(R=9):1] against [Hog%(4):1]; Defender Hog%(4) recipe changed to Hog%n(4), was captured; Attacker nD(R=9) showing 1 changed to Hog(4), which then split into: Hog(2) which grew into Hog(4) showing 4, and Hog(2) which grew into Hog(4) showing 3. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(-2, 6, -5.3, 5.3),
            array(array(0, 1, array('value' => 4, 'sides' => 4, 'recipe' => 'Hog(4)', 'description' => 'Mighty Ornery Stinger 4-sided die', 'skills' => array('Mighty', 'Ornery', 'Stinger'), 'properties' => array('HasJustMorphed', 'HasJustSplit', 'HasJustGrown')))),
            array(array(1, 0)),
            array(),
            array(array(0, array('value' => 1, 'sides' => 4, 'recipe' => 'Hog%n(4)')))
        );
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 0, array(array('value' => '3', 'sides' => 4, 'recipe' => 'Hog(4)', 'description' => 'Mighty Ornery Stinger 4-sided die', 'skills'  => array('Mighty', 'Ornery', 'Stinger'), 'properties' => array('HasJustMorphed', 'HasJustSplit', 'HasJustGrown'))));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [nD(R=9):1] against [Hog%(4):1]; Defender Hog%(4) recipe changed to Hog%n(4), was captured; Attacker nD(R=9) showing 1 changed to Hog(4), which then split into: Hog(2) which grew into Hog(4) showing 4, and Hog(2) which grew into Hog(4) showing 3'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 performed Skill attack using [Hog%(4):3,Hog%(4):1] against [Hog(4):4]
        // [f(3):3, Hog(4):4, Hog(4):3, (1):1, n(2):1, Bp(U=8):5] <= [Hog%(4):3, Hog%(4):4, Hog%(4):1]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(5, 5, 6),
            'responder004 performed Skill attack using [Hog%(4):3,Hog%(4):1] against [Hog(4):4]; Defender Hog(4) was captured; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 3 => 5; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 1 => 5. responder004\'s idle ornery dice rerolled at end of turn: Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 4 => 6. ',
            $retval, array(array(0, 1), array(1, 0), array(1, 2)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill', 'Berserk'),
            array(-4, 13, -11.3, 11.3),
            array(array(0, 1, array('properties' => array())),
                  array(0, 2, array('properties' => array())),
                  array(1, 0, array('value' => 5, 'sides' => 6, 'recipe' => 'Hog%(6)', 'description' => 'Mighty Ornery Stinger Radioactive 6-sided die', 'properties' => array('HasJustGrown'))),
                  array(1, 1, array('value' => 6, 'sides' => 6, 'recipe' => 'Hog%(6)', 'description' => 'Mighty Ornery Stinger Radioactive 6-sided die', 'properties' => array('HasJustGrown', 'HasJustRerolledOrnery'))),
                  array(1, 2, array('value' => 5, 'sides' => 6, 'recipe' => 'Hog%(6)', 'description' => 'Mighty Ornery Stinger Radioactive 6-sided die', 'properties' => array('HasJustGrown')))),
            array(array(0, 1)),
            array(array(0, 0)),
            array(array(1, array('value' => 4, 'sides' => 4, 'recipe' => 'Hog(4)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [Hog%(4):3,Hog%(4):1] against [Hog(4):4]; Defender Hog(4) was captured; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 3 => 5; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 1 => 5'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004\'s idle ornery dice rerolled at end of turn: Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 4 => 6'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Berserk attack using [Bp(U=8):5] against [Hog%(6):5]
        // [f(3):3, Hog(4):3, (1):1, n(2):1, Bp(U=8):5] => [Hog%(6):5, Hog%(6):6, Hog%(6):5]
        $this->verify_api_submitTurn(
            array(1, 2, 4),
            'responder003 performed Berserk attack using [Bp(U=8):5] against [Hog%(6):5]; Defender Hog%(6) was captured; Attacker Bp(U=8) showing 5 changed to p(U=4), which then split into: p(U=2) showing 1, and p(U=2) showing 2. responder003\'s idle ornery dice rerolled at end of turn: Hog(4) changed size from 4 to 6 sides, recipe changed from Hog(4) to Hog(6), rerolled 3 => 4. ',
            $retval, array(array(0, 4), array(1, 2)),
            $gameId, 1, 'Berserk', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(7, 10, -2.0, 2.0),
            array(array(0, 1, array('value' => 4, 'sides' => 6, 'recipe' => 'Hog(6)', 'description' => 'Mighty Ornery Stinger 6-sided die', 'properties' => array('HasJustGrown', 'HasJustRerolledOrnery'))),
                  array(0, 4, array('value' => 1, 'sides' => '2', 'recipe' => 'p(U)', 'description' => 'Poison U Swing Die (with 2 sides)', 'properties' => array('HasJustSplit', 'JustPerformedBerserkAttack'), 'skills' => array('Poison'))),
                  array(1, 0, array('properties' => array())),
                  array(1, 1, array('properties' => array()))),
            array(array(1, 2)),
            array(array(1, 0)),
            array(array(0, array('value' => 5, 'sides' => 6, 'recipe' => 'Hog%(6)')))
        );
        $expData['playerDataArray'][0]['activeDieArray'][]= array('value' => 2, 'sides' => 2, 'recipe' => 'p(U)', 'description' => 'Poison U Swing Die (with 2 sides)', 'properties' => array('HasJustSplit', 'JustPerformedBerserkAttack'), 'skills' => array('Poison'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Berserk attack using [Bp(U=8):5] against [Hog%(6):5]; Defender Hog%(6) was captured; Attacker Bp(U=8) showing 5 changed to p(U=4), which then split into: p(U=2) showing 1, and p(U=2) showing 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003\'s idle ornery dice rerolled at end of turn: Hog(4) changed size from 4 to 6 sides, recipe changed from Hog(4) to Hog(6), rerolled 3 => 4'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }
}
