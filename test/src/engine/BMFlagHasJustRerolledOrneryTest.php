<?php

class BMFlagHasJustRerolledOrneryTest extends PHPUnit\Framework\TestCase {
    
    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustRerolledOrnery');
        $this->assertInstanceOf('BMFlagHasJustRerolledOrnery', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustRerolledOrnery');
        $this->assertEquals('HasJustRerolledOrnery', strval($flag));
    }
}
