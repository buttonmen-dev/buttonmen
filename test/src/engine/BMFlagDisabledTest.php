<?php

class BMFlagDisabledTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagDisabled::__construct
     * @covers BMFlagDisabled::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('Disabled');
        $this->assertInstanceOf('BMFlagDisabled', $flag);
    }

    /**
     * @covers BMFlagDisabled::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('Disabled');
        $this->assertEquals('Disabled', strval($flag));
    }
}
