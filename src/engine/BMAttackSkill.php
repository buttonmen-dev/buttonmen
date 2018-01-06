<?php
/**
 * BMAttackSkill: Code specific to skill attacks
 *
 * The degenerate case of skill attacks is where you have a lot of potential
 * attacks, and very few (or none) of them hit. Golo vs Nelde (Y=1), perhaps.
 * We’re going to have to check every combination of Golo’s dice, for every
 * value of each die, against each of Nelde’s dice. Worst case (10, 12, 20, 20),
 * that’s 62 1-die checks, 120 + 200 + 200 + 240 + 240 + 400 2-die checks,
 * 2400 + 2400 + 4000 + 4800 3-die, and 48000 4-die. (Times five, since nelde
 * has five dice.)
 *
 * This can be made a lot faster if the skill attack code understands how the
 * various skills behave, but that’s a nightmare of special cases, and should
 * be avoided unless there’s no other choice.
 *
 * The idea of hit tables is to make the bad cases faster by making the general
 * cases a bit slower. We build a complete list of the values that the
 * attacker’s dice can make, and each value has an attached list of which
 * combinations of dice can make it. At the very least, that cuts the Golo vs.
 * nelde case by a factor of five, and IIRC, there are some other optimizations
 * in there to reduce it further. (And it’s almost certainly tractable to
 * future optimization as well.)
 *
 * The way the skill attack code should work is:
 *
 * - Build the hit table.
 * - Check each of the defender’s die values by looking it up in the table. If
 *   it’s there, and the attack validates, we’re good.
 * - If we have no direct hits, check to see if there’s any assist values
 *   available (Fire, or any future skill that works similarly).
 * - If there is assistance, search the entire hit table, using validate_attack
 *   to see if any of the possible attacks are possible due to assistance.
 *
 * That last step is slow, and only necessary rarely.
 *
 * @author Julian
 */

/**
 * This class contains code specific to skill attacks
 */
class BMAttackSkill extends BMAttack {
    /**
     * Type of attack
     *
     * @var string
     */
    public $type = "Skill";

    /**
     * Hit table containing all possible hit values without help from fire.
     *
     * @var BMUtilityHitTable
     */
    protected $hitTable = NULL;

    /**
     * Create $this->hitTable anew.
     *
     * If $includeOptional is FALSE, attacking warrior dice are excluded.
     *
     * @param bool $includeOptional
     */
    protected function generate_hit_table($includeOptional = TRUE) {
        if ($includeOptional) {
            $validDice = $this->validDice;
        } else {
            $validDice = array();

            foreach ($this->validDice as &$die) {
                if ($die->has_skill('Warrior')) {
                    continue;
                }
                $validDice[] = $die;
            }
        }

        self::strip_excess_zeros($validDice);

        $this->hitTable = new BMUtilityHitTable($validDice);
    }

    protected static function strip_excess_zeros(&$dieArray) {
        $zeroCount = 0;
        $zeroIdxArray = array();

        foreach ($dieArray as $dieIdx => $die) {
            if (0 === $die->value) {
                $zeroCount++;
                $zeroIdxArray[] = $dieIdx;
            }
        }

        while ($zeroCount > 2) {
            $zeroCount--;
            array_splice($dieArray, $zeroIdxArray[$zeroCount], 1);
            unset($zeroIdxArray[$zeroCount]);
        }
    }

    /**
     * Determine if there is at least one valid attack of this type from
     * the set of all possible attackers and defenders.
     *
     * If $includeOptional is FALSE, then optional attacks are excluded.
     * These include skill attacks involving warrior dice.
     *
     * @param BMGame $game
     * @param bool $includeOptional
     * @return bool
     */
    public function find_attack($game, $includeOptional = TRUE) {
        $targets = $game->defenderAllDieArray;

        if (count($targets) < 1) {
            return FALSE;
        }

        $this->generate_hit_table($includeOptional);
        $hits = $this->hitTable->list_hits();

        foreach ($targets as $t) {
            // Sort the multidimensional array by distance from target
            $this->sort_distance($hits, $t->value);

            foreach ($hits as $hit) {
                $def = array($t);
                $combos = $this->hitTable->find_hit($hit);
                foreach ($combos as $att) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                }
            }
        }

