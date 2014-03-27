<?php

/** Alternative responder which doesn't use real databases or
 *  sessions, but rather exists only to send dummy data used for
 *  automated testing of API compliance
 */

class DummyApiResponder {
    // properties

    // N.B. this class is always used for some type of testing,
    // but, the usage here matches the way responder uses this flag:
    // * FALSE: this instance is being accessed remotely via POST
    // * TRUE:  this instance is being accessed locally by unit tests
    private $isTest;               // whether this invocation is for testing

    // constructor
    // * For live invocation:
    //   * start a session (don't use api_core because dummy_responder has no backend)
    // * For test invocation:
    //   * don't start a session
    public function __construct(ApiSpec $spec, $isTest = FALSE) {
        $this->spec = $spec;
        $this->isTest = $isTest;

        if (!($this->isTest)) {
            session_start();
        }
    }

    // This function looks at the provided arguments, fakes appropriate
    // data to match the public API, and returns either some game
    // data on success, or NULL on failure.  (Failure will happen if
    // the requested arguments are invalid.)
    protected function get_interface_response($args) {
        $funcName = 'get_interface_response_' . $args['type'];
        if (method_exists($this, $funcName)) {
            $result = $this->$funcName($args);
        } else {
            $result = array(NULL, 'Specified API function does not exist');
        }

        return $result;
    }

    protected function get_interface_response_createUser($args) {
        $dummy_users = array(
            'tester1' => 1,
            'tester2' => 2,
            'tester3' => 3);
        $username = $args['username'];
        if (array_key_exists($username, $dummy_users)) {
            $userid = $dummy_users[$username];
            return array(NULL, "$username already exists (id=$userid)");
        }
        return array(array('userName' => $username),
                     'User ' . $username . ' created successfully.  ' .
                     'A verification code has been e-mailed to ' . $username . '@example.com.  ' .
                     'Follow the link in that message to start beating people up!');
    }

    protected function get_interface_response_verifyUser($args) {
        return array(TRUE, "New user tester1 has been verified.");
    }

    protected function get_interface_response_createGame() {
        // for verisimilitude, choose a game ID of one greater than
        // the number of "existing" games represented in loadGameData
        // and loadActiveGames

        $gameId = 19;
        return array(array('gameId' => $gameId), "Game $gameId created successfully.");
    }

    protected function get_interface_response_loadActiveGames() {
        // Use the same fake games here which were described in loadGameData
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
        $data['gameIdArray'][] = 1;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "SPECIFY_DICE";
        $data['statusArray'][] = "ACTIVE";

        // game 2
        $data['gameIdArray'][] = 2;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "SPECIFY_DICE";
        $data['statusArray'][] = "ACTIVE";

        // game 3
        $data['gameIdArray'][] = 3;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "START_TURN";
        $data['statusArray'][] = "ACTIVE";

        // game 4
        $data['gameIdArray'][] = 4;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "START_TURN";
        $data['statusArray'][] = "ACTIVE";

        // fake game 5 is completed

        // game 6
        $data['gameIdArray'][] = 6;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Buck Godot";
        $data['opponentButtonNameArray'][] = "Von Pinn";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "SPECIFY_DICE";
        $data['statusArray'][] = "ACTIVE";

        // game 7
        $data['gameIdArray'][] = 7;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Crab";
        $data['opponentButtonNameArray'][] = "Crab";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "REACT_TO_INITIATIVE";
        $data['statusArray'][] = "ACTIVE";

        // game 8
        $data['gameIdArray'][] = 8;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "John Kovalic";
        $data['opponentButtonNameArray'][] = "John Kovalic";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "REACT_TO_INITIATIVE";
        $data['statusArray'][] = "ACTIVE";

        // game 9
        $data['gameIdArray'][] = 9;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "John Kovalic";
        $data['opponentButtonNameArray'][] = "John Kovalic";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "REACT_TO_INITIATIVE";
        $data['statusArray'][] = "ACTIVE";

        // tester1 is not a participant in fake game 10
        // tester1 is not a participant in fake game 11
        // tester1 is not a participant in fake game 12

        // game 13
        $data['gameIdArray'][] = 13;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "King Arthur";
        $data['opponentButtonNameArray'][] = "King Arthur";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "CHOOSE_AUXILIARY_DICE";
        $data['statusArray'][] = "ACTIVE";

        // game 14
        $data['gameIdArray'][] = 14;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "King Arthur";
        $data['opponentButtonNameArray'][] = "King Arthur";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "CHOOSE_AUXILIARY_DICE";
        $data['statusArray'][] = "ACTIVE";

        // tester1 is not a participant in fake game 15

        // game 16
        $data['gameIdArray'][] = 16;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Cammy Neko";
        $data['opponentButtonNameArray'][] = "Cammy Neko";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 1;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "CHOOSE_RESERVE_DICE";
        $data['statusArray'][] = "ACTIVE";

        // game 17
        $data['gameIdArray'][] = 17;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Cammy Neko";
        $data['opponentButtonNameArray'][] = "Cammy Neko";
        $data['nWinsArray'][] = 1;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "CHOOSE_RESERVE_DICE";
        $data['statusArray'][] = "ACTIVE";

        // tester1 is not a participant in fake game 18

        return array($data, "All game details retrieved successfully.");
    }

