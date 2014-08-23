<?php

class BMFlagHasJustRerolledOrneryTest extends PHPUnit_Framework_TestCase {
    
    /**
     * @covers BMFlagHasJustRerolledOrnery::__construct
     * @covers BMFlagHasJustRerolledOrnery::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustRerolledOrnery');
        $this->assertInstanceOf('BMFlagHasJustRerolledOrnery', $flag);
    }

    /**
     * @covers BMFlagHasJustRerolledOrnery::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustRerolledOrnery');
        $this->assertEquals('HasJustRerolledOrnery', strval($flag));
    }
}
