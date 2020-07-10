<?php
/**
 * BMDieSwing: Code specific to swing dice
 *
 * @author Julian
 */

/**
 * This class contains all the logic to do with requesting and setting swing values
 *
 * @property      char  $swingType         Swing type
 * @property      int   $swingValue        Swing value
 * @property      int   $swingMax          Maximum possible value of this swing type
 * @property      int   $swingMin          Minimum possible value of this swing type
 * @property-read bool  $needsSwingValue   Flag indicating if a swing value is still needed
 * @property-read bool  $valueRequested    Flag indicating if a swing request has been sent to the owning BMGame
 */
class BMDieSwing extends BMDie {
    /**
     * Swing type
     *
     * @var char
     */
    public $swingType;

    /**
     * Swing value
     *
     * This is ALWAYS the value chosen by the player.
     *
     * @var int
     */
    public $swingValue;

    /**
     * Maximum possible value of this swing type
     *
     * @var int
     */
    public $swingMax;

    /**
     * Minimum possible value of this swing type
     *
     * @var int
     */
    public $swingMin;

    /**
     * Flag indicating if a swing value is still needed
     *
     * @var bool
     */
    protected $needsSwingValue = TRUE;

    /**
     * Flag indicating if a swing request has been sent to the owning BMGame
     *
     * @var bool
     */
    protected $valueRequested = FALSE;

    /**
     * Swing ranges for all swing types
     *
     * Don't really like putting data in the code, but where else
     * should it go?
     *
     * Should be a constant, but that isn't allowed. Instead, we wrap
     * it in a method
     *
     * @var array
     */
    public static $swingRanges = array(
        "R" => array(2, 16),
        "S" => array(6, 20),
        "T" => array(2, 12),
        "U" => array(8, 30),
        "V" => array(6, 12),
        "W" => array(4, 12),
        "X" => array(4, 20),
        "Y" => array(1, 20),
        "Z" => array(4, 30));

    /**
     * Swing range for a specified swing type
     *
     * @param char $type
     * @return array
     */
    public static function swing_range($type) {
        if (array_key_exists($type, self::$swingRanges)) {
            return self::$swingRanges[$type];
        }
        return NULL;
    }

    /**
     * Set the swing type for the BMDieSwing, and add die skills
     *
     * Hackish: the caller can specify each skill as either a plain
     * value, "skill", or a key/value pair "ClassName" => "skill",
     * where the key is the class name which implements that skill.
     * This is only for use by callers outside of engine (e.g.
     * testing), and should never be used for the default BMSkill*
     * set of skills.
     *
     * @param char $type
     * @param array $skills
     */
    public function init($type, array $skills = NULL) {
        $this->min = 1;

        $this->divisor = 1;
        $this->remainder = 0;

        $this->needsSwingValue = TRUE;
        $this->valueRequested = FALSE;

        $this->swingType = $type;

        $range = $this->swing_range($type);
        if (is_null($range)) {
            throw new UnexpectedValueException("Invalid swing type: $type");
        }
        $this->swingMin = $range[0];
        $this->swingMax = $range[1];

        $this->add_multiple_skills($skills);
    }

    /**
     * Create a BMDieSwing with a specified swing type, then
     * add skills to the die.
     *
     * @param string $recipe
     * @param array $skills
     * @return BMDieSwing
     */
    public static function create($recipe, array $skills = NULL) {

        if (!is_string($recipe) || strlen($recipe) != 1 ||
            ord("R") > ord($recipe) || ord($recipe) > ord("Z")) {
            throw new UnexpectedValueException("Invalid recipe: $recipe");
        }

        $die = new BMDieSwing;

        $die->init($recipe, $skills);

        return $die;
    }

    /**
     * Wakes up a die from its container to be used in a game.
     * Does not roll the die.
     *
     * Clones the die and returns the clone.
     *
     * @param bool $forceSwingRequest
     */
    public function activate($forceSwingRequest = FALSE) {
        $newDie = clone $this;

        if (!$this->does_skip_swing_request() || $forceSwingRequest) {
            // The clone is the one going into the game, so it's the one
            // that needs a swing value to be set.
            $this->ownerObject->request_swing_values(
                $newDie,
                $newDie->swingType,
                $newDie->playerIdx
            );
            $newDie->valueRequested = TRUE;
        }

        $this->ownerObject->add_die($newDie);
    }

