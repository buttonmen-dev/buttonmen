<?php

class TestDummyPDOStatement {
    public function __construct($dummyReturnValue) {
        $this->returnValue = $dummyReturnValue;
        $this->hasExecuted = FALSE;
        $this->cursorOffset = 0;
    }

    public function execute($parameters) {
        $this->hasExecuted = TRUE;
    }

    public function fetch() {
        if (!$this->hasExecuted) {
            throw new Exception("Attempted to fetch from a DB statement before executing it");
        }
        if ($this->cursorOffset < count($this->returnValue)) {
            $retval = $this->returnValue[$this->cursorOffset];
            $this->cursorOffset++;
            return $retval;
        }
        return NULL;
    }
}
