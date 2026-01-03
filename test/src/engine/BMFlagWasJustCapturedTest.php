<?php

class BMFlagWasJustCapturedTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('WasJustCaptured');
        $this->assertInstanceOf('BMFlagWasJustCaptured', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('WasJustCaptured');
        $this->assertEquals('WasJustCaptured', strval($flag));
    }
}
