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
    public function collect_contributions($donors) {
        return FALSE;
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
    public function commit_attack($game, $attackers, $defenders) {
        return FALSE;
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

    protected function search_onevmany($game, $attackers, $defenders) {
        $compare = function($g, $att, $def) {
            return $this->validate_attack($g, $att, $def);
        };

        return search_ovm_helper($game, $attackers, $defenders, $compare);
    }

    // It is entirely possible this method will never be used.
    protected function search_manyvone($game, $attackers, $defenders) {
        $compare = function($g, $def, $att) {
            return $this->validate_attack($g, $att, $def);
        };

        return search_ovm_helper($game, $defenders, $attackers, $compare);
    }
}


class BMAttackPower extends BMAttack {
    public $name = "Power";

    public function find_attack($game) {
        // This method doesn't exist; either needs to, or to be
        // replaced with equivalent functionality
        $targets = $game->defender_dice();

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, $attackers, $defenders) {
        if (count($attackers) != 1 || count($defenders) != 1) {
            return FALSE;
        }


        $helpers = array();

        // Need to implement this method or replace it with something
        // equivalent
        foreach ($game->attacker_dice() as $die) {
            $helpVals = $die->assist_attack($this->name, $attackers, $defenders);
            if ($helpVals[0] != 0) {
                $helpers[] = $helpVals;
            }
        }

        $bounds = $this->help_bounds($helpers);

        foreach ($attackers[0]->attack_values($this->name) as $aVal) {

            if ($aVal + $bounds[1] >= $defenders[0]->defense_value()) {

                if ($attackers[0]->valid_attack($this->name, $attackers, $defenders) &&
                    $defenders[0]->valid_target($this->name, $attackers, $defenders))
                {
                    return $TRUE;
                }
            }

        }

        return FALSE;
    }

    // Some of this should be in the game, rather than here.
    public function commit_attack($game, $attackers, $defenders) {
        // Paranoia
        if (!$this->validate_attack($game, $attackers, $defenders)) {
            return FALSE;
        }

        $att = $attackers[0];
        $def = $defenders[0];

        $att->capture($this->name, $attackers, $defenders);
        
        $def->be_captured($this->name, $attackers, $defenders);

        $att->has_attacked = TRUE;
        $att->roll();

        $def->captured = TRUE;

        // neither method here exists yet
        $game->capture_die($game->active_player(), $def);

        return TRUE;
    }

}

class BMAttackSkill extends BMAttack {
    public $name = "Skill";

    public function find_attack($game) {

    }

    public function validate_attack($game, $attackers, $defenders) {

    }

    public function commit_attack($game, $attackers, $defenders) {

    }

}


class BMAttackShadow extends BMAttackPower {
    public $name = "Shadow";

    public function validate_attack($game, $attackers, $defenders) {

    }
}

class BMAttackPass extends BMAttack {
    public $name = "Pass";

}


?>