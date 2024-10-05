<?php

/**
 * responder04Test: API tests of the buttonmen responder, file 04
 *
 * This file contains numbered game playback tests 61-80.
 */

require_once __DIR__.'/responderTestFramework.php';

class responder04Test extends responderTestFramework {

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     */
    public function test_interface_game_00061() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 61;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array('bm_rand' => array(1, 49, 11, 46, 23, 12, 11), 'bm_skill_rand' => array()),
            'responder003', 'responder004', 'Boot2daHead', 'Wildcard', 3,
            '', NULL, 'gameId', array()
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Fire', 'Null', 'Ornery', 'Poison', 'Rage', 'Speed', 'Stealth', 'Turbo', 'Wildcard'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Boot2daHead', 'recipe' => 'd(1) Gz(Z) !p(V) !n(Y,Y) oF(C)', 'originalRecipe' => 'd(1) Gz(Z) !p(V) !n(Y,Y) oF(C)', 'artFilename' => 'boot2dahead.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Wildcard', 'recipe' => '(C) (C) (C) (C) (C)', 'originalRecipe' => '(C) (C) (C) (C) (C)', 'artFilename' => 'wildcard.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('Z' => array(4, 30), 'V' => array(6, 12), 'Y' => array(1, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Stealth'), 'properties' => array(), 'recipe' => 'd(1)', 'description' => 'Stealth 1-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Rage', 'Speed'), 'properties' => array(), 'recipe' => 'Gz(Z)', 'description' => 'Rage Speed Z Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Poison', 'Turbo'), 'properties' => array(), 'recipe' => 'p(V)!', 'description' => 'Poison Turbo V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Null', 'Turbo'), 'properties' => array('Twin'), 'recipe' => 'n(Y,Y)!', 'description' => 'Null Turbo Twin Y Swing Die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Ornery', 'Fire'), 'properties' => array(), 'recipe' => 'oF(C)', 'description' => 'Ornery Fire Wildcard die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(C)', 'description' => 'Wildcard die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(C)', 'description' => 'Wildcard die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(C)', 'description' => 'Wildcard die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(C)', 'description' => 'Wildcard die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(C)', 'description' => 'Wildcard die'),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(22, 2, 1, 4),
            $gameId, 1, array('V' => 6, 'Y' => 7, 'Z' => 29), NULL);

        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: V=6, Y=7, Z=29'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [d(1):1, Gz(Z=29):22, p(V=6)!:2, n(Y=7,Y=7)!:5, oF(C):JS], responder004 rolled [(C):QC, (C):9S, (C):QD, (C):AD, (C):KC]. responder003 has dice which are not counted for initiative due to die skills: [Gz(Z=29)].'));
        $expData['gameActionLogCount'] = 3;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Rage Speed Z Swing Die (with 29 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 29;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 22;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Poison Turbo V Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Null Turbo Twin Y Swing Die (both with 7 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray'] = array(array("sides" => "7", "value" => "1"), array("sides" => "7", "value" => "4"));
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][4]['wildcardPropsArray'] = array('type' => 'Wildcard', 'suit' => 'Spades', 'colour' => 'black', 'displayedValue' => 'J<span class="suit_black">&spades;</span>');
        $expData['playerDataArray'][0]['roundScore'] = 17;
        $expData['playerDataArray'][0]['sideScore'] = -15.3;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array("2" => array(6, 7, 8, 9, 10, 11, 12), "3" => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][0]['wildcardPropsArray'] = array('type' => 'Wildcard', 'suit' => 'Clubs', 'colour' => 'black', 'displayedValue' => 'Q<span class="suit_black">&clubs;</span>');
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][1]['wildcardPropsArray'] = array('type' => 'Wildcard', 'suit' => 'Spades', 'colour' => 'black', 'displayedValue' => '9<span class="suit_black">&spades;</span>');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][2]['wildcardPropsArray'] = array('type' => 'Wildcard', 'suit' => 'Diamonds', 'colour' => 'red', 'displayedValue' => 'Q<span class="suit_red">&diams;</span>');
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['wildcardPropsArray'] = array('type' => 'Wildcard', 'suit' => 'Diamonds', 'colour' => 'red', 'displayedValue' => 'A<span class="suit_red">&diams;</span>');
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 13;
        $expData['playerDataArray'][1]['activeDieArray'][4]['wildcardPropsArray'] = array('type' => 'Wildcard', 'suit' => 'Clubs', 'colour' => 'black', 'displayedValue' => 'K<span class="suit_black">&clubs;</span>');
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 15.3;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Speed");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(25, 13),
            'responder003 performed Speed attack using [Gz(Z=29):22] against [(C):9S,(C):KC]; Defender (C) was captured; Defender (C) was captured; Attacker Gz(Z=29) recipe changed from Gz(Z=29) to z(Z=29), rerolled 22 => 25. responder003\'s idle ornery dice rerolled at end of turn: oF(C) rerolled JS => AD. ',
            $retval, array(array(0, 1), array(1, 1), array(1, 4)),
            $gameId, 1, 'Speed', 0, 1, '', array());

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Speed attack using [Gz(Z=29):22] against [(C):9S,(C):KC]; Defender (C) was captured; Defender (C) was captured; Attacker Gz(Z=29) recipe changed from Gz(Z=29) to z(Z=29), rerolled 22 => 25'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003\'s idle ornery dice rerolled at end of turn: oF(C) rerolled JS => AD'));
        $expData['gameActionLogCount'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Speed Z Swing Die (with 29 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "z(Z)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Speed");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 25;
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array("HasJustRerolledOrnery");
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['wildcardPropsArray'] = array("colour" => "red", "displayedValue" => 'A<span class="suit_red">&diams;</span>', "suit" => "Diamonds", "type" => "Wildcard");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "(C)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 9;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '9<span class="suit_black">&spades;</span>', "suit" => "Spades", "type" => "Wildcard");
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "(C)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 13;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => 'K<span class="suit_black">&clubs;</span>', "suit" => "Clubs", "type" => "Wildcard");
        $expData['playerDataArray'][0]['roundScore'] = 49;
        $expData['playerDataArray'][0]['sideScore'] = 16.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][1]['wildcardPropsArray'] = array("colour" => "red", "displayedValue" => 'Q<span class="suit_red">&diams;</span>', "suit" => "Diamonds", "type" => "Wildcard");
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['wildcardPropsArray'] = array("colour" => "red", "displayedValue" => 'A<span class="suit_red">&diams;</span>', "suit" => "Diamonds", "type" => "Wildcard");
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 24;
        $expData['playerDataArray'][1]['sideScore'] = -16.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(43, 36, 5),
            'responder004 performed Skill attack using [(C):QC,(C):QD,(C):AD] against [z(Z=29):25]; Defender z(Z=29) was captured; Attacker (C) rerolled QC => 10S; Attacker (C) rerolled QD => 2S; Attacker (C) rerolled AD => 6C. ',
            $retval, array(array(1, 0), array(1, 1), array(1, 2), array(0, 1)),
            $gameId, 1, 'Skill', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [(C):QC,(C):QD,(C):AD] against [z(Z=29):25]; Defender z(Z=29) was captured; Attacker (C) rerolled QC => 10S; Attacker (C) rerolled QD => 2S; Attacker (C) rerolled AD => 6C'));
        $expData['gameActionLogCount'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Poison Turbo V Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "p(V)!";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Poison", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Null Turbo Twin Y Swing Die (both with 7 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("Twin");
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "n(Y,Y)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Null", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['subdieArray'] = array(array("sides" => "7", "value" => "1"), array("sides" => "7", "value" => "4"));
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 5;
        unset($expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray']);
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Ornery Fire Wildcard die";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "oF(C)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Ornery", "Fire");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['wildcardPropsArray'] = array("colour" => "red", "displayedValue" => 'A<span class="suit_red">&diams;</span>', "suit" => "Diamonds", "type" => "Wildcard");
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 34.5;
        $expData['playerDataArray'][0]['sideScore'] = -12.3;
        $expData['playerDataArray'][0]['turboSizeArray'] = array("1" => array(6, 7, 8, 9, 10, 11, 12), "2" => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20));
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][0]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '10<span class="suit_black">&spades;</span>', "suit" => "Spades", "type" => "Wildcard");
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '2<span class="suit_black">&spades;</span>', "suit" => "Spades", "type" => "Wildcard");
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '6<span class="suit_black">&clubs;</span>', "suit" => "Clubs", "type" => "Wildcard");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "z(Z)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 29;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 25;
        $expData['playerDataArray'][1]['roundScore'] = 53;
        $expData['playerDataArray'][1]['sideScore'] = 12.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(2, 50),
            'responder003 performed Power attack using [p(V=6)!:2] against [(C):2S]; Defender (C) was captured; Attacker p(V=6)! rerolled from 2. Turbo die p(V=6)! changed size from 6 to 9 sides, recipe changed from p(V=6)! to p(V=9)!, rolled 2. responder003\'s idle ornery dice rerolled at end of turn: oF(C) rerolled AD => Jkr (red). ',
            $retval, array(array(0, 1), array(1, 1)),
            $gameId, 1, 'Power', 0, 1, '', array(1 => 9));

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [p(V=6)!:2] against [(C):2S]; Defender (C) was captured; Attacker p(V=6)! rerolled from 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die p(V=6)! changed size from 6 to 9 sides, recipe changed from p(V=6)! to p(V=9)!, rolled 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003\'s idle ornery dice rerolled at end of turn: oF(C) rerolled AD => Jkr (red)'));
        $expData['gameActionLogCount'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Poison Turbo V Swing Die (with 9 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array("HasJustTurboed");
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array("HasJustRerolledOrnery");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][3]['wildcardPropsArray'] = array("colour" => "red", "displayedValue" => '<span class="suit_red">Jkr</span>', "suit" => "", "type" => "Wildcard");
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][2]['recipe'] = "(C)";
        $expData['playerDataArray'][0]['capturedDieArray'][2]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['value'] = 2;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '2<span class="suit_black">&spades;</span>', "suit" => "Spades", "type" => "Wildcard");
        $expData['playerDataArray'][0]['roundScore'] = 47.5;
        $expData['playerDataArray'][0]['sideScore'] = 1.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '6<span class="suit_black">&clubs;</span>', "suit" => "Clubs", "type" => "Wildcard");
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 45;
        $expData['playerDataArray'][1]['sideScore'] = -1.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(38),
            'responder004 performed Power attack using [(C):6C] against [n(Y=7,Y=7)!:5]; Defender n(Y=7,Y=7)! was captured; Attacker (C) rerolled 6C => 6S. ',
            $retval, array(array(1, 1), array(0, 2)),
            $gameId, 1, 'Power', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(C):6C] against [n(Y=7,Y=7)!:5]; Defender n(Y=7,Y=7)! was captured; Attacker (C) rerolled 6C => 6S'));
        $expData['gameActionLogCount'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        unset($expData['playerDataArray'][0]['activeDieArray'][2]['subdieArray']);
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Ornery Fire Wildcard die";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "oF(C)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Ornery", "Fire");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][2]['wildcardPropsArray'] = array("colour" => "red", "displayedValue" => '<span class="suit_red">Jkr</span>', "suit" => "", "type" => "Wildcard");
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array("1" => array(6, 7, 8, 9, 10, 11, 12));
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][1]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '6<span class="suit_black">&spades;</span>', "suit" => "Spades", "type" => "Wildcard");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured", "Twin");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "n(Y,Y)!";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 14;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['subdieArray'] = array(array("sides" => "7", "value" => "1"), array("sides" => "7", "value" => "4"));
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn_failure(
            array(),
            'Attacking die values do not sum up to target die value.',
            $retval, array(array(0, 1), array(1, 1)),
            $gameId, 1, 'Skill', 0, 1, '', array());

        $this->verify_api_submitTurn(
            array(),
            'responder003 chose to perform a Skill attack using [d(1):1,p(V=9)!:2] against [(C):10S]; responder003 must turn down fire dice to complete this attack. ',
            $retval, array(array(0, 0), array(0, 1), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '', array(1 => 6));

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 chose to perform a Skill attack using [d(1):1,p(V=9)!:2] against [(C):10S]; responder003 must turn down fire dice to complete this attack'));
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] = 11;
        $expData['gameState'] = 'ADJUST_FIRE_DICE';
        $expData['validAttackTypeArray'] = array('Skill');
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('IsAttacker');
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array('IsAttacker');
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array('IsAttackTarget');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_adjustFire(
            array(1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3, 30),
            'responder003 turned down fire dice: oF(C) from 20 to 13; Defender (C) was captured; Attacker d(1) rerolled 1 => 1; Attacker p(V=9)! rerolled from 2. Turbo die p(V=9)! changed size from 9 to 6 sides, recipe changed from p(V=9)! to p(V=6)!, rolled 3. responder003\'s idle ornery dice rerolled at end of turn: oF(C) rerolled KC => 5S. ',
            $retval, $gameId, 1, 'turndown', array(2), array(13));

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 turned down fire dice: oF(C) from 20 to 13; Defender (C) was captured; Attacker d(1) rerolled 1 => 1; Attacker p(V=9)! rerolled from 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die p(V=9)! changed size from 9 to 6 sides, recipe changed from p(V=9)! to p(V=6)!, rolled 3'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003\'s idle ornery dice rerolled at end of turn: oF(C) rerolled KC => 5S'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        $expData['gameActionLogCount'] = 14;
        $expData['gameState'] = 'START_TURN';
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['turboSizeArray'] = array("1" => array(6, 7, 8, 9, 10, 11, 12));
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][0]['roundScore'] = 66.5;
        $expData['playerDataArray'][0]['sideScore'] = 19.7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array('HasJustTurboed');
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Poison Turbo V Swing Die (with 6 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustRerolledOrnery');
        $expData['playerDataArray'][0]['activeDieArray'][2]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '5<span class="suit_black">&spades;</span>', "suit" => "Spades", "type" => "Wildcard");
        $expData['playerDataArray'][0]['capturedDieArray'][3]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][3]['recipe'] = "(C)";
        $expData['playerDataArray'][0]['capturedDieArray'][3]['sides'] = 20;
        $expData['playerDataArray'][0]['capturedDieArray'][3]['value'] = 10;
        $expData['playerDataArray'][0]['capturedDieArray'][3]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '10<span class="suit_black">&spades;</span>', "suit" => "Spades", "type" => "Wildcard");
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['roundScore'] = 37;
        $expData['playerDataArray'][1]['sideScore'] = -19.7;
        array_shift($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("Twin");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     */
    public function test_interface_game_00062() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 62;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array('bm_rand' => array(1, 17, 4, 4, 1, 1), 'bm_skill_rand' => array()),
            'responder003', 'responder004', 'Boot2daHead', 'Envy', 3,
            '', NULL, 'gameId', array()
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Doppelganger', 'Fire', 'Null', 'Ornery', 'Poison', 'Rage', 'Speed', 'Stealth', 'Turbo', 'Wildcard'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Boot2daHead', 'recipe' => 'd(1) Gz(Z) !p(V) !n(Y,Y) oF(C)', 'originalRecipe' => 'd(1) Gz(Z) !p(V) !n(Y,Y) oF(C)', 'artFilename' => 'boot2dahead.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Envy', 'recipe' => 'D(4) D(6) D(10) D(12) D(X)', 'originalRecipe' => 'D(4) D(6) D(10) D(12) D(X)', 'artFilename' => 'envy.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Stealth'), 'properties' => array(), 'recipe' => 'd(1)', 'description' => 'Stealth 1-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Rage', 'Speed'), 'properties' => array(), 'recipe' => 'Gz(Z)', 'description' => 'Rage Speed Z Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Poison', 'Turbo'), 'properties' => array(), 'recipe' => 'p(V)!', 'description' => 'Poison Turbo V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Null', 'Turbo'), 'properties' => array('Twin'), 'recipe' => 'n(Y,Y)!', 'description' => 'Null Turbo Twin Y Swing Die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Ornery', 'Fire'), 'properties' => array(), 'recipe' => 'oF(C)', 'description' => 'Ornery Fire Wildcard die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(4)', 'description' => 'Doppelganger 4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(6)', 'description' => 'Doppelganger 6-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(10)', 'description' => 'Doppelganger 10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(12)', 'description' => 'Doppelganger 12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Doppelganger'), 'properties' => array(), 'recipe' => 'D(X)', 'description' => 'Doppelganger X Swing Die'),
        );
        $expData['playerDataArray'][0]['swingRequestArray'] = array(
            'Z' => array(4, 30),
            'V' => array(6, 12),
            'Y' => array(1, 20),
        );
        $expData['playerDataArray'][1]['swingRequestArray'] = array(
            'X' => array(4, 20),
        );

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(13),
            $gameId, 1, array('X' => 15), NULL);

        $_SESSION = $this->mock_test_user_login('responder003');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set die sizes'));
        $expData['gameActionLogCount'] = 2;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(13, 10, 11, 6),
            $gameId, 1, array('V' => 10, 'Y' => 11, 'Z' => 21), NULL);

        $expData['activePlayerIdx'] = 1;
        $expData['gameActionLog'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'Game created by responder003'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=15'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: V=10, Y=11, Z=21'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 1. Initial die values: responder003 rolled [d(1):1, Gz(Z=21):13, p(V=10)!:10, n(Y=11,Y=11)!:17, oF(C):5D], responder004 rolled [D(4):4, D(6):4, D(10):1, D(12):1, D(X=15):13]. responder003 has dice which are not counted for initiative due to die skills: [Gz(Z=21)].'));
        $expData['gameActionLogCount'] = 4;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Rage Speed Z Swing Die (with 21 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 21;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 13;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Poison Turbo V Swing Die (with 10 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Null Turbo Twin Y Swing Die (both with 11 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 22;
        $expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray'] = array(array("sides" => "11", "value" => "11"), array("sides" => "11", "value" => "6"));
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 17;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][4]['wildcardPropsArray'] = array("colour" => "red", "displayedValue" => '5<span class="suit_red">&diams;</span>', "suit" => "Diamonds", "type" => "Wildcard");
        $expData['playerDataArray'][0]['roundScore'] = 9;
        $expData['playerDataArray'][0]['sideScore'] = -9.7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array("2" => array(6, 7, 8, 9, 10, 11, 12), "3" => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20));
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Doppelganger X Swing Die (with 15 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 15;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 13;
        $expData['playerDataArray'][1]['roundScore'] = 23.5;
        $expData['playerDataArray'][1]['sideScore'] = 9.7;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(7),
            'responder004 performed Power attack using [D(X=15):13] against [p(V=10)!:10]; Defender p(V=10)! was captured; Attacker D(X=15) changed size from 15 to 10 sides, recipe changed from D(X=15) to p(V=10)!, rerolled 13 => 7. ',
            $retval, array(array(1, 4), array(0, 2)),
            $gameId, 1, 'Power', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [D(X=15):13] against [p(V=10)!:10]; Defender p(V=10)! was captured; Attacker D(X=15) changed size from 15 to 10 sides, recipe changed from D(X=15) to p(V=10)!, rerolled 13 => 7'));
        $expData['gameActionLogCount'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Null Turbo Twin Y Swing Die (both with 11 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("Twin");
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "n(Y,Y)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 22;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Null", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['subdieArray'] = array(array("sides" => "11", "value" => "11"), array("sides" => "11", "value" => "6"));
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 17;
        unset($expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray']);
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Ornery Fire Wildcard die";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "oF(C)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Ornery", "Fire");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][3]['wildcardPropsArray'] = array("colour" => "red", "displayedValue" => '5<span class="suit_red">&diams;</span>', "suit" => "Diamonds", "type" => "Wildcard");
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['roundScore'] = 19;
        $expData['playerDataArray'][0]['sideScore'] = 12;
        $expData['playerDataArray'][0]['turboSizeArray'] = array("2" => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20));
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Poison Turbo V Swing Die (with 10 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array("HasJustDoppelgangered");
        $expData['playerDataArray'][1]['activeDieArray'][4]['recipe'] = "p(V)!";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][4]['skills'] = array("Poison", "Turbo");
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 7;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "p(V)!";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 10;
        $expData['playerDataArray'][1]['roundScore'] = 1;
        $expData['playerDataArray'][1]['sideScore'] = -12;
        $expData['playerDataArray'][1]['turboSizeArray'] = array("4" => array(6, 7, 8, 9, 10, 11, 12));
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Speed");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(9, 8, 4, 1, 3),
            'responder003 performed Power attack using [n(Y=11,Y=11)!:17] against [D(12):1]; Defender D(12) recipe changed to Dn(12), was captured; Attacker n(Y=11,Y=11)! rerolled from 17. Turbo die n(Y=11,Y=11)! changed size from 22 to 14 sides, recipe changed from n(Y=11,Y=11)! to n(Y=7,Y=7)!, rolled 5. responder003\'s idle ornery dice rerolled at end of turn: oF(C) rerolled 5D => 4C. ',
            $retval, array(array(0, 2), array(1, 3)),
            $gameId, 1, 'Power', 0, 1, '', array(2 => 7));

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [n(Y=11,Y=11)!:17] against [D(12):1]; Defender D(12) recipe changed to Dn(12), was captured; Attacker n(Y=11,Y=11)! rerolled from 17'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die n(Y=11,Y=11)! changed size from 22 to 14 sides, recipe changed from n(Y=11,Y=11)! to n(Y=7,Y=7)!, rolled 5'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003\'s idle ornery dice rerolled at end of turn: oF(C) rerolled 5D => 4C'));
        $expData['gameActionLogCount'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Null Turbo Twin Y Swing Die (both with 7 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("HasJustTurboed", "Twin");
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][2]['subdieArray'] = array(array("sides" => "7", "value" => "4"), array("sides" => "7", "value" => "1"));
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array("HasJustRerolledOrnery");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '4<span class="suit_black">&clubs;</span>', "suit" => "Clubs", "type" => "Wildcard");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "Dn(12)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 12;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['sideScore'] = 16;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "Poison Turbo V Swing Die (with 10 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "p(V)!";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['skills'] = array("Poison", "Turbo");
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 7;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = -5;
        $expData['playerDataArray'][1]['sideScore'] = -16;
        $expData['playerDataArray'][1]['turboSizeArray'] = array("3" => array(6, 7, 8, 9, 10, 11, 12));
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(4),
            'responder004 performed Skill attack using [D(4):4] against [oF(C):4C]; Defender oF(C) was captured; Attacker D(4) rerolled 4 => 4. ',
            $retval, array(array(1, 0), array(0, 3)),
            $gameId, 1, 'Skill', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [D(4):4] against [oF(C):4C]; Defender oF(C) was captured; Attacker D(4) rerolled 4 => 4'));
        $expData['gameActionLogCount'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("Twin");
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 11;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "oF(C)";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '4<span class="suit_black">&clubs;</span>', "suit" => "Clubs", "type" => "Wildcard");
        $expData['playerDataArray'][1]['roundScore'] = 11;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(4, 2, 20, 13),
            'responder003 performed Power attack using [n(Y=7,Y=7)!:5] against [D(6):4]; Defender D(6) recipe changed to Dn(6), was captured; Attacker n(Y=7,Y=7)! rerolled from 5. Turbo die n(Y=7,Y=7)! changed size from 14 to 40 sides, recipe changed from n(Y=7,Y=7)! to n(Y=20,Y=20)!, rolled 33. responder004 passed. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 1, 'Power', 0, 1, '', array(2 => 20));

        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [n(Y=7,Y=7)!:5] against [D(6):4]; Defender D(6) recipe changed to Dn(6), was captured; Attacker n(Y=7,Y=7)! rerolled from 5'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die n(Y=7,Y=7)! changed size from 14 to 40 sides, recipe changed from n(Y=7,Y=7)! to n(Y=20,Y=20)!, rolled 33'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        $expData['gameActionLogCount'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Null Turbo Twin Y Swing Die (both with 20 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 40;
        $expData['playerDataArray'][0]['activeDieArray'][2]['subdieArray'] = array(array("sides" => "20", "value" => "20"), array("sides" => "20", "value" => "13"));
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 33;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "Dn(6)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['sideScore'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "Doppelganger 10-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "D(10)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "Poison Turbo V Swing Die (with 10 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "p(V)!";
        $expData['playerDataArray'][1]['activeDieArray'][2]['skills'] = array("Poison", "Turbo");
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 7;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 8;
        $expData['playerDataArray'][1]['sideScore'] = -2;
        $expData['playerDataArray'][1]['turboSizeArray'] = array("2" => array(6, 7, 8, 9, 10, 11, 12));

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(17, 4, 2, 2),
            'responder003 performed Power attack using [n(Y=20,Y=20)!:33] against [p(V=10)!:7]; Defender p(V=10)! recipe changed to pn(V=10)!, was captured; Attacker n(Y=20,Y=20)! rerolled from 33. Turbo die n(Y=20,Y=20)! changed size from 40 to 4 sides, recipe changed from n(Y=20,Y=20)! to n(Y=2,Y=2)!, rolled 4. ',
            $retval, array(array(0, 2), array(1, 2)),
            $gameId, 1, 'Power', 0, 1, '', array(2 => 2));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [n(Y=20,Y=20)!:33] against [p(V=10)!:7]; Defender p(V=10)! recipe changed to pn(V=10)!, was captured; Attacker n(Y=20,Y=20)! rerolled from 33'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die n(Y=20,Y=20)! changed size from 40 to 4 sides, recipe changed from n(Y=20,Y=20)! to n(Y=2,Y=2)!, rolled 4'));
        $expData['gameActionLogCount'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Null Turbo Twin Y Swing Die (both with 2 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("HasJustTurboed", "Twin");
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['subdieArray'] = array(array("sides" => "2", "value" => "2"), array("sides" => "2", "value" => "2"));
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][2]['recipe'] = "pn(V)!";
        $expData['playerDataArray'][0]['capturedDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][0]['sideScore'] = -4.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 18;
        $expData['playerDataArray'][1]['sideScore'] = 4.7;
        $expData['playerDataArray'][1]['turboSizeArray'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3),
            'responder004 performed Skill attack using [D(4):4] against [n(Y=2,Y=2)!:4]; Defender n(Y=2,Y=2)! was captured; Attacker D(4) rerolled 4 => 3. ',
            $retval, array(array(1, 0), array(0, 2)),
            $gameId, 1, 'Skill', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [D(4):4] against [n(Y=2,Y=2)!:4]; Defender n(Y=2,Y=2)! was captured; Attacker D(4) rerolled 4 => 3'));
        $expData['gameActionLogCount'] = 15;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array("WasJustCaptured", "Twin");
        $expData['playerDataArray'][1]['capturedDieArray'][2]['recipe'] = "n(Y,Y)!";
        $expData['playerDataArray'][1]['capturedDieArray'][2]['sides'] = 4;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['subdieArray'] = array(array("sides" => "2", "value" => "2"), array("sides" => "2", "value" => "2"));
        $expData['playerDataArray'][1]['capturedDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(4),
            'responder003 performed Power attack using [Gz(Z=21):13] against [D(10):1]; Defender D(10) was captured; Attacker Gz(Z=21) recipe changed from Gz(Z=21) to z(Z=21), rerolled 13 => 4. responder004 passed. ',
            $retval, array(array(0, 1), array(1, 1)),
            $gameId, 1, 'Power', 0, 1, '', array());

        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [Gz(Z=21):13] against [D(10):1]; Defender D(10) was captured; Attacker Gz(Z=21) recipe changed from Gz(Z=21) to z(Z=21), rerolled 13 => 4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        $expData['gameActionLogCount'] = 17;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Speed Z Swing Die (with 21 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "z(Z)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Speed");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['capturedDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][3]['recipe'] = "D(10)";
        $expData['playerDataArray'][0]['capturedDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][0]['capturedDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 21;
        $expData['playerDataArray'][0]['sideScore'] = 5.3;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array("Twin");
        $expData['playerDataArray'][1]['roundScore'] = 13;
        $expData['playerDataArray'][1]['sideScore'] = -5.3;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(15, 1, 14, 6, 6, 1, 0, 1, 3, 10, 6),
            'responder003 performed Power attack using [z(Z=21):4] against [D(4):3]; Defender D(4) was captured; Attacker z(Z=21) rerolled 4 => 15. End of round: responder003 won round 1 (25 vs. 11). ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '', array());

        $expData['activePlayerIdx'] = null;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [z(Z=21):4] against [D(4):3]; Defender D(4) was captured; Attacker z(Z=21) rerolled 4 => 15'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: responder003 won round 1 (25 vs. 11)'));
        $expData['gameActionLogCount'] = 19;
        $expData['gameState'] = "SPECIFY_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Rage Speed Z Swing Die (with 21 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "Gz(Z)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Rage", "Speed");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Poison Turbo V Swing Die (with 10 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "p(V)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Poison", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Null Turbo Twin Y Swing Die (both with 11 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array("Twin");
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "n(Y,Y)!";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 22;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Null", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray'] = array(array("sides" => "11"), array("sides" => "11"));
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Ornery Fire Wildcard die";
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "oF(C)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array("Ornery", "Fire");
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][4]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => 'A<span class="suit_black">&clubs;</span>', "suit" => "Clubs", "type" => "Wildcard");
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        $expData['playerDataArray'][0]['gameScoreArray'] = array("D" => 0, "L" => 0, "W" => 1);
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array("V" => 10, "Y" => 11, "Z" => 21);
        $expData['playerDataArray'][0]['roundScore'] = null;
        $expData['playerDataArray'][0]['sideScore'] = null;
        $expData['playerDataArray'][0]['swingRequestArray'] = array("V" => array(6, 12), "Y" => array(1, 20), "Z" => array(4, 30));
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "Doppelganger 6-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "D(6)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['skills'] = array("Doppelganger");
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "Doppelganger 10-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "D(10)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][2]['skills'] = array("Doppelganger");
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "Doppelganger 12-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "D(12)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][3]['skills'] = array("Doppelganger");
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Doppelganger X Swing Die";
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][4]['recipe'] = "D(X)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][4]['skills'] = array("Doppelganger");
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = null;
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        $expData['playerDataArray'][1]['gameScoreArray'] = array("D" => 0, "L" => 1, "W" => 0);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array("X" => 15);
        $expData['playerDataArray'][1]['roundScore'] = null;
        $expData['playerDataArray'][1]['sideScore'] = null;
        $expData['playerDataArray'][1]['swingRequestArray'] = array("X" => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['roundNumber'] = 2;
        $expData['validAttackTypeArray'] = array();

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(1),
            $gameId, 2, array('X' => 7), NULL);

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004 won initiative for round 2. Initial die values: responder003 rolled [d(1):1, Gz(Z=21):14, p(V=10)!:6, n(Y=11,Y=11)!:7, oF(C):AC], responder004 rolled [D(4):1, D(6):3, D(10):10, D(12):6, D(X=7):1]. responder003 has dice which are not counted for initiative due to die skills: [Gz(Z=21)].'));
        $expData['gameActionLogCount'] = 21;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][3]['subdieArray'] = array(array("sides" => "11", "value" => "6"), array("sides" => "11", "value" => "1"));
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 9;
        $expData['playerDataArray'][0]['sideScore'] = -7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['turboSizeArray'] = array("2" => array(6, 7, 8, 9, 10, 11, 12), "3" => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20));
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Doppelganger X Swing Die (with 7 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 19.5;
        $expData['playerDataArray'][1]['sideScore'] = 7;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['validAttackTypeArray'] = array("Power", "Skill");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(48),
            'responder004 performed Power attack using [D(6):3] against [oF(C):AC]; Defender oF(C) was captured; Attacker D(6) changed size from 6 sides to Wildcard, recipe changed from D(6) to oF(C), rerolled 3 => 10S. ',
            $retval, array(array(1, 1), array(0, 4)),
            $gameId, 2, 'Power', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [D(6):3] against [oF(C):AC]; Defender oF(C) was captured; Attacker D(6) changed size from 6 sides to Wildcard, recipe changed from D(6) to oF(C), rerolled 3 => 10S'));
        $expData['gameActionLogCount'] = 22;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['roundScore'] = 1;
        $expData['playerDataArray'][0]['sideScore'] = -26.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "Ornery Fire Wildcard die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array("HasJustDoppelgangered");
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "oF(C)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][1]['skills'] = array("Ornery", "Fire");
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][1]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '10<span class="suit_black">&spades;</span>', "suit" => "Spades", "type" => "Wildcard");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "oF(C)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 20;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => 'A<span class="suit_black">&clubs;</span>', "suit" => "Clubs", "type" => "Wildcard");
        $expData['playerDataArray'][1]['roundScore'] = 40.5;
        $expData['playerDataArray'][1]['sideScore'] = 26.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(4),
            'responder003 performed Skill attack using [p(V=10)!:6] against [D(12):6]; Defender D(12) was captured; Attacker p(V=10)! rerolled from 6. Turbo die p(V=10)! changed size from 10 to 11 sides, recipe changed from p(V=10)! to p(V=11)!, rolled 4. ',
            $retval, array(array(0, 2), array(1, 3)),
            $gameId, 2, 'Skill', 0, 1, '', array(2 => 11));

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [p(V=10)!:6] against [D(12):6]; Defender D(12) was captured; Attacker p(V=10)! rerolled from 6'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Turbo die p(V=10)! changed size from 10 to 11 sides, recipe changed from p(V=10)! to p(V=11)!, rolled 4'));
        $expData['gameActionLogCount'] = 24;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Poison Turbo V Swing Die (with 11 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("HasJustTurboed");
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "D(12)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 12;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][0]['roundScore'] = 12;
        $expData['playerDataArray'][0]['sideScore'] = -15;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "Doppelganger X Swing Die (with 7 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "D(X)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 34.5;
        $expData['playerDataArray'][1]['sideScore'] = 15;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(),
            'responder004 chose to perform a Power attack using [D(4):1] against [p(V=11)!:4]; responder004 must turn down fire dice to complete this attack. ',
            $retval, array(array(1, 0), array(0, 2)),
            $gameId, 2, 'Power', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 chose to perform a Power attack using [D(4):1] against [p(V=11)!:4]; responder004 must turn down fire dice to complete this attack'));
        $expData['gameActionLogCount'] = 25;
        $expData['gameState'] = "ADJUST_FIRE_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("HasJustTurboed", "IsAttackTarget");
        $expData['playerDataArray'][0]['turboSizeArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array("IsAttacker");
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_adjustFire(
            array(52, 38, 37, 28, 40, 15, 36, 22, 14, 13, 3, 30, 20, 27, 32, 5, 2),
            'responder004 turned down fire dice: oF(C) from 10 to 7; Defender p(V=11)! was captured; Attacker D(4) changed size from 4 to 11 sides, recipe changed from D(4) to p(V=11)!, rerolled 1 => 5. responder004\'s idle ornery dice rerolled at end of turn: oF(C) rerolled 7S => 3C. ',
            $retval, $gameId, 2, 'turndown', array(1), array('7'));

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 turned down fire dice: oF(C) from 10 to 7; Defender p(V=11)! was captured; Attacker D(4) changed size from 4 to 11 sides, recipe changed from D(4) to p(V=11)!, rerolled 1 => 5'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004\'s idle ornery dice rerolled at end of turn: oF(C) rerolled 7S => 3C'));
        $expData['gameActionLogCount'] = 27;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Null Turbo Twin Y Swing Die (both with 11 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array("Twin");
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "n(Y,Y)!";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 22;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Null", "Turbo");
        $expData['playerDataArray'][0]['activeDieArray'][2]['subdieArray'] = array(array("sides" => "11", "value" => "6"), array("sides" => "11", "value" => "1"));
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 7;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 23;
        $expData['playerDataArray'][0]['sideScore'] = 4.7;
        $expData['playerDataArray'][0]['turboSizeArray'] = array("2" => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "Poison Turbo V Swing Die (with 11 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array("HasJustDoppelgangered");
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "p(V)!";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][0]['skills'] = array("Poison", "Turbo");
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array("HasJustRerolledOrnery");
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['wildcardPropsArray'] = array("colour" => "black", "displayedValue" => '3<span class="suit_black">&clubs;</span>', "suit" => "Clubs", "type" => "Wildcard");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "p(V)!";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 11;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['roundScore'] = 16;
        $expData['playerDataArray'][1]['sideScore'] = -4.7;
        $expData['playerDataArray'][1]['turboSizeArray'] = array("0" => array(6, 7, 8, 9, 10, 11, 12));
        $expData['validAttackTypeArray'] = array('Power', 'Speed');

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     */
    public function test_interface_game_00063() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 63;
        $_SESSION = $this->mock_test_user_login('responder003');


        $gameId = $this->verify_api_createGame(
            array('bm_rand' => array(3, 3, 7, 11, 1, 3, 7, 7), 'bm_skill_rand' => array()),
            'responder003', 'responder004', 'Pappy', 'Bruno', 3,
            '', NULL, 'gameId', array()
        );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Berserk', 'Bruno', 'Pappy'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Pappy', 'recipe' => '(4) (4) (10) B(20) (X) B(X)', 'originalRecipe' => '(4) (4) (10) B(20) (X)', 'artFilename' => 'pappy.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Bruno', 'recipe' => 'B(8) B(8) B(20) B(20) B(X) (X)', 'originalRecipe' => 'B(8) B(8) B(20) B(20) B(X)', 'artFilename' => 'bruno.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(20)', 'description' => 'Berserk 20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(X)', 'description' => 'Berserk X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 8, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(8)', 'description' => 'Berserk 8-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(8)', 'description' => 'Berserk 8-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(20)', 'description' => 'Berserk 20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(20)', 'description' => 'Berserk 20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Berserk'), 'properties' => array(), 'recipe' => 'B(X)', 'description' => 'Berserk X Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'The button recipe for Pappy changed from [(4) (4) (10) B(20) (X)] to [(4) (4) (10) B(20) (X) B(X)] because of Pappy\'s button special against Bruno'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'The button recipe for Bruno changed from [B(8) B(8) B(20) B(20) B(X)] to [B(8) B(8) B(20) B(20) B(X) (X)] because of Bruno\'s button special against Pappy'));
        $expData['gameActionLogCount'] = 3;

        $expData['gameId'] = $gameId;
        $expData['playerDataArray'][0]['playerId'] = $this->user_ids['responder003'];
        $expData['playerDataArray'][1]['playerId'] = $this->user_ids['responder004'];

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(2, 1),
            $gameId, 1, array('X' => 9), NULL);

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set die sizes'));
        $expData['gameActionLogCount'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "X Swing Die (with 9 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][5]['description'] = "Berserk X Swing Die (with 9 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][5]['sides'] = 9;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(10, 8),
            $gameId, 1, array('X' => 14), NULL);

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        $expData['gameActionLog'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'Game created by responder003'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'The button recipe for Pappy changed from [(4) (4) (10) B(20) (X)] to [(4) (4) (10) B(20) (X) B(X)] because of Pappy\'s button special against Bruno'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'The button recipe for Bruno changed from [B(8) B(8) B(20) B(20) B(X)] to [B(8) B(8) B(20) B(20) B(X) (X)] because of Bruno\'s button special against Pappy'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=9'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=14'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(4):3, (4):3, (10):7, B(20):11, (X=9):2, B(X=9):1], responder004 rolled [B(8):1, B(8):3, B(20):7, B(20):7, B(X=14):10, (X=14):8].'));
        $expData['gameActionLogCount'] = 6;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][5]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 28;
        $expData['playerDataArray'][0]['sideScore'] = -9.3;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "Berserk X Swing Die (with 14 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 14;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][5]['description'] = "X Swing Die (with 14 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][5]['sides'] = 14;
        $expData['playerDataArray'][1]['activeDieArray'][5]['value'] = 8;
        $expData['playerDataArray'][1]['roundScore'] = 42;
        $expData['playerDataArray'][1]['sideScore'] = 9.3;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Berserk");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(1),
            'responder003 performed Power attack using [(X=9):2] against [B(8):1]; Defender B(8) was captured; Attacker (X=9) rerolled 2 => 1. ',
            $retval, array(array(0, 4), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '', array());

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(X=9):2] against [B(8):1]; Defender B(8) was captured; Attacker (X=9) rerolled 2 => 1'));
        $expData['gameActionLogCount'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "B(8)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 8;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 36;
        $expData['playerDataArray'][0]['sideScore'] = -1.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "Berserk 20-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "B(20)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "Berserk X Swing Die (with 14 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "B(X)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 14;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "X Swing Die (with 14 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 38;
        $expData['playerDataArray'][1]['sideScore'] = 1.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Berserk");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(4),
            'responder004 performed Berserk attack using [B(X=14):10] against [(4):3,(10):7]; Defender (4) was captured; Defender (10) was captured; Attacker B(X=14) changed size from 14 to 7 sides, recipe changed from B(X=14) to (X=7), rerolled 10 => 4. ',
            $retval, array(array(1, 3), array(0, 0), array(0, 2)),
            $gameId, 1, 'Berserk', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Berserk attack using [B(X=14):10] against [(4):3,(10):7]; Defender (4) was captured; Defender (10) was captured; Attacker B(X=14) changed size from 14 to 7 sides, recipe changed from B(X=14) to (X=7), rerolled 10 => 4'));
        $expData['gameActionLogCount'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Berserk 20-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "B(20)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Berserk");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "X Swing Die (with 9 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "(X)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Berserk X Swing Die (with 9 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "B(X)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 29;
        $expData['playerDataArray'][0]['sideScore'] = -13;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "X Swing Die (with 7 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array("HasJustSplit", "JustPerformedBerserkAttack");
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "(4)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 4;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "(10)";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 10;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][1]['roundScore'] = 48.5;
        $expData['playerDataArray'][1]['sideScore'] = 13;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Berserk");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     */
    public function test_interface_game_00064() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 64;
        $_SESSION = $this->mock_test_user_login('responder003');


          $gameId = $this->verify_api_createGame(
              array('bm_rand' => array(1, 4, 4, 12, 4, 10, 3), 'bm_skill_rand' => array()),
              'responder003', 'responder004', 'Cheathem', 'Mutton Ben', 3,
              '', NULL, 'gameId', array()
          );

        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Mighty', 'Ornery', 'Poison', 'Rush', 'Shadow'));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Cheathem', 'recipe' => 'Ho(1) s(6) o(10) o(14) s(X)', 'originalRecipe' => 'Ho(1) s(6) o(10) o(14) s(X)', 'artFilename' => 'cheathem.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Mutton Ben', 'recipe' => 'p(8) #(12) s(20) (X) (X)', 'originalRecipe' => 'p(8) #(12) s(20) (X) (X)', 'artFilename' => 'muttonben.png');
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Mighty', 'Ornery'), 'properties' => array(), 'recipe' => 'Ho(1)', 'description' => 'Mighty Ornery 1-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(6)', 'description' => 'Shadow 6-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Ornery'), 'properties' => array(), 'recipe' => 'o(10)', 'description' => 'Ornery 10-sided die'),
            array('value' => NULL, 'sides' => 14, 'skills' => array('Ornery'), 'properties' => array(), 'recipe' => 'o(14)', 'description' => 'Ornery 14-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(X)', 'description' => 'Shadow X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 8, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(8)', 'description' => 'Poison 8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array('Rush'), 'properties' => array(), 'recipe' => '#(12)', 'description' => 'Rush 12-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(20)', 'description' => 'Shadow 20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );

        $expData['gameId'] = $gameId;
        $expData['playerDataArray'][0]['playerId'] = $this->user_ids['responder003'];
        $expData['playerDataArray'][1]['playerId'] = $this->user_ids['responder004'];

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(1),
            $gameId, 1, array('X' => 14), NULL);

        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set die sizes'));
        $expData['gameActionLogCount'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Shadow X Swing Die (with 14 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 14;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(10, 7),
            $gameId, 1, array('X' => 12), NULL);

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        $expData['gameActionLog'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'Game created by responder003'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=14'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 set swing values: X=12'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [Ho(1):1, s(6):4, o(10):4, o(14):12, s(X=14):1], responder004 rolled [p(8):4, #(12):10, s(20):3, (X=12):10, (X=12):7].'));
        $expData['gameActionLogCount'] = 4;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = 22.5;
        $expData['playerDataArray'][0]['sideScore'] = 1.7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "X Swing Die (with 12 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "X Swing Die (with 12 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 7;
        $expData['playerDataArray'][1]['roundScore'] = 20;
        $expData['playerDataArray'][1]['sideScore'] = -1.7;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Shadow");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(4, 1, 14),
            'responder003 performed Skill attack using [o(10):4] against [p(8):4]; Defender p(8) was captured; Attacker o(10) rerolled 4 => 4. responder003\'s idle ornery dice rerolled at end of turn: Ho(1) changed size from 1 to 2 sides, recipe changed from Ho(1) to Ho(2), rerolled 1 => 1; o(14) rerolled 12 => 14. ',
            $retval, array(array(0, 2), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '', array());

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [o(10):4] against [p(8):4]; Defender p(8) was captured; Attacker o(10) rerolled 4 => 4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003\'s idle ornery dice rerolled at end of turn: Ho(1) changed size from 1 to 2 sides, recipe changed from Ho(1) to Ho(2), rerolled 1 => 1; o(14) rerolled 12 => 14'));
        $expData['gameActionLogCount'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Mighty Ornery 2-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array("HasJustGrown", "HasJustRerolledOrnery");
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "Ho(2)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array("HasJustRerolledOrnery");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 14;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][0]['recipe'] = "p(8)";
        $expData['playerDataArray'][0]['capturedDieArray'][0]['sides'] = 8;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['roundScore'] = 19;
        $expData['playerDataArray'][0]['sideScore'] = -6;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "Rush 12-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "#(12)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][0]['skills'] = array("Rush");
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "Shadow 20-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "s(20)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][1]['skills'] = array("Shadow");
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "X Swing Die (with 12 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][2]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 7;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][1]['sideScore'] = 6;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Shadow");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2),
            'responder004 performed Shadow attack using [s(20):3] against [o(14):14]; Defender o(14) was captured; Attacker s(20) rerolled 3 => 2. ',
            $retval, array(array(1, 1), array(0, 3)),
            $gameId, 1, 'Shadow', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Shadow attack using [s(20):3] against [o(14):14]; Defender o(14) was captured; Attacker s(20) rerolled 3 => 2'));
        $expData['gameActionLogCount'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Shadow X Swing Die (with 14 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "s(X)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Shadow");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 12;
        $expData['playerDataArray'][0]['sideScore'] = -20;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][0]['recipe'] = "o(14)";
        $expData['playerDataArray'][1]['capturedDieArray'][0]['sides'] = 14;
        $expData['playerDataArray'][1]['capturedDieArray'][0]['value'] = 14;
        $expData['playerDataArray'][1]['roundScore'] = 42;
        $expData['playerDataArray'][1]['sideScore'] = 20;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Shadow");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(1, 6, 7, 4),
            'responder003 performed Skill attack using [Ho(2):1,s(6):4,o(10):4,s(X=14):1] against [(X=12):10]; Defender (X=12) was captured; Attacker Ho(2) changed size from 2 to 4 sides, recipe changed from Ho(2) to Ho(4), rerolled 1 => 1; Attacker s(6) rerolled 4 => 6; Attacker o(10) rerolled 4 => 7; Attacker s(X=14) rerolled 1 => 4. ',
            $retval, array(array(0, 0), array(0, 1), array(0, 2), array(0, 3), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '', array());

        $expData['activePlayerIdx'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [Ho(2):1,s(6):4,o(10):4,s(X=14):1] against [(X=12):10]; Defender (X=12) was captured; Attacker Ho(2) changed size from 2 to 4 sides, recipe changed from Ho(2) to Ho(4), rerolled 1 => 1; Attacker s(6) rerolled 4 => 6; Attacker o(10) rerolled 4 => 7; Attacker s(X=14) rerolled 1 => 4'));
        $expData['gameActionLogCount'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Mighty Ornery 4-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array("HasJustGrown");
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "Ho(4)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 4;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][1]['recipe'] = "(X)";
        $expData['playerDataArray'][0]['capturedDieArray'][1]['sides'] = 12;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['value'] = 10;
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][0]['sideScore'] = -7.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 7;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 36;
        $expData['playerDataArray'][1]['sideScore'] = 7.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Rush", "Shadow");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(3),
            'responder004 performed Power attack using [(X=12):7] against [Ho(4):1]; Defender Ho(4) was captured; Attacker (X=12) rerolled 7 => 3. ',
            $retval, array(array(1, 2), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = 0;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(X=12):7] against [Ho(4):1]; Defender Ho(4) was captured; Attacker (X=12) rerolled 7 => 3'));
        $expData['gameActionLogCount'] = 9;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Shadow 6-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "s(6)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Shadow");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Ornery 10-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "o(10)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Ornery");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Shadow X Swing Die (with 14 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "s(X)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Shadow");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['canStillWin'] = true;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 23;
        $expData['playerDataArray'][0]['sideScore'] = -11.3;
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['canStillWin'] = true;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][1]['capturedDieArray'][1]['recipe'] = "Ho(4)";
        $expData['playerDataArray'][1]['capturedDieArray'][1]['sides'] = 4;
        $expData['playerDataArray'][1]['capturedDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 11.3;
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Shadow");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn(
            array(3, 11, 1),
            'responder003 performed Skill attack using [s(6):6,s(X=14):4] against [#(12):10]; Defender #(12) was captured; Attacker s(6) rerolled 6 => 3; Attacker s(X=14) rerolled 4 => 11. responder003\'s idle ornery dice rerolled at end of turn: o(10) rerolled 7 => 1. ',
            $retval, array(array(0, 0), array(0, 2), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '', array());

        $expData['activePlayerIdx'] = 1;
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [s(6):6,s(X=14):4] against [#(12):10]; Defender #(12) was captured; Attacker s(6) rerolled 6 => 3; Attacker s(X=14) rerolled 4 => 11'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003\'s idle ornery dice rerolled at end of turn: o(10) rerolled 7 => 1'));
        $expData['gameActionLogCount'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array("HasJustRerolledOrnery");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 11;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array("WasJustCaptured");
        $expData['playerDataArray'][0]['capturedDieArray'][2]['recipe'] = "#(12)";
        $expData['playerDataArray'][0]['capturedDieArray'][2]['sides'] = 12;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][0]['roundScore'] = 35;
        $expData['playerDataArray'][0]['sideScore'] = 0.7;
        $expData['playerDataArray'][0]['waitingOnAction'] = false;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "Shadow 20-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "s(20)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][0]['skills'] = array("Shadow");
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "X Swing Die (with 12 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][1]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 3;
        array_pop($expData['playerDataArray'][1]['activeDieArray']);
        $expData['playerDataArray'][1]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 34;
        $expData['playerDataArray'][1]['sideScore'] = -0.7;
        $expData['playerDataArray'][1]['waitingOnAction'] = true;

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(17),
            'responder004 performed Shadow attack using [s(20):2] against [s(6):3]; Defender s(6) was captured; Attacker s(20) rerolled 2 => 17. responder003 passed. ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 1, 'Shadow', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Shadow attack using [s(20):2] against [s(6):3]; Defender s(6) was captured; Attacker s(20) rerolled 2 => 17'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        $expData['gameActionLogCount'] = 13;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Ornery 10-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "o(10)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Ornery");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Shadow X Swing Die (with 14 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "s(X)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][1]['skills'] = array("Shadow");
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 11;
        array_pop($expData['playerDataArray'][0]['activeDieArray']);
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = -5.3;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 17;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][2]['recipe'] = "s(6)";
        $expData['playerDataArray'][1]['capturedDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][1]['capturedDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 5.3;
        $expData['validAttackTypeArray'] = array("Power");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2, 1, 5, 10, 7, 4, 6, 5, 6, 9),
            'responder004 performed Power attack using [(X=12):3] against [o(10):1]; Defender o(10) was captured; Attacker (X=12) rerolled 3 => 2. responder003 passed. responder004 passed. End of round: responder004 won round 1 (50 vs. 27). ',
            $retval, array(array(1, 1), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '', array());

        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['activePlayerIdx'] = null;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(X=12):3] against [o(10):1]; Defender o(10) was captured; Attacker (X=12) rerolled 3 => 2'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: responder004 won round 1 (50 vs. 27)'));
        $expData['gameActionLogCount'] = 17;
        $expData['gameState'] = "SPECIFY_DICE";
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = "Mighty Ornery 1-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][0]['recipe'] = "Ho(1)";
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][0]['skills'] = array("Mighty", "Ornery");
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = "Shadow 6-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][1]['recipe'] = "s(6)";
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = "Ornery 10-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][2]['recipe'] = "o(10)";
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][2]['skills'] = array("Ornery");
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = "Ornery 14-sided die";
        $expData['playerDataArray'][0]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][3]['recipe'] = "o(14)";
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 14;
        $expData['playerDataArray'][0]['activeDieArray'][3]['skills'] = array("Ornery");
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Shadow X Swing Die";
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = "s(X)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = null;
        $expData['playerDataArray'][0]['activeDieArray'][4]['skills'] = array("Shadow");
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = null;
        $expData['playerDataArray'][0]['canStillWin'] = null;
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        array_pop($expData['playerDataArray'][0]['capturedDieArray']);
        $expData['playerDataArray'][0]['gameScoreArray'] = array("D" => 0, "L" => 1, "W" => 0);
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array("X" => 14);
        $expData['playerDataArray'][0]['roundScore'] = null;
        $expData['playerDataArray'][0]['sideScore'] = null;
        $expData['playerDataArray'][0]['swingRequestArray'] = array("X" => array(4, 20));
        $expData['playerDataArray'][0]['waitingOnAction'] = true;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = "Poison 8-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = "p(8)";
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][0]['skills'] = array("Poison");
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = "Rush 12-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = "#(12)";
        $expData['playerDataArray'][1]['activeDieArray'][1]['skills'] = array("Rush");
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = "Shadow 20-sided die";
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = "s(20)";
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 20;
        $expData['playerDataArray'][1]['activeDieArray'][2]['skills'] = array("Shadow");
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = "X Swing Die (with 12 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][3]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = null;
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] = "X Swing Die (with 12 sides)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][4]['recipe'] = "(X)";
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][4]['skills'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = null;
        $expData['playerDataArray'][1]['canStillWin'] = null;
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        array_pop($expData['playerDataArray'][1]['capturedDieArray']);
        $expData['playerDataArray'][1]['gameScoreArray'] = array("D" => 0, "L" => 0, "W" => 1);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array("X" => 12);
        $expData['playerDataArray'][1]['roundScore'] = null;
        $expData['playerDataArray'][1]['sideScore'] = null;
        $expData['playerDataArray'][1]['swingRequestArray'] = array("X" => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = false;
        $expData['roundNumber'] = 2;
        $expData['validAttackTypeArray'] = array();

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitDieValues(
            array(6),
            $gameId, 2, array('X' => 20), NULL);

        $expData['activePlayerIdx'] = 0;
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=20'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [Ho(1):1, s(6):5, o(10):10, o(14):7, s(X=20):6], responder004 rolled [p(8):4, #(12):6, s(20):5, (X=12):6, (X=12):9].'));
        $expData['gameActionLogCount'] = 19;
        $expData['gameState'] = "START_TURN";
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = "Shadow X Swing Die (with 20 sides)";
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 6;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][0]['roundScore'] = 25.5;
        $expData['playerDataArray'][0]['sideScore'] = 3.7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 9;
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['roundScore'] = 20;
        $expData['playerDataArray'][1]['sideScore'] = -3.7;
        $expData['playerDataArray'][1]['swingRequestArray'] = array();
        $expData['validAttackTypeArray'] = array("Power", "Skill", "Rush", "Shadow");

        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);

        $this->verify_api_submitTurn_failure(
            array(),
            'Attack type Pass is not valid right now.',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '', array());
    }
}

