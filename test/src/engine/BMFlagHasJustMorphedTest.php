<?php

class BMFlagHasJustMorphedTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
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
