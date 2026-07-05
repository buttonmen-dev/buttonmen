<?php

class BMFlagHasJustSplitTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlagHasJustGrownOrShrunk::__construct
     * @covers BMFlagHasJustGrownOrShrunk::value
     * @covers BMFlagHasJustSplit::__construct
     * @covers BMFlagHasJustSplit::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustSplit__B(8)');
        $this->assertInstanceOf('BMFlagHasJustSplit', $flag);
        $this->assertEquals('B(8)', $flag->value());
    }

    /**
     * @covers BMFlagHasJustGrownOrShrunk::__toString
     * @covers BMFlagHasJustSplit::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustShrunk__B(8)');
        $this->assertEquals('HasJustShrunk__B(8)', strval($flag));
    }
}
