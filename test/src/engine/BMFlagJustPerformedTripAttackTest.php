<?php

class BMFlagJustPerformedTripAttackTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlagJustPerformedTripAttack::__construct
     * @covers BMFlagJustPerformedTripAttack::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('JustPerformedTripAttack__B(10):6');
        $this->assertInstanceOf('BMFlagJustPerformedTripAttack', $flag);
        $this->assertEquals('B(10):6', $flag->value());
    }

    /**
     * @covers BMFlagJustPerformedTripAttack::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('JustPerformedTripAttack__B(10):6');
        $this->assertEquals('JustPerformedTripAttack__B(10):6', strval($flag));
    }
}
