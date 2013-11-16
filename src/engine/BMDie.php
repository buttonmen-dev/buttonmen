<?php

require_once('BMSkill.php');

/*
 * BMDie: the fundamental unit of game mechanics
 *
 * @author: Julian Lighton
 */

class BMDie {
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
    protected $recipe;

// references back to the owner
    public $ownerObject;
    public $playerIdx;
    public $originalPlayerIdx;

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

    public function run_hooks($func, $args)
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

    // Other code inside engine must never set $skillClass, but
    // instead name skill classes according to the expected pattern.
    // The optional argument is only for outside code which needs
    // to add skills (currently, it's used for unit testing).
    public function add_skill($skill, $skillClass = False)
    {
        if (!$skill) {
            return;
        }

        if (!$skillClass) {
            $skillClass = "BMSkill$skill";
        }

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
    // Hackish: the caller can specify each skill as either a plain
    // value, "skill", or a key/value pair "ClassName" => "skill",
    // where the key is the class name which implements that skill.
    // This is only for use by callers outside of engine (e.g.
    // testing), and should never be used for the default BMSkill<skill>
    // set of skills.
    public function init($sides, array $skills = NULL)
    {
        $this->min = 1;
        $this->max = $sides;

        if ($skills) {
            foreach ($skills as $skillClass => $skill) {
                if (is_string($skillClass)) {
                    $this->add_skill($skill, $skillClass);
                } else {
                    $this->add_skill($skill);
                }
            }
        }
    }

    public static function parse_recipe_for_sides($recipe) {
        if (preg_match('/\((.*)\)/', $recipe, $match)) {
            return $match[1];
        } else {
            return '';
        }
    }

    public static function parse_recipe_for_skills($recipe) {
        return BMSkill::expand_skill_string(preg_replace('/\(.*\)/', '', $recipe));
    }

    // given a string describing a die and a list of skills, return a
    // new BMDie or appropriate subclass thereof

    // Depending on implementation details, this may end up being
    // replaced with something that doesn't need to do string parsing

    public static function create_from_string_components($recipe, array $skills = NULL) {
        $die = NULL;

        try {
            $opt_list = explode('|', $recipe);

            // Option dice divide on a |, can contain any die type
            if (count($opt_list) > 1) {
                $die = BMDieOption::create_from_list($opt_list, $skills);
            }
            // Twin dice divide on a comma, can contain any type but option
            elseif (count($twin_list = explode(',', $recipe)) > 1) {
                $die = BMDieTwin::create_from_list($twin_list, $skills);
            }
            elseif ('C' == $recipe) {
                $die = BMDieWildcard::create($recipe, $skills);
            }
            // Integers are normal dice
            elseif (is_numeric($recipe) && ($recipe == (int)$recipe)) {
                $die = BMDie::create((int)$recipe, $skills);
            }
            // Single character that's not a number is a swing die
            elseif (strlen($recipe) == 1) {
                $die = BMDieSwing::create($recipe, $skills);
            }
            // oops
            else {
                throw new UnexpectedValueException("Invalid recipe: $recipe");
            }
        }
        catch (UnexpectedValueException $e) {
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

    public function activate()
    {
        $newDie = clone $this;

        $this->run_hooks(__FUNCTION__, array('die' => $newDie));

        $this->ownerObject->add_die($newDie);
    }

// Roll the die into a game. Clone self, roll, return the clone.
    public function make_play_die()
    {
        $newDie = clone $this;

        $newDie->roll(FALSE);

        $this->run_hooks(__FUNCTION__, array('die' => $newDie));

        return $newDie;
    }


    public function roll($successfulAttack = FALSE)
    {

        if ($this->doesReroll) {
            $this->value = mt_rand($this->min, $this->max);
        }

        $this->run_hooks(__FUNCTION__, array('isSuccessfulAttack' => $successfulAttack));
    }

    public function attack_list()
    {
        $list = array('Power' => 'Power', 'Skill' => 'Skill');

        $this->run_hooks(__FUNCTION__, array('attackTypeArray' => &$list));

        return $list;
    }

    // Return all possible values the die may use in this type of attack
    //
    // The values must be sorted, highest to lowest, with no duplication.
    public function attack_values($type)
    {
        $list = array($this->value);

        $this->run_hooks(__FUNCTION__, array('attackType' => $type,
                                             'attackValues' => &$list));

        return $list;
    }

    public function defense_value($type)
    {
        $val = $this->value;

        $this->run_hooks(__FUNCTION__, array('attackType' => $type,
                                             'defenceValue' => &$val));

        return $val;
    }

// returns ten times the "real" scoring value
//
// We do not want to use floating-point math -- there's a real risk of
// having 10.5 not equal 10.5.
//
// We use a multiplier and divisor so various skills can manipulate them
// without stepping on each others' toes
    public function get_scoreValueTimesTen()
    {
        $scoreValue = $this->max;

        $mult = 1;
        if ($this->captured) {
            $div = 1;
        }
        else {
            $div = 2;
        }

        $this->run_hooks('score_value',
                         array('scoreValue' => &$scoreValue,
                               'value'      => $this->value,
                               'mult'       => &$mult,
                               'div'        => &$div,
                               'captured'   => $this->captured));

        if (is_null($scoreValue)) {
            return NULL;
        } else {
            return (10 * $scoreValue * $mult) / $div;
        }
    }

    // Return an array of the die's possible initiative values. 0
    // means it doesn't count for initiative. "?" means it's a chance
    // die.

    public function initiative_value()
    {
        $vals = array($this->value);

        $this->run_hooks(__FUNCTION__, array('possibleInitiativeValues' => &$vals));

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
    public function assist_values($type, array $attackers, array $defenders) {

        $vals = array(0);

        // Attackers can't help their own attack
        if (FALSE !== array_search($this, $attackers)) {
            return $vals;
        }

        $this->run_hooks(__FUNCTION__, array('attackType' => $type,
                                             'attackers' => $attackers,
                                             'defenders' => $defenders,
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

        // Hooks are where the die gets adjusted if need be.
        if ($valid) {
            $this->run_hooks(__FUNCTION__, array('attackType' => $type,
                                                 'attackers' => $attackers,
                                                 'defenders' => $defenders,
                                                 'amount' => $amount));
        }
        return $valid;

    }

// check for special-case situations where an otherwise-valid attack
// is not legal. Single-die skill attacks with stealth dice are the only
// situation I can come up with off the top of my head
//
// These methods cannot act, they may only check: they're called a lot
    public function is_valid_attacker($type, array $attackers, array $defenders)
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

        $this->run_hooks(__FUNCTION__, array('attackType' => $type,
                                             'die' => $this,
                                             'isValid' => &$valid));

        return $valid;
    }


    public function is_valid_target($type, array $attackers, array $defenders)
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


        $this->run_hooks(__FUNCTION__, array('attackType' => $type,
                                             'die' => $this,
                                             'isValid' => &$valid));

        return $valid;
    }

    public function capture($type, array &$attackers, array &$victims)
    {
        $this->run_hooks(__FUNCTION__, array('type' => $type,
                                             'attackers' => $attackers,
                                             'victims' => $victims));
    }


    public function be_captured($type, array &$attackers, array &$victims)
    {
        $this->captured = TRUE;

        $this->run_hooks(__FUNCTION__, array('type' => $type,
                                             'attackers' => $attackers,
                                             'victims' => $victims));
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

        $this->run_hooks(__FUNCTION__, array('dice' => &$dice));

        return $dice;
    }

    public function run_hooks_at_game_state($gameState, $activePlayerIdx) {
        switch ($gameState) {
            case BMGameState::endTurn:
                if ($this->playerIdx === $activePlayerIdx) {
                    $this->inactive = "";
                }
                $this->hasAttacked = FALSE;
                break;
            default:
                // do nothing special
        }

        $this->run_hooks(__FUNCTION__, array('activePlayerIdx' => $activePlayerIdx));
    }

    public function get_recipe() {
        $recipe = '';
        foreach ($this->skillList as $skill) {
            $recipe .= BMSkill::abbreviate_skill_name($skill);
        }
        $recipe .= '(';

        // Option dice divide on a |, can contain any die type
        if ($this instanceof BMDieOption) {

        }
        // Twin dice divide on a comma, can contain any type but option
        elseif ($this instanceof BMDieTwin) {

        }
        elseif ($this instanceof BMDieWildcard) {
            $recipe .= 'C';
        }
        elseif ($this instanceof BMDieSwing) {
            $recipe .= $this->swingType;
        }
        else {
            $recipe .= $this->max;
        }

        $recipe .= ')';

        return $recipe;
    }


    // utility methods

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            switch ($property) {
                case 'recipe':
                    return $this->get_recipe();
                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value)
    {
//        switch ($property) {
//            default:
                $this->$property = $value;
//        }
    }

    public function __toString()
    {
//        print($this->mRecipe);
    }

    public function __clone() {
        // Doesn't do anything for the base class, but subclasses will
        // need to clone their subdice.
    }
}

?>
