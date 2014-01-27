<?php

class BMAttackShadow extends BMAttackPower {
    public $type = 'Shadow';

    public function validate_attack($game, array $attackers, array $defenders) {
        if (1 != count($attackers) ||
            1 != count($defenders) ||
            $this->has_disabled_attackers($attackers)) {
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            return FALSE;
        }

        $att = $attackers[0];
        $def = $defenders[0];

        $isDieLargeEnough = $att->max >=
                            $def->defense_value('Shadow');

        $attackValueArray = $att->attack_values($this->type);
        assert(1 == count($attackValueArray));
        $attackValue = $attackValueArray[0];
        $defenseValue = $def->defense_value($this->type);
        $isValueSmallEnough = $attackValue <= $defenseValue;

        $canAttDoThisAttack =
            $att->is_valid_attacker($this->type, $attackers);
        $isDefValidTarget =
            $def->is_valid_target($this->type, $defenders);

        $isValidAttack = $isDieLargeEnough &&
                         $isValueSmallEnough &&
                         $canAttDoThisAttack &&
                         $isDefValidTarget;

        return $isValidAttack;
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attArray must have one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $att = $attArray[0];
        $def = $defArray[0];

        $returnVal = TRUE;

        if ($att->has_skill('Stealth')) {
            return FALSE;
        }

        if (!($att->has_skill('Shadow') ||
            ($att->has_skill('Queer') && (1 == $att->value % 2)))
        ) {
            return FALSE;
        }

        return $returnVal;
    }
}
