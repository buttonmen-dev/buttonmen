<?php

class BMAttackShadow extends BMAttackPower {
    public $type = 'Shadow';

    public function validate_attack($game, array $attackers, array $defenders) {
        if (1 != count($attackers) || 1 != count($defenders)) {
            return FALSE;
        }

        if ($this->has_disabled_attackers($attackers)) {
            return FALSE;
        }

        $attacker = $attackers[0];
        $defender = $defenders[0];

        $hasAttackerShadow = $attacker->has_skill('Shadow');
        $hasAttackerQueer = $attacker->has_skill('Queer');
        $isAttackerOdd = (1 == $attacker->value % 2);

        $isDieLargeEnough = $attacker->max >=
                            $defender->defense_value($this->type);

        $attackValueArray = $attacker->attack_values($this->type);
        assert(1 == count($attackValueArray));
        $attackValue = $attackValueArray[0];
        $defenseValue = $defender->defense_value($this->type);
        $isValueSmallEnough = $attackValue <= $defenseValue;

        $canAttDoThisAttack =
            $attacker->is_valid_attacker($this->type, $attackers);
        $isDefValidTarget =
            $defender->is_valid_target($this->type, $defenders);

        return (($hasAttackerShadow ||
                 ($hasAttackerQueer && $isAttackerOdd)) &&
                $isDieLargeEnough &&
                $isValueSmallEnough &&
                $canAttDoThisAttack &&
                $isDefValidTarget);
    }
}
