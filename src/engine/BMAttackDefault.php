<?php
/**
 * BMAttackDefault: Code allowing automatic choice of a default attack
 *
 * @author james
 */

/**
 * This class contains the code required to enable a default attack, when
 * there is no ambiguity about the type of attack that is desired
 */
class BMAttackDefault extends BMAttack {
    /**
     * Type of attack
     *
     * @var string
     */
    public $type = 'Default';

    /**
     * Resolved type of attack, if the attack is unambiguous
     *
     * @var string
     */
    protected $resolvedType = '';

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
        return $this->validate_attack(
            $game,
            $this->attackerAttackDieArray,
            $game->defenderAttackDieArray
        );
    }

    /**
     * Determine if specified attack is valid.
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return bool
     */
    public function validate_attack($game, array $attackers, array $defenders, array $args = array()) {
        $this->validationMessage = '';
        $this->resolvedType = '';

        $possibleAttackTypeArray = $game->valid_attack_types();
        $validAttackTypeArray = array();

        foreach ($possibleAttackTypeArray as $attackType) {
            $attack = BMAttack::create($attackType);
            if (!empty($this->validDice)) {
                foreach ($this->validDice as &$die) {
                    $attack->add_die($die);
                }
            }
            if ($attack->validate_attack($game, $attackers, $defenders, $args)) {
                $validAttackTypeArray[] = $attackType;
            }
        }

        switch (count($validAttackTypeArray)) {
            case 1:
                $this->resolvedType = $validAttackTypeArray[0];
                return TRUE;
            case 0:
                $this->validationMessage = 'There is no valid attack corresponding to the dice selected.';
                return FALSE;
            default:
                if ($this->is_one_on_two_no_frills_attack($attackers, $defenders, $validAttackTypeArray) ||
                    $this->is_one_on_one_no_frills_attack($game, $attackers, $defenders, $validAttackTypeArray)) {
                    $this->resolvedType = $validAttackTypeArray[0];
                    $this->validationMessage = '';
                    return TRUE;
                }

                return FALSE;
        }
    }

    /**
     * Check whether the specified attack is one-vs-one and unambiguous
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @param array $validAttackTypes
     * @return bool
     */
    protected function is_one_on_one_no_frills_attack(
        BMGame $game,
        array $attackers,
        array $defenders,
        array $validAttackTypes
    ) {
        $messageRoot = 'Default attack is ambiguous. ';
        $messageAttackTypes = 'Possible attack types: ' .
            implode(', ', $validAttackTypes) . '.';

        if (1 != count($attackers)) {
            $this->validationMessage = $messageRoot . $messageAttackTypes;
            return FALSE;
        }

        if (1 != count($defenders)) {
            $this->validationMessage = $messageRoot . $messageAttackTypes;
            return FALSE;
        }

        $attacker = $attackers[0];
        $defender = $defenders[0];

        // deal with skills with side effects
        if ($attacker->has_skill('Doppelganger') && in_array('Power', $validAttackTypes)) {
            $this->validationMessage = $messageRoot .
                'A power attack will trigger the Doppelganger skill, while other attack types will not.';
            return FALSE;
        }

        // deal with attacks with side effects
        if (in_array('Berserk', $validAttackTypes)) {
            $this->validationMessage = $messageRoot .
                'A berserk attack will trigger the berserk skill, while other attack types will not.';
            return FALSE;
        }

        if (in_array('Trip', $validAttackTypes)) {
            $this->validationMessage = $messageRoot .
                'It is not clear whether or not you want to perform a trip attack.';
            return FALSE;
        }

        if (in_array('Boom', $validAttackTypes)) {
            $this->validationMessage = $messageRoot .
                'It is not clear whether or not you want to perform a boom attack.';
            return FALSE;
        }

        if ($this->is_fire_assistance_possible($game, $attacker, $defender, $validAttackTypes)) {
            // deal with the case where the only possibilities are power and skill, and
            // then choose power, since this allows both exact firing and overfiring
            if ((2 == count($validAttackTypes)) &&
                in_array('Power', $validAttackTypes) &&
                in_array('Skill', $validAttackTypes)) {
                assert('Power' == $validAttackTypes[0]);
                return TRUE;
            }

            $this->validationMessage = $messageRoot .
                'It is not clear if you wish to use your Fire die to assist in the attack.';
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Check whether fire assistance is possible for the specified combination of
     * attacker and defender
     *
     * @param BMGame $game
     * @param BMDie $attacker
     * @param BMDie $defender
     * @param array $validAttackTypes
     * @return bool
     */
    protected function is_fire_assistance_possible(
        BMGame $game,
        BMDie $attacker,
        BMDie $defender,
        array $validAttackTypes
    ) {
        $fireTurndownAvailable = 0;

        foreach ($game->attackerAllDieArray as $die) {
            if ($die === $attacker) {
                continue;
            }

            if ($die->has_skill('Fire')) {
                $fireTurndownAvailable += $die->value - $die->min;
            }
        }

        if ($fireTurndownAvailable > 0) {
            // power attack with the possibility of fire assistance
            if (in_array('Power', $validAttackTypes) && ($attacker->value < $attacker->max)) {
                return TRUE;
            }

            // skill attack with the need for fire assistance
            if (in_array('Skill', $validAttackTypes) && ($attacker->value < $defender->value)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Check whether the specified attack is one-vs-two and unambiguous
     *
     * @param array $attackers
     * @param array $defenders
     * @param array $validAttackTypes
     * @return bool
     */
    protected function is_one_on_two_no_frills_attack(
        array $attackers,
        array $defenders,
        array $validAttackTypes
    ) {
        $messageRoot = 'Default attack is ambiguous. ';
        $messageAttackTypes = 'Possible attack types: ' .
            implode(', ', $validAttackTypes) . '.';

        if (1 != count($attackers)) {
            $this->validationMessage = $messageRoot . $messageAttackTypes;
            return FALSE;
        }

        if (2 != count($defenders)) {
            $this->validationMessage = $messageRoot . $messageAttackTypes;
            return FALSE;
        }

        // deal with attacks with side effects
        if (in_array('Berserk', $validAttackTypes)) {
            $this->validationMessage = $messageRoot .
                'A berserk attack will trigger the berserk skill, while other attack types will not.';
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Accessor for the attack type, used by logging code
     *
     * @return string
     */
    public function type_for_log() {
        return $this->resolvedType;
    }

    /**
     * Check if skills are compatible with this type of attack.
     *
     * @param array $attArray
     * @param array $defArray
     * @return bool
     */
    protected function are_skills_compatible(array $attArray, array $defArray) {
        return TRUE;
    }
}
