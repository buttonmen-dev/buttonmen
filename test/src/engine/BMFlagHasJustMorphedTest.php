<?php

class BMFlagHasJustMorphedTest extends PHPUnit_Framework_TestCase {
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustMorphed');
        $this->assertInstanceOf('BMFlagHasJustMorphed', $flag);
    }

    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustMorphed');
        $this->assertEquals('HasJustMorphed', strval($flag));
    }
}
