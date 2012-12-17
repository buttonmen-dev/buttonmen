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

// references back to the game we're part of
    public $game;
    public $owner;

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
    public function init($sides, $skills)
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
        if ($size < 1 || $size > 99) {
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

    public function activate($game, $owner)
    {
        $newDie = clone $this;

        $newDie->game = $game;
        $newDie->owner = $owner;

        $this->run_hooks(__FUNCTION__, array($newDie));

        return $newDie;
    }

// Roll the die into a game. Clone self, roll, return the clone.
    public function first_roll()
    {
        $newDie = clone $this;

        $newDie->roll(FALSE);

        $this->run_hooks(__FUNCTION__, array($newDie));

        return $newDie;
    }


    public function roll($successfulAttack)
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

    public function split()
    {
        $this->run_hooks(__FUNCTION__, array());
    }

    public function start_turn($player)
    {
        $this->run_hooks(__FUNCTION__, array());
    }

    public function end_turn($player)
    {
        $this->run_hooks(__FUNCTION__, array());
    }

    public function start_round($player)
    {
        $this->run_hooks(__FUNCTION__, array());
    }

    public function end_round($player)
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
# validation logic:
#                    $ord("R") <= $ord($recipe) &&
#                    $ord($recipe) <= $ord("Z")
}

class BMWildcardDie extends BMDie {

}

class BMTwinDie extends BMDie {

}

class BMOptionDie extends BMDie {

}



?>
