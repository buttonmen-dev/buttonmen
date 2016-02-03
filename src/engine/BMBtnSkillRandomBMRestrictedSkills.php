<?php
/**
 * BMBtnSkillRandomBMRestrictedSkills: Code specific to RandomBMRestrictedSkills
 *
 * @author: james
 */

/**
 * This class provides the common infrastructure for a number of RandomBM* buttons
 * which require the set of available skills to be restricted
 */
class BMBtnSkillRandomBMRestrictedSkills extends BMBtnSkillRandomBM {
    /**
     * Array containing excluded die skill names
     *
     * @return array
     */
    protected static function excluded_skill_array() {
        // Actively exclude possibly problematic skills
        // The current selection is conservative, and should be whittled down in time,
        // after we deal with bugs that arise from strange skill combinations
        return array(
            'Auxiliary', 'Reserve', 'Warrior', // game-level skills
            'Doppelganger', 'Mad', 'Mood',
            'Morphing', 'Radioactive', // recipe-changing skills
            'Fire', // skills that add an extra step to attacks
            'Slow', // skills excluded because they're no fun
        );
    }

    /**
     * Array containing excluded die skill characters
     *
     * @return array
     */
    protected static function excluded_skill_char_array() {
        $skillCharArray = array_map(
            'BMSkill::abbreviate_skill_name',
            self::excluded_skill_array()
        );
        sort($skillCharArray);
        return $skillCharArray;
    }
}
