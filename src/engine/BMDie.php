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
 * @property      BMGame|BMButton $ownerObject  Game or button that owns the die
 * @property      int    $playerIdx             Index of player that currently owns the die
 * @property-read int    $activeDieIdx          Index of die in BMPlayer->activeDieArray
 * @property      int    $originalPlayerIdx     Index of player that originally owned the die
 * @property      bool   $doesReroll            Can the die reroll?
 * @property      bool   $captured              Has the die has been captured?
 * @property      bool   $outOfPlay             Is the die out of play?
 * @property-read array  $flagList              Array designed to contain various BMFlags
 *
 *  */
class BMDie extends BMCanHaveSkill {
    // properties

// Basic facts about the die
    /**
     * Minimum die value
     *
     * @var int
     */
    protected $min;

    /**
     * Maximum die value
     *
     * @var int
     */
    protected $max;

    /**
     * Current die value
     *
     * @var int
     */
    protected $value;

    /**
     * Die recipe
     *
     * @var string
     */
    protected $recipe;

    /**
     * Maximum amount that the die can be fired up
     *
     * @var int
     */
    protected $firingMax;

    /**
     * Game or button that owns the die
     *
     * @var BMGame|BMButton
     */
    protected $ownerObject;

    /**
     * Index of player that currently owns the die
     *
     * @var int
     */
    protected $playerIdx;

    /**
     * Index of die in BMPlayer->activeDieArray
     *
     * @var int
     */
    protected $activeDieIdx;

    /**
     * Index of player that originally owned the die
     *
     * @var int
     */
    protected $originalPlayerIdx;

    /**
     * Flag signalling whether the die can reroll
     *
     * @var bool
     */
    protected $doesReroll = TRUE;

    /**
     * Flag signalling whether the die has been captured
     *
     * @var bool
     */
    protected $captured = FALSE;

    /**
     * Flag signalling whether the die is out of play
     *
     * @var bool
     */
    protected $outOfPlay = FALSE;

    /**
     * Array designed to contain various BMFlags
     *
     * @var array
     */
    protected $flagList = array();

    /**
     * Set number of sides of the die, and add die skills
     *
     * Hackish: the caller can specify each skill as either a plain
     * value, "skill", or a key/value pair "ClassName" => "skill",
     * where the key is the class name which implements that skill.
     * This is only for use by callers outside of engine (e.g.
     * testing), and should never be used for the default BMSkill*
     * set of skills.
     *
     * @param int $sides
     * @param array $skills
     */
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

    /**
     * Parse the die recipe to extract the number of sides
     *
     * @param string $recipe
     * @return string
     */
    protected static function parse_recipe_for_sides($recipe) {
        if (preg_match('/\((.*)\)/', $recipe, $match)) {
            return $match[1];
        } else {
            return '';
        }
    }

    /**
     * Parse the die recipe to extract the skills into an array of BMSkills
     *
     * @param string $recipe
     * @return array
     */
    protected static function parse_recipe_for_skills($recipe) {
        return BMSkill::expand_skill_string(preg_replace('/\(.*\)/', '', $recipe));
    }

    /**
     * Determine if there is an unimplemented skill in a skill recipe string
     *
     * @param string $recipe
     * @return bool
     */
    public static function unimplemented_skill_in_recipe($recipe) {
        return BMSkill::unimplemented_skill_in_string(preg_replace('/\(.*\)/', '', $recipe));
    }

    // given a string describing a die and a list of skills, return a
    // new BMDie or appropriate subclass thereof

    // Depending on implementation details, this may end up being
    // replaced with something that doesn't need to do string parsing

    /**
     * Create an appropriate type of BMDie from a core recipe (the bit inside
     * the parentheses), and then add skills.
     *
     * @param string $recipe
     * @param array $skills
     * @return BMDie
     */
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

    /**
     * Create an appropriate type of BMDie from a full die recipe
     *
     * @param string $recipe
     * @return BMDie
     */
    public static function create_from_recipe($recipe) {
        $sides = BMDie::parse_recipe_for_sides($recipe);
        $skills = BMDie::parse_recipe_for_skills($recipe);
        return BMDie::create_from_string_components($sides, $skills);
    }

    /**
     * Create an appropriate BMDie with a specified number of sides, then
     * add skills to the die.
     *
     * @param int $size
     * @param array $skills
     * @return BMDie
     */
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

