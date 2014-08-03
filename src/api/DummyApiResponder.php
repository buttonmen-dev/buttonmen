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

    // constructor
    // * For live invocation:
    //   * start a session (don't use api_core because dummy_responder has no backend)
    // * For test invocation:
    //   * don't start a session
    public function __construct(ApiSpec $spec, $isTest = FALSE) {
        $this->spec = $spec;
        $this->isTest = $isTest;

        if ($this->isTest) {
            $this->jsonFileRoot = BW_PHP_ROOT . "/api/dummy_data/";
        } else {
            session_start();
            $this->jsonFileRoot = "./dummy_data/";
        }
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
        $dummy_users = array(
            'tester1' => 1,
            'tester2' => 2,
            'tester3' => 3);
        $username = $args['username'];
        if (array_key_exists($username, $dummy_users)) {
            $userid = $dummy_users[$username];
            return array(NULL, "$username already exists (id=$userid)");
        }
        return array(array('userName' => $username),
                     'User ' . $username . ' created successfully.  ' .
                     'A verification code has been e-mailed to ' . $username . '@example.com.  ' .
                     'Follow the link in that message to start beating people up!');
    }

    protected function get_interface_response_verifyUser() {
        return array(TRUE, "New user tester1 has been verified.");
    }

    protected function get_interface_response_createGame() {
        // for verisimilitude, choose a game ID of one greater than
        // the number of "existing" games represented in loadGameData
        // and loadActiveGames

        $gameId = 26;
        return array(array('gameId' => $gameId), "Game $gameId created successfully.");
    }

    protected function get_interface_response_joinOpenGame() {
        // join_open_game() does not need to return much data
        return array(TRUE, "");
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
            'challengerButton' => 'Agatha',
            'challengerColor' => '#cccccc',
            'victimButton' => 'Krosp',
            'targetWins' => 3,
        );

        return array(array('games' => $games), "Open games retrieved successfully.");
    }

    protected function get_interface_response_searchGameHistory($args) {
        $games = array();

        if ((!isset($args['status']) || $args['status'] == 'COMPLETE')) {
            $games[] = $this->mock_completed_game($args);
        }

        if ((!isset($args['status']) || $args['status'] == 'ACTIVE')) {
            $games[] = $this->mock_active_game($args);
        }

        $summary = array();
        $summary['matchesFound'] = count($games);
        $summary['earliestStart'] = 1399605464;
        $summary['latestMove'] = 1399691809;
        $summary['gamesWonA'] = count($games);
        $summary['gamesWonB'] = 0;
        $summary['gamesCompleted'] = 1;

        $data = array(
            'games' => $games,
            'summary' => $summary
        );

        return array($data, "Search results retrieved successfully.");
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

    protected function get_interface_response_loadActiveGames() {
        // Use the same fake games here which were described in loadGameData
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
            'playerColorArray' => array(),
            'opponentColorArray' => array(),
        );

        for ($gameIdx = 1; $gameIdx <= 24; $gameIdx++) {
            $funcname = 'add_active_game_data_'.$gameIdx;
            $this->$funcname($data);
        }

        return array($data, "All game details retrieved successfully.");
    }

    protected function add_active_game_data_1(&$data) {
        $data['gameIdArray'][] = 1;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_2(&$data) {
        $data['gameIdArray'][] = 2;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_3(&$data) {
        $data['gameIdArray'][] = 3;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_4(&$data) {
        $data['gameIdArray'][] = 4;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_5() {
        // fake game 5 is completed
    }

    protected function add_active_game_data_6(&$data) {
        $data['gameIdArray'][] = 6;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_7(&$data) {
        $data['gameIdArray'][] = 7;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_8(&$data) {
        $data['gameIdArray'][] = 8;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_9(&$data) {
        $data['gameIdArray'][] = 9;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_14(&$data) {
        $data['gameIdArray'][] = 14;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_15() {
        // tester1 is not a participant in fake game 15
    }

    protected function add_active_game_data_16(&$data) {
        $data['gameIdArray'][] = 16;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_17(&$data) {
        $data['gameIdArray'][] = 17;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_18() {
        // tester1 is not a participant in fake game 18
    }

    protected function add_active_game_data_19(&$data) {
        $data['gameIdArray'][] = 19;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_23(&$data) {
        $data['gameIdArray'][] = 23;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";
    }

    protected function add_active_game_data_24() {
        // tester1 is not a participant in fake game 24
    }

    protected function get_interface_response_loadCompletedGames() {
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
            'playerColorArray' => array(),
            'opponentColorArray' => array(),
        );

        // game 5
        $data['gameIdArray'][] = 5;
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
        $data['playerColorArray'][] = "#dd99dd";
        $data['opponentColorArray'][] = "#ddffdd";

        return array($data, "All game details retrieved successfully.");
    }

    protected function get_interface_response_loadNextPendingGame($args) {
        // Assume that game IDs 7 is the next one waiting for tester1,
        // and that 4 is next after that if 7 is being skipped
        if (isset($args['currentGameId']) && $args['currentGameId'] == 7) {
            $data = array('gameId' => 4);
        } else {
            $data = array('gameId' => 7);
        }
        return array($data, 'Next game ID retrieved successfully.');
    }

    protected function get_interface_response_loadActivePlayers() {
        $players = array(
            array(
                'playerName' => 'responder003',
                'idleness' => '0 seconds',
            ),
            array(
                'playerName' => 'responder004',
                'idleness' => '12 minutes',
            ),
        );

        return array(array('players' => $players),
            'Active players retrieved successfully.');
    }

    protected function get_interface_response_loadButtonData() {
        $data = array();

        // a button with no special skills
        $data[] = array(
            'buttonName' => "Avis",
            'recipe' => "(4) (4) (10) (12) (X)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "Soldiers",
            'dieSkills' => array(),
            'isTournamentLegal' => TRUE,
        );

        // a button with an unimplemented skill
        $data[] = array(
            'buttonName' => "Zeppo",
            'recipe' => "(4) (12) (20) (X)!",
            'hasUnimplementedSkill' => TRUE,
            'buttonSet' => "1999 Rare / Promo",
            'dieSkills' => array(),
            'isTournamentLegal' => TRUE,
        );

        // a button with four dice and some implemented skills
        $data[] = array(
            'buttonName' => "Jellybean",
            'recipe' => "p(20) s(20) (V) (X)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "BROM",
            'dieSkills' => array("Poison", "Shadow"),
            'isTournamentLegal' => TRUE,
        );

        // Buck Godot
        $data[] = array(
            'buttonName' => "Buck Godot",
            'recipe' => "(6,6) (10) (12) (20) (W,W)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "Studio Foglio",
            'dieSkills' => array(),
            'isTournamentLegal' => TRUE,
        );

        // Von Pinn
        $data[] = array(
            'buttonName' => "Von Pinn",
            'recipe' => "(4) p(6,6) (10) (20) (W)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "Studio Foglio",
            'dieSkills' => array("Poison"),
            'isTournamentLegal' => TRUE,
        );

        // Crab: a button with focus dice
        $data[] = array(
            'buttonName' => "Crab",
            'recipe' => "(8) (10) (12) f(20) f(20)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "Legend of the Five Rings",
            'dieSkills' => array("Focus"),
            'isTournamentLegal' => TRUE,
        );

        // John Kovalic: a button with chance dice
        $data[] = array(
            'buttonName' => "John Kovalic",
            'recipe' => "(6) c(6) (10) (12) c(20)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "Yoyodyne",
            'dieSkills' => array("Chance"),
            'isTournamentLegal' => TRUE,
        );

        $data[] = array(
        // King Arthur: a button with an auxiliary die
            'buttonName' => "King Arthur",
            'recipe' => "(8) (8) (10) (20) (X) +(20)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "Buttonlords",
            'dieSkills' => array("Auxiliary"),
            'isTournamentLegal' => TRUE,
        );

        // Cammy Neko: a button with reserve dice
        $data[] = array(
            'buttonName' => "Cammy Neko",
            'recipe' => "(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "Geekz",
            'dieSkills' => array("Reserve"),
            'isTournamentLegal' => TRUE,
        );

        // Apples: a button with option dice
        $data[] = array(
            'buttonName' => "Apples",
            'recipe' => "(8) (8) (2/12) (8/16) (20/24)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "Chicagoland Gamers Conclave",
            'dieSkills' => array(),
            'isTournamentLegal' => TRUE,
        );

        // CactusJack: a button with swing and option dice (and shadow and speed skills)
        $data[] = array(
            'buttonName' => "CactusJack",
            'recipe' => "z(8/12) (4/16) s(6/10) z(X) s(U)",
            'hasUnimplementedSkill' => FALSE,
            'buttonSet' => "Classic Fanatics",
            'dieSkills' => array("Shadow", "Speed"),
            'isTournamentLegal' => FALSE,
        );

        return array($data, "All button names retrieved successfully.");
    }

    protected function get_interface_response_loadGameData($args) {
        // The dummy loadGameData returns one of a number of
        // sets of dummy game data, for general test use.
        // Specify which one you want using the game number:
        //   1: a newly-created game, waiting for both players to set swing dice
        //   2: new game in which the active player has set swing dice
        //   3: game in which it is the current player's turn to attack
        //   4: game in which it is the opponent's turn to attack
        //   5: game which has been completed
        //   7: game in which focus dice can be used to respond to initiative
        //   8: game in which chance dice can be used to respond to initiative
        //   9: game in which opponent can use chance dice to respond to initiative
        //  10: game in "specify dice" state in which active player is not a participant
        //  11: game in "start turn" state in which active player is not a participant
        //  12: game in "react to initiative" state in which active player is not a participant
        //  13: game in which active player can decide whether to choose auxiliary die
        //  14: game in which it is the opponents turn to choose auxiliary die
        //  15: game in "choose auxiliary" state in which active player is not a participant
        //  16: game in which active player can decide whether to add reserve die
        //  17: game in which opponent can decide whether to add reserve die
        //  18: game in "choose reserve" state in which active player is not a participant
        //  19: game in which active player can choose option die values
        //  20: game in which active player can turn down fire dice

        $data = NULL;

        if ($args['game'] <= 24) {
            $data = $this->load_json_data_from_file(
                'loadGameData',
                $args['game'] . '.json'
            );
        }

        if ($data) {
            if (isset($args['logEntryLimit']) && $args['logEntryLimit'] > 0) {
                $data['gameActionLog'] =
                    array_slice($data['gameActionLog'], 0, $args['logEntryLimit']);
                $data['gameChatLog'] =
                    array_slice($data['gameChatLog'], 0, $args['logEntryLimit']);
            }

            $timestamp = strtotime('now');
            $data['timestamp'] = $timestamp;
            return array($data, "Loaded data for game " . $args['game']);
        }
        return array(NULL, "Game does not exist.");
    }

    protected function get_interface_response_countPendingGames() {
        return array(array('count' => 0), 'Pending game count succeeded.');
    }

    protected function get_interface_response_loadPlayerName() {
        return array(array('userName' => 'tester1'), NULL);
    }

    protected function get_interface_response_loadPlayerInfo() {
        $playerInfoArray = array('id' => 1,
                                'name_ingame' => 'tester1',
                                'name_irl' => '',
                                'email' => 'tester1@example.com',
                                'is_email_public' => FALSE,
                                'status' => 'active',
                                'dob_month' => 0,
                                'dob_day' => 0,
                                'gender' => '',
                                'image_size' => NULL,
                                'autopass' => TRUE,
                                'uses_gravatar' => FALSE,
                                'monitor_redirects_to_game' => FALSE,
                                'monitor_redirects_to_forum' => FALSE,
                                'automatically_monitor' => FALSE,
                                'comment' => NULL,
                                'favorite_button' => NULL,
                                'favorite_buttonset' => NULL,
                                'last_action_time' => 0,
                                'last_access_time' => 0,
                                'creation_time' => 1388193734,
                                'fanatic_button_id' => 0,
                                'n_games_won' => 0,
                                'n_games_lost' => 0,
                               );

        return array(array('user_prefs' => $playerInfoArray), NULL);
    }

    protected function get_interface_response_savePlayerInfo() {
        return array(array('playerId' => 1), 'Player info updated successfully.');
    }

    protected function get_interface_response_loadProfileInfo($args) {
        $profileInfoArray = array(
            'id' => 3,
            'name_ingame' => $args['playerName'],
            'name_irl' => 'Test User',
            'email' => NULL,
            'email_hash' => '55502f40dc8b7c769880b10874abc9d0',
            'is_email_public' => FALSE,
            'dob_month' => 2,
            'dob_day' => 29,
            'gender' => '',
            'image_size' => NULL,
            'uses_gravatar' => FALSE,
            'comment' => '',
            'favorite_button' => NULL,
            'favorite_buttonset' => NULL,
            'last_access_time' => 0,
            'creation_time' => 0,
            'fanatic_button_id' => 0,
            'n_games_won' => 0,
            'n_games_lost' => 0,
        );

        return array(array('profile_info' => $profileInfoArray), NULL);
    }

    protected function get_interface_response_loadPlayerNames() {
        $data = array(
            'nameArray' => array(),
            'statusArray' => array(),
        );

        // three test players exist and are all active
        $data['nameArray'][] = 'tester1';
        $data['statusArray'][] = 'active';
        $data['nameArray'][] = 'tester2';
        $data['statusArray'][] = 'active';
        $data['nameArray'][] = 'tester3';
        $data['statusArray'][] = 'active';

        return array($data, "Names retrieved successfully.");
    }

    protected function get_interface_response_submitDieValues() {
        return array(TRUE, 'Successfully set die sizes');
    }

    protected function get_interface_response_reactToInitiative() {
        return array(array('gainedInitiative' => TRUE),
                     'Successfully gained initiative');
    }

    protected function get_interface_response_reactToAuxiliary() {
        return array(TRUE, 'Auxiliary die chosen successfully');
    }

    protected function get_interface_response_reactToReserve() {
        return array(TRUE, 'Reserve die chosen successfully');
    }

    protected function get_interface_response_adjustFire() {
        return array(TRUE, 'Successfully completed attack by turning down fire dice');
    }

    protected function get_interface_response_submitChat($args) {
        if (array_key_exists('edit', $args)) {
            if ($args['chat']) {
                return array(TRUE, 'Updated previous game message');
            } else {
                return array(TRUE, 'Deleted previous game message');
            }
        } else {
            if ($args['chat']) {
                return array(TRUE, 'Added game message');
            } else {
                return array(FALSE, 'No game message specified');
            }
        }
    }

    protected function get_interface_response_submitTurn() {
        return array(TRUE, 'Dummy turn submission accepted');
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

    protected function get_interface_response_dismissGame() {
        return array(TRUE, 'Dismissing game succeeded');
    }

    ////////////////////////////////////////////////////////////
    // Forum-related methods

    protected function get_interface_response_loadForumOverview() {
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

    protected function get_interface_response_loadForumBoard() {
        $results = array();
        $results['boardId'] = 1;
        $results['boardName'] = 'Miscellaneous Chatting';
        $results['boardColor'] = '#d0e0f0';
        $results['threadColor'] = '#e7f0f7';
        $results['description'] = 'Any topic that doesn\'t fit anywhere else.';

        $threads = array();
        $threads[] = array(
            'threadId' => 1,
            'threadTitle' => 'Who likes ice cream?',
            'numberOfPosts' => 2,
            'originalPosterName' => 'responder003',
            'originalCreationTime' => 1401055337,
            'latestPosterName' => 'responder004',
            'latestLastUpdateTime' => 1401055397,
            'firstNewPostId' => 2,
        );
        $threads[] = array(
            'threadId' => 2,
            'threadTitle' => 'Welcome to Button Men',
            'numberOfPosts' => 1,
            'originalPosterName' => 'responder003',
            'originalCreationTime' => 1401055367,
            'latestPosterName' => 'responder003',
            'latestLastUpdateTime' => 1401055367,
            'firstNewPostId' => NULL,
        );

        $results['threads'] = $threads;
        $results['timestamp'] = 1401118756;

        return array($results, 'Forum board loading succeeded');
    }

    protected function get_interface_response_loadForumThread($args) {
        $results = array();
        $results['threadId'] = 1;
        $results['threadTitle'] = 'Who likes ice cream?';
        $results['boardId'] = 1;
        $results['boardName'] = 'Miscellaneous Chatting';
        $results['boardColor'] = '#d0e0f0';
        $results['boardThreadColor'] = '#e7f0f7';
        if (isset($args['currentPostId'])) {
            $results['currentPostId'] = (int)$args['currentPostId'];
        } else {
            $results['currentPostId'] = NULL;
        }


        $posts = array();
        $posts[] = array(
            'postId' => 1,
            'posterName' => 'responder003',
            'posterColor' => '#cccccc',
            'creationTime' => 1401055337,
            'lastUpdateTime' => 1401055337,
            'isNew' => FALSE,
            'body' => 'I can\'t be the only one!',
            'deleted' => FALSE,
        );
        $posts[] = array(
            'postId' => 2,
            'posterName' => 'responder004',
            'posterColor' => '#cccccc',
            'creationTime' => 1401055397,
            'lastUpdateTime' => 1401055397,
            'isNew' => TRUE,
            'body' => 'Hey, wow, I do too!',
            'deleted' => FALSE,
        );

        $results['posts'] = $posts;
        $results['timestamp'] = 1401118756;

        return array($results, 'Forum thread loading succeeded');
    }

    protected function get_interface_response_loadNextNewPost() {
        $results = array();
        $results['nextNewPostId'] = 3;
        $results['nextNewPostThreadId'] = 2;
        return array($results, 'Checked new forum posts successfully');
    }

    protected function get_interface_response_markForumRead() {
        $otherResults = $this->get_interface_response_loadForumOverview();
        $results = $otherResults[0];
        return array($results, 'Forum board marked read successfully');
    }

    protected function get_interface_response_markForumBoardRead() {
        $otherResults = $this->get_interface_response_loadForumOverview();
        $results = $otherResults[0];
        return array($results, 'Forum board marked read successfully');
    }

    protected function get_interface_response_markForumThreadRead() {
        $otherResults = $this->get_interface_response_loadForumBoard(NULL);
        $results = $otherResults[0];
        return array($results, 'Forum thread marked read successfully');
    }

    protected function get_interface_response_createForumThread() {
        $otherResults = $this->get_interface_response_loadForumThread(
            array()
        );
        $results = $otherResults[0];
        return array($results, 'Forum thread created successfully');
    }

    protected function get_interface_response_createForumPost() {
        $otherResults = $this->get_interface_response_loadForumThread(
            array('currentPostId' => 2)
        );
        $results = $otherResults[0];
        return array($results, 'Forum post created successfully');
    }

    protected function get_interface_response_editForumPost() {
        $otherResults = $this->get_interface_response_loadForumThread(
            array('currentPostId' => 2)
        );
        $results = $otherResults[0];
        return array($results, 'Forum post edited successfully');
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
