<?php
/**
 * BMAttackPower: code specific to power attacks
 *
 * @author Julian
 */

/**
 * This class contains code specific to power attacks
 */
class BMAttackPower extends BMAttack {
    /**
     * Type of attack
     *
     * @var string
     */
    public $type = 'Power';

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
     * @param array $args
     * @return bool
     */
    public function validate_attack($game, array $attackers, array $defenders, array $args = array()) {
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
            // validation message set within $this->has_dizzy_attackers()
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            // validation message set within $this->are_skills_compatible()
            return FALSE;
        }

        $helpValue = NULL;

        if (array_key_exists('helpValue', $args)) {
            $helpValue = $args['helpValue'];
        }

        if (is_null($helpValue)) {
            $bounds = $this->help_bounds_specific($game, $attackers, $defenders);
        } else {
            $bounds = array($helpValue, $helpValue);
        }

        $att = $attackers[0];
        $def = $defenders[0];

        foreach ($att->attack_values($this->type) as $aVal) {
            $validationArray = array();
            // james: 'isDieLargeEnough' is required for the case of fired-up dice
            $validationArray['isDieLargeEnough'] =
                $att->max >= $def->defense_value($this->type);
            $validationArray['isValLargeEnough'] =
                $aVal + $bounds[1] >= $def->defense_value($this->type);
            // james: 'isIncreasedValueValid' is required for the case of fired-up dice
            if ($helpValue) {
                $validationArray['isIncreasedValueValid'] =
                    ($aVal + $helpValue <= $att->max);
            } else {
                $validationArray['isIncreasedValueValid'] = TRUE;
            }
            $validationArray['isValidAttacker'] =
                $att->is_valid_attacker($attackers);
            $validationArray['isValidTarget'] =
                $def->is_valid_target($defenders);

            $this->validationMessage =
                $this->get_validation_message($validationArray, $helpValue);

            if (empty($this->validationMessage)) {
                return TRUE;
            }
        }

        return FALSE;
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

        // attacker skills
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

        if ($att->has_skill('Fire')) {
            $this->validationMessage = 'Fire dice cannot perform power attacks.';
            return FALSE;
        }

        if ($att->has_skill('Queer') && (1 == $att->value % 2)) {
            $this->validationMessage = 'Odd queer dice cannot perform power attacks.';
            return FALSE;
        }

        if ($att->has_skill('Warrior')) {
            $this->validationMessage = 'Warrior dice cannot perform power attacks.';
            return FALSE;
        }

        // defender skills
        if ($def->has_Skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot be attacked by power attacks.';
            return FALSE;
        }

        if ($def->has_Skill('Warrior')) {
            $this->validationMessage = 'Warrior dice cannot be attacked.';
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Determine error message for incorrect power attack
     *
     * @param array $validationArray
     * @param int $helpValue
     * @return string
     */
    protected function get_validation_message($validationArray, $helpValue) {
        if (!$validationArray['isDieLargeEnough']) {
            return 'Attacking die size must be at least as large as target die value';
        }

        if (!$validationArray['isValLargeEnough']) {
            if ($helpValue) {
                return 'Fire dice not turned down enough.';
            } else {
                return 'Attacking die value must be at least as large as target die value';
            }
        }

        if (!$validationArray['isIncreasedValueValid']) {
            if (1 == $helpValue) {
                $helpValueUnit = 'point';
            } else {
                $helpValueUnit = 'points';
            }
            return 'Attacker cannot be fired up by ' .
                   $helpValue . ' ' . $helpValueUnit . '.';
        }

        if (!$validationArray['isValidAttacker']) {
            return 'Invalid attacking die';
        }

        if (!$validationArray['isValidTarget']) {
            return 'Invalid target die';
        }

        return '';
    }
}
