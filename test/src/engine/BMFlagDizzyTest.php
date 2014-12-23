<?php

class BMFlagDizzyTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagDizzy::__construct
     * @covers BMFlagDizzy::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('Dizzy');
        $this->assertInstanceOf('BMFlagDizzy', $flag);
    }

    /**
     * @covers BMFlagDizzy::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('Dizzy');
        $this->assertEquals('Dizzy', strval($flag));
    }
}
