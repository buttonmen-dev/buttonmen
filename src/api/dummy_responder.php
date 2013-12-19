<?php

/** Alternative responder which doesn't use real databases or
 *  sessions, but rather exists only to send dummy data used for
 *  automated testing of API compliance
 */

    session_start();

    header('Content-Type: application/json');

    switch ($_POST['type']) {

//        case 'createUser':
//	    $data = $interface->create_user($_POST['username'],
//	                                    $_POST['password']);
//            break;

//        case 'createGame':
//            $playerNameArray = $_POST['playerNameArray'];
//            $playerIdArray = array();
//            foreach ($playerNameArray as $playerName) {
//                $playerId = $interface->get_player_id_from_name($playerName);
//                if (is_int($playerId)) {
//                    $playerIdArray[] = $playerId;
//                } else {
//                    $playerIdArray[] = NULL;
//                }
//            }
//
//            $buttonNameArray = $_POST['buttonNameArray'];
//            $maxWins = $_POST['maxWins'];
//
//            $data = $interface->create_game($playerIdArray, $buttonNameArray, $maxWins);
//            break;
//
//        case 'loadActiveGames':
//            $data = $interface->get_all_active_games($_SESSION['user_id']);
//            break;
//
        case 'loadButtonNames':
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

            $message = "All button names retrieved successfully.";
            break;

        case 'loadGameData':
	    // The dummy loadGameData returns one of a number of
	    // sets of dummy game data, for general test use.
	    // Specify which one you want using the game number:
            //   1: a newly-created game, waiting for both players to set swing dice
            //   2: new game in which the active player has set swing dice
            $data = NULL;
            if ($_POST['game'] == '1') {
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
                            "swingRequestArrayArray" => array(array("X"), array("X")),
                            "validAttackTypeArray" => array(),
                            "roundScoreArray" => array(15, 15),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(),
                );
            } elseif ($_POST['game'] == '2') {
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
                            "swingRequestArrayArray" => array(array("X"), array("X")),
                            "validAttackTypeArray" => array(),
                            "roundScoreArray" => array(15, 15),
                            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                                           array("W" => 0, "L" => 0, "D" => 0)),
                        ),
                    ),
                    'currentPlayerIdx' => 0,
                    'gameActionLog' => array(),
                );
            }
            if ($data) {
                $data['playerNameArray'] = array('tester1', 'tester2');
                $timestamp = new DateTime();
                $data['timestamp'] = $timestamp->format(DATE_RSS);
                $message = "Loaded data for game " . $_POST['game'];
            }
            break;

//
//        case 'loadPlayerName':
//            if (array_key_exists('user_name', $_SESSION)) {
//                $data = array('userName' => $_SESSION['user_name']);
//            } else {
//                $data = NULL;
//            }
//            break;
//
        case 'loadPlayerNames':
            $data = array(
                'nameArray' => array(),
            );

            // three test players exist
            $data['nameArray'][] = 'tester1';
            $data['nameArray'][] = 'tester2';
            $data['nameArray'][] = 'tester3';

            $message = "Names retrieved successfully.";
            break;

//        case 'submitSwingValues':
//            $data = $interface->submit_swing_values($_SESSION['user_id'],
//                                                    $_POST['game'],
//                                                    $_POST['roundNumber'],
//                                                    $_POST['timestamp'],
//                                                    $_POST['swingValueArray']);
//            break;
//
//        case 'submitTurn':
//            $data = $interface->submit_turn($_SESSION['user_id'],
//                                            $_POST['game'],
//                                            $_POST['roundNumber'],
//                                            $_POST['timestamp'],
//                                            $_POST['dieSelectStatus'],
//                                            $_POST['attackType'],
//                                            (int)$_POST['attackerIdx'],
//                                            (int)$_POST['defenderIdx']);
//            break;
//
//        case 'login':
//            $login_success = login($_POST['username'], $_POST['password']);
//            if ($login_success) {
//                $data = array('userName' => $_POST['username']);
//            } else {
//                $data = NULL;
//            }
//            break;
//
//        case 'logout':
//            logout();
//            $data = array('userName' => False);
//            break;
//
        default:
            $data = NULL;
            $message = 'Requested function not implemented in dummy_responder';
    }

    $output = array(
        'data' => $data,
        'message' => $message,
    );
    if ($data) {
        $output['status'] = 'ok';
    } else {
        $output['status'] = 'failed';
    }

    echo json_encode($output);
?>
