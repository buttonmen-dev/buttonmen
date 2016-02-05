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
    /**
     * Type of attack
     *
     * @var string
     */
    public $type = "Pass";

    /**
     * Determine if there is at least one valid attack of this type from
     * the set of all possible attackers and defenders.
     *
     * If $includeOptional is FALSE, then optional attacks are excluded.
     * These include skill attacks involving warrior dice.
     *
     * @param BMGame $game
     * @param bool $includeOptional
     * @return bool
     */
    public function find_attack($game, $includeOptional = TRUE) {
        return $this->validate_attack(
            $game,
            $this->validDice,
            $game->defenderAttackDieArray
        );
    }

    /**
     * Determine if specified attack is valid.
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return bool
     */
    public function validate_attack($game, array $attackers, array $defenders) {
        $this->validationMessage = '';

        $isValid = empty($attackers) && empty($defenders);

        if (!$isValid) {
            $this->validationMessage = 'Please deselect all dice before passing.';
        }

        return $isValid;
    }

    /**
     * Check if skills are compatible with this type of attack.
     *
     * @param array $attArray
     * @param array $defArray
     * @return bool
     */
    protected function are_skills_compatible(array $attArray, array $defArray) {
        return TRUE;
    }
}
