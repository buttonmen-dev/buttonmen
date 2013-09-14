<?php

/*
 * BMSkill: Used to modify the operation of BMDie
 *
 * @author: Julian Lighton
 */

class BMSkill {

    public static function expand_skill_string($skillString) {
        if ('' === $skillString) {
            return array();
        }

        $skillLetterArray = str_split($skillString);
        $skillArray = array();

        foreach ($skillLetterArray as $skillLetter) {
            $skillArray[] = BMSkill::expand_skill_letter($skillLetter);
        }

        return $skillArray;
    }

    private static function expand_skill_letter($skillLetter) {
        switch ($skillLetter) {
            case 'p':
                return 'Poison';
            case 's':
                return 'Shadow';
            case '':
                return '';
            default:
                return '';
        }
    }
}

?>
