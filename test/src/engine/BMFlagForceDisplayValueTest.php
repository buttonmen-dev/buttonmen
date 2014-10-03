<?php

class BMFlagForceDisplayValueTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('ForceDisplayValue');
        $this->assertInstanceOf('BMFlagForceDisplayValue', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('ForceDisplayValue');
        $this->assertEquals('ForceDisplayValue', strval($flag));
    }
}
