<?php

class BMFlagAddReserveTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlag::__construct
     * @covers BMFlag::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('AddReserve');
        $this->assertInstanceOf('BMFlagAddReserve', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('AddReserve');
        $this->assertEquals('AddReserve', strval($flag));
    }
}
