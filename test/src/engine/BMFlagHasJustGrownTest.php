<?php

class BMFlagHasJustGrownTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlagHasJustGrownOrShrunk::__construct
     * @covers BMFlagHasJustGrownOrShrunk::value
     * @covers BMFlagHasJustGrown::__construct
     * @covers BMFlagHasJustGrown::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustGrown__H(8)');
        $this->assertInstanceOf('BMFlagHasJustGrown', $flag);
        $this->assertEquals('H(8)', $flag->value());
    }

    /**
     * @covers BMFlagHasJustGrownOrShrunk::__toString
     * @covers BMFlagHasJustGrown::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustGrown__H(8)');
        $this->assertEquals('HasJustGrown__H(8)', strval($flag));
    }
}
