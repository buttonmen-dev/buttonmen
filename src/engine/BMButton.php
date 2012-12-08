<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BMButton
 *
 * @author james
 */
class BMButton {
    // properties
    public $recipe;
    public $dieArray;
    // three lists of dice

    // methods
    public function loadFromRecipe($recipe) {
        $this->validateRecipe($recipe);
        $dieSides = $this->parseRecipeForSides($recipe);
        $dieSkills = $this->parseRecipeForSkills($recipe);
        unset($this->dieArray);

        // set die sides and skills, one die at a time
        for ($dieIdx = 0; $dieIdx <= (count($dieSides) - 1); $dieIdx++) {
            $tempBMDie = new BMDie;
            $tempBMDie->mSides = $dieSides[$dieIdx];
            if (!empty($dieSkills[$dieIdx])) {
                $tempBMDie->mSkills = $dieSkills[$dieIdx];
            }
            $this->dieArray[] = $tempBMDie;
        }
    }

    public function loadValues($valueArray) {
        if (count($this->dieArray) != count($valueArray)) {
            throw new InvalidArgumentException('Invalid number of values.');
        }

        for ($dieIdx = 0; $dieIdx <= (count($valueArray) - 1); $dieIdx++) {
            $this->dieArray[$dieIdx]->scoreValue = $valueArray[$dieIdx];
        }

    }

    private function validateRecipe($recipe) {
        $dieArray = preg_split('/[[:space:]]+/', $recipe,
                               NULL, PREG_SPLIT_NO_EMPTY);

        for ($dieIdx = 0; $dieIdx < count($dieArray); $dieIdx++) {
            // ideally, we want to shift this functionality to the
            // and then we just validate each die individually
            $dieContainsDigit = preg_match('/[[:digit:]]/', $dieArray[$dieIdx]);
            print($dieContainsDigit);
            if (1 !== $dieContainsDigit) {
                throw new InvalidArgumentException('Invalid button recipe.');
            }
        }
    }

    private function parseRecipeForSides($recipe) {
        $dieSizeArray = preg_split('/[^[:digit:]]+/', $recipe,
                                   NULL, PREG_SPLIT_NO_EMPTY);
        return $dieSizeArray;
    }

    private function parseRecipeForSkills($recipe) {
        $dieSkillArray = preg_split('/[[:space:]]+/', $recipe);
        for ($dieIdx = 0; $dieIdx < count($dieSkillArray); $dieIdx++) {
            $dieSkillArray[$dieIdx] = preg_replace('/[[:digit:]]/', '',
                                                  $dieSkillArray[$dieIdx]);
        }

        return $dieSkillArray;
    }

    // create dice

    // load die values
}

?>
