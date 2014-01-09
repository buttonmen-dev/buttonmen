<?php

class BMAttackPower extends BMAttack {
    public $type = 'Power';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        if (1 != count($attackers) ||
            1 != count($defenders) ||
            $this->has_disabled_attackers($attackers)) {
            return FALSE;
        }

        $attacker = $attackers[0];
        $defender = $defenders[0];

        if (!BMAttackPower::is_attacker_valid($attacker)) {
            return FALSE;
        }

        $helpers = $this->collect_helpers($game, $attackers, $defenders);

        $bounds = $this->help_bounds($helpers);

        foreach ($attacker->attack_values($this->type) as $aVal) {
            $isValLargeEnough =
                $aVal + $bounds[1] >= $defender->defense_value($this->type);
            $isValidAttacker = $attacker->is_valid_attacker($this->type, $attackers);
            $isValidTarget = $defender->is_valid_target($this->type, $defenders);

            $isValidAttack = $isValLargeEnough && $isValidAttacker && $isValidTarget;

            if ($isValidAttack) {
                return TRUE;
            }
        }

        return FALSE;
    }

    protected static function is_attacker_valid($attacker) {
        $hasAttShadow = $attacker->has_skill('Shadow');
        $hasAttKonstant = $attacker->has_skill('Konstant');
        $hasAttOddQueer = $attacker->has_skill('Queer') &&
                          (1 == $attacker->value % 2);

        return !$hasAttShadow && !$hasAttKonstant && !$hasAttOddQueer;
    }
}
