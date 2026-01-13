<?php

class BMFlagAddReserveTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
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
