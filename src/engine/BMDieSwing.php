<?php

class BMDieSwing extends BMDie {
    public $swingType;
    public $swingValue;
    public $swingMax;
    public $swingMin;
    protected $needsSwingValue = TRUE;
    protected $valueRequested = FALSE;

    // Don't really like putting data in the code, but where else
    // should it go?
    //
    // Should be a constant, but that isn't allowed. Instead, we wrap
    // it in a method
    private static $swingRanges = array(
        "R"	=> array(2, 16),
        "S"	=> array(6, 20),
        "T"	=> array(2, 12),
        "U"	=> array(8, 30),
        "V"	=> array(6, 12),
        "W"	=> array(4, 12),
        "X"	=> array(4, 20),
        "Y"	=> array(1, 20),
        "Z"	=> array(4, 30));

    public static function swing_range($type) {
        if (array_key_exists($type, self::$swingRanges)) {
            return self::$swingRanges[$type];
        }
        return NULL;
    }

    public function init($type, array $skills = NULL) {
        $this->min = 1;

        $this->divisor = 1;
        $this->remainder = 0;

        $this->needsSwingValue = TRUE;
        $this->valueRequested = FALSE;

        $this->swingType = $type;

        $range = $this->swing_range($type);
        if (is_null($range)) {
            throw new UnexpectedValueException("Invalid swing type: $type");
        }
        $this->swingMin = $range[0];
        $this->swingMax = $range[1];

        $this->add_multiple_skills($skills);
    }

    public static function create($recipe, array $skills = NULL) {

        if (!is_string($recipe) || strlen($recipe) != 1 ||
            ord("R") > ord($recipe) || ord($recipe) > ord("Z")) {
            throw new UnexpectedValueException("Invalid recipe: $recipe");
        }

        $die = new BMDieSwing;

        $die->init($recipe, $skills);

        return $die;

    }

    public function activate() {
        $newDie = clone $this;

        $hookResult = $this->run_hooks(__FUNCTION__, array('die' => $newDie));
        $skipSwingRequest = is_array($hookResult) &&
                            array_search('skipSwingRequest', $hookResult);

        if (!$skipSwingRequest) {
            // The clone is the one going into the game, so it's the one
            // that needs a swing value to be set.
            $this->ownerObject->request_swing_values(
                $newDie,
                $newDie->swingType,
                $newDie->playerIdx
            );
            $newDie->valueRequested = TRUE;
        }

        $this->ownerObject->add_die($newDie);
    }

    public function make_play_die() {
        // Get swing value from the game before cloning, so it's saved
        // from round to round.
        if ($this->needsSwingValue) {
            $this->ownerObject->require_values();
        }

        return parent::make_play_die();
    }

    public function roll($successfulAttack = FALSE) {
        if ($this->needsSwingValue) {
            if (!$this->valueRequested) {
                $this->ownerObject->request_swing_values($this, $this->swingType);
                $this->valueRequested = TRUE;
            }
            $this->ownerObject->require_values();
        }

        parent::roll($successfulAttack);
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
        }

        $valueStr = '';
        if ($isValueRequired && isset($this->value)) {
            $valueStr = " showing {$this->value}";
        }

        $result = "{$skillStr}{$this->swingType} Swing Die{$sideStr}{$valueStr}";

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

    public function set_swingValue($swingList) {
        $valid = TRUE;

        if (!array_key_exists($this->swingType, $swingList)) {
            return FALSE;
        }

        $sides = (int)$swingList[$this->swingType];

        if ($sides < $this->swingMin || $sides > $this->swingMax) {
            return FALSE;
        }

        $this->run_hooks(__FUNCTION__, array('isValid'   => &$valid,
                                             'swingList' => $swingList));

        if ($valid) {
            $this->swingValue = $sides;
            $this->needsSwingValue = FALSE;
            $this->valueRequested = FALSE;
            $this->max = $sides;
            $this->scoreValue = $sides;
        }

        return $valid;
    }
}
