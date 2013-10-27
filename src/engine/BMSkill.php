<?php

/*
 * BMSkill: Used to modify the operation of BMDie
 *
 * @author: James Ong
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
            case 'n':
                return 'Null';
            case 'p':
                return 'Poison';
            case 's':
                return 'Shadow';
            case 'v':
                return 'Value';
            case 'z':
                return 'Speed';
            case '':
                return '';
            default:
                return '';
        }
    }

    public static function abbreviate_skill_name($fullSkillName) {
        if (0 === strpos($fullSkillName, 'BMSkill')) {
            $skillName = substr($fullSkillName, 7);
        } else {
            $skillName = $fullSkillName;
        }

        switch ($skillName) {
            case 'Null':
                return 'n';
            case 'Poison':
                return 'p';
            case 'Shadow':
                return 's';
            case 'Speed':
                return 'z';
            case 'Value':
                return 'v';
            default:
                return '';
        }
    }
}

?>