    /**
     * Wakes up a die from its container to be used in a game.
     * Does not roll the die.
     *
     * Clones the die and returns the clone.
     */
    public function activate() {
        $newDie = clone $this;

        $this->ownerObject->add_die($newDie);
    }

    /**
     * Roll the die into a game. Clone self, roll, return the clone.
     *
     * @return BMDie
     */
    public function make_play_die() {
        $newDie = clone $this;
        $newDie->roll();
        return $newDie;
    }

    /**
     * Roll die
     *
     * @param bool $isTriggeredByAttack
     * @param bool $isSubdie
     */
    public function roll($isTriggeredByAttack = FALSE, $isSubdie = FALSE) {
        $this->run_hooks('pre_roll', array('die' => $this,
                                           'isTriggeredByAttack' => $isTriggeredByAttack,
                                           'isSubdie' => $isSubdie));

        $hookResultArray = $this->run_hooks(__FUNCTION__, array('die' => $this));
        $doHooksAllowReroll =
            empty($hookResultArray) ||
            (0 == count(array_filter($hookResultArray, function ($value) {
                return $value !== FALSE;
            })));

        $turboDieIsAboutToBeReplaced =
            is_array($hookResultArray) &&
            array_key_exists('BMSkillTurbo', $hookResultArray) &&
            $hookResultArray['BMSkillTurbo'] &&
            !$this->has_flag('JustPerformedTripAttack') &&
            !$this->has_flag('IsAboutToPerformTripAttack');

        $needsToReroll =
            !isset($this->value) ||
            ($this->doesReroll &&
             !$this->has_flag('JustPerformedTripAttack') &&
             $doHooksAllowReroll);

        if ($turboDieIsAboutToBeReplaced) {
            unset($this->value);
        } elseif ($needsToReroll) {
            $this->set__value(bm_rand($this->min, $this->max));
        }

        $this->run_hooks('post_roll', array('die' => $this,
                                            'isTriggeredByAttack' => $isTriggeredByAttack));
    }

    /**
     * Return all possible values the die may use in this type of attack.
     * The values must be sorted, highest to lowest, with no duplication.
     *
     * @param string $type
     * @return array
     */
    public function attack_values($type) {
        $list = array($this->value);

        $this->run_hooks(__FUNCTION__, array('attackType' => $type,
                                             'attackValues' => &$list,
                                             'minValue' => $this->min,
                                             'value' => $this->value));
        return $list;
    }

    /**
     * Defense value of the die
     *
     * @return int
     */
    public function defense_value() {
        $val = $this->value;
        return $val;
    }

//
    /**
     * Returns ten times the "real" scoring value
     *
     * We do not want to use floating-point math -- there's a real risk of
     * having 10.5 not equal 10.5.
     *
     * We use a multiplier and divisor so various skills can manipulate them
     * without stepping on each others' toes
     *
     * @return int
     */
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

    //

    /**
     * Return die's initiative value.
     * Negative means it doesn't count for initiative.
     *
     * @return int
     */
    public function initiative_value() {
        $val = $this->value;

        $this->run_hooks(__FUNCTION__, array('initiativeValue' => &$val));

        return $val;
    }

    /**
     * Returns what values the die can contribute to an attack that
     * it's not actually participating in.
     *
     * Fire is currently the only skill that requires this
     *
     * Returned values must be sorted from lowest to highest, and zero
     * must be omitted unless you cannot contribute.
     *
     * The attack code currently assumes that every value between the
     * lowest and highest is possible, and that 1 and -1 are possible
     * values if the help values go above or below zero. If that
     * changes, the code'll need some work.
     *
     * It does not assume that the values are positive, even though
     * they must be at the moment.
     *
     * @param string $type
     * @param array $attackers
     * @return array
     */
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


    /**
     * Actually contribute to an attack. Returns true if the attack
     * was contributed to, false otherwise.
     *
     * Returning false in normal usage indicates an error somewhere or
     * an attempt to cheat.
     *
     * once again, this is just for Fire
     *
     * @param string $type
     * @param array $attackers
     * @param array $defenders
     * @param int $amount
     * @return bool
     */
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

    /**
     * Check for special-case situations where an otherwise-valid attacker
     * is not legal
     *
     * @param array $attackers
     * @return bool
     */
    public function is_valid_attacker(array $attackers) {
        return in_array($this, $attackers, TRUE);
    }

