<?php
    session_start();
    require 'api_core.php';

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
            $output = array('status' => 'ok',
                            'data' => $_SESSION['active_game']);
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

            $currentPlayerId = $_SESSION['user_id'];
            $currentPlayerIdx = array_search($currentPlayerId, $game->playerIdArray);

            foreach ($game->playerIdArray as $playerId) {
                $playerNameArray[] = $interface->get_player_name_from_id($playerId);
            }

            $output = array('status' => 'ok',
                            'currentPlayerIdx' => $currentPlayerIdx,
                            'gameData' => $game->getJsonData(),
                            'playerNameArray' => $playerNameArray,
                            'timestamp' => $interface->timestamp->format(DATE_RSS));
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
            $game = $interface->load_game($_SESSION['active_game']);
            $currentPlayerIdx = array_search($_SESSION['user_id'], $game->playerIdArray);
            $roundNumber = $_POST['roundNumber'];

            // check that the timestamp and the game state are correct, and that
            // the swing values still need to be set
            if (!is_page_current($interface,
                                 $game,
                                 BMGameState::specifyDice,
                                 $_POST['timestamp'],
                                 $roundNumber,
                                 $_SESSION['user_id'])) {
                $output = FALSE;
                break;
            }

            // try to set swing values
            $swingValueArray = $_POST['swingValueArray'];
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
        case 'submitTurn':
            $game = $interface->load_game($_SESSION['active_game']);
            if (!is_page_current($interface,
                                 $game,
                                 BMGameState::startTurn,
                                 $_POST['timestamp'],
                                 $_POST['roundNumber'],
                                 $_SESSION['user_id'])) {
                $output = FALSE;
                break;
            }

            require_once '../engine/BMAttack.php';

            // load dieSelectStatus, which should contain boolean values of whether each
            // die is selected, starting with attacker dice and concluding with
            // defender dice
            $dieSelectStatus = $_POST['dieSelectStatus'];

            // determine attacker and defender indices from POST
            $attackerIdx = intval($_POST['attackerIdx']);
            $defenderIdx = intval($_POST['defenderIdx']);
            $attackers = array();
            $defenders = array();
            $attackerDieIdx = array();
            $defenderDieIdx = array();

            // divide selected dice up into attackers and defenders
            $nAttackerDice = count($game->activeDieArrayArray[$attackerIdx]);
            $nDefenderDice = count($game->activeDieArrayArray[$defenderIdx]);

            for ($dieIdx = 0; $dieIdx < $nAttackerDice; $dieIdx++) {
                if (filter_var($dieSelectStatus['playerIdx_'.$attackerIdx.'_dieIdx_'.$dieIdx],
                    FILTER_VALIDATE_BOOLEAN)) {
                    $attackers[] = $game->activeDieArrayArray[$attackerIdx][$dieIdx];
                    $attackerDieIdx[] = $dieIdx;
                }
            }

            for ($dieIdx = 0; $dieIdx < $nDefenderDice; $dieIdx++) {
                if (filter_var($dieSelectStatus['playerIdx_'.$defenderIdx.'_dieIdx_'.$dieIdx],
                    FILTER_VALIDATE_BOOLEAN)) {
                    $defenders[] = $game->activeDieArrayArray[$defenderIdx][$dieIdx];
                    $defenderDieIdx[] = $dieIdx;
                }
            }

            // validate attack
            // james: eventually, we expect the attack type to be passed from
            // the front-end to responder.php, meaning that the following code
            // can be even more streamlined, since we will then not need to
            // work out all the possible attack types
            $attackTypeArray = $game->valid_attack_types();
            $success = FALSE;

            foreach ($attackTypeArray as $idx => $attackType) {
                // find out if the chosen dice form a valid attack
                $game->attack = array($attackerIdx, $defenderIdx,
                                      $attackerDieIdx, $defenderDieIdx,
                                      $attackTypeArray[$idx]);
                $attack = BMAttack::get_instance($attackType);

                foreach ($attackers as $attackDie) {
                    $attack->add_die($attackDie);
                }
                if ($attack->find_attack($game)) {
                    if ($attack->validate_attack($game, $attackers, $defenders)) {
                        $success = TRUE;
                        break;
                    }
                }
            }

            // output the result of the attack
            if ($success) {
                $output = array('status' => 'attack valid');
                $interface->save_game($game);
            } else {
                $output = array('status' => 'attack invalid');
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
