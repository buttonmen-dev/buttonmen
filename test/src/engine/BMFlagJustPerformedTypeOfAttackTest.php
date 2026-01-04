<?php

class BMFlagJustPerformedTypeOfAttackTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlagJustPerformedTypeOfAttack::__construct
     * @covers BMFlagJustPerformedTypeOfAttack::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('JustPerformedTypeOfAttack__6');
        $this->assertInstanceOf('BMFlagJustPerformedTypeOfAttack', $flag);
        $this->assertNull($flag->value());
    }

    /**
     * @covers BMFlagJustPerformedTypeOfAttack::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('JustPerformedTypeOfAttack__6');
        $this->assertEquals('JustPerformedTypeOfAttack__', strval($flag));
    }
}
