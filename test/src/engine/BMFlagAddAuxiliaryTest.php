<?php

class BMFlagAddAuxiliaryTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
     * @covers BMFlag::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('AddAuxiliary');
        $this->assertInstanceOf('BMFlagAddAuxiliary', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('AddAuxiliary');
        $this->assertEquals('AddAuxiliary', strval($flag));
    }
}
