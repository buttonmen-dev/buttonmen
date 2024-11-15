<?php
/**
 * BMBtnSkillRandomBMMonoskill: Code specific to RandomBMMonoskill
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of RandomBMMonoskill, which has
 * 4 normal dice and a swing die, and 1 skill appearing a total of 2 times on various dice.
 */
class BMBtnSkillRandomBMMonoskill extends BMBtnSkillRandomBM {
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
     * @return boolean
     */
    public static function specify_recipes(array $args) {
        if (!parent::specify_recipes($args)) {
            return FALSE;
        }

        $skillCharArray = self::included_skill_char_array();

        $button = $args['button'];
        $dieSizeArray = array_merge(
            parent::generate_die_sizes(4),
            parent::randomly_select_swing_types()
        );
        $dieIdxIsSwingArray = array(4);
        $dieSkillLetterArrayArray = parent::generate_die_skills(
            5,
            parent::randomly_select_skills(1, $skillCharArray, TRUE),
            0,
            2,
            $dieIdxIsSwingArray
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
        return 'Four regular dice and one swing die, and 1 skill appearing a total of 2 times on various dice.';
    }
}