    /**
     * Check for special-case situations where an otherwise-valid defender
     * is not legal
     *
     * @param array $defenders
     * @return bool
     */
    public function is_valid_target(array $defenders) {
        $valid = in_array($this, $defenders, TRUE);

        return $valid;
    }

    /**
     * Run die hooks that trigger before capture when the die is an attacker.
     *
     * This allows attacks that have a possibility of not capturing to add
     * various flags before rage triggers.
     *
     * @param string $type
     * @param array $attackers
     * @param array $defenders
     */
    public function pre_capture($type, array &$attackers, array &$defenders) {
        $this->run_hooks(__FUNCTION__, array('type' => $type,
                                             'attackers' => &$attackers,
                                             'defenders' => &$defenders,
                                             'caller' => $this));
    }

    /**
     * Run die hooks that trigger before capture when the die is an defender.
     *
     * This allows rage to clone the die before anything else happens to it.
     *
     * @param string $type
     * @param array $attackers
     * @param array $defenders
     */
    public function pre_be_captured($type, array &$attackers, array &$defenders) {
        $this->run_hooks(__FUNCTION__, array('type' => $type,
                                             'attackers' => &$attackers,
                                             'defenders' => &$defenders,
                                             'caller' => $this));
    }

    /**
     * Run die hooks that trigger at capture when the die is an attacker
     *
     * @param string $type
     * @param array $attackers
     * @param array $defenders
     */
    public function capture($type, array &$attackers, array &$defenders) {
        $this->run_hooks(__FUNCTION__, array('type' => $type,
                                             'attackers' => &$attackers,
                                             'defenders' => &$defenders,
                                             'caller' => $this));
    }


    /**
     * Run die hooks that trigger at capture when the die is an defender
     *
     * @param string $type
     * @param array $attackers
     * @param array $defenders
     */
    public function be_captured($type, array &$attackers, array &$defenders) {
        $this->run_hooks(__FUNCTION__, array('type' => $type,
                                             'attackers' => &$attackers,
                                             'defenders' => &$defenders,
                                             'caller' => $this));
    }

    /**
     * Print long description
     *
     * @param bool $isValueRequired
     * @return string
     */
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

