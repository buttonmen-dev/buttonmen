<?php

class BMFlagWasJustCapturedTest extends PHPUnit_Framework_TestCase {
    public function testConstruct() {
        $flag = BMFlag::create_from_string('WasJustCaptured');
        $this->assertInstanceOf('BMFlagWasJustCaptured', $flag);
    }

    public function testToString() {
        $flag = BMFlag::create_from_string('WasJustCaptured');
        $this->assertEquals('WasJustCaptured', strval($flag));
    }
}
