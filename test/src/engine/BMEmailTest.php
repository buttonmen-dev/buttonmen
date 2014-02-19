<?php

class BMEmailTest extends PHPUnit_Framework_TestCase {

    /**
     * @var BMEmail
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new BMEmail('bmemail-test@example.com', TRUE);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     * @covers BMEmail::__construct()
     */
    public function test_construct() {
        $this->assertEquals($this->object->recipient, 'bmemail-test@example.com');
    }
}

?>
