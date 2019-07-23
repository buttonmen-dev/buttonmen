<?php

/**
 * responder02Test: API tests of the buttonmen responder, file 02
 *
 * This file contains numbered game playback tests 21-40.
 */

require_once __DIR__.'/responderTestFramework.php';

class responder02Test extends responderTestFramework {

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test reproduces an internal error bug caused by zero-sided swing dice
     * 0. Start a game with responder003 playing slamkrypare and responder004 playing gman97216
     * 1. responder003 set swing values: Y=1
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [t(1):1, (10):1, (10):4, z(12):9, (Y=1):1], responder004 rolled [Hog%(4):3, Hog%(4):2, Hog%(4):1, Hog%(4):3]. responder003 has dice which are not counted for initiative due to die skills: [t(1)]. responder004 has dice which are not counted for initiative due to die skills: [Hog%(4), Hog%(4), Hog%(4), Hog%(4)].
     * 2. responder003 performed Power attack using [(Y=1):1] against [Hog%(4):1]
     * This triggers the internal error
     */
    public function test_interface_game_021() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 21;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // slamkrypare rolls 4 dice and gman97216 rolls 4
        $gameId = $this->verify_api_createGame(
            array(1, 1, 4, 9, 3, 2, 1, 3),
            'responder003', 'responder004', 'slamkrypare', 'gman97216', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Mighty', 'Ornery', 'Radioactive', 'Speed', 'Stinger', 'Trip'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('Y' => array(1, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'slamkrypare', 'recipe' => 't(1) (10) (10) z(12) (Y)', 'originalRecipe' => 't(1) (10) (10) z(12) (Y)', 'artFilename' => 'slamkrypare.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'gman97216', 'recipe' => 'Hog%(4) Hog%(4) Hog%(4) Hog%(4)', 'originalRecipe' => 'Hog%(4) Hog%(4) Hog%(4) Hog%(4)', 'artFilename' => 'gman97216.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(1)', 'description' => 'Trip 1-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(12)', 'description' => 'Speed 12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(Y)', 'description' => 'Y Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 set swing values: Y=1
        $this->verify_api_submitDieValues(
            array(1),
            $gameId, 1, array('Y' => '1'), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Speed', 'Trip');
        $expData['playerDataArray'][0]['roundScore'] = 17;
        $expData['playerDataArray'][1]['roundScore'] = 8;
        $expData['playerDataArray'][0]['sideScore'] = 6.0;
        $expData['playerDataArray'][1]['sideScore'] = -6.0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 1 side)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: Y=1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [t(1):1, (10):1, (10):4, z(12):9, (Y=1):1], responder004 rolled [Hog%(4):3, Hog%(4):2, Hog%(4):1, Hog%(4):3]. responder003 has dice which are not counted for initiative due to die skills: [t(1)]. responder004 has dice which are not counted for initiative due to die skills: [Hog%(4), Hog%(4), Hog%(4), Hog%(4)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 Power attack using [(Y=1):1] against [Hog%(4):1]
        // [t(1):1, (10):1, (10):4, z(12):9, (Y=1):1] => [Hog%(4):3, Hog%(4):2, Hog%(4):1, Hog%(4):3]
        $this->verify_api_submitTurn(
            array(1, 0),
            'responder003 performed Power attack using [(Y=1):1] against [Hog%(4):1]; Defender Hog%(4) was captured; Attacker (Y=1) showing 1 split into: (Y=1) showing 1, and (Y=0) showing 0. ',
            $retval, array(array(0, 4), array(1, 2)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(21, 6, 10.0, -10.0),
            array(array(0, 4, array())), // not sure any aspect of this die actually changes in this attack
            array(array(1, 2)),
            array(),
            array(array(0, array('value' => 1, 'sides' => 4, 'recipe' => 'Hog%(4)')))
        );
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('HasJustSplit');
        $expData['playerDataArray'][0]['activeDieArray'][]= array('value' => 0, 'sides' => 0, 'recipe' => '(Y)', 'description' => 'Y Swing Die (with 0 sides)', 'properties' => array('HasJustSplit'), 'skills' => array());
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(Y=1):1] against [Hog%(4):1]; Defender Hog%(4) was captured; Attacker (Y=1) showing 1 split into: (Y=1) showing 1, and (Y=0) showing 0'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test reproduces an internal error bug caused by zero-sided twin swing dice
     * 0. Start a game with responder003 playing Pjack and responder004 playing gman97216
     * 1. responder003 set swing values: T=2
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [q(2):1, z(3):2, (5):3, s(23):11, t(T=2,T=2):3], responder004 rolled [Hog%(4):4, Hog%(4):1, Hog%(4):2, Hog%(4):4]. responder003 has dice which are not counted for initiative due to die skills: [t(T=2,T=2)]. responder004 has dice which are not counted for initiative due to die skills: [Hog%(4), Hog%(4), Hog%(4), Hog%(4)].
     * 2. responder003 performed Trip attack using [t(T=2,T=2):3] against [Hog%(4):4]; Attacker t(T=2,T=2) rerolled 3 => 3; Defender Hog%(4) recipe changed to Hog%(6), rerolled 4 => 1, was captured
     * 3. responder004 performed Skill attack using [Hog%(4):1,Hog%(4):2] against [(5):3]; Defender (5) was captured; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 1 => 3; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 2 => 2
     *    responder004's idle ornery dice rerolled at end of turn: Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 4 => 2
     * 4. responder003 performed Power attack using [t(T=1,T=1):2] against [Hog%(6):2]
     */
    public function test_interface_game_022() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 22;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Pjack rolls 4 dice and gman97216 rolls 4
        $gameId = $this->verify_api_createGame(
            array(1, 2, 3, 11, 4, 1, 2, 4),
            'responder003', 'responder004', 'Pjack', 'gman97216', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Mighty', 'Ornery', 'Queer', 'Radioactive', 'Shadow', 'Speed', 'Stinger', 'Trip'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('T' => array(2, 12));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'Pjack', 'recipe' => 'q(2) z(3) (5) s(23) t(T,T)', 'originalRecipe' => 'q(2) z(3) (5) s(23) t(T,T)', 'artFilename' => 'pjack.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'gman97216', 'recipe' => 'Hog%(4) Hog%(4) Hog%(4) Hog%(4)', 'originalRecipe' => 'Hog%(4) Hog%(4) Hog%(4) Hog%(4)', 'artFilename' => 'gman97216.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 2, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(2)', 'description' => 'Queer 2-sided die'),
            array('value' => NULL, 'sides' => 3, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(3)', 'description' => 'Speed 3-sided die'),
            array('value' => NULL, 'sides' => 5, 'skills' => array(), 'properties' => array(), 'recipe' => '(5)', 'description' => '5-sided die'),
            array('value' => NULL, 'sides' => 23, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(23)', 'description' => 'Shadow 23-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Trip'), 'properties' => array('Twin'), 'recipe' => 't(T,T)', 'description' => 'Trip Twin T Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Mighty', 'Ornery', 'Stinger', 'Radioactive'), 'properties' => array(), 'recipe' => 'Hog%(4)', 'description' => 'Mighty Ornery Stinger Radioactive 4-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 set swing values: T=2
        $this->verify_api_submitDieValues(
            array(2, 1),
            $gameId, 1, array('T' => '2'), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow', 'Speed', 'Trip');
        $expData['playerDataArray'][0]['roundScore'] = 18.5;
        $expData['playerDataArray'][1]['roundScore'] = 8;
        $expData['playerDataArray'][0]['sideScore'] = 7.0;
        $expData['playerDataArray'][1]['sideScore'] = -7.0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('Twin');
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (both with 2 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['subdieArray'] = array(array('sides' => 2, 'value' => 2), array('sides' => 2, 'value' => 1));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: T=2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [q(2):1, z(3):2, (5):3, s(23):11, t(T=2,T=2):3], responder004 rolled [Hog%(4):4, Hog%(4):1, Hog%(4):2, Hog%(4):4]. responder003 has dice which are not counted for initiative due to die skills: [t(T=2,T=2)]. responder004 has dice which are not counted for initiative due to die skills: [Hog%(4), Hog%(4), Hog%(4), Hog%(4)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performed Trip attack using [t(T=2,T=2):3] against [Hog%(4):4] (successfully)
        // [q(2):1, z(3):2, (5):3, s(23):11, t(T=2,T=2):3] => [Hog%(4):4, Hog%(4):1, Hog%(4):2, Hog%(4):4]
        $this->verify_api_submitTurn(
            array(1, 2, 1, 1, 1, 1, 1),
            'responder003 performed Trip attack using [t(T=2,T=2):3] against [Hog%(4):4]; Attacker t(T=2,T=2) rerolled 3 => 3; Defender Hog%(4) recipe changed to Hog%(6), rerolled 4 => 1, was captured; Attacker t(T=2,T=2) showing 3 split into: t(T=1,T=1) showing 2, and t(T=1,T=1) showing 2. ',
            $retval, array(array(0, 4), array(1, 0)),
            $gameId, 1, 'Trip', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(24.5, 6, 12.3, -12.3),
            array(array(0, 4, array('value' => 2, 'sides' => 2, 'properties' => array('JustPerformedTripAttack', 'HasJustSplit', 'Twin'), 'description' => 'Trip Twin T Swing Die (both with 1 side)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 1, 'value' => 1))))),
            array(array(1, 0)),
            array(),
            array(array(0, array('value' => 1, 'sides' => 6, 'recipe' => 'Hog%(6)')))
        );
        $expData['playerDataArray'][0]['activeDieArray'][]= array('value' => 2, 'sides' => 2, 'recipe' => 't(T,T)', 'description' => 'Trip Twin T Swing Die (both with 1 side)', 'properties' => array('JustPerformedTripAttack', 'HasJustSplit', 'Twin'), 'skills' => array('Trip'), 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 1, 'value' => 1)));
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'][] = 'HasJustGrown';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [t(T=2,T=2):3] against [Hog%(4):4]; Attacker t(T=2,T=2) rerolled 3 => 3; Defender Hog%(4) recipe changed to Hog%(6), rerolled 4 => 1, was captured; Attacker t(T=2,T=2) showing 3 split into: t(T=1,T=1) showing 2, and t(T=1,T=1) showing 2'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 03 - responder004 performed Skill attack using [Hog%(4):1,Hog%(4):2] against [(5):3]
        // [q(2):1, z(3):2, (5):3, s(23):11, t(T=1,T=1):2, t(T=1,T=1):2] <= [Hog%(4):1, Hog%(4):2, Hog%(4):4]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3, 2, 2),
            'responder004 performed Skill attack using [Hog%(4):1,Hog%(4):2] against [(5):3]; Defender (5) was captured; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 1 => 3; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 2 => 2. responder004\'s idle ornery dice rerolled at end of turn: Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 4 => 2. ',
            $retval, array(array(0, 2), array(1, 0), array(1, 1)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill', 'Shadow', 'Speed', 'Trip'),
            array(22, 14, 5.3, -5.3),
            array(array(0, 4, array('properties' => array('Twin'))),
                  array(0, 5, array('properties' => array('Twin'))),
                  array(1, 0, array('value' => 3, 'sides' => 6, 'recipe' => 'Hog%(6)', 'description' => 'Mighty Ornery Stinger Radioactive 6-sided die', 'properties' => array('HasJustGrown'))),
                  array(1, 1, array('value' => 2, 'sides' => 6, 'recipe' => 'Hog%(6)', 'description' => 'Mighty Ornery Stinger Radioactive 6-sided die', 'properties' => array('HasJustGrown'))),
                  array(1, 2, array('value' => 2, 'sides' => 6, 'recipe' => 'Hog%(6)', 'description' => 'Mighty Ornery Stinger Radioactive 6-sided die', 'properties' => array('HasJustGrown', 'HasJustRerolledOrnery')))),
            array(array(0, 2)),
            array(array(0, 0)),
            array(array(1, array('value' => 3, 'sides' => 5, 'recipe' => '(5)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [Hog%(4):1,Hog%(4):2] against [(5):3]; Defender (5) was captured; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 1 => 3; Attacker Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 2 => 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004\'s idle ornery dice rerolled at end of turn: Hog%(4) changed size from 4 to 6 sides, recipe changed from Hog%(4) to Hog%(6), rerolled 4 => 2'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder003 Power attack using [t(T=1,T=1):2] against [Hog%(6):2]
        // [q(2):1, z(3):2, s(23):11, t(T=1,T=1):2, t(T=1,T=1):2] => [Hog%(6):3, Hog%(6):2, Hog%(6):2]
        $this->verify_api_submitTurn(
            array(1, 0, 0, 1),
            'responder003 performed Power attack using [t(T=1,T=1):2] against [Hog%(6):2]; Defender Hog%(6) was captured; Attacker t(T=1,T=1) showing 2 split into: t(T=1,T=0) showing 1, and t(T=0,T=1) showing 1. ',
            $retval, array(array(0, 4), array(1, 1)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(28, 11, 11.3, -11.3),
            array(array(0, 4, array('value' => 1, 'sides' => 1, 'properties' => array('HasJustSplit', 'Twin'), 'description' => 'Trip Twin T Swing Die (with 1 and 0 sides)', 'subdieArray' => array(array('sides' => 1, 'value' => 1), array('sides' => 0, 'value' => 0)))),
                  array(1, 0, array('properties' => array())),
                  array(1, 2, array('properties' => array()))),
            array(array(1, 1)),
            array(array(1, 0)),
            array(array(0, array('value' => 1, 'sides' => 2, 'recipe' => 'Hog%(6)')))
        );

        $expData['playerDataArray'][0]['activeDieArray'][] = array('value' => 1, 'sides' => 1, 'skills' => array('Trip'), 'properties' => array('HasJustSplit', 'Twin'), 'recipe' => 't(T,T)', 'description' => 'Trip Twin T Swing Die (with 0 and 1 sides)', 'subdieArray' => array(array('sides' => 0, 'value' => 0), array('sides' => 1, 'value' => 1)));
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 6;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [t(T=1,T=1):2] against [Hog%(6):2]; Defender Hog%(6) was captured; Attacker t(T=1,T=1) showing 2 split into: t(T=1,T=0) showing 1, and t(T=0,T=1) showing 1'));
        $expData['gameActionLogCount'] += 1;

        // This throws the internal error
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test reproduces a bug in which TheFool's sides have different sizes on reroll.
     * 0. Start a game with responder003 playing BlackOmega and responder004 playing TheFool
     * 1. responder004 set swing values: R=2
     *    responder004 won initiative for round 1. Initial die values: responder003 rolled [tm(6):3, f(8):8, g(10):7, z(10):1, sF(20):3], responder004 rolled [v(5):2, v(10):6, vq(10):1, vs(15):5, s(R=2,R=2)?:2]. responder003 has dice which are not counted for initiative due to die skills: [tm(6), g(10)].
     * 2. responder003 chose not to try to gain initiative using chance or focus dice
     * 3. responder004 performed Shadow attack using [s(R=2,R=2)?:2] against [sF(20):3]; Defender sF(20) was captured; Attacker s(R=2,R=2)? changed size from 4 to 16 sides, recipe changed from s(R=2,R=2)? to s(R=4,R=10)?, rerolled 2 => 4
     * 4. responder003 performed Skill attack using [tm(6):3,z(10):1] against [s(R=8,R=8)?:4]; Defender s(R=8,R=8)? was captured; Attacker tm(6) changed size from 6 to 16 sides, recipe changed from tm(6) to tm(R=8,R=8), rerolled 3 => 6; Attacker z(10) rerolled 1 => 3
     * 5. responder004 performed Power attack using [v(10):6] against [tm(R=8,R=8):6]; Defender tm(R=8,R=8) recipe changed to tmv(R=8,R=8), was captured; Attacker v(10) rerolled 6 => 2
     */
    public function test_interface_game_023() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 23;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // BlackOmega rolls 5 dice and TheFool rolls 4
        $gameId = $this->verify_api_createGame(
            array(3, 8, 7, 1, 3, 2, 6, 1, 5),
            'responder003', 'responder004', 'BlackOmega', 'TheFool', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Fire', 'Focus', 'Mood', 'Morphing', 'Queer', 'Shadow', 'Speed', 'Stinger', 'Trip', 'Value'));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('R' => array(2, 16));
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'BlackOmega', 'recipe' => 'tm(6) f(8) g(10) z(10) sF(20)', 'originalRecipe' => 'tm(6) f(8) g(10) z(10) sF(20)', 'artFilename' => 'blackomega.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'TheFool', 'recipe' => 'v(5) v(10) vq(10) vs(15) s(R,R)?', 'originalRecipe' => 'v(5) v(10) vq(10) vs(15) s(R,R)?', 'artFilename' => 'thefool.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 6, 'skills' => array('Trip', 'Morphing'), 'properties' => array(), 'recipe' => 'tm(6)', 'description' => 'Trip Morphing 6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(8)', 'description' => 'Focus 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Stinger'), 'properties' => array(), 'recipe' => 'g(10)', 'description' => 'Stinger 10-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(10)', 'description' => 'Speed 10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Shadow', 'Fire'), 'properties' => array(), 'recipe' => 'sF(20)', 'description' => 'Shadow Fire 20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 5, 'skills' => array('Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'v(5)', 'description' => 'Value 5-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'v(10)', 'description' => 'Value 10-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Value', 'Queer'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'vq(10)', 'description' => 'Value Queer 10-sided die'),
            array('value' => NULL, 'sides' => 15, 'skills' => array('Value', 'Shadow'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'vs(15)', 'description' => 'Value Shadow 15-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Shadow', 'Mood'), 'properties' => array('Twin'), 'recipe' => 's(R,R)?', 'description' => 'Shadow Twin R Mood Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 set swing values: R=2
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(1, 1),
            $gameId, 1, array('R' => '2'), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'REACT_TO_INITIATIVE';
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['activePlayerIdx'] = NULL;
        $expData['playerDataArray'][0]['roundScore'] = 27;
        $expData['playerDataArray'][1]['roundScore'] = 9;
        $expData['playerDataArray'][0]['sideScore'] = 12.0;
        $expData['playerDataArray'][1]['sideScore'] = -12.0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] .= ' (both with 2 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['subdieArray'] = array(array('sides' => 2, 'value' => 1), array('sides' => 2, 'value' => 1));
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: R=2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [tm(6):3, f(8):8, g(10):7, z(10):1, sF(20):3], responder004 rolled [v(5):2, v(10):6, vq(10):1, vs(15):5, s(R=2,R=2)?:2]. responder003 has dice which are not counted for initiative due to die skills: [tm(6), g(10)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 chose not to try to gain initiative using chance or focus dice
        $this->verify_api_reactToInitiative(
            array(),
            'Failed to gain initiative', array('gainedInitiative' => FALSE),
            $retval, $gameId, 1, 'decline', NULL, NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose not to try to gain initiative using chance or focus dice'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 03 - responder004 performed Shadow attack using [s(R=2,R=2)?:2] against [sF(20):3]
        // [tm(6):3, f(8):8, g(10):7, z(10):1, sF(20):3] <= [v(5):2, v(10):6, vq(10):1, vs(15):5, s(R=2,R=2)?:2]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            // * the first value changes the mood swing die size from 4 to 8
            // * the second value changes the value rolled for subdie 1
            // * the third value changes the value rolled for subdie 2
            array(3, 1, 3),
            'responder004 performed Shadow attack using [s(R=2,R=2)?:2] against [sF(20):3]; Defender sF(20) was captured; Attacker s(R=2,R=2)? changed size from 4 to 16 sides, recipe changed from s(R=2,R=2)? to s(R=8,R=8)?, rerolled 2 => 4. ',
            $retval, array(array(0, 4), array(1, 4)),
            $gameId, 1, 'Shadow', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill', 'Speed', 'Trip'),
            array(17, 35, -12.0, 12.0),
            array(array(1, 4, array('value' => 4, 'sides' => 16, 'properties' => array('Twin'), 'description' => 'Shadow Twin R Mood Swing Die (both with 8 sides)', 'subdieArray' => array(array('sides' => 8, 'value' => 1), array('sides' => 8, 'value' => 3))))),
            array(array(0, 4)),
            array(),
            array(array(1, array('value' => 3, 'sides' => 20, 'recipe' => 'sF(20)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Shadow attack using [s(R=2,R=2)?:2] against [sF(20):3]; Defender sF(20) was captured; Attacker s(R=2,R=2)? changed size from 4 to 16 sides, recipe changed from s(R=2,R=2)? to s(R=8,R=8)?, rerolled 2 => 4'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Skill attack using [tm(6):3, z(10):1] against [s(R=8,R=8)?:4]
        // [tm(6):3, f(8):8, g(10):7, z(10):1] => [v(5):2, v(10):6, vq(10):1, vs(15):5, s(R=8,R=8)?:4]
        $this->verify_api_submitTurn(
            // * 1 (4): roll value of left R
            // * 2 (2): roll value of right R
            // * 3 (3): roll value of z(10)
            array(4, 2, 3),
            'responder003 performed Skill attack using [tm(6):3,z(10):1] against [s(R=8,R=8)?:4]; Defender s(R=8,R=8)? was captured; Attacker tm(6) changed size from 6 to 16 sides, recipe changed from tm(6) to tm(R=8,R=8), rerolled 3 => 6; Attacker z(10) rerolled 1 => 3. ',
            $retval, array(array(0, 0), array(0, 3), array(1, 4)),
            $gameId, 1, 'Skill', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill', 'Shadow'),
            array(38, 27, 7.3, -7.3),
            array(array(0, 0, array('value' => 6, 'sides' => 16, 'properties' => array('HasJustMorphed', 'Twin'), 'recipe' => 'tm(R,R)', 'description' => 'Trip Morphing Twin R Swing Die (both with 8 sides)', 'subdieArray' => array(array('sides' => 8, 'value' => 4), array('sides' => 8, 'value' => 2))),),
                  array(0, 3, array('value' => 3))),
            array(array(1, 4)),
            array(),
            array(array(0, array('value' => 4, 'sides' => 16, 'recipe' => 's(R,R)?', 'properties' => array('WasJustCaptured', 'Twin'), 'subdieArray' => array(array('sides' => 8, 'value' => 1), array('sides' => 8, 'value' => 3)))))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [tm(6):3,z(10):1] against [s(R=8,R=8)?:4]; Defender s(R=8,R=8)? was captured; Attacker tm(6) changed size from 6 to 16 sides, recipe changed from tm(6) to tm(R=8,R=8), rerolled 3 => 6; Attacker z(10) rerolled 1 => 3'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array('WasJustCaptured', 'Twin');
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder004 performed Power attack using [v(10):6] against [tm(R=8,R=8):6]
        // [tm(R=8,R=8):6, f(8):8, g(10):7, z(10):1] <= [v(5):2, v(10):6, vq(10):1, vs(15):5]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2),
            'responder004 performed Power attack using [v(10):6] against [tm(R=8,R=8):6]; Defender tm(R=8,R=8) recipe changed to tmv(R=8,R=8), was captured; Attacker v(10) rerolled 6 => 2. ',
            $retval, array(array(0, 0), array(1, 1)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill', 'Speed'),
            array(30, 31, -0.7, 0.7),
            array(array(1, 1, array('value' => 2))),
            array(array(0, 0)),
            array(array(0, 0)),
            array(array(1, array('value' => 6, 'sides' => 16, 'recipe' => 'tmv(R,R)')))
        );
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array('Twin');
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array('ValueRelevantToScore', 'WasJustCaptured', 'Twin');
        $expData['playerDataArray'][1]['capturedDieArray'][1]['subdieArray'] = array(array('sides' => 8, 'value' => 4), array('sides' => 8, 'value' => 2));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [v(10):6] against [tm(R=8,R=8):6]; Defender tm(R=8,R=8) recipe changed to tmv(R=8,R=8), was captured; Attacker v(10) rerolled 6 => 2'));
        $expData['gameActionLogCount'] += 1;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

     // 5. responder004 performed Power attack using [v(10):6] against [tm(R=8,R=8):6]; Defender tm(R=8,R=8) recipe changes from tm(R=8,R=8):6 to tmv(R=8,R=8):6, was captured; Attacker v(10) rerolled 6 => 2
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game provides examples of auxiliary with different dice,
     * active player waiting for the other player's auxiliary choice,
     * and decline of auxiliary dice, for use in UI testing
     */
    public function test_interface_game_024() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 24;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // In a game with auxiliary dice, no dice are rolled until after auxiliary selection
        $gameId = $this->verify_api_createGame(
            array(),
            'responder003', 'responder004', 'Merlin', 'Ein', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'CHOOSE_AUXILIARY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Auxiliary', 'Focus', 'Shadow', 'Trip'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20), 'Y' => array(1, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20), 'Y' => array(1, 20));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Merlin', 'recipe' => '(2) (4) s(10) s(20) (X) +s(X)', 'originalRecipe' => '(2) (4) s(10) s(20) (X) +s(X)', 'artFilename' => 'merlin.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Ein', 'recipe' => '(8) (8) f(8) t(8) (X) +(Y)', 'originalRecipe' => '(8) (8) f(8) t(8) (X) +(Y)', 'artFilename' => 'ein.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(10)', 'description' => 'Shadow 10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(20)', 'description' => 'Shadow 20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Auxiliary', 'Shadow'), 'properties' => array(), 'recipe' => '+s(X)', 'description' => 'Auxiliary Shadow X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(8)', 'description' => 'Focus 8-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(8)', 'description' => 'Trip 8-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Auxiliary'), 'properties' => array(), 'recipe' => '+(Y)', 'description' => 'Auxiliary Y Swing Die'),
        );

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // now load the game as non-participating player responder001 and check its state
        $_SESSION = $this->mock_test_user_login('responder001');
        $expData['dieBackgroundType'] = 'symmetric';
        $this->verify_api_loadGameData_as_nonparticipant($expData, $gameId, 10);
        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['dieBackgroundType'] = 'realistic';

        ////////////////////
        // Move 01 - responder003 chose to use auxiliary die +s(X) in this game
        // no dice are rolled when the first player chooses auxiliary
        $this->verify_api_reactToAuxiliary(
            array(),
            'Chose to add auxiliary die',
            $gameId, 'add', 5);

        // the courtesy die is not available after this
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][5]['properties'] = array('AddAuxiliary');
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20), 'Y' => array(1, 20));

        // now load the game and check its state
        $expData['gameActionLogCount'] = 1;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 chose not to use auxiliary die +(Y) in this game
        // 4 of Merlin's dice, and 4 of Ein's, are rolled initially
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_reactToAuxiliary(
            array(1, 1, 4, 15, 8, 2, 4, 8),
            'responder004 chose not to use auxiliary dice in this game: neither player will get an auxiliary die. ',
            $gameId, 'decline', NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        // #1216: Auxiliary shouldn't be removed from the list of game skills
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Focus', 'Shadow', 'Trip'));
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['button']['recipe'] = '(2) (4) s(10) s(20) (X)';
        $expData['playerDataArray'][1]['button']['recipe'] = '(8) (8) f(8) t(8) (X)';
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 5, 1);
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 5, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to use auxiliary die +s(X) in this game'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 chose not to use auxiliary dice in this game: neither player will get an auxiliary die'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game tries to reproduce a bug with trip twin dice.
     */
    public function test_interface_game_025() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 25;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // each Pjack rolls 4 dice
        $gameId = $this->verify_api_createGame(
            array(1, 3, 5, 6, 2, 1, 1, 16),
            'responder003', 'responder004', 'Pjack', 'Pjack', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Queer', 'Shadow', 'Speed', 'Trip'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('T' => array(2, 12));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('T' => array(2, 12));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Pjack', 'recipe' => 'q(2) z(3) (5) s(23) t(T,T)', 'originalRecipe' => 'q(2) z(3) (5) s(23) t(T,T)', 'artFilename' => 'pjack.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Pjack', 'recipe' => 'q(2) z(3) (5) s(23) t(T,T)', 'originalRecipe' => 'q(2) z(3) (5) s(23) t(T,T)', 'artFilename' => 'pjack.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 2, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(2)', 'description' => 'Queer 2-sided die'),
            array('value' => NULL, 'sides' => 3, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(3)', 'description' => 'Speed 3-sided die'),
            array('value' => NULL, 'sides' => 5, 'skills' => array(), 'properties' => array(), 'recipe' => '(5)', 'description' => '5-sided die'),
            array('value' => NULL, 'sides' => 23, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(23)', 'description' => 'Shadow 23-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Trip'), 'properties' => array('Twin'), 'recipe' => 't(T,T)', 'description' => 'Trip Twin T Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 2, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(2)', 'description' => 'Queer 2-sided die'),
            array('value' => NULL, 'sides' => 3, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(3)', 'description' => 'Speed 3-sided die'),
            array('value' => NULL, 'sides' => 5, 'skills' => array(), 'properties' => array(), 'recipe' => '(5)', 'description' => '5-sided die'),
            array('value' => NULL, 'sides' => 23, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(23)', 'description' => 'Shadow 23-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Trip'), 'properties' => array('Twin'), 'recipe' => 't(T,T)', 'description' => 'Trip Twin T Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 set swing values: T=2
        $this->verify_api_submitDieValues(
            array(2, 1),
            $gameId, 1, array('T' => '2'), NULL);

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray']['4']['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray']['4']['description'] .= ' (both with 2 sides)';
        $expData['playerDataArray'][0]['activeDieArray']['4']['subdieArray'] = array(array('sides' => 2), array('sides' => 2));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set die sizes'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 set swing values: T=2
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(1, 2),
            $gameId, 1, array('T' => '2'), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Speed', 'Trip');
        $expData['playerDataArray'][0]['roundScore'] = 18.5;
        $expData['playerDataArray'][0]['sideScore'] = 0.0;
        $expData['playerDataArray'][1]['roundScore'] = 18.5;
        $expData['playerDataArray'][1]['sideScore'] = 0.0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][4]['subdieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['subdieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 16;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray']['4']['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray']['4']['description'] .= ' (both with 2 sides)';
        $expData['playerDataArray'][1]['activeDieArray']['4']['subdieArray'] = array(array('sides' => 2, 'value' => 1), array('sides' => 2, 'value' => 2));
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['gameActionLog'][0]['message'] = 'responder003 set swing values: T=2';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: T=2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [q(2):1, z(3):3, (5):5, s(23):6, t(T=2,T=2):3], responder004 rolled [q(2):2, z(3):1, (5):1, s(23):16, t(T=2,T=2):3]. responder003 has dice which are not counted for initiative due to die skills: [t(T=2,T=2)]. responder004 has dice which are not counted for initiative due to die skills: [t(T=2,T=2)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 performed Trip attack using [t(T=2,T=2):3] against [(5):5]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2, 2, 4),
            'responder004 performed Trip attack using [t(T=2,T=2):3] against [(5):5]; Attacker t(T=2,T=2) rerolled 3 => 4; Defender (5) rerolled 5 => 4, was captured. ',
            $retval, array(array(0, 2), array(1, 4)),
            $gameId, 1, 'Trip', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill', 'Shadow', 'Speed', 'Trip'),
            array(16, 23.5, -5.0, 5.0),
            array(array(1, 4, array('value' => 4, 'properties' => array('JustPerformedTripAttack', 'Twin')))),
            array(array(0, 2)),
            array(),
            array(array(1, array('value' => 4, 'sides' => 5, 'recipe' => '(5)', 'properties' => array('WasJustCaptured'))))
        );
        $expData['playerDataArray'][1]['activeDieArray'][4]['subdieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][4]['subdieArray'][1]['value'] = 2;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Trip attack using [t(T=2,T=2):3] against [(5):5]; Attacker t(T=2,T=2) rerolled 3 => 4; Defender (5) rerolled 5 => 4, was captured'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game tests RandomBMMixed and verifies that trip attacks fail against too-large shadow maximum dice
     */
    public function test_interface_game_026() {
        global $RANDOMBM_SKILL;

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 26;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        $gameId = $this->verify_api_createGame(
            array(
                'bm_rand' => array(
                    4, 3, 0, 3, 4,     // die sizes for r3: 4, 10, 10, 12, 12 (these get sorted)
                    1, 3, 0, 2, 0, 2,  // distribution of skills onto dice for r3
                    1, 2, 2, 3, 5,     // die sizes for r4
                    1, 3, 1, 4, 0, 4,  // distribution of skills onto dice for r4
                    4, 3, 3, 5, 5,     // initial die rolls for r3
                    6, 5, 7,           // initial die rolls for r4
                ),
                'bm_skill_rand' => array(
                    $RANDOMBM_SKILL['c'], $RANDOMBM_SKILL['n'], $RANDOMBM_SKILL['t'],
                    $RANDOMBM_SKILL['s'], $RANDOMBM_SKILL['M'], $RANDOMBM_SKILL['o'],
                ),
            ),
            'responder003', 'responder004', 'RandomBMMixed', 'RandomBMMixed', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Chance', 'Maximum', 'Null', 'Ornery', 'Shadow', 'Trip', 'RandomBMMixed'));
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Trip');
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['playerDataArray'][0]['roundScore'] = 17;
        $expData['playerDataArray'][1]['roundScore'] = 26;
        $expData['playerDataArray'][0]['sideScore'] = -6.0;
        $expData['playerDataArray'][1]['sideScore'] = 6.0;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'RandomBMMixed', 'recipe' => 'nt(4) c(10) nt(10) c(12) (12)', 'originalRecipe' => 'nt(4) c(10) nt(10) c(12) (12)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'RandomBMMixed', 'recipe' => 'o(6) Ms(8) (8) s(10) Mo(20)', 'originalRecipe' => 'o(6) Ms(8) (8) s(10) Mo(20)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 4, 'sides' => 4, 'skills' => array('Null', 'Trip'), 'properties' => array(), 'recipe' => 'nt(4)', 'description' => 'Null Trip 4-sided die'),
            array('value' => 3, 'sides' => 10, 'skills' => array('Chance'), 'properties' => array(), 'recipe' => 'c(10)', 'description' => 'Chance 10-sided die'),
            array('value' => 3, 'sides' => 10, 'skills' => array('Null', 'Trip'), 'properties' => array(), 'recipe' => 'nt(10)', 'description' => 'Null Trip 10-sided die'),
            array('value' => 5, 'sides' => 12, 'skills' => array('Chance'), 'properties' => array(), 'recipe' => 'c(12)', 'description' => 'Chance 12-sided die'),
            array('value' => 5, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 6, 'sides' => 6, 'skills' => array('Ornery'), 'properties' => array(), 'recipe' => 'o(6)', 'description' => 'Ornery 6-sided die'),
            array('value' => 8, 'sides' => 8, 'skills' => array('Maximum', 'Shadow'), 'properties' => array(), 'recipe' => 'Ms(8)', 'description' => 'Maximum Shadow 8-sided die'),
            array('value' => 5, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => 7, 'sides' => 10, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(10)', 'description' => 'Shadow 10-sided die'),
            array('value' => 20, 'sides' => 20, 'skills' => array('Maximum', 'Ornery'), 'properties' => array(), 'recipe' => 'Mo(20)', 'description' => 'Maximum Ornery 20-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [nt(4):4, c(10):3, nt(10):3, c(12):5, (12):5], responder004 rolled [o(6):6, Ms(8):8, (8):5, s(10):7, Mo(20):20]. responder003 has dice which are not counted for initiative due to die skills: [nt(4), nt(10)].'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - verify that a trip attack by the smaller trip die against the Ms(8) is rejected
        // [nt(4):4, c(10):3, nt(10):3, c(12):5, (12):5] => [o(6):6, Ms(8):8, (8):5, s(10):7, Mo(20):20]
        $this->verify_api_submitTurn_failure(
            array(),
            'The attacking die cannot roll high enough to capture the target die',
            $retval, array(array(0, 0), array(1, 1)),
            $gameId, 1, 'Trip', 0, 1, '');

        // A trip attack by the larger trip die against the Ms(8) should be allowed
        $this->verify_api_submitTurn(
            array(7),
            'responder003 performed Trip attack using [nt(10):3] against [Ms(8):8]; Attacker nt(10) rerolled 3 => 7; Defender Ms(8) rerolled 8 => 8, was not captured. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 1, 'Trip', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill', 'Shadow'),
            array(17, 26, -6.0, 6.0),
            array(array(0, 2, array('value' => 7, 'properties' => array('JustPerformedTripAttack', 'JustPerformedUnsuccessfulAttack')))),
            array(),
            array(),
            array()
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [nt(10):3] against [Ms(8):8]; Attacker nt(10) rerolled 3 => 7; Defender Ms(8) rerolled 8 => 8, was not captured'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game is a regression test for the behavior of warrior dice
     */
    public function test_interface_game_027() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 27;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Shadow Warriors rolls 7 dice, Fernworthy rolls 8 (warrior dice always start with max values, but they roll first anyway)
        $gameId = $this->verify_api_createGame(
            array(1, 1, 1, 1, 1, 1, 1, 1, 2, 7, 4, 1, 1, 1, 1),
            'responder003', 'responder004', 'Shadow Warriors', 'Fernworthy', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Speed', 'Warrior'));
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['button'] = array('name' => 'Shadow Warriors', 'recipe' => '(1) (2) `(4) `(6) `(8) `(10) `(12)', 'originalRecipe' => '(1) (2) `(4) `(6) `(8) `(10) `(12)', 'artFilename' => 'shadowwarriors.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'fernworthy', 'recipe' => '(1) (2) z(12) z(20) `(4) `(6) `(8) `(10)', 'originalRecipe' => '(1) (2) z(12) z(20) `(4) `(6) `(8) `(10)', 'artFilename' => 'fernworthy.png');
        $expData['playerDataArray'][0]['roundScore'] = 1.5;
        $expData['playerDataArray'][1]['roundScore'] = 17.5;
        $expData['playerDataArray'][0]['sideScore'] = -10.7;
        $expData['playerDataArray'][1]['sideScore'] = 10.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 1, 'skills' => array(), 'properties' => array(), 'recipe' => '(1)', 'description' => '1-sided die'),
            array('value' => 1, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
            array('value' => 4, 'sides' => 4, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(4)', 'description' => 'Warrior 4-sided die'),
            array('value' => 6, 'sides' => 6, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(6)', 'description' => 'Warrior 6-sided die'),
            array('value' => 8, 'sides' => 8, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(8)', 'description' => 'Warrior 8-sided die'),
            array('value' => 10, 'sides' => 10, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(10)', 'description' => 'Warrior 10-sided die'),
            array('value' => 12, 'sides' => 12, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(12)', 'description' => 'Warrior 12-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 1, 'skills' => array(), 'properties' => array(), 'recipe' => '(1)', 'description' => '1-sided die'),
            array('value' => 2, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
            array('value' => 7, 'sides' => 12, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(12)', 'description' => 'Speed 12-sided die'),
            array('value' => 4, 'sides' => 20, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(20)', 'description' => 'Speed 20-sided die'),
            array('value' => 4, 'sides' => 4, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(4)', 'description' => 'Warrior 4-sided die'),
            array('value' => 6, 'sides' => 6, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(6)', 'description' => 'Warrior 6-sided die'),
            array('value' => 8, 'sides' => 8, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(8)', 'description' => 'Warrior 8-sided die'),
            array('value' => 10, 'sides' => 10, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(10)', 'description' => 'Warrior 10-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(1):1, (2):1, `(4):4, `(6):6, `(8):8, `(10):10, `(12):12], responder004 rolled [(1):1, (2):2, z(12):7, z(20):4, `(4):4, `(6):6, `(8):8, `(10):10]. responder003 has dice which are not counted for initiative due to die skills: [`(4), `(6), `(8), `(10), `(12)]. responder004 has dice which are not counted for initiative due to die skills: [`(4), `(6), `(8), `(10)].'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 performed Skill attack using [(2):1,`(6):6] against [z(12):7]
        // [(1):1, (2):1, `(4):4, `(6):6, `(8):8, `(10):10, `(12):12] => [(1):1, (2):2, z(12):7, z(20):4, `(4):4, `(6):6, `(8):8, `(10):10]
        $this->verify_api_submitTurn(
            array(1, 4),
            'responder003 performed Skill attack using [(2):1,`(6):6] against [z(12):7]; Defender z(12) was captured; Attacker (2) rerolled 1 => 1; Attacker `(6) recipe changed from `(6) to (6), rerolled 6 => 4. ',
            $retval, array(array(0, 1), array(0, 3), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill', 'Speed'),
            array(16.5, 11.5, 3.3, -3.3),
            array(array(0, 3, array('value' => 4, 'skills' => array(), 'recipe' => '(6)', 'description' => '6-sided die'))),
            array(array(1, 2)),
            array(),
            array(array(0, array('value' => 7, 'sides' => 12, 'recipe' => 'z(12)', 'properties' => array('WasJustCaptured'))))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(2):1,`(6):6] against [z(12):7]; Defender z(12) was captured; Attacker (2) rerolled 1 => 1; Attacker `(6) recipe changed from `(6) to (6), rerolled 6 => 4'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 performed Speed attack using [z(20):4] against [(6):4]
        // [(1):1, (2):1, `(4):4, (6):4, `(8):8, `(10):10, `(12):12] <= [(1):1, (2):2, z(20):4, `(4):4, `(6):6, `(8):8, `(10):10]

        $_SESSION = $this->mock_test_user_login('responder004');

        // Verify that attacking a warrior die fails
        $this->verify_api_submitTurn_failure(
            array(),
            'Warrior dice cannot be attacked',
            $retval, array(array(0, 4), array(1, 2), array(1, 3)),
            $gameId, 1, 'Skill', 1, 0, '');

        $this->verify_api_submitTurn(
            array(13),
            'responder004 performed Speed attack using [z(20):4] against [(6):4]; Defender (6) was captured; Attacker z(20) rerolled 4 => 13. ',
            $retval, array(array(0, 3), array(1, 2)),
            $gameId, 1, 'Speed', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(13.5, 17.5, -2.7, 2.7),
            array(array(1, 2, array('value' => 13))),
            array(array(0, 3)),
            array(array(0, 0, array('properties' => array()))),
            array(array(1, array('value' => 4, 'sides' => 6, 'recipe' => '(6)', 'properties' => array('WasJustCaptured'))))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Speed attack using [z(20):4] against [(6):4]; Defender (6) was captured; Attacker z(20) rerolled 4 => 13'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder003 performed Power attack using [(2):1] against [(1):1]
        // [(1):1, (2):1, `(4):4, `(8):8, `(10):10, `(12):12] => [(1):1, (2):2, z(20):13, `(4):4, `(6):6, `(8):8, `(10):10]

        // Verify that multiple warrior dice can't be brought in at once
        $this->verify_api_submitTurn_failure(
            array(),
            'Only one Warrior die can be brought into play at a time',
            $retval, array(array(0, 1), array(0, 2), array(0, 3), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '');

        $this->verify_api_submitTurn(
            array(1),
            'responder003 performed Power attack using [(2):1] against [(1):1]; Defender (1) was captured; Attacker (2) rerolled 1 => 1. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power'),
            array(14.5, 17.0, -1.7, 1.7),
            array(),
            array(array(1, 0)),
            array(array(1, 0, array('properties' => array()))),
            array(array(0, array('value' => 1, 'sides' => 1, 'recipe' => '(1)', 'properties' => array('WasJustCaptured'))))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(2):1] against [(1):1]; Defender (1) was captured; Attacker (2) rerolled 1 => 1'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder004 performed Power attack using [(2):2] against [(2):1]
        // [(1):1, (2):1, `(4):4, `(8):8, `(10):10, `(12):12] <= [(2):2, z(20):13, `(4):4, `(6):6, `(8):8, `(10):10]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2),
            'responder004 performed Power attack using [(2):2] against [(2):1]; Defender (2) was captured; Attacker (2) rerolled 2 => 2. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Skill', 'Pass'),
            array(13.5, 19.0, -3.7, 3.7),
            array(),
            array(array(0, 1)),
            array(array(0, 1, array('properties' => array()))),
            array(array(1, array('value' => 1, 'sides' => 2, 'recipe' => '(2)', 'properties' => array('WasJustCaptured'))))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(2):2] against [(2):1]; Defender (2) was captured; Attacker (2) rerolled 2 => 2'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder003 has the option to make a skill attack, but chooses to pass
        // [(1):1, `(4):4, `(8):8, `(10):10, `(12):12] => [(2):2, z(20):13, `(4):4, `(6):6, `(8):8, `(10):10]
        $this->verify_api_submitTurn(
            array(),
            'responder003 passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power'),
            array(13.5, 19.0, -3.7, 3.7),
            array(),
            array(),
            array(array(1, 1, array('properties' => array()))),
            array()
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder004 performed Power attack using [z(20):13] against [(1):1], ending the round
        // [(1):1, `(4):4, `(8):8, `(10):10, `(12):12] <= [(2):2, z(20):13, `(4):4, `(6):6, `(8):8, `(10):10]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(12, 1, 2, 3, 3, 3, 3, 3, 1, 1, 7, 9, 4, 4, 4, 4),
            'responder004 performed Power attack using [z(20):13] against [(1):1]; Defender (1) was captured; Attacker z(20) rerolled 13 => 12. responder003 passed. responder004 passed. End of round: responder004 won round 1 (20 vs. 13). responder004 won initiative for round 2. Initial die values: responder003 rolled [(1):1, (2):2, `(4):4, `(6):6, `(8):8, `(10):10, `(12):12], responder004 rolled [(1):1, (2):1, z(12):7, z(20):9, `(4):4, `(6):6, `(8):8, `(10):10]. responder003 has dice which are not counted for initiative due to die skills: [`(4), `(6), `(8), `(10), `(12)]. responder004 has dice which are not counted for initiative due to die skills: [`(4), `(6), `(8), `(10)]. ',
            $retval, array(array(0, 0), array(1, 1)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['roundNumber'] = 2;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 1.5;
        $expData['playerDataArray'][1]['roundScore'] = 17.5;
        $expData['playerDataArray'][0]['sideScore'] = -10.7;
        $expData['playerDataArray'][1]['sideScore'] = 10.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 1, 'skills' => array(), 'properties' => array(), 'recipe' => '(1)', 'description' => '1-sided die'),
            array('value' => 2, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
            array('value' => 4, 'sides' => 4, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(4)', 'description' => 'Warrior 4-sided die'),
            array('value' => 6, 'sides' => 6, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(6)', 'description' => 'Warrior 6-sided die'),
            array('value' => 8, 'sides' => 8, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(8)', 'description' => 'Warrior 8-sided die'),
            array('value' => 10, 'sides' => 10, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(10)', 'description' => 'Warrior 10-sided die'),
            array('value' => 12, 'sides' => 12, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(12)', 'description' => 'Warrior 12-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 1, 'skills' => array(), 'properties' => array(), 'recipe' => '(1)', 'description' => '1-sided die'),
            array('value' => 1, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
            array('value' => 7, 'sides' => 12, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(12)', 'description' => 'Speed 12-sided die'),
            array('value' => 9, 'sides' => 20, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(20)', 'description' => 'Speed 20-sided die'),
            array('value' => 4, 'sides' => 4, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(4)', 'description' => 'Warrior 4-sided die'),
            array('value' => 6, 'sides' => 6, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(6)', 'description' => 'Warrior 6-sided die'),
            array('value' => 8, 'sides' => 8, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(8)', 'description' => 'Warrior 8-sided die'),
            array('value' => 10, 'sides' => 10, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(10)', 'description' => 'Warrior 10-sided die'),
        );
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [z(20):13] against [(1):1]; Defender (1) was captured; Attacker z(20) rerolled 13 => 12'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 1 (20 vs. 13)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 2. Initial die values: responder003 rolled [(1):1, (2):2, `(4):4, `(6):6, `(8):8, `(10):10, `(12):12], responder004 rolled [(1):1, (2):1, z(12):7, z(20):9, `(4):4, `(6):6, `(8):8, `(10):10]. responder003 has dice which are not counted for initiative due to die skills: [`(4), `(6), `(8), `(10), `(12)]. responder004 has dice which are not counted for initiative due to die skills: [`(4), `(6), `(8), `(10)].'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] += 5;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }


    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game is a partial regression test for the behavior of fire dice
     * 0. Create a game with Beatnik Turtle vs. Firebreather
     * 1. responder004 set swing values: S=7
     *    responder004 won initiative for round 1. Initial die values: responder003 rolled [wHF(4):1, (8):7, (10):6, vz(20):5, vz(20):11], responder004 rolled [(4):1, F(6):1, F(6):4, (12):3, (S=7):6]. responder003 has dice which are not counted for initiative due to die skills: [wHF(4)].
     * 2. responder004 chose to perform a Power attack using [(12):3] against [vz(20):5]; responder004 must turn down fire dice to complete this attack
     * 3. responder004 turned down fire dice: F(6) from 4 to 1; Defender vz(20) was captured; Attacker (12) rerolled 3 => 4
     */
    public function test_interface_game_028() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 28;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Beatnik Turtle rolls 5 dice, Firebreather rolls 4
        $gameId = $this->verify_api_createGame(
            array(4, 7, 6, 5, 11, 1, 1, 4, 3),
            'responder003', 'responder004', 'Beatnik Turtle', 'Firebreather', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Fire', 'Mighty', 'Slow', 'Speed', 'Value'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Beatnik Turtle', 'recipe' => 'wHF(4) (8) (10) vz(20) vz(20)', 'originalRecipe' => 'wHF(4) (8) (10) vz(20) vz(20)', 'artFilename' => 'beatnikturtle.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Firebreather', 'recipe' => '(4) F(6) F(6) (12) (S)', 'originalRecipe' => '(4) F(6) F(6) (12) (S)', 'artFilename' => 'firebreather.png');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['swingRequestArray'] = array('S' => array(6, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Slow', 'Mighty', 'Fire'), 'properties' => array(), 'recipe' => 'wHF(4)', 'description' => 'Slow Mighty Fire 4-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Value', 'Speed'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'vz(20)', 'description' => 'Value Speed 20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Value', 'Speed'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'vz(20)', 'description' => 'Value Speed 20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Fire'), 'properties' => array(), 'recipe' => 'F(6)', 'description' => 'Fire 6-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Fire'), 'properties' => array(), 'recipe' => 'F(6)', 'description' => 'Fire 6-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(S)', 'description' => 'S Swing Die'),
        );
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 1.5;
        $expData['playerDataArray'][1]['roundScore'] = 17.5;
        $expData['playerDataArray'][0]['sideScore'] = -10.7;
        $expData['playerDataArray'][1]['sideScore'] = 10.7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();


        ////////////////////
        // Move 01 - responder004 set swing values: S=7
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(6),
            $gameId, 1, array('S' => '7'), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 19;
        $expData['playerDataArray'][0]['sideScore'] = 1.0;
        $expData['playerDataArray'][1]['roundScore'] = 17.5;
        $expData['playerDataArray'][1]['sideScore'] = -1.0;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray']['4']['sides'] = 7;
        $expData['playerDataArray'][1]['activeDieArray']['4']['description'] .= ' (with 7 sides)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: S=7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [wHF(4):4, (8):7, (10):6, vz(20):5, vz(20):11], responder004 rolled [(4):1, F(6):1, F(6):4, (12):3, (S=7):6]. responder003 has dice which are not counted for initiative due to die skills: [wHF(4)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 chose to perform a Power attack using [(12):3] against [vz(20):5] (and must turn down fire dice)
        // [wHF(4):1, (8):7, (10):6, vz(20):5, vz(20):11] <= [(4):1, F(6):1, F(6):4, (12):3, (S=7):6]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(),
            'responder004 chose to perform a Power attack using [(12):3] against [vz(20):5]; responder004 must turn down fire dice to complete this attack. ',
            $retval, array(array(0, 3), array(1, 3)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'ADJUST_FIRE_DICE';
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array('ValueRelevantToScore', 'IsAttackTarget');
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array('IsAttacker');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 chose to perform a Power attack using [(12):3] against [vz(20):5]; responder004 must turn down fire dice to complete this attack'));
        $expData['gameActionLogCount'] += 1;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 turned down fire dice: F(6) from 4 to 1; Defender vz(20) was captured; Attacker (12) rerolled 3 => 8
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_adjustFire(
            array(8),
            'responder004 turned down fire dice: F(6) from 4 to 1; Defender vz(20) was captured; Attacker (12) rerolled 3 => 8. ',
            $retval, $gameId, 1, 'turndown', array(2), array('1'));
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill', 'Speed'),
            array(16.5, 22.5, -4.0, 4.0),
            array(array(1, 2, array('value' => 1)),
                  array(1, 3, array('value' => 8, 'properties' => array()))),
            array(array(0, 3)),
            array(),
            array(array(1, array('value' => 5, 'sides' => 20, 'recipe' => 'vz(20)')))
        );
        $expData['gameState'] = 'START_TURN';
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array('ValueRelevantToScore', 'WasJustCaptured');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 turned down fire dice: F(6) from 4 to 1; Defender vz(20) was captured; Attacker (12) rerolled 3 => 8'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Skill attack using [(10):6] against [(12):8]
        $this->verify_api_submitTurn(
            array(),
            'responder003 chose to perform a Skill attack using [(10):6] against [(12):8]; responder003 must turn down fire dice to complete this attack. ',
            $retval, array(array(0, 2), array(1, 3)),
            $gameId, 1, 'Skill', 0, 1, '');

        $expData['gameState'] = 'ADJUST_FIRE_DICE';
        $expData['validAttackTypeArray'] = array('Skill');
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('IsAttacker');
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array('IsAttackTarget');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to perform a Skill attack using [(10):6] against [(12):8]; responder003 must turn down fire dice to complete this attack'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder003 turned down fire dice
        $this->verify_api_adjustFire(
            array(2),
            'responder003 turned down fire dice: wHF(4) from 4 to 2; Defender (12) was captured; Attacker (10) rerolled 6 => 2. ',
            $retval, $gameId, 1, 'turndown', array(0), array('2'));
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game reproduces a bug in which the backend fails to offer Skill as an attack option
     * 0. Start a game with responder003 playing Fernworthy and responder004 playing Noeh
     * 1. responder004 set swing values: Y=20 and option dice: f(4/20=4)
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [(1):1, (2):1, z(12):1, z(20):10, `(4):4, `(6):6, `(8):8, `(10):10], responder004 rolled [z(15,15):17, tg(6):6, n(Y=20):19, f(4/20=4):4, sgv(17):15, `(1):1]. responder003 has dice which are not counted for initiative due to die skills: [`(4), `(6), `(8), `(10)]. responder004 has dice which are not counted for initiative due to die skills: [tg(6), sgv(17), `(1)].
     * 2. responder003 performed Skill attack using [(1):1,(2):1,z(12):1,z(20):10,`(4):4] against [z(15,15):17]; Defender z(15,15) was captured; Attacker (1) rerolled 1 => 1; Attacker (2) rerolled 1 => 1; Attacker z(12) rerolled 1 => 11; Attacker z(20) rerolled 10 => 8; Attacker `(4) recipe changed from `(4) to (4), rerolled 4 => 1

     */
    public function test_interface_game_029() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 29;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Fernworthy rolls 8 dice, Noeh rolls 5 (warrior dice always start with max values, but they roll first anyway)
        $gameId = $this->verify_api_createGame(
            array(1, 1, 1, 10, 4, 6, 8, 10, 14, 3, 6, 15, 1),
            'responder003', 'responder004', 'Fernworthy', 'Noeh', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Focus', 'Null', 'Shadow', 'Speed', 'Stinger', 'Trip', 'Value', 'Warrior'));
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['swingRequestArray'] = array('Y' => array(1, 20));
        $expData['playerDataArray'][1]['optRequestArray'] = array(3 => array(4, 20));
        $expData['playerDataArray'][0]['button'] = array('name' => 'fernworthy', 'recipe' => '(1) (2) z(12) z(20) `(4) `(6) `(8) `(10)', 'originalRecipe' => '(1) (2) z(12) z(20) `(4) `(6) `(8) `(10)', 'artFilename' => 'fernworthy.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Noeh', 'recipe' => 'z(15,15) tg(6) n(Y) f(4/20) sgv(17) `(1)', 'originalRecipe' => 'z(15,15) tg(6) n(Y) f(4/20) sgv(17) `(1)', 'artFilename' => 'noeh.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array(), 'properties' => array(), 'recipe' => '(1)', 'description' => '1-sided die'),
            array('value' => NULL, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2)', 'description' => '2-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(12)', 'description' => 'Speed 12-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Speed'), 'properties' => array(), 'recipe' => 'z(20)', 'description' => 'Speed 20-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(4)', 'description' => 'Warrior 4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(6)', 'description' => 'Warrior 6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(8)', 'description' => 'Warrior 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(10)', 'description' => 'Warrior 10-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 30, 'skills' => array('Speed'), 'properties' => array('Twin'), 'recipe' => 'z(15,15)', 'description' => 'Speed Twin Die (both with 15 sides)', 'subdieArray' => array(array('sides' => 15), array('sides' => 15))),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Trip', 'Stinger'), 'properties' => array(), 'recipe' => 'tg(6)', 'description' => 'Trip Stinger 6-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Null'), 'properties' => array(), 'recipe' => 'n(Y)', 'description' => 'Null Y Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(4/20)', 'description' => 'Focus Option Die (with 4 or 20 sides)'),
            array('value' => NULL, 'sides' => 17, 'skills' => array('Shadow', 'Stinger', 'Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'sgv(17)', 'description' => 'Shadow Stinger Value 17-sided die'),
            array('value' => NULL, 'sides' => 1, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(1)', 'description' => 'Warrior 1-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder004 set swing values: Y=20 and option dice: f(4/20=4)

        // this should cause the swing and option dice to be rerolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(19, 4),
            $gameId, 1, array('Y' => 20), array(3 => 4));
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Speed');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 17.5;
        $expData['playerDataArray'][1]['roundScore'] = 27.5;
        $expData['playerDataArray'][0]['sideScore'] = -6.7;
        $expData['playerDataArray'][1]['sideScore'] = 6.7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][6]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][7]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 17;
        $expData['playerDataArray'][1]['activeDieArray'][0]['subdieArray'][0]['value'] = 14;
        $expData['playerDataArray'][1]['activeDieArray'][0]['subdieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 19;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = 'Null Y Swing Die (with 20 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = 'Focus Option Die (with 4 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 1;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: Y=20 and option dice: f(4/20=4)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(1):1, (2):1, z(12):1, z(20):10, `(4):4, `(6):6, `(8):8, `(10):10], responder004 rolled [z(15,15):17, tg(6):6, n(Y=20):19, f(4/20=4):4, sgv(17):15, `(1):1]. responder003 has dice which are not counted for initiative due to die skills: [`(4), `(6), `(8), `(10)]. responder004 has dice which are not counted for initiative due to die skills: [tg(6), sgv(17), `(1)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performed Skill attack using [(1):1,(2):1,z(12):1,z(20):10,`(4):4] against [z(15,15):17]
        // [(1):1, (2):1, z(12):1, z(20):10, `(4):4, `(6):6, `(8):8, `(10):10] => [z(15,15):17, tg(6):6, n(Y=20):19, f(4/20=4):4, sgv(17):15, `(1):1]

        $this->verify_api_submitTurn(
            array(1, 1, 11, 8, 1),
            'responder003 performed Skill attack using [(1):1,(2):1,z(12):1,z(20):10,`(4):4] against [z(15,15):17]; Defender z(15,15) was captured; Attacker (1) rerolled 1 => 1; Attacker (2) rerolled 1 => 1; Attacker z(12) rerolled 1 => 11; Attacker z(20) rerolled 10 => 8; Attacker `(4) recipe changed from `(4) to (4), rerolled 4 => 1. ',
            $retval, array(array(0, 0), array(0, 1), array(0, 2), array(0, 3), array(0, 4), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill', 'Trip'),
            array(49.5, 12.5, 24.7, -24.7),
            array(array(0, 2, array('value' => 11)),
                  array(0, 3, array('value' => 8)),
                  array(0, 4, array('value' => 1, 'recipe' => '(4)', 'skills' => array(), 'description' => '4-sided die'))),
            array(array(1, 0)),
            array(),
            array(array(0, array('value' => 17, 'sides' => 30, 'recipe' => 'z(15,15)')))
        );
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array('WasJustCaptured', 'Twin');
        $expData['playerDataArray'][0]['capturedDieArray'][0]['subdieArray'] = array(array('sides' => 15, 'value' => 14), array('sides' => 15, 'value' => 3));
        $expData['playerDataArray'][1]['optRequestArray'] = array(2 => array(4, 20)); // does this usually happen?
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(1):1,(2):1,z(12):1,z(20):10,`(4):4] against [z(15,15):17]; Defender z(15,15) was captured; Attacker (1) rerolled 1 => 1; Attacker (2) rerolled 1 => 1; Attacker z(12) rerolled 1 => 11; Attacker z(20) rerolled 10 => 8; Attacker `(4) recipe changed from `(4) to (4), rerolled 4 => 1'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     */
    public function test_interface_game_030() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 30;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // dexx rolls 5 dice (because of the twin), GorgorBey rolls 2 (warrior dice always start with max values, but they roll first anyway)
        $gameId = $this->verify_api_createGame(
            array(5, 2, 5, 4, 1, 3, 12),
            'responder003', 'responder004', 'dexx', 'GorgorBey', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Focus', 'Konstant', 'Mighty', 'Mood', 'Ornery', 'Poison', 'Rage', 'Shadow', 'Slow', 'Speed', 'Stealth', 'Stinger', 'Trip', 'Turbo', 'Warrior'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20), 'Z' => array(4, 30));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('Y' => array(1, 20));
        $expData['playerDataArray'][1]['optRequestArray'] = array(1 => array(1, 15), 2 => array(5, 10));
        $expData['playerDataArray'][0]['button'] = array('name' => 'dexx', 'recipe' => 'k(7) p?(X) o!(Z) G(3,17) t(5) g`(2)', 'originalRecipe' => 'k(7) p?(X) o!(Z) G(3,17) t(5) g`(2)', 'artFilename' => 'dexx.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'GorgorBey', 'recipe' => 'ft(5) ds(1/15) `G(5/10) !p(Y) wHz(12)', 'originalRecipe' => 'ft(5) ds(1/15) `G(5/10) !p(Y) wHz(12)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 7, 'skills' => array('Konstant'), 'properties' => array(), 'recipe' => 'k(7)', 'description' => 'Konstant 7-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Poison', 'Mood'), 'properties' => array(), 'recipe' => 'p(X)?', 'description' => 'Poison X Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Turbo'), 'properties' => array(), 'recipe' => 'o(Z)!', 'description' => 'Ornery Turbo Z Swing Die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Rage'), 'properties' => array('Twin'), 'recipe' => 'G(3,17)', 'description' => 'Rage Twin Die (with 3 and 17 sides)', 'subdieArray' => array(array('sides' => 3), array('sides' => 17))),
            array('value' => NULL, 'sides' => 5, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(5)', 'description' => 'Trip 5-sided die'),
            array('value' => NULL, 'sides' => 2, 'skills' => array('Stinger', 'Warrior'), 'properties' => array(), 'recipe' => 'g`(2)', 'description' => 'Stinger Warrior 2-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 5, 'skills' => array('Focus', 'Trip'), 'properties' => array(), 'recipe' => 'ft(5)', 'description' => 'Focus Trip 5-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Stealth', 'Shadow'), 'properties' => array(), 'recipe' => 'ds(1/15)', 'description' => 'Stealth Shadow Option Die (with 1 or 15 sides)'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Warrior', 'Rage'), 'properties' => array(), 'recipe' => '`G(5/10)', 'description' => 'Warrior Rage Option Die (with 5 or 10 sides)'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Poison', 'Turbo'), 'properties' => array(), 'recipe' => 'p(Y)!', 'description' => 'Poison Turbo Y Swing Die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Slow', 'Mighty', 'Speed'), 'properties' => array(), 'recipe' => 'wHz(12)', 'description' => 'Slow Mighty Speed 12-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder003 set swing values: X=4, Z=4

        // this should cause the one option die to be rerolled
        $this->verify_api_submitDieValues(
            array(4, 4),
            $gameId, 1, array('X' => 4, 'Z' => 4), NULL);

        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] .= ' (with 4 sides)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set die sizes'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder004 set swing values: Y=1 and option dice: ds(1/15=1), `G(5/10=5)

        // this should cause the one option die to be rerolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(1, 5, 1),
            $gameId, 1, array('Y' => 1), array(1 => 1, 2 => 5));
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Speed', 'Trip');
        $expData['playerDataArray'][0]['roundScore'] = 14;
        $expData['playerDataArray'][1]['roundScore'] = 8;
        $expData['playerDataArray'][0]['sideScore'] = 4.0;
        $expData['playerDataArray'][1]['sideScore'] = -4.0;
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = 'Stealth Shadow Option Die (with 1 side)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = 'Warrior Rage Option Die (with 5 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] .= ' (with 1 side)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray'][1]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 12;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array(2 => range(4, 30));
        $expData['playerDataArray'][1]['turboSizeArray'] = array(3 => range(1, 20));
        $expData['gameActionLog'][0]['message'] = 'responder003 set swing values: X=4, Z=4';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: Y=1 and option dice: ds(1/15=1), `G(5/10=5)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [k(7):5, p(X=4)?:4, o(Z=4)!:4, G(3,17):7, t(5):4, g`(2):2], responder004 rolled [ft(5):3, ds(1/15=1):1, `G(5/10=5):5, p(Y=1)!:1, wHz(12):12]. responder003 has dice which are not counted for initiative due to die skills: [G(3,17), t(5), g`(2)]. responder004 has dice which are not counted for initiative due to die skills: [ft(5), `G(5/10=5), wHz(12)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 performed Skill attack using [ds(1/15=1):1,`G(5/10=5):5,p(Y=1)!:1] against [G(3,17):7]
        // [k(7):5, p(X=4)?:4, o(Z=4)!:4, (3,17):7, t(5):4, g`(2):2] <= [ft(5):3, ds(1/15=1):1, `G(5/10=5):5, p(Y=1)!:1, wHz(12):12]

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1, 3, 2, 14, 1),
            'responder004 performed Skill attack using [ds(1/15=1):1,`G(5/10=5):5,p(Y=1)!:1] against [G(3,17):7]; Defender G(3,17) was captured; Defender (3,17):16 was added; Attacker ds(1/15=1) rerolled 1 => 1; Attacker `G(5/10=5) recipe changed from `G(5/10=5) to (5/10=5), rerolled 5 => 3; Attacker p(Y=1)! rerolled from 1. Turbo die p(Y=1)! remained the same size, rolled 1. ',
            $retval, array(array(0, 3), array(1, 1), array(1, 2), array(1, 3)),
            $gameId, 1, 'Skill', 1, 0, '', array(3 => 1));
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill', 'Trip'),
            array(14, 30.5, -11.0, 11.0),
            array(array(1, 2, array('recipe' => '(5/10)', 'skills' => array(), 'value' => 3, 'description' => 'Option Die (with 5 sides)'))),
            array(array(0, 3)),
            array(),
            array(array(1, array('value' => 7, 'sides' => 20, 'recipe' => 'G(3,17)')))
        );
        array_splice(
            $expData['playerDataArray'][0]['activeDieArray'],
            3,
            0,
            array(
                array(
                    'value' => 16,
                    'sides' => 20,
                    'recipe' => '(3,17)',
                    'skills' => array(),
                    'properties' => array('IsRageTargetReplacement', 'Twin'),
                    'description' => 'Twin Die (with 3 and 17 sides)',
                    'subdieArray' => array(
                        array('value' => 2, 'sides' => 3),
                        array('value' => 14, 'sides' => 17)
                    )
                )
            )
        );
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array('WasJustCaptured', 'Twin');
        $expData['playerDataArray'][1]['capturedDieArray'][0]['subdieArray'] = array(array('sides' => 3, 'value' => 2), array('sides' => 17, 'value' => 5));

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [ds(1/15=1):1,`G(5/10=5):5,p(Y=1)!:1] against [G(3,17):7]; Defender G(3,17) was captured; Defender (3,17):16 was added; Attacker ds(1/15=1) rerolled 1 => 1; Attacker `G(5/10=5) recipe changed from `G(5/10=5) to (5/10=5), rerolled 5 => 3; Attacker p(Y=1)! rerolled from 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'Turbo die p(Y=1)! remained the same size, rolled 1'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        ////////////////////
        // Move 04 - responder003 performed Power attack using [p(X=4)?:4] against [p(Y=1)!:1]
        // [k(7):5, p(X=4)?:4, o(Z=4)!:4, (3,17):7, t(5):4, g`(2):2] => [ft(5):3, ds(1/15=1):1, (5/10=5):3, p(Y=1)!:1, wHz(12):12]

        $this->verify_api_submitTurn(
            array(3, 9, 1),
            'responder003 performed Power attack using [p(X=4)?:4] against [p(Y=1)!:1]; Defender p(Y=1)! was captured; Attacker p(X=4)? changed size from 4 to 10 sides, recipe changed from p(X=4)? to p(X=10)?, rerolled 4 => 9. responder003\'s idle ornery dice rerolled at end of turn: o(Z=4)! rerolled 4 => 1. ',
            $retval, array(array(0, 1), array(1, 3)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill', 'Trip'),
            array(7.5, 31.5, -16.0, 16.0),
            array(array(0, 1, array('value' => 9, 'sides' => '10', 'description' => 'Poison X Mood Swing Die (with 10 sides)')),
                  array(0, 2, array('value' => 1, 'properties' => array('HasJustRerolledOrnery')))),
            array(array(1, 3)),
            array(array(1, 0)),
            array(array(0, array('value' => 1, 'sides' => 1, 'recipe' => 'p(Y)!')))
        );
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array('Twin');
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['turboSizeArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array('Twin');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [p(X=4)?:4] against [p(Y=1)!:1]; Defender p(Y=1)! was captured; Attacker p(X=4)? changed size from 4 to 10 sides, recipe changed from p(X=4)? to p(X=10)?, rerolled 4 => 9'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003\'s idle ornery dice rerolled at end of turn: o(Z=4)! rerolled 4 => 1'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder004 performed Skill attack using [ft(5):3,ds(1/15=1):1] against [t(5):4]
        // [k(7):5, p(X=10)?:9, o(Z=4)!:1, (3,17):7, t(5):4, g`(2):2] <= [ft(5):3, ds(1/15=1):1, (5/10=5):3, wHz(12):12]

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(5, 1),
            'responder004 performed Skill attack using [ft(5):3,ds(1/15=1):1] against [t(5):4]; Defender t(5) was captured; Attacker ft(5) rerolled 3 => 5; Attacker ds(1/15=1) rerolled 1 => 1. ',
            $retval, array(array(0, 4), array(1, 0), array(1, 1)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(5, 36.5, -21.0, 21.0),
            array(array(1, 0, array('value' => 5)),
                  array(0, 2, array('properties' => array()))),
            array(array(0, 4)),
            array(array(0, 0)),
            array(array(1, array('value' => 4, 'sides' => 5, 'recipe' => 't(5)')))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [ft(5):3,ds(1/15=1):1] against [t(5):4]; Defender t(5) was captured; Attacker ft(5) rerolled 3 => 5; Attacker ds(1/15=1) rerolled 1 => 1'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - responder003 performed Skill attack using [o(Z=4)!:1,g`(2):2] against [(5/10=5):3]
        // [k(7):5, p(X=10)?:9, o(Z=4)!:1, (3,17):7, g`(2):2] => [ft(5):5, ds(1/15=1):1, (5/10=5):3, wHz(12):12]

        $this->verify_api_submitTurn(
            array(1, 1),
            'responder003 performed Skill attack using [o(Z=4)!:1,g`(2):2] against [(5/10=5):3]; Defender (5/10=5) was captured; Attacker o(Z=4)! rerolled from 1; Attacker g`(2) recipe changed from g`(2) to g(2), rerolled 2 => 1. Turbo die o(Z=4)! remained the same size, rolled 1. ',
            $retval, array(array(0, 2), array(0, 4), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, 'Warrior stinger dice must use their full value', array(2 => 4));

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill', 'Trip'),
            array(11, 34, -15.3, 15.3),
            array(array(0, 4, array('value' => 1, 'recipe' => 'g(2)', 'skills' => array('Stinger'), 'description' => 'Stinger 2-sided die'))),
            array(array(1, 2)),
            array(array(1, 1)),
            array(array(0, array('value' => 3, 'sides' => 5, 'recipe' => '(5/10)')))
        );
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][1]['optRequestArray'] = array(1 => array(1, 15));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [o(Z=4)!:1,g`(2):2] against [(5/10=5):3]; Defender (5/10=5) was captured; Attacker o(Z=4)! rerolled from 1; Attacker g`(2) recipe changed from g`(2) to g(2), rerolled 2 => 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die o(Z=4)! remained the same size, rolled 1'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Warrior stinger dice must use their full value'));
        $expData['gameActionLogCount'] += 2;
        $expData['gameChatLogCount'] = 1;
        $expData['gameChatEditable'] = 'TIMESTAMP';

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitChat($gameId, 'now i want to say something else', 'Updated previous game message', $retval['gameChatEditable']);

        $expData['gameChatLog'][0]['message'] = 'now i want to say something else';

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game reproduces a bug in which time and space is not triggered on a twin die
     * 0. Start a game with responder003 playing LadyJ and responder004 playing Giant
     * 1. responder003 set swing values: T=3, W=4, X=4
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [d(17):2, Ho(W=4)?:2, q(X=4):3, ^B(T=3,T=3):4, (5):5], responder004 rolled [(20):4, (20):1, (20):15, (20):14, (20):19, (20):11]. responder004's button has the "slow" button special, and cannot win initiative normally.
     * 2. responder003 performed Power attack using [^B(T=3,T=3):4] against [(20):4]; Defender (20) was captured; Attacker ^B(T=3,T=3) rerolled 4 => 3
     *    responder003's idle ornery dice rerolled at end of turn: Ho(W=4)? changed size from 4 to 12 sides, recipe changed from Ho(W=4)? to Ho(W=12)?, rerolled 2 => 8
     */
    public function test_interface_game_031() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 31;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // LadyJ rolls 2 dice, Giant rolls 6
        $gameId = $this->verify_api_createGame(
            array(2, 5, 4, 1, 15, 14, 19, 11),
            'responder003', 'responder004', 'LadyJ', 'Giant', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Berserk', 'Mighty', 'Mood', 'Ornery', 'Queer', 'Rage', 'Stealth', 'TimeAndSpace', 'Giant'));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['swingRequestArray'] = array('W' => array(4, 12), 'T' => array(2, 12), 'X' => array(4, 20));
        $expData['playerDataArray'][0]['button'] = array('name' => 'LadyJ', 'recipe' => 'dG(17) Ho(W)? q(X) ^B(T,T) (5)', 'originalRecipe' => 'dG(17) Ho(W)? q(X) ^B(T,T) (5)', 'artFilename' => 'ladyj.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Giant', 'recipe' => '(20) (20) (20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20) (20) (20)', 'artFilename' => 'giant.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 17, 'skills' => array('Stealth', 'Rage'), 'properties' => array(), 'recipe' => 'dG(17)', 'description' => 'Stealth Rage 17-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Mighty', 'Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'Ho(W)?', 'description' => 'Mighty Ornery W Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(X)', 'description' => 'Queer X Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('TimeAndSpace', 'Berserk'), 'properties' => array('Twin'), 'recipe' => '^B(T,T)', 'description' => 'TimeAndSpace Berserk Twin T Swing Die'),
            array('value' => NULL, 'sides' => 5, 'skills' => array(), 'properties' => array(), 'recipe' => '(5)', 'description' => '5-sided die'),
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
        // Move 01 - responder003 set swing values: T=3, W=4, X=4
        // responder003 won initiative for round 1. Initial die values: responder003 rolled [d(17):2, Ho(W=4)?:2, q(X=4):3, ^B(T=3,T=3):4, (5):5], responder004 rolled [(20):4, (20):1, (20):15, (20):14, (20):19, (20):11]. responder004's button has the "slow" button special, and cannot win initiative normally.

        // this should cause four die rolls (for 3 dice)
        $this->verify_api_submitDieValues(
            array(2, 3, 3, 1),
            $gameId, 1, array('W' => 4, 'X' => 4, 'T' => 3), NULL);

        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Berserk', 'Shadow');
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][1]['roundScore'] = 60;
        $expData['playerDataArray'][0]['sideScore'] = -28.0;
        $expData['playerDataArray'][1]['sideScore'] = 28.0;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] .= ' (both with 3 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray'] = array(array('sides' => 3, 'value' => 3), array('sides' => 3, 'value' => 1));
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 14;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 19;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 11;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: T=3, W=4, X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [dG(17):2, Ho(W=4)?:2, q(X=4):3, ^B(T=3,T=3):4, (5):5], responder004 rolled [(20):4, (20):1, (20):15, (20):14, (20):19, (20):11]. responder004\'s button has the "slow" button special, and cannot win initiative normally.'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performed Power attack using [^B(T=3,T=3):4] against [(20):4]; Defender (20) was captured; Attacker ^B(T=3,T=3) rerolled 4 => 3
        // responder003's idle ornery dice rerolled at end of turn: Ho(W=4)? changed size from 4 to 12 sides, recipe changed from Ho(W=4)? to Ho(W=12)?, rerolled 2 => 8

        $this->verify_api_submitTurn(
            array(1, 2, 3, 8),
            'responder003 performed Power attack using [^B(T=3,T=3):4] against [(20):4]; Defender (20) was captured; Attacker ^B(T=3,T=3) rerolled 4 => 3. responder003 gets another turn because a Time and Space die rolled odd. responder003\'s idle ornery dice rerolled at end of turn: Ho(W=4)? changed size from 4 to 12 sides, recipe changed from Ho(W=4)? to Ho(W=12)?, rerolled 2 => 8. ',
            $retval, array(array(0, 3), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(42, 50, -5.3, 5.3),
            array(array(0, 1, array('value' => 8, 'sides' => 12, 'description' => 'Mighty Ornery W Mood Swing Die (with 12 sides)', 'properties' => array('HasJustGrown', 'HasJustRerolledOrnery'))),
                  array(0, 3, array('value' => 3))),
            array(array(1, 0)),
            array(),
            array(array(0, array('value' => 4, 'sides' => 20, 'recipe' => '(20)')))
        );
        $expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray'][1]['value'] = 2;
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [^B(T=3,T=3):4] against [(20):4]; Defender (20) was captured; Attacker ^B(T=3,T=3) rerolled 4 => 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 gets another turn because a Time and Space die rolled odd'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003\'s idle ornery dice rerolled at end of turn: Ho(W=4)? changed size from 4 to 12 sides, recipe changed from Ho(W=4)? to Ho(W=12)?, rerolled 2 => 8'));
        $expData['gameActionLogCount'] += 3;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * 0. Start a game with responder003 playing Max Factor and responder004 playing Noeh
     * 1. responder004 set swing values: Y=5 and option dice: f(4/20=20)
     * 2. responder003 set swing values: X=11
     *    responder003 won initiative for round 2. Initial die values: responder003 rolled [(6):2, (8):3, (12):10, (X=11):4, (X=11):8], responder004 rolled [z(15,15):6, tg(6):6, n(Y=5):4, f(4/20=20):3, sgv(17):6, `(1):1]. responder004 has dice which are not counted for initiative due to die skills: [tg(6), sgv(17), `(1)].
     * Now the game state should be REACT_TO_INITIATIVE
     */
    public function test_interface_game_032() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 32;
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup
        // Max Factor rolls 3 dice, Noeh rolls 5
        $gameId = $this->verify_api_createGame(
            array(2, 3, 10, 4, 2, 6, 6, 1),
            'responder003', 'responder004', 'Max Factor', 'Noeh', 3);

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Focus', 'Null', 'Shadow', 'Speed', 'Stinger', 'Trip', 'Value', 'Warrior'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('Y' => array(1, 20));
        $expData['playerDataArray'][1]['optRequestArray'] = array(3 => array(4, 20));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Max Factor', 'recipe' => '(6) (8) (12) (X) (X)', 'originalRecipe' => '(6) (8) (12) (X) (X)', 'artFilename' => 'maxfactor.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Noeh', 'recipe' => 'z(15,15) tg(6) n(Y) f(4/20) sgv(17) `(1)', 'originalRecipe' => 'z(15,15) tg(6) n(Y) f(4/20) sgv(17) `(1)', 'artFilename' => 'noeh.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 30, 'skills' => array('Speed'), 'properties' => array('Twin'), 'recipe' => 'z(15,15)', 'description' => 'Speed Twin Die (both with 15 sides)', 'subdieArray' => array(array('sides' => 15), array('sides' => 15))),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Trip', 'Stinger'), 'properties' => array(), 'recipe' => 'tg(6)', 'description' => 'Trip Stinger 6-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Null'), 'properties' => array(), 'recipe' => 'n(Y)', 'description' => 'Null Y Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Focus'), 'properties' => array(), 'recipe' => 'f(4/20)', 'description' => 'Focus Option Die (with 4 or 20 sides)'),
            array('value' => NULL, 'sides' => 17, 'skills' => array('Shadow', 'Stinger', 'Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'sgv(17)', 'description' => 'Shadow Stinger Value 17-sided die'),
            array('value' => NULL, 'sides' => 1, 'skills' => array('Warrior'), 'properties' => array(), 'recipe' => '`(1)', 'description' => 'Warrior 1-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - responder004 set swing values: Y=5 and option dice: f(4/20=20)

        // this should cause the swing and option dice to be rerolled
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(4, 3),
            $gameId, 1, array('Y' => 5), array(3 => 20));
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set die sizes'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 set swing values: X=11
        //   responder003 won initiative for round 2. Initial die values: responder003 rolled [(6):2, (8):3, (12):10, (X=11):4, (X=11):8], responder004 rolled [z(15,15):6, tg(6):6, n(Y=5):4, f(4/20=20):3, sgv(17):6, `(1):1]. responder004 has dice which are not counted for initiative due to die skills: [tg(6), sgv(17), `(1)].

        $this->verify_api_submitDieValues(
            array(4, 8),
            $gameId, 1, array('X' => 11), NULL);

        $expData['gameState'] = 'REACT_TO_INITIATIVE';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 24;
        $expData['playerDataArray'][1]['roundScore'] = 31;
        $expData['playerDataArray'][0]['sideScore'] = -4.7;
        $expData['playerDataArray'][1]['sideScore'] = 4.7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'X Swing Die (with 11 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'X Swing Die (with 11 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][0]['subdieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][0]['subdieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = 'Null Y Swing Die (with 5 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = 'Focus Option Die (with 20 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 1;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();

        $expData['gameActionLog'][0]['message'] = 'responder004 set swing values: Y=5 and option dice: f(4/20=20)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=11'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(6):2, (8):3, (12):10, (X=11):4, (X=11):8], responder004 rolled [z(15,15):6, tg(6):6, n(Y=5):4, f(4/20=20):3, sgv(17):6, `(1):1]. responder004 has dice which are not counted for initiative due to die skills: [tg(6), sgv(17), `(1)].'));
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * 0. Start a game with responder003 playing RandomBMMixed [k(4) kp(4) np(8) n(10) (20)] and
     *    responder004 playing RandomBMMixed [Mhk(4) M(4) k(10) h(12) (12)]
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [k(4):1, kp(4):3, np(8):6, n(10):3, (20):3], responder004 rolled [Mhk(4):4, M(4):4, k(10):5, h(12):2, (12):7].
     * 1. responder003 performed Skill attack using [k(4):1,np(8):6] against [k(10):5]; Defender k(10) recipe changed to kn(10), was captured; Attacker k(4) does not reroll; Attacker np(8) rerolled 6 => 7
     * 2. responder004 performed Skill attack using [Mhk(4):4,(12):7] against [n(10):3]; Defender n(10) was captured; Attacker Mhk(4) changed size from 4 to 2 sides, recipe changed from Mhk(4) to Mhk(2), does not reroll; Attacker (12) rerolled 7 => 9
     */
    public function test_interface_game_033() {
        global $RANDOMBM_SKILL;

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 33;
        $_SESSION = $this->mock_test_user_login('responder003');

        $gameId = $this->verify_api_createGame(
            array(
                'bm_rand' => array(
                    0, 5, 0, 2, 3,     // die sizes for r3: 4, 4, 8, 10, 20 (these get sorted)
                    0, 0, 1, 2, 1, 2, 3,  // distribution of skills onto dice for r3 (one reroll)
                    4, 4, 0, 3, 0,     // die sizes for r4: 4, 4, 10, 12, 12 (these get sorted)
                    3, 0, 0, 0, 0, 2, 0, 1, // distribution of skills onto dice for r4 (some rerolls)
                    1, 3, 6, 3, 3,     // initial die rolls for r3
                    5, 2, 7,           // initial die rolls for r4
                ),
                'bm_skill_rand' => array(
                    $RANDOMBM_SKILL['k'], $RANDOMBM_SKILL['p'], $RANDOMBM_SKILL['n'],
                    $RANDOMBM_SKILL['h'], $RANDOMBM_SKILL['k'], $RANDOMBM_SKILL['M'],
                ),
            ),
            'responder003', 'responder004', 'RandomBMMixed', 'RandomBMMixed', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Konstant', 'Maximum', 'Null', 'Poison', 'Weak', 'RandomBMMixed'));
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['playerDataArray'][0]['roundScore'] = 8;
        $expData['playerDataArray'][1]['roundScore'] = 21;
        $expData['playerDataArray'][0]['sideScore'] = -8.7;
        $expData['playerDataArray'][1]['sideScore'] = 8.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'RandomBMMixed', 'recipe' => 'k(4) kp(4) np(8) n(10) (20)', 'originalRecipe' => 'k(4) kp(4) np(8) n(10) (20)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'RandomBMMixed', 'recipe' => 'Mhk(4) M(4) k(10) h(12) (12)', 'originalRecipe' => 'Mhk(4) M(4) k(10) h(12) (12)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 4, 'skills' => array('Konstant'), 'properties' => array(), 'recipe' => 'k(4)', 'description' => 'Konstant 4-sided die'),
            array('value' => 3, 'sides' => 4, 'skills' => array('Konstant', 'Poison'), 'properties' => array(), 'recipe' => 'kp(4)', 'description' => 'Konstant Poison 4-sided die'),
            array('value' => 6, 'sides' => 8, 'skills' => array('Null', 'Poison'), 'properties' => array(), 'recipe' => 'np(8)', 'description' => 'Null Poison 8-sided die'),
            array('value' => 3, 'sides' => 10, 'skills' => array('Null'), 'properties' => array(), 'recipe' => 'n(10)', 'description' => 'Null 10-sided die'),
            array('value' => 3, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 4, 'sides' => 4, 'skills' => array('Maximum', 'Weak', 'Konstant'), 'properties' => array(), 'recipe' => 'Mhk(4)', 'description' => 'Maximum Weak Konstant 4-sided die'),
            array('value' => 4, 'sides' => 4, 'skills' => array('Maximum'), 'properties' => array(), 'recipe' => 'M(4)', 'description' => 'Maximum 4-sided die'),
            array('value' => 5, 'sides' => 10, 'skills' => array('Konstant'), 'properties' => array(), 'recipe' => 'k(10)', 'description' => 'Konstant 10-sided die'),
            array('value' => 2, 'sides' => 12, 'skills' => array('Weak'), 'properties' => array(), 'recipe' => 'h(12)', 'description' => 'Weak 12-sided die'),
            array('value' => 7, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [k(4):1, kp(4):3, np(8):6, n(10):3, (20):3], responder004 rolled [Mhk(4):4, M(4):4, k(10):5, h(12):2, (12):7].'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(7),
            'responder003 performed Skill attack using [k(4):1,np(8):6] against [k(10):5]; Defender k(10) recipe changed to kn(10), was captured; Attacker k(4) does not reroll; Attacker np(8) rerolled 6 => 7. ',
            $retval, array(array(0, 0), array(0, 2), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '');

        $this->update_expected_data_after_normal_attack(
            $expData, 1, array('Power', 'Skill'),
            array(8, 16, -5.3, 5.3),
            array(array(0, 2, array('value' => 7))),
            array(array(1, 2)),
            array(),
            array(array(0, array('value' => 5, 'sides' => 10, 'recipe' => 'kn(10)', 'properties' => array('WasJustCaptured'))))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [k(4):1,np(8):6] against [k(10):5]; Defender k(10) recipe changed to kn(10), was captured; Attacker k(4) does not reroll; Attacker np(8) rerolled 6 => 7'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(9),
            'responder004 performed Skill attack using [Mhk(4):4,(12):7] against [n(10):3]; Defender n(10) was captured; Attacker Mhk(4) does not reroll; Attacker (12) rerolled 7 => 9. ',
            $retval, array(array(1, 0), array(1, 3), array(0, 3)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $this->update_expected_data_after_normal_attack(
            $expData, 0, array('Power', 'Skill'),
            array(8, 16, -5.3, 5.3),
            array(array(1, 3, array('value' => 9))),
            array(array(0, 3)),
            array(array(0, 0)),
            array(array(1, array('value' => 3, 'sides' => 10, 'recipe' => 'n(10)', 'properties' => array('WasJustCaptured'))))
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [Mhk(4):4,(12):7] against [n(10):3]; Defender n(10) was captured; Attacker Mhk(4) does not reroll; Attacker (12) rerolled 7 => 9'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This reproduces an internal error bug affecting bobby 5150
     */
    public function test_interface_game_034() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 34;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(2, 12, 6, 15, 9, 7),
            'responder003', 'responder004', 'bobby 5150', 'Wiseman', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Focus', 'Poison', 'Reserve', 'Shadow', 'Speed', 'TimeAndSpace', 'Trip', 'Turbo'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('R' => array(2, 16));
        $expData['playerDataArray'][0]['optRequestArray'] = array(3 => array(1, 30));
        $expData['playerDataArray'][0]['button'] = array('name' => 'bobby 5150', 'recipe' => '^(3) fsp(R) ftz(14) tz!(1/30) r^(4) rz(12) r!(1/30)', 'originalRecipe' => '^(3) fsp(R) ftz(14) tz!(1/30) r^(4) rz(12) r!(1/30)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Wiseman', 'recipe' => '(20) (20) (20) (20)', 'originalRecipe' => '(20) (20) (20) (20)', 'artFilename' => 'wiseman.png');
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 3, 'skills' => array('TimeAndSpace'), 'properties' => array(), 'recipe' => '^(3)', 'description' => 'TimeAndSpace 3-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Focus', 'Shadow', 'Poison'), 'properties' => array(), 'recipe' => 'fsp(R)', 'description' => 'Focus Shadow Poison R Swing Die'),
            array('value' => NULL, 'sides' => 14, 'skills' => array('Focus', 'Trip', 'Speed'), 'properties' => array(), 'recipe' => 'ftz(14)', 'description' => 'Focus Trip Speed 14-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Trip', 'Speed', 'Turbo'), 'properties' => array(), 'recipe' => 'tz(1/30)!', 'description' => 'Trip Speed Turbo Option Die (with 1 or 30 sides)'),
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
            array(2, 19),
            $gameId, 1, array('R' => 5), array(3 => 30));

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: R=5 and option dice: tz(1/30=30)!'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [^(3):2, fsp(R=5):2, ftz(14):12, tz(1/30=30)!:19], responder004 rolled [(20):6, (20):15, (20):9, (20):7]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=30)!].'));
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Shadow Poison R Swing Die (with 5 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 19;
        $expData['playerDataArray'][0]['roundScore'] = 18.5;
        $expData['playerDataArray'][0]['sideScore'] = -14.3;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array(3 => array(1, 30));
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 14.3;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Trip");
        $expData['gameActionLogCount'] += 2;
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(9, 8),
            'responder003 performed Trip attack using [ftz(14):12] against [(20):6]; Attacker ftz(14) rerolled 12 => 9; Defender (20) rerolled 6 => 8, was captured. ',
            $retval, array(array(0, 2), array(1, 0)),
            $gameId, 1, 'Trip', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [ftz(14):12] against [(20):6]; Attacker ftz(14) rerolled 12 => 9; Defender (20) rerolled 6 => 8, was captured'));
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("JustPerformedTripAttack");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 9;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "(20)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 8;
        $expData['playerDataArray'][0]['roundScore'] = 38.5;
        $expData['playerDataArray'][0]['sideScore'] = 5.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 7;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][1]['sideScore'] = -5.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(7),
            'responder004 performed Power attack using [(20):9] against [^(3):2]; Defender ^(3) was captured; Attacker (20) rerolled 9 => 7. ',
            $retval, array(array(1, 1), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):9] against [^(3):2]; Defender ^(3) was captured; Attacker (20) rerolled 9 => 7'));
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Focus Shadow Poison R Swing Die (with 5 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "fsp(R)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Focus", "Shadow", "Poison");
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Trip Speed 14-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Focus", "Trip", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 19;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array("2" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(2 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 37;
        $expData['playerDataArray'][0]['sideScore'] = 2.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "^(3)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 3;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['roundScore'] = 33;
        $expData['playerDataArray'][1]['sideScore'] = -2.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Trip");
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(4, 18),
            'responder003 performed Trip attack using [ftz(14):9] against [(20):7]; Attacker ftz(14) rerolled 9 => 4; Defender (20) rerolled 7 => 18, was not captured. ',
            $retval, array(array(0, 1), array(1, 2)),
            $gameId, 1, 'Trip', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [ftz(14):9] against [(20):7]; Attacker ftz(14) rerolled 9 => 4; Defender (20) rerolled 7 => 18, was not captured'));
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array("JustPerformedTripAttack", "JustPerformedUnsuccessfulAttack");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 18;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(11),
            'responder004 performed Power attack using [(20):15] against [fsp(R=5):2]; Defender fsp(R=5) was captured; Attacker (20) rerolled 15 => 11. ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):15] against [fsp(R=5):2]; Defender fsp(R=5) was captured; Attacker (20) rerolled 15 => 11'));
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Focus Trip Speed 14-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Focus", "Trip", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 19;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][0]['optRequestArray'] = array("1" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(1 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 42;
        $expData['playerDataArray'][0]['sideScore'] = 7.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 11;
        $expData['playerDataArray'][1]['canStillWin'] = null;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "fsp(R)";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 5;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['roundScore'] = 30.5;
        $expData['playerDataArray'][1]['sideScore'] = -7.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Trip");
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(13, 3),
            'responder003 performed Trip attack using [ftz(14):4] against [(20):11]; Attacker ftz(14) rerolled 4 => 13; Defender (20) rerolled 11 => 3, was captured. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Trip', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [ftz(14):4] against [(20):11]; Attacker ftz(14) rerolled 4 => 13; Defender (20) rerolled 11 => 3, was captured'));
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array("JustPerformedTripAttack");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 13;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "(20)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['roundScore'] = 62;
        $expData['playerDataArray'][0]['sideScore'] = 27.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 18;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 20.5;
        $expData['playerDataArray'][1]['sideScore'] = -27.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(9),
            'responder004 performed Power attack using [(20):18] against [ftz(14):13]; Defender ftz(14) was captured; Attacker (20) rerolled 18 => 9. ',
            $retval, array(array(1, 1), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):18] against [ftz(14):13]; Defender ftz(14) was captured; Attacker (20) rerolled 18 => 9'));
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 19;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array(0 => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(0 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 55;
        $expData['playerDataArray'][0]['sideScore'] = 13.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 9;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][2]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][1]['capturedDieArray'][2]['sides'] = 14;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['value'] = 13;
        $expData['playerDataArray'][1]['roundScore'] = 34.5;
        $expData['playerDataArray'][1]['sideScore'] = -13.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Trip");
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(6, 20),
            'Turbo die tz(1/30=30)! remained the same size. responder003 performed Trip attack using [tz(1/30=30)!] against [(20):9]; Attacker tz(1/30=30)! rolled 6; Defender (20) rerolled 9 => 20, was not captured. ',
            $retval, array(array(0, 0), array(1, 1)),
            $gameId, 1, 'Trip', 0, 1, '', array(0 => 30));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tz(1/30=30)!] against [(20):9]; Attacker tz(1/30=30)! rolled 6; Defender (20) rerolled 9 => 20, was not captured'));
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array("JustPerformedTripAttack", "JustPerformedUnsuccessfulAttack");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 20;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(12),
            'responder004 performed Power attack using [(20):7] against [tz(1/30=30)!:6]; Defender tz(1/30=30)! was captured; Attacker (20) rerolled 7 => 12. End of round: responder004 won round 1 (64.5 vs. 40). ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = null;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):7] against [tz(1/30=30)!:6]; Defender tz(1/30=30)! was captured; Attacker (20) rerolled 7 => 12'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 1 (64.5 vs. 40)'));
        $expData['gameState'] = "CHOOSE_RESERVE_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "TimeAndSpace 3-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
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
        $expData['playerDataArray'][0]['canStillWin'] = null;
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        $expData['playerDataArray'][0]['gameScoreArray'] = array("D" => 0, "L" => 1, "W" => 0);
        $expData['playerDataArray'][0]['optRequestArray'] = array("3" => array(1, 30), "6" => array(1, 30));
        $expData['playerDataArray'][0]['prevOptValueArray'] = array("3" => 30);
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array("R" => 5);
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = null;
        $expData['playerDataArray'][0]['sideScore'] = null;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "20-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "(20)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][2]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "20-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "(20)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][3]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        $expData['playerDataArray'][1]['gameScoreArray'] = array("D" => 0, "L" => 0, "W" => 1);
        $expData['playerDataArray'][1]['roundScore'] = null;
        $expData['playerDataArray'][1]['sideScore'] = null;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['playerDataArray'][0]['swingRequestArray'] = array('R' => array(2, 16));

        $expData['roundNumber'] = 2;
        $expData['validAttackTypeArray'] = array();
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_reactToReserve(
            array(3, 5, 7, 8, 1, 7),
            'responder003 added a reserve die: r(1/30)!. ',
            $gameId, 'add', 6);

        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 added a reserve die: r(1/30)!'));
        $expData['gameState'] = "SPECIFY_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Turbo Option Die (with 1 or 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array("Turbo");
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['button']['recipe'] = "^(3) fsp(R) ftz(14) tz(1/30)! r^(4) rz(12) (1/30)!";
        $expData['playerDataArray'][0]['optRequestArray'] = array("3" => array(1, 30), "4" => array(1, 30));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(9, 20, 1),
            $gameId, 2, array('R' => 14), array(3 => 30, 4 => 1));

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: R=14 and option dice: tz(1/30=30)!, (1/30=1)!'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [^(3):3, fsp(R=14):9, ftz(14):5, tz(1/30=30)!:20, (1/30=1)!:1], responder004 rolled [(20):7, (20):8, (20):1, (20):7]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=30)!].'));
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Shadow Poison R Swing Die (with 14 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Turbo Option Die (with 1 side)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['prevOptValueArray'] = array();
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array(3 => array(1, 30), 4 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 10;
        $expData['playerDataArray'][0]['sideScore'] = -20;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 20;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Trip");
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(1),
            'responder003 performed Skill attack using [(1/30=1)!:1] against [(20):1]; Defender (20) was captured; Attacker (1/30=1)! rerolled from 1. Turbo die (1/30=1)! remained the same size, rolled 1. ',
            $retval, array(array(0, 4), array(1, 2)),
            $gameId, 2, 'Skill', 0, 1, '', array(4 => 1));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(1/30=1)!:1] against [(20):1]; Defender (20) was captured; Attacker (1/30=1)! rerolled from 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die (1/30=1)! remained the same size, rolled 1'));
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "(20)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 30;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 7;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Power attack using [(20):8] against [(1/30=1)!:1]; Defender (1/30=1)! was captured; Attacker (20) rerolled 8 => 1. ',
            $retval, array(array(1, 1), array(0, 4)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):8] against [(1/30=1)!:1]; Defender (1/30=1)! was captured; Attacker (20) rerolled 8 => 1'));
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array("3" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(3 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 29.5;
        $expData['playerDataArray'][0]['sideScore'] = -1;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "(1/30)!";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 1;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 31;
        $expData['playerDataArray'][1]['sideScore'] = 1;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Trip");
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(12),
            'responder003 performed Power attack using [tz(1/30=30)!:20] against [(20):7]; Defender (20) was captured; Attacker tz(1/30=30)! rerolled from 20. Turbo die tz(1/30=30)! remained the same size, rolled 12. ',
            $retval, array(array(0, 3), array(1, 2)),
            $gameId, 2, 'Power', 0, 1, '', array(3 => 30));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [tz(1/30=30)!:20] against [(20):7]; Defender (20) was captured; Attacker tz(1/30=30)! rerolled from 20'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size, rolled 12'));
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "(20)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][0]['roundScore'] = 49.5;
        $expData['playerDataArray'][0]['sideScore'] = 19;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 21;
        $expData['playerDataArray'][1]['sideScore'] = -19;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(7),
            'responder004 performed Power attack using [(20):7] against [^(3):3]; Defender ^(3) was captured; Attacker (20) rerolled 7 => 7. ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):7] against [^(3):3]; Defender ^(3) was captured; Attacker (20) rerolled 7 => 7'));
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Focus Shadow Poison R Swing Die (with 14 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "fsp(R)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Focus", "Shadow", "Poison");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Focus Trip Speed 14-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Focus", "Trip", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 12;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array("2" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(2 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 48;
        $expData['playerDataArray'][0]['sideScore'] = 16;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "^(3)";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 3;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['roundScore'] = 24;
        $expData['playerDataArray'][1]['sideScore'] = -16;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Trip");
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(8),
            'responder003 performed Power attack using [tz(1/30=30)!:12] against [(20):1]; Defender (20) was captured; Attacker tz(1/30=30)! rerolled from 12. Turbo die tz(1/30=30)! remained the same size, rolled 8. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 2, 'Power', 0, 1, '', array(2 => 30));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [tz(1/30=30)!:12] against [(20):1]; Defender (20) was captured; Attacker tz(1/30=30)! rerolled from 12'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size, rolled 8'));
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][2]['recipe'] = "(20)";
        $expData['playerDataArray'][0]['capturedDieArray'][2]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 68;
        $expData['playerDataArray'][0]['sideScore'] = 36;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 14;
        $expData['playerDataArray'][1]['sideScore'] = -36;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(12),
            'responder004 performed Power attack using [(20):7] against [ftz(14):5]; Defender ftz(14) was captured; Attacker (20) rerolled 7 => 12. ',
            $retval, array(array(1, 0), array(0, 1)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):7] against [ftz(14):5]; Defender ftz(14) was captured; Attacker (20) rerolled 7 => 12'));
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Trip Speed Turbo Option Die (with 30 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 30;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Trip", "Speed", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 8;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['optRequestArray'] = array("1" => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(1 => array(1, 30));
        $expData['playerDataArray'][0]['roundScore'] = 61;
        $expData['playerDataArray'][0]['sideScore'] = 22;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 12;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][2]['recipe'] = "ftz(14)";
        $expData['playerDataArray'][1]['capturedDieArray'][2]['sides'] = 14;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][1]['sideScore'] = -22;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Shadow", "Trip");
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(5, 7),
            'Turbo die tz(1/30=30)! remained the same size. responder003 performed Trip attack using [tz(1/30=30)!] against [(20):12]; Attacker tz(1/30=30)! rolled 5; Defender (20) rerolled 12 => 7, was not captured. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 2, 'Trip', 0, 1, '', array(1 => 30));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die tz(1/30=30)! remained the same size'));
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Trip attack using [tz(1/30=30)!] against [(20):12]; Attacker tz(1/30=30)! rolled 5; Defender (20) rerolled 12 => 7, was not captured'));
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array("JustPerformedTripAttack", "JustPerformedUnsuccessfulAttack");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 7;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(15),
            'responder004 performed Power attack using [(20):7] against [tz(1/30=30)!:5]; Defender tz(1/30=30)! was captured; Attacker (20) rerolled 7 => 15. responder003 passed. ',
            $retval, array(array(1, 0), array(0, 1)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):7] against [tz(1/30=30)!:5]; Defender tz(1/30=30)! was captured; Attacker (20) rerolled 7 => 15'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['optRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 46;
        $expData['playerDataArray'][0]['sideScore'] = -8;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 15;
        $expData['playerDataArray'][1]['capturedDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][3]['recipe'] = "tz(1/30)!";
        $expData['playerDataArray'][1]['capturedDieArray'][3]['sides'] = 30;
        $expData['playerDataArray'][1]['capturedDieArray'][3]['value'] = 5;
        $expData['playerDataArray'][1]['roundScore'] = 58;
        $expData['playerDataArray'][1]['sideScore'] = 8;
        $expData['gameActionLogCount'] += 2;
        $expData['playerDataArray'][0]['canStillWin'] = true;
        $expData['playerDataArray'][1]['canStillWin'] = true;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3, 1, 1, 1, 1, 1, 20, 20, 20, 20),
            'responder004 performed Power attack using [(20):15] against [fsp(R=14):9]; Defender fsp(R=14) was captured; Attacker (20) rerolled 15 => 3. End of round: responder003 won round 2 (60 vs. 51). responder003 won initiative for round 3. Initial die values: responder003 rolled [^(3):1, fsp(R=14):1, ftz(14):1, tz(1/30=30)!:1, (1/30=1)!:1], responder004 rolled [(20):20, (20):20, (20):20, (20):20]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=30)!]. ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['gameActionLogCount'] += 3;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):15] against [fsp(R=14):9]; Defender fsp(R=14) was captured; Attacker (20) rerolled 15 => 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: responder003 won round 2 (60 vs. 51)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 3. Initial die values: responder003 rolled [^(3):1, fsp(R=14):1, ftz(14):1, tz(1/30=30)!:1, (1/30=1)!:1], responder004 rolled [(20):20, (20):20, (20):20, (20):20]. responder003 has dice which are not counted for initiative due to die skills: [ftz(14), tz(1/30=30)!].'));
        $expData['roundNumber'] = 3;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Trip');
        $expData['playerDataArray'][0]['optRequestArray'] = array(3 => array(1, 30), 4 => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array(3 => array(1, 30), 4 => array(1, 30));
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 10;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -20.0;
        $expData['playerDataArray'][1]['sideScore'] = 20.0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 3, 'skills' => array('TimeAndSpace'), 'properties' => array(), 'recipe' => '^(3)', 'description' => 'TimeAndSpace 3-sided die'),
            array('value' => 1, 'sides' => 14, 'skills' => array('Focus', 'Shadow', 'Poison'), 'properties' => array(), 'recipe' => 'fsp(R)', 'description' => 'Focus Shadow Poison R Swing Die (with 14 sides)'),
            array('value' => 1, 'sides' => 14, 'skills' => array('Focus', 'Trip', 'Speed'), 'properties' => array(), 'recipe' => 'ftz(14)', 'description' => 'Focus Trip Speed 14-sided die'),
            array('value' => 1, 'sides' => 30, 'skills' => array('Trip', 'Speed', 'Turbo'), 'properties' => array(), 'recipe' => 'tz(1/30)!', 'description' => 'Trip Speed Turbo Option Die (with 30 sides)'),
            array('value' => 1, 'sides' => 1, 'skills' => array('Turbo'), 'properties' => array(), 'recipe' => '(1/30)!', 'description' => 'Turbo Option Die (with 1 side)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 20, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => 20, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => 20, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => 20, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(),
            'responder003 surrendered. End of round: responder004 won round 3 because opponent surrendered. ',
            $retval, array(),
            $gameId, 3, 'Surrender', 0, 1, '');

        $expData['gameActionLogCount'] += 2;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 surrendered'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 3 because opponent surrendered'));
        $expData['roundNumber'] = 4;
        $expData['gameState'] = 'CHOOSE_RESERVE_DICE';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array('R' => '14');
        $expData['playerDataArray'][0]['prevOptValueArray'] = array(3 => '30', 4 => '1');
        $expData['playerDataArray'][0]['optRequestArray'] = array(3 => array(1, 30), 6 => array(1, 30));
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 2;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 2;
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = 'Focus Shadow Poison R Swing Die';
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'Trip Speed Turbo Option Die (with 1 or 30 sides)';
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 4, 0, array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Reserve', 'TimeAndSpace'), 'properties' => array(), 'recipe' => 'r^(4)', 'description' => 'Reserve TimeAndSpace 4-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Reserve', 'Speed'), 'properties' => array(), 'recipe' => 'rz(12)', 'description' => 'Reserve Speed 12-sided die'),
        ));
        $expData['playerDataArray'][0]['activeDieArray'][6]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][6]['sides'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][6]['description'] = 'Turbo Option Die (with 1 or 30 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = NULL;
        $expData['playerDataArray'][0]['swingRequestArray'] = array('R' => array(2, 16));
        $expData['playerDataArray'][0]['canStillWin'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_reactToReserve(
            array(1, 1, 20, 20, 20, 20),
            'responder003 chose not to add a reserve die. ',
            $gameId, 'decline', NULL);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This demonstrates some basic Boom die functionality, including
     * a Boom die making a Boom attack, and a Boom die making some
     * other type of attack
     */
    public function test_interface_game_035() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 35;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(4, 2, 1, 9, 3, 1, 6, 7),
            'responder003', 'responder004', 'Elsie', 'Elsie', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Boom', 'Mad'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Elsie', 'recipe' => '(4) b(4) (10) b(12) (Y)&', 'originalRecipe' => '(4) b(4) (10) b(12) (Y)&', 'artFilename' => 'elsie.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Elsie', 'recipe' => '(4) b(4) (10) b(12) (Y)&', 'originalRecipe' => '(4) b(4) (10) b(12) (Y)&', 'artFilename' => 'elsie.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('Y' => array(1, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('Y' => array(1, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
             array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
             array('value' => NULL, 'sides' => 4, 'skills' => array('Boom'), 'properties' => array(), 'recipe' => 'b(4)', 'description' => 'Boom 4-sided die'),
             array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
             array('value' => NULL, 'sides' => 12, 'skills' => array('Boom'), 'properties' => array(), 'recipe' => 'b(12)', 'description' => 'Boom 12-sided die'),
             array('value' => NULL, 'sides' => NULL, 'skills' => array('Mad'), 'properties' => array(), 'recipe' => '(Y)&', 'description' => 'Y Mad Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
             array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
             array('value' => NULL, 'sides' => 4, 'skills' => array('Boom'), 'properties' => array(), 'recipe' => 'b(4)', 'description' => 'Boom 4-sided die'),
             array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
             array('value' => NULL, 'sides' => 12, 'skills' => array('Boom'), 'properties' => array(), 'recipe' => 'b(12)', 'description' => 'Boom 12-sided die'),
             array('value' => NULL, 'sides' => NULL, 'skills' => array('Mad'), 'properties' => array(), 'recipe' => '(Y)&', 'description' => 'Y Mad Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        //////////
        // Move 1 - responder004 set swing values: Y=19
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(6),
            $gameId, 1, array('Y' => 19), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set die sizes'));
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        //////////
        // Move 2 - responder003 set swing values: Y=9
        $this->verify_api_submitDieValues(
            array(8),
            $gameId, 1, array('Y' => 9), NULL);

        $expData['activePlayerIdx'] = 0;
        array_shift($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: Y=19'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: Y=9'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(4):4, b(4):2, (10):1, b(12):9, (Y=9)&:8], responder004 rolled [(4):3, b(4):1, (10):6, b(12):7, (Y=19)&:6].'));
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Y Mad Swing Die (with 9 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][0]['roundScore'] = 19.5;
        $expData['playerDataArray'][0]['sideScore'] = -3.3;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Y Mad Swing Die (with 19 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 19;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 6;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 24.5;
        $expData['playerDataArray'][1]['sideScore'] = 3.3;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Boom");
        $expData['gameActionLogCount'] += 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        //////////
        // Move 3 - responder003 performed Boom attack using [b(4):2] against [(Y=19)&:6]
        $this->verify_api_submitTurn(
            array(2, 6),
            'responder003 performed Boom attack using [b(4):2] against [(Y=19)&:6]; Defender (Y=19)& recipe changed to (Y=6)&, rerolled 6 => 6, was not captured; Attacker b(4) does not reroll, was taken out of play. ',
            $retval, array(array(0, 1), array(1, 4)),
            $gameId, 1, 'Boom', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Boom attack using [b(4):2] against [(Y=19)&:6]; Defender (Y=19)& recipe changed to (Y=6)&, rerolled 6 => 6, was not captured; Attacker b(4) does not reroll, was taken out of play'));
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][0]['outOfPlayDieArray'][0]['properties'] = array("IsAttacker");
        $expData['playerDataArray'][0]['outOfPlayDieArray'][0]['recipe'] = "b(4)";
        $expData['playerDataArray'][0]['outOfPlayDieArray'][0]['sides'] = 4;
        $expData['playerDataArray'][0]['outOfPlayDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['roundScore'] = 17.5;
        $expData['playerDataArray'][0]['sideScore'] = -0.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Y Mad Swing Die (with 6 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 6;
        $expData['playerDataArray'][1]['roundScore'] = 18;
        $expData['playerDataArray'][1]['sideScore'] = 0.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        //////////
        // Move 4 - responder004 performed Skill attack using [b(4):1] against [(10):1]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(4),
            'responder004 performed Skill attack using [b(4):1] against [(10):1]; Defender (10) was captured; Attacker b(4) rerolled 1 => 4. ',
            $retval, array(array(1, 1), array(0, 1)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [b(4):1] against [(10):1]; Defender (10) was captured; Attacker b(4) rerolled 1 => 4'));
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][0]['roundScore'] = 12.5;
        $expData['playerDataArray'][0]['sideScore'] = -10.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "(10)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][1]['sideScore'] = 10.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game reproduces logging behavior when a die with a second size-changing power makes a Berserk attack
     */
    public function test_interface_game_036() {
        global $RANDOMBM_SKILL;

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 36;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(
                'bm_rand' => array(
                    4, 1, 3, 3, 0,        // die sizes for r3: 4, 6, 10, 10, 12
                    2, 2, 4, 1, 3, 4, 3,  // distribution of skills onto dice for r3
                    5, 1, 4, 1, 3,        // die sizes for r4: 6, 6, 10, 12, 20
                    4, 2, 3, 0, 3, 1,     // distribution of skills onto dice for r4
                    4, 4, 1, 2, 2,        // initial die rolls for r3
                    2, 2, 4, 2, 13,       // initial die rolls for r4
                ),
                'bm_skill_rand' => array(
                    $RANDOMBM_SKILL['B'], $RANDOMBM_SKILL['d'], $RANDOMBM_SKILL['h'],
                    $RANDOMBM_SKILL['v'], $RANDOMBM_SKILL['b'], $RANDOMBM_SKILL['b'], $RANDOMBM_SKILL['q'],
                ),
            ),
            'responder003', 'responder004', 'RandomBMMixed', 'RandomBMMixed', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Berserk', 'Boom', 'Queer', 'Stealth', 'Value', 'Weak', 'RandomBMMixed'));
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Berserk');
        $expData['playerDataArray'][0]['roundScore'] = 21;
        $expData['playerDataArray'][1]['roundScore'] = 20.5;
        $expData['playerDataArray'][0]['sideScore'] = 0.3;
        $expData['playerDataArray'][1]['sideScore'] = -0.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'RandomBMMixed', 'recipe' => '(4) d(6) B(10) dh(10) Bh(12)', 'originalRecipe' => '(4) d(6) B(10) dh(10) Bh(12)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'RandomBMMixed', 'recipe' => 'b(6) q(6) v(10) bq(12) v(20)', 'originalRecipe' => 'b(6) q(6) v(10) bq(12) v(20)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 4, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => 4, 'sides' => 6, 'skills' => array('Stealth'), 'properties' => array(), 'recipe' => 'd(6)', 'description' => 'Stealth 6-sided die'),
            array('value' => 1, 'sides' => 10, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(10)', 'description' => 'Berserk 10-sided die'),
            array('value' => 2, 'sides' => 10, 'skills' => array('Stealth', 'Weak'), 'properties' => array(), 'recipe' => 'dh(10)', 'description' => 'Stealth Weak 10-sided die'),
            array('value' => 2, 'sides' => 12, 'skills' => array('Berserk', 'Weak'), 'properties' => array(), 'recipe' => 'Bh(12)', 'description' => 'Berserk Weak 12-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 2, 'sides' => 6, 'skills' => array('Boom'), 'properties' => array(), 'recipe' => 'b(6)', 'description' => 'Boom 6-sided die'),
            array('value' => 2, 'sides' => 6, 'skills' => array('Queer'), 'properties' => array(), 'recipe' => 'q(6)', 'description' => 'Queer 6-sided die'),
            array('value' => 4, 'sides' => 10, 'skills' => array('Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'v(10)', 'description' => 'Value 10-sided die'),
            array('value' => 2, 'sides' => 12, 'skills' => array('Boom', 'Queer'), 'properties' => array(), 'recipe' => 'bq(12)', 'description' => 'Boom Queer 12-sided die'),
            array('value' => 13, 'sides' => 20, 'skills' => array('Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'v(20)', 'description' => 'Value 20-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(4):4, d(6):4, B(10):1, dh(10):2, Bh(12):2], responder004 rolled [b(6):2, q(6):2, v(10):4, bq(12):2, v(20):13].'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        //////////
        // Move 1 responder003 performed Power attack using [(4):4] against [b(6):2]
        $this->verify_api_submitTurn(
            array(1),
            'responder003 performed Power attack using [(4):4] against [b(6):2]; Defender b(6) was captured; Attacker (4) rerolled 4 => 1. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(4):4] against [b(6):2]; Defender b(6) was captured; Attacker (4) rerolled 4 => 1'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "b(6)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['roundScore'] = 27;
        $expData['playerDataArray'][0]['sideScore'] = 6.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][1]['roundScore'] = 17.5;
        $expData['playerDataArray'][1]['sideScore'] = -6.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Boom");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        //////////
        // Move 2 - responder004 performed Boom attack using [bq(12):2] against [dh(10):2]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(4),
            'responder004 performed Boom attack using [bq(12):2] against [dh(10):2]; Defender dh(10) recipe changed to dh(8), rerolled 2 => 4, was not captured; Attacker bq(12) does not reroll, was taken out of play. ',
            $retval, array(array(1, 2), array(0, 3)),
            $gameId, 1, 'Boom', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Boom attack using [bq(12):2] against [dh(10):2]; Defender dh(10) recipe changed to dh(8), rerolled 2 => 4, was not captured; Attacker bq(12) does not reroll, was taken out of play'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Stealth Weak 8-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array("HasJustShrunk");
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "dh(8)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 26;
        $expData['playerDataArray'][0]['sideScore'] = 9.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['outOfPlayDieArray'][0]['properties'] = array("IsAttacker");
        $expData['playerDataArray'][1]['outOfPlayDieArray'][0]['recipe'] = "bq(12)";
        $expData['playerDataArray'][1]['outOfPlayDieArray'][0]['sides'] = 12;
        $expData['playerDataArray'][1]['outOfPlayDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['roundScore'] = 11.5;
        $expData['playerDataArray'][1]['sideScore'] = -9.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Berserk");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        //////////
        // Move 3 - responder003 performed Berserk attack using [Bh(12):2] against [q(6):2]
        $this->verify_api_submitTurn(
            array(1),
            'responder003 performed Berserk attack using [Bh(12):2] against [q(6):2]; Defender q(6) was captured; Attacker Bh(12) changed to h(6) and changed size from 12 to 6 sides because of the Berserk attack, and then changed size from 6 to 4 sides, recipe changed from h(6) to h(4), rerolled 2 => 1. ',
            $retval, array(array(0, 4), array(1, 0)),
            $gameId, 1, 'Berserk', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Berserk attack using [Bh(12):2] against [q(6):2]; Defender q(6) was captured; Attacker Bh(12) changed to h(6) and changed size from 12 to 6 sides because of the Berserk attack, and then changed size from 6 to 4 sides, recipe changed from h(6) to h(4), rerolled 2 => 1'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Weak 4-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array("HasJustSplit", "JustPerformedBerserkAttack", "HasJustShrunk");
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "h(4)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array("Weak");
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "q(6)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['roundScore'] = 28;
        $expData['playerDataArray'][0]['sideScore'] = 13;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][1]['roundScore'] = 8.5;
        $expData['playerDataArray'][1]['sideScore'] = -13;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game reproduces a bug in which a null attack against a
     * rage die causes the newly-introduced replacement die to be null too
     */
    public function test_interface_game_037() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 37;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(3, 5, 6, 3, 4, 2, 4, 1, 8, 14),
            'responder003', 'responder004', 'Rold Rage', 'ElihuRoot', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Null', 'Rage', 'Weak'));
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 15;
        $expData['playerDataArray'][1]['roundScore'] = 11.5;
        $expData['playerDataArray'][0]['sideScore'] = 2.3;
        $expData['playerDataArray'][1]['sideScore'] = -2.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'Rold Rage', 'recipe' => '(6) (6) G(6) G(6) G(6)', 'originalRecipe' => '(6) (6) G(6) G(6) G(6)', 'artFilename' => 'roldrage.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'ElihuRoot', 'recipe' => '(3) h(10) h(10) n(20) n(30)', 'originalRecipe' => '(3) h(10) h(10) n(20) n(30)', 'artFilename' => 'elihuroot.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 3, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 5, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 6, 'sides' => 6, 'skills' => array('Rage'), 'properties' => array(), 'recipe' => 'G(6)', 'description' => 'Rage 6-sided die'),
            array('value' => 3, 'sides' => 6, 'skills' => array('Rage'), 'properties' => array(), 'recipe' => 'G(6)', 'description' => 'Rage 6-sided die'),
            array('value' => 4, 'sides' => 6, 'skills' => array('Rage'), 'properties' => array(), 'recipe' => 'G(6)', 'description' => 'Rage 6-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 2, 'sides' => 3, 'skills' => array(), 'properties' => array(), 'recipe' => '(3)', 'description' => '3-sided die'),
            array('value' => 4, 'sides' => 10, 'skills' => array('Weak'), 'properties' => array(), 'recipe' => 'h(10)', 'description' => 'Weak 10-sided die'),
            array('value' => 1, 'sides' => 10, 'skills' => array('Weak'), 'properties' => array(), 'recipe' => 'h(10)', 'description' => 'Weak 10-sided die'),
            array('value' => 8, 'sides' => 20, 'skills' => array('Null'), 'properties' => array(), 'recipe' => 'n(20)', 'description' => 'Null 20-sided die'),
            array('value' => 14, 'sides' => 30, 'skills' => array('Null'), 'properties' => array(), 'recipe' => 'n(30)', 'description' => 'Null 30-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [(6):3, (6):5, G(6):6, G(6):3, G(6):4], responder004 rolled [(3):2, h(10):4, h(10):1, n(20):8, n(30):14]. responder003 has dice which are not counted for initiative due to die skills: [G(6), G(6), G(6)].'));
        $expData['gameActionLogCount'] += 1;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(19, 4),
            'responder004 performed Power attack using [n(30):14] against [G(6):3]; Defender G(6) recipe changed to Gn(6), was captured; Defender (6):4 was added; Attacker n(30) rerolled 14 => 19. ',
            $retval, array(array(1, 4), array(0, 3)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [n(30):14] against [G(6):3]; Defender G(6) recipe changed to Gn(6), was captured; Defender (6):4 was added; Attacker n(30) rerolled 14 => 19'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = '6-sided die';
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array('IsRageTargetReplacement');
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = '(6)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['roundScore'] = 15;
        $expData['playerDataArray'][0]['sideScore'] = 2.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 19;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "Gn(6)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['sideScore'] = -2.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(4),
            'responder003 performed Skill attack using [G(6):4] against [h(10):4]; Defender h(10) was captured; Attacker G(6) recipe changed from G(6) to (6), rerolled 4 => 4. ',
            $retval, array(array(0, 4), array(1, 1)),
            $gameId, 1, 'Skill', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [G(6):4] against [h(10):4]; Defender h(10) was captured; Attacker G(6) recipe changed from G(6) to (6), rerolled 4 => 4'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "6-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "(6)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "h(10)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][0]['sideScore'] = 12.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "Null 20-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "n(20)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][2]['skills'] = array("Null");
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "Null 30-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "n(30)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 30;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 19;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 6.5;
        $expData['playerDataArray'][1]['sideScore'] = -12.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game reproduces an internal error bug caused by a successful trip attack against a rage die
     */
    public function test_interface_game_038() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 38;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(4, 2, 1, 10, 4, 4, 6, 7),
            'responder003', 'responder004', 'Stumbling Clowns', 'Delt Rage', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Rage', 'Trip'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Stumbling Clowns', 'recipe' => '(8) t(8) (10) t(10) (X)', 'originalRecipe' => '(8) t(8) (10) t(10) (X)', 'artFilename' => 'stumblingclowns.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Delt Rage', 'recipe' => 'G(4) G(4) (10) (12) G(X)', 'originalRecipe' => 'G(4) G(4) (10) (12) G(X)', 'artFilename' => 'deltrage.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(8)', 'description' => 'Trip 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Trip'), 'properties' => array(), 'recipe' => 't(10)', 'description' => 'Trip 10-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Rage'), 'properties' => array(), 'recipe' => 'G(4)', 'description' => 'Rage 4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Rage'), 'properties' => array(), 'recipe' => 'G(4)', 'description' => 'Rage 4-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Rage'), 'properties' => array(), 'recipe' => 'G(X)', 'description' => 'Rage X Swing Die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(10),
            $gameId, 1, array('X' => 11), NULL);

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set die sizes'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "X Swing Die (with 11 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 11;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(7),
            $gameId, 1, array('X' => 19), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_shift($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=11'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=19'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(8):4, t(8):2, (10):1, t(10):10, (X=11):10], responder004 rolled [G(4):4, G(4):4, (10):6, (12):7, G(X=19):7]. responder003 has dice which are not counted for initiative due to die skills: [t(8), t(10)]. responder004 has dice which are not counted for initiative due to die skills: [G(4), G(4), G(X=19)].'));
        $expData['gameActionLogCount'] += 2;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 10;
        $expData['playerDataArray'][0]['roundScore'] = 23.5;
        $expData['playerDataArray'][0]['sideScore'] = -0.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Rage X Swing Die (with 19 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 19;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 24.5;
        $expData['playerDataArray'][1]['sideScore'] = 0.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(2, 7),
            'responder003 performed Skill attack using [(8):4,t(8):2] against [(10):6]; Defender (10) was captured; Attacker (8) rerolled 4 => 2; Attacker t(8) rerolled 2 => 7. ',
            $retval, array(array(0, 0), array(0, 1), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '');

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(8):4,t(8):2] against [(10):6]; Defender (10) was captured; Attacker (8) rerolled 4 => 2; Attacker t(8) rerolled 2 => 7'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "(10)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][0]['roundScore'] = 33.5;
        $expData['playerDataArray'][0]['sideScore'] = 9.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['roundScore'] = 19.5;
        $expData['playerDataArray'][1]['sideScore'] = -9.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1),
            'responder004 performed Power attack using [G(4):4] against [(10):1]; Defender (10) was captured; Attacker G(4) recipe changed from G(4) to (4), rerolled 4 => 1. ',
            $retval, array(array(1, 0), array(0, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [G(4):4] against [(10):1]; Defender (10) was captured; Attacker G(4) recipe changed from G(4) to (4), rerolled 4 => 1'));
        $expData['gameActionLogCount'] += 1;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 28.5;
        $expData['playerDataArray'][0]['sideScore'] = -0.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "4-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "(4)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "(10)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 29.5;
        $expData['playerDataArray'][1]['sideScore'] = 0.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Trip");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(8, 2, 1),
            'responder003 performed Trip attack using [t(10):10] against [G(4):4]; Attacker t(10) rerolled 10 => 8; Defender G(4) rerolled 4 => 2, was captured; Defender (4):1 was added. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 1, 'Trip', 0, 1, '');
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game reproduces a bug in which a die which attacks a radioactive rage die does not split
     */
    public function test_interface_game_039() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 39;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array(),
            'responder003', 'responder004', 'wtrollkin', 'Maryland', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'CHOOSE_AUXILIARY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Auxiliary', 'Berserk', 'Ornery', 'Morphing', 'Radioactive', 'Rage', 'Reserve', 'Shadow', 'Slow', 'Stinger', 'Poison', 'Turbo'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'wtrollkin', 'recipe' => 'p(4) pG%(7) s(15) sB(S) s%(S)! worm(Y)', 'originalRecipe' => 'p(4) pG%(7) s(15) sB(S) s%(S)! worm(Y)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Maryland', 'recipe' => 'g(4) m(8) o(10) (W) (X) +@(8)', 'originalRecipe' => 'g(4) m(8) o(10) (W) (X) +@(8)', 'artFilename' => 'maryland.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('S' => array(6, 20), 'Y' => array(1, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('W' => array(4, 12), 'X' => array(4, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(4)', 'description' => 'Poison 4-sided die'),
            array('value' => NULL, 'sides' => 7, 'skills' => array('Poison', 'Rage', 'Radioactive'), 'properties' => array(), 'recipe' => 'pG%(7)', 'description' => 'Poison Rage Radioactive 7-sided die'),
            array('value' => NULL, 'sides' => 15, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(15)', 'description' => 'Shadow 15-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Shadow', 'Berserk'), 'properties' => array(), 'recipe' => 'sB(S)', 'description' => 'Shadow Berserk S Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Shadow', 'Radioactive', 'Turbo'), 'properties' => array(), 'recipe' => 's%(S)!', 'description' => 'Shadow Radioactive Turbo S Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Slow', 'Ornery', 'Reserve', 'Morphing'), 'properties' => array(), 'recipe' => 'worm(Y)', 'description' => 'Slow Ornery Reserve Morphing Y Swing Die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Auxiliary'), 'properties' => array(), 'recipe' => '+(8)', 'description' => 'Auxiliary 8-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Stinger'), 'properties' => array(), 'recipe' => 'g(4)', 'description' => 'Stinger 4-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(8)', 'description' => 'Morphing 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Ornery'), 'properties' => array(), 'recipe' => 'o(10)', 'description' => 'Ornery 10-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(W)', 'description' => 'W Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Auxiliary'), 'properties' => array(), 'recipe' => '+(8)', 'description' => 'Auxiliary 8-sided die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_reactToAuxiliary(
            array(2, 3, 14, 4, 1, 6),
            'responder003 chose not to use auxiliary dice in this game: neither player will get an auxiliary die. ',
            $gameId, 'decline');

        $expData['playerDataArray'][0]['swingRequestArray'] = array('S' => array(6, 20));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose not to use auxiliary dice in this game: neither player will get an auxiliary die'));
        $expData['gameActionLogCount'] += 1;
        $expData['gameSkillsInfo'] =  $this->get_skill_info(array('Berserk', 'Morphing', 'Ornery', 'Poison', 'Radioactive', 'Rage', 'Reserve', 'Shadow', 'Slow', 'Stinger', 'Turbo'));
        $expData['gameState'] = "SPECIFY_DICE";
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['button'] = array('artFilename' => 'BMdefaultRound.png', 'name' => 'wtrollkin', 'recipe' => 'p(4) pG%(7) s(15) sB(S) s%(S)! worm(Y)', 'originalRecipe' => 'p(4) pG%(7) s(15) sB(S) s%(S)! worm(Y)');
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['button'] = array('artFilename' => 'maryland.png', 'name' => 'Maryland', 'recipe' => 'g(4) m(8) o(10) (W) (X)', 'originalRecipe' => 'g(4) m(8) o(10) (W) (X) +@(8)');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(2, 8),
            $gameId, 1, array('W' => 7, 'X' => 17), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set die sizes'));
        $expData['gameActionLogCount'] += 1;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(3, 5),
            $gameId, 1, array('S' => 6), NULL);

        $expData['activePlayerIdx'] = 1;
        $expData['gameActionLog'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'Game created by responder003'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose not to use auxiliary dice in this game: neither player will get an auxiliary die'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: W=7, X=17'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: S=6'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [p(4):2, pG%(7):3, s(15):14, sB(S=6):3, s%(S=6)!:5], responder004 rolled [g(4):4, m(8):1, o(10):6, (W=7):2, (X=17):8]. responder003 has dice which are not counted for initiative due to die skills: [pG%(7)]. responder004 has dice which are not counted for initiative due to die skills: [g(4)].'));
        $expData['gameActionLogCount'] += 2;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Shadow Berserk S Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Shadow Radioactive Turbo S Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 5;
        $expData['playerDataArray'][0]['roundScore'] = 2.5;
        $expData['playerDataArray'][0]['sideScore'] = -13.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "W Swing Die (with 7 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "X Swing Die (with 17 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 17;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array(4 => range(6, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 23;
        $expData['playerDataArray'][1]['sideScore'] = 13.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(8, 7, 4, 1),
            'responder004 performed Power attack using [(X=17):8] against [pG%(7):3]; Defender pG%(7) was captured; Defender p%(7):4 was added; Attacker (X=17) showing 8 split into: (X=9) showing 8, and (X=8) showing 7. responder004\'s idle ornery dice rerolled at end of turn: o(10) rerolled 6 => 1. ',
            $retval, array(array(1, 4), array(0, 1)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(X=17):8] against [pG%(7):3]; Defender pG%(7) was captured; Defender p%(7):4 was added; Attacker (X=17) showing 8 split into: (X=9) showing 8, and (X=8) showing 7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004\'s idle ornery dice rerolled at end of turn: o(10) rerolled 6 => 1'));
        $expData['gameActionLogCount'] += 2;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 0, array($expData['playerDataArray'][1]['activeDieArray'][4]));
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Poison Radioactive 7-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array("IsRageTargetReplacement");
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "p%(7)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Poison", "Radioactive");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['sideScore'] = -11.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array("HasJustRerolledOrnery");
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = 'X Swing Die (with 9 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array('HasJustSplit');
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][5]['description'] = 'X Swing Die (with 8 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][5]['properties'] = array('HasJustSplit');
        $expData['playerDataArray'][1]['activeDieArray'][5]['sides'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 7;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "pG%(7)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 7;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['roundScore'] = 19.5;
        $expData['playerDataArray'][1]['sideScore'] = 11.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Berserk", "Shadow");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This game regression-tests and fixes the behavior of trip attacks against size-changing Maximum opponents
     */
    public function test_interface_game_040() {
        global $RANDOMBM_SKILL;

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 40;
        $_SESSION = $this->mock_test_user_login('responder003');

        $gameId = $this->verify_api_createGame(
            array(
                'bm_rand' => array(
                3, 0, 2, 5, 3,          // die sizes for r3: 6, 8, 10, 10, 20
                0, 3, 2, 0, 0, 1,       // distribution of skills onto dice for r3
                0, 5, 1, 3, 0,          // die sizes for r4: 4, 4, 6, 10, 20
                2, 4, 2, 2, 2, 1, 2, 1, // distribution of skills onto dice for r4
                7, 10, 20,              // initial die rolls for r3 (note: Maximum dice don't use random values)
                3, 1, 1, 1, 3           // initial die rolls for r4
                ),
                'bm_skill_rand' => array(
                    $RANDOMBM_SKILL['M'], $RANDOMBM_SKILL['H'], $RANDOMBM_SKILL['v'],
                    $RANDOMBM_SKILL['v'], $RANDOMBM_SKILL['B'], $RANDOMBM_SKILL['v'], $RANDOMBM_SKILL['t'],
                ),
            ),
            'responder003', 'responder004', 'RandomBMMixed', 'RandomBMMixed', 3
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'START_TURN');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('RandomBMMixed', 'Berserk', 'Maximum', 'Mighty', 'Trip', 'Value'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'RandomBMMixed', 'recipe' => 'HMv(4) v(8) H(10) M(10) (20)', 'originalRecipe' => 'HMv(4) v(8) H(10) M(10) (20)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'RandomBMMixed', 'recipe' => '(4) Bt(4) Btv(6) (10) v(20)', 'originalRecipe' => '(4) Bt(4) Btv(6) (10) v(20)', 'artFilename' => 'BMdefaultRound.png');
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Skill', 'Trip');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 25.5;
        $expData['playerDataArray'][1]['roundScore'] = 11;
        $expData['playerDataArray'][0]['sideScore'] = 9.7;
        $expData['playerDataArray'][1]['sideScore'] = -9.7;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 4, 'sides' => 4, 'skills' => array('Mighty', 'Maximum', 'Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'HMv(4)', 'description' => 'Mighty Maximum Value 4-sided die'),
            array('value' => 7, 'sides' => 8, 'skills' => array('Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'v(8)', 'description' => 'Value 8-sided die'),
            array('value' => 10, 'sides' => 10, 'skills' => array('Mighty'), 'properties' => array(), 'recipe' => 'H(10)', 'description' => 'Mighty 10-sided die'),
            array('value' => 10, 'sides' => 10, 'skills' => array('Maximum'), 'properties' => array(), 'recipe' => 'M(10)', 'description' => 'Maximum 10-sided die'),
            array('value' => 20, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 3, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => 1, 'sides' => 4, 'skills' => array('Berserk', 'Trip'), 'properties' => array(), 'recipe' => 'Bt(4)', 'description' => 'Berserk Trip 4-sided die'),
            array('value' => 1, 'sides' => 6, 'skills' => array('Berserk', 'Trip', 'Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'Btv(6)', 'description' => 'Berserk Trip Value 6-sided die'),
            array('value' => 1, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => 3, 'sides' => 20, 'skills' => array('Value'), 'properties' => array('ValueRelevantToScore'), 'recipe' => 'v(20)', 'description' => 'Value 20-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [HMv(4):4, v(8):7, H(10):10, M(10):10, (20):20], responder004 rolled [(4):3, Bt(4):1, Btv(6):1, (10):1, v(20):3]. responder004 has dice which are not counted for initiative due to die skills: [Bt(4), Btv(6)].'));
        $expData['gameActionLogCount'] = 2;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        // Verify that a Trip attack HMv(4):4 <= Bt(4) is rejected because Bt(4) won't be able to roll high enough once HMv(4) grows
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn_failure(
            array(),
            'The attacking die cannot roll high enough to capture the target die',
            $retval, array(array(1, 1), array(0, 0)),
            $gameId, 1, 'Trip', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        // Verify that a Trip attack HMv(4):4 <= Btv(6) is allowed because Btv(6) will still be able to roll high enough once HMv(4) grows
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(5),
            'responder004 performed Trip attack using [Btv(6):1] against [HMv(4):4]; Attacker Btv(6) rerolled 1 => 5; Defender HMv(4) recipe changed to HMv(6), rerolled 4 => 6, was not captured. ',
            $retval, array(array(1, 2), array(0, 0)),
            $gameId, 1, 'Trip', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Trip attack using [Btv(6):1] against [HMv(4):4]; Attacker Btv(6) rerolled 1 => 5; Defender HMv(4) recipe changed to HMv(6), rerolled 4 => 6, was not captured'));
        $expData['gameActionLogCount'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Mighty Maximum Value 6-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array("ValueRelevantToScore", "HasJustGrown");
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "HMv(6)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][0]['roundScore'] = 26.5;
        $expData['playerDataArray'][0]['sideScore'] = 9.0;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array("ValueRelevantToScore", "JustPerformedTripAttack", "JustPerformedUnsuccessfulAttack");
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][1]['roundScore'] = 13;
        $expData['playerDataArray'][1]['sideScore'] = -9.0;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }
}
