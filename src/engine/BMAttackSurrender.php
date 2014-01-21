<?php

class BMAttackSurrender extends BMAttack {
    public $type = "Surrender";

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

    protected function are_skills_compatible(array $attArray) {
        return TRUE;
    }
}
