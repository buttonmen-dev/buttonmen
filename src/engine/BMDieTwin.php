<?php

class BMDieTwin extends BMDie {
    public $dice;

    // james: done
    public function init(array $sidesArray, array $skills = NULL)
    {
        if (2 != count($sidesArray)) {
            throw new LogicException('There must be exactly two dice in a BMDieTwin.');
        }

        $this->min = 0;
        $this->max = 0;

        foreach($sidesArray as $dieIdx => $sides) {
            $this->dice[$dieIdx] =
                BMDie::create_from_string_components($sides, $skills);
            $this->min += $this->dice[$dieIdx]->min;
            $this->max += $this->dice[$dieIdx]->max;
        }
    }

    // james: done
    public static function parse_recipe_for_sides($recipe) {
        $sidesArray = array();
        if (preg_match('/\((.*),(.*)\)/', $recipe, $match)) {
            $sidesArray[0] = $match[1];
            $sidesArray[1] = $match[2];
        } else {
            return '';
        }
    }

    // james: done
    public static function create_from_recipe($recipe) {
        $sidesArray = BMDieTwin::parse_recipe_for_sides($recipe);
        $skills = BMDie::parse_recipe_for_skills($recipe);
        return BMDieTwin::create_from_string_components($sidesArray, $skills);
    }

    // james: done
    public static function create(array $sizeArray, array $skills = NULL) {
        foreach ($sizeArray as $size) {
            if (!is_numeric($size) || ($size != (int)$size) ||
                $size < 1 || $size > 99) {
                throw new UnexpectedValueException("Illegal die size in twin die recipe: $size");
            }
        }

        $die = new BMDieTwin;

        $die->init($sizeArray, $skills);

        return $die;
    }

    // james : done
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

    // james : done
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
