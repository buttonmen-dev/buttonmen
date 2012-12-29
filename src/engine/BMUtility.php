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
