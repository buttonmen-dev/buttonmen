<?php
    require_once '../engine/BMInterface.php';

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
        case 'loadMockGameData':
            require_once 'loadMockGameData.php';
            $game = loadMockGameDataWaitingForSwing();
            $output = $game->getJsonData();
            break;
        case 'loadPlayerName':
            $output = array('status' => 'ok',
                            'data' => 'blackshadowshade');
            break;
        case 'submitSwingValues':
            $output = array('status' => 'ok',
                            'data' => 'created game');
            break;
        default:
            $output = FALSE;
    }

    if (is_array($output)) {
        $output['message'] = $interface->message;
    }

    echo json_encode($output);
?>