    /**
     * split a die in twain. If something needs to cut a die's size in
     * half, it should use this and throw one part away. (Or toss both;
     * all references to the original die will pick up the split.)
     *
     * In the case of an odd number of sides, the remainder stays with the
     * original die
     *
     * @return array
     */
    public function split() {
        $oldRecipe = $this->get_recipe(TRUE);
        unset($this->value);
        $newdie = clone $this;

        $remainder = $newdie->max % 2;
        $newdie->max -= $remainder;
        $newdie->max = $newdie->max / 2;
        $this->max -= $newdie->max;

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

    /**
     * shrink() is intended to be used for weak dice
     */
    public function shrink() {
        $dieSizes = self::grow_shrink_die_sizes();
        rsort($dieSizes);

        foreach ($dieSizes as $size) {
            if ($size < $this->max) {
                $this->add_flag('HasJustShrunk', $this->get_recipe());
                $this->max = $size;
                unset($this->value);
                return;
            }
        }
    }

    /**
     * grow() is intended to be used for mighty dice
     */
    public function grow() {
        $dieSizes = self::grow_shrink_die_sizes();
        sort($dieSizes);

        foreach ($dieSizes as $size) {
            if ($size > $this->max) {
                $this->add_flag('HasJustGrown', $this->get_recipe());
                $this->max = $size;
                $this->min = 1;  // deal explicitly with the possibility of 0-siders
                unset($this->value);
                return;
            }
        }
    }

    /**
     * Get recipe of die
     *
     * @param bool $addMaxvals
     * @return string
     */
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

    /**
     * helper function to print a die sidecount with or without its swing/option value
     *
     * @param string $sidecountStr
     * @param BMDie $dieObj
     * @param bool $addMaxval
     * @return string Representation of the side count of the die
     */
    protected function get_sidecount_maxval_str($sidecountStr, $dieObj, $addMaxval) {
        if ($addMaxval && isset($dieObj->max)) {
            return ($sidecountStr . '=' . $dieObj->max);
        } else {
            return ($sidecountStr);
        }
    }

    /**
     * function that calculates how much a die can be fired up by a helper die
     *
     * @return int Number of sides that the die can be fired up
     */
    protected function get_firingMax() {
        return ($this->max - $this->value);
    }

    /**
     * function that looks for the current object (BMDie) within the owning player's activeDieArray
     *
     * @return mixed Index of die in activeDieArray of the owning player
     */
    protected function get_activeDieIdx() {
        $owner = $this->ownerObject;

        if (!isset($owner) || !($owner instanceof BMGame)) {
            return NULL;
        }

        // search for the exact instance of the current BMDie requires TRUE as third argument
        $dieIdx = array_search(
            $this,
            $owner->playerArray[$this->playerIdx]->activeDieArray,
            TRUE
        );

        if (FALSE === $dieIdx) {
            return NULL;
        }

        return $dieIdx;
    }

    /**
     * Return all information about a die which is useful when
     * constructing an action log entry, in the form of an array.
     * This function exists so that BMGame can easily compare the
     * die state before the attack to the die state after the attack.
     *
     * @return array
     */
    public function get_action_log_data() {
        $recipe = $this->get_recipe(TRUE);

        if (!isset($this->value)) {
            $value = NULL;
            $recipeStatus = $recipe;
        } else {
            $value = $this->value;
            $recipeStatus = $recipe . ':' . $value;
        }

        $actionLogInfo = array(
            'recipe' => $recipe,
            'min' => $this->min,
            'max' => $this->max,
            'value' => $value,
            'doesReroll' => $this->doesReroll,
            'captured' => $this->captured,
            'outOfPlay' => $this->outOfPlay,
            'recipeStatus' => $recipeStatus,
        );

        if ($this->forceReportDieSize()) {
            $actionLogInfo['forceReportDieSize'] = TRUE;
        }

        if ($this->forceHideDieReroll()) {
            $actionLogInfo['forceHideDieReroll'] = TRUE;
        }

        $this->addFlagInfoToActionLog($actionLogInfo);

        return($actionLogInfo);
    }

    /**
     * Add flag information to the action log.
     *
     * @param array $actionLogInfo
     */
    protected function addFlagInfoToActionLog(array &$actionLogInfo) {
        if ($this->has_flag('HasJustMorphed')) {
            $actionLogInfo['hasJustMorphed'] = TRUE;
        }

        if ($this->has_flag('HasJustTurboed')) {
            $actionLogInfo['hasJustTurboed'] = $this->flagList['HasJustTurboed']->value();
        }

        if ($this->has_flag('HasJustRerolledOrnery')) {
            $actionLogInfo['hasJustRerolledOrnery'] = TRUE;
        }

        if ($this->has_flag('JustPerformedTripAttack')) {
            // the value in the flag should now look something like 'B(10):6', but
            // old log entries may still have just the value, so deal with both options
            $postTripRecipeAndValue = $this->flagList['JustPerformedTripAttack']->value();
            $postTripDetails = explode(':', $postTripRecipeAndValue);

            if (1 == count($postTripDetails)) {
                $actionLogInfo['valueAfterTripAttack'] = $postTripDetails[0];
            } else {
                $actionLogInfo['recipeAfterTripAttack'] = $postTripDetails[0];
                $actionLogInfo['valueAfterTripAttack'] = $postTripDetails[1];
            }
        }

        if ($this->has_flag('JustPerformedBerserkAttack')) {
            $actionLogInfo['recipeAfterBerserkAttack'] =
                $this->flagList['JustPerformedBerserkAttack']->value();
        }

        if ($this->has_flag('HasJustGrown')) {
            $actionLogInfo['recipeBeforeGrowing'] =
                $this->flagList['HasJustGrown']->value();
        }

        if ($this->has_flag('HasJustShrunk')) {
            $actionLogInfo['recipeBeforeShrinking'] =
                $this->flagList['HasJustShrunk']->value();
        }

        if ($this->has_flag('HasJustSplit')) {
            $actionLogInfo['recipeBeforeSplitting'] =
                $this->flagList['HasJustSplit']->value();
        }

        if ($this->has_flag('IsRageTargetReplacement')) {
            $actionLogInfo['isRageTargetReplacement'] = TRUE;
        }
    }

    /**
     * Determine whether the die size always needs to be reported, even when
     * there is no change.
     *
     * @return bool
     */
    public function forceReportDieSize() {
        return ($this->has_skill('Mood') || $this->has_skill('Mad') ||
                $this->has_flag('HasJustMorphed') ||
                $this->has_flag('HasJustTurboed'));
    }

    /**
     * Determine whether the die reroll needs to be suppressed.
     *
     * @return bool
     */
    public function forceHideDieReroll() {
        return ($this->has_skill('Turbo') &&
                ($this->has_flag('IsAttacker') ||
                 $this->has_flag('JustPerformedTripAttack')));
    }

    /**
     * Determine whether the die skips the swing request phase
     *
     * @return bool
     */
    public function does_skip_swing_request() {
        $hookResult = $this->run_hooks(__FUNCTION__, array('die' => $this));

        $doesSkipSwingRequest = is_array($hookResult) &&
                                array_search('does_skip_swing_request', $hookResult);

        return $doesSkipSwingRequest;
    }

    /**
     * Checks whether a die has a certain flag
     *
     * @param string $flag
     * @return bool
     */
    public function has_flag($flag) {
        return array_key_exists($flag, $this->flagList);
    }

    /**
     * Add a flag to the die
     *
     * @param string $flag
     * @param mixed $flagValue
     */
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

    /**
     * Remove a flag from the die
     *
     * @param string $flag
     */
    public function remove_flag($flag) {
        if ($this->has_flag($flag)) {
            unset($this->flagList[$flag]);
        }
    }

    /**
     * Remove all flags from the die
     */
    public function remove_all_flags() {
        $this->flagList = array();
    }

    /**
     * Print list of flags
     *
     * @return string
     */
    public function flags_as_string() {
        if (empty($this->flagList)) {
            return '';
        }

        return implode(';', $this->flagList);
    }

    /**
     * Load flags from a string and add them to the die
     *
     * @param string $string
     */
    public function load_flags_from_string($string) {
        if (empty($string)) {
            return;
        }

        $flagArray = explode(';', $string);
        foreach ($flagArray as $flag) {
            $this->add_flag($flag);
        }
    }

    /**
     * Get all die types.
     *
     * @return array
     */
    public function getDieTypes() {
        $typesList = array();
        return $typesList;
    }

    /**
     * Attempt to do a turbo size set
     *
     * @param int $size
     */
    public function setTurboSize($size) {
        if (isset($this->swingType)) {
            $setSuccess = $this->set_swingValue(array($this->swingType => $size));
            if (!$setSuccess) {
                throw new LogicException('Invalid swing value for turbo die');
            }
        } elseif ($this instanceof BMDieOption) {
            $setSuccess = $this->set_optionValue($size);
            if (!$setSuccess) {
                throw new LogicException('Invalid option value for turbo die');
            }
        } else {
            if ((int)$size !== $this->max) {
                throw new LogicException('Cannot change die size for a non-swing, non-option turbo die');
            }
        }
    }

    /**
     * The standard die sizes are used for mood swing
     *
     * @return array
     */
    public static function standard_die_sizes() {
        return array(1, 2, 4, 6, 8, 10, 12, 20, 30);
    }

    /**
     * The die sizes are used for weak and mighty
     *
     * @return array
     */
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

    /**
     * Set the minimum value of the die
     */
    protected function set__min() {
        throw new LogicException(
            'min is set at creation time.'
        );
    }

    /**
     * Set the maximum value of the die
     *
     * @param int $value
     */
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

    /**
     * Set the value of the die
     *
     * @param int $value
     */
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

    /**
     * Set the recipe of the die
     *
     * @param string $value
     */
    protected function set__recipe() {
        throw new LogicException(
            'Die recipe is derived automatically.'
        );
    }

    /**
     * Set the ownerObject of the die
     *
     * @param mixed $value
     */
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

    /**
     * Set the index value of the player who owns the die
     *
     * @param int $value
     */
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

    /**
     * Prevent direct setting of the active die index
     */
    protected function set__activeDieIdx() {
        throw new LogicException(
            'Die index is derived automatically.'
        );
    }

    /**
     * Set the player index of the player who originally owned the die
     *
     * @param int $value
     */
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

    /**
     * Set whether a die rerolls
     *
     * @param int $value
     */
    protected function set__doesReroll($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(
                'doesReroll is a boolean.'
            );
        }
        $this->doesReroll = $value;
    }

    /**
     * Set whether a die has been captured
     *
     * @param int $value
     */
    protected function set__captured($value) {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(
                'captured is a boolean.'
            );
        }
        $this->captured = $value;
    }

    /**
     * Set flag list of the die
     *
     * @param array $value
     */
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
     * @return bool
     */
    public function __isset($property) {
        return isset($this->$property);
    }

    /**
     * Unset
     *
     * @param mixed $property
     * @return bool
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
