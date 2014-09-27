<?php

class BMAttackShadow extends BMAttackPower {
    public $type = 'Shadow';

    public function validate_attack($game, array $attackers, array $defenders) {
        $this->validationMessage = '';

        if (1 != count($attackers)) {
            $this->validationMessage = 'There must be exactly one attacking die for a shadow attack.';
            return FALSE;
        }

        if (1 != count($defenders)) {
            $this->validationMessage = 'There must be exactly one target die for a shadow attack.';
            return FALSE;
        }

        if ($this->has_dizzy_attackers($attackers)) {
            // validation message set within $this->has_dizzy_attackers()
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            // validation message set within $this->are_skills_compatible()
            return FALSE;
        }

        $att = $attackers[0];
        $def = $defenders[0];

        $isDieLargeEnough = $att->max >=
                            $def->defense_value('Shadow');
        if (!$isDieLargeEnough) {
            $this->validationMessage = 'Attacking die size must be at least as large as target die value.';
            return FALSE;
        }

        $attackValueArray = $att->attack_values($this->type);
        assert(1 == count($attackValueArray));
        $attackValue = $attackValueArray[0];
        $defenseValue = $def->defense_value($this->type);
        $isValueSmallEnough = $attackValue <= $defenseValue;
        if (!$isValueSmallEnough) {
            $this->validationMessage = 'Attacking die value must be no larger than the target die value.';
            return FALSE;
        }

        $canAttDoThisAttack =
            $att->is_valid_attacker($this->type, $attackers);
        if (!$canAttDoThisAttack) {
            $this->validationMessage = 'Invalid attacking die';
            return FALSE;
        }

        $isDefValidTarget =
            $def->is_valid_target($this->type, $defenders);
        if (!$isDefValidTarget) {
            $this->validationMessage = 'Invalid target die';
            return FALSE;
        }

        return TRUE;
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
            $this->validationMessage = 'Stealth dice cannot perform shadow attacks.';
            return FALSE;
        }

        if ($def->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot be attacked by shadow attacks.';
            return FALSE;
        }

        if (!($att->has_skill('Shadow') ||
              ($att->has_skill('Queer') && (1 == $att->value % 2)))) {
            $this->validationMessage = 'Only shadow and odd queer dice can perform shadow attacks.';
            return FALSE;
        }

        return $returnVal;
    }
}
