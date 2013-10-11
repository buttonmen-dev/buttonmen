<?php

class BMAttackShadow extends BMAttackPower {
    public $name = 'Shadow';
    public $type = 'Shadow';

    public function validate_attack($game, array $attackers, array $defenders) {
        return (1 == count($attackers) &&
                1 == count($defenders) &&
                $attackers[0]->max >= $defenders[0]->defense_value($this->type) &&
                $attackers[0]->attack_values($this->type)[0] <= $defenders[0]->defense_value($this->type) &&
                $attackers[0]->valid_attack($this->type, $attackers, $defenders) &&
                $defenders[0]->valid_target($this->type, $attackers, $defenders));
    }
}
