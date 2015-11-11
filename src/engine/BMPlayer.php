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
 * @property      BMGame   $ownerObject            BMGame that owns this BMPlayer object
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
     * BMGame that owns this BMPlayer object
     *
     * @var BMGame
     */
    protected $ownerObject;


    // setter methods
    /**
     * Set ID
     *
     * @param int|null $value
     */
    protected function set__playerId($value) {
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
        $this->waitingOnAction = FALSE;
        if (strlen($buttonRecipe) > 0) {
            $this->set__button(new BMButton);
            $this->button->load($buttonRecipe);
        }
        $this->isPrevRoundWinner = FALSE;
    }

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        $funcName = 'get__'.$property;
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
        $funcName = 'set__'.$property;
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
     * @return boolean
     */
    public function __isset($property) {
        return isset($this->$property);
    }

    /**
     * Define behaviour of unset()
     *
     * @param string $property
     * @return boolean
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
