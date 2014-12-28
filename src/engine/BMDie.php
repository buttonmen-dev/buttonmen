<?php
/**
 * BMDie: the fundamental unit of game mechanics
 *
 * @author: Julian
 */

/**
 * This class contains all the logic to do with dice, including instantiating, activating,
 * rolling, capturing, describing, as well as die flags
 *
 * @property-read int    $min                   Minimum die value
 * @property-read int    $max                   Maximum die value
 * @property      int    $value                 Current die value
 * @property-read string $recipe                Die recipe
 * @property-read int    $firingMax             Maximum amount that the die can be fired up
 * @property      BMGame/BMButton $ownerObject  Game or button that owns the die
 * @property      int    $playerIdx             Index of player that currently owns the die
 * @property-read int    $activeDieIdx          Index of die in activeDieArrayArray
 * @property      int    $originalPlayerIdx     Index of player that originally owned the die
 * @property      bool   $doesReroll            Can the die reroll?
 * @property      bool   $captured              Has the die has been captured?
 * @property      bool   $hasAttacked           Has the die attacked this turn?
 * @property      bool   $selected              Does the player want to add this auxiliary die?
 * @property-read array  $flagList              Array designed to contain various BMFlags
 *
 *  */
class BMDie extends BMCanHaveSkill {
    // properties

// Basic facts about the die
    protected $min;
    protected $max;
    protected $value;
    protected $recipe;
    protected $firingMax;

// references back to the owner
    protected $ownerObject;
    protected $playerIdx;
    protected $activeDieIdx;
    protected $originalPlayerIdx;

    protected $doesReroll = TRUE;
    protected $captured = FALSE;

    protected $hasAttacked = FALSE;

    // $flagList is designed to contain various BMFlags
    protected $flagList = array();

    // Hackish: the caller can specify each skill as either a plain
    // value, "skill", or a key/value pair "ClassName" => "skill",
    // where the key is the class name which implements that skill.
    // This is only for use by callers outside of engine (e.g.
    // testing), and should never be used for the default BMSkill<skill>
    // set of skills.
    public function init($sides, array $skills = NULL) {
        if (0 == $sides) {
            $this->min = 0;
            $this->max = 0;
        } else {
            $this->min = 1;
            $this->max = $sides;
        }

        $this->add_multiple_skills($skills);
    }

    protected static function parse_recipe_for_sides($recipe) {
        if (preg_match('/\((.*)\)/', $recipe, $match)) {
            return $match[1];
        } else {
            return '';
        }
    }

    protected static function parse_recipe_for_skills($recipe) {
        return BMSkill::expand_skill_string(preg_replace('/\(.*\)/', '', $recipe));
    }

    public static function unimplemented_skill_in_recipe($recipe) {
        return BMSkill::unimplemented_skill_in_string(preg_replace('/\(.*\)/', '', $recipe));
    }

    // given a string describing a die and a list of skills, return a
    // new BMDie or appropriate subclass thereof

    // Depending on implementation details, this may end up being
    // replaced with something that doesn't need to do string parsing

    protected static function create_from_string_components($recipe, array $skills = NULL) {
        $die = NULL;

        try {
            // Option dice divide on a /, can contain any die type
            if (count($optionArray = explode('/', $recipe)) > 1) {
                $die = BMDieOption::create($optionArray, $skills);
            } elseif (count($twinArray = explode(',', $recipe)) > 1) {
                // Twin dice divide on a comma, can contain any type but option
                $die = BMDieTwin::create($twinArray, $skills);
            } elseif ('C' == $recipe) {
//                $die = BMDieWildcard::create($recipe, $skills);
                throw new BMUnimplementedDieException("Wildcard skill not implemented");
            } elseif (is_numeric($recipe) && ($recipe == (int)$recipe)) {
                // Integers are normal dice
                $die = BMDie::create((int)$recipe, $skills);
            } elseif (strlen($recipe) == 1) {
                // Single character that's not a number is a swing die
                $die = BMDieSwing::create($recipe, $skills);
            } else {
                // oops
                throw new UnexpectedValueException("Invalid recipe: $recipe");
            }
        } catch (UnexpectedValueException $e) {
            error_log(
                "Caught exception in BMDie::create_from_string_components: " .
                $e->getMessage()
            );
            return NULL;
        }

        return $die;
    }

