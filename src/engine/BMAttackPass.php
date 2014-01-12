<?php

class BMAttackPass extends BMAttack {
    public $type = "Pass";

    public function find_attack($game) {
        return $this->validate_attack(
            $game,
            $this->validDice,
            $game->defenderAttackDieArray
        );
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        return (empty($attackers) && empty($defenders));
    }
}
