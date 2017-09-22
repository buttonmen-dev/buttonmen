<?php

/**
 * BMPlayer: game-relevant attributes of a player
 *
 * @author james
 */

/**
 * This class contains all the player-related attributes of a game
 *
 * @property      int      $playerId               Player ID number in the database
 * @property      int      $position               Position index of this player in this game
 * @property      BMButton $button                 Button used by this player in this game
 * @property      array    $activeDieArray         Array of active dice owned by this player
 * @property      array    $capturedDieArray       Array of dice captured by this player
 * @property      array    $outOfPlayDieArray      Array of dice out of play
 * @property      bool     $waitingOnAction        Does this player need to perform an action?
 * @property      bool     $isPrevRoundWinner      Has this player just won the previous round?
 * @property      float    $roundScore             Current points score in this round
 * @property      array    $gameScoreArray         Number of games W/L/D
 * @property      array    $swingRequestArray      Swing requests
 * @property      array    $swingValueArray        Swing values
 * @property      array    $prevSwingValueArray    Swing values for previous round
 * @property      array    $optRequestArray        Option requests
 * @property      array    $optValueArray          Option values for current round
 * @property      array    $prevOptValueArray      Option values for previous round
 * @property      bool     $autopass               Has player enabled autopass?
 * @property      bool     $fireOvershooting       Has player enabled fire overshooting?
 * @property      bool     $hasPlayerAcceptedGame  Has player accepted this game?
 * @property      bool     $hasPlayerDismissedGame Has player dismissed this game?
 * @property      bool     $isButtonChoiceRandom   Was button chosen at random?
 * @property      int      $lastActionTime         Time of last action
 * @property      BMGame   $ownerObject            BMGame that owns this BMPlayer object
 * @property      bool     $isOnVacation           Is player on vacation? (Used by BMInterface only)
 * @property      bool     $isChatPrivate          Has player set chat to private for this game?
 *
 * @SuppressWarnings(PMD.TooManyFields)
 */
class BMPlayer {
    // properties -- all accessible, but written as protected to enable the use of
    //               getters and setters

    /**
     * Player ID number in the database
     *
     * @var int
     */
    protected $playerId;

    /**
     * Position index of this player in this game
     *
     * @var int
     */
    protected $position;

    /**
     * Button used by the player in this game
     *
     * @var BMButton
     */
    protected $button;

    /**
     * Array of active dice owned by this player
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $activeDieArray;

    /**
     * Array of dice captured by this player
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $capturedDieArray;

    /**
     * Array of dice out of play, but nominally belonging to this player
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $outOfPlayDieArray;

    /**
     * Boolean specifying if this player needs to perform an action at this time
     *
     * @var bool
     */
    protected $waitingOnAction;

    /**
     * Boolean specifying if this player won the previous round
     *
     * @var bool
     */
    protected $isPrevRoundWinner;

    /**
     * Current round score
     *
     * @var float
     */
    protected $roundScore;

    /**
     * Number of games W/L/D
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $gameScoreArray;

    /**
     * Array containing swing value requests
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $swingRequestArray;

    /**
     * Array of arrays containing chosen swing values
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $swingValueArray;

    /**
     * Array containing chosen swing values from last round
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $prevSwingValueArray;

    /**
     * Array containing option value requests
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $optRequestArray;

    /**
     * Array containing chosen option values
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $optValueArray;

    /**
     * Array containing chosen option values from last round
     *
     * This is deliberately public, since PHP doesn't allow proper access to
     * the contents of encapsulated arrays.
     *
     * @var array
     */
    public $prevOptValueArray;

    /**
     * Has player enabled autopass?
     *
     * @var bool
     */
    protected $autopass;

    /**
     * Has player enabled fire overshooting?
     *
     * @var bool
     */
    protected $fireOvershooting;

    /**
     * Used by the database to record whether this player has accepted this game
     *
     * @var bool
     */
    protected $hasPlayerAcceptedGame;

    /**
     * Used by the database to record whether this player has dismissed this game
     *
     * @var bool
     */
    protected $hasPlayerDismissedGame;