    public static function create_from_recipe($recipe) {
        $sides = BMDie::parse_recipe_for_sides($recipe);
        $skills = BMDie::parse_recipe_for_skills($recipe);
        return BMDie::create_from_string_components($sides, $skills);
    }

    public static function create($size, array $skills = NULL) {
        if (!is_numeric($size) || ($size != (int)$size) ||
            $size < 0 || $size > 99) {
            throw new UnexpectedValueException("Illegal die size: $size");
        }

        $die = new BMDie;

        $die->init($size, $skills);

        return $die;
    }


    // hooked methods

// When a die is "woken up" from its container to be used in a
//  game. Does not roll the die
//
// Clones the die and returns the clone

    public function activate() {
        $newDie = clone $this;

        $this->ownerObject->add_die($newDie);
    }

// Roll the die into a game. Clone self, roll, return the clone.
    public function make_play_die() {
        $newDie = clone $this;
        $newDie->roll();
        return $newDie;
    }


    public function roll($isTriggeredByAttack = FALSE, $isSubdie = FALSE) {
        $this->run_hooks('pre_roll', array('die' => $this,
                                           'isTriggeredByAttack' => $isTriggeredByAttack,
                                           'isSubdie' => $isSubdie));

        if (!isset($this->value) ||
            ($this->doesReroll && !$this->has_flag('JustPerformedTripAttack'))) {
            $this->set__value(bm_rand($this->min, $this->max));
        }

        $this->run_hooks('post_roll', array('die' => $this,
                                            'isTriggeredByAttack' => $isTriggeredByAttack));
    }

    public function attack_list() {
        $list = array('Power' => 'Power', 'Skill' => 'Skill');

        $nAttDice = 0;
        $owner = $this->ownerObject;

        if (isset($owner)) {
            $attDice = $owner->attackerAttackDieArray;
            if (is_array($attDice)) {
                $nAttDice = count($attDice);
            }
        }

        $this->run_hooks(__FUNCTION__, array('attackTypeArray' => &$list,
                                             'value' => (int)$this->value,
                                             'nAttDice' => $nAttDice));

        return $list;
    }

    // Return all possible values the die may use in this type of attack
    //
    // The values must be sorted, highest to lowest, with no duplication.
    public function attack_values($type) {
        $list = array($this->value);

        $this->run_hooks(__FUNCTION__, array('attackType' => $type,
                                             'attackValues' => &$list,
                                             'minValue' => $this->min));

        return $list;
    }

    public function defense_value() {
        $val = $this->value;
        return $val;
    }

// returns ten times the "real" scoring value
//
// We do not want to use floating-point math -- there's a real risk of
// having 10.5 not equal 10.5.
//
// We use a multiplier and divisor so various skills can manipulate them
// without stepping on each others' toes
    public function get_scoreValueTimesTen() {
        $scoreValue = $this->max;

        $mult = 1;
        if ($this->captured) {
            $div = 1;
        } else {
            $div = 2;
        }

        $this->run_hooks(
            'score_value',
            array('scoreValue' => &$scoreValue,
                  'value'      => $this->value,
                  'mult'       => &$mult,
                  'div'        => &$div,
                  'captured'   => $this->captured)
        );

        if (is_null($scoreValue)) {
            return NULL;
        } else {
            return (10 * $scoreValue * $mult) / $div;
        }
    }

    // Return die's initiative value.
    // Negative means it doesn't count for initiative.
    // "?" means it's a chance die.

    public function initiative_value() {
        $val = $this->value;

        $this->run_hooks(__FUNCTION__, array('initiativeValue' => &$val));

        return $val;
    }


