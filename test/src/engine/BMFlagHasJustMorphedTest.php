<?php

class BMFlagHasJustMorphedTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagHasJustMorphed::__construct
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustMorphed');
        $this->assertInstanceOf('BMFlagHasJustMorphed', $flag);
    }

    /**
     * @covers BMFlagHasJustMorphed::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustMorphed');
        $this->assertEquals('HasJustMorphed', strval($flag));
    }
}
