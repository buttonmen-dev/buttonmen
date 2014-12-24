<?php
/**
 * BMAttackSpeed: Code specific to speed attacks
 *
 * @author james
 */

/**
 * This class contains code specific to speed attacks
 */
class BMAttackSpeed extends BMAttack {
    public $type = 'Speed';

    public function validate_attack($game, array $attackers, array $defenders) {
        $this->validationMessage = '';

        if (1 != count($attackers)) {
            $this->validationMessage = 'There must be exactly one attacking die for a ' .
                                       strtolower($this->type). ' attack.';
            return FALSE;
        }

        if (count($defenders) < 1) {
            $this->validationMessage = 'There must be at least one target die for a ' .
                                       strtolower($this->type). ' attack.';
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

        $attacker = $attackers[0];

        $defenderSum = 0;
        foreach ($defenders as $defender) {
            $defenderSum += $defender->value;
        }
        $areValuesEqual = $attacker->value == $defenderSum;
        if (!$areValuesEqual) {
            $this->validationMessage = 'Target die values do not sum up to attacking die value.';
            return FALSE;
        }

        $canAttDoThisAttack =
            $attacker->is_valid_attacker($attackers);
        if (!$canAttDoThisAttack) {
            $this->validationMessage = 'Invalid attacking die';
            return FALSE;
        }

        $areDefValidTargets = TRUE;
        foreach ($defenders as $defender) {
            if (!($defender->is_valid_target($defenders))) {
                $areDefValidTargets = FALSE;
                break;
            }
        }
        if (!$areDefValidTargets) {
            $this->validationMessage = 'Invalid target die';
            return FALSE;
        }

        return TRUE;
    }

    public function find_attack($game) {
        return $this->search_onevmany(
            $game,
            $game->attackerAllDieArray,
            $game->defenderAllDieArray
        );
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attArray must have one element.');
        }

        if (0 == count($defArray)) {
            throw new InvalidArgumentException('defArray must be nonempty.');
        }

        $att = $attArray[0];

        if ($att->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot perform ' .
                                       strtolower($this->type). ' attacks.';
            return FALSE;
        }

        if (!$att->has_skill($this->type)) {
            $this->validationMessage = 'Dice without ' .
                                       strtolower($this->type). ' cannot perform ' .
                                       strtolower($this->type). ' attacks.';
            return FALSE;
        }

        foreach ($defArray as $def) {
            if ($def->has_skill('Stealth')) {
                $this->validationMessage = 'Stealth dice cannot be attacked by ' .
                                           strtolower($this->type). ' attacks.';
                return FALSE;
            }
        }

        return TRUE;
    }
}
