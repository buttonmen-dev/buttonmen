<?php

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

    static function getInstance() {
        $class = get_called_class();
        if (!isset(static::$instance[$class])) {
            static::$instance[$class] = new $class;
        }
        return static::$instance[$class];
    }

    // Dice that effect or affect this attack
    protected $validDice = array();

    public function add_die($die) {
        if (!array_contains($die, $validDice)) {
            $validDice[] = $die;
        }
    }

    // Figure out what help can be added to the total
    //
    // Returns the minimum and maximum values that can be contributed.
    //
    // $helpers is and array of the returned values from
    // assist_values; we don't need to know which die contributes what
    // here.

    public function help_bounds($helpers) {
        $helpMin = $helpMax = 0;

        if (count($helpers) == 0) { return array($helpMin, $helpMax); }

        // Help values are sorted lowest to highest, and we enforce
        // some assumptions about the values to simplify this code a lot
        foreach ($helpers as $helpVals) {
            $min = $helpers[0];
            $max = end($helpers);

            if ($max >= 0) {
                if ($helpMax > 0) { $helpMax += $max; }
                else { $helpMax = $max; }
            }
            else {
                // Simplifying assumption here, but life's a lot more
                // complex if there can be gaps in the help coverage.
                $helpMax = -1;
            }

            if ($min <= 0) {
                if ($helpMin < 0) { $helpMin -= $min; }
                else { $helpMin = $min; }
            }
            else {
                // Simplifying assumption here, but life's a lot more
                // complex if there can be gaps in the help coverage.
                $helpMin = 1;;
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

    protected function search_onevmany($game, $attackers, $defenders) {
        // Sanity check

        if (count($attackers) < 1 || count($defenders) < 1) {
            return FALSE;
        }

        // We only need to iterate over half the space, since we can
        // search the complement of the set at the same time.

        $count = count($defenders);
        $rem = $count % 2;
        $count -= $rem;
        $count /= 2;

        $aIt = new XCYIterator($attackers, 1);

        for ($i = 1; $i <= $count; $i++) {
            $dIt = new XCYIterator($defenders, $i);

            foreach ($dIt as $def) {
                foreach ($aIt as $att) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                    $complement =  array_diff($defenders, $def);
                    if ($this->validate_attack($game, $att, $complement)) {
                        return TRUE;
                    }
                }
            }
        }
        // Odd number of dice
        if ($rem) {
            $dIt = new XCYIterator($defenders, $count + 1);
            foreach ($dIt as $def) {
                foreach ($aIt as $att) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

    // It is entirely possible this method will never be used.
    // 
    // This and onevmany could be combined fairly easily
    protected function search_manyvone($game, $attackers, $defenders) {
        // Sanity check

        if (count($attackers) < 1 || count($defenders) < 1) {
            return FALSE;
        }

        // We only need to iterate over half the space, since we can
        // search the complement of the set at the same time.

        $count = count($attackers);
        $rem = $count % 2;
        $count -= $rem;
        $count /= 2;

        $dIt = new XCYIterator($defenders, 1);

        for ($i = 1; $i <= $count; $i++) {
            $aIt = new XCYIterator($attackers, $i);

            foreach ($aIt as $att) {
                foreach ($dIt as $def) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                    $complement =  array_diff($attackers, $att);
                    if ($this->validate_attack($game, $complement, $def)) {
                        return TRUE;
                    }
                }
            }
        }
        // Odd number of dice
        if ($rem) {
            $aIt = new XCYIterator($attackers, $count + 1);
            foreach ($aIt as $att) {
                foreach ($dIt as $def) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
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
            if (array_search($die, $attackers)) {
                // Attackers can't help their own attack
                next;
            }

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

// Iterator to return all possible lists of Y elements from list X.
//
// Once you take stinger and constant into account, this is probably
// way too slow for the full search for a skill attack, but we need it
// anyway for choose 1 and choose 2, and it's easier to understand
// than the optimized search I have in mind.
//
// a generator would be nicer here, but that's not available
class XCYIterator implements Iterator {
    private $position;
    private $list;
    private $baseList;
    private $head;
    private $depth;
    private $tail = NULL;

    public function __construct($array, $y) {
        $this->baseList = $array;
        $this->depth = $y;
    }

    public function setPosition($newPos) {
        $this->position = $newPos;
    }

    public function rewind() {
        $this->position = 1;
        $this->list = $this->baseList;
        unset($this->tail);

        $this->head = array_pop($list);
        if (count($this->list > 0) && $this->depth > 1) {
            $this->tail = new XCYIterator($this->list, $this->depth - 1);
        }
        if ($this->tail) { 
            $this->tail->setPosition($position + 1);
            $this->tail->rewind();
            
        }
    }

    public function current() {
        if ($this->tail) {
            $tmp = $this->tail->current();
            return array_push($tmp, $this->head);
        }
        else {
            return array($this->head);
        }
    }

    // Mostly useless.
    public function key() {
        if ($this->tail) {
            return $this->tail->key() . $this->position;
        }
        else {
            return $this->position;
        }
    }

    public function next() {
        if ($this->tail) {
            $this->tail->next();
            if (!$this->tail->valid()) {
                unset($this->tail);
                $this->head = array_pop($this->list);
                if (count($this->list > 0) && $this->depth > 1) {
                    $this->tail = new XCYIterator($this->list, $this->depth - 1);
                }
            }
        }
        else {
            $this->head = array_pop($this->list);
            $this->position++;
        }
    }

    public function valid() {
        if ($this->head) { return TRUE; }
        else { return FALSE; }
    }

    
}


?>