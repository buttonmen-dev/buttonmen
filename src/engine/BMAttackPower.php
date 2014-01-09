<?php

class BMAttackPower extends BMAttack {
    public $type = 'Power';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        if (count($attackers) != 1 ||
            count($defenders) != 1 ||
            $this->has_disabled_attackers($attackers)) {
            return FALSE;
        }

        $attacker = $attackers[0];
        $defender = $defenders[0];

        if ($attacker->has_skill('Shadow') ||
            $attacker->has_skill('Konstant') ||
            ($attacker->has_skill('Queer') && (1 == $attacker->value % 2))) {
            return FALSE;
        }

        $helpers = $this->collect_helpers($game, $attackers, $defenders);

        $bounds = $this->help_bounds($helpers);

        foreach ($attacker->attack_values($this->type) as $aVal) {
            if (($aVal + $bounds[1] >= $defender->defense_value($this->type)) &&
                $attacker->is_valid_attacker($this->type, $attackers) &&
                $defender->is_valid_target($this->type, $defenders)) {
                    return TRUE;
            }
        }

        return FALSE;
    }
}
