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
    private $recipe;
    public $dieArray;
    // three lists of dice

    // methods
    public function loadFromRecipe($recipe) {
        $this->validateRecipe($recipe);
        $this->recipe = $recipe;
        $dieSides = $this->parseRecipeForSides($recipe);
        $dieSkills = $this->parseRecipeForSkills($recipe);
        $this->dieArray = array();

        // set die sides and skills, one die at a time
        for ($dieIdx = 0; $dieIdx <= (count($dieSides) - 1); $dieIdx++) {
            $tempBMDie = new BMDie;
            if (is_numeric($dieSides[$dieIdx])) {
                $tempBMDie->mSides = $dieSides[$dieIdx];
            }
            if (!empty($dieSkills[$dieIdx])) {
                $tempBMDie->mSkills = $dieSkills[$dieIdx];
            }
            $this->dieArray[] = $tempBMDie;
        }
    }

    public function loadFromName($name) {
        $this->recipe = '(8) (10) (12) (20) (X)';
    }

    public function loadValues($valueArray) {
        if ((!isset($this->dieArray)) |
            (count($this->dieArray) != count($valueArray))) {
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
            $dieContainsSides = preg_match('/\(.+\)/', $dieArray[$dieIdx]);
            if (1 !== $dieContainsSides) {
                throw new InvalidArgumentException('Invalid button recipe.');
            }
        }
    }

    private function parseRecipeForSides($recipe) {
        $dieSizeArray = preg_split('/[[:space:]]+/', $recipe);

        for ($dieIdx = 0; $dieIdx < count($dieSizeArray); $dieIdx++) {
            $dieSizeArray[$dieIdx] = preg_replace('/^.*\(/', '',
                                                  $dieSizeArray[$dieIdx]);
            $dieSizeArray[$dieIdx] = preg_replace('/\).*$/', '',
                                                  $dieSizeArray[$dieIdx]);
        }

        return $dieSizeArray;
    }

    private function parseRecipeForSkills($recipe) {
        $dieSkillArray = preg_split('/[[:space:]]+/', $recipe);

        for ($dieIdx = 0; $dieIdx < count($dieSkillArray); $dieIdx++) {
            $dieSkillArray[$dieIdx] = preg_replace('/\(.+\)/', '',
                                                  $dieSkillArray[$dieIdx]);
        }

        return $dieSkillArray;
    }

    // create dice

    // load die values

    // utility methods

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
//            switch ($property) {
//                default:
//                    return $this->$property;
//            }
        }
    }

    public function __set($property, $value)
    {
        switch ($property) {
            case 'recipe':
                $this->loadFromRecipe($value);
                break;

            default:
                $this->$property = $value;
        }
    }
}

?>
