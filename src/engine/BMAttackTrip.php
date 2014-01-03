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

        $attacker = $attackers[0];
        $defender = $defenders[0];

        $doesAttackerHaveTrip = $attacker->has_skill('Trip');
        $isDieLargeEnough = ($attacker->max >= $defender->min);

        // james: add something about not being able to attack stealth dice

        return ($doesAttackerHaveTrip &&
                $isDieLargeEnough);
    }
}

?>