    /**
     * Used by the database to record whether the choice of the
     * button was random or not
     *
     * @var bool
     */
    protected $isButtonChoiceRandom;

    /**
     * The last time that this player performed an action
     *
     * @var int
     */
    public $lastActionTime;

    /**
     * BMGame that owns this BMPlayer object
     *
     * @var BMGame
     */
    protected $ownerObject;

    /**
     * Is current player on vacation? Currently only used by BMInterface.
     *
     * @var bool
     */
    protected $isOnVacation;

    /**
     * Has current player set chat to private? Currently only used by BMInterface*.
     *
     * @var bool
     */
    protected $isChatPrivate;

    /**
     * Find indices of active dice that do not have reserve
     *
     * @return array
     */
    public function die_indices_without_reserve() {
        if (empty($this->activeDieArray)) {
            return array();
        }

        $dieIndicesWithoutReserve = array();

        foreach ($this->activeDieArray as $dieIdx => $die) {
            if (!$die->has_skill('Reserve')) {
                $dieIndicesWithoutReserve[] = $dieIdx;
            }
        }

        return($dieIndicesWithoutReserve);
    }


    // setter methods
    /**
     * Set ID
     *
     * @param int|null $value
     */
    protected function set__playerId($value) {
        if (!is_null($value) && !is_int($value)) {
            throw new InvalidArgumentException('Player ID must be an integer or null');
        }

        if (!is_null($value)) {
            $this->playerId = intval($value);
        }
    }

    /**
     * Set position
     *
     * @param int|null $value
     */
    protected function set__position($value) {
        if (!is_null($value) && !is_int($value)) {
            throw new InvalidArgumentException('Player position must be an integer or null');
        }

        if (!is_null($value)) {
            $this->position = intval($value);
        }
    }

    /**
     * Set button
     *
     * @param BMButton $value
     */
    protected function set__button($value) {
        if (!is_null($value) && !($value instanceof BMButton)) {
            throw new InvalidArgumentException('Button must be a BMButton object');
        }

        $this->button = $value;
        if (!is_null($value)) {
            $this->button->ownerObject = $this->ownerObject;
            $this->button->playerIdx = $this->position;
        }
    }

    /**
     * Set waitingOnAction
     *
     * @param bool $value
     */
    protected function set__waitingOnAction($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('waitingOnAction must be a boolean');
        }

