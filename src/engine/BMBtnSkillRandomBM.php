<?php
/**
 * BMBtnSkillRandomBM: Code specific to RandomBM
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of RandomBM
 */
class BMBtnSkillRandomBM extends BMBtnSkill {
    public static $hooked_methods = array('specify_recipes');

    public static $die_sizes_soldiers = array(4, 6, 8, 10, 12, 20);

    public static function specify_recipes(array $args) {
        // implement functionality that will be shared by all child classes
        assert(array_key_exists('button', $args) &&
               $args['button'] instanceof BMButton);

        $button = $args['button'];

        if (!empty($button->recipe)) {
            return;
        }

        $button->hasAlteredRecipe = TRUE;
    }
}
