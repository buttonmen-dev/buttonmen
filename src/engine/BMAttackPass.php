<?php
/**
 * BMAttackPass: Code specific to pass attacks
 *
 * @author james
 */

/**
 * This class contains code specific to pass attacks
 */
class BMAttackPass extends BMAttack {
    public $type = "Pass";

    public function find_attack($game, $includeOptional = TRUE) {
        return $this->validate_attack(
            $game,
            $this->validDice,
            $game->defenderAttackDieArray
        );
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        $this->validationMessage = '';

        $isValid = empty($attackers) && empty($defenders);

        if (!$isValid) {
            $this->validationMessage = 'Please deselect all dice before passing.';
        }

        return $isValid;
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        return TRUE;
    }
}
