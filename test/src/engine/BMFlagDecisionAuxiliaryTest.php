<?php

class BMFlagDecisionAuxiliaryTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagDecisionAuxiliary::__construct
     * @covers BMFlagDecisionAuxiliary::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('DecisionAuxiliary__0');
        $this->assertInstanceOf('BMFlagDecisionAuxiliary', $flag);
        $this->assertTrue(FALSE === $flag->value());

        $flag = BMFlag::create_from_string('DecisionAuxiliary__1');
        $this->assertInstanceOf('BMFlagDecisionAuxiliary', $flag);
        $this->assertTrue(TRUE === $flag->value());
    }

    /**
     * @covers BMFlagDecisionAuxiliary::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('DecisionAuxiliary__0');
        $this->assertEquals('DecisionAuxiliary__0', strval($flag));

        $flag = BMFlag::create_from_string('DecisionAuxiliary__1');
        $this->assertEquals('DecisionAuxiliary__1', strval($flag));
    }
}
