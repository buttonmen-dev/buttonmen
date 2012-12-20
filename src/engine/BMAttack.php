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
    protected $helperDice = array()

    public function add_die($die) {
        if (!array_contains($die, $validDice)) {
            $validDice[] = $die;
        }
    }

    public function add_helper($die) {
        if (!array_contains($die, $helperDice)) {
            $helperDice[] = $die;
        }
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
    }

    protected function search_onevmany($game, $attackers, $defenders) {
        // Sanity check

        if (count($attackers) < 1 || count($defenders) < 1) {
            return FALSE;
        }

        $aIt = new XCYIterator($attackers, 1);
        $dIt = new XCYIterator($defenders, count($defenders));

        foreach ($aIt as $att) {
            foreach ($dIt as $def) {
                if ($this->validate_attack($game, $att, $def)) {
                    return TRUE;
                }
            }
        }
    }

    // It is entirely possible this method will never be used.
    protected function search_manyvone($game, $attackers, $defenders) {
        // Sanity check

        if (count($attackers) < 1 || count($defenders) < 1) {
            return FALSE;
        }

        $aIt = new XCYIterator($attackers, count($attackers));
        $dIt = new XCYIterator($defenders, 1);

        foreach ($aIt as $att) {
            foreach ($dIt as $def) {
                if ($this->validate_attack($game, $att, $def)) {
                    return TRUE;
                }
            }
        }
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
        $helpers = array();
        // This is wrong; need to look at BMGame code, find whose turn
        // it is, get their die list
        foreach ($game->potentialAttackers as $die) {
            if ($die === $attackers[0]) { next; }

            $helpVals = $die->assist_attack($this->name, $attackers, $defenders);
            if ($helpVals[0] != 0 || count($helpVals) > 1) {
                $helpers[] = $die;
            }
        }

        if (count($attackers) != 1 || count($defenders) != 1) {
            return FALSE;
        }

        if ($defenders[0]->defense_value() > $attackers[0]->$value) {
            // Check for helper dice here
            foreach ($helpers as $die) {

            }

            return FALSE;
        }

        return ($attackers[0]->valid_attack($this->name, $attackers, $defenders) &&
                $defenders[0]->valid_target($this->name, $attackers, $defenders));

    }

    public function commit_attack($game, $attackers, $defenders) {
        // Paranoia
        if (!$this->validate_attack($game, $attackers, $defenders)) {
            return FALSE;
        }

        $at = $attackers[0];
        $df = $defenders[0];

        $at->capture($this->name, $attackers, $defenders);
        
        $at->has_attacked = TRUE;
        $at->roll();

        $df->captured = TRUE;

        $df->be_captured($this->name, $attackers, $defenders);

        // neither method here exists yet
        $game->capture_die($game->active_player(), $df);

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