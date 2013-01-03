<?php
require_once 'BMSkill.php';

/*
 * BMDie: the fundamental unit of game mechanics
 *
 * @author: Julian Lighton
 */

class BMDie
{
    // properties

// an array keyed by function name. Value is an array of the skills
//  that are modifying that function
    protected $hookList = array();

// keyed by the Names of the skills that the die has, with values of
// the skill class's name
    protected $skillList = array();

// Basic facts about the die
    public $min;
    public $max;
    public $value;

// references back to the owner
    public $ownerObject;

    protected $scoreValue;

    protected $mRecipe;
    protected $mSides;
    protected $mSkills;

    protected $doesReroll = true;
    public $captured = false;

    public $hasAttacked = false;

// This is set when the button may not attack (sleep or focus, for instance)
// It is set to a string, so the cause may be described. It is cleared at
// the end of each of your turns.
    public $inactive = "";

// Set when the button isn't in the game for whatever reason, but
//  could suddenly join (Warrior Dice). Prevents from being attacked,
//  but not attacking
    public $unavailable = false;

    // unhooked methods

// Run the skill hooks for a given function. $args is an array of
//  argumentsfor the function.
//
// Important note on PHP references, since they make no bloody sense:
//
// To put a reference into the args array and have it still be such
// when you take it out again, you must:
//
// Put it into the args array as a reference: $args = array(&$foo)
// --AND--
// Take it out as a reference: $thing = &$args[0]

    protected function run_hooks($func, $args)
    {
        // get the hooks for the calling function

        if (!array_key_exists($func, $this->hookList)) {
            return;
        }

        foreach ($this->hookList[$func] as $skillClass)
        {
            $skillClass::$func($args);
        }
    }

    public function add_skill($skill)
    {
        $skillClass = "BMSkill$skill";

        // Don't add skills that are already added
        if (!array_key_exists($skill, $this->skillList)) {
            $this->skillList[$skill] = $skillClass;

            foreach ($skillClass::$hooked_methods as $func) {
                $this->hookList[$func][] = $skillClass;
            }
        }
    }

// This one may need to be hookable. So might add_skill, depending on
//  how Chaotic shakes out.
    public function remove_skill($skill)
    {
        if (!$this->has_skill($skill)) {
            return FALSE;
        }

        $skillClass = $this->skillList[$skill];

        unset($this->skillList[$skill]);

        foreach ($skillClass::$hooked_methods as $func) {
            $key = array_search($skillClass, $this->hookList[$func], TRUE);
            if ($key === FALSE) {
                // should never happen, and we should error hard if it does
            }
            unset($this->hookList[$func][$key]);
        }

        return TRUE;
    }

    public function has_skill($skill)
    {
        return array_key_exists($skill, $this->skillList);
    }

// This needs to be fixed to work properly within PHP's magic method semantics
//
// will need an init_from_db method, too (eventually)
    public function init($sides, $skills = array())
    {
        $this->min = 1;
        $this->max = $sides;

        $this->scoreValue = $sides;

        foreach ($skills as $s)
        {
            $this->add_skill($s);
        }
    }

    // given a string describing a die and a list of skills, return a
    // new BMDie or appropriate subclass thereof

    // Depending on implementation details, this may end up being
    // replaced with something that doesn't need to do string parsing

    public static function create_from_string($recipe, $skills) {

        try {
            $opt_list = explode("|", $recipe);

            // Option dice divide on a |, can contain any die type
            if (count($opt_list) > 1) {
                return BMOptionDie::create_from_list($opt_list, $skills);
            }
            // Twin dice divide on a comma, can contain any type but option
            elseif (count($twin_list = explode(",", $recipe)) > 1) {
                return BMTwinDie::create_from_list($twin_list, $skills);
            }
            elseif ($recipe == "C") {
                return BMWildcardDie::create($recipe, $skills);
            }
            // Integers are normal dice
            elseif (is_numeric($recipe) && ($recipe == (int)$recipe)) {
                return BMDie::create($recipe, $skills);
            }
            // Single character that's not a number is a swing die
            elseif (strlen($recipe) == 1) {
                return BMSwingDie::create($recipe, $skills);
            }
            // oops
            throw new UnexpectedValueException("Invalid recipe: $recipe");
        }
        catch (UnexpectedValueException $e) {
            return NULL;
        }

    }

