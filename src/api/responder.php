<?php
    session_start();

    require_once('../lib/bootstrap.php');

    header('Content-Type: application/json');

    $interface = new BMInterface;

    switch ($_POST['type']) {

        case 'checkPlayerNames':
            $arePlayerNamesValid = TRUE;
            foreach ($_POST['playerNameArray'] as $playerName) {
                if ('' == $interface->get_player_id_from_name($playerName)) {
                    $arePlayerNamesValid = FALSE;
                }
            }
            $output = array('status' => 'ok',
                            'data' => $arePlayerNamesValid);
            break;

        case 'chooseActiveGame':
            $_SESSION['active_game'] = $_POST['input'];
            $output = array('status' => 'ok');
            break;

        case 'chooseButtons':
            $playerNameArray = $_POST['playerNameArray'];
            $playerIdArray = array();
            foreach ($playerNameArray as $playerName) {
                $playerIdArray[] = $interface->get_player_id_from_name($playerName);
            }

            $buttonNameArray = $_POST['buttonNameArray'];
            $maxWins = $_POST['maxWins'];

            $gameId = $interface->create_game($playerIdArray, $buttonNameArray, $maxWins);

            $output = array('status' => 'ok',
                            'data' => $gameId);
            break;

        case 'loadActiveGames':
            $output = $interface->get_all_active_games($_SESSION['user_id']);
            break;

        case 'loadButtonNames':
            $output = $interface->get_all_button_names();
            break;

        case 'loadGameData':
            $game = $interface->load_game($_SESSION['active_game']);
            $game->proceed_to_next_user_action();

            $currentPlayerId = $_SESSION['user_id'];
            $currentPlayerIdx = array_search($currentPlayerId, $game->playerIdArray);

            $output = array('status' => 'ok',
                            'currentPlayerIdx' => $currentPlayerIdx,
                            'gameData' => $game->getJsonData());
            break;

        case 'loadMockGameDataDeterminingInitiative':
            // load game
            require_once 'loadMockGameData.php';
            $game = loadMockGameDataDeterminingInitiative();

            // save game to create a real file
            $interface->save_game($game);

            // reload game
            $gameId = $game->gameId;
            $game = $interface->load_game($gameId);
            $output = $game->getJsonData();
            break;

        case 'loadMockGameDataRoundStart':
            // load game
            require_once 'loadMockGameData.php';
            $game = loadMockGameDataRoundStart();

            // save game to create a real file
            $interface->save_game($game);

            // reload game
            $gameId = $game->gameId;
            $game = $interface->load_game($gameId);
            $output = $game->getJsonData();
            break;

        case 'loadMockGameDataWaitingForSwing':
            // load game
            require_once 'loadMockGameData.php';
            $game = loadMockGameDataWaitingForSwing();

            // save game to create a real file
            $interface->save_game($game);

            // reload game
            $gameId = $game->gameId;
            $game = $interface->load_game($gameId);
            $output = $game->getJsonData();
            break;

        case 'loadPlayerName':
            $output = array('status' => 'ok',
                            'data' => $_SESSION['user_name']);
            break;

        case 'loadPlayerNames':
            $output = $interface->get_player_names_like('');
            break;

        case 'loadPlayerNamesLike':
            $input = $_POST['input'];
            $output = $interface->get_player_names_like($input);
            break;

        case 'submitSwingValues':
            $gameId = $_POST['gameId'];

            $game = $interface->load_game($gameId);

            // check that the game state is correct and that the swing values
            // still need to be set
            $roundNumber = $_POST['roundNumber'];
            $currentPlayerIdx = $_POST['currentPlayerIdx'];
            if (($roundNumber != $game->roundNumber) ||
                (BMGameState::specifyDice != $game->gameState) ||
                (FALSE == $game->waitingOnActionArray[$currentPlayerIdx])) {
                $output = FALSE;
                break;
            }

            // try to set swing values
            $swingValueArray = json_decode($_POST['swingValueArray']);
            $swingRequestArray = array_keys($game->swingRequestArrayArray[$currentPlayerIdx]);

            if (count($swingRequestArray) != count($swingValueArray)) {
                $output = FALSE;
                break;
            }

            $swingValueArrayWithKeys = array();
            foreach ($swingRequestArray as $swingIdx => $swingRequest) {
                $swingValueArrayWithKeys[$swingRequest] = $swingValueArray[$swingIdx];
            }

            $game->swingValueArrayArray[$currentPlayerIdx] = $swingValueArrayWithKeys;

            $game->proceed_to_next_user_action();

            // check for successful swing value set
            if ((FALSE == $game->waitingOnActionArray[$currentPlayerIdx]) ||
                ($game->gameState > BMGameState::specifyDice) ||
                ($game->roundNumber > $roundNumber)) {
                $output = array('status' => 'ok');
                $interface->save_game($game);
            } else {
                $output = array('status' => $game->message);
            }

            break;

        default:
            $output = FALSE;
    }

    if (is_array($output) && !array_key_exists('message', $output)) {
        $output['message'] = $interface->message;
    }

    echo json_encode($output);
?>
