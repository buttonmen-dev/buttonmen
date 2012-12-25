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
    public function load_from_recipe($recipe) {
        $this->validate_recipe($recipe);
        $this->recipe = $recipe;
        $this->dieArray = array();

        if (empty($recipe)) {
            return;
        }

        $dieSides = $this->parse_recipe_for_sides($recipe);
        $dieSkills = $this->parse_recipe_for_skills($recipe);

        // set die sides and skills, one die at a time
        for ($dieIdx = 0, $count = count($dieSides);
             $dieIdx <= $count - 1; $dieIdx++) {
            // james: this will probably be replaced by a call to
            // BMDie::create_from_string
            $tempBMDie = new BMDie;
            $tempBMDie->mSides = $dieSides[$dieIdx];
            if (!empty($dieSkills[$dieIdx])) {
                $tempBMDie->mSkills = $dieSkills[$dieIdx];
            }
            $this->dieArray[] = $tempBMDie;
        }
    }

    public function load_from_name($name) {
        // james:
        // The implementation here is currently a stub that always returns the
        // recipe of Bauer. This will eventually be replaced by a database call
        // to retrieve the recipe, and then a recipe set for the current button.
        $this->name = $name;
        $this->recipe = '(8) (10) (12) (20) (X)';
    }

    public function load_values($valueArray) {
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
            $this->dieArray[$dieIdx]->value = $valueArray[$dieIdx];
        }

    }

    private function validate_recipe($recipe) {
        $dieArray = preg_split('/[[:space:]]+/', $recipe,
                               NULL, PREG_SPLIT_NO_EMPTY);

        if (empty($recipe)) {
            return;
        }

        foreach ($dieArray as $die) {
        // james: this validation is probably incomplete
            $dieContainsSides = preg_match('/\(.+\)/', $die);
            if (1 !== $dieContainsSides) {
                throw new InvalidArgumentException('Invalid button recipe.');
            }
        }
    }

    private function parse_recipe_for_sides($recipe) {
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

    private function parse_recipe_for_skills($recipe) {
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
    // to allow array elements to be set directly, change the __get to &__get
    // to return the result by reference
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        switch ($property) {
            case 'name':
                $this->load_from_name($value);
                break;

            case 'recipe':
                $this->load_from_recipe($value);
                break;

            default:
                $this->$property = $value;
        }
    }

    public function __isset($property) {
        return isset($this->$property);
    }

    public function __unset($property) {
        if (isset($this->$property)) {
            unset($this->$property);
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

?>
