<?php

/*
 * BMDie: the fundamental unit of game mechanics
 *
 * @author: Julian Lighton
 */

class BMDie extends BMCanHaveSkill {
    // properties

// Basic facts about the die
    public $min;
    public $max;
    public $value;
    protected $recipe;

// references back to the owner
    public $ownerObject;
    public $playerIdx;
    public $originalPlayerIdx;

    protected $doesReroll = TRUE;
    public $captured = FALSE;

    public $hasAttacked = FALSE;

    // $selected is set when a player wants to add an auxiliary die
    public $selected = FALSE;

// This is set when the button may not attack (sleep or focus, for instance)
// It is set to a string, so the cause may be described. It is cleared at
// the end of each of your turns.
    public $inactive = "";

// Set when the button isn't in the game for whatever reason, but
//  could suddenly join (Warrior Dice). Prevents from being attacked,
//  but not attacking
    public $unavailable = FALSE;

// This needs to be fixed to work properly within PHP's magic method semantics
//
// will need an init_from_db method, too (eventually)
    // Hackish: the caller can specify each skill as either a plain
    // value, "skill", or a key/value pair "ClassName" => "skill",
    // where the key is the class name which implements that skill.
    // This is only for use by callers outside of engine (e.g.
    // testing), and should never be used for the default BMSkill<skill>
    // set of skills.
    public function init($sides, array $skills = NULL) {
        $this->min = 1;
        $this->max = $sides;

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
                throw new Exception("Wildcard skill not implemented");
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

    public function activate() {
        $newDie = clone $this;

        $this->run_hooks(__FUNCTION__, array('die' => $newDie));

        $this->ownerObject->add_die($newDie);
    }

// Roll the die into a game. Clone self, roll, return the clone.
    public function make_play_die() {
        $newDie = clone $this;
        $newDie->roll();
        return $newDie;
    }


    public function roll($isTriggeredByAttack = FALSE) {
        $this->run_hooks('pre_roll', array('die' => $this,
                                           'isTriggeredByAttack' => $isTriggeredByAttack));

        if ($this->doesReroll || !isset($this->value)) {
            $this->value = mt_rand($this->min, $this->max);
        }

        //$this->run_hooks('post_roll', array('isTriggeredByAttack' => $isTriggeredByAttack));
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
                                             'attackValues' => &$list));

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
    // 0 means it doesn't count for initiative.
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
    public function is_valid_attacker($type, array $attackers) {
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


    public function is_valid_target($type, array $defenders) {
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

    public function capture($type, array $attackers, array $defenders) {
        $result = $this->run_hooks(__FUNCTION__, array('type' => $type,
                                                       'attackers' => $attackers,
                                                       'defenders' => $defenders,
                                                       'caller' => $this));

        if (isset($result)) {
            if (array_key_exists('BMSkillMorphing', $result)) {
                return $result['BMSkillMorphing'];
            } elseif (array_key_exists('BMSkillDoppleganger', $result)) {
                return $result['BMSkillDoppleganger'];
            }
        }
    }


    public function be_captured($type, array &$attackers, array &$defenders) {
        $this->run_hooks(__FUNCTION__, array('type' => $type,
                                             'attackers' => $attackers,
                                             'defenders' => $defenders));
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
//
// constant needs to hook this method to fix the die's value. Very
// little else will.
    public function split() {
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

    public function run_hooks_at_game_state($gameState, $args) {
        switch ($gameState) {
            case BMGameState::END_TURN:
                if ($this->playerIdx === $args['activePlayerIdx']) {
                    $this->inactive = "";
                }
                $this->hasAttacked = FALSE;
                break;
            default:
                // do nothing special
        }

        $this->run_hooks(__FUNCTION__, array('activePlayerIdx' =>
                                             $args['activePlayerIdx']));
    }

    public function get_recipe() {
        $recipe = '';
        foreach ($this->skillList as $skill) {
            if (BMSkill::do_print_skill_preceding($skill)) {
                $recipe .= BMSkill::abbreviate_skill_name($skill);
            }
        }
        $recipe .= '(';

        // Option dice divide on a /, can contain any die type
        if ($this instanceof BMDieOption) {
            $recipe .= "{$this->optionValueArray[0]}/{$this->optionValueArray[1]}";
        } elseif ($this instanceof BMDieTwin) {
            // Twin dice divide on a comma, can contain any type but option
            if ($this->dice[0] instanceof BMDieSwing) {
                $recipe .= $this->dice[0]->swingType;
            } else {
                $recipe .= $this->dice[0]->max;
            }
            $recipe .= ',';
            if ($this->dice[1] instanceof BMDieSwing) {
                $recipe .= $this->dice[1]->swingType;
            } else {
                $recipe .= $this->dice[1]->max;
            }
        } elseif ($this instanceof BMDieWildcard) {
            $recipe .= 'C';
        } elseif ($this instanceof BMDieSwing) {
            $recipe .= $this->swingType;
        } else {
            $recipe .= $this->max;
        }

        $recipe .= ')';

        foreach ($this->skillList as $skill) {
            if (!BMSkill::do_print_skill_preceding($skill)) {
                $recipe .= BMSkill::abbreviate_skill_name($skill);
            }
        }

        return $recipe;
    }

    // Return all information about a die which is useful when
    // constructing an action log entry, in the form of an array.
    // This function exists so that BMGame can easily compare the
    // die state before the attack to the die state after the attack.
    public function get_action_log_data() {
        $recipe = $this->get_recipe();
        return(array(
            'recipe' => $recipe,
            'min' => $this->min,
            'max' => $this->max,
            'value' => $this->value,
            'doesReroll' => $this->doesReroll,
            'captured' => $this->captured,
            'recipeStatus' => $recipe . ':' . $this->value,
        ));
    }

    public function cast_as_BMDie() {
        if (!($this instanceof BMDie)) {
            return NULL;
        }

        $newDie = new BMDie;

        foreach (get_object_vars($this) as $key => $value) {
            $newDie->$key = $value;
        }
        return $newDie;
    }

    public function doesSkipSwingRequest() {
        $hookResult = $this->run_hooks(__FUNCTION__, array('die' => $this));

        $doesSkipSwingRequest = is_array($hookResult) &&
                                array_search('doesSkipSwingRequest', $hookResult);

        return $doesSkipSwingRequest;
    }

    // utility methods

    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                case 'recipe':
                    return $this->get_recipe();
                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value) {
//        switch ($property) {
//            default:
                $this->$property = $value;
//        }
    }

    public function __toString() {
        return $this->get_recipe();
    }

    public function __clone() {
        // Doesn't do anything for the base class, but subclasses will
        // need to clone their subdice.
    }
}
