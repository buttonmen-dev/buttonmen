<?php

/** Alternative responder which doesn't use real databases or
 *  sessions, but rather exists only to send dummy data used for
 *  automated testing of API compliance
 */

class dummy_responder {

    // properties

    // N.B. this class is always used for some type of testing,
    // but, the usage here matches the way responder uses this flag:
    // * False: this instance is being accessed remotely via POST
    // * True:  this instance is being accessed locally by unit tests
    private $isTest;               // whether this invocation is for testing

    // Set of keys expected by each responder argument type
    private $keylists = array(
        'submitSwingValues' => array('type', 'game', 'roundNumber', 'swingValueArray', 'timestamp'),
        'reactToInitiative' => array('type', 'game', 'roundNumber', 'timestamp',
                                     'action', 'dieIdxArray', 'dieValueArray')
    );

    // constructor
    // * For live invocation:
    //   * start a session (don't use api_core because dummy_responder has no backend)
    // * For test invocation:
    //   * don't start a session
    public function __construct($isTest = FALSE) {
        $this->isTest = $isTest;

        if (!($this->isTest)) {
            session_start();
        }
    }

    // This function verifies that the set of keys provided as
    // arguments is exactly the expected set
    protected function verify_key_list($args, $keylist) {
        foreach ($keylist as $idx => $key) {
            if (!(array_key_exists($key, $args))) {
                return False;
            }
        }
        foreach ($args as $key => $value) {
            if (!(in_array($key, $keylist))) {
                return False;
            }
        }
        return True;
    }

    // look for errors in the argument list
    protected function is_arg_list_error($args) {
        if (!(array_key_exists('type', $args))) {
            return "no type argument specified";
        }

        if (array_key_exists($args['type'], $this->keylists)) {
            $keylist = $this->keylists[$args['type']];
            if (!($this->verify_key_list($args, $keylist))) {
                return ('responder error: ' . $args['type'] . ' expects keys: ' . implode(',', $keylist));
            }
        }
        return NULL;
    }

