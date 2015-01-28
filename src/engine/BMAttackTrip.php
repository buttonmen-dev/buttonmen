<?php
/**
 * BMAttackTrip: Code specific to trip attacks
 *
 * @author james
 */

/**
 * This class contains code specific to trip attacks
 */
class BMAttackTrip extends BMAttack {
    public $type = 'Trip';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        $this->validationMessage = '';

        if (count($attackers) != 1) {
            $this->validationMessage = 'There must be exactly one attacking die for a trip attack.';
            return FALSE;
        }

        if (count($defenders) != 1) {
            $this->validationMessage = 'There must be exactly one target die for a trip attack.';
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

        if ($this->is_disabled_by_konstant($attackers, $defenders)) {
            // validation message set within $this->is_disabled_by_konstant()
            return FALSE;
        }

        $attacker = $attackers[0];
        $defender = $defenders[0];

        $isDieLargeEnough = ($attacker->max >= $defender->min);

        return $isDieLargeEnough;
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attArray must have one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $att = $attArray[0];
        $def = $defArray[0];

        if ($att->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot perform trip attacks.';
            return FALSE;
        }

        if ($att->has_skill('Warrior')) {
            $this->validationMessage = 'Warrior dice cannot perform trip attacks.';
            return FALSE;
        }

        if (!$att->has_skill('Trip')) {
            $this->validationMessage = 'Dice without trip cannot perform trip attacks.';
            return FALSE;
        }

        if ($def->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot be the target of trip attacks.';
            return FALSE;
        }

        if ($def->has_skill('Warrior')) {
            $this->validationMessage = 'Warrior dice cannot be attacked.';
            return FALSE;
        }

        return TRUE;
    }

    protected function is_disabled_by_konstant($attArray, $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attack must have one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $att = $attArray[0];
        $def = $defArray[0];

        if ($att->has_skill('Konstant') && ($att->value < $def->min)) {
            $this->validationMessage = 'The attacking die cannot roll high enough to capture the target die';
            return TRUE;
        }

        if ($def->has_skill('Konstant') && ($att->max < $def->value)) {
            $this->validationMessage = 'The attacking die cannot roll high enough to capture the target die';
            return TRUE;
        }

        if ($att->has_skill('Konstant') &&
            $def->has_skill('Konstant') &&
            ($att->value < $def->value)) {
            $this->validationMessage = 'The attacking die cannot roll high enough to capture the target die';
            return TRUE;
        }

        return FALSE;
    }
}
