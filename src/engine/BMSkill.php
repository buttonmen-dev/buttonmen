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
        $skillLetter = array_search($skillLetter,
                                    BMSkill::skill_name_abbreviation_mapping());
        if ($skillLetter) {
            return $skillLetter;
        } else {
            return '';
        }
    }

    public static function abbreviate_skill_name($fullSkillName) {
        if (0 === strpos($fullSkillName, 'BMSkill')) {
            $skillName = substr($fullSkillName, 7);
        } else {
            $skillName = $fullSkillName;
        }

        $skill_mapping = BMSkill::skill_name_abbreviation_mapping();
        if (array_key_exists($skillName, $skill_mapping)) {
            return $skill_mapping[$skillName];
        } else {
            return '';
        }
    }

    protected static function skill_name_abbreviation_mapping() {
        return array('Berserk' => 'B',
                     'Null'    => 'n',
                     'Poison'  => 'p',
                     'Shadow'  => 's',
                     'Speed'   => 'z',
                     'Trip'    => 't',
                     'Value'   => 'v');
    }

    public static function attack_types() {
        return array(// skill related attack types
                     'Berserk',
                     'Null',
                     'Shadow',
                     'Speed',
                     'Trip',
                     // standard attack types
                     'Power',
                     'Skill',
                     'Pass');
    }
}

?>