    // This function looks at the provided arguments, fakes appropriate
    // data to match the public API, and returns either some game
    // data on success, or NULL on failure.  (Failure will happen if
    // the requested arguments are invalid.)
    protected function get_interface_response($args) {

        $argerror = $this->is_arg_list_error($args);
        if ($argerror) {
            return array(NULL, "responder error: $argerror");
        }

        if ($args['type'] == 'createUser') {
            $dummy_users = array(
                'tester1' => 1,
                'tester2' => 2,
                'tester3' => 3);
            $username = $args['username'];
            if (array_key_exists($username, $dummy_users)) {
                $userid = $dummy_users[$username];
                return array(NULL, "$username already exists (id=$userid)");
            }
            return array(array('userName' => $username), "User $username created successfully");
        }

        // for verisimilitude, choose a game ID of one greater than
        // the number of "existing" games represented in loadGameData
        // and loadActiveGames
        if ($args['type'] == 'createGame') {
            $gameId = '10';
            return array(array('gameId' => $gameId), "Game $gameId created successfully.");
        }

        // Use the same fake games here which were described in loadGameData
        if ($args['type'] == 'loadActiveGames') {
            $data = array(
                'gameIdArray' => array(),
                'opponentIdArray' => array(),
                'opponentNameArray' => array(),
                'myButtonNameArray' => array(),
                'opponentButtonNameArray' => array(),
                'nWinsArray' => array(),
                'nLossesArray' => array(),
                'nDrawsArray' => array(),
                'nTargetWinsArray' => array(),
                'isAwaitingActionArray' => array(),
                'gameStateArray' => array(),
                'statusArray' => array(),
            );

            // game 1
            $data['gameIdArray'][] = "1";
            $data['opponentIdArray'][] = "2";
            $data['opponentNameArray'][] = "tester2";
            $data['myButtonNameArray'][] = "Avis";
            $data['opponentButtonNameArray'][] = "Avis";
            $data['nWinsArray'][] = "0";
            $data['nLossesArray'][] = "0";
            $data['nDrawsArray'][] = "0";
            $data['nTargetWinsArray'][] = "3";
            $data['isAwaitingActionArray'][] = "1";
            $data['gameStateArray'][] = "24";
            $data['statusArray'][] = "ACTIVE";

            // game 2
            $data['gameIdArray'][] = "2";
            $data['opponentIdArray'][] = "2";
            $data['opponentNameArray'][] = "tester2";
            $data['myButtonNameArray'][] = "Avis";
            $data['opponentButtonNameArray'][] = "Avis";
            $data['nWinsArray'][] = "0";
            $data['nLossesArray'][] = "0";
            $data['nDrawsArray'][] = "0";
            $data['nTargetWinsArray'][] = "3";
            $data['isAwaitingActionArray'][] = "0";
            $data['gameStateArray'][] = "24";
            $data['statusArray'][] = "ACTIVE";

            // game 3
            $data['gameIdArray'][] = "3";
            $data['opponentIdArray'][] = "2";
            $data['opponentNameArray'][] = "tester2";
            $data['myButtonNameArray'][] = "Avis";
            $data['opponentButtonNameArray'][] = "Avis";
            $data['nWinsArray'][] = "0";
            $data['nLossesArray'][] = "0";
            $data['nDrawsArray'][] = "0";
            $data['nTargetWinsArray'][] = "3";
            $data['isAwaitingActionArray'][] = "1";
            $data['gameStateArray'][] = "40";
            $data['statusArray'][] = "ACTIVE";

            // game 4
            $data['gameIdArray'][] = "4";
            $data['opponentIdArray'][] = "2";
            $data['opponentNameArray'][] = "tester2";
            $data['myButtonNameArray'][] = "Avis";
            $data['opponentButtonNameArray'][] = "Avis";
            $data['nWinsArray'][] = "0";
            $data['nLossesArray'][] = "0";
            $data['nDrawsArray'][] = "0";
            $data['nTargetWinsArray'][] = "3";
            $data['isAwaitingActionArray'][] = "0";
            $data['gameStateArray'][] = "40";
            $data['statusArray'][] = "ACTIVE";

            // game 5
            $data['gameIdArray'][] = "5";
            $data['opponentIdArray'][] = "2";
            $data['opponentNameArray'][] = "tester2";
            $data['myButtonNameArray'][] = "Avis";
            $data['opponentButtonNameArray'][] = "Avis";
            $data['nWinsArray'][] = "0";
            $data['nLossesArray'][] = "0";
            $data['nDrawsArray'][] = "0";
            $data['nTargetWinsArray'][] = "3";
            $data['isAwaitingActionArray'][] = "0";
            $data['gameStateArray'][] = "60";
            $data['statusArray'][] = "COMPLETE";

            // game 6
            $data['gameIdArray'][] = "6";
            $data['opponentIdArray'][] = "2";
            $data['opponentNameArray'][] = "tester2";
            $data['myButtonNameArray'][] = "Buck";
            $data['opponentButtonNameArray'][] = "Von Pinn";
            $data['nWinsArray'][] = "0";
            $data['nLossesArray'][] = "0";
            $data['nDrawsArray'][] = "0";
            $data['nTargetWinsArray'][] = "3";
            $data['isAwaitingActionArray'][] = "1";
            $data['gameStateArray'][] = "24";
            $data['statusArray'][] = "ACTIVE";

            // game 7
            $data['gameIdArray'][] = "7";
            $data['opponentIdArray'][] = "2";
            $data['opponentNameArray'][] = "tester2";
            $data['myButtonNameArray'][] = "Crab";
            $data['opponentButtonNameArray'][] = "Crab";
            $data['nWinsArray'][] = "0";
            $data['nLossesArray'][] = "0";
            $data['nDrawsArray'][] = "0";
            $data['nTargetWinsArray'][] = "3";
            $data['isAwaitingActionArray'][] = "1";
            $data['gameStateArray'][] = "27";
            $data['statusArray'][] = "ACTIVE";

            // game 8
            $data['gameIdArray'][] = "8";
            $data['opponentIdArray'][] = "2";
            $data['opponentNameArray'][] = "tester2";
            $data['myButtonNameArray'][] = "John Kovalic";
            $data['opponentButtonNameArray'][] = "John Kovalic";
            $data['nWinsArray'][] = "0";
            $data['nLossesArray'][] = "0";
            $data['nDrawsArray'][] = "0";
            $data['nTargetWinsArray'][] = "3";
            $data['isAwaitingActionArray'][] = "1";
            $data['gameStateArray'][] = "27";
            $data['statusArray'][] = "ACTIVE";

            // game 9
            $data['gameIdArray'][] = "9";
            $data['opponentIdArray'][] = "2";
            $data['opponentNameArray'][] = "tester2";
            $data['myButtonNameArray'][] = "John Kovalic";
            $data['opponentButtonNameArray'][] = "John Kovalic";
            $data['nWinsArray'][] = "0";
            $data['nLossesArray'][] = "0";
            $data['nDrawsArray'][] = "0";
            $data['nTargetWinsArray'][] = "3";
            $data['isAwaitingActionArray'][] = "0";
            $data['gameStateArray'][] = "27";
            $data['statusArray'][] = "ACTIVE";

            return array($data, "All game details retrieved successfully.");
        }

        if ($args['type'] == 'loadButtonNames') {
            $data = array(
              'buttonNameArray' => array(),
              'recipeArray' => array(),
              'hasUnimplementedSkillArray' => array(),
            );

            // a button with no special skills
            $data['buttonNameArray'][] = "Avis";
            $data['recipeArray'][] = "(4) (4) (10) (12) (X)";
            $data['hasUnimplementedSkillArray'][] = false;

            // a button with an unimplemented skill
            $data['buttonNameArray'][] = "Adam Spam";
            $data['recipeArray'][] = "F(4) F(6) (6) (12) (X)";
            $data['hasUnimplementedSkillArray'][] = true;

            // a button with four dice and some implemented skills
            $data['buttonNameArray'][] = "Jellybean";
            $data['recipeArray'][] = "p(20) s(20) (V) (X)";
            $data['hasUnimplementedSkillArray'][] = false;

            // Buck
            $data['buttonNameArray'][] = "Buck";
            $data['recipeArray'][] = "(6,6) (10) (12) (20) (W,W)";
            $data['hasUnimplementedSkillArray'][] = false;

            // Von Pinn
            $data['buttonNameArray'][] = "Von Pinn";
            $data['recipeArray'][] = "(4) p(6,6) (10) (20) (W)";
            $data['hasUnimplementedSkillArray'][] = false;

            // Crab: a button with focus dice
            $data['buttonNameArray'][] = "Crab";
            $data['recipeArray'][] = "(8) (10) (12) f(20) f(20)";
            $data['hasUnimplementedSkillArray'][] = false;

            // John Kovalic: a button with chance dice
            $data['buttonNameArray'][] = "John Kovalic";
            $data['recipeArray'][] = "(6) c(6) (10) (12) c(20)";
            $data['hasUnimplementedSkillArray'][] = false;

            return array($data, "All button names retrieved successfully.");
        }

        // The dummy loadGameData returns one of a number of
        // sets of dummy game data, for general test use.
        // Specify which one you want using the game number:
        //   1: a newly-created game, waiting for both players to set swing dice
        //   2: new game in which the active player has set swing dice
        //   3: game in which it is the current player's turn to attack
        //   4: game in which it is the opponent's turn to attack
        //   5: game which has been completed
        //   7: game in which focus dice can be used to respond to initiative
        if ($args['type'] == 'loadGameData') {
            $data = NULL;
            if ($args['game'] == '1') {
                $data = array(
                    'gameData' => array(
                        "status" => "ok",
                        "data" => array(
                            "gameId" => "1",
                            "gameState" => 24,
                            "roundNumber" => 1,
                            "maxWins" => "3",
                            "activePlayerIdx" => null,
                            "playerWithInitiativeIdx" => null,
                            "playerIdArray" => array("1", "2"),
                            "buttonNameArray" => array("Avis", "Avis"),
                            "waitingOnActionArray" => array(true,true),
                            "nDieArray" => array(5, 5),
                            "valueArrayArray" => array(array(null,null,null,null,null),
                                                       array(null,null,null,null,null)),
                            "sidesArrayArray" => array(array(4,4,10,12,null),
                                                       array(null,null,null,null,null)),
                            "dieRecipeArrayArray" => array(array("(4)","(4)","(10)","(12)","(X)"),
                                                           array("(4)","(4)","(10)","(12)","(X)")),
                            "nCapturedDieArray" => array(0, 0),
                            "capturedValueArrayArray" => array(array(), array()),
                            "capturedSidesArrayArray" => array(array(), array()),
                            "capturedRecipeArrayArray" => array(array(), array()),
                            "swingRequestArrayArray" => array(array("X" => array('4', '20')), array("X" => array('4', '20'))),
                            "validAttackTypeArray" => array(),
                            "roundScoreArray" => array(15, 15),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(),
                    'gameChatLog' => array(),
                );
            } elseif ($args['game'] == '2') {
                $data = array(
                    'gameData' => array(
                        "status" => "ok",
                        "data" => array(
                            "gameId" => "2",
                            "gameState" => 24,
                            "roundNumber" => 1,
                            "maxWins" => "3",
                            "activePlayerIdx" => null,
                            "playerWithInitiativeIdx" => null,
                            "playerIdArray" => array("1", "2"),
                            "buttonNameArray" => array("Avis", "Avis"),
                            "waitingOnActionArray" => array(false,true),
                            "nDieArray" => array(5, 5),
                            "valueArrayArray" => array(array(null,null,null,null,null),
                                                       array(null,null,null,null,null)),
                            "sidesArrayArray" => array(array(4,4,10,12,4),
                                                       array(null,null,null,null,null)),
                            "dieRecipeArrayArray" => array(array("(4)","(4)","(10)","(12)","(X)"),
                                                           array("(4)","(4)","(10)","(12)","(X)")),
                            "nCapturedDieArray" => array(0, 0),
                            "capturedValueArrayArray" => array(array(), array()),
                            "capturedSidesArrayArray" => array(array(), array()),
                            "capturedRecipeArrayArray" => array(array(), array()),
                            "swingRequestArrayArray" => array(array("X" => array('4', '20')), array("X" => array('4', '20'))),
                            "validAttackTypeArray" => array(),
                            "roundScoreArray" => array(15, 15),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(),
                    'gameChatLog' => array(),
                );
            } elseif ($args['game'] == '3') {
                $data = array(
                    'gameData' => array(
                        "status" => "ok",
                        "data" => array(
                            "gameId" => "3",
                            "gameState" => 40,
                            "roundNumber" => 1,
                            "maxWins" => "3",
                            "activePlayerIdx" => 0,
                            "playerWithInitiativeIdx" => 1,
                            "playerIdArray" => array("1", "2"),
                            "buttonNameArray" => array("Avis", "Avis"),
                            "waitingOnActionArray" => array(true, false),
                            "nDieArray" => array(2, 3),
                            "valueArrayArray" => array(array("4", "2"),
                                                       array("4", "4", "5")),
                            "sidesArrayArray" => array(array(4,4),
                                                       array(4,4,12)),
                            "dieRecipeArrayArray" => array(array("(4)","(X)"),
                                                           array("(4)","(4)","(12)")),
                            "nCapturedDieArray" => array(2, 3),
                            "capturedValueArrayArray" => array(array("3", "1"),
                                                               array("11", "7", "1")),
                            "capturedSidesArrayArray" => array(array("10", "4"),
                                                               array("12", "10", "4")),
                            "capturedRecipeArrayArray" => array(array("(10)", "(X)"),
                                                                array("(12)", "(10)", "(4)")),
                            "swingRequestArrayArray" => array(array("X" => array('4', '20')), array("X" => array('4', '20'))),
                            "validAttackTypeArray" => array("Power" => "Power", "Skill" => "Skill", ),
                            "roundScoreArray" => array(18, 36),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(
                        array("timestamp" => "2013-12-22 21:09:01",
                              "message" => "tester2 performed Power attack using [(12):1] against [(4):1]; Defender (4) was captured; Attacker (12) rerolled 1 => 5"),
                        array("timestamp" => "2013-12-22 21:08:56",
                              "message" => "tester1 performed Power attack using [(4):2] against [(X):1]; Defender (X) was captured; Attacker (4) rerolled 2 => 1"),
                        array("timestamp" => "2013-12-22 21:03:52",
                              "message" => "tester2 performed Skill attack using [(4):4,(X):3] against [(10):7]; Defender (10) was captured; Attacker (4) rerolled 4 => 4; Attacker (X) rerolled 3 => 1"),
                        array("timestamp" => "2013-12-22 21:03:39",
                              "message" => "tester1 performed Power attack using [(4):3] against [(10):3]; Defender (10) was captured; Attacker (4) rerolled 3 => 4"),
                        array("timestamp" => "2013-12-22 21:03:12",
                              "message" => "tester2 performed Skill attack using [(4):1,(10):5,(12):5] against [(12):11]; Defender (12) was captured; Attacker (4) rerolled 1 => 4; Attacker (10) rerolled 5 => 3; Attacker (12) rerolled 5 => 1")
                    ),
                    'gameChatLog' => array(),
                );



            } elseif ($args['game'] == '4') {
                $data = array(
                    'gameData' => array(
                        "status" => "ok",
                        "data" => array(
                            "gameId" => "4",
                            "gameState" => 40,
                            "roundNumber" => 1,
                            "maxWins" => "3",
                            "activePlayerIdx" => 1,
                            "playerWithInitiativeIdx" => 1,
                            "playerIdArray" => array("1", "2"),
                            "buttonNameArray" => array("Avis", "Avis"),
                            "waitingOnActionArray" => array(false, true),
                            "nDieArray" => array(5, 5),
                            "valueArrayArray" => array(array("3", "4", "7", "9", "4"),
                                                       array("1", "3", "4", "5", "2")),
                            "sidesArrayArray" => array(array(4,4,10,12,4),
                                                       array(4,4,10,12,4)),
                            "dieRecipeArrayArray" => array(array("(4)","(4)","(10)","(12)","(X)"),
                                                           array("(4)","(4)","(10)","(12)","(X)")),
                            "nCapturedDieArray" => array(0, 0),
                            "capturedValueArrayArray" => array(array(), array()),
                            "capturedSidesArrayArray" => array(array(), array()),
                            "capturedRecipeArrayArray" => array(array(), array()),
                            "swingRequestArrayArray" => array(array("X" => array('4', '20')), array("X" => array('4', '20'))),
                            "validAttackTypeArray" => array("Power" => "Power", "Skill" => "Skill", ),
                            "roundScoreArray" => array(17, 17),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(),
                    'gameChatLog' => array(),
                );
            } elseif ($args['game'] == '5') {
                $data = array(
                    'gameData' => array(
                        "status" => "ok",
                        "data" => array(
                            "gameId" => "5",
                            "gameState" => 60,
                            "roundNumber" => 6,
                            "maxWins" => "3",
                            "activePlayerIdx" => null,
                            "playerWithInitiativeIdx" => 1,
                            "playerIdArray" => array("1", "2"),
                            "buttonNameArray" => array("Avis", "Avis"),
                            "waitingOnActionArray" => array(false, false),
                            "nDieArray" => array(0, 0),
                            "valueArrayArray" => array(array(), array()),
                            "sidesArrayArray" => array(array(), array()),
                            "dieRecipeArrayArray" => array(array(), array()),
                            "swingRequestArrayArray" => array(array("X" => array('4', '20')), array("X" => array('4', '20'))),
                            "validAttackTypeArray" => array(),
                            "roundScoreArray" => array(0, 0),
                            "gameScoreArrayArray" => array(array("W" => 3, "L" => 2, "D" => 0),
                                                           array("W" => 2, "L" => 3, "D" => 0)),
                            "nCapturedDieArray" => array(0, 0),
                            "capturedValueArrayArray" => array(array(), array()),
                            "capturedSidesArrayArray" => array(array(), array()),
                            "capturedRecipeArrayArray" => array(array(), array()),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(
                        array("timestamp" => "2013-12-20 00:52:42",
                              "message" => "End of round: tester1 won round 5 (46 vs 30)"),
                        array("timestamp" => "2013-12-20 00:52:42",
                              "message" => "tester1 performed Power attack using [(X):7] against [(4):2]; Defender (4) was captured; Attacker (X) rerolled 7 => 4"),
                        array("timestamp" => "2013-12-20 00:52:36",
                              "message" => "tester2 passed"),
                        array("timestamp" => "2013-12-20 00:52:33",
                              "message" => "tester1 performed Power attack using [(X):14] against [(10):4]; Defender (10) was captured; Attacker (X) rerolled 14 => 7"),
                        array("timestamp" => "2013-12-20 00:52:29",
                              "message" => "tester2 performed Power attack using [(10):10] against [(4):4]; Defender (4) was captured; Attacker (10) rerolled 10 => 4"),
                    ),
                    'gameChatLog' => array(),
                );
            } else if ($args['game'] == '6') {
                $data = array(
                    'gameData' => array(
                        "status" => "ok",
                        "data" => array(
                            "gameId" => "6",
                            "gameState" => 24,
                            "roundNumber" => 1,
                            "maxWins" => "3",
                            "activePlayerIdx" => null,
                            "playerWithInitiativeIdx" => null,
                            "playerIdArray" => array("1", "2"),
                            "buttonNameArray" => array("Buck", "Von Pinn"),
                            "waitingOnActionArray" => array(true,true),
                            "nDieArray" => array(5, 5),
                            "valueArrayArray" => array(array(null,null,null,null,null),
                                                       array(null,null,null,null,null)),
                            "sidesArrayArray" => array(array(12,10,12,20,null),
                                                       array(null,null,null,null,null)),
                            "dieRecipeArrayArray" => array(array("(6,6)","(10)","(12)","(20)","(W,W)"),
                                                           array("(4)","p(6,6)","(10)","(20)","(W)")),
                            "nCapturedDieArray" => array(0, 0),
                            "capturedValueArrayArray" => array(array(), array()),
                            "capturedSidesArrayArray" => array(array(), array()),
                            "capturedRecipeArrayArray" => array(array(), array()),
                            "swingRequestArrayArray" => array(array("W" => array('4', '12')), array("W" => array('4', '12'))),
                            "validAttackTypeArray" => array(),
                            "roundScoreArray" => array(27, 5),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(),
                    'gameChatLog' => array(),
                );
            } elseif ($args['game'] == '7') {
                $data = array(
                    'gameData' => array(
                        "status" => "ok",
                        "data" => array(
                            "gameId" => "7",
                            "gameState" => 27,
                            "roundNumber" => 1,
                            "maxWins" => "3",
                            "activePlayerIdx" => null,
                            "playerWithInitiativeIdx" => 1,
                            "playerIdArray" => array("1", "2"),
                            "buttonNameArray" => array("Crab", "Crab"),
                            "waitingOnActionArray" => array(true, false),
                            "nDieArray" => array(5, 5),
                            "valueArrayArray" => array(array("1", "8", "10", "6", "18"),
                                                       array("4", "7", "5", "1", "12")),
                            "sidesArrayArray" => array(array(8,10,12,20,20),
                                                       array(8,10,12,20,20)),
                            "dieRecipeArrayArray" => array(array("(8)","(10)","(12)","f(20)","f(20)"),
                                                           array("(8)","(10)","(12)","f(20)","f(20)")),
                            "nCapturedDieArray" => array(0, 0),
                            "capturedValueArrayArray" => array(array(), array()),
                            "capturedSidesArrayArray" => array(array(), array()),
                            "capturedRecipeArrayArray" => array(array(), array()),
                            "swingRequestArrayArray" => array(array(), array()),
                            "validAttackTypeArray" => array(),
                            "roundScoreArray" => array(35, 35),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(),
                    'gameChatLog' => array(),
                );
            } elseif ($args['game'] == '8') {
                $data = array(
                    'gameData' => array(
                        "status" => "ok",
                        "data" => array(
                            "gameId" => "8",
                            "gameState" => 27,
                            "roundNumber" => 1,
                            "maxWins" => "3",
                            "activePlayerIdx" => null,
                            "playerWithInitiativeIdx" => 1,
                            "playerIdArray" => array("1", "2"),
                            "buttonNameArray" => array("John Kovalic", "John Kovalic"),
                            "waitingOnActionArray" => array(true, false),
                            "nDieArray" => array(5, 5),
                            "valueArrayArray" => array(array("4", "3", "6", "5", "4"),
                                                       array("2", "4", "2", "3", "18")),
                            "sidesArrayArray" => array(array(6,6,10,12,20),
                                                       array(6,6,10,12,20)),
                            "dieRecipeArrayArray" => array(array("(6)","c(6)","(10)","(12)","c(20)"),
                                                           array("(6)","c(6)","(10)","(12)","c(20)")),
                            "nCapturedDieArray" => array(0, 0),
                            "capturedValueArrayArray" => array(array(), array()),
                            "capturedSidesArrayArray" => array(array(), array()),
                            "capturedRecipeArrayArray" => array(array(), array()),
                            "swingRequestArrayArray" => array(array(), array()),
                            "validAttackTypeArray" => array(),
                            "roundScoreArray" => array(27, 27),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(),
                    'gameChatLog' => array(),
                );
            } elseif ($args['game'] == '9') {
                $data = array(
                    'gameData' => array(
                        "status" => "ok",
                        "data" => array(
                            "gameId" => "9",
                            "gameState" => 27,
                            "roundNumber" => 1,
                            "maxWins" => "3",
                            "activePlayerIdx" => null,
                            "playerWithInitiativeIdx" => 0,
                            "playerIdArray" => array("1", "2"),
                            "buttonNameArray" => array("John Kovalic", "John Kovalic"),
                            "waitingOnActionArray" => array(false, true),
                            "nDieArray" => array(5, 5),
                            "valueArrayArray" => array(array("2", "4", "2", "3", "18"),
                                                       array("4", "3", "6", "5", "4")),
                            "sidesArrayArray" => array(array(6,6,10,12,20),
                                                       array(6,6,10,12,20)),
                            "dieRecipeArrayArray" => array(array("(6)","c(6)","(10)","(12)","c(20)"),
                                                           array("(6)","c(6)","(10)","(12)","c(20)")),
                            "nCapturedDieArray" => array(0, 0),
                            "capturedValueArrayArray" => array(array(), array()),
                            "capturedSidesArrayArray" => array(array(), array()),
                            "capturedRecipeArrayArray" => array(array(), array()),
                            "swingRequestArrayArray" => array(array(), array()),
                            "validAttackTypeArray" => array(),
                            "roundScoreArray" => array(27, 27),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(),
                    'gameChatLog' => array(),
                );
            }

            if ($data) {
                $data['playerNameArray'] = array('tester1', 'tester2');
                $timestamp = new DateTime();
                $data['timestamp'] = $timestamp->format(DATE_RSS);
                return array($data, "Loaded data for game " . $args['game']);
            }
            return array(NULL, "Game does not exist.");
        }

        if ($args['type'] == 'loadPlayerName') {
            return array(array('userName' => 'tester1'), NULL);
        }

        if ($args['type'] == 'loadPlayerNames') {
            $data = array(
                'nameArray' => array(),
            );

            // three test players exist
            $data['nameArray'][] = 'tester1';
            $data['nameArray'][] = 'tester2';
            $data['nameArray'][] = 'tester3';

            return array($data, "Names retrieved successfully.");
        }

        if ($args['type'] == 'submitSwingValues') {
            $valid_swing = array('R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($args['swingValueArray'] as $letter => $value) {
                if (!(in_array($letter, $valid_swing, True))) {
                    return array(NULL, "Unknown swing letter $letter");
                }
            }
            return array(True, 'Successfully set swing values');
        }

        if ($args['type'] == 'reactToInitiative') {
            return array(True, 'Successfully gained initiative');
        }

        if ($args['type'] == 'submitTurn') {
            return array(True, 'Dummy turn submission accepted');
        }

        if ($args['type'] == 'login') {
//            $login_success = login($_POST['username'], $_POST['password']);
//            if ($login_success) {
//                $data = array('userName' => $_POST['username']);
//            } else {
//                $data = NULL;
//            }
            return array(NULL, "function not implemented");
        }

        if ($args['type'] == 'logout') {
//            logout();
//            $data = array('userName' => False);
            return array(NULL, "function not implemented");
        }

        return array(NULL, NULL);
    }

    // Ask get_interface_response() for the dummy response to the
    // request, then construct a response.  Match the logic in
    // responder as closely as possible for convenience.
    // * For live (remote) invocation:
    //   * display the output to the user
    // * For test invocation:
    //   * return the output as a PHP variable
    public function process_request($args) {
        $retval = $this->get_interface_response($args);
        $data = $retval[0];
        $message = $retval[1];

        $output = array(
            'data' => $data,
            'message' => $message,
        );
        if ($data) {
            $output['status'] = 'ok';
        } else {
            $output['status'] = 'failed';
        }

        if ($this->isTest) {
            return $output;
        } else {
            header('Content-Type: application/json');
            echo json_encode($output);
        }
    }
}

// If dummy_responder was called via a POST request (rather than
// by test code), the $_POST variable will be set
if ($_POST) {
    $dummy_responder = new dummy_responder(False);
    $dummy_responder->process_request($_POST);
}
?>
