<?php

/**
 * BMButton: instantiated button as existing at the beginning of a round
 *
 * @author james
 *
 * @property      string $name        Name of button
 * @property      string $recipe      String representation of the button recipe
 * @property-read array  $dieArray    Array of BMDie
 * @property      BMGame $ownerObject BMGame that owns the BMButton
 * $property      BMGame $playerIdx   BMGame index of the player that owns the BMButton
 */
class BMButton {
    // properties
    private $name;
    private $recipe;
    private $dieArray;
    private $ownerObject;
    private $playerIdx;

    // methods
    public function load($recipe, $name = NULL) {
        if (!is_null($name)) {
            $this->name = $name;
        }

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
            $tempDie = BMDie::create_from_string($tempDieSides,
                                                 $dieSkillsArray[$dieIdx]);
            $this->dieArray[] = $tempDie;
        }
    }

    public function reload() {
        $this->load($this->recipe);
    }

    public function load_values(array $valueArray) {
        if (count($this->dieArray) != count($valueArray)) {
            throw new InvalidArgumentException('Invalid number of values.');
        }

        foreach ($valueArray as $dieIdx => $tempValue) {
            if (($tempValue < 1)
                | ($tempValue > $this->dieArray[$dieIdx]->max)
                ) {
                throw new InvalidArgumentException('Invalid values.');
            }
            $this->dieArray[$dieIdx]->value = $tempValue;
        }
    }

    public function add_die($die) {
        $this->dieArray[] = $die;
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

    public function activate() {
        foreach ($this->dieArray as $die) {
            $die->activate();
        }
    }

    private function parse_recipe_for_sides($recipe) {
        // split by spaces
        $dieSizeArray = preg_split('/[[:space:]]+/', $recipe);

        foreach ($dieSizeArray as $dieIdx => $tempDieSize) {
            // remove everything before the opening parenthesis
            $tempDieSize = preg_replace('/^.*\(/', '', $tempDieSize);
            // remove everything after the closing parenthesis
            $tempDieSize = preg_replace('/\).*$/', '', $tempDieSize);
            $tempDieSizeInt = intval($tempDieSize);
            $dieSizeArray[$dieIdx] = ($tempDieSizeInt > 0 ?
                                          $tempDieSizeInt :
                                          $tempDieSize);
        }

        return $dieSizeArray;
    }

    private function parse_recipe_for_skills($recipe) {
        // split by spaces
        $dieSkillArray = preg_split('/[[:space:]]+/', $recipe);
        
        // remove everything within parentheses
        foreach ($dieSkillArray as $dieIdx => $tempDieSkills) {
            $dieSkillArray[$dieIdx] = BMSkill::expand_skill_string(
                                          preg_replace('/\(.+\)/', '', $tempDieSkills));
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
            case 'recipe':
                $this->load($value);
                break;

            case 'dieArray':
                $this->dieArray = $value;
                foreach ($this->dieArray as $die) {
                    if (isset($this->ownerObject)) {
                        $die->ownerObject = $this->ownerObject;
                    }
                    if (isset($this->playerIdx)) {
                        $die->playerIdx = $this->playerIdx;
                    }
                }
                break;

            case 'ownerObject':
                $this->ownerObject = $value;
                if (isset($this->dieArray)) {
                    foreach ($this->dieArray as $die) {
                        $die->ownerObject = $this->ownerObject;
                    }
                }
                break;

            case 'playerIdx':
                $this->playerIdx = $value;
                if (isset($this->dieArray)) {
                    foreach ($this->dieArray as $die) {
                        $die->playerIdx = $this->playerIdx;
                    }
                }
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
