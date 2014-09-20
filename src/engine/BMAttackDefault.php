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
    public $type = 'Default';
    protected $resolvedType = '';

    public function find_attack($game) {
        return $this->validate_attack(
            $game,
            $this->attackerAttackDieArray,
            $game->defenderAttackDieArray
        );
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        $this->validationMessage = '';
        $this->resolvedType = '';

        $possibleAttackTypeArray = $game->valid_attack_types();
        $validAttackTypeArray = array();

        foreach ($possibleAttackTypeArray as $attackType) {
            $attack = BMAttack::get_instance($attackType);
            if (!empty($this->validDice)) {
                foreach ($this->validDice as &$die) {
                    $attack->add_die($die);
                }
            }
            if ($attack->validate_attack($game, $attackers, $defenders)) {
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
                if ($this->is_one_on_one_no_frills_attack($game, $attackers, $defenders, $validAttackTypeArray)) {
                    $this->resolvedType = $validAttackTypeArray[0];
                    return TRUE;
                }

                return FALSE;
        }
    }

    protected function is_one_on_one_no_frills_attack(
        BMGame $game,
        array $attackers,
        array $defenders,
        array $validAttackTypes
    ) {
        $messageRoot = 'Default attack is ambiguous. ';
        $messageAttackTypes = implode(', ', $validAttackTypes);

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
                'Doppelganger only triggers on a Power attack.';
            return FALSE;
        }

        // deal with attacks with side effects
        if (in_array('Berserk', $validAttackTypes)) {
            $this->validationMessage = $messageRoot .
                'It is not clear whether you want to perform a berserk attack or not.';
            return FALSE;
        }

        if (in_array('Trip', $validAttackTypes)) {
            $this->validationMessage = $messageRoot .
                'It is not clear whether you want to perform a trip attack or not.';
            return FALSE;
        }

        if ($this->is_fire_assistance_possible($game, $attacker, $defender, $validAttackTypes)) {
            $this->validationMessage = $messageRoot .
                'It is not clear whether you want to fire your attacking die or not.';
            return FALSE;
        }

        return TRUE;
    }

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

    public function type_for_log() {
        return $this->resolvedType;
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        return TRUE;
    }
}
