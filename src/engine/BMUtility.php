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
    // 
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

    public function valid() {
        if (!is_null($this->head)) { return TRUE; }
        else { 
            return FALSE;
        }
    }

    
}
