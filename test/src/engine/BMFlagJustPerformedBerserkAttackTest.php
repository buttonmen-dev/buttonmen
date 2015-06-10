<?php

class BMFlagJustPerformedBerserkAttackTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagJustPerformedBerserkAttack::__construct
     * @covers BMFlagJustPerformedBerserkAttack::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('JustPerformedBerserkAttack__6');
        $this->assertInstanceOf('BMFlagJustPerformedBerserkAttack', $flag);
        $this->assertEquals(6, $flag->value());
    }

    /**
     * @covers BMFlagJustPerformedBerserkAttack::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('JustPerformedBerserkAttack__6');
        $this->assertEquals('JustPerformedBerserkAttack__6', strval($flag));
    }
}
