<?php
/**
 * BMDieOption: Code specific to option dice
 *
 * @author james
 */

/**
 * This class contains all the logic to do with requesting and setting option values
 *
 * @property      array $optionValueArray  Possible option values
 * @property-read bool  $needsOptionValue  Flag indicating if an option value is still needed
 * @property-read bool  $valueRequested    Flag indicating if an option request has been sent to the owner
 */
class BMDieOption extends BMDie {
    /**
     * Possible option values
     *
     * @var array
     */
    protected $optionValueArray;

    /**
     * Flag indicating if an option value is still needed
     *
     * @var bool
     */
    protected $needsOptionValue;

    /**
     * Flag indicating if an option request has been sent to the owning BMGame
     *
     * @var type
     */
    protected $valueRequested;

    /**
     * Set the possible option values for the BMDieOption, and add die skills
     *
     * Hackish: the caller can specify each skill as either a plain
     * value, "skill", or a key/value pair "ClassName" => "skill",
     * where the key is the class name which implements that skill.
     * This is only for use by callers outside of engine (e.g.
     * testing), and should never be used for the default BMSkill*
     * set of skills.
     *
     * @param array $optionArray
     * @param array $skills
     */
    public function init($optionArray, array $skills = NULL) {
        if (!is_array($optionArray) ||
            2 != count($optionArray)) {
            throw new InvalidArgumentException('optionArray must be an array with at exactly two elements.');
        }

        $this->min = 1;

        $this->divisor = 1;
        $this->remainder = 0;

        $this->optionValueArray = $optionArray;
        $this->needsOptionValue = TRUE;
        $this->valueRequested = FALSE;

        $this->add_multiple_skills($skills);
    }

    /**
     * Create a BMDieOption with a specified array of option values, then
     * add skills to the die.
     *
     * @param array $optionArray
     * @param array $skills
     * @return BMDieOption
     */
    public static function create($optionArray, array $skills = NULL) {
        $die = new BMDieOption;

        $die->init($optionArray, $skills);

        return $die;
    }

    /**
     * Wakes up a die from its container to be used in a game.
     * Does not roll the die.
     *
     * Clones the die and returns the clone.
     */
    public function activate() {
        $newDie = clone $this;

        $this->ownerObject->add_die($newDie);

        // The clone is the one going into the game, so it's the one
        // that needs a option value to be set.
        $this->ownerObject->request_option_values(
            $newDie,
            $newDie->optionValueArray,
            $newDie->playerIdx
        );
        $newDie->valueRequested = TRUE;
    }

    /**
     * Roll die
     *
     * @param bool $isTriggeredByAttack
     * @param bool $isSubdie
     */
    public function roll($isTriggeredByAttack = FALSE, $isSubdie = FALSE) {
        if ($this->needsOptionValue && !isset($this->max)) {
            if (!$this->valueRequested) {
                $this->ownerObject->request_option_values(
                    $this,
                    $this->optionValueArray,
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

        $skillStr = '';
        if (count($this->skillList) > 0) {
            foreach (array_keys($this->skillList) as $skill) {
                $skillStr .= "$skill ";
            }
        }

        $sideStr = '';
        if (isset($this->max)) {
            $sideStr = " (with {$this->max} side";
            if ($this->max != 1) {
                $sideStr .= 's';
            }
            $sideStr .= ')';
        } else {
            $sideStr = " (with {$this->optionValueArray[0]} or {$this->optionValueArray[1]} sides)";
        }

        $valueStr = '';
        if ($isValueRequired && isset($this->value)) {
            $valueStr = " showing {$this->value}";
        }

        $result = "{$skillStr}Option Die{$sideStr}{$valueStr}";

        return $result;
    }

    /**
     * Try to set option value for this BMDieOption
     *
     * @param int $optionValue
     * @return bool
     */
    public function set_optionValue($optionValue) {
        if (FALSE === array_search($optionValue, $this->optionValueArray)) {
            return FALSE;
        }

        $this->needsOptionValue = FALSE;
        $this->valueRequested = FALSE;
        $this->max = $optionValue;
        $this->scoreValue = $optionValue;

        return TRUE;
    }

    /**
     * Get all die types.
     *
     * @return array
     */
    public function getDieTypes() {
        $typesList = array();
        $typesList['Option'] = array(
            'code' => '/',
            'description' =>
                'Option Dice are represented as two numbers with a slash ' .
                'between them, resembling a fraction. They function like ' .
                'Swing Dice and can be changed at any time a Swing Die could ' .
                'be changed. However, Option Dice are restricted to only two ' .
                'values. For example, a 4/10 Option Die can be only a 4 or a ' .
                '10.',
        );
        return $typesList;
    }

    /**
     * Setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
//        switch ($property) {
//            default:
                parent::__set($property, $value);
//        }
    }
}
