<?php
/**
 * DummyApiResponder: Contains the mock data used for unit testing the UI
 *
 * @author chaos
 */

/**
 * This class generates the mock data necessary for unit testing the UI
 */
class DummyApiResponder {
    // properties

    // N.B. this class is always used for some type of testing,
    // but, the usage here matches the way responder uses this flag:
    // * FALSE: this instance is being accessed remotely via POST
    // * TRUE:  this instance is being accessed locally by unit tests
    private $isTest;               // whether this invocation is for testing

    /**
     * Constructor
     * For live invocation:
     *   start a session (don't use api_core because dummy_responder has no backend)
     * For test invocation:
     *   don't start a session
     *
     * @param ApiSpec $spec
     * @param boolean $isTest
     */
    public function __construct(ApiSpec $spec, $isTest = FALSE) {
        $this->spec = $spec;
        $this->isTest = $isTest;

        if ($this->isTest) {
            $this->jsonFileRoot = BW_PHP_ROOT . "/api/dummy_data/";
        } else {
            session_start();
            $this->jsonFileRoot = "./dummy_data/";
        }

        // Functions whose dummy data is not yet being provided by responderTest
        $this->untransformedFunctions = array(
            'loadActiveGames',
            'loadCompletedGames',
            'loadRejectedGames',
            'loadNewGames',
            'loadOpenGames',
            'login',
            'logout',
        );
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

    /** Utility function to load canned JSON data from a file
     *
     * @param string $apiFunction
     * @param string $fileName
     *
     * @return array tuple containing data on success or NULL on failure
     */
    protected function load_json_data_from_file($apiFunction, $fileName) {
        $filePath = $this->jsonFileRoot . $apiFunction . '/' . $fileName;
        if (file_exists($filePath)) {
            try {
                $file_data = file_get_contents($filePath);
                return json_decode($file_data, TRUE);
            } catch (Exception $e) {
                error_log(
                    "Received exception in DummyApiResponder while trying to read " . $fileName .
                    "in response to an API query for " . $apiFunction . ": " . $e
                );
                return NULL;
            }
        } else {
            error_log(
                "DummyApiResponder tried to read nonexistent file " . $fileName .
                " in response to an API query for " . $apiFunction
            );
            return NULL;
        }
    }

    protected function get_interface_response_createUser($args) {
        return $this->load_json_data_from_file(
            'createUser',
            $args['username'] . '.json'
        );
    }

    protected function get_interface_response_verifyUser($args) {
        return $this->load_json_data_from_file(
            'verifyUser',
            $args['playerId'] . '.json'
        );
    }

    protected function get_interface_response_createGame($args) {
        return $this->load_json_data_from_file(
            'createGame',
            $args['playerInfoArray'][0][1] . '_' . $args['playerInfoArray'][1][1] . '.json'
        );
        // for verisimilitude, choose a game ID of one greater than
        // the number of "existing" games represented in loadGameData
        // and loadActiveGames

        $gameId = 26;
        return array(array('gameId' => $gameId), "Game $gameId created successfully.");
    }

    protected function get_interface_response_joinOpenGame($args) {
        return $this->load_json_data_from_file(
            'joinOpenGame',
            $args['gameId'] . '.json'
        );
    }

    protected function get_interface_response_selectButton() {
        // select_button() does not need to return much data
        return array(TRUE, "");
    }

    protected function get_interface_response_loadOpenGames() {
        // Use games that didn't appear in loadGameData
        $games = array();

        // game 20
        $games[] = array(
            'gameId' => 20,
            'challengerId' => 1,
            'challengerName' => 'tester',
            'challengerButton' => 'Avis',
            'challengerColor' => '#cccccc',
            'victimButton' => NULL,
            'targetWins' => 3,
        );

        // game 21
        $games[] = array(
            'gameId' => 21,
            'challengerId' => 2,
            'challengerName' => 'tester2',
            'challengerButton' => 'Von Pinn',
            'challengerColor' => '#cccccc',
            'victimButton' => 'Apples',
            'targetWins' => 3,
        );

        return array(array('games' => $games), "Open games retrieved successfully.");
    }

    protected function get_interface_response_searchGameHistory($args) {
        if (isset($args['status'])) {
            $argval = $args['status'];
        } elseif (isset($args['buttonNameA'])) {
            $argval = $args['buttonNameA'];
        } else {
            $argval = 'noargs';
        }
        return $this->load_json_data_from_file(
            'searchGameHistory',
            $argval . '.json'
        );
    }

    protected function mock_completed_game($args) {
        if (!isset($args['playerNameA']) || $args['playerNameA'] == 'tester') {
            // game 5
            $game = array(
                'gameId' => 5,
                'playerIdA' => 1,
                'playerNameA' => 'tester',
                'buttonNameA' => 'Avis',
                'colorA' => '#cccccc',
                'playerIdB' => 2,
                'playerNameB' => 'tester2',
                'buttonNameB' => 'Avis',
                'colorB' => '#dddddd',
                'gameStart' => 1399605464,
                'lastMove' => 1399691804,
                'roundsWonA' => 3,
                'roundsWonB' => 2,
                'roundsDrawn' => 0,
                'targetWins' => 3,
                'status' => 'COMPLETE',
            );
        } elseif (!isset($args['playerNameA']) || $args['playerNameA'] == 'tester2') {
            // game 5
            $game = array(
                'gameId' => 5,
                'playerIdA' => 2,
                'playerNameA' => 'tester2',
                'buttonNameA' => 'Avis',
                'colorA' => '#cccccc',
                'playerIdB' => 1,
                'playerNameB' => 'tester',
                'buttonNameB' => 'Avis',
                'colorB' => '#dddddd',
                'gameStart' => 1399605464,
                'lastMove' => 1399691804,
                'roundsWonA' => 3,
                'roundsWonB' => 2,
                'roundsDrawn' => 0,
                'targetWins' => 3,
                'status' => 'COMPLETE',
            );
        }

        return $game;
    }

    protected function mock_active_game($args) {
        if (!isset($args['playerNameA']) || $args['playerNameA'] == 'tester') {
            // game 6
            $game = array(
                'gameId' => 6,
                'playerIdA' => 1,
                'playerNameA' => 'tester',
                'buttonNameA' => 'Buck Godot',
                'colorA' => '#cccccc',
                'playerIdB' => 2,
                'playerNameB' => 'tester2',
                'buttonNameB' => 'Von Pinn',
                'colorB' => '#dddddd',
                'gameStart' => 1399605469,
                'lastMove' => 1399691809,
                'roundsWonA' => 0,
                'roundsWonB' => 0,
                'roundsDrawn' => 0,
                'targetWins' => 3,
                'status' => 'ACTIVE',
            );
        } elseif (!isset($args['playerNameA']) || $args['playerNameA'] == 'tester2') {
            // game 5
            $game = array(
                'gameId' => 6,
                'playerIdA' => 2,
                'playerNameA' => 'tester2',
                'buttonNameA' => 'Buck Godot',
                'colorA' => '#cccccc',
                'playerIdB' => 1,
                'playerNameB' => 'tester',
                'buttonNameB' => 'Von Pinn',
                'colorB' => '#dddddd',
                'gameStart' => 1399605469,
                'lastMove' => 1399691809,
                'roundsWonA' => 0,
                'roundsWonB' => 0,
                'roundsDrawn' => 0,
                'targetWins' => 3,
                'status' => 'ACTIVE',
            );
        }

        return $game;
    }

    protected function get_interface_response_loadNewGames() {
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
            'inactivityArray' => array(),
            'inactivityRawArray' => array(),
            'playerColorArray' => array(),
            'opponentColorArray' => array(),
        );

        return array($data, "All game details retrieved successfully.");
    }

