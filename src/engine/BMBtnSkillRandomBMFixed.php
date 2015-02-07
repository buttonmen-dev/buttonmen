<?php
/**
 * BMBtnSkillRandomBMFixed: Code specific to RandomBMFixed
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of RandomBMFixed, which has
 * fixed random recipes (5 dice, no swing dice, two of them having a single skill
 * chosen independently from c, f, and d)
 */
class BMBtnSkillRandomBMFixed extends BMBtnSkillRandomBM {
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
            array('c', 'f', 'd'),
            2,
            0,
            1
        );
        $button->recipe = parent::generate_recipe($dieSizeArray, $dieSkillLetterArrayArray);

        return TRUE;
    }
}
