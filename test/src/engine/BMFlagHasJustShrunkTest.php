<?php

class BMFlagHasJustShrunkTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlagHasJustGrownOrShrunk::__construct
     * @covers BMFlagHasJustGrownOrShrunk::value
     * @covers BMFlagHasJustShrunk::__construct
     * @covers BMFlagHasJustShrunk::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustShrunk__h(8)');
        $this->assertInstanceOf('BMFlagHasJustShrunk', $flag);
        $this->assertEquals('h(8)', $flag->value());
    }

    /**
     * @covers BMFlagHasJustGrownOrShrunk::__toString
     * @covers BMFlagHasJustShrunk::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustShrunk__h(8)');
        $this->assertEquals('HasJustShrunk__h(8)', strval($flag));
    }
}
