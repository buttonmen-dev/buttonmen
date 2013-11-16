<?php

class BMAttackPower extends BMAttack {
    public $name = 'Power';
    public $type = 'Power';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        if (count($attackers) != 1 || count($defenders) != 1) {
            return FALSE;
        }

        $attacker = $attackers[0];
        $defender = $defenders[0];

        if (array_key_exists('Shadow', $attacker->skillList)) {
            return FALSE;
        }

        $helpers = $this->collect_helpers($game, $attackers, $defenders);

        $bounds = $this->help_bounds($helpers);

        foreach ($attacker->attack_values($this->type) as $aVal) {

            if ($aVal + $bounds[1] >= $defender->defense_value($this->type)) {

                if ($attacker->is_valid_attacker($this->type, $attackers, $defenders) &&
                    $defender->is_valid_target($this->type, $attackers, $defenders))
                {
                    return TRUE;
                }
            }

        }

        return FALSE;
    }

    // return how much help is needed and who can contribute
    public function calculate_contributions($game, array $attackers, array $defenders) {
        return array(0, array());
    }
}
