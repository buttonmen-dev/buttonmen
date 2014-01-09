<?php

class BMAttackPower extends BMAttack {
    public $type = 'Power';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        $attCountValid = count($attackers) == 1;
        $defCountValid = count($defenders) == 1;
        $attAllEnabled = !$this->has_disabled_attackers($attackers);

        $inputVarValid = $attCountValid && $defCountValid && $attAllEnabled;

        if (!$inputVarValid) {
            return FALSE;
        }

        $attacker = $attackers[0];
        $defender = $defenders[0];

        $attHasShadow = $attacker->has_skill('Shadow');
        $attHasKonstant = $attacker->has_skill('Konstant');
        $attHasOddQueer = $attacker->has_skill('Queer') &&
                          (1 == $attacker->value % 2);
        $attValid = !$attHasShadow && !$attHasKonstant && !$attHasOddQueer;

        if (!$attValid) {
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
}
