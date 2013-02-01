<?php

require_once 'BMUtility.php';
require_once 'BMDie.php';

/**
 * BMAttack: attack validation and commital code.
 *
 * @author Julian
 */
class BMAttack {
    protected static $instance = array();

    // True for attacks that do something besides simple capture,
    // because the player may have to choose which attack type to
    // use. Captures are indistinguishable among attacks with no
    // side effects
    public $sideEffect = FALSE;

    public $name;
    // The attack's type, which is usually the same as its name.
    //
    // This is used for attacks like Sorcates' special attack, which
    // is a skill attack, so can work on Stealth dice and use Fire
    // dice, but needs its own class.
    public $type;

    private function __construct() {
        // You can't instantiate me; I'm a Singleton!
    }

    static function get_instance() {
        $class = get_called_class();
        if (!isset(static::$instance[$class])) {
            static::$instance[$class] = new $class;
        }
        return static::$instance[$class];
    }

    // Dice that effect or affect this attack
    protected $validDice = array();

    public function add_die($die) {
        if (!is_a($die, "BMDie")) { return; }
        // need to search with strict on to avoid identical-valued
        // objects matching
        if (!in_array($die, $this->validDice, TRUE)) {
            $this->validDice[] = $die;
        }
    }

    // Figure out what help can be added to the total
    //
    // Returns the minimum and maximum values that can be contributed.
    //
    // $helpers is an array of the sets of returned values from
    // assist_values; we don't need to know which die contributes what
    // here.

    public function help_bounds($helpers) {
        $helpMin = $helpMax = 0;

        if (count($helpers) == 0) { return array($helpMin, $helpMax); }

        // Help values are sorted lowest to highest, and we enforce
        // some assumptions about the values to simplify this code a lot
        foreach ($helpers as $helpVals) {
            $min = $helpVals[0];
            $max = end($helpVals);

            if ($max > 0) {
                if ($helpMax > 0) { $helpMax += $max; }
                else { $helpMax = $max; }
            }
            elseif ($max < 0 && $helpMax < 1) {
                // Simplifying assumption here, but life's a lot more
                // complex if there can be gaps in the help coverage.
                $helpMax = -1;
            }

            if ($min < 0) {
                if ($helpMin < 0) { $helpMin += $min; }
                else { $helpMin = $min; }
            }
            elseif ($min > 0 && $helpMin > -1 ) {
                // Simplifying assumption here, but life's a lot more
                // complex if there can be gaps in the help coverage.
                $helpMin = 1;
            }
        }

        return array($helpMin, $helpMax);
    }


    // gather contributions from assisting dice to make the attack work
    // returns FALSE if it failed to do so (user cancel or error)
    //
    // I don't yet understand what the guts of this function looks like
    public function collect_contributions($game, $attackers, $defenders) {
        $needed = $this->calculate_contributions($game, $attackers, $defenders);

        $amount = $needed[0];
        $helpers = $needed[1];

        if ($amount == 0) { return TRUE; }
        return FALSE;
    }

    // return how much help is needed and who can contribute
    //
    // implemented in subclassed where they actually know what help they need
    public function calculate_contributions($game, $attackers, $defenders) {
        return array(0, array());
    }

    // uses the dice in validDice to find a single valid attack within the game
    public function find_attack($game) {
        return FALSE;
    }

    // confirm that an attack is legal
    public function validate_attack($game, $attackers, $defenders) {
        return FALSE;
    }

    // actually make the attack
    // Some of this should perhaps be in the game, rather than here.
    public function commit_attack($game, $attackers, $defenders) {
        // Paranoia
        if (!$this->validate_attack($game, $attackers, $defenders)) {
            return FALSE;
        }

        // Collect the necessary help
        // not implemented yet
//        if (!$this->collect_contributions($game, $attackers, $defenders)) {
//            // return FALSE;
//        }

        foreach ($attackers as $att) {
            $att->capture($this->type, $attackers, $defenders);
        }

        foreach ($defenders as $def) {
            $def->be_captured($this->type, $attackers, $defenders);
        }

        // Yes, the separation is important for a number of skills

        foreach ($attackers as $att) {
            $att->hasAttacked = TRUE;
            $att->roll(TRUE);
        }

        foreach ($defenders as $def) {
            $def->captured = TRUE;
            $game->capture_die($def);
        }

        return TRUE;
    }


