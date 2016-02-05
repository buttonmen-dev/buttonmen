<?php
/**
 * BMBtnSkillRandomBMVanilla: Code specific to RandomBMVanilla
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of RandomBMVanilla, which has
 * vanilla random recipes: 5 dice, no swing dice, no skills
 */
class BMBtnSkillRandomBMVanilla extends BMBtnSkillRandomBM {
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
        $dieSkillLettersArray = array_fill(0, $nDice, NULL);

        $button->recipe = parent::generate_recipe($dieSizeArray, $dieSkillLettersArray);
        return TRUE;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    public static function get_description() {
        return '5 dice, no swing dice, no skills.';
    }
}
