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
        if (!parent::specify_recipes($args)) {
            return FALSE;
        }

        $button = $args['button'];
        $nDice = 5;
        $dieSizeArray = array_fill(0, $nDice, NULL);
        $dieRecipeArray = array_fill(0, $nDice, NULL);
        $validDieSizeArray = parent::$die_sizes_soldiers;
        $nValidDieSizes = count($validDieSizeArray);

        foreach ($dieSizeArray as &$dieSize) {
            $dieSize = $validDieSizeArray[bm_rand(0, $nValidDieSizes - 1)];
        }

        sort($dieSizeArray, SORT_NUMERIC);

        foreach ($dieRecipeArray as $dieIdx => &$dieRecipe) {
            $dieRecipe = '(' .
                         $dieSizeArray[$dieIdx] .
                         ')';
        }

        $button->recipe = implode(' ', $dieRecipeArray);

        return TRUE;
    }
}
