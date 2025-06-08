<?php
/**
 * BMTournament: Contains all generic tournament logic
 *
 * @author: james
 */

/**
 * This class is the parent class for all tournament types
 *
 * @property      int    $tournamentId           Tournament ID in the database
 * @property      string $type                   Type of tournament
 * @property      int    $creatorId              Player ID of tournament creator
 * @property      string $name                   Name of tournament
 * @property      string $description            Description of tournament
 * @property      int    $nPlayers               Number of players
 * @property      int    $autoAdvanceInterval    Number of days before a game is auto-advanced
 * @property      bool   $arePlayersAnonymous    Do we hide players' names until everyone has joined?
 * @property      bool   $doShufflePlayers       Do we initially shuffle the order of players?
 * @property      bool   $areButtonsAnonymous    Do we hide the selected button names initially?
 * @property-read int    $roundNumber            Current round number of tournament
 * @property      int    $gameMaxWins            Max wins per game in this tournament round
 * @property-read array  $playerIdArray          Array of player IDs taking part in the tournament
 * @property-read array  $remainCountArray       Array of remain chances for each player in the tournament
 * @property-read array  $buttonIdArrayArray     Array of arrays of button IDs, indexed by player ID
 * @property      array  $gameIdArrayArray       Array of arrays of game IDs, zero-indexed
 * @property-read BMTournamentState $tournamentState Current tournament state as an enum
 * @property-read array  $gameDataToBeCreatedArray Array of game data to be created by BMInterface at save time
 *
 * @SuppressWarnings(PMD.TooManyFields)
 */
abstract class BMTournament {
    // properties -- all accessible, but written as protected to enable the use of
    //               getters and setters

    /**
     * Tournament ID in the database
     *
     * @var int
     */
    protected $tournamentId;

    /**
     * Type of tournament
     *
     * @var string
     */
    protected $type;

    /**
     * Player ID of tournament creator
     *
     * @var int
     */
    protected $creatorId;

    /**
     * Name of tournament
     *
     * @var string
     */
    protected $name;

    /**
     * Description of tournament
     *
     * @var string
     */
    protected $description;

    /**
     * Number of players
     *
     * @var int
     */
    protected $nPlayers;

    /**
     * Number of days before a game is auto-advanced
     *
     * @var int
     */
    protected $autoAdvanceInterval;

    /**
     * Do we hide players' names initially?
     *
     * @var bool
     */
    protected $arePlayersAnonymous;

    /**
     * Do we initially shuffle the order of players?
     *
     * @var bool
     */
    protected $doShufflePlayers;

    /**
     * Do we hide the selected button names initially?
     *
     * @var bool
     */
    protected $areButtonsAnonymous;

    /**
     * Current round number of the tournament
     *
     * @var int
     */
    protected $roundNumber;

    /**
     * Maximum number of wins for each game in this round of the tournament
     *
     * @var int
     */
    protected $gameMaxWins;

    /**
     * Array of player IDs taking part in the tournament
     *
     * @var array
     */
    protected $playerIdArray;

    /**
     * Array of remain chances for each player in the tournament
     *
     * @var type
     */
    protected $remainCountArray;

    /**
     * Array of arrays of button IDs, indexed by player ID
     *
     * @var array
     */
    protected $buttonIdArrayArray;

    /**
     * Array of arrays of game IDs that are part of the tournament
     *
     * The outer array is indexed by round number (zero-indexed),
     * the inner array by position number (zero-indexed)
     *
     * e.g.  array(
     *         array(456, 457, 458, 459),
     *         array(562, 563)
     *       )
     *
     * @var array
     */
    protected $gameIdArrayArray;

    /**
     * Array of arrays of games that are part of the tournament
     *
     * The outer array is indexed by round number (zero-indexed),
     * the inner array by position number (zero-indexed)
     *
     * e.g.  array(
     *         array(456, 457, 458, 459),
     *         array(562, 563)
     *       )
     *
     * @var array
     */
    protected $gameArrayArray;

    /**
     * Current tournament state as a BMTournamentState enum
     *
     * @var BMTournamentState
     */
    protected $tournamentState;

    /**
     * Array of game data to be created by BMInterface at save time
     *
     * @var array
     */
    protected $gameDataToBeCreatedArray;

    /**
     * Indicates if games are created in the test database
     *
     * @var bool
     */
    public $isTest;


    // methods

