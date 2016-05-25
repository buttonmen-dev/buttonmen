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
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('specify_recipes');

    /**
     * Array of standard die sizes found in Soldiers
     *
     * @var array
     */
    public static $die_sizes_soldiers = array(4, 6, 8, 10, 12, 20);

    /**
     * Hooked method applied when specifying recipes
     *
     * @param array $args
     * @return bool
     */
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

    /**
     * Choose n skills from an array of possible skills
     *
     * @param int $nSkills
     * @param array $possibleSkillArray
     * @return array
     */
    public static function randomly_select_skills($nSkills, array $possibleSkillArray) {
        if (count($possibleSkillArray) < $nSkills) {
            throw new LogicException('Not enough possible skills to select from');
        }

        $skillArray = array();
        $nPossibleSkills = count($possibleSkillArray);

        while (count($skillArray) < $nSkills) {
            $skillArray[$possibleSkillArray[bm_rand(0, $nPossibleSkills - 1)]] = TRUE;
        }

        return array_keys($skillArray);
    }

    /**
     * Choose n swing types at random. Duplicates are allowed.
     *
     * @param int $nTypes
     * @param array $possibleSwingTypes
     * @return array
     */
    public static function randomly_select_swing_types(
        $nTypes = 1,
        array $possibleSwingTypes = array('R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z')
    ) {
        $swingTypeArray = array();

        for ($idx = 0; $idx < $nTypes; $idx++) {
            $swingTypeArray[] = $possibleSwingTypes[bm_rand(0, count($possibleSwingTypes) - 1)];
        }

        return $swingTypeArray;
    }

    /**
     * Generates an array of die sizes
     *
     * @param int $nDice
     * @return array
     */
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

    /**
     * Generates an array of skill strings
     *
     * @param int                                $nDice number of dice in total
     * @param array $validDieSkillLetterArray    skill letters to be used
     * @param int $nSkillsToBeGeneratedRandomly  number of times to choose a skill at random
     * @param int $nTimesToGenerateAllSkills     number of times to generate all skills
     * @param int $maxSkillsPerDie               maximum number of skills per die
     * @return array
     */
    protected static function generate_die_skills(
        $nDice,
        array $validDieSkillLetterArray,
        $nSkillsToBeGeneratedRandomly,
        $nTimesToGenerateAllSkills,
        $maxSkillsPerDie = PHP_INT_MAX
    ) {
        if ($nDice*$maxSkillsPerDie < $nSkillsToBeGeneratedRandomly +
                                      $nTimesToGenerateAllSkills*count($validDieSkillLetterArray)) {
            throw new LogicException('Each die would have too many skills');
        }

        $dieSkillLetterArrayArray = array_fill(0, $nDice, array());

        foreach ($validDieSkillLetterArray as $skillLetter) {
            for ($nSkillIdx = 0; $nSkillIdx < $nTimesToGenerateAllSkills; $nSkillIdx++) {
                $assigned = FALSE;
                while (!$assigned) {
                    $dieIdx = bm_rand(0, $nDice - 1);
                    if ((count($dieSkillLetterArrayArray[$dieIdx]) < $maxSkillsPerDie) &&
                        (!in_array($skillLetter, $dieSkillLetterArrayArray[$dieIdx]))) {
                        // add the $skillLetter to the index to ensure that the final
                        // string is sorted in alphabetical order
                        $dieSkillLetterArrayArray[$dieIdx][$skillLetter] = $skillLetter;
                        $assigned = TRUE;
                    }
                }
            }
        }

        $nSkills = count($validDieSkillLetterArray);
        $nSkillsGenerated = 0;

        while ($nSkillsGenerated < $nSkillsToBeGeneratedRandomly) {
            $skillChosen = $validDieSkillLetterArray[bm_rand(0, $nSkills - 1)];
            $dieIdx = bm_rand(0, $nDice - 1);
            if ((count($dieSkillLetterArrayArray[$dieIdx]) < $maxSkillsPerDie) &&
                (!in_array($skillChosen, $dieSkillLetterArrayArray[$dieIdx]))) {
                // add the $skillChosen to the index to ensure that the final
                // string is sorted in alphabetical order
                $dieSkillLetterArrayArray[$dieIdx][$skillChosen] = $skillChosen;
                $nSkillsGenerated++;
            }
        }

        return $dieSkillLetterArrayArray;
    }

    /**
     * Generates random recipe
     *
     * @param array $dieSizeArray
     * @param array $dieSkillLetterArrayArray
     * @return string
     */
    protected static function generate_recipe(
        array $dieSizeArray,
        array $dieSkillLetterArrayArray
    ) {
        if (count($dieSizeArray) != count($dieSkillLetterArrayArray)) {
            throw new LogicException('die sizes and skills must have the same length');
        }

        $dieRecipeArray = array_fill(0, count($dieSizeArray), NULL);

        foreach ($dieRecipeArray as $dieIdx => &$dieRecipe) {
            $dieRecipe = '(' .
                         $dieSizeArray[$dieIdx] .
                         ')';

            if (!empty($dieSkillLetterArrayArray[$dieIdx])) {
                $dieSkillLetterArray = $dieSkillLetterArrayArray[$dieIdx];
                sort($dieSkillLetterArray)
                foreach ($dieSkillLetterArray as $skillLetter) {
                    $skillNameArray = BMSkill::expand_skill_string($skillLetter);
                    $skillName = 'BMSkill' . $skillNameArray[0];
                    $skill = new $skillName;

                    if ($skill::do_print_skill_preceding()) {
                        $dieRecipe = $skillLetter . $dieRecipe;
                    } else {
                        $dieRecipe = $dieRecipe . $skillLetter;
                    }
                }
            }
        }

        $recipe = implode(' ', $dieRecipeArray);

        return $recipe;
    }

    /**
     * Array containing excluded die skill names
     *
     * @return array
     */
    protected static function excluded_skill_array() {
        // Actively exclude possibly problematic skills
        // The current selection is conservative, and should be whittled down in time,
        // after we deal with bugs that arise from strange skill combinations
        return array(
            'Auxiliary', 'Reserve', 'Warrior', // game-level skills
            'Doppelganger', 'Mad', 'Mood',
            'Morphing', 'Radioactive', // recipe-changing skills
            'Fire', // skills that add an extra step to attacks
            'Slow', // skills excluded because they're no fun
        );
    }

    /**
     * Array containing excluded die skill characters
     *
     * @return array
     */
    protected static function excluded_skill_char_array() {
        $skillCharArray = array_map(
            'BMSkill::abbreviate_skill_name',
            self::excluded_skill_array()
        );
        sort($skillCharArray);
        return $skillCharArray;
    }
}
