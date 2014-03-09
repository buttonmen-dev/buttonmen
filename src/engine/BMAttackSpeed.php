<?php

class BMAttackSpeed extends BMAttack {
    public $type = 'Speed';

    public function validate_attack($game, array $attackers, array $defenders) {
        if (1 != count($attackers) || count($defenders) < 1) {
            return FALSE;
        }

        if ($this->has_dizzy_attackers($attackers)) {
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            return FALSE;
        }

        $attacker = $attackers[0];

        $defenderSum = 0;
        foreach ($defenders as $defender) {
            $defenderSum += $defender->value;
        }
        $areValuesEqual = $attacker->value == $defenderSum;

        $canAttDoThisAttack =
            $attacker->is_valid_attacker($this->type, $attackers);
        $areDefValidTargets = TRUE;
        foreach ($defenders as $defender) {
            if (!($defender->is_valid_target($this->type, $defenders))) {
                $areDefValidTargets = FALSE;
                break;
            }
        }

        return ($areValuesEqual &&
                $canAttDoThisAttack &&
                $areDefValidTargets);
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

        $returnVal = TRUE;

        $att = $attArray[0];

        if ($att->has_skill('Stealth')) {
            $returnVal =  FALSE;
        }

        if (!$att->has_skill('Speed')) {
            $returnVal = FALSE;
        }

        foreach ($defArray as $def) {
            if ($def->has_skill('Stealth')) {
                $returnVal = FALSE;
            }
        }

        return $returnVal;
    }
}
