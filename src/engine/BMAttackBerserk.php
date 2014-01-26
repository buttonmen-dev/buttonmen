<?php

class BMAttackBerserk extends BMAttackSpeed {
    public $type = 'Berserk';

    protected function are_skills_compatible(array $attArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attArray must have one element.');
        }

        $att = $attArray[0];

        if ($att->has_skill('Stealth')) {
            return FALSE;
        }

        if ($att->has_skill('Berserk')) {
            return TRUE;
        }

        return FALSE;
    }
}