    // Returns what values the die can contribute to an attack that
    // it's not actually participating in.
    //
    // Fire is currently the only skill that requires this
    //
    // Returned values must be sorted from lowest to highest, and zero
    // must be omitted unless you cannot contribute.
    //
    // The attack code currently assumes that every value between the
    // lowest and highest is possible, and that 1 and -1 are possible
    // values if the help values go above or below zero. If that
    // changes, the code'll need some work.
    //
    // It does not assume that the values are positive, even though
    // they must be at the moment.
    public function assist_values($type, array $attackers) {
        $vals = array(0);

        // Attackers can't help their own attack
        if (FALSE !== array_search($this, $attackers, TRUE)) {
            return $vals;
        }

        $this->run_hooks(__FUNCTION__, array('attackType'           => $type,
                                             'assistingDie'         => $this,
                                             'possibleAssistValues' => &$vals));

        return $vals;
    }

    // Actually contribute to an attack. Returns true if the attack
    // was contributed to, false otherwise.
    //
    // Returning false in normal usage indicates an error somewhere or
    // an attempt to cheat.
    //
    // once again, this is just for Fire
    public function attack_contribute($type, array $attackers, array $defenders, $amount) {
        if ($amount == 0) {
            return FALSE;
        }

        $possibleVals = $this->assist_values($type, $attackers, $defenders);

        $valid = FALSE;

        foreach ($possibleVals as $val) {
            if ($val == $amount) {
                $valid = TRUE;
                break;
            }
        }

        return $valid;

    }

// check for special-case situations where an otherwise-valid attack
// is not legal
    public function is_valid_attacker(array $attackers) {
        return in_array($this, $attackers, TRUE);
    }


    public function is_valid_target(array $defenders) {
        $valid = in_array($this, $defenders, TRUE);

        // james: needs to be reactivated when Warrior skill is added,
        // probably in a die hook
//        if ($this->has_skill('Warrior')) {
//            $valid = FALSE;
//        }

        return $valid;
    }

    public function capture($type, array &$attackers, array &$defenders) {
        $this->run_hooks(__FUNCTION__, array('type' => $type,
                                             'attackers' => &$attackers,
                                             'defenders' => &$defenders,
                                             'caller' => $this));
    }


    public function be_captured($type, array &$attackers, array &$defenders) {
        $this->run_hooks(__FUNCTION__, array('type' => $type,
                                             'attackers' => &$attackers,
                                             'defenders' => &$defenders));
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

        $valueStr = '';
        if ($isValueRequired && isset($this->value)) {
            $valueStr = " showing {$this->value}";
        }

        $result = "{$skillStr}{$this->max}-sided die{$valueStr}";

        return $result;
    }

// split a die in twain. If something needs to cut a die's size in
// half, it should use this and throw one part away. (Or toss both;
// all references to the original die will pick up the split.)
//
// In the case of an odd number of sides, the remainder stays with the
// original die
//
// At the moment, only attacking dice can split, so the dice will
// automatically pick up the need to reroll. (It is possible there is
// some undesireable behavior there, but I cannot think
// what. Radioactive removes T&S.)
    public function split() {
        $oldRecipe = $this->get_recipe(TRUE);
        unset($this->value);
        $newdie = clone $this;

        // james: reinstate the commented condition if we want a 1-sider to split into
        //        two 1-siders
//        if ($newdie->max > 1) {
        $remainder = $newdie->max % 2;
        $newdie->max -= $remainder;
        $newdie->max = $newdie->max / 2;
        $this->max -= $newdie->max;
//        }

        if (0 == $this->max) {
            $this->min = 0;
        }

        if (0 == $newdie->max) {
            $newdie->min = 0;
        }

        $this->add_flag('HasJustSplit', $oldRecipe);
        $newdie->add_flag('HasJustSplit', $oldRecipe);

        $dice = array($this, $newdie);

        $this->run_hooks(__FUNCTION__, array('dice' => &$dice));

        return $dice;
    }

