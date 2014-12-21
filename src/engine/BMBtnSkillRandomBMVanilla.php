<?php
/**
 * BMBtnSkillRandomBMVanilla: Code specific to RandomBMVanilla
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of RandomBMVanilla, which has
 * vanilla random recipes (5 dice, no swing dice, no skills)
 */
class BMBtnSkillRandomBMVanilla extends BMBtnSkillRandomBM {
    public static $hooked_methods = array('specify_recipes');

    public static function specify_recipes(array $args) {
        parent::specify_recipes($args);

        $button = $args['button'];

        if (!$button->hasAlteredRecipe) {
            return;
        }

        $dieRecipeArray = array_fill(0, 5, NULL);
        $validDieSizeArray = parent::$die_sizes_soldiers;
        foreach ($dieRecipeArray as &$dieRecipe) {
            $dieRecipe = '(' .
                         $validDieSizeArray[bm_rand(0, count($validDieSizeArray) - 1)] .
                         ')';
        }

        $button->recipe = implode(' ', $dieRecipeArray);
    }
}
