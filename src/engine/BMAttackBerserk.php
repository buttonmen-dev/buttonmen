<?php

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

        $returnVal = TRUE;

        if (!$att->has_skill('Berserk')) {
            $returnVal = FALSE;
        }

        if ($att->has_skill('Stealth')) {
            $returnVal = FALSE;
        }

        foreach ($defArray as $def) {
            if ($def->has_skill('Stealth')) {
                $returnVal = FALSE;
            }
        }

        return $returnVal;
    }
}
