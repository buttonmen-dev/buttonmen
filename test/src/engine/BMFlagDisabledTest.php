<?php

class BMFlagDisabledTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
     * @covers BMFlag::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('Disabled');
        $this->assertInstanceOf('BMFlagDisabled', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('Disabled');
        $this->assertEquals('Disabled', strval($flag));
    }
}
