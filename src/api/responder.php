<?php
    require_once '../engine/BMInterface.php';
    require_once '../engine/BMGame.php';

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

        case 'chooseButtons':
            $playerNameArray = $_POST['playerNameArray'];
            $playerIdArray = [];
            foreach ($playerNameArray as $playerName) {
                $playerIdArray[] = $interface->get_player_id_from_name($playerName);
            }

            $buttonNameArray = $_POST['buttonNameArray'];
            $maxWins = $_POST['maxWins'];

            $gameId = $interface->create_game($playerIdArray, $buttonNameArray, $maxWins);

            $output = array('status' => 'ok',
                            'data' => $gameId);
            break;

        case 'loadButtonNames':
            $output = array('buttonNameArray' => $interface->get_all_button_names());
            break;

        case 'loadGameData':
            $gameId = $_POST['gameId'];
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
                            'data' => 'blackshadowshade');
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
