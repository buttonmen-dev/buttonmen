<?php
/**
 * BMBtnSkillRandomBMAnime: Code specific to RandomBMAnime
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of RandomBMAnime, which has
 * fixed random recipes (4 normal dice and 4 reserve dice, chosen from standard die sizes)
 */
class BMBtnSkillRandomBMAnime extends BMBtnSkillRandomBM {
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
        $dieSizeArray = array_merge(
            parent::generate_die_sizes(4),
            parent::generate_die_sizes(4)
        );
        $dieSkillLetterArrayArray = array_merge(
            array_fill(0, 4, array()),
            array_fill(0, 4, array('r' => 'r'))
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
        return '4 normal dice and 4 reserve dice, chosen from standard die sizes.';
    }
}