        $this->waitingOnAction = $value;
    }

    /**
     * Set isPrevRoundWinner
     *
     * @param bool $value
     */
    protected function set__isPrevRoundWinner($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('isPrevRoundWinner must be a boolean');
        }

        $this->isPrevRoundWinner = $value;
    }

    /**
     * Prevents round score from being set directly
     */
    protected function set__roundScore() {
        throw new LogicException('Round score cannot be set directly');
    }

    /**
     * Get the current round score for this player
     *
     * @return int|NULL
     */
    protected function get__roundScore() {
        if ($this->ownerObject->gameState <= BMGameState::SPECIFY_DICE) {
            return NULL;
        }

        $roundScoreX10 = 0;
        $activeDieScoreX10 = 0;

        foreach ($this->activeDieArray as $activeDie) {
            $activeDieScoreX10 += $activeDie->get_scoreValueTimesTen();
        }
        $roundScoreX10 = $activeDieScoreX10;

        if (!empty($this->capturedDieArray)) {
            $capturedDieScoreX10 = 0;
            foreach ($this->capturedDieArray as $capturedDie) {
                $capturedDieScoreX10 += $capturedDie->get_scoreValueTimesTen();
            }
            $roundScoreX10 += $capturedDieScoreX10;
        }

        return $roundScoreX10 / 10;
    }

    /**
     * Set gameScoreArray
     *
     * @param array $value
     */
    public function set_gameScoreArray($value) {
        if (!is_array($value)) {
            throw new InvalidArgumentException('gameScoreArray must be an array');
        }

        // check whether there are three inputs and they are all positive
        if ((3 != count($value)) || min($value) < 0) {
            throw new InvalidArgumentException(
                'Invalid W/L/T array provided.'
            );
        }

        if (array_key_exists('W', $value) &&
            array_key_exists('L', $value) &&
            array_key_exists('D', $value)) {
            $this->gameScoreArray = array('W' => (int)$value['W'],
                                          'L' => (int)$value['L'],
                                          'D' => (int)$value['D']);
        } else {
            $this->gameScoreArray = array('W' => (int)$value[0],
                                          'L' => (int)$value[1],
                                          'D' => (int)$value[2]);
        }
    }

    /**
     * Set autopass
     *
     * @param bool $value
     */
    protected function set__autopass($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('autopass must be a boolean');
        }

        $this->autopass = $value;
    }

    /**
     * Set fire overshooting
     *
     * @param bool $value
     */
    protected function set__fireOvershooting($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('fire overshooting must be a boolean');
        }

        $this->fireOvershooting = $value;
    }

    /**
     * Set hasPlayerAcceptedGame
     *
     * @param bool $value
     */
    protected function set__hasPlayerAcceptedGame($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('hasPlayerAcceptedGame must be a boolean');
        }

        $this->hasPlayerAcceptedGame = $value;
    }

    /**
     * Set hasPlayerDismissedGame
     *
     * @param bool $value
     */
    protected function set__hasPlayerDismissedGame($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('hasPlayerDismissedGame must be a boolean');
        }

        $this->hasPlayerDismissedGame = $value;
    }

    /**
     * Set ownerObject
     *
     * @param BMGame $value
     */
    protected function set__ownerObject($value) {
        if (!($value instanceof BMGame)) {
            throw new InvalidArgumentException('BMPlayer must be owned by a BMGame');
        }

        $this->ownerObject = $value;

        if ($this->button instanceof BMButton) {
            $this->button->ownerObject = $value;
            $this->button->playerIdx = $this->position;
        }
    }

    /**
     * Set isOnVacation
     *
     * @param bool $value
     */
    protected function set__isOnVacation($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('isOnVacation must be a boolean');
        }

        $this->isOnVacation = $value;
    }

    /**
     * Set isChatPrivate
     *
     * @param bool $value
     */
    protected function set__isChatPrivate($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('isChatPrivate must be a boolean');
        }

        $this->isChatPrivate = $value;
    }

    // utility methods
    /**
     * Constructor
     *
     * @param int    $playerId
     * @param string $buttonRecipe
     * @param int    $position
     */
    public function __construct(
        $playerId = NULL,
        $buttonRecipe = '',
        $position = NULL
    ) {
        $this->set__playerId($playerId);
        $this->set__position($position);
        $this->activeDieArray = NULL;
        $this->capturedDieArray = NULL;
        $this->outOfPlayDieArray = NULL;
        $this->waitingOnAction = FALSE;
        $this->button = NULL;
        if (strlen($buttonRecipe) > 0) {
            $this->set__button(new BMButton);
            $this->button->load($buttonRecipe);
        }
        $this->set_gameScoreArray(array('W' => 0, 'L' => 0, 'D' => 0));
        $this->isPrevRoundWinner = FALSE;
        $this->swingRequestArray = array();
        $this->swingValueArray = array();
        $this->prevSwingValueArray = array();
        $this->optRequestArray = array();
        $this->optValueArray = array();
        $this->prevOptValueArray = array();
        $this->autopass = FALSE;
        $this->fireOvershooting = FALSE;
        $this->hasPlayerAcceptedGame = FALSE;
        $this->hasPlayerDismissedGame = FALSE;
        $this->isButtonChoiceRandom = FALSE;
        $this->lastActionTime = NULL;
    }

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        $funcName = 'get__' . $property;
        if (method_exists($this, $funcName)) {
            return $this->$funcName();
        } elseif (property_exists($this, $property)) {
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
        $funcName = 'set__' . $property;
        if (method_exists($this, $funcName)) {
            $this->$funcName($value);
        } else {
            $this->$property = $value;
        }
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
}
