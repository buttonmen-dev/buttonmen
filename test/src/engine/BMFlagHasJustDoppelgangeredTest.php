<?php

class BMFlagHasJustDoppelgangeredTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustDoppelgangered');
        $this->assertInstanceOf('BMFlagHasJustDoppelgangered', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustDoppelgangered');
        $this->assertEquals('HasJustDoppelgangered', strval($flag));
    }
}
