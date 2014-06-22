<?php

class BMFlagWasJustPerformedTripAttackTest extends PHPUnit_Framework_TestCase {
    
    /**
     * @covers BMFlagWasJustPerformedTripAttack::__construct
     * @covers BMFlagWasJustPerformedTripAttack::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('JustPerformedTripAttack__6');
        $this->assertInstanceOf('BMFlagJustPerformedTripAttack', $flag);
        $this->assertEquals(6, $flag->value());
    }

    /**
     * @covers BMFlagWasJustPerformedTripAttack::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('JustPerformedTripAttack__6');
        $this->assertEquals('JustPerformedTripAttack__6', strval($flag));
    }
}