    public static function create($size, $skills) {
        if (!is_numeric($size) || ($size != (int)$size) ||
            $size < 1 || $size > 99) {
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

    public function activate($playerIdx)
    {
        $newDie = clone $this;

        $this->run_hooks(__FUNCTION__, array($newDie));

        $this->ownerObject->add_die($newDie, $playerIdx);
    }

// Roll the die into a game. Clone self, roll, return the clone.
    public function make_play_die()
    {
        $newDie = clone $this;

        $newDie->roll(FALSE);

        $this->run_hooks(__FUNCTION__, array($newDie));

        return $newDie;
    }


    public function roll($successfulAttack = FALSE)
    {

        if ($this->doesReroll) {
            $this->value = mt_rand($this->min, $this->max);
        }

        $this->run_hooks(__FUNCTION__, array($successfulAttack));
    }

    public function attack_list()
    {
        $list = array("Power", "Skill");

        $this->run_hooks(__FUNCTION__, array(&$list));

        return $list;
    }

    // Return all possible values the die may use in this type of attack
    //
    // The values must be sorted, highest to lowest, with no duplication.
    public function attack_values($type)
    {
        $list = array($this->value);

        $this->run_hooks(__FUNCTION__, array($type, &$list));

        return $list;
    }

    public function defense_value($type)
    {
        $val = $this->value;

        $this->run_hooks(__FUNCTION__, array($type, &$val));

        return $val;
    }

// returns ten times the "real" scoring value
//
// We do not want to use floating-point math -- there's a real risk of
// having 10.5 not equal 10.5.
//
// We use a multiplier and divisor so various skills can manipulate them
// without stepping on each others' toes
    public function get_scoreValue()
    {
        $mult = 1;
        if ($this->captured) {
            $div = 1;
        }
        else {
            $div = 2;
        }

        $this->run_hooks(__FUNCTION__, array(&$this->scoreValue, $mult, $div, $this->captured));

        return (10 * $this->scoreValue * $mult) / $div;
    }

    // Return an array of the die's possible initiative values. 0
    // means it doesn't count for initiative. "?" means it's a chance
    // die.

    public function initiative_value()
    {
        $vals = array($this->value);

        $this->run_hooks(__FUNCTION__, array(&$vals));

        return $vals;
    }


    // Returns what values the die can contribute to an attack that
    // it's not actually participating in.
    //
    // Fire is currently the only skill that requires this
    //
    // Returned values must be sorted from lowest to highest, and zero
    // must be ommited unlees you cannot contribute.
    //
    // The attack code currently assumes that every value between the
    // lowest and highest is possible, and that 1 and -1 are possible
    // values if the help values go above or below zero. If that
    // changes, the code'll need some work.
    //
    // It does not assume that the values are positive, even though
    // they must be at the moment.
    public function assist_values($type, $attackers, $defenders) {

        $vals = array(0);

        // Attackers can't help their own attack
        if (FALSE !== array_search($this, $attackers)) {
            return $vals;
        }

        $this->run_hooks(__FUNCTION__, array($type, $attackers, $defenders, &$vals));

        return $vals;
    }

    // Actually contribute to an attack. Returns true if the attack
    // was contributed to, false otherwise.
    //
    // Returning false in normal usage indicates an error somewhere or
    // an attempt to cheat.
    //
    // once again, this is just for Fire
    public function attack_contribute($type, $attackers, $defenders, $amount) {
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

        // Hooks are where the die gets adjusted if need be.
        if ($valid) {
            $this->run_hooks(__FUNCTION__, array($type, $attackers, $defenders, $amount));
        }
        return $valid;

    }

// check for special-case situations where an otherwise-valid attack
// is not legal. Single-die skill attacks with stealth dice are the only
// situation I can come up with off the top of my head
//
// These methods cannot act, they may only check: they're called a lot
    public function valid_attack($type, $attackers, $defenders)
    {
        $valid = TRUE;

        if ($this->inactive || $this->hasAttacked) {
            $valid = FALSE;
        }


        // Are we actually among the attackers?
        $found = FALSE;

        foreach ($attackers as $die) {
            if ($die === $this) {
                $found = TRUE;
                break;
            }
        }
        if (!$found) {
            $valid = FALSE;
        }


        $this->run_hooks(__FUNCTION__, array($type, $attackers, $defenders, &$valid));

        return $valid;
    }


    public function valid_target($type, $attackers, $defenders)
    {
        $valid = TRUE;

        if ($this->unavailable) {
            $valid = FALSE;
        }

        // Are we actually among the defenders?
        $found = FALSE;

        foreach ($defenders as $die) {
            if ($die === $this) {
                $found = TRUE;
                break;
            }
        }
        if (!$found) {
            $valid = FALSE;
        }


        $this->run_hooks(__FUNCTION__, array($type, $attackers, $defenders, &$valid));

        return $valid;
    }

    public function capture($type, $attackers, $victims)
    {
        $this->run_hooks(__FUNCTION__, array());
    }


    public function be_captured($type, $attackers, $victims)
    {
        $this->captured = TRUE;

        $this->run_hooks(__FUNCTION__, array());
    }

// Print long description
    public function describe()
    {
        $this->run_hooks(__FUNCTION__, array());
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
//
// constant needs to hook this method to fix the die's value. Very
// little else will.
    public function split()
    {
        $newdie = clone $this;

        if ($newdie->max > 1) {
            $remainder = $newdie->max % 2;
            $newdie->max -= $remainder;
            $newdie->max = $newdie->max / 2;
            $this->max -= $newdie->max;
        }

        $dice = array($this, $newdie);

        $this->run_hooks(__FUNCTION__, array(&$dice));

        return $dice;
    }

    public function start_turn($player)
    {
        $this->run_hooks(__FUNCTION__, array($player));
    }

    public function end_turn($player)
    {
        if ($player === $this->owner) {
            $this->inactive = "";
        }

        $this->run_hooks(__FUNCTION__, array($player));

        $this->hasAttacked = FALSE;
    }

    public function start_round()
    {
        $this->run_hooks(__FUNCTION__, array());
    }

    public function end_round()
    {
        $this->run_hooks(__FUNCTION__, array());
    }


    // utility methods

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            switch ($property) {
                case '$mRecipe':
                    return ($this->mSkills).($this->mSides);

                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value)
    {
        switch ($property) {
            case 'mSides':
                // require a positive integer number of sides if an integer is provided
                if (filter_var($value,
                               FILTER_VALIDATE_INT,
                               array("options"=>array("min_range"=>1)))) {
                    $this->mSides = $value;
                // check for a swing die
                } elseif (filter_var($value,
                               FILTER_VALIDATE_REGEXP,
                               array("options"=>array("regexp"=>"/[[:alpha:]]/")))) {
                    $this->mSides = $value;
                // check for an option die
                } elseif (filter_var($value,
                               FILTER_VALIDATE_REGEXP,
                               array("options"=>array("regexp"=>"#.+/.+#")))) {
                    $this->mSides = $value;
                // this is an invalid number of sides
                } else {
                    throw new InvalidArgumentException("Invalid number of sides.");
                }
                break;

            case 'mRecipe':
                throw new Exception("Cannot set recipe directly.");
                break;

            default:
                $this->$property = $value;
        }
    }

    public function __toString()
    {
        print($this->mRecipe);
    }

    public function __clone() {
        // Doesn't do anything for the base class, but subclasses will
        // need to clone their subdice.
    }
}

class BMSwingDie extends BMDie {
    public $swingType;
    public $swingValue;
    public $swingMax;
    public $swingMin;
    protected $needsValue = TRUE;
    protected $valueRequested = FALSE;

    // To allow correct behavior for turbo and mood swings that get
    // cut in half.
    protected $divisor = 1;
    protected $remainder = 0;


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

    public function init($type, $skills = array()) {
        $this->min = 1;

        $this->divisor = 1;
        $this->remainder = 0;

        $this->needsValue = TRUE;
        $this->valueRequested = FALSE;

        $this->swingType = $type;

        $range = $this->swing_range($type);
        if (is_null($range)) {
            throw new UnexpectedValueException("Invalid swing type: $type");
        }
        $this->swingMin = $range[0];
        $this->swingMax = $range[1];

        foreach ($skills as $s)
        {
            $this->add_skill($s);
        }

    }

    public static function create($recipe, $skills = array()) {

        if (!is_string($recipe) || strlen($recipe) != 1 ||
            ord("R") > ord($recipe) || ord($recipe) > ord("Z")) {
            throw new UnexpectedValueException("Invalid recipe: $recipe");
        }

        $die = new BMSwingDie;

        $die->init($recipe, $skills);

        return $die;

    }

    public function activate($playerIdx) {
        $newDie = clone $this;

        $this->run_hooks(__FUNCTION__, array($newDie));

        // The clone is the one going into the game, so it's the one
        // that needs a swing value to be set.
        $this->ownerObject->request_swing_values($newDie, $newDie->swingType);
        $newDie->valueRequested = TRUE;

        $this->ownerObject->add_die($newDie, $playerIdx);
    }

    public function make_play_die()
    {
        if (!($this->ownerObject instanceof BMGame)) {
            throw new LogicException(
                'Play dice can only be added to a BMGame.');
        }

        // Get swing value from the game before cloning, so it's saved
        // from round to round.
        if ($this->needsValue) {
            $this->ownerObject->require_values();
        }

        return parent::make_play_die();
    }

    public function roll($successfulAttack = FALSE)
    {
        if ($this->needsValue) {
            if (!$this->valueRequested) {
                $this->ownerObject->request_swing_values($this, $this->swingType);
                $this->valueRequested = TRUE;
            }
            $this->ownerObject->require_values();
        }

        parent::roll($successfulAttack);
    }

// Print long description
    public function describe()
    {
        $this->run_hooks(__FUNCTION__, array());
    }

    public function split()
    {
        $this->divisor *= 2;
        $this->remainder = 0;

        $dice = parent::split();

        if ($this->max > $dice[1]->max) {
            $this->remainder = 1;
        }

        return $dice;
    }

    public function set_swingValue($swingList) {
        $valid = TRUE;

        if (!array_key_exists($this->swingType, $swingList)) {
            return FALSE;
        }

        $sides = $swingList[$this->swingType];

        if ($sides < $this->swingMin || $sides > $this->swingMax) {
            return FALSE;
        }

        $this->run_hooks(__FUNCTION__, array(&$valid, $swingList));

        if ($valid) {
            $this->swingValue = $sides;

            // Don't need to ask for a swing value any more
            $this->needsValue = FALSE;
            $this->valueRequested = FALSE;

            // correctly handle cut-in-half swing dice, however many
            // times they may have been cut
            for($i = $this->divisor; $i > 1; $i /= 2) {
                if ($sides > 1) {
                    $rem = $sides % 2;
                    $sides -= $rem;
                    $sides /= 2;
                    if ($rem && $this->remainder) {
                        $sides += 1;
                    }
                }

            }
            $this->max = $sides;
        }

        return $valid;

    }



}

class BMWildcardDie extends BMDie {

}

class BMTwinDie extends BMDie {

}

class BMOptionDie extends BMDie {

}



?>
