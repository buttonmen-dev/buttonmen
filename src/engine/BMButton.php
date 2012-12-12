<?php

/**
 * BMButton: instantiated button as existing at the beginning of a round
 *
 * @author james
 */
class BMButton {
    // properties
    private $name;
    private $recipe;
    public $dieArray;

    // methods
    public function loadFromRecipe($recipe) {
        $this->validateRecipe($recipe);
        $this->recipe = $recipe;
        $dieSides = $this->parseRecipeForSides($recipe);
        $dieSkills = $this->parseRecipeForSkills($recipe);
        $this->dieArray = array();

        // set die sides and skills, one die at a time
        for ($dieIdx = 0, $count = count($dieSides);
             $dieIdx <= $count - 1; $dieIdx++) {
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
        // The implementation here is currently a stub that always returns the
        // recipe of Bauer. This will eventually be replaced by a database call
        // to retrieve the recipe, and then a recipe set for the current button.
        $this->recipe = '(8) (10) (12) (20) (X)';
    }

    public function loadValues($valueArray) {
        if ((!isset($this->dieArray)) |
            (count($this->dieArray) != count($valueArray))) {
            throw new InvalidArgumentException('Invalid number of values.');
        }

        for ($dieIdx = 0, $count = count($valueArray);
             $dieIdx <= $count - 1; $dieIdx++) {
            if (($valueArray[$dieIdx] < 1) |
                ($valueArray[$dieIdx] > $this->dieArray[$dieIdx]->mSides)) {
                throw new InvalidArgumentException('Invalid values.');
            }
        }

        for ($dieIdx = 0, $count = count($valueArray);
             $dieIdx <= $count - 1; $dieIdx++) {
            $this->dieArray[$dieIdx]->scoreValue = $valueArray[$dieIdx];
        }

    }

    private function validateRecipe($recipe) {
        $dieArray = preg_split('/[[:space:]]+/', $recipe,
                               NULL, PREG_SPLIT_NO_EMPTY);

        foreach ($dieArray as $die) {
            // ideally, we want to shift this functionality to the
            // and then we just validate each die individually
            $dieContainsSides = preg_match('/\(.+\)/', $die);
            if (1 !== $dieContainsSides) {
                throw new InvalidArgumentException('Invalid button recipe.');
            }
        }
    }

    private function parseRecipeForSides($recipe) {
        // split by spaces
        $dieSizeArray = preg_split('/[[:space:]]+/', $recipe);

        for ($dieIdx = 0; $dieIdx < count($dieSizeArray); $dieIdx++) {
            // remove everything before the opening parenthesis
            $dieSizeArray[$dieIdx] = preg_replace('/^.*\(/', '',
                                                  $dieSizeArray[$dieIdx]);
            // remove everything after the closing parenthesis
            $dieSizeArray[$dieIdx] = preg_replace('/\).*$/', '',
                                                  $dieSizeArray[$dieIdx]);
        }

        return $dieSizeArray;
    }

    private function parseRecipeForSkills($recipe) {
        // split by spaces
        $dieSkillArray = preg_split('/[[:space:]]+/', $recipe);

        // remove everything within parentheses
        for ($dieIdx = 0; $dieIdx < count($dieSkillArray); $dieIdx++) {
            $dieSkillArray[$dieIdx] = preg_replace('/\(.+\)/', '',
                                                  $dieSkillArray[$dieIdx]);
        }

        return $dieSkillArray;
    }

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
            case 'name':
                $this->loadFromName($value);
                break;

            case 'recipe':
                $this->loadFromRecipe($value);
                break;

            default:
                $this->$property = $value;
        }
    }
}

?>
