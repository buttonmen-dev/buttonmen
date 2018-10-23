<?php
/**
 * BMDieTwin: Code specific to twin dice
 *
 * @author james
 */

/**
 * This class contains all the logic to do with instantiating twin dice
 *
 * @property      array $dice         Array of two dice that make up the twin die
 */
class BMDieTwin extends BMDie {
    /**
     * Array of two subdice
     *
     * @var array
     */
    public $dice;

    /**
     * Create the subdice from specified subdie recipes, and add die skills
     *
     * Hackish: the caller can specify each skill as either a plain
     * value, "skill", or a key/value pair "ClassName" => "skill",
     * where the key is the class name which implements that skill.
     * This is only for use by callers outside of engine (e.g.
     * testing), and should never be used for the default BMSkill*
     * set of skills.
     *
     * @param array $sidesArray
     * @param array $skills
     */
    public function init($sidesArray, array $skills = NULL) {
        if (!is_array($sidesArray)) {
            throw new InvalidArgumentException('sidesArray must be an array.');
        }

        if (2 != count($sidesArray)) {
            throw new InvalidArgumentException('sidesArray must have exactly two elements.');
        }

        foreach ($sidesArray as $dieIdx => $sides) {
            $this->dice[$dieIdx] =
                BMDie::create_from_string_components($sides, $skills, TRUE);
        }

        $this->add_multiple_skills($skills);

        if ($this->dice[0] instanceof BMDieSwing &&
            $this->dice[1] instanceof BMDieSwing &&
            $this->dice[0]->swingType != $this->dice[1]->swingType) {
            throw new InvalidArgumentException('A twin die can only have one swing type.');
        }

        if ($this->dice[0] instanceof BMDieSwing) {
            $this->swingType = $this->dice[0]->swingType;
            $this->swingMax = $this->dice[0]->swingMax;
            $this->swingMin = $this->dice[0]->swingMin;
        } elseif ($this->dice[1] instanceof BMDieSwing) {
            $this->swingType = $this->dice[1]->swingType;
            $this->swingMax = $this->dice[1]->swingMax;
            $this->swingMin = $this->dice[1]->swingMin;
        }

        $this->recalc_max_min();
    }

