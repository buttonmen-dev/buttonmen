<?php

class BMFlagHasJustBeenMoodyTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMFlagHasJustBeenMoody::__construct
     * @covers BMFlagHasJustBeenMoody::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustBeenMoody__p(X=6)?');
        $this->assertInstanceOf('BMFlagHasJustBeenMoody', $flag);
        $this->assertEquals('p(X=6)?', $flag->value());
    }

    /**
     * @covers BMFlagHasJustBeenMoody::__toString
     * @covers BMFlagHasJustBeenMoody::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustBeenMoody__p(X=6)?');
        $this->assertEquals('HasJustBeenMoody__p(X=6)?', strval($flag));
    }
}
