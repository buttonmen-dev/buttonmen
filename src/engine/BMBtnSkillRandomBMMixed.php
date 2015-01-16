<?php
/**
 * BMBtnSkillRandomBMMixed: Code specific to RandomBMMixed
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of RandomBMMixed, which has
 * mixed random recipes (5 dice, no swing dice, three skills chosen from all
 * existing skills, with each skill dealt out twice randomly and independently
 * over all dice)
 */
class BMBtnSkillRandomBMMixed extends BMBtnSkillRandomBM {
    public static $hooked_methods = array('specify_recipes');

    public static function specify_recipes(array $args) {
        if (!parent::specify_recipes($args)) {
            return FALSE;
        }

        $button = $args['button'];
        $nDice = 5;
        $dieSizeArray = parent::generate_die_sizes($nDice);
        $dieSkillLetterArrayArray = parent::generate_die_skills(
            5,
            parent::randomly_select_skills(BMSkill::all_skill_chars(), 3),
            0,
            2);
        $button->recipe = parent::generate_recipe($dieSizeArray, $dieSkillLetterArrayArray);

        return TRUE;
    }
}
