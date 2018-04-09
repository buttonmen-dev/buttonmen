<?php
/**
 * BMAttackRush: Code specific to rush attacks
 *
 * @author james
 */

/**
 * This class contains code specific to rush attacks
 */
class BMAttackRush extends BMAttackSpeed {
    /**
     * Type of attack
     *
     * @var string
     */
    public $type = 'Rush';

    /**
     * Determine if specified attack is valid.
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return bool
     */
    public function validate_attack($game, array $attackers, array $defenders) {
        if (2 != count($defenders)) {
            $this->validationMessage = 'There must be exactly two target dice for a ' .
                                       strtolower($this->type). ' attack.';
            return FALSE;
        }

        if (!parent::validate_attack($game, $attackers, $defenders)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Do specific checks for this particular skill for whether skills are compatible
     *
     * Note that the default value for the second parameter has been added to avoid
     * an E_STRICT warning. It should never actually use the default value.
     *
     * @param array $attArray
     * @param array $defArray
     * @return bool
     */
    protected function are_skills_compatible_specific(array $attArray, array $defArray = array()) {
        if (empty($defArray)) {
            // deal with the case where $defArray uses the default value
            //
            // note: this should never happen, but it's here for completeness
            return FALSE;
        }

        if ($attArray[0]->has_skill($this->type)) {
            return TRUE;
        }

        foreach ($defArray as $def) {
            if ($def->has_skill($this->type)) {
                return TRUE;
            }
        }

        $this->validationMessage =
            'A ' . strtolower($this->type) . 'attack needs to involve a ' .
            strtolower($this->type) . ' die.';
        return FALSE;
    }
}
