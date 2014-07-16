<?php

class BMAttackDefault extends BMAttack {
    public $type = "Default";

    public function find_attack($game) {
        return $this->validate_attack(
            $game,
            $this->attackerAttackDieArray,
            $game->defenderAttackDieArray
        );
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        $this->validationMessage = '';

        $possibleAttackTypeArray = $game->valid_attack_types();
        $validAttackTypeArray = array();

        foreach ($possibleAttackTypeArray as $attackType) {
            $attack = BMAttack::get_instance($attackType);
            if ($attack->validate_attack($game, $attackers, $defenders)) {
                $validAttackTypeArray[] = $attackType;
            }
        }

        switch (count($validAttackTypeArray)) {
            case 1:
                return TRUE;
            case 0:
                $this->validationMessage = 'There is no valid attack corresponding to the dice selected.';
                return FALSE;
            default:
                $this->validationMessage = 'Default attack is ambiguous.';
                return FALSE;
        }
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        return TRUE;
    }
}
