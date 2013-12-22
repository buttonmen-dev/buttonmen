<?php

class responder {

    // properties
    private $isTest;               // whether this invocation is for testing

    // constructor
    // * For live invocation:
    //   * start a session (and require api_core to get session functions)
    // * For test invocation:
    //   * don't start a session
    public function __construct($isTest = FALSE) {
        $this->isTest = $isTest;

        if (!($this->isTest)) {
            session_start();
            require 'api_core.php';
            require_once('../lib/bootstrap.php');
        }
    }

    // This function looks at the provided arguments, calls an
    // appropriate interface routine, and returns either some game
    // data on success, or NULL on failure
    protected function get_interface_response($interface, $args) {

        if ($args['type'] == 'createUser') {
            return $interface->create_user($args['username'], $args['password']);
        }

        if ($args['type'] == 'createGame') {
            $playerNameArray = $args['playerNameArray'];
            $playerIdArray = array();
            foreach ($playerNameArray as $playerName) {
                $playerId = $interface->get_player_id_from_name($playerName);
                if (is_int($playerId)) {
                    $playerIdArray[] = $playerId;
                } else {
                    $playerIdArray[] = NULL;
                }
            }

            $buttonNameArray = $args['buttonNameArray'];
            $maxWins = $args['maxWins'];

            return $interface->create_game($playerIdArray, $buttonNameArray, $maxWins);
        }

        if ($args['type'] == 'loadActiveGames') {
            return $interface->get_all_active_games($_SESSION['user_id']);
        }

        if ($args['type'] == 'loadButtonNames') {
            return $interface->get_all_button_names();
        }

        if ($args['type'] == 'loadGameData') {
            $data = NULL;
            $game = $interface->load_game($args['game']);
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
                    'gameActionLog' => $interface->load_game_action_log($game),
                );
            }
            return $data;
        }

        if ($args['type'] == 'loadPlayerName') {
            if (array_key_exists('user_name', $_SESSION)) {
                return array('userName' => $_SESSION['user_name']);
            } else {
                return NULL;
            }
        }

        if ($args['type'] == 'loadPlayerInfo') {
            return $interface->get_player_info($_SESSION['user_id']);
        }

        if ($args['type'] == 'savePlayerInfo') {
            $interface->set_player_info($_SESSION['user_id'],
                                        array('autopass' => $args['autopass']));
        }

        if ($args['type'] == 'loadPlayerNames') {
            return $interface->get_player_names_like('');
        }

        if ($args['type'] == 'submitSwingValues') {
            return $interface->submit_swing_values($_SESSION['user_id'],
                                                   $args['game'],
                                                   $args['roundNumber'],
                                                   $args['timestamp'],
                                                   $args['swingValueArray']);
        }

        if ($args['type'] == 'submitTurn') {
            return $interface->submit_turn($_SESSION['user_id'],
                                           $args['game'],
                                           $args['roundNumber'],
                                           $args['timestamp'],
                                           $args['dieSelectStatus'],
                                           $args['attackType'],
                                           (int)$args['attackerIdx'],
                                           (int)$args['defenderIdx']);
        }

        if ($args['type'] == 'login') {
            $login_success = login($args['username'], $args['password']);
            if ($login_success) {
                return array('userName' => $args['username']);
            } else {
                return NULL;
            }
        }

        if ($args['type'] == 'logout') {
            logout();
            return array('userName' => False);
        }

        // no action specified
        return NULL;
    }

    // Construct an interface, ask it for the response to the
    // request, then construct a response
    // * For live invocation:
    //   * display the output to the user
    // * For test invocation:
    //   * return the output as a PHP variable
    public function process_request($args) {
        $interface = new BMInterface($this->isTest);
        $data = $this->get_interface_response($interface, $args);

        $output = array(
            'data' => $data,
            'message' => $interface->message,
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

// If responder was called via a POST request (rather than by
// test code), the $_POST variable will be set
if ($_POST) {
    $responder = new responder(False);
    $responder->process_request($_POST);
}
?>
