<?php

class BMFlagDecisionTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagDecision::__construct
     * @covers BMFlagDecision::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('Decision__0');
        $this->assertInstanceOf('BMFlagDecision', $flag);
        $this->assertTrue(FALSE === $flag->value());

        $flag = BMFlag::create_from_string('Decision__1');
        $this->assertInstanceOf('BMFlagDecision', $flag);
        $this->assertTrue(TRUE === $flag->value());
    }

    /**
     * @covers BMFlagDecision::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('Decision__0');
        $this->assertEquals('Decision__0', strval($flag));

        $flag = BMFlag::create_from_string('Decision__1');
        $this->assertEquals('Decision__1', strval($flag));
    }
}
