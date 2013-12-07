<?php

class BMDieTwin extends BMDie {
    public $dice;

    public function init($sidesArray, array $skills = NULL)
    {
        if (!is_array($sidesArray)) {
            throw new InvalidArgumentException('sidesArray must be an array.');
        }

        if (2 != count($sidesArray)) {
            throw new InvalidArgumentException('sidesArray must have exactly two elements.');
        }

        $this->min = 0;
        $this->max = 0;
        $this->add_multiple_skills($skills);

        foreach($sidesArray as $dieIdx => $sides) {
            $this->dice[$dieIdx] =
                BMDie::create_from_string_components($sides, $skills);
            if (is_null($this->dice[$dieIdx]->min) ||
                is_null($this->dice[$dieIdx]->max)) {
                $this->min = NULL;
                $this->max = NULL;
                break;
            }
            $this->min += $this->dice[$dieIdx]->min;
            $this->max += $this->dice[$dieIdx]->max;
        }
    }

    public static function parse_recipe_for_sides($recipe) {
        $sidesArray = array();
        if (preg_match('/\((.*),(.*)\)/', $recipe, $match)) {
            $sidesArray[0] = $match[1];
            $sidesArray[1] = $match[2];
        } else {
            return '';
        }
    }

    public static function create_from_recipe($recipe) {
        $sidesArray = BMDieTwin::parse_recipe_for_sides($recipe);
        $skills = BMDie::parse_recipe_for_skills($recipe);
        return BMDieTwin::create_from_string_components($sidesArray, $skills);
    }

    public static function create($sidesArray, array $skills = NULL) {
        if (!is_array($sidesArray)) {
            throw new LogicException('$sidesArray must be an array.');
        }

        foreach ($sidesArray as $sides) {
            if (!is_numeric($sides) || ($sides != (int)$sides) ||
                $sides < 1 || $sides > 99) {
                throw new UnexpectedValueException("Illegal die size in twin die recipe: $sides");
            }
        }

        $die = new BMDieTwin;

        $die->init($sidesArray, $skills);

        return $die;
    }

    public function roll($successfulAttack = FALSE) {
        $this->value = 0;
        foreach ($dice as &$die) {
            $die->roll();
            $this->value += $die->value;
        }

        $this->run_hooks(__FUNCTION__, array('isSuccessfulAttack' => $successfulAttack));
    }

// Print long description
    public function describe() {
        $this->run_hooks(__FUNCTION__, array());
    }

    public function split() {
        $newdie = clone $this;

        foreach ($this->dice as $dieIdx => $die) {
            $splitDieArray = $die->split();
            $this->dice[$dieIdx] = $splitDieArray[0];
            $newdie->dice[$dieIdx] = $splitDieArray[1];
        }

        $dice = array($this, $newdie);

        $this->run_hooks(__FUNCTION__, array('dice' => &$dice));

        return $dice;
    }

    // Return all information about a die which is useful when
    // constructing an action log entry, in the form of an array.
    // This function exists so that BMGame can easily compare the
    // die state before the attack to the die state after the attack.
//    public function get_action_log_data() {
//       $recipe = $this->get_recipe();
//       return(array(
//           'recipe' => $recipe,
//           'min' => $this->min,
//           'max' => $this->max,
//           'value' => $this->value,
//           'doesReroll' => $this->doesReroll,
//           'captured' => $this->captured,
//           'recipeStatus' => $recipe . ':' . $this->value,
//       ));
//    }
}

?>