    /**
     * Create a BMDieTwin with the specified dice, then
     * add skills to the die.
     *
     * @param array $sidesArray
     * @param array $skills
     * @return BMDieTwin
     */
    public static function create($sidesArray, array $skills = NULL) {
        if (!is_array($sidesArray)) {
            throw new InvalidArgumentException('sidesArray must be an array.');
        }

        $die = new BMDieTwin;
        $die->init($sidesArray, $skills);

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

        foreach ($this->dice as $die) {
            if ($die instanceof BMDieSwing) {
                $this->ownerObject->request_swing_values(
                    $newDie,
                    $die->swingType,
                    $newDie->playerIdx
                );
            }
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
        if (is_null($this->max)) {
            return;
        }

        $this->run_hooks('pre_roll', array('die' => $this,
                                           'isTriggeredByAttack' => $isTriggeredByAttack,
                                           'isSubdie' => $isSubdie));

        foreach ($this->dice as &$die) {
            // note that we do not want to trigger the hooks again, so we set the
            // input parameter of roll() to FALSE
            $die->roll(FALSE, TRUE);
        }

        $this->recalc_max_min();

        $this->run_hooks('post_roll', array('die' => $this,
                                            'isTriggeredByAttack' => $isTriggeredByAttack));
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

        $skillStr = $this->skillStr();
        $typeStr = $this->typeStr();
        $sideStr = $this->sideStr();

        $valueStr = '';
        if ($isValueRequired && isset($this->value)) {
            $valueStr = " showing {$this->value}";
        }

        $result = "{$skillStr}{$typeStr}{$sideStr}{$valueStr}";

        return $result;
    }

    /**
     * Create string listing all skills except those associated with swing
     *
     * @return string
     */
    protected function skillStr() {
        $skillStr = '';
        if (count($this->skillList) > 0) {
            foreach (array_keys($this->skillList) as $skill) {
                if (('Mood' != $skill) && ('Mad' != $skill) && ('Turbo' != $skill)) {
                    $skillStr .= "$skill ";
                }
            }
        }

        return $skillStr;
    }

    /**
     * Create string for mood skills associated with swing
     *
     * @return string
     */
    protected function moodStr() {
        $moodStr = '';
        if ($this->has_skill('Mad')) {
            $moodStr = ' Mad';
        } elseif ($this->has_skill('Mood')) {
            $moodStr = ' Mood';
        }

        return $moodStr;
    }

    /**
     * Create string for turbo skills associated with swing
     *
     * @return string
     */
    protected function turboStr() {
        $turboStr = '';
        if ($this->has_skill('Turbo')) {
            $turboStr = 'Turbo ';
        }

        return $turboStr;
    }

    /**
     * Create string of the type of twin die, including its swing type
     *
     * @return string
     */
    protected function typeStr() {
        $typeStr = '';
        if ($this->dice[0] instanceof BMDieSwing &&
            $this->dice[1] instanceof BMDieSwing) {
            $typeStr = "{$this->turboStr()}Twin {$this->dice[0]->swingType}" .
                       "{$this->moodStr()} Swing Die";
        } else {
            $typeStr = 'Twin Die';
        }

        return $typeStr;
    }

    /**
     * Create string of the number of sides in the twin die
     *
     * @return string
     */
    protected function sideStr() {
        $sideStr = '';
        if (isset($this->dice[0]->max)) {
            if ($this->dice[0]->max == $this->dice[1]->max) {
                $sideStr = " (both with {$this->dice[0]->max} side";
                if ($this->dice[0]->max != 1) {
                    $sideStr .= 's';
                }
                $sideStr .= ')';
            } else {
                $sideStr = " (with {$this->dice[0]->max} and {$this->dice[1]->max} sides)";
            }
        }

        return $sideStr;
    }

    /**
     * split a die in twain. If something needs to cut a die's size in
     * half, it should use this and throw one part away. (Or toss both;
     * all references to the original die will pick up the split.)
     *
     * In the case of an odd number of sides, the remainder stays with the
     * original die
     *
     * @return array
     */
    public function split() {
        $oldRecipe = $this->get_recipe(TRUE);
        $this->value = NULL;
        $newdie = clone $this;

        foreach ($this->dice as $dieIdx => &$die) {
            $splitDieArray = $die->split();
            $this->dice[$dieIdx] = $splitDieArray[$dieIdx % 2];
            $newdie->dice[$dieIdx] = $splitDieArray[($dieIdx + 1) % 2];
        }

        $this->recalc_max_min();
        $newdie->recalc_max_min();

        $this->add_flag('HasJustSplit', $oldRecipe);
        $newdie->add_flag('HasJustSplit', $oldRecipe);

        $splitDice = array($this, $newdie);

        $this->run_hooks(__FUNCTION__, array('dice' => &$splitDice));

        return $splitDice;
    }

    /**
     * shrink() is intended to be used for weak dice
     */
    public function shrink() {
        $oldRecipe = $this->get_recipe();

        foreach ($this->dice as &$die) {
            $die->shrink();
        }

        $this->recalc_max_min();

        if ($this->get_recipe() != $oldRecipe) {
            $this->add_flag('HasJustShrunk', $oldRecipe);
        }
    }

    /**
     * grow() is intended to be used for mighty dice
     */
    public function grow() {
        $oldRecipe = $this->get_recipe();

        foreach ($this->dice as &$die) {
            $die->grow();
        }

        $this->recalc_max_min();

        if ($this->get_recipe() != $oldRecipe) {
            $this->add_flag('HasJustGrown', $oldRecipe);
        }
    }

    /**
     * Try to set swing value for this BMDieTwin from an array of all swing values
     *
     * @param array $swingList
     * @return bool
     */
    public function set_swingValue($swingList) {
        $valid = TRUE;

        foreach ($this->dice as &$die) {
            if ($die instanceof BMDieSwing) {
                $valid &= $die->set_swingValue($swingList);
                $this->swingValue = $die->swingValue;
            }
        }

        $this->recalc_max_min();

        return $valid || !isset($this->swingType);
    }

    /**
     * Recalculate maximum and minimum value of BMDieTwin
     */
    public function recalc_max_min() {
        $this->min = 0;
        $this->max = 0;
        $value = 0;
        $hasBothSubValues = TRUE;

        foreach ($this->dice as $subdie) {
            if (!isset($subdie->value)) {
                $hasBothSubValues = FALSE;
            }

            if ($hasBothSubValues) {
                $value += $subdie->value;
            }

            if (!isset($subdie->min) ||
                !isset($subdie->max)) {
                $this->min = NULL;
                $this->max = NULL;
                break;
            }
            $this->min += $subdie->min;
            $this->max += $subdie->max;
        }

        if ($hasBothSubValues) {
            $this->value = $value;
        } else {
            $this->value = NULL;
        }

        $this->remove_flag('Twin');

        $subdieMaxArray = array();
        $subdieValueArray = array();

        foreach ($this->dice as $subdieIdx => $subdie) {
            if (isset($subdie->max)) {
                $subdieMaxArray[$subdieIdx] = $subdie->max;
            } else {
                $subdieMaxArray[$subdieIdx] = NULL;
            }

            if (isset($subdie->value)) {
                $subdieValueArray[$subdieIdx] = $subdie->value;
            } else {
                $subdieValueArray[$subdieIdx] = NULL;
            }
        }

        $this->add_flag(
            'Twin',
            array('sides' => $subdieMaxArray,
                  'values' => $subdieValueArray)
        );
    }

    /**
     * Get all die types.
     *
     * @return array
     */
    public function getDieTypes() {
        $typesList = array();
        $typesList['Twin'] = array(
            'code' => ',',
            'description' => self::getDescription(),
        );
        foreach ($this->dice as $subDie) {
            $typesList += $subDie->getDieTypes();
        }
        return $typesList;
    }

    /**
     * Get description of twin dice
     *
     * @return string
     */
    public static function getDescription() {
        return  'Twin Dice appear as two numbers with a comma between them ' .
                'and are played as two dice that add together. For example, ' .
                'a twin 8 is represented as (8,8) and treated as a single ' .
                'die. The two 8\'s are rolled as one, captured as one, and ' .
                'scored as one die worth 16 points. Twin Dice may contain ' .
                'either standard dice or Swing Dice.';
    }

    /**
     * To be run after a BMDieTwin object is cloned.
     *
     * This causes the subdice to also be cloned.
     */
    public function __clone() {
        $newDieArray = array();

        foreach ($this->dice as $die) {
            $newDieArray[] = clone $die;
        }

        $this->dice = $newDieArray;
    }
}
