<?php

require_once 'BMUtility.php';
require_once 'BMDie.php';
require_once 'BMAttack.php';

class BMAttackPower extends BMAttack {
    public $name = "Power";
    public $type = 'Power';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, $attackers, $defenders) {
        if (count($attackers) != 1 || count($defenders) != 1) {
            return FALSE;
        }

        $helpers = $this->collect_helpers($game, $attackers, $defenders);

        $bounds = $this->help_bounds($helpers);

        foreach ($attackers[0]->attack_values($this->type) as $aVal) {

            if ($aVal + $bounds[1] >= $defenders[0]->defense_value($this->type)) {

                if ($attackers[0]->valid_attack($this->type, $attackers, $defenders) &&
                    $defenders[0]->valid_target($this->type, $attackers, $defenders))
                {
                    return TRUE;
                }
            }

        }

        return FALSE;
    }

    // return how much help is needed and who can contribute
    public function calculate_contributions($game, $attackers, $defenders) {
        return array(0, array());
    }
}
