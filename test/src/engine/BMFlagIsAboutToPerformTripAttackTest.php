<?php

class BMFlagIsAboutToPerformTripAttackTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('IsAboutToPerformTripAttack');
        $this->assertInstanceOf('BMFlagIsAboutToPerformTripAttack', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('IsAboutToPerformTripAttack');
        $this->assertEquals('IsAboutToPerformTripAttack', strval($flag));
    }
}
