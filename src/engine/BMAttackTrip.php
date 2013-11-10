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

        $attacker = $attackers[0];
        $defender = $defenders[0];

        if (array_key_exists('Trip', $attacker->skillList)) {
            return FALSE;
        }

        if ($attacker->max < $defender->min) {
            return FALSE;
        }

        // james: add something about not being able to attack stealth dice

        return TRUE;
    }
//
//    // return how much help is needed and who can contribute
//    public function calculate_contributions($game, array $attackers, array $defenders) {
//        return array(0, array());
//    }
}

?>
