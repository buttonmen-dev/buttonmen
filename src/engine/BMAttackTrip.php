<?php

class BMAttackTrip extends BMAttack {
    public $type = 'Trip';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        if (count($attackers) != 1 || count($defenders) != 1) {
            return FALSE;
        }

        if ($this->has_disabled_attackers($attackers)) {
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
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

        $returnVal = TRUE;

        $att = $attArray[0];
        $def = $defArray[0];

        if ($att->has_skill('Stealth')) {
            $returnVal = FALSE;
        }

        if (!$att->has_skill('Trip')) {
            $returnVal = FALSE;
        }

        if ($def->has_skill('Stealth')) {
            $returnVal = FALSE;
        }

        return $returnVal;
    }
}
