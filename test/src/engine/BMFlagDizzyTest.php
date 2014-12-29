<?php

class BMFlagDizzyTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlag::__construct
     * @covers BMFlag::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('Dizzy');
        $this->assertInstanceOf('BMFlagDizzy', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('Dizzy');
        $this->assertEquals('Dizzy', strval($flag));
    }
}