    protected function get_interface_response_loadActiveGames() {
        // Use the same fake games here which were described in loadGameData
        $data = array(
            'gameIdArray' => array(),
            'gameDescriptionArray' => array(),
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
            'inactivityArray' => array(),
            'inactivityRawArray' => array(),
            'playerColorArray' => array(),
            'opponentColorArray' => array(),
        );

        for ($gameIdx = 1; $gameIdx <= 25; $gameIdx++) {
            $funcname = 'add_active_game_data_'.$gameIdx;
            $this->$funcname($data);
        }

        return array($data, "All game details retrieved successfully.");
    }

    protected function add_active_game_data_1(&$data) {
        $data['gameIdArray'][] = 1;
        $data['gameDescriptionArray'][] = 'Game 1';
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
        $data['inactivityArray'][] = "17 minutes";
        $data['inactivityRawArray'][] = 17*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_2(&$data) {
        $data['gameIdArray'][] = 2;
        $data['gameDescriptionArray'][] = 'Game 2';
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
        $data['inactivityArray'][] = "2 hours";
        $data['inactivityRawArray'][] = 2*60*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_3(&$data) {
        $data['gameIdArray'][] = 3;
        $data['gameDescriptionArray'][] = 'Game 3';
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
        $data['inactivityArray'][] = "5 minutes";
        $data['inactivityRawArray'][] = 5*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_4(&$data) {
        $data['gameIdArray'][] = 4;
        $data['gameDescriptionArray'][] = 'Game 4';
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
        $data['inactivityArray'][] = "6 days";
        $data['inactivityRawArray'][] = 6*60*60*24;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_5() {
        // fake game 5 is completed
    }

    protected function add_active_game_data_6(&$data) {
        $data['gameIdArray'][] = 6;
        $data['gameDescriptionArray'][] = 'Game 6';
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
        $data['inactivityArray'][] = "44 seconds";
        $data['inactivityRawArray'][] = 44;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_7(&$data) {
        $data['gameIdArray'][] = 7;
        $data['gameDescriptionArray'][] = 'Game 7';
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
        $data['inactivityArray'][] = "22 minutes";
        $data['inactivityRawArray'][] = 22*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_8(&$data) {
        $data['gameIdArray'][] = 8;
        $data['gameDescriptionArray'][] = 'Game 8';
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
        $data['inactivityArray'][] = "19 hours";
        $data['inactivityRawArray'][] = 19*60*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_9(&$data) {
        $data['gameIdArray'][] = 9;
        $data['gameDescriptionArray'][] = 'Game 9';
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
        $data['inactivityArray'][] = "1 day";
        $data['inactivityRawArray'][] = 1*60*60*24;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_10() {
        // tester1 is not a participant in fake game 10
    }

    protected function add_active_game_data_11() {
        // tester1 is not a participant in fake game 11
    }

    protected function add_active_game_data_12() {
        // tester1 is not a participant in fake game 12
    }

    protected function add_active_game_data_13(&$data) {
        $data['gameIdArray'][] = 13;
        $data['gameDescriptionArray'][] = 'Game 13';
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
        $data['inactivityArray'][] = "16 days";
        $data['inactivityRawArray'][] = 16*60*60*24;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_14(&$data) {
        $data['gameIdArray'][] = 14;
        $data['gameDescriptionArray'][] = 'Game 14';
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
        $data['inactivityArray'][] = "38 minutes";
        $data['inactivityRawArray'][] = 38*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_15() {
        // tester1 is not a participant in fake game 15
    }

    protected function add_active_game_data_16(&$data) {
        $data['gameIdArray'][] = 16;
        $data['gameDescriptionArray'][] = 'Game 16';
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
        $data['inactivityArray'][] = "1 minute";
        $data['inactivityRawArray'][] = 1*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_17(&$data) {
        $data['gameIdArray'][] = 17;
        $data['gameDescriptionArray'][] = 'Game 17';
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
        $data['inactivityArray'][] = "21 hours";
        $data['inactivityRawArray'][] = 21*60*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_18() {
        // tester1 is not a participant in fake game 18
    }

    protected function add_active_game_data_19(&$data) {
        $data['gameIdArray'][] = 19;
        $data['gameDescriptionArray'][] = 'Game 19';
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Apples";
        $data['opponentButtonNameArray'][] = "Apples";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "SPECIFY_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "10 minutes";
        $data['inactivityRawArray'][] = 10*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_20() {
        // fake game 20 is an open game
    }

    protected function add_active_game_data_21() {
        // fake game 21 is an open game
    }

    protected function add_active_game_data_22(&$data) {
        $data['gameIdArray'][] = 22;
        $data['gameDescriptionArray'][] = 'Game 22';
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Adam Spam";
        $data['opponentButtonNameArray'][] = "Adam Spam";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "ADJUST_FIRE_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "4 minutes";
        $data['inactivityRawArray'][] = 4*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_23(&$data) {
        $data['gameIdArray'][] = 23;
        $data['gameDescriptionArray'][] = 'Game 23';
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Adam Spam";
        $data['opponentButtonNameArray'][] = "Adam Spam";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "ADJUST_FIRE_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "4 minutes";
        $data['inactivityRawArray'][] = 4*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_24() {
        // tester1 is not a participant in fake game 24
    }

    protected function add_active_game_data_25(&$data) {
        $data['gameIdArray'][] = 25;
        $data['gameDescriptionArray'][] = 'Game 25';
        $data['opponentIdArray'][] = 1;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Miser";
        $data['opponentButtonNameArray'][] = "Miser";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "START_TURN";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "5 minutes";
        $data['inactivityRawArray'][] = 5*60;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function get_interface_response_loadCompletedGames() {
        $data = array(
            'gameIdArray' => array(),
            'gameDescriptionArray' => array(),
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
            'inactivityArray' => array(),
            'playerColorArray' => array(),
            'opponentColorArray' => array(),
        );

        // game 5
        $data['gameIdArray'][] = 5;
        $data['gameDescriptionArray'][] = 'Game 5';
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
        $data['inactivityArray'][] = "8 days";
        $data['inactivityRawArray'][] = 8*60*60*24;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";

        return array($data, "All game details retrieved successfully.");
    }

    protected function get_interface_response_loadRejectedGames() {
        $data = array(
            'gameIdArray' => array(),
            'gameDescriptionArray' => array(),
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
            'inactivityArray' => array(),
            'playerColorArray' => array(),
            'opponentColorArray' => array(),
        );

        // game 505
        $data['gameIdArray'][] = 505;
        $data['gameDescriptionArray'][] = 'Game 505';
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 3;
        $data['nLossesArray'][] = 2;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "REJECTED";
        $data['statusArray'][] = "REJECTED";
        $data['inactivityArray'][] = "8 days";
        $data['inactivityRawArray'][] = 8*60*60*24;
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";

        return array($data, "All game details retrieved successfully.");
    }

    protected function get_interface_response_loadNextPendingGame($args) {
        if (isset($args['currentGameId'])) {
            $argval = $args['currentGameId'];
        } else {
            $argval = 'noargs';
        }
        return $this->load_json_data_from_file(
            'loadNextPendingGame',
            $argval . '.json'
        );
    }

    protected function get_interface_response_loadActivePlayers($args) {
        return $this->load_json_data_from_file(
            'loadActivePlayers',
            $args['numberOfPlayers'] . '.json'
        );
    }

    protected function get_interface_response_loadButtonData($args) {
        if (isset($args['buttonName'])) {
            $argval = str_replace(' ', '_', $args['buttonName']);
        } else {
            $argval = 'noargs';
        }
        return $this->load_json_data_from_file(
            'loadButtonData',
            $argval . '.json'
        );
    }


    protected function get_interface_response_loadButtonSetData($args) {
        if (isset($args['buttonSet'])) {
            $argval = str_replace(' ', '_', $args['buttonSet']);
        } else {
            $argval = 'noargs';
        }
        return $this->load_json_data_from_file(
            'loadButtonSetData',
            $argval . '.json'
        );
    }

    protected function get_interface_response_loadGameData($args) {
        return $this->load_json_data_from_file(
            'loadGameData',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_countPendingGames() {
        return $this->load_json_data_from_file(
            'countPendingGames',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadPlayerName() {
        return $this->load_json_data_from_file(
            'loadPlayerName',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadPlayerInfo() {
        return $this->load_json_data_from_file(
            'loadPlayerInfo',
            'noargs.json'
        );
    }

    protected function get_interface_response_savePlayerInfo($args) {
        $argval = str_replace(' ', '_', $args['name_irl']);
        return $this->load_json_data_from_file(
            'savePlayerInfo',
            $argval . '.json'
        );
    }

    protected function get_interface_response_loadProfileInfo($args) {
        return $this->load_json_data_from_file(
            'loadProfileInfo',
            $args['playerName'] . '.json'
        );
    }

    protected function get_interface_response_loadPlayerNames() {
        return $this->load_json_data_from_file(
            'loadPlayerNames',
            'noargs.json'
        );
    }

    protected function get_interface_response_submitDieValues($args) {
        return $this->load_json_data_from_file(
            'submitDieValues',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_reactToInitiative($args) {
        return $this->load_json_data_from_file(
            'reactToInitiative',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_reactToAuxiliary($args) {
        return $this->load_json_data_from_file(
            'reactToAuxiliary',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_reactToReserve($args) {
        return $this->load_json_data_from_file(
            'reactToReserve',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_adjustFire($args) {
        return $this->load_json_data_from_file(
            'adjustFire',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_submitChat($args) {
        return $this->load_json_data_from_file(
            'submitChat',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_submitTurn($args) {
        return $this->load_json_data_from_file(
            'submitTurn',
            $args['game'] . '.json'
        );
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

    protected function get_interface_response_reactToNewGame($args) {
        return $this->load_json_data_from_file(
            'reactToNewGame',
            $args['action'] . '.json'
        );
    }

    protected function get_interface_response_dismissGame($args) {
        return $this->load_json_data_from_file(
            'dismissGame',
            $args['gameId'] . '.json'
        );
    }

    ////////////////////////////////////////////////////////////
    // Forum-related methods

    protected function get_interface_response_loadForumOverview() {
        return $this->load_json_data_from_file(
            'loadForumOverview',
            'noargs.json'
        );
        $results = array();

        $boards = array();
        $boards[] = array(
            'boardId' => 1,
            'boardName' => 'Miscellaneous Chatting',
            'boardColor' => '#d0e0f0',
            'threadColor' => '#e7f0f7',
            'description' => 'Any topic that doesn\'t fit anywhere else.',
            'numberOfThreads' => 2,
            'firstNewPostId' => 3,
            'firstNewPostThreadId' => 2,
        );
        $boards[] = array(
            'boardId' => 2,
            'boardName' => 'Features and Bugs',
            'boardColor' => '#f0d0d0',
            'threadColor' => '#f7e7e7',
            'description' =>
                'Feedback on new features that have been added, features ' .
                    'you\'d like to see or bugs you\'ve discovered.',
            'numberOfThreads' => 0,
            'firstNewPostId' => NULL,
            'firstNewPostThreadId' => NULL,
        );

        $results['boards'] = $boards;
        $results['timestamp'] = 1401118756;

        return array($results, 'Forum overview loading succeeded');
    }

    protected function get_interface_response_loadForumBoard($args) {
        return $this->load_json_data_from_file(
            'loadForumBoard',
            $args['boardId'] . '.json'
        );
    }

    protected function get_interface_response_loadForumThread($args) {
        return $this->load_json_data_from_file(
            'loadForumThread',
            $args['threadId'] . '.json'
        );
    }

    protected function get_interface_response_loadNextNewPost() {
        return $this->load_json_data_from_file(
            'loadNextNewPost',
            'noargs.json'
        );
    }

    protected function get_interface_response_markForumRead() {
        return $this->load_json_data_from_file(
            'markForumRead',
            'noargs.json'
        );
    }

    protected function get_interface_response_markForumBoardRead($args) {
        return $this->load_json_data_from_file(
            'markForumBoardRead',
            $args['boardId'] . '.json'
        );
    }

    protected function get_interface_response_markForumThreadRead($args) {
        return $this->load_json_data_from_file(
            'markForumThreadRead',
            $args['threadId'] . '.json'
        );
    }

    protected function get_interface_response_createForumThread($args) {
        return $this->load_json_data_from_file(
            'createForumThread',
            $args['boardId'] . '.json'
        );
    }

    protected function get_interface_response_createForumPost($args) {
        return $this->load_json_data_from_file(
            'createForumPost',
            $args['threadId'] . '.json'
        );
    }

    protected function get_interface_response_editForumPost($args) {
        return $this->load_json_data_from_file(
            'editForumPost',
            $args['postId'] . '.json'
        );
    }

    // End of Forum-related methods
    ////////////////////////////////////////////////////////////

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
            if (FALSE !== array_search($args['type'], $this->untransformedFunctions)) {
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
                if ($retval) {
                    $output = $retval;
                } else {
                    $output = array(
                        'data' => NULL,
                        'status' => 'failed',
                        'message' => 'The arguments provided to dummy_responder were not recognized fake inputs',
                    );
                }
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
