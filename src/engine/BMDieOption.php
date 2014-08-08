<?php
/**
 * BMDieOption: Code specific to option dice
 *
 * @author james
 */

/**
 * This class contains all the logic to do with requesting and setting option values
 *
 * @property      array $optionValueArray  Possible option values
 * @property-read bool  $needsOptionValue  Flag indicating whether an option value is still needed
 * @property-read bool  $valueRequested    Flag indicating whether an option request has been sent to the parent
 */
class BMDieOption extends BMDie {
    protected $optionValueArray;
    protected $needsOptionValue;
    protected $valueRequested;

    public function init($optionArray, array $skills = NULL) {
        if (!is_array($optionArray) ||
            2 != count($optionArray)) {
            throw new InvalidArgumentException('optionArray must be an array with at exactly two elements.');
        }

        $this->min = 1;

        $this->divisor = 1;
        $this->remainder = 0;

        $this->optionValueArray = $optionArray;
        $this->needsOptionValue = TRUE;
        $this->valueRequested = FALSE;

        $this->typesList['Option'] = array(
            'code' => '/',
            'description' =>
                'Option Dice are represented as two numbers with a slash ' .
                'between them, resembling a fraction. They function like ' .
                'Swing Dice and can be changed at any time a Swing Die could ' .
                'be changed. However, Option Dice are restricted to only two ' .
                'values. For example, a 4/10 Option Die can be only a 4 or a ' .
                '10.',
        );

        $this->add_multiple_skills($skills);
    }

    public static function create($optionArray, array $skills = NULL) {
        $die = new BMDieOption;

        $die->init($optionArray, $skills);

        return $die;
    }

    public function activate() {
        $newDie = clone $this;

        $this->run_hooks(__FUNCTION__, array('die' => $newDie));

        $this->ownerObject->add_die($newDie);

        // The clone is the one going into the game, so it's the one
        // that needs a option value to be set.
        $this->ownerObject->request_option_values(
            $newDie,
            $newDie->optionValueArray,
            $newDie->playerIdx
        );
        $newDie->valueRequested = TRUE;
    }

    public function roll($isTriggeredByAttack = FALSE) {
        if ($this->needsOptionValue) {
            if (!$this->valueRequested) {
                $this->ownerObject->request_option_values(
                    $this,
                    $this->optionValueArray,
                    $this->playerIdx
                );
                $this->valueRequested = TRUE;
            }
        } else {
            parent::roll($isTriggeredByAttack);
        }
    }

    // Print long description
    public function describe($isValueRequired = FALSE) {
        if (!is_bool($isValueRequired)) {
            throw new InvalidArgumentException('isValueRequired must be boolean');
        }

        $skillStr = '';
        if (count($this->skillList) > 0) {
            foreach (array_keys($this->skillList) as $skill) {
                $skillStr .= "$skill ";
            }
        }

        $sideStr = '';
        if (isset($this->max)) {
            $sideStr = " (with {$this->max} sides)";
        } else {
            $sideStr = " (with {$this->optionValueArray[0]} or {$this->optionValueArray[1]} sides)";
        }

        $valueStr = '';
        if ($isValueRequired && isset($this->value)) {
            $valueStr = " showing {$this->value}";
        }

        $result = "{$skillStr}Option Die{$sideStr}{$valueStr}";

        return $result;
    }

    public function split() {
        $normalDie = new BMDie();
        // note: init requires an array without string keys
        $normalDie->init($this->max, array_keys($this->skillList));
        $normalDie->ownerObject = $this->ownerObject;
        $normalDie->playerIdx = $this->playerIdx;
        $normalDie->originalPlayerIdx = $this->originalPlayerIdx;
        return $normalDie->split();
    }

    public function set_optionValue($optionValue) {
        if (FALSE === array_search($optionValue, $this->optionValueArray)) {
            return FALSE;
        }

        $this->needsOptionValue = FALSE;
        $this->valueRequested = FALSE;
        $this->max = $optionValue;
        $this->scoreValue = $optionValue;

        return TRUE;
    }

    public function __set($property, $value) {
        switch ($property) {
            case 'max':
                if (in_array($value, $this->optionValueArray) ||
                    is_null($value)) {
                    $this->$property = $value;
                } else {
                    throw new LogicException('Chosen option value is invalid.');
                }
                break;
            default:
                parent::__set($property, $value);
        }
    }
}
