<?php

class BMFlagJustPerformedUnsuccessfulAttackTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('JustPerformedUnsuccessfulAttack');
        $this->assertInstanceOf('BMFlagJustPerformedUnsuccessfulAttack', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('JustPerformedUnsuccessfulAttack');
        $this->assertEquals('JustPerformedUnsuccessfulAttack', strval($flag));
    }
}