    protected function get_interface_response_loadCompletedGames() {
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

        // game 5
        $data['gameIdArray'][] = 5;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 3;
        $data['nLossesArray'][] = 2;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "END_GAME";
        $data['statusArray'][] = "COMPLETE";

        return array($data, "All game details retrieved successfully.");
    }

    protected function get_interface_response_loadNextPendingGame() {
        // Assume that game IDs 7 is the next one waiting for tester1
        $data = array('gameId' => 7);
        return array($data, 'Next game ID retrieved successfully.');
    }

    protected function get_interface_response_loadButtonNames() {
        $data = array(
          'buttonNameArray' => array(),
          'recipeArray' => array(),
          'hasUnimplementedSkillArray' => array(),
        );

        // a button with no special skills
        $data['buttonNameArray'][] = "Avis";
        $data['recipeArray'][] = "(4) (4) (10) (12) (X)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // a button with an unimplemented skill
        $data['buttonNameArray'][] = "Adam Spam";
        $data['recipeArray'][] = "F(4) F(6) (6) (12) (X)";
        $data['hasUnimplementedSkillArray'][] = TRUE;

        // a button with four dice and some implemented skills
        $data['buttonNameArray'][] = "Jellybean";
        $data['recipeArray'][] = "p(20) s(20) (V) (X)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // Buck Godot
        $data['buttonNameArray'][] = "Buck Godot";
        $data['recipeArray'][] = "(6,6) (10) (12) (20) (W,W)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // Von Pinn
        $data['buttonNameArray'][] = "Von Pinn";
        $data['recipeArray'][] = "(4) p(6,6) (10) (20) (W)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // Crab: a button with focus dice
        $data['buttonNameArray'][] = "Crab";
        $data['recipeArray'][] = "(8) (10) (12) f(20) f(20)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // John Kovalic: a button with chance dice
        $data['buttonNameArray'][] = "John Kovalic";
        $data['recipeArray'][] = "(6) c(6) (10) (12) c(20)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // King Arthur: a button with an auxiliary die
        $data['buttonNameArray'][] = "King Arthur";
        $data['recipeArray'][] = "(8) (8) (10) (20) (X) +(20)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // Cammy Neko: a button with reserve dice
        $data['buttonNameArray'][] = "Cammy Neko";
        $data['recipeArray'][] = "(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        return array($data, "All button names retrieved successfully.");
    }

    protected function get_interface_response_loadGameData($args) {
        // The dummy loadGameData returns one of a number of
        // sets of dummy game data, for general test use.
        // Specify which one you want using the game number:
        //   1: a newly-created game, waiting for both players to set swing dice
        //   2: new game in which the active player has set swing dice
        //   3: game in which it is the current player's turn to attack
        //   4: game in which it is the opponent's turn to attack
        //   5: game which has been completed
        //   7: game in which focus dice can be used to respond to initiative

        $data = NULL;

        // base params for an unstarted Avis vs. Avis game - override these as needed
        // Regardless, you *must* set gameId and gameState
        $gameData = array(
            "roundNumber" => 1,
            "maxWins" => 3,
            "activePlayerIdx" => NULL,
            "playerWithInitiativeIdx" => NULL,
            "playerIdArray" => array(1, 2),
            "buttonNameArray" => array("Avis", "Avis"),
            "buttonRecipeArray" => array("(4) (4) (10) (12) (X)", "(4) (4) (10) (12) (X)"),
            "waitingOnActionArray" => array(TRUE, TRUE),
            "nDieArray" => array(5, 5),
            "valueArrayArray" => array(array(NULL,NULL,NULL,NULL,NULL),
                                       array(NULL,NULL,NULL,NULL,NULL)),
            "sidesArrayArray" => array(array(4,4,10,12,NULL),
                                       array(4,4,10,12,NULL)),
            "dieSkillsArrayArray" => array(array(array(), array(), array(), array(), array()),
                                           array(array(), array(), array(), array(), array())),
            "diePropertiesArrayArray" => array(array(array(), array(), array(), array(), array()),
                                               array(array(), array(), array(), array(), array())),
            "dieRecipeArrayArray" => array(array("(4)","(4)","(10)","(12)","(X)"),
                                           array("(4)","(4)","(10)","(12)","(X)")),
            "dieDescriptionArrayArray" =>
                array(
                    array(
                        '4-sided die',
                        '4-sided die',
                        '10-sided die',
                        '12-sided die',
                        'X Swing Die'
                    ),
                    array(
                        '4-sided die',
                        '4-sided die',
                        '10-sided die',
                        '12-sided die',
                        'X Swing Die'
                    )
                ),
            "nCapturedDieArray" => array(0, 0),
            "capturedValueArrayArray" => array(array(), array()),
            "capturedSidesArrayArray" => array(array(), array()),
            "capturedRecipeArrayArray" => array(array(), array()),
            "swingRequestArrayArray" => array(array("X" => array(4, 20)), array("X" => array(4, 20))),
            "validAttackTypeArray" => array(),
            "roundScoreArray" => array(NULL, NULL),
            "sideScoreArray" => array(NULL, NULL),
            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                           array("W" => 0, "L" => 0, "D" => 0)),
        );

