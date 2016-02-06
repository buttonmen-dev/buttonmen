<?php
/**
 * BMAttackBoom: Code specific to boom attacks
 *
 * @author james
 */

/**
 * This class contains code specific to boom attacks
 */
class BMAttackBoom extends BMAttack {
    /**
     * Type of attack
     *
     * @var string
     */
    public $type = 'Boom';

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
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
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

        if (count($attackers) != 1) {
            $this->validationMessage = 'There must be exactly one attacking die for a boom attack.';
            return FALSE;
        }

        if (count($defenders) != 1) {
            $this->validationMessage = 'There must be exactly one target die for a boom attack.';
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

        return TRUE;
    }

    /**
     * Check if skills are compatible with this type of attack.
     *
     * @param array $attArray
     * @param array $defArray
     * @return bool
     */
    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attArray must have one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $att = $attArray[0];
        $def = $defArray[0];

        if ($att->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot perform boom attacks.';
            return FALSE;
        }

        if ($att->has_skill('Warrior')) {
            $this->validationMessage = 'Warrior dice cannot perform boom attacks.';
            return FALSE;
        }

        if (!$att->has_skill('Boom')) {
            $this->validationMessage = 'Dice without boom cannot perform boom attacks.';
            return FALSE;
        }

        if ($def->has_skill('Warrior')) {
            $this->validationMessage = 'Warrior dice cannot be attacked.';
            return FALSE;
        }

        return TRUE;
    }
}