    // methods to find that there is a valid attack
    //
    // If anybody wants to add a many dice vs many dice attack, I will
    // cut then. (It'd _work_, but the words "combinatoric explosion"
    // are deeply relevant.)


    protected function search_onevone($game, $attackers, $defenders) {
        // Sanity check

        if (count($attackers) < 1 || count($defenders) < 1) {
            return FALSE;
        }

        // OK, these aren't necessary for this one, but it's consistent.
        $aIt = new XCYIterator($attackers, 1);
        $dIt = new XCYIterator($defenders, 1);

        foreach ($aIt as $att) {
            foreach ($dIt as $def) {
                if ($this->validate_attack($game, $att, $def)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    // Combine the logic for onevmany and manyvone by use of a
    // comparison function.
    protected function search_ovm_helper($game, $one, $many, $compare) {
        // Sanity check

        if (count($many) < 1 || count($one) < 1) {
            return FALSE;
        }


        $count = count($many);

        $oneIt = new XCYIterator($one, 1);

        $checkedSizes = array();

        for ($i = 1; $i <= $count; $i++) {
            $checkedSizes[$i] = FALSE;
        }


        for ($i = 1; $i <= $count; $i++) {
            if ($checkedSizes[$i]) {
                continue;
            }

            // We only need to iterate over about half the space, since we
            // can search the complement of the set at the same time.
            $checkedSizes[$count - $i] = TRUE;

            $manyIt = new XCYIterator($many, $i);

            foreach ($manyIt as $m) {
                foreach ($oneIt as $o) {
                    if ($compare($game, $o, $m)) {
                        return TRUE;
                    }
                    // Don't search the complement when we're halfway
                    // through an even-sized list
                    if ($i == $count - $i) { continue; }

                    // Or if the complement is empty
                    if (count($many) == count($m)) { continue; }

                    $complement =  array_diff($many, $m);
                    if ($compare($game, $o, $complement)) {
                        return TRUE;
                    }
                }
            }
        }

        return FALSE;
    }

    // $this may not be used in anonymous functions in PHP 5.3. Bastards.
    protected function search_onevmany($game, $attackers, $defenders) {
        $myself = $this;
        $compare = function($g, $att, $def) use ($myself) {
            return $myself->validate_attack($g, $att, $def);
        };

        return $this->search_ovm_helper($game, $attackers, $defenders, $compare);
    }

    // It is entirely possible this method will never be used, since
    // skill attacks build a hit table instead. (For hopefully
    // improved efficiency.)
    protected function search_manyvone($game, $attackers, $defenders) {
        $myself = $this;
        $compare = function($g, $def, $att) use ($myself) {
            return $myself->validate_attack($g, $att, $def);
        };

        return $this->search_ovm_helper($game, $defenders, $attackers, $compare);
    }

    // returns a list of possible values that can aid an attack
    protected function collect_helpers($game, $attackers, $defenders) {
        $helpers = array();

        if (is_null($game->attackerAllDieArray)) {
            return $helpers;
        }

        foreach ($game->attackerAllDieArray as $die) {
            $helpVals = $die->assist_values($this->type, $attackers, $defenders);
            if ($helpVals[0] != 0) {
                $helpers[] = $helpVals;
            }
        }
        return $helpers;
    }
}


class BMAttackPower extends BMAttack {
    public $name = "Power";
    public $type = 'Power';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, $attackers, $defenders) {
        if (count($attackers) != 1 || count($defenders) != 1) {
            return FALSE;
        }

        $helpers = $this->collect_helpers($game, $attackers, $defenders);

        $bounds = $this->help_bounds($helpers);

        foreach ($attackers[0]->attack_values($this->type) as $aVal) {

            if ($aVal + $bounds[1] >= $defenders[0]->defense_value($this->type)) {

                if ($attackers[0]->valid_attack($this->type, $attackers, $defenders) &&
                    $defenders[0]->valid_target($this->type, $attackers, $defenders))
                {
                    return TRUE;
                }
            }

        }

        return FALSE;
    }

    // return how much help is needed and who can contribute
    public function calculate_contributions($game, $attackers, $defenders) {
        return array(0, array());
    }
}

class BMAttackSkill extends BMAttack {
    public $name = "Skill";
    public $type = "Skill";

    // Especially once you take stinger and constant into account,
    // searching the full attack space is slow and complex
    //
    // Building a hit table once trades some increased setup cost for
    // a much reduced search cost
    //
    // Fire still makes life more complex than it might be.
    //
    // "Premature optimization is the root of all evil." -- Knuth
    protected $hit_table = NULL;

    public function find_attack($game) {
        if (!$this->hit_table) {
            $this->hit_table = new BMHitTable($this->validDice);
        }

        $targets = $game->defenderAllDieArray;

        if (count($targets) < 1) { return FALSE; }

        // Check all precise hits before trying any with help, because
        // help is slow
        foreach ($targets as $def) {
            if ($this->hit_table->find_hit($def->defense_value($this->type))) {
                return TRUE;
            }
        }

        // Potentially save some time by checking for the possibility of help

        $help_found = FALSE;
        foreach ($targets as $def) {
            foreach ($game->attackerAllDieArray as $att) {
                if ($this->collect_helpers($game, array($att), array($def))) {
                    $help_found = TRUE;
                    break 2;
                }
            }
        }

        if (!$help_found) { return FALSE; }

        $hits = $this->hit_table->list_hits();
        sort($hits);

        // Should perhaps start around the defending die's value and
        // work outward, but probably not worth the extra overhead to
        // do so. We half-ass it by starting in the middle.

        // PHP, why do you have no integer division operator?
        $i = (int)(count($hits) / 2);
        $j = $i - 1;
        $j += count($hits) % 2; // Start in the exact middle of an odd count
        while ($j >= 0) {
            foreach ($targets as $t) {
                $def = array($t);
                $combos = $this->hit_table->find_hit($hits[$i]);
                foreach ($combos as $att) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                }
                if ($i == $j) { continue; }
                $combos = $this->hit_table->find_hit($hits[$j]);
                foreach ($combos as $att) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                }
            }
            $j--; $i++;
        }
            
        return FALSE;
    }

    public function validate_attack($game, $attackers, $defenders) {
        if (count($attackers) < 1 || count($defenders) != 1) {
            return FALSE;
        }

        // array_intersect tries to convert to strings, so we
        // use array_uintersect, which needs a comparison
        // function
        $cmp = function ($v1,$v2) {
            if ($v1===$v2) { return 0; }
            if ($v1 > $v2) { return 1; }
            return -1;
        };

        $dval = $defenders[0]->defense_value($this->type);

        // exact hits
        $combos = $this->hit_table->find_hit($dval);
        if ($combos) {
            foreach ($combos as $c) {
                if (count($c) == count($attackers) &&
                    count(array_uintersect($c, $attackers, $cmp)) == 
                    count($c)) {
                    return TRUE;
                }
            }
        } 

        // assisted attacks
        $helpers = $this->collect_helpers($game, $attackers, $defenders);
        $bounds = $this->help_bounds($helpers);
        if ($bounds[0] == 0 && $bounds[1] == 0) { return FALSE; }
        for ($i = $bounds[0]; $i <= $bounds[1]; $i++) {
            $combos = $this->hit_table->find_hit($dval + $i);
            if ($combos) {
                foreach ($combos as $c) {
                    if (count($c) == count($attackers) &&
                        count(array_uintersect($c, $attackers, $cmp)) ==
                        count($c)) {
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

    // return how much help is needed and who can contribute
    public function calculate_contributions($game, $attackers, $defenders) {
        return array(0, array());
    }
}


class BMAttackShadow extends BMAttackPower {
    public $name = "Shadow";
    public $type = "Shadow";

    public function validate_attack($game, $attackers, $defenders) {
        if (count($attackers) != 1 || count($defenders) != 1) {
            return FALSE;
        }


        $helpers = $this->collect_helpers($game, $attackers, $defenders);

        $bounds = $this->help_bounds($helpers);

        foreach ($attackers[0]->attack_values($this->type) as $aVal) {

            if ($defenders[0]->defense_value($this->type) <= $attackers[0]->max &&
                ($defenders[0]->defense_value($this->type) >= $aVal ||
                 $defenders[0]->defense_value($this->type) >= $aVal + $bounds[0])) {

                if ($attackers[0]->valid_attack($this->type, $attackers, $defenders) &&
                    $defenders[0]->valid_target($this->type, $attackers, $defenders))
                {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    // return how much help is needed and who can contribute
    public function calculate_contributions($game, $attackers, $defenders) {
        return array(0, array());
    }
}

class BMAttackPass extends BMAttack {
    public $name = "Pass";
    public $type = "Pass";

    public function find_attack($game) {

    }
}


?>