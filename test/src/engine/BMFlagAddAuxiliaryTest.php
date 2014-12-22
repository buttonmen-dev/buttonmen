<?php

class BMFlagAddAuxiliaryTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagAddAuxiliary::__construct
     * @covers BMFlagAddAuxiliary::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('AddAuxiliary');
        $this->assertInstanceOf('BMFlagAddAuxiliary', $flag);
    }

    /**
     * @covers BMFlagAddAuxiliary::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('AddAuxiliary');
        $this->assertEquals('AddAuxiliary', strval($flag));
    }
}
