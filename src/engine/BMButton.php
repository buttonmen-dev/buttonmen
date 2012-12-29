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

        $dieSidesArray = $this->parse_recipe_for_sides($recipe);
        $dieSkillsArray = $this->parse_recipe_for_skills($recipe);

        // set die sides and skills, one die at a time
        foreach ($dieSidesArray as $dieIdx => $tempDieSides) {
            // james: this will probably be replaced by a call to
            // BMDie::create_from_string
            $tempBMDie = new BMDie;
            $tempBMDie->mSides = $tempDieSides;
            if (!empty($tempDieSides)) {
                $tempBMDie->mSkills = $dieSkillsArray[$dieIdx];
            }
            $this->dieArray[] = $tempBMDie;
        }
    }

    public function reload() {
        $this->load_from_recipe($this->recipe);
    }

    public function load_from_name($name) {
        // james:
        // The implementation here is currently a stub that always returns the
        // recipe of Bauer. This will eventually be replaced by a database call
        // to retrieve the recipe, and then a recipe set for the current button.
        $this->name = $name;
        $this->recipe = '(8) (10) (12) (20) (X)';
    }

    public function load_values(array $valueArray) {
        if (count($this->dieArray) != count($valueArray)) {
            throw new InvalidArgumentException('Invalid number of values.');
        }

        foreach ($valueArray as $dieIdx => $tempValue) {
            if (($tempValue < 1) |
                ($tempValue > $this->dieArray[$dieIdx]->mSides)) {
                throw new InvalidArgumentException('Invalid values.');
            }
            $this->dieArray[$dieIdx]->value = $tempValue;
        }
    }

    private function validate_recipe($recipe) {
        $dieArray = preg_split('/[[:space:]]+/', $recipe,
                               NULL, PREG_SPLIT_NO_EMPTY);

        if (empty($recipe)) {
            return;
        }

        foreach ($dieArray as $tempDie) {
        // james: this validation is probably incomplete
            $dieContainsSides = preg_match('/\(.+\)/', $tempDie);
            if (1 !== $dieContainsSides) {
                throw new InvalidArgumentException('Invalid button recipe.');
            }
        }
    }

    private function parse_recipe_for_sides($recipe) {
        // split by spaces
        $dieSizeArray = preg_split('/[[:space:]]+/', $recipe);

        foreach ($dieSizeArray as $dieIdx => $tempDieSize) {
            // remove everything before the opening parenthesis
            $tempDieSize = preg_replace('/^.*\(/', '', $tempDieSize);
            // remove everything after the closing parenthesis
            $dieSizeArray[$dieIdx] = preg_replace('/\).*$/', '', $tempDieSize);
        }

        return $dieSizeArray;
    }

    private function parse_recipe_for_skills($recipe) {
        // split by spaces
        $dieSkillArray = preg_split('/[[:space:]]+/', $recipe);

        // remove everything within parentheses
        foreach ($dieSkillArray as $dieIdx => $tempDieSkill) {
            $dieSkillArray[$dieIdx] = preg_replace('/\(.+\)/', '', $tempDieSkill);
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
