<?php

require_once 'BMUtility.php';
require_once 'BMDie.php';
require_once 'BMAttack.php';
require_once 'BMAttackPower.php';

class BMAttackShadow extends BMAttackPower {
    public $name = "Shadow";
    public $type = "Shadow";

    public function validate_attack($game, $attackers, $defenders) {
        if (count($attackers) != 1 || count($defenders) != 1) {
            return FALSE;
        }


        $helpers = $this->collect_helpers($game, $attackers, $defenders);

        $bounds = $this->help_bounds($helpers);

        foreach ($attackers[0]->attack_values($this->type) as $aVal) {

            if ($defenders[0]->defense_value($this->type) <= $attackers[0]->max &&
                ($defenders[0]->defense_value($this->type) >= $aVal ||
                 $defenders[0]->defense_value($this->type) >= $aVal + $bounds[0])) {

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