        // base params for a John Kovalic vs John Kovalic game, here to
        // avoid the duplicated code warning
        $gameDataJohnKovalic = $gameData;
        $gameDataJohnKovalic['gameState'] = "REACT_TO_INITIATIVE";
        $gameDataJohnKovalic['playerWithInitiativeIdx'] = 1;
        $gameDataJohnKovalic['buttonNameArray'] = array("John Kovalic", "John Kovalic");
        $gameDataJohnKovalic['buttonRecipeArray'] = array("(6) c(6) (10) (12) c(20)", "(6) c(6) (10) (12) c(20)");
        $gameDataJohnKovalic['waitingOnActionArray'] = array(TRUE, FALSE);
        $gameDataJohnKovalic['valueArrayArray'] = array(array(4, 3, 6, 5, 4), array(2, 4, 2, 3, 18));
        $gameDataJohnKovalic['sidesArrayArray'] = array(array(6, 6, 10, 12, 20), array(6, 6, 10, 12, 20));
        $gameDataJohnKovalic['dieRecipeArrayArray'] =
            array(
                array("(6)","c(6)","(10)","(12)","c(20)"),
                array("(6)","c(6)","(10)","(12)","c(20)")
            );
        $gameDataJohnKovalic['dieDescriptionArrayArray'] =
            array(
                array(
                    '6-sided die',
                    'Chance 6-sided die',
                    '10-sided die',
                    '12-sided die',
                    'Chance 20-sided die'
                ),
                array(
                    '6-sided die',
                    'Chance 6-sided die',
                    '10-sided die',
                    '12-sided die',
                    'Chance 20-sided die'
                )
            );
        $gameDataJohnKovalic['swingRequestArrayArray'] = array(array(), array());
        $gameDataJohnKovalic['roundScoreArray'] = array(NULL, NULL);

        // base params for a King Arthur vs King Arthur game, here to
        // avoid the duplicated code warning
        $gameDataKingArthur = $gameData;
        $gameDataKingArthur['gameState'] = "CHOOSE_AUXILIARY_DICE";
        $gameDataKingArthur['buttonNameArray'] = array("King Arthur", "King Arthur");
        $gameDataKingArthur['nDieArray'] = array(6, 6);
        $gameDataKingArthur['buttonRecipeArray'] = array("(8) (8) (10) (20) (X) +(20)", "(8) (8) (10) (20) (X) +(20)");
        $gameDataKingArthur['waitingOnActionArray'] = array(TRUE, TRUE);
        $gameDataKingArthur['valueArrayArray'] =
            array(
                array(NULL, NULL, NULL, NULL, NULL, NULL),
                array(NULL, NULL, NULL, NULL, NULL, NULL)
            );
        $gameDataKingArthur['sidesArrayArray'] =
            array(
                array(8, 8, 10, 20, NULL, 20),
                array(NULL, NULL, NULL, NULL, NULL, NULL)
            );
        $gameDataKingArthur['dieRecipeArrayArray'] =
            array(
                array("(8)","(8)","(10)","(20)","(X)","+(20)"),
                array("(8)","(8)","(10)","(20)","(X)","+(20)")
            );
        $gameDataKingArthur['dieSkillsArrayArray'] =
            array(
                array(array(), array(), array(), array(), array(), array('Auxiliary' => TRUE)),
                array(array(), array(), array(), array(), array(), array('Auxiliary' => TRUE))
            );
        $gameDataKingArthur['diePropertiesArrayArray'] =
            array(
                array(array(), array(), array(), array(), array(), array()),
                array(array(), array(), array(), array(), array(), array())
            );
        $gameDataKingArthur['dieDescriptionArrayArray'] =
            array(
                array(
                    '8-sided die',
                    '8-sided die',
                    '10-sided die',
                    '20-sided die',
                    'X Swing Die',
                    'Auxiliary 20-sided die'
                ),
                array(
                    '8-sided die',
                    '8-sided die',
                    '10-sided die',
                    '20-sided die',
                    'X Swing Die',
                    'Auxiliary 20-sided die'
                )
            );
        $gameDataKingArthur['roundScoreArray'] = array(NULL, NULL);

