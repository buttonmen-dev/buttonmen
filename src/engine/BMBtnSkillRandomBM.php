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
        if (!array_key_exists('button', $args) ||
            !($args['button'] instanceof BMButton)) {
            throw new LogicException('specify_recipes requires a BMButton input argument');
        }

        $button = $args['button'];

        if (!empty($button->recipe)) {
            return FALSE;
        }

        $button->hasAlteredRecipe = TRUE;
        return TRUE;
    }

    protected static function generate_die_sizes($nDice) {
        $dieSizeArray = array_fill(0, $nDice, NULL);
        $validDieSizeArray = self::$die_sizes_soldiers;
        $nValidDieSizes = count($validDieSizeArray);

        foreach ($dieSizeArray as &$dieSize) {
            $dieSize = $validDieSizeArray[bm_rand(0, $nValidDieSizes - 1)];
        }

        sort($dieSizeArray, SORT_NUMERIC);

        return $dieSizeArray;
    }

    protected static function generate_die_skills(
        $nDice,
        array $validDieSkillLetterArray,
        $nSkillsToBeGenerated,
        $maxSkillsPerDie = PHP_INT_MAX
    ) {
        $dieSkillArrayArray = array_fill(0, $nDice, array());
        $nSkills = count($validDieSkillLetterArray);
        $nSkillsGenerated = 0;

        while ($nSkillsGenerated < $nSkillsToBeGenerated) {
            $skillChosen = $validDieSkillLetterArray[bm_rand(0, $nSkills - 1)];
            $dieIdx = bm_rand(0, $nDice - 1);
            if ((count($dieSkillArrayArray[$dieIdx]) < $maxSkillsPerDie) &&
                (!in_array($skillChosen, $dieSkillArrayArray[$dieIdx]))) {
                // add the $skillChosen to the index to ensure that the final
                // string is sorted in alphabetical order
                $dieSkillArrayArray[$dieIdx][$skillChosen] = $skillChosen;
                $nSkillsGenerated++;
            }
        }

        $dieSkillLettersArray = array_fill(0, $nDice, '');

        foreach ($dieSkillLettersArray as $dieIdx => &$dieSkillLetters) {
            $dieSkillLetters = implode('', $dieSkillArrayArray[$dieIdx]);
        }

        return $dieSkillLettersArray;
    }

    protected static function generate_die_recipe(
        array $dieSizeArray,
        array $dieSkillLettersArray
    ) {
        if (count($dieSizeArray) != count($dieSkillLettersArray)) {
            throw new LogicException('die sizes and skills must have the same length');
        }

        $dieRecipeArray = array_fill(0, count($dieSizeArray), NULL);

        foreach ($dieRecipeArray as $dieIdx => &$dieRecipe) {
            $dieRecipe = $dieSkillLettersArray[$dieIdx] .
                         '(' .
                         $dieSizeArray[$dieIdx] .
                         ')';
        }

        return implode(' ', $dieRecipeArray);
    }
}