    /**
     * Roll die
     *
     * @param bool $isTriggeredByAttack
     * @param bool $isSubdie
     */
    public function roll($isTriggeredByAttack = FALSE, $isSubdie = FALSE) {
        if ($this->needsSwingValue && !isset($this->max)) {
            if (!$this->valueRequested) {
                $this->ownerObject->request_swing_values(
                    $this,
                    $this->swingType,
                    $this->playerIdx
                );
                $this->valueRequested = TRUE;
            }
        } else {
            parent::roll($isTriggeredByAttack, $isSubdie);
        }
    }

    /**
     * Print long description
     *
     * @param bool $isValueRequired
     * @return string
     */
    public function describe($isValueRequired = FALSE) {
        if (!is_bool($isValueRequired)) {
            throw new InvalidArgumentException('isValueRequired must be boolean');
        }

        $skillStr = $this->skill_string();

        $moodStr = '';
        if ($this->has_skill('Mad')) {
            $moodStr = ' Mad';
        } elseif ($this->has_skill('Mood')) {
            $moodStr = ' Mood';
        }

        $turboStr = '';
        if ($this->has_skill('Turbo')) {
            $turboStr = 'Turbo ';
        }

        $sideStr = '';
        if (isset($this->max)) {
            $sideStr = " (with {$this->max} side";
            if ($this->max != 1) {
                $sideStr .= 's';
            }
            $sideStr .= ')';
        }

        $valueStr = '';
        if ($isValueRequired && isset($this->value)) {
            $valueStr = " showing {$this->value}";
        }

        $result = "{$skillStr}{$turboStr}{$this->swingType}{$moodStr}" .
                  " Swing Die{$sideStr}{$valueStr}";

        return $result;
    }

    /**
     * Get the skill string for this die
     *
     * @return string
     */
    protected function skill_string() {
        $skillStr = '';
        if (count($this->skillList) > 0) {
            foreach (array_keys($this->skillList) as $skill) {
                if (('Mood' != $skill) && ('Mad' != $skill) && 'Turbo' != $skill) {
                    $skillStr .= "$skill ";
                }
            }
        }

        return $skillStr;
    }

    /**
     * Try to set swing value for this BMDieSwing from an array of all swing values
     *
     * @param array $swingList
     * @return bool
     */
    public function set_swingValue($swingList) {
        $valid = TRUE;

        if (!array_key_exists($this->swingType, $swingList)) {
            return FALSE;
        }

        $sides = (int)$swingList[$this->swingType];

        if ($sides < $this->swingMin || $sides > $this->swingMax) {
            return FALSE;
        }

        if ($valid) {
            $this->swingValue = $sides;
            $this->needsSwingValue = FALSE;
            $this->valueRequested = FALSE;
            $this->max = $sides;
            $this->scoreValue = $sides;
        }

        return $valid;
    }

    /**
     * Get all die types.
     *
     * @return array
     */
    public function getDieTypes() {
        $typesList = array();
        $typesList[$this->swingType . ' Swing'] = array(
            'code' => $this->swingType,
            'swingMin' => $this->swingMin,
            'swingMax' => $this->swingMax,
            'description' => $this->getDescription(),
        );
        return $typesList;
    }

    /**
     * Get description of this type of swing die
     *
     * @return string
     */
    public function getDescription() {
        return  $this->swingType . ' Swing Dice can be any die between ' .
                $this->swingMin . ' and ' . $this->swingMax . '. Swing Dice ' .
                'are allowed to be any integral size between their upper and ' .
                'lower limit, including both ends, and including nonstandard ' .
                'die sizes like 17 or 9. Each player chooses his or her ' .
                'Swing Die in secret at the beginning of the match, and ' .
                'thereafter the loser of each round may change their Swing ' .
                'Die between rounds. If a character has any two Swing Dice ' .
                'of the same letter, they must always be the same size.';
    }
}