    /**
     * The standard factory method that generates all BMTournament* objects
     *
     * @param string $type
     * @return BMTournament*
     */
    public static function create($type = NULL) {
        if ($type) {
            $cname = "BMTournament" . preg_replace('/[^a-zA-Z0-9]/', '', $type);
            if (class_exists($cname)) {
                return $cname::create();
            } else {
                return NULL;
            }
        }

        $class = get_called_class();
        return new $class;
    }

    public function add_player($playerId, $buttonIdArray = NULL) {
        if (!is_int($playerId)) {
            throw new InvalidArgumentException('Player ID must be an integer');
        }

        if ($playerId < 0) {
            throw new InvalidArgumentException('Player ID of player to be added must be non-negative');
        }

        if (count($this->playerIdArray) >= $this->nPlayers) {
            throw new LogicException('Tournament already has enough players');
        }

        if (FALSE !== array_search($playerId, $this->playerIdArray)) {
            // player has already joined tournament
            return;
        }

        $this->playerIdArray[] = $playerId;

        $this->add_button_array($playerId, $buttonIdArray);
    }

    public function add_button_array($playerId, $buttonIdArray) {
        if (!is_array($buttonIdArray)) {
            throw new LogicException('Each player must have selected a button');
        }

        foreach ($buttonIdArray as $buttonId) {
            if (!isset($buttonId)) {
                continue;
            }

            if (!is_int($buttonId)) {
                throw new InvalidArgumentException('Button IDs must be integers');
            }
        }

        $this->buttonIdArrayArray[$playerId] = $buttonIdArray;
    }

    /**
     * This is a generic caller function that calls each
     * do_next_step_*() function, based on the current tournament state.
     */
    public function do_next_step() {
        if (!isset($this->tournamentState)) {
            throw new LogicException('Tournament state must be set.');
        }

        $funcName = 'do_next_step_'.
                    strtolower(BMTournamentState::as_string($this->tournamentState));
        $this->$funcName();
    }

    /**
     * This is a generic caller function that calls each
     * update_tournament_state_*() function, based on the current tournament state.
     */
    public function update_tournament_state() {
        if (!isset($this->tournamentState)) {
            throw new LogicException('Tournament state must be set.');
        }

        $funcName = 'update_tournament_state_'.
                    strtolower(BMTournamentState::as_string($this->tournamentState));
        $this->$funcName();
    }

    /**
     * Perform the logic required at BMTournamentState::START_TOURNAMENT
     */
    protected function do_next_step_start_tournament() {
    }

    /**
     * Update tournament state from BMTournamentState::START_TOURNAMENT if necessary
     */
    protected function update_tournament_state_start_tournament() {
        $this->tournamentState = BMTournamentState::JOIN_TOURNAMENT;
    }

    /**
     * Perform the logic required at BMTournamentState::JOIN_TOURNAMENT
     */
    protected function do_next_step_join_tournament() {
    }

    /**
     * Update tournament state from BMTournamentState::JOIN_TOURNAMENT if necessary
     */
    protected function update_tournament_state_join_tournament() {
        if (count($this->playerIdArray) < $this->nPlayers) {
            return;
        }

        foreach ($this->buttonIdArrayArray as $buttonIdArray) {
            if (empty($buttonIdArray)) {
                return;
            }
        }

        $this->initialiseRemainCountArray();

        $this->tournamentState = BMTournamentState::SHUFFLE_PLAYERS;
    }

    /**
     * Perform the logic required at BMTournamentState::SHUFFLE_PLAYERS
     */
    protected function do_next_step_shuffle_players() {
        if ($this->doShufflePlayers) {
            $playerIdArray = $this->playerIdArray;
            bm_shuffle($playerIdArray);
            $this->playerIdArray = $playerIdArray;
        }
    }

    /**
     * Update tournament state from BMTournamentState::SHUFFLE_PLAYERS if necessary
     */
    protected function update_tournament_state_shuffle_players() {
        $this->tournamentState = BMTournamentState::START_ROUND;
    }

    /**
     * Perform the logic required at BMTournamentState::START_ROUND
     */
    protected function do_next_step_start_round() {
        $this->create_games_for_round($this->roundNumber);
    }

    /**
     * Update tournament state from BMTournamentState::START_ROUND if necessary
     */
    protected function update_tournament_state_start_round() {
        if (array_key_exists($this->roundNumber - 1, $this->gameIdArrayArray)) {
            $this->tournamentState = BMTournamentState::PLAY_GAMES;
        }
    }

    /**
     * Perform the logic required at BMTournamentState::PLAY_GAMES
     */
    protected function do_next_step_play_games() {
    }

    /**
     * Update tournament state from BMTournamentState::PLAY_GAMES if necessary
     */
    protected function update_tournament_state_play_games() {
        if ($this->isWaitingOnAnyAction()) {
            return;
        }

        $this->tournamentState = BMTournamentState::END_ROUND;
    }

