<?php

class BMFlagHasJustMorphedTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlag::__construct
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustMorphed');
        $this->assertInstanceOf('BMFlagHasJustMorphed', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustMorphed');
        $this->assertEquals('HasJustMorphed', strval($flag));
    }
}