        return FALSE;
    }

    /**
     * Sort values by distance from $targetValue in ascending order
     *
     * @param array $values
     * @param double $targetValue
     */
    protected function sort_distance(array &$values, $targetValue) {
        // centre values around $targetValue
        array_walk(
            $values,
            function (&$val, $key, $centre) {
                $val = $val - $centre;
            },
            $targetValue
        );

        // sort according to absolute distance from centre
        usort($values, array($this, 'absolute_distance_cmp'));

        // remove centration
        array_walk(
            $values,
            function (&$val, $key, $centre) {
                $val = $val + $centre;
            },
            $targetValue
        );
    }

    /**
     * Comparator used for sorting by absolute distance.
     *
     * @param double $aVal
     * @param double $bVal
     * @return bool
     */
    protected function absolute_distance_cmp($aVal, $bVal) {
        return abs($aVal) > abs($bVal);
    }

    /**
     * Determine if specified attack is valid.
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @param array $args
     * @return bool
     */
    public function validate_attack($game, array $attackers, array $defenders, array $args = array()) {
        $this->validationMessage = '';

        if (count($attackers) < 1) {
            $this->validationMessage = 'There must be at least one attacking die for a skill attack.';
            return FALSE;
        }

        if (count($defenders) != 1) {
            $this->validationMessage = 'There must be exactly one target die for a skill attack.';
            return FALSE;
        }

        if ($this->has_dizzy_attackers($attackers)) {
            // validation message set within $this->has_dizzy_attackers()
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            // validation message set within $this->are_skills_compatible()
            return FALSE;
        }

        if (!$this->are_button_skills_compatible($defenders)) {
            // validation message set within $this->are_button_skills_compatible()
            return FALSE;
        }

        $dval = $defenders[0]->defense_value($this->type);

        if (!($this->hitTable instanceof BMUtilityHitTable)) {
            $this->generate_hit_table();
        }

        if ($this->is_direct_attack_valid($attackers, $dval)) {
            return TRUE;
        }

        $helpValue = NULL;

        if (array_key_exists('helpValue', $args)) {
            $helpValue = $args['helpValue'];
        }

        return $this->is_assisted_attack_valid($game, $attackers, $defenders, $dval, $helpValue);
    }

    /**
     * Comparator used for merging arrays of BMDie.
     *
     * array_intersect tries to convert to strings, so we use array_uintersect,
     * which needs a comparison function.
     *
     * @param mixed $var1
     * @param mixed $var2
     * @return int
     */
    protected static function cmp($var1, $var2) {
        if ($var1===$var2) {
            return 0;
        }
        if ($var1 > $var2) {
            return 1;
        }
        return -1;
    }

    /**
     * Determine if direct attack without help is valid.
     *
     * @param array $attackers
     * @param double $dval
     * @return bool
     */
    protected function is_direct_attack_valid($attackers, $dval) {
        $combos = $this->hitTable->find_hit($dval);
        if ($combos) {
            foreach ($combos as $c) {
                if (count($c) == count($attackers) &&
                    count(array_uintersect($c, $attackers, 'BMAttackSkill::cmp')) ==
                      count($c)) {
                    return TRUE;
                }
            }
        }
    }

    /**
     * Determine if assisted attack (including help) is valid.
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @param double $dval
     * @param double $helpValue
     * @return bool
     */
    protected function is_assisted_attack_valid($game, $attackers, $defenders, $dval, $helpValue) {
        if (is_null($helpValue)) {
            $bounds = $this->help_bounds(
                $this->collect_helpers($game, $attackers, $defenders),
                $this->collect_firing_maxima($attackers)
            );
        } else {
            $bounds = array($helpValue, $helpValue);
        }

        if ($bounds[0] == 0 && $bounds[1] == 0) {
            $this->validationMessage = 'Attacking die values do not sum up to target die value.';
            return FALSE;
        }
        for ($i = $bounds[0]; $i <= $bounds[1]; $i++) {
            // james: This logic assumes that firing effectively reduces the defence value.
            //        This assumption fails in the case that part of the skill sum comes
            //        from a konstant die that is being subtracted from the total.
            $combos = $this->hitTable->find_hit($dval - $i);
            if ($combos) {
                foreach ($combos as $c) {
                    if (count($c) == count($attackers) &&
                        count(array_uintersect($c, $attackers, 'BMAttackSkill::cmp')) ==
                        count($c)) {
                        return TRUE;
                    }
                }
            }
        }
        $this->validationMessage = 'Attacking die values do not sum up to target die value.';
        return FALSE;
    }

    /**
     * Check if skills are compatible with this type of attack.
     *
     * @param array $attArray
     * @param array $defArray
     * @return bool
     */
    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (0 == count($attArray)) {
            throw new InvalidArgumentException('attArray must have at least one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $def = $defArray[0];

        if (1 == count($attArray)) {
            if ($attArray[0]->has_skill('Stealth')) {
                $this->validationMessage = 'Skill attacks involving a single attacking stealth die are invalid.';
                return FALSE;
            }

            if ($attArray[0]->has_skill('Konstant')) {
                $this->validationMessage = 'Skill attacks involving a single attacking konstant die are invalid.';
                return FALSE;
            }
        }

        if (1 == count($attArray) &&
            $def->has_skill('Stealth')) {
            $this->validationMessage = 'Multiple attacking dice are required to skill attack a stealth die.';
            return FALSE;
        }

        if ($def->has_skill('Warrior')) {
            $this->validationMessage = 'Warrior dice cannot be attacked';
            return FALSE;
        }

        foreach ($attArray as $att) {
            if ($att->has_skill('Berserk')) {
                $this->validationMessage = 'Berserk dice cannot perform skill attacks.';
                return FALSE;
            }
        }

        if ($this->is_invalid_warrior_attack($attArray)) {
            // validation message set within $this->is_invalid_warrior_attack()
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Check if button skills are compatible with this type of attack.
     *
     * @param array $defArray
     * @return bool
     */
    protected function are_button_skills_compatible(array $defArray) {
        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $def = $defArray[0];

        if ($def->has_skill('Insult')) {
            $this->validationMessage =
                'Dice with the Insult skill cannot be attacked by skill attacks.';
            return FALSE;
        }

        if ($def->ownerObject instanceof BMGame) {
            $ownerButton = $def->ownerObject->playerArray[$def->playerIdx]->button;
            if ($ownerButton->has_skill('TheJapaneseBeetle')) {
                $this->validationMessage =
                    'Dice owned by The Japanese Beetle cannot be attacked by skill attacks.';
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Check if the proposed attack is an invalid warrior attack, namely:
     *
     * - there are multiple warrior dice present, or
     * - there are no non-warrior dice present
     *
     * @param array $attArray
     * @return bool
     */
    protected function is_invalid_warrior_attack(array $attArray) {
        $nWarrior = 0;
        foreach ($attArray as $attacker) {
            if ($attacker->has_skill('Warrior')) {
                $nWarrior++;
            }
        }

        // check if there are any warrior dice present
        if (0 == $nWarrior) {
            return FALSE;
        }

        // check that there is only one warrior die present
        if ($nWarrior != 1) {
            $this->validationMessage = 'Only one Warrior die can be brought into play at a time';
            return TRUE;
        }

        // check that there is at least one non-warrior die taking part
        if (1 == count($attArray)) {
            $this->validationMessage = 'There must be at least one non-Warrior attacker';
            return TRUE;
        }
    }
}