    // shrink() is intended to be used for weak dice
    public function shrink() {
        $dieSizes = self::grow_shrink_die_sizes();
        rsort($dieSizes);

        foreach ($dieSizes as $size) {
            if ($size < $this->max) {
                $this->add_flag('HasJustShrunk', $this->get_recipe());
                $this->max = $size;
                return;
            }
        }
    }

    // grow() is intended to be used for mighty dice
    public function grow() {
        $dieSizes = self::grow_shrink_die_sizes();
        sort($dieSizes);

        foreach ($dieSizes as $size) {
            if ($size > $this->max) {
                $this->add_flag('HasJustGrown', $this->get_recipe());
                $this->max = $size;
                $this->min = 1;  // deal explicitly with the possibility of 0-siders
                return;
            }
        }
    }

    public function get_recipe($addMaxvals = FALSE) {
        $recipe = '';
        foreach ($this->skillList as $skill) {
            if ($skill::do_print_skill_preceding()) {
                $recipe .= BMSkill::abbreviate_skill_name($skill);
            }
        }
        $recipe .= '(';

        // Option dice divide on a /, can contain any die type
        if ($this instanceof BMDieOption) {
            $recipe .= $this->get_sidecount_maxval_str(
                "{$this->optionValueArray[0]}/{$this->optionValueArray[1]}",
                $this,
                $addMaxvals
            );
        } elseif ($this instanceof BMDieTwin) {
            // Twin dice divide on a comma, can contain any type but option
            if ($this->dice[0] instanceof BMDieSwing) {
                $recipe .= $this->get_sidecount_maxval_str(
                    $this->dice[0]->swingType,
                    $this->dice[0],
                    $addMaxvals
                );
            } else {
                $recipe .= $this->dice[0]->max;
            }
            $recipe .= ',';
            if ($this->dice[1] instanceof BMDieSwing) {
                $recipe .= $this->get_sidecount_maxval_str(
                    $this->dice[1]->swingType,
                    $this->dice[1],
                    $addMaxvals
                );
            } else {
                $recipe .= $this->dice[1]->max;
            }
        } elseif ($this instanceof BMDieWildcard) {
            $recipe .= 'C';
        } elseif ($this instanceof BMDieSwing) {
            $recipe .= $this->get_sidecount_maxval_str(
                $this->swingType,
                $this,
                $addMaxvals
            );
        } else {
            $recipe .= $this->max;
        }

        $recipe .= ')';

        foreach ($this->skillList as $skill) {
            if (!$skill::do_print_skill_preceding()) {
                $recipe .= BMSkill::abbreviate_skill_name($skill);
            }
        }

        return $recipe;
    }

    /** helper function to print a die sidecount with or without its swing/option value
     *
     * @return string Representation of the side count of the die
     */
    protected function get_sidecount_maxval_str($sidecountStr, $dieObj, $addMaxval) {
        if ($addMaxval && isset($dieObj->max)) {
            return ($sidecountStr . '=' . $dieObj->max);
        } else {
            return ($sidecountStr);
        }
    }

    /** function that calculates how much a die can be fired up by a helper die
     *
     * @return int Number of sides that the die can be fired up
     */
    protected function get_firingMax() {
        return ($this->max - $this->value);
    }

    /** function that looks for the current object (BMDie) within the owning player's activeDieArray
     *
     * @return mixed Index of die in activeDieArray of the owning player
     */
    protected function get_activeDieIdx() {
        $owner = $this->ownerObject;

        if (!isset($owner) || !($owner instanceof BMGame)) {
            return NULL;
        }

        $activeDieArrayArray = $owner->activeDieArrayArray;

        // search for the exact instance of the current BMDie requires TRUE as third argument
        $dieIdx = array_search(
            $this,
            $activeDieArrayArray[$this->playerIdx],
            TRUE
        );

        if (FALSE === $dieIdx) {
            return NULL;
        }

        return $dieIdx;
    }

