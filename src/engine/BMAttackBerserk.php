<?php
/**
 * BMAttackBerserk: Code specific to berserk attacks
 *
 * @author james
 */

/**
 * This class contains code specific to berserk attacks
 */
class BMAttackBerserk extends BMAttackSpeed {
    public $type = 'Berserk';

    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attArray must have one element.');
        }

        if (0 == count($defArray)) {
            throw new InvalidArgumentException('defArray must be nonempty.');
        }

        $att = $attArray[0];

        if (!$att->has_skill('Berserk')) {
            $this->validationMessage = 'Dice without berserk cannot perform berserk attacks.';
            return FALSE;
        }

        if ($att->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot perform berserk attacks.';
            return FALSE;
        }

        foreach ($defArray as $def) {
            if ($def->has_skill('Stealth')) {
                $this->validationMessage = 'Stealth dice cannot be attacked by berserk attacks.';
                return FALSE;
            }
        }

        return TRUE;
    }
}
