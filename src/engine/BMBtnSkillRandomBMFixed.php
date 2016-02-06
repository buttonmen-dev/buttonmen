<?php
/**
 * BMBtnSkillRandomBMFixed: Code specific to RandomBMFixed
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of RandomBMFixed, which has
 * fixed random recipes: 5 dice, no swing dice, two of them having a single skill
 * chosen from c, f, and d (the same skill on both)
 */
class BMBtnSkillRandomBMFixed extends BMBtnSkillRandomBM {
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

        $button = $args['button'];
        $nDice = 5;
        $dieSizeArray = parent::generate_die_sizes($nDice);
        $dieSkillLetterArrayArray = parent::generate_die_skills(
            5,
            parent::randomly_select_skills(1, array('c', 'f', 'd')),
            0,
            2,
            1
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
        return '5 dice, no swing dice, two of them having a single skill ' .
               'chosen from c, f, and d (the same skill on both).';
    }
}
