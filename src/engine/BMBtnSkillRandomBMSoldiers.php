<?php
/**
 * BMBtnSkillRandomBMSoldiers: Code specific to RandomBMSoldiers
 *
 * @author: irilyth
 */

/**
 * This class currently supports the special skills of RandomBMSoldiers, which has
 * a recipe similar to the Soldiers set: 4 regular dice and one X swing die, no skills.
 */
class BMBtnSkillRandomBMSoldiers extends BMBtnSkillRandomBM {
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

        $button = $args['button'];
        $nDice = 5;
        $dieSizeArray = array_merge(
            parent::generate_die_sizes(4),
            array('X')
        );
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
        return 'A recipe similar to the Soldiers set: Four regular dice and one X swing die, no skills.';
    }
}
