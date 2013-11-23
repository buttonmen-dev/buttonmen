<?php
    session_start();
    require 'api_core.php';

    require_once('../lib/bootstrap.php');

    header('Content-Type: application/json');

    $interface = new BMInterface;

    switch ($_POST['type']) {

        case 'createUser':
	    $data = $interface->create_user($_POST['username'],
	                                    $_POST['password']);
            break;

        case 'createGame':
            $playerNameArray = $_POST['playerNameArray'];
            $playerIdArray = array();
            foreach ($playerNameArray as $playerName) {
                $playerIdArray[] = $interface->get_player_id_from_name($playerName);
            }

            $buttonNameArray = $_POST['buttonNameArray'];
            $maxWins = $_POST['maxWins'];

            $data = $interface->create_game($playerIdArray, $buttonNameArray, $maxWins);
            break;

        case 'loadActiveGames':
            $data = $interface->get_all_active_games($_SESSION['user_id']);
            break;

        case 'loadButtonNames':
            $data = $interface->get_all_button_names();
            break;

        case 'loadGameData':
            $data = NULL;
            $game = $interface->load_game($_POST['game']);
            if ($game) {
                $currentPlayerId = $_SESSION['user_id'];
                $currentPlayerIdx = array_search($currentPlayerId, $game->playerIdArray);

                foreach ($game->playerIdArray as $playerId) {
                    $playerNameArray[] = $interface->get_player_name_from_id($playerId);
                }

                $data = array(
                    'currentPlayerIdx' => $currentPlayerIdx,
                    'gameData' => $game->getJsonData($currentPlayerId),
                    'playerNameArray' => $playerNameArray,
                    'timestamp' => $interface->timestamp->format(DATE_RSS),
                );
            }
            break;

        case 'loadPlayerName':
            if (array_key_exists('user_name', $_SESSION)) {
                $data = array('userName' => $_SESSION['user_name']);
            } else {
                $data = NULL;
            }
            break;

        case 'loadPlayerNames':
            $data = $interface->get_player_names_like('');
            break;

        case 'submitSwingValues':
            $data = $interface->submit_swing_values($_SESSION['user_id'],
                                                    $_POST['game'],
                                                    $_POST['roundNumber'],
                                                    $_POST['timestamp'],
                                                    $_POST['swingValueArray']);
            break;

        case 'submitTurn':
            $data = $interface->submit_turn($_SESSION['user_id'],
                                            $_POST['game'],
                                            $_POST['roundNumber'],
                                            $_POST['timestamp'],
                                            $_POST['dieSelectStatus'],
                                            $_POST['attackType'],
                                            (int)$_POST['attackerIdx'],
                                            (int)$_POST['defenderIdx']);
            break;

        case 'login':
            $login_success = login($_POST['username'], $_POST['password']);
            if ($login_success) {
                $data = array('userName' => $_POST['username']);
            } else {
                $data = NULL;
            }
            break;

        case 'logout':
            logout();
            $data = array('userName' => False);
            break;

        default:
            $data = NULL;
    }

    $output = array(
        'data' => $data,
        'message' => $interface->message,
    );
    if ($data) {
        $output['status'] = 'ok';
    } else {
        $output['status'] = 'failed';
    }

    echo json_encode($output);
?>
