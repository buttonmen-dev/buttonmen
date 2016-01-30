<?php
/**
 * BMBtnSkillRandomBMMixed: Code specific to RandomBMMixed
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of RandomBMMixed, which has
 * mixed random recipes: 5 dice, no swing dice, three skills chosen from all
 * existing skills, with each skill dealt out twice randomly and independently
 * over all dice
 */
class BMBtnSkillRandomBMMixed extends BMBtnSkillRandomBM {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('specify_recipes');

    /**
     * Hooked method applied when specifying recipes
     *
     * @param array $args
     * @return bool
     */
    public static function specify_recipes(array $args) {
        if (!parent::specify_recipes($args)) {
            return FALSE;
        }

        $skillCharArray = array_merge(array_diff(
            BMSkill::all_skill_chars(),
            self::excluded_skill_char_array()
        ));

        $button = $args['button'];
        $nDice = 5;
        $dieSizeArray = parent::generate_die_sizes($nDice);
        $dieSkillLetterArrayArray = parent::generate_die_skills(
            5,
            parent::randomly_select_skills($skillCharArray, 3),
            0,
            2
        );
        $button->recipe = parent::generate_recipe($dieSizeArray, $dieSkillLetterArrayArray);

        return TRUE;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    public static function get_description() {
        return '5 dice, no swing dice, three skills chosen from all ' .
               'existing skills except ' .
               implode(self::excluded_skill_char_array()) .
               ', with each skill dealt out twice ' .
               'randomly and independently over all dice.';
    }

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
