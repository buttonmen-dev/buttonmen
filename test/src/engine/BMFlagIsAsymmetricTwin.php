<?php

class BMFlagIsAsymmetricTwinTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagIsAsymmetricTwin::__construct
     * @covers BMFlagIsAsymmetricTwin::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('IsAsymmetricTwin__[2,4]');
        $this->assertInstanceOf('BMFlagIsAsymmetricTwin', $flag);
        $this->assertEquals(array(2,4), $flag->value());
    }

    /**
     * @covers BMFlagIsAsymmetricTwin::__construct
     * @covers BMFlagIsAsymmetricTwin::value
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('IsAsymmetricTwin__[2,4]');
        $this->assertEquals('IsAsymmetricTwin__[2,4]', strval($flag));
    }
}
