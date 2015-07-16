<?php

class BMFlagIsRageTargetReplacementTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('IsRageTargetReplacement');
        $this->assertInstanceOf('BMFlagIsRageTargetReplacement', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('IsRageTargetReplacement');
        $this->assertEquals('IsRageTargetReplacement', strval($flag));
    }
}