    /**
     * Perform the logic required at BMTournamentState::END_ROUND
     */
    protected function do_next_step_end_round() {
    }

    /**
     * Update tournament state from BMTournamentState::END_ROUND if necessary
     */
    protected function update_tournament_state_end_round() {
        $this->update_remainCountArray();

        if ($this->has_tournament_completed()) {
            $this->tournamentState = BMTournamentState::END_TOURNAMENT;
            return;
        }

        $this->roundNumber++;
        $this->tournamentState = BMTournamentState::START_ROUND;
    }

    /**
     * Perform the logic required at BMTournamentState::END_TOURNAMENT
     */
    protected function do_next_step_end_tournament() {
    }

    /**
     * Update tournament state from BMTournamentState::END_TOURNAMENT if necessary
     */
    protected function update_tournament_state_end_tournament() {
    }

    /**
     * Perform the logic required at BMTournamentState::CANCELLED
     */
    protected function do_next_step_cancelled() {
    }

    /**
     * Update tournament state from BMTournamentState::CANCELLED if necessary
     */
    protected function update_tournament_state_cancelled() {
    }

    /**
     * Carry out all automated tournament actions until a player needs to do something
     *
     * The idea here is to call update_tournament_state() and do_next_step() one after
     * another until:
     * (i) the tournament requires a player action, or
     * (ii) the tournament reaches a tournament state that signals that it has ended
     *
     * The variable $tournamentStateBreakpoint is used for debugging purposes only.
     * If used, the tournament will stop as soon as the tournament state becomes
     * the value of $tournamentStateBreakpoint.
     *
     * @param int $tournamentStateBreakpoint
     */
    public function proceed_to_next_user_action($tournamentStateBreakpoint = NULL) {
        $repeatCount = 0;
        $initialTournamentState = $this->tournamentState;
        $this->update_tournament_state();

        if (isset($tournamentStateBreakpoint) &&
            ($tournamentStateBreakpoint == $this->tournamentState) &&
            ($initialTournamentState != $this->tournamentState)) {
            return;
        }

        $this->do_next_step();

        while (!$this->isWaitingOnAnyAction()) {
            $tempTournamentState = $this->tournamentState;
            $this->update_tournament_state();

            if (isset($tournamentStateBreakpoint) &&
                ($tournamentStateBreakpoint == $this->tournamentState)) {
                return;
            }

            $this->do_next_step();

            if ($this->tournamentState >= BMTournamentState::END_TOURNAMENT) {
                return;
            }

            if ($tempTournamentState === $this->tournamentState) {
                $repeatCount++;
            } else {
                $repeatCount = 0;
            }
            if ($repeatCount >= 20) {
                $finalTournamentState = BMTournamentState::as_string($this->tournamentState);
                throw new LogicException(
                    'Infinite loop detected when advancing tournament state. '.
                    "Final tournament state: $finalTournamentState"
                );
            }
        }
    }

