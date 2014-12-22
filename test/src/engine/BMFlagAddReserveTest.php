<?php

class BMFlagAddReserveTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagAddReserve::__construct
     * @covers BMFlagAddReserve::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('AddReserve');
        $this->assertInstanceOf('BMFlagAddReserve', $flag);
    }

    /**
     * @covers BMFlagAddReserve::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('AddReserve');
        $this->assertEquals('AddReserve', strval($flag));
    }
}
