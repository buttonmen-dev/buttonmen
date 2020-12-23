<?php

require_once __DIR__.'/TestDummyPDOStatement.php';

class TestDummyPDOConn {
    public function __construct() {
        $this->nextExpectedReturnValue = NULL;
    }

    public function setNextExpectedReturnValue($value) {
        $this->nextExpectedReturnValue = $value;
    }

    public function prepare($stmt) {
        return new TestDummyPDOStatement($this->nextExpectedReturnValue);
    }
}
