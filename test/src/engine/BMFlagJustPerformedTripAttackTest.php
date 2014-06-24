<?php

class BMFlagJustPerformedTripAttackTest extends PHPUnit_Framework_TestCase {
    
    /**
     * @covers BMFlagJustPerformedTripAttack::__construct
     * @covers BMFlagJustPerformedTripAttack::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('JustPerformedTripAttack__6');
        $this->assertInstanceOf('BMFlagJustPerformedTripAttack', $flag);
        $this->assertEquals(6, $flag->value());
    }

    /**
     * @covers BMFlagJustPerformedTripAttack::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('JustPerformedTripAttack__6');
        $this->assertEquals('JustPerformedTripAttack__6', strval($flag));
    }
}
