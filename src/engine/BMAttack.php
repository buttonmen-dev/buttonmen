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
    public sideEffect = FALSE;

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

    // Dice that have this attack
    protected $validDice = array();

    public function add_die($die) {
        if (!array_contains($die, $validDice)) {
            $validDice[] = $die;
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
}


class BMAttackPower extends BMAttack {
    public $name = "Power";

    public function find_attack($game) {
        // This is wrong; need to look at BMGame code, find whose turn
        // it isn't, get their die list
        $targets = $game->targets;

        $found = FALSE;

        foreach ($validDice as $attacker) {
            foreach ($targets as $defender) {
                if ($this->validate_attack($game, array($attacker), array($defender))) {
                    $found = TRUE;
                    break 2;
                }
            }
        }

        return $found;
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
        // tell the game to move the defender
        $df->be_captured($this->name, $attackers, $defenders);

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
    private $head;
    private $depth;
    private $tail = NULL;

    public function __construct($array, $y) {
        $list = $array;
        $depth = $y;

        $position = 1;
    }

    public function setPosition($newPos) {
        $position = $newPos;
    }

    public function rewind() {
        $head = array_pop($list);
        if (count($list > 0) && $depth > 1) {
            $tail = new XCYIterator($list, $depth - 1);
        }
        if ($tail) { 
            $tail->setPosition($position + 1);
            $tail->rewind();
            
        }
    }

    public function current() {
        if ($tail) {
            $tmp = $tail->current();
            return array_push($tmp, $head);
        }
        else {
            return array($head);
        }
    }

    // Mostly useless.
    public function key() {
        if ($tail) {
            return $tail->key() . $position;
        }
        else {
            return $position;
        }
    }

    public function next() {
        if ($tail) {
            $tail->next();
            if (!$tail->valid()) {
                unset($tail);
                $head = array_pop($list);
                if (count($list > 0) && $depth > 1) {
                    $tail = new XCYIterator($list, $depth - 1);
                }
            }
        }
        else {
            $head = array_pop($list);
            $position++;
        }
    }

    public function valid() {
        if ($head) { return TRUE; }
        else { return FALSE; }
    }

    
}


?>