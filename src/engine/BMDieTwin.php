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
    public $dice;

    public function init($sidesArray, array $skills = NULL) {
        if (!is_array($sidesArray)) {
            throw new InvalidArgumentException('sidesArray must be an array.');
        }

        if (2 != count($sidesArray)) {
            throw new InvalidArgumentException('sidesArray must have exactly two elements.');
        }

        foreach ($sidesArray as $dieIdx => $sides) {
            $this->dice[$dieIdx] =
                BMDie::create_from_string_components($sides, $skills);
        }

        $this->add_multiple_skills($skills);

        if ($this->dice[0] instanceof BMDieSwing &&
            $this->dice[1] instanceof BMDieSwing &&
            $this->dice[0]->swingType != $this->dice[1]->swingType) {
            throw new InvalidArgumentException('A twin die can only have one swing type.');
        }

        if ($this->dice[0] instanceof BMDieSwing) {
            $this->swingType = $this->dice[0]->swingType;
        } elseif ($this->dice[1] instanceof BMDieSwing) {
            $this->swingType = $this->dice[1]->swingType;
        }

        $this->recalc_max_min();
    }

    public static function create($sidesArray, array $skills = NULL) {
        if (!is_array($sidesArray)) {
            throw new InvalidArgumentException('sidesArray must be an array.');
        }

        $die = new BMDieTwin;
        $die->init($sidesArray, $skills);

        return $die;
    }

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

    public function roll($isTriggeredByAttack = FALSE) {
        if (is_null($this->max)) {
            return;
        }

        $this->run_hooks('pre_roll', array('die' => $this,
                                           'isTriggeredByAttack' => $isTriggeredByAttack));

        // james: note that $this->value cannot be set to zero directly, since this triggers a bug
        $value = 0;
        foreach ($this->dice as &$die) {
            // note that we do not want to trigger the hooks again, so we set the
            // input parameter of roll() to FALSE
            $die->roll(FALSE);
            $value += $die->value;
        }

        $this->value = $value;

        //$this->run_hooks('post_roll', array('isTriggeredByAttack' => $isTriggeredByAttack));
    }

    // Print long description
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

    protected function skillStr() {
        $skillStr = '';
        if (count($this->skillList) > 0) {
            foreach (array_keys($this->skillList) as $skill) {
                if (('Mood' != $skill) && 'Mad' != $skill) {
                    $skillStr .= "$skill ";
                }
            }
        }

        return $skillStr;
    }

    protected function moodStr() {
        $moodStr = '';
        if ($this->has_skill('Mad')) {
            $moodStr = ' Mad';
        } elseif ($this->has_skill('Mood')) {
            $moodStr = ' Mood';
        }

        return $moodStr;
    }

    protected function typeStr() {
        $typeStr = '';
        if ($this->dice[0] instanceof BMDieSwing &&
            $this->dice[1] instanceof BMDieSwing) {
            $typeStr = "Twin {$this->dice[0]->swingType}{$this->moodStr()} Swing Die";
        } else {
            $typeStr = 'Twin Die';
        }

        return $typeStr;
    }

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

    public function split() {
        $oldRecipe = $this->get_recipe();
        unset($this->value);
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

    // shrink() is intended to be used for weak dice
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

    // grow() is intended to be used for mighty dice
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

    public function recalc_max_min() {
        $this->min = 0;
        $this->max = 0;

        foreach ($this->dice as $die) {
            if (is_null($die->min) ||
                is_null($die->max)) {
                $this->min = NULL;
                $this->max = NULL;
                break;
            }
            $this->min += $die->min;
            $this->max += $die->max;
        }

        $this->remove_flag('IsAsymmetricTwin');
        if ($this->dice[0]->max != $this->dice[1]->max) {
            $this->add_flag('IsAsymmetricTwin',
                            array($this->dice[0]->max, $this->dice[1]->max));
        }
    }

    public function getDieTypes() {
        $typesList = array();
        $typesList['Twin'] = array(
            'code' => ',',
            'description' =>
                'Twin Dice appear as two numbers with a comma between them ' .
                'and are played as two dice that add together. For example, ' .
                'a twin 8 is represented as (8,8) and treated as a single ' .
                'die. The two 8\'s are rolled as one, captured as one, and ' .
                'scored as one die worth 16 points. Twin Dice may contain ' .
                'either standard dice or Swing Dice.',
        );
        foreach ($this->dice as $subDie) {
            $typesList += $subDie->getDieTypes();
        }
        return $typesList;
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