    // Return all information about a die which is useful when
    // constructing an action log entry, in the form of an array.
    // This function exists so that BMGame can easily compare the
    // die state before the attack to the die state after the attack.
    public function get_action_log_data() {
        $recipe = $this->get_recipe(TRUE);
        $valueAfterTripAttack = NULL;
        $recipeBeforeGrowing = NULL;
        $recipeBeforeShrinking = NULL;
        $recipeBeforeSplitting = NULL;
        if ($this->has_flag('JustPerformedTripAttack')) {
            $valueAfterTripAttack = $this->flagList['JustPerformedTripAttack']->value();
        }
        if ($this->has_flag('HasJustGrown')) {
            $recipeBeforeGrowing = $this->flagList['HasJustGrown']->value();
        }
        if ($this->has_flag('HasJustShrunk')) {
            $recipeBeforeShrinking = $this->flagList['HasJustShrunk']->value();
        }
        if ($this->has_flag('HasJustSplit')) {
            $recipeBeforeSplitting = $this->flagList['HasJustSplit']->value();
        }
        return(array(
            'recipe' => $recipe,
            'min' => $this->min,
            'max' => $this->max,
            'value' => $this->value,
            'doesReroll' => $this->doesReroll,
            'captured' => $this->captured,
            'recipeStatus' => $recipe . ':' . $this->value,
            'forceReportDieSize' => $this->forceReportDieSize(),
            'valueAfterTripAttack' => $valueAfterTripAttack,
            'hasJustMorphed' => $this->has_flag('HasJustMorphed'),
            'hasJustRerolledOrnery' => $this->has_flag('HasJustRerolledOrnery'),
            'recipeBeforeGrowing' => $recipeBeforeGrowing,
            'recipeBeforeShrinking' => $recipeBeforeShrinking,
            'recipeBeforeSplitting' => $recipeBeforeSplitting,
        ));
    }

    public function forceReportDieSize() {
        return ($this->has_skill('Mood') || $this->has_skill('Mad') ||
                $this->has_flag('HasJustMorphed'));
    }

    public function does_skip_swing_request() {
        $hookResult = $this->run_hooks(__FUNCTION__, array('die' => $this));

        $doesSkipSwingRequest = is_array($hookResult) &&
                                array_search('does_skip_swing_request', $hookResult);

        return $doesSkipSwingRequest;
    }

    public function has_flag($flag) {
        return array_key_exists($flag, $this->flagList);
    }

    public function add_flag($flag, $flagValue = NULL) {
        $flagString = $flag;

        if (isset($flagValue)) {
            if (is_array($flagValue)) {
                $flagString .= '__' . json_encode($flagValue);
            } else {
                $flagString .= '__' . $flagValue;
            }
        }

        $flagObject = BMFlag::create_from_string($flagString);
        if (isset($flagObject)) {
            $this->flagList[$flagObject->type()] = $flagObject;
        }
    }

    public function remove_flag($flag) {
        if ($this->has_flag($flag)) {
            unset($this->flagList[$flag]);
        }
    }

    public function remove_all_flags() {
        $this->flagList = array();
    }

    public function flags_as_string() {
        if (empty($this->flagList)) {
            return '';
        }

        return implode(';', $this->flagList);
    }

    public function load_flags_from_string($string) {
        if (empty($string)) {
            return;
        }

        $flagArray = explode(';', $string);
        foreach ($flagArray as $flag) {
            $this->add_flag($flag);
        }
    }

    public function getDieTypes() {
        $typesList = array();
        return $typesList;
    }

    // these are used for mood swing
    public static function standard_die_sizes() {
        return array(1, 2, 4, 6, 8, 10, 12, 20, 30);
    }

    // these are used for Mighty and Weak
    public static function grow_shrink_die_sizes() {
        return array(1, 2, 4, 6, 8, 10, 12, 16, 20, 30);
    }

