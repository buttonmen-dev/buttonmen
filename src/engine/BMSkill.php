<?php
/**
 * BMSkill: Used to modify the operation of BMDie
 *
 * @author: james
 */

/**
 * This class is the parent class for all die skills
 */
class BMSkill {

    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array();

    /**
     * Convert a string of skill abbreviations into an array of BMSkills
     *
     * @param string $skillString
     * @return array
     */
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

    /**
     * Determine if there is an unimplemented skill in a skill recipe string
     *
     * @param string $skillString
     * @return bool
     */
    public static function unimplemented_skill_in_string($skillString) {
        if ('' === $skillString) {
            return FALSE;
        }

        $skillLetterArray = str_split($skillString);

        foreach ($skillLetterArray as $skillLetter) {
            $lookupSkillLetter = BMSkill::expand_skill_letter($skillLetter);
            if ($lookupSkillLetter == '') {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Convert a skill letter into a full skill name
     *
     * @param string $skillLetter
     * @return string
     */
    protected static function expand_skill_letter($skillLetter) {
        $skillLetter = array_search(
            $skillLetter,
            BMSkill::skill_name_abbreviation_mapping()
        );
        if ($skillLetter) {
            return $skillLetter;
        } else {
            return '';
        }
    }

    /**
     * Abbreviate the name of a skill
     *
     * @param string $fullSkillName
     * @return string
     */
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

    /**
     * All skill name abbreviations
     *
     * @return array
     */
    protected static function skill_name_abbreviation_mapping() {
        return array('Auxiliary'    => '+',
                     'Berserk'      => 'B',
                     'Boom'         => 'b',
                     'Chance'       => 'c',
                     'Doppelganger' => 'D',
                     'Fire'         => 'F',
                     'Focus'        => 'f',
                     'Insult'       => 'I',
                     'Konstant'     => 'k',
                     'Mad'          => '&',
                     'Maximum'      => 'M',
                     'Mighty'       => 'H',
                     'Mood'         => '?',
                     'Morphing'     => 'm',
                     'Null'         => 'n',
                     'Ornery'       => 'o',
                     'Poison'       => 'p',
                     'Queer'        => 'q',
                     'Radioactive'  => '%',
                     'Rage'         => 'G',
                     'Reserve'      => 'r',
                     'Shadow'       => 's',
                     'Slow'         => 'w',
                     'Speed'        => 'z',
                     'Stealth'      => 'd',
                     'Stinger'      => 'g',
                     'TimeAndSpace' => '^',
                     'Trip'         => 't',
// james: Turbo must stay commented so that it remains inactive until the
//        implementation is complete
//                     'Turbo'        => '!',
                     'Value'        => 'v',
                     'Warrior'      => '`',
                     'Weak'         => 'h');
    }

    /**
     * All possible skill characters
     *
     * @return array
     */
    public static function all_skill_chars() {
        return array_values(self::skill_name_abbreviation_mapping());
    }

    /**
     * All possible attack types
     *
     * @return array
     */
    public static function attack_types() {
        return array(// skill related attack types
                     'Boom',
                     'Berserk',
                     'Konstant',
                     'Null',
                     'Shadow',
                     'Speed',
                     'Trip',
                     // standard attack types
                     'Default',
                     'Power',
                     'Skill',
                     'Pass',
                     'Surrender');
    }

    /**
     * Attack types incompatible with this skill type
     *
     * @return array
     */
    public static function incompatible_attack_types() {
        return array();
    }

    /**
     * Comparator needed for skill sorting
     *
     * @param BMSkill $skill1
     * @param BMSkill $skill2
     * @return int
     */
    public static function skill_order_comparator($skill1, $skill2) {
        $skill1Pos = array_search($skill1, self::skill_order_array());
        $skill2Pos = array_search($skill2, self::skill_order_array());

        if (FALSE === $skill1Pos) {
            $skill1Pos = PHP_INT_MAX;
        }

        if (FALSE === $skill2Pos) {
            $skill2Pos = PHP_INT_MAX;
        }

        if ($skill1Pos < $skill2Pos) {
            $cmp = -1;
        } elseif ($skill1Pos > $skill2Pos) {
            $cmp = 1;
        } else {
            $cmp = 0;
        }

        return $cmp;
    }

    /**
     * All skills in order
     *
     * @return array
     */
    protected static function skill_order_array() {
        // fires first
        return array('BMSkillAuxiliary',
                     'BMSkillReserve',
                     'BMSkillChance',
                     'BMSkillFocus',
                     'BMSkillBoom',
                     'BMSkillRage',
                     'BMSkillSpeed',
                     'BMSkillTrip',
                     'BMSkillQueer',
                     'BMSkillBerserk',
                     'BMSkillShadow',
                     'BMSkillSlow',
                     'BMSkillStinger',
                     'BMSkillFire',
                     'BMSkillStealth',
                     'BMSkillOrnery',
                     'BMSkillMood',
                     'BMSkillMad',
                     'BMSkillKonstant',
                     'BMSkillMorphing',
                     'BMSkillRadioactive',
                     'BMSkillWeak',
                     'BMSkillMighty',
                     'BMSkillDoppelganger',
                     'BMSkillValue',
                     'BMSkillPoison',
                     'BMSkillNull',
                     'BMSkillMaximum',
                     'BMSkillTimeAndSpace',
                     'BMSkillWarrior',
                     'BMSkillInsult');
        // fires last
    }

    /**
     * Determine if a skill abbreviation should appear before the die recipe
     *
     * @return bool
     */
    public static function do_print_skill_preceding() {
        return TRUE;
    }

    /**
     * Complete description of skill, packaged for front end
     *
     * @param string $skill
     * @param mixed $interactionList
     * @return array
     */
    public static function describe($skill, $interactionList = NULL) {
        $skillClass = "BMSkill$skill";
        $skillDescription = array(
            'code' => BMSkill::abbreviate_skill_name($skill),
            'description' => $skillClass::get_description(),
            'interacts' => array(),
        );
        $allInteractions = $skillClass::get_interaction_descriptions();
        foreach ($allInteractions as $otherSkill => $interactionDesc) {
            if (is_null($interactionList) || (in_array($otherSkill, $interactionList))) {
                $skillDescription['interacts'][$otherSkill] = $interactionDesc;
            }
        }
        return $skillDescription;
    }

    /**
     * Description of skill
     *
     * Each skill class must override this with a meaningful
     * description of how the skill works
     *
     * @return string
     */
    protected static function get_description() {
        return 'UNDOCUMENTED';
    }

    /**
     * Descriptions of interactions between this skill and other skills
     *
     * Each skill class must override this with an array, indexed
     * by other skill name, whose values are descriptions of
     * interactions between the relevant skills
     *
     * @return array
     */
    protected static function get_interaction_descriptions() {
        return array();
    }

    /**
     * Does this skill prevent the determination of whether a player can win?
     *
     * @return bool
     */
    public static function prevents_win_determination() {
        return FALSE;
    }

    /**
     * Return the single defender die, taking into account that rage may add
     * an extra die that is not captured
     *
     * @param array $defenderArray
     * @param bool $allowOnlyOneDef
     * @return BMDie
     */
    protected static function get_single_defender(array $defenderArray, $allowOnlyOneDef) {
        if ($allowOnlyOneDef && !self::has_single_defender($defenderArray)) {
            throw new LogicException('Exactly one defender expected');
        }

        $defender = NULL;

        foreach ($defenderArray as &$def) {
            if ($def->captured) {
                $defender = &$def;
                break;
            }
        }

        if (is_null($defender)) {
            throw new LogicException('No defender found');
        }

        return $defender;
    }

    /**
     * Checks whether there is exactly one defender
     *
     * @param array $defenderArray
     * @return bool
     */
    protected static function has_single_defender(array $defenderArray) {
        // exclude new defenders that have been added because of Rage
        $defCount = 0;
        foreach ($defenderArray as &$def) {
            if (!($def->has_flag('IsRageTargetReplacement'))) {
                $defCount++;
            }
        }

        return (1 == $defCount);
    }
}
