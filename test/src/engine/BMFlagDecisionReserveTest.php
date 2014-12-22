<?php

class BMFlagDecisionReserveTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagDecisionReserve::__construct
     * @covers BMFlagDecisionReserve::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('DecisionReserve__0');
        $this->assertInstanceOf('BMFlagDecisionReserve', $flag);
        $this->assertTrue(FALSE === $flag->value());

        $flag = BMFlag::create_from_string('DecisionReserve__1');
        $this->assertInstanceOf('BMFlagDecisionReserve', $flag);
        $this->assertTrue(TRUE === $flag->value());
    }

    /**
     * @covers BMFlagDecisionReserve::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('DecisionReserve__0');
        $this->assertEquals('DecisionReserve__0', strval($flag));

        $flag = BMFlag::create_from_string('DecisionReserve__1');
        $this->assertEquals('DecisionReserve__1', strval($flag));
    }
}