        // base params for a Cammy Neko vs Cammy Neko game
        $gameDataCammyNeko = $gameData;
        $gameDataCammyNeko['gameState'] = "CHOOSE_RESERVE_DICE";
        $gameDataCammyNeko['roundNumber'] = 2;
        $gameDataCammyNeko['gameScoreArrayArray'] =
            array(
                array("W" => 0, "L" => 1, "D" => 0),
                array("W" => 1, "L" => 0, "D" => 0)
            );
        $gameDataCammyNeko['buttonNameArray'] = array("Cammy Neko", "Cammy Neko");
        $gameDataCammyNeko['nDieArray'] = array(8, 8);
        $gameDataCammyNeko['buttonRecipeArray'] =
            array(
                "(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)",
                "(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)"
            );
        $gameDataCammyNeko['waitingOnActionArray'] = array(TRUE, FALSE);
        $gameDataCammyNeko['valueArrayArray'] =
            array(
                array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
                array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)
            );
        $gameDataCammyNeko['sidesArrayArray'] =
            array(
                array(4, 6, 12, 20, 12, 20, 20, 16),
                array(4, 6, 12, 20, 12, 20, 20, 16)
            );
        $gameDataCammyNeko['dieRecipeArrayArray'] =
            array(
                array("(4)","(6)","(12)","(10,10)","r(12)","r(20)","r(20)","r(8,8)"),
                array("(4)","(6)","(12)","(10,10)","r(12)","r(20)","r(20)","r(8,8)")
            );
        $gameDataCammyNeko['dieSkillsArrayArray'] =
            array(
                array(array(), array(), array(), array(),
                      array('Reserve' => TRUE), array('Reserve' => TRUE),
                      array('Reserve' => TRUE), array('Reserve' => TRUE)),
                array(array(), array(), array(), array(),
                      array('Reserve' => TRUE), array('Reserve' => TRUE),
                      array('Reserve' => TRUE), array('Reserve' => TRUE))
            );
        $gameDataCammyNeko['diePropertiesArrayArray'] =
            array(
                array(array(), array(), array(), array(), array(), array(), array(), array()),
                array(array(), array(), array(), array(), array(), array(), array(), array())
            );
        $gameDataCammyNeko['dieDescriptionArrayArray'] =
            array(
                array(
                    '4-sided die',
                    '6-sided die',
                    '12-sided die',
                    'Twin Die (both with 10 sides)',
                    'Reserve 12-sided die',
                    'Reserve 20-sided die',
                    'Reserve 20-sided die',
                    'Reserve Twin Die (both with 8 sides)'
                ),
                array(
                    '4-sided die',
                    '6-sided die',
                    '12-sided die',
                    'Twin Die (both with 10 sides)',
                    'Reserve 12-sided die',
                    'Reserve 20-sided die',
                    'Reserve 20-sided die',
                    'Reserve Twin Die (both with 8 sides)'
                )
            );
        $gameDataCammyNeko['roundScoreArray'] = array(NULL, NULL);

        if ($args['game'] == '1') {
            $gameData['gameId'] = 1;
            $gameData['gameState'] = "SPECIFY_DICE";
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameData,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '2') {
            $gameData['gameId'] = 2;
            $gameData['gameState'] = "SPECIFY_DICE";
            $gameData['waitingOnActionArray'] = array(FALSE, TRUE);
            $gameData['sidesArrayArray'] = array(array(4,4,10,12,4),
                                                 array(NULL,NULL,NULL,NULL,NULL));
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameData,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif (($args['game'] == '3') || ($args['game'] == '11')) {
            // these two examples use the same somewhat-involved
            // game state, but in game 3, tester1 is the active player,
            // while in game 11, tester1 is not a participant
            if ($args['game'] == '3') {
                $gameData['gameId'] = 3;
            } elseif ($args['game'] == '11') {
                $gameData['gameId'] = 11;
            }
            $gameData['gameState'] = "START_TURN";
            $gameData['activePlayerIdx'] = 0;
            $gameData['playerWithInitiativeIdx'] = 1;
            $gameData['waitingOnActionArray'] = array(TRUE, FALSE);
            $gameData['nDieArray'] = array(2, 3);
            $gameData['valueArrayArray'] = array(array(4, 2),
                                                 array(4, 4, 5));
            $gameData['sidesArrayArray'] = array(array(4,4),
                                                 array(4,4,12));
            $gameData['dieRecipeArrayArray'] = array(array("(4)","(X)"),
                                                     array("(4)","(4)","(12)"));
            $gameData['dieDescriptionArrayArray'] =
                array(
                    array(
                        '4-sided die',
                        'X Swing Die (with 4 sides)'
                    ),
                    array(
                        '4-sided die',
                        '4-sided die',
                        '12-sided die'
                    )
                );
            $gameData['nCapturedDieArray'] = array(2, 3);
            $gameData['capturedValueArrayArray'] = array(array(3, 1),
                                                         array(11, 7, 1));
            $gameData['capturedSidesArrayArray'] = array(array(10, 4),
                                                         array(12, 10, 4));
            $gameData['capturedRecipeArrayArray'] = array(array("(10)", "(X)"),
                                                          array("(12)", "(10)", "(4)"));
            $gameData['validAttackTypeArray'] = array("Power" => "Power", "Skill" => "Skill", );
            $gameData['roundScoreArray'] = array(18, 36);
            $gameData['sideScoreArray'] = array(-12, 12);
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameData,
                ),
                'gameActionLog' => array(
                    array("timestamp" => "2013-12-22 21:09:01",
                          "message" =>
                              "tester2 performed Power attack using [(12):1] against [(4):1]; " .
                              "Defender (4) was captured; Attacker (12) rerolled 1 => 5"),
                    array("timestamp" => "2013-12-22 21:08:56",
                          "message" =>
                              "tester1 performed Power attack using [(4):2] against [(X):1]; " .
                              "Defender (X) was captured; Attacker (4) rerolled 2 => 1"),
                    array("timestamp" => "2013-12-22 21:03:52",
                          "message" =>
                              "tester2 performed Skill attack using [(4):4,(X):3] against [(10):7]; " .
                              "Defender (10) was captured; Attacker (4) rerolled 4 => 4; " .
                              "Attacker (X) rerolled 3 => 1"),
                    array("timestamp" => "2013-12-22 21:03:39",
                          "message" =>
                              "tester1 performed Power attack using [(4):3] against [(10):3]; " .
                              "Defender (10) was captured; Attacker (4) rerolled 3 => 4"),
                    array("timestamp" => "2013-12-22 21:03:12",
                          "message" =>
                              "tester2 performed Skill attack using [(4):1,(10):5,(12):5] against [(12):11]; " .
                              "Defender (12) was captured; Attacker (4) rerolled 1 => 4; " .
                              "Attacker (10) rerolled 5 => 3; Attacker (12) rerolled 5 => 1")
                ),
                'gameChatLog' => array(
                    array("timestamp" => "2013-12-22 21:09:01",
                          "player" => "tester2",
                          "message" => "Hello."),
                    array("timestamp" => "2013-12-22 21:08:56",
                          "player" => "tester1",
                          "message" => "Hi."),
                    array("timestamp" => "2013-12-22 21:03:52",
                          "player" => "tester2",
                          "message" => "Greetings."),
                    array("timestamp" => "2013-12-22 21:03:39",
                          "player" => "tester1",
                          "message" => "Salutations."),
                    array("timestamp" => "2013-12-22 21:03:12",
                          "player" => "tester2",
                          "message" => "Good morning."),
                    array("timestamp" => "2013-11-22 21:09:01",
                          "player" => "tester2",
                          "message" => "Bonjour."),
                    array("timestamp" => "2013-11-22 21:08:56",
                          "player" => "tester1",
                          "message" => "Yo."),
                    array("timestamp" => "2013-11-22 21:03:52",
                          "player" => "tester2",
                          "message" => "How are you?"),
                    array("timestamp" => "2013-11-22 21:03:39",
                          "player" => "tester1",
                          "message" => "Howdy."),
                    array("timestamp" => "2013-11-22 21:03:12",
                          "player" => "tester2",
                          "message" => "Ping!"),
                    array("timestamp" => "2013-10-22 21:03:12",
                          "player" => "tester2",
                          "message" => "G'day."),
                ),
            );
            if ($args['game'] == '3') {
                $data['currentPlayerIdx'] = 0;
            } elseif ($args['game'] == '11') {
                $data['currentPlayerIdx'] = FALSE;
                $data['playerNameArray'] = array('tester2', 'tester3');
            }
        } elseif ($args['game'] == '4') {
            $gameData['gameId'] = 4;
            $gameData['gameState'] = "START_TURN";
            $gameData['activePlayerIdx'] = 1;
            $gameData['playerWithInitiativeIdx'] = 1;
            $gameData['waitingOnActionArray'] = array(FALSE, TRUE);
            $gameData['valueArrayArray'] = array(array(3, 4, 7, 9, 4),
                                                 array(1, 3, 4, 5, 2));
            $gameData['sidesArrayArray'] = array(array(4,4,10,12,4),
                                                 array(4,4,10,12,4));
            $gameData['validAttackTypeArray'] = array("Power" => "Power", "Skill" => "Skill", );
            $gameData['roundScoreArray'] = array(17, 17);
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameData,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );

        } elseif ($args['game'] == '5') {
            $gameData['gameId'] = 5;
            $gameData['gameState'] = "END_GAME";
            $gameData['roundNumber'] = 6;
            $gameData['playerWithInitiativeIdx'] = 1;
            $gameData['waitingOnActionArray'] = array(FALSE, FALSE);
            $gameData['nDieArray'] = array(0, 0);
            $gameData['valueArrayArray'] = array(array(), array());
            $gameData['sidesArrayArray'] = array(array(), array());
            $gameData['dieRecipeArrayArray'] = array(array(), array());
            $gameData['dieDescriptionArrayArray'] = array(array(), array());
            $gameData['roundScoreArray'] = array(0, 0);
            $gameData['sideScoreArray'] = array(0, 0);
            $gameData['gameScoreArrayArray'] = array(array("W" => 3, "L" => 2, "D" => 0),
                                                     array("W" => 2, "L" => 3, "D" => 0));
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameData,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(
                    array("timestamp" => "2013-12-20 00:52:42",
                          "message" => "End of round: tester1 won round 5 (46 vs 30)"),
                    array("timestamp" => "2013-12-20 00:52:42",
                          "message" =>
                              "tester1 performed Power attack using [(X):7] against [(4):2]; " .
                              "Defender (4) was captured; Attacker (X) rerolled 7 => 4"),
                    array("timestamp" => "2013-12-20 00:52:36",
                          "message" => "tester2 passed"),
                    array("timestamp" => "2013-12-20 00:52:33",
                          "message" =>
                              "tester1 performed Power attack using [(X):14] against [(10):4]; " .
                              "Defender (10) was captured; Attacker (X) rerolled 14 => 7"),
                    array("timestamp" => "2013-12-20 00:52:29",
                          "message" =>
                              "tester2 performed Power attack using [(10):10] against [(4):4]; " .
                              "Defender (4) was captured; Attacker (10) rerolled 10 => 4"),
                ),
                'gameChatLog' => array(
                    array("timestamp" => "2013-12-20 00:52:42",
                          "player" => "tester1",
                          "message" => "Pong."),
                    array("timestamp" => "2013-12-20 00:52:29",
                          "player" => "tester2",
                          "message" => "Ping!"),
                ),
            );
        } elseif ($args['game'] == '6') {
            $gameData['gameId'] = 6;
            $gameData['gameState'] = "SPECIFY_DICE";
            $gameData['buttonNameArray'] = array("Buck Godot", "Von Pinn");
            $gameData['buttonRecipeArray'] = array("(6,6) (10) (12) (20) (W,W)", "(4) p(6,6) (10) (20) (W)");
            $gameData['sidesArrayArray'] = array(array(12,10,12,20,NULL),
                                                 array(NULL,NULL,NULL,NULL,NULL));
            $gameData['dieRecipeArrayArray'] = array(array("(6,6)","(10)","(12)","(20)","(W,W)"),
                                                     array("(4)","p(6,6)","(10)","(20)","(W)"));
            $gameData['dieDescriptionArrayArray'] =
                array(
                    array(
                        'Twin Die (both with 6 sides)',
                        '10-sided die',
                        '12-sided die',
                        '20-sided die',
                        'Twin W Swing Die'
                    ),
                    array(
                        '4-sided die',
                        'Poison Twin Die (both with 6 sides)',
                        '10-sided die',
                        '20-sided die',
                        'W Swing Die'
                    )
                );
            $gameData['swingRequestArrayArray'] = array(array("W" => array(4, 12)), array("W" => array(4, 12)));
            $gameData['roundScoreArray'] = array(NULL, NULL);
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameData,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '7') {
            $gameData['gameId'] = 7;
            $gameData['gameState'] = "REACT_TO_INITIATIVE";
            $gameData['playerWithInitiativeIdx'] = 1;
            $gameData['buttonNameArray'] = array("Crab", "Crab");
            $gameData['buttonRecipeArray'] = array("(8) (10) (12) f(20) f(20)", "(8) (10) (12) f(20) f(20)");
            $gameData['waitingOnActionArray'] = array(TRUE, FALSE);
            $gameData['valueArrayArray'] = array(array(1, 8, 10, 6, 18),
                                                 array(4, 7, 5, 1, 12));
            $gameData['sidesArrayArray'] = array(array(8,10,12,20,20),
                                                 array(8,10,12,20,20));
            $gameData['dieRecipeArrayArray'] = array(array("(8)","(10)","(12)","f(20)","f(20)"),
                                                     array("(8)","(10)","(12)","f(20)","f(20)"));
            $gameData['dieDescriptionArrayArray'] =
                array(
                    array(
                        '8-sided die',
                        '10-sided die',
                        '12-sided die',
                        'Focus 20-sided die',
                        'Focus 20-sided die'
                    ),
                    array(
                        '8-sided die',
                        '10-sided die',
                        '12-sided die',
                        'Focus 20-sided die',
                        'Focus 20-sided die'
                    )
                );
            $gameData['swingRequestArrayArray'] = array(array(), array());
            $gameData['roundScoreArray'] = array(NULL, NULL);
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameData,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '8') {
            $gameDataJohnKovalic['gameId'] = 8;
            $gameDataJohnKovalic['playerWithInitiativeIdx'] = 1;
            $gameDataJohnKovalic['waitingOnActionArray'] = array(TRUE, FALSE);
            $gameDataJohnKovalic['valueArrayArray'] = array(array(4, 3, 6, 5, 4), array(2, 4, 2, 3, 18));
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameDataJohnKovalic,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '9') {
            $gameDataJohnKovalic['gameId'] = 9;
            $gameDataJohnKovalic['playerWithInitiativeIdx'] = 0;
            $gameDataJohnKovalic['waitingOnActionArray'] = array(FALSE, TRUE);
            $gameDataJohnKovalic['valueArrayArray'] = array(array(2, 4, 2, 3, 18), array(4, 3, 6, 5, 4));
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameDataJohnKovalic,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '10') {
            $gameData['gameId'] = 10;
            $gameData['gameState'] = "SPECIFY_DICE";
            $gameData['playerIdArray'] = array(2, 3);
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameData,
                ),
                'currentPlayerIdx' => FALSE,
                'playerNameArray' => array('tester2', 'tester3'),
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        // game 11 is grouped with game 3 above
        } elseif ($args['game'] == '12') {
            $gameDataJohnKovalic['gameId'] = 12;
            $gameDataJohnKovalic['playerWithInitiativeIdx'] = 1;
            $gameDataJohnKovalic['waitingOnActionArray'] = array(TRUE, FALSE);
            $gameDataJohnKovalic['valueArrayArray'] = array(array(4, 3, 6, 5, 4), array(2, 4, 2, 3, 18));
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameDataJohnKovalic,
                ),
                'currentPlayerIdx' => FALSE,
                'playerNameArray' => array('tester2', 'tester3'),
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '13') {
            $gameDataKingArthur['gameId'] = 13;
            $gameDataKingArthur['waitingOnActionArray'] = array(TRUE, TRUE);
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameDataKingArthur,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '14') {
            $gameDataKingArthur['gameId'] = 14;
            $gameDataKingArthur['waitingOnActionArray'] = array(FALSE, TRUE);
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameDataKingArthur,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '15') {
            $gameDataKingArthur['gameId'] = 15;
            $gameDataKingArthur['waitingOnActionArray'] = array(TRUE, TRUE);
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameDataKingArthur,
                ),
                'currentPlayerIdx' => FALSE,
                'playerNameArray' => array('tester2', 'tester3'),
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '16') {
            $gameDataCammyNeko['gameId'] = 16;
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameDataCammyNeko,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '17') {
            $gameDataCammyNeko['gameId'] = 17;  // FIXME
            $gameDataCammyNeko['waitingOnActionArray'] = array(FALSE, TRUE);
            $gameDataCammyNeko['gameScoreArrayArray'] =
                array(
                    array("W" => 1, "L" => 0, "D" => 0),
                    array("W" => 0, "L" => 1, "D" => 0)
                );
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameDataCammyNeko,
                ),
                'currentPlayerIdx' => 0,
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif ($args['game'] == '18') {
            $gameDataCammyNeko['gameId'] = 18;
            $data = array(
                'gameData' => array(
                    "status" => "ok",
                    "data" => $gameDataCammyNeko,
                ),
                'currentPlayerIdx' => FALSE,
                'playerNameArray' => array('tester2', 'tester3'),
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        }

        if ($data) {
            if (isset($args['logEntryLimit']) && $args['logEntryLimit'] > 0) {
                $data['gameActionLog'] =
                    array_slice($data['gameActionLog'], 0, $args['logEntryLimit']);
                $data['gameChatLog'] =
                    array_slice($data['gameChatLog'], 0, $args['logEntryLimit']);
            }

            if (!(array_key_exists('playerNameArray', $data))) {
                $data['playerNameArray'] = array('tester1', 'tester2');
            }
            $timestamp = new DateTime();
            $data['timestamp'] = $timestamp->format(DATE_RSS);
            return array($data, "Loaded data for game " . $args['game']);
        }
        return array(NULL, "Game does not exist.");
    }

    protected function get_interface_response_loadGameActionLog() {
        $data = NULL;

        // Use the same fake games here which were described in loadGameData
        $gamesWithoutLogs =
            array('1', '2', '4', '6', '7', '8', '9', '10', '12', '13',
                '14', '15', '16', '17', '18');
        if (in_array($args['game'], $gamesWithoutLogs)) {
            $data = array(
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif (($args['game'] == '3') || ($args['game'] == '11')) {
            $data = array(
                'gameActionLog' => array(
                    array("timestamp" => "2013-12-22 21:09:01",
                          "message" =>
                              "tester2 performed Power attack using [(12):1] against [(4):1]; " .
                              "Defender (4) was captured; Attacker (12) rerolled 1 => 5"),
                    array("timestamp" => "2013-12-22 21:08:56",
                          "message" =>
                              "tester1 performed Power attack using [(4):2] against [(X):1]; " .
                              "Defender (X) was captured; Attacker (4) rerolled 2 => 1"),
                    array("timestamp" => "2013-12-22 21:03:52",
                          "message" =>
                              "tester2 performed Skill attack using [(4):4,(X):3] against [(10):7]; " .
                              "Defender (10) was captured; Attacker (4) rerolled 4 => 4; " .
                              "Attacker (X) rerolled 3 => 1"),
                    array("timestamp" => "2013-12-22 21:03:39",
                          "message" =>
                              "tester1 performed Power attack using [(4):3] against [(10):3]; " .
                              "Defender (10) was captured; Attacker (4) rerolled 3 => 4"),
                    array("timestamp" => "2013-12-22 21:03:12",
                          "message" =>
                              "tester2 performed Skill attack using [(4):1,(10):5,(12):5] against [(12):11]; " .
                              "Defender (12) was captured; Attacker (4) rerolled 1 => 4; " .
                              "Attacker (10) rerolled 5 => 3; Attacker (12) rerolled 5 => 1")
                ),
            );
        } elseif ($args['game'] == '5') {
            $data = array(
                'gameActionLog' => array(
                    array("timestamp" => "2013-12-20 00:52:42",
                          "message" => "End of round: tester1 won round 5 (46 vs 30)"),
                    array("timestamp" => "2013-12-20 00:52:42",
                          "message" =>
                              "tester1 performed Power attack using [(X):7] against [(4):2]; " .
                              "Defender (4) was captured; Attacker (X) rerolled 7 => 4"),
                    array("timestamp" => "2013-12-20 00:52:36",
                          "message" => "tester2 passed"),
                    array("timestamp" => "2013-12-20 00:52:33",
                          "message" =>
                              "tester1 performed Power attack using [(X):14] against [(10):4]; " .
                              "Defender (10) was captured; Attacker (X) rerolled 14 => 7"),
                    array("timestamp" => "2013-12-20 00:52:29",
                          "message" =>
                              "tester2 performed Power attack using [(10):10] against [(4):4]; " .
                              "Defender (4) was captured; Attacker (10) rerolled 10 => 4"),
                ),
            );
        }

        if ($data) {
            return array($data, "Loaded action log for game " . $args['game']);
        }
        return array(NULL, "Game does not exist.");
    }

    protected function get_interface_response_loadGameChatLog() {
        $data = NULL;

        // Use the same fake games here which were described in loadGameData
        $gamesWithoutLogs =
            array('1', '2', '4', '6', '7', '8', '9', '10', '12', '13',
                '14', '15', '16', '17', '18');
        if (in_array($args['game'], $gamesWithoutLogs)) {
            $data = array(
                'gameActionLog' => array(),
                'gameChatLog' => array(),
            );
        } elseif (($args['game'] == '3') || ($args['game'] == '11')) {
            $data = array(
                'gameChatLog' => array(
                    array("timestamp" => "2013-12-22 21:09:01",
                          "player" => "tester2",
                          "message" => "Hello."),
                    array("timestamp" => "2013-12-22 21:08:56",
                          "player" => "tester1",
                          "message" => "Hi."),
                    array("timestamp" => "2013-12-22 21:03:52",
                          "player" => "tester2",
                          "message" => "Greetings."),
                    array("timestamp" => "2013-12-22 21:03:39",
                          "player" => "tester1",
                          "message" => "Salutations."),
                    array("timestamp" => "2013-12-22 21:03:12",
                          "player" => "tester2",
                          "message" => "Good morning."),
                ),
            );
        } elseif ($args['game'] == '5') {
            $data = array(
                'gameChatLog' => array(
                    array("timestamp" => "2013-12-20 00:52:42",
                          "player" => "tester1",
                          "message" => "Pong."),
                    array("timestamp" => "2013-12-20 00:52:29",
                          "player" => "tester2",
                          "message" => "Ping!"),
                ),
            );
        }

        if ($data) {
            return array($data, "Loaded chat log for game " . $args['game']);
        }
        return array(NULL, "Game does not exist.");
    }

    protected function get_interface_response_loadPlayerName() {
        return array(array('userName' => 'tester1'), NULL);
    }

    protected function get_interface_response_loadPlayerInfo() {
        return array(array('id' => 1,
                           'name_ingame' => 'tester1',
                           'name_irl' => '',
                           'email' => 'tester1@example.com',
                           'status' => 'active',
                           'dob' => NULL,
                           'autopass' => TRUE,
                           'image_path' => NULL,
                           'comment' => NULL,
                           'last_action_time' => "0000-00-00 00:00:00",
                           'creation_time' => "2013-12-28 01:22:14",
                           'fanatic_button_id' => 0,
                           'n_games_won' => 0,
                           'n_games_lost' => 0,
                          ), NULL);
    }

    protected function get_interface_response_savePlayerInfo() {
        return array(array('playerId' => 1), 'Player info updated successfully.');
    }

    protected function get_interface_response_loadPlayerNames() {
        $data = array(
            'nameArray' => array(),
            'statusArray' => array(),
        );

        // three test players exist and are all active
        $data['nameArray'][] = 'tester1';
        $data['statusArray'][] = 'active';
        $data['nameArray'][] = 'tester2';
        $data['statusArray'][] = 'active';
        $data['nameArray'][] = 'tester3';
        $data['statusArray'][] = 'active';

        return array($data, "Names retrieved successfully.");
    }

    protected function get_interface_response_submitSwingValues($args) {
        $valid_swing = array('R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        foreach (array_keys($args['swingValueArray']) as $letter) {
            if (!(in_array($letter, $valid_swing, TRUE))) {
                return array(NULL, "Unknown swing letter $letter");
            }
        }
        return array(TRUE, 'Successfully set swing values');
    }

    protected function get_interface_response_reactToInitiative() {
        return array(array('gainedInitiative' => TRUE),
                     'Successfully gained initiative');
    }

    protected function get_interface_response_reactToAuxiliary() {
        return array(TRUE, 'Auxiliary die chosen successfully');
    }

    protected function get_interface_response_reactToReserve() {
        return array(TRUE, 'Reserve die chosen successfully');
    }

    protected function get_interface_response_submitTurn() {
        return array(TRUE, 'Dummy turn submission accepted');
    }

    protected function get_interface_response_login() {
//            $login_success = login($_POST['username'], $_POST['password']);
//            if ($login_success) {
//                $data = array('userName' => $_POST['username']);
//            } else {
//                $data = NULL;
//            }
        return array(NULL, "function not implemented");
    }

    protected function get_interface_response_logout() {
//            logout();
//            $data = array('userName' => FALSE);
        return array(NULL, "function not implemented");
    }

    // Ask get_interface_response() for the dummy response to the
    // request, then construct a response.  Match the logic in
    // responder as closely as possible for convenience.
    // * For live (remote) invocation:
    //   * display the output to the user
    // * For test invocation:
    //   * return the output as a PHP variable
    public function process_request($args) {

        // make sure all arguments passed to the function are
        // syntactically reasonable, using the same ApiSpec used
        // by the real responder
        $argcheck = $this->spec->verify_function_args($args);
        if ($argcheck['ok']) {

            // As far as we can easily tell, arguments are okay.
            // Pass them along to the dummy responder functions.
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
        } else {
            $output = array(
                'data' => NULL,
                'status' => 'failed',
                'message' => $argcheck['message'],
            );
        }

        if ($this->isTest) {
            return $output;
        } else {
            header('Content-Type: application/json');
            echo json_encode($output);
        }
    }
}
