<?php

class BMAttackPower extends BMAttack {
    public $type = 'Power';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        $this->validationMessage = '';

        if (1 != count($attackers)) {
            $this->validationMessage = 'There must be exactly one attacking die for a power attack.';
            return FALSE;
        }

        if (1 != count($defenders)) {
            $this->validationMessage = 'There must be exactly one target die for a power attack.';
            return FALSE;
        }

        if ($this->has_dizzy_attackers($attackers)) {
            $this->validationMessage = 'Dizzy dice cannot be used as attacking dice.';
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            // validation message set within $this->are_skills_compatible()
            return FALSE;
        }

        $helpers = $this->collect_helpers($game, $attackers, $defenders);

        $bounds = $this->help_bounds($helpers);

        $att = $attackers[0];
        $def = $defenders[0];

        foreach ($att->attack_values($this->type) as $aVal) {
            $isValLargeEnough = $aVal + $bounds[1] >= $def->defense_value($this->type);
            $isValidAttacker = $att->is_valid_attacker($this->type, $attackers);
            $isValidTarget = $def->is_valid_target($this->type, $defenders);
            
            if (!$isValLargeEnough) {
                $this->validationMessage = 'Attacker die value must be at least as large as target die value.';
            } elseif (!$isValidAttacker) {
                $this->validationMessage = 'Invalid attacker die';
            } elseif (!$isValidTarget) {
                $this->validationMessage = 'Invalid target die';
            } else {
                $this->validationMessage = '';
                return TRUE;
            }
        }

        return FALSE;
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

        if ($att->has_skill('Shadow')) {
            $this->validationMessage = 'Shadow dice cannot perform power attacks.';
            return FALSE;
        }
                
        if ($att->has_skill('Konstant')) {
            $this->validationMessage = 'Konstant dice cannot perform power attacks.';
            return FALSE;
        }

        if ($att->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot perform power attacks.';
            return FALSE;
        }

        if ($att->has_skill('Queer') && (1 == $att->value % 2)) {
            $this->validationMessage = 'Odd queer dice cannot perform power attacks.';
            return FALSE;
        }

        if ($def->has_Skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot be attacked by power attacks.';
            return FALSE;
        }

        return TRUE;
    }
}
