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

        $skillCharArray = self::included_skill_char_array();

        $button = $args['button'];
        $nDice = 5;
        $dieSizeArray = parent::generate_die_sizes($nDice);
        $dieIdxIsSwingArray = array();
        $dieSkillLetterArrayArray = parent::generate_die_skills(
            5,
            parent::randomly_select_skills(3, $skillCharArray, FALSE),
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
        return '5 dice, no swing dice, three skills chosen from all ' .
               'existing skills except ' .
               implode(self::excluded_skill_char_array(FALSE)) .
               ', with each skill dealt out twice ' .
               'randomly and independently over all dice.';
    }
}
