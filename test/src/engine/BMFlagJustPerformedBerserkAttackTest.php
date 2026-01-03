<?php

class BMFlagJustPerformedBerserkAttackTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlagJustPerformedBerserkAttack::__construct
     * @covers BMFlagJustPerformedBerserkAttack::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('JustPerformedBerserkAttack__p(6)');
        $this->assertInstanceOf('BMFlagJustPerformedBerserkAttack', $flag);
        $this->assertEquals('p(6)', $flag->value());
    }

    /**
     * @covers BMFlagJustPerformedBerserkAttack::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('JustPerformedBerserkAttack__p(6)');
        $this->assertEquals('JustPerformedBerserkAttack__p(6)', strval($flag));
    }
}
