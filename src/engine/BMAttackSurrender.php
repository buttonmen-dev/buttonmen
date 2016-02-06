<?php
/**
 * BMAttackSurrender: Code specific to surrender attacks
 *
 * @author james
 */

/**
 * This class contains code specific to surrender attacks
 */
class BMAttackSurrender extends BMAttackPass {
    /**
     * Type of attack
     *
     * @var string
     */
    public $type = "Surrender";

    /**
     * Determine if specified attack is valid.
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return bool
     */
    public function validate_attack($game, array $attackers, array $defenders) {
        $isValid = parent::validate_attack($game, $attackers, $defenders);

        if (!$isValid) {
            $this->validationMessage = 'Please deselect all dice before surrendering.';
        }

        return $isValid;
    }
}