    /**
     * Is the tournament waiting on any player actions?
     *
     * @return bool
     */
    protected function isWaitingOnAnyAction() {
        if (count($this->playerIdArray) < $this->nPlayers) {
            return TRUE;
        }

        if (BMTournamentState::PLAY_GAMES == $this->tournamentState) {
            if (!isset($this->gameArrayArray) ||
                !isset($this->gameArrayArray[$this->roundNumber - 1])) {
                return TRUE;
            }

            // check whether all games in this current tournament round are complete
            foreach ($this->gameArrayArray[$this->roundNumber - 1] as $game) {
                if ($game->gameState < BMGameState::END_GAME) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     *  Initialise remainCountArray
     */
    abstract protected function initialiseRemainCountArray();

    /**
     * Create all games for a specific round of the tournament
     *
     * @param int $roundNumber
     */
    abstract protected function create_games_for_round($roundNumber);

    /**
     * Update remainCountArray
     */
    abstract protected function update_remainCountArray();

    /**
     * Determine whether the tournament has completed
     *
     * @return bool
     */
    abstract protected function has_tournament_completed();

    /**
     * Validate the number of players for this specific tournament type
     *
     * @return bool
     */
    public function validate_n_players($nPlayers) {
        return in_array($nPlayers, $this->allowed_n_players());
    }

    /**
     * Specify the number of players required to participate in this specific tournament type
     *
     * @return array
     */
    public function allowed_n_players() {
        return array(4, 8, 16, 32);
    }

    /**
     * Calculate the total number of rounds in the tournament, based on the
     * tournament type and the number of players.
     *
     * This returns a string because of the possibility that there might be
     * an incalculable or infinite number of rounds.
     *
     * @return string
     */
    abstract public function max_round();

    /**
     * Validate buttons chosen by a player
     *
     * @param array $buttonIdArray
     * @return bool
     */
    abstract public function validate_button_choice($buttonIdArray);

    /**
     * Determine whether a player has won
     *
     * @return bool
     */
    public function has_player_won($playerId) {
        return $this->has_tournament_completed() &&
               array_search($playerId, $this->remainingPlayerIdArray());
    }

    /**
     * Array of player IDs remaining in the tournament
     *
     * @var array
     */
    public function remainingPlayerIdArray() {
        $remainingPlayerIdArray = array();

        foreach ($this->remainCountArray as $playerIdx => $remainCount) {
            if ($remainCount > 0) {
                $remainingPlayerIdArray[] = $this->playerIdArray[$playerIdx];
            }
        }

        return $remainingPlayerIdArray;
    }

    // utility methods

    public function __construct(
        $tournamentId = 0
    ) {
        $this->tournamentId = $tournamentId;
        $this->type = '';
        $this->creatorId = -1;
        $this->name = '';
        $this->description = '';
        $this->autoAdvanceInterval = -1;
        $this->arePlayersAnonymous = FALSE;
        $this->doShufflePlayers = TRUE;
        $this->areButtonsAnonymous = FALSE;
        $this->roundNumber = 1;
        $this->gameMaxWins = 3;
        $this->playerIdArray = array();
        $this->remainCountArray = array();
        $this->buttonIdArrayArray = array();
        $this->gameIdArrayArray = array();
        $this->gameArrayArray = array();
        $this->tournamentState = BMTournamentState::START_TOURNAMENT;
        $this->isTest = FALSE;
    }

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (($this instanceof BMTournament) && property_exists($this, $property)) {
            // bypass explicit getter methods for BMTournament child classes
            return $this->$property;
        }

        // support explicit accessor methods
        $funcName = 'get__'.$property;
        if (method_exists($this, $funcName)) {
            return $this->$funcName();
        }

        // support direct access to explicitly defined properties
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    /**
     * Setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        // bypass explicit setter methods for child classes
        if (($this instanceof BMTournament) && property_exists($this, $property)) {
            $this->$property = $value;
            return;
        }

        // support explicit setter methods
        $funcName = 'set__'.$property;
        if (method_exists($this, $funcName)) {
            $this->$funcName($value);
            return;
        }

        // support setting of explicitly defined properties
        if (property_exists($this, $property)) {
            $this->$property = $value;
            return;
        }
    }

    /**
     * Prevent setting the round number
     */
    protected function set__roundNumber() {
        throw new LogicException(
            'roundNumber is altered exclusively via BMTournament->advance_to_next_round().'
        );
    }

    /**
     * Prevent setting the player ID array
     */
    protected function set__playerIdArray() {
        throw new LogicException(
            'playerIdArray is altered exclusively via BMTournament->add_player().'
        );
    }

    /**
     * Prevent setting the button ID array
     */
    protected function set__buttonIdArrayArray() {
        throw new LogicException(
            'buttonIdArrayArray is only altered indirectly.'
        );
    }

    /**
     * Prevent setting the array containing data for games that need to be created
     */
    protected function set__gameDataToBeCreatedArray() {
        throw new LogicException(
            'gameDataToBeCreatedArray is altered exclusively via BMTournament->create_games_for_round().'
        );
    }

    /**
     * Define behaviour of isset()
     *
     * @param string $property
     * @return bool
     */
    public function __isset($property) {
        return isset($this->$property);
    }

    /**
     * Define behaviour of unset()
     *
     * @param string $property
     * @return bool
     */
    public function __unset($property) {
        if (isset($this->$property)) {
            unset($this->$property);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Get the JSON data corresponding to this tournament
     *
     * @return array
     */
    public function getJsonData() {
        $dataArray = array(
            'tournamentId'               => $this->tournamentId,
            'tournamentRoundNumber'      => $this->roundNumber,
            'tournamentState'            => BMTournamentState::as_string($this->tournamentState),
            'type'                       => $this->type,
            'nPlayers'                   => $this->nPlayers,
            'maxWins'                    => $this->gameMaxWins,
            'maxRound'                   => $this->max_round(),
            'description'                => $this->description,
            'creatorDataArray'           => array('creatorId' => $this->creatorId),
            'remainCountArray'           => $this->remainCountArray
        );
        return $dataArray;
    }
}