    // utility methods
    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                case 'recipe':
                    return $this->get_recipe();
                case 'firingMax':
                    return $this->get_firingMax();
                case 'activeDieIdx':
                    return $this->get_activeDieIdx();
                default:
                    return $this->$property;
            }
        }
    }

    /**
     * Setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        $funcName = 'set__'.$property;
        if (method_exists($this, $funcName)) {
            $this->$funcName($value);
        } else {
            $this->$property = $value;
        }
    }

    protected function set__min() {
        throw new LogicException(
            'min is set at creation time.'
        );
    }

    protected function set__max($value) {
        if ($value === 0) {
            $this->min = 0;
            $this->max = 0;
        }

        if (!is_null($value) &&
            (FALSE ===
             filter_var(
                 $value,
                 FILTER_VALIDATE_INT,
                 array("options" => array("min_range"=>$this->min))
             )
            )
           ) {
            throw new InvalidArgumentException(
                'Invalid max die value.'
            );
        }
        $this->max = $value;
    }

    protected function set__value($value) {
        if (!is_null($value) &&
            (FALSE ===
             filter_var(
                 $value,
                 FILTER_VALIDATE_INT,
                 array("options" => array("min_range"=>$this->min,
                                          "max_range"=>$this->max))
             )
            )
           ) {
            throw new InvalidArgumentException(
                'Invalid die value: ' . $value . ' is not between ' .
                $this->min . ' and ' . $this->max . ' for die ' . $this
            );
        }
        $this->value = $value;
    }

    protected function set__recipe() {
        throw new LogicException(
            'Die recipe is derived automatically.'
        );
    }

    protected function set__ownerObject($value) {
        if (!(is_null($value) ||
              ($value instanceof BMButton) ||
              ($value instanceof BMGame) ||
              ($value instanceof TestDummyGame))) {
            throw new LogicException(
                'ownerObject must be NULL, a BMButton, a BMGame, or a TestDummyGame.'
            );
        }
        $this->ownerObject = $value;
    }

    protected function set__playerIdx($value) {
        if (!is_null($value) &&
            (FALSE ===
             filter_var(
                 $value,
                 FILTER_VALIDATE_INT,
                 array("options" => array("min_range"=>0,
                                          "max_range"=>1))
             )
            )
           ) {
            throw new InvalidArgumentException(
                'Invalid player index.'
            );
        }
        $this->playerIdx = $value;
    }

    protected function set__activeDieIdx() {
        throw new LogicException(
            'Die index is derived automatically.'
        );
    }

    protected function set__originalPlayerIdx($value) {
        if (!is_null($value) &&
            (FALSE ===
             filter_var(
                 $value,
                 FILTER_VALIDATE_INT,
                 array("options" => array("min_range"=>0,
                                          "max_range"=>1))
             )
            )
           ) {
            throw new InvalidArgumentException(
                'Invalid original player index.'
            );
        }
        $this->originalPlayerIdx = $value;
    }

    protected function set__doesReroll($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(
                'doesReroll is a boolean.'
            );
        }
        $this->doesReroll = $value;
    }

    protected function set__captured($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(
                'captured is a boolean.'
            );
        }
        $this->captured = $value;
    }

    protected function set__hasAttacked($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(
                'hasAttacked is a boolean.'
            );
        }
        $this->hasAttacked = $value;
    }

    protected function set__flagList($value) {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'flagList is an array.'
            );
        }
        foreach ($value as $item) {
            if (!($item instanceof BMFlag)) {
                throw new InvalidArgumentException(
                    'flagList can only contain BMFlag objects.'
                );
            }
        }
        $this->flagList = $value;
    }

    /**
     * Define behaviour of isset()
     *
     * @param string $property
     * @return boolean
     */
    public function __isset($property) {
        return isset($this->$property);
    }

    /**
     * Unset
     *
     * @param type $property
     * @return boolean
     */
    public function __unset($property) {
        if (isset($this->$property)) {
            unset($this->$property);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString() {
        return $this->get_recipe();
    }

    /**
     * To be run after a BMDie object is cloned.
     *
     * Doesn't do anything for the base class, but subclasses will need to
     * clone their subdice.
     */
    public function __clone() {
    }
}
