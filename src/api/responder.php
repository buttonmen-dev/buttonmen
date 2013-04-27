<?php
    require_once '../engine/BMInterface.php';

    $interface = new BMInterface;

    header('Content-Type: application/json');

    switch ($_POST['type']) {
        case 'loadPlayerName':
            echo json_encode(array('status' => 'ok',
                                   'data' => 'blackshadowshade'));
            break;
        case 'loadButtonNames':
            $dataArray = array(
                'buttonNameArray' => $interface->get_all_button_names());
            echo json_encode($dataArray);
            break;
        case 'checkPlayerNames':
            $arePlayerNamesValid = TRUE;
            foreach ($_POST['playerNameArray'] as $playerName) {
                if ('' == $interface->get_player_id_from_name($playerName)) {
                    $arePlayerNamesValid = FALSE;
                }
            }
            echo json_encode(array('status' => 'ok',
                                   'data' => $arePlayerNamesValid));
            break;
        case 'chooseButtons':
            $buttonNameArray = $_POST['buttonNameArray'];
            echo json_encode(array('status' => 'ok',
                                   'data' => $buttonNameArray));
            break;
        case 'submitSwingValues':
            echo json_encode(array('status' => 'ok',
                                   'data' => 'created game'));
            break;
        default:
            //do nothing
    }
?>
