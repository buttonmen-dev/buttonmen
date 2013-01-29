<?php

/**
 * BMUtility: utility classes
 *
 * @author Julian
 */

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
        $this->basepos = 1;
        if ($this->depth > count($array)) {
            $this->depth = count($array);
        }
        if ($this->depth < 1) {
            $this->depth = 1;
        }
    }

    public function setPosition($newPos) {
        $this->basepos = $newPos;
    }

    // Called when the foreach begins
    //
    // keep making sub-iterators with the tail of the list until we
    // reach the full depth
    public function rewind() {
        $this->position = $this->basepos;
        $this->list = $this->baseList;
        $this->tail = NULL;

        $this->head = array_pop($this->list);
        if (count($this->list > 0) && $this->depth > 1) {
            $this->tail = new XCYIterator($this->list, $this->depth - 1);
        }
        if ($this->tail) {
            $this->tail->setPosition($this->position + 1);
            $this->tail->rewind();

        }
    }

    // Get the current value
    public function current() {
        if ($this->tail) {
            $tmp = $this->tail->current();
            array_push($tmp, $this->head);
            return $tmp;
        }
        else {
            return array($this->head);
        }
    }

    // Get the "array key" to go with the value
    //
    // Mostly useless.
    public function key() {
        if ($this->tail) {
            return $this->tail->key() . $this->position;
        }
        else {
            return $this->position;
        }
    }

    // step to the next value. Can fall off the end of the list, which
    // is checked for elsewhere
    //
    // Here, we cycle the depth-one iterator. If it's fallen off the
    // end, the depth-two iterator has to advance one step and make a
    // new depth-one with the remaining elements of the list that it
    // hasn't stepped to yet.
    //
    // The catch is that each successive layer falls off the end
    // sooner. The depth-one can keep going until its list is
    // empty. The depth-two hits the end with one item left in the
    // list, since it has to have something to feed the depth-one.
    // And so on, and so forth.
    public function next() {
        if ($this->tail) {
            $this->tail->next();
            if (!$this->tail->valid()) {
                $this->tail = NULL;
                $this->head = NULL;
                $this->position++;
                if (count($this->list) >= $this->depth) {
                    $this->head = array_pop($this->list);
                    $this->tail = new XCYIterator($this->list, $this->depth - 1);
                    $this->tail->setPosition($this->position + 1);
                    $this->tail->rewind();
                }
            }
        }
        else {
            $this->head = array_pop($this->list);
            $this->position++;
        }
    }

    // Check whether we fell off the end.
    public function valid() {
        if (!is_null($this->head)) { return TRUE; }
        else {
            return FALSE;
        }
    }
}

// Class to hold and search all the possible combinations that a skill
// attack could hit
//
// It's huge and complicated, but hopefully better than a naive search.
class BMHitTable {
    private $dice = array();

    // $hits is an array keyed by numbers. Values is an array, keyed
    // by the combined unique ids of the sets of dice used to make the value
    // 
    // So, if 4 can be made with A and B or C and D, 
    // $hits[4] = [ AB => [ dieA, dieB ], CD => [ dieC, dieD ] ]
    private $hits = array();

    public function __construct($dice) {
        // Every die needs a unique identifier, no matter how many
        // there are, but if there are more than 36, something is
        // very, very wrong.
        $ids = str_split("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        for ($i = 0; $i < count($dice); $i++) {
            $die = $dice[$i];
            $die_id = $ids[$i];

            $this->dice[] = $die;

            foreach (array_keys($this->hits) as $target) {

                foreach ($this->hits[$target] as $key => $combo) {
                    // We've already been used in this combo
                    if (FALSE !== strpos($key, $die_id)) {
                        continue;
                    }

                    foreach ($die->attack_values("Skill") as $val) {
                        $newcombo = $this->hits[$target][$key];
                        $newcombo[] = $die;
                        // the new key will always be sorted, since we
                        // process the dice in order
                        $newkey = $key.$die_id;
                        $newtarget = $target + $val;
                        if (array_key_exists($newtarget, $this->hits)) {
                            // If the same die combo makes a number
                            // two ways, we just overwrite the old
                            // entry.
                            $this->hits[$newtarget][$newkey] = $newcombo;
                        } else {
                            $this->hits[$newtarget] = array($newkey => $newcombo);
                        }
                    }
                }
            }

            // Add the unique values the die may provide
            foreach ($die->attack_values("Skill") as $val) {
                if (array_key_exists($val, $this->hits)) {
                    continue;
                }
                $this->hits[$val] = array($die_id => array($die));
            }

        }
    }

    // Test for a hit. Return all possible sets of dice that can make that hit.
    public function find_hit($target) {
        if (array_key_exists($target, $this->hits)) {

            return array_values($this->hits[$target]);
        }
        return FALSE;
    }

    // Return a list of all possible hits
    public function list_hits() {
        return array_keys($this->hits);
    }
}