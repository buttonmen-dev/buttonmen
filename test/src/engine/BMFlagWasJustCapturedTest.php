<?php

class BMFlagWasJustCapturedTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlag::__construct
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('WasJustCaptured');
        $this->assertInstanceOf('BMFlagWasJustCaptured', $flag);
    }

    /**
     * @covers BMFlag::__construct
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('WasJustCaptured');
        $this->assertEquals('WasJustCaptured', strval($flag));
    }
}
