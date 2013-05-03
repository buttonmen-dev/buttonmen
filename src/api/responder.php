<?php
    require_once '../engine/BMInterface.php';

    header('Content-Type: application/json');

    $interface = new BMInterface;

    switch ($_POST['type']) {
        case 'loadPlayerName':
            $output = array('status' => 'ok',
                            'data' => 'blackshadowshade');
            break;
        case 'loadButtonNames':
            $output = array('buttonNameArray' => $interface->get_all_button_names());
            break;
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
        case 'submitSwingValues':
            $output = array('status' => 'ok',
                            'data' => 'created game');
            break;
        default:
            $output = FALSE;
    }

    echo json_encode($output);
?>
