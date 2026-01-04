<?php

class BMFlagDizzyTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
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
