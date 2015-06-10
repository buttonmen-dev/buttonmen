<?php

class BMFlagJustPerformedTypeOfAttackTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagJustPerformedTypeOfAttack::__construct
     * @covers BMFlagJustPerformedTypeOfAttack::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('JustPerformedTypeOfAttack__6');
        $this->assertInstanceOf('BMFlagJustPerformedTypeOfAttack', $flag);
        $this->assertEquals(6, $flag->value());
    }

    /**
     * @covers BMFlagJustPerformedTypeOfAttack::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('JustPerformedTypeOfAttack__6');
        $this->assertEquals('JustPerformedTypeOfAttack__6', strval($flag));
    }
}
