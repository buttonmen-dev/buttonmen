<?php

class BMFlagHasJustTurboedTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlagHasJustTurboed::__construct
     * @covers BMFlagHasJustTurboed::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('HasJustTurboed__o(X=5)!');
        $this->assertInstanceOf('BMFlagHasJustTurboed', $flag);
        $this->assertEquals('o(X=5)!', $flag->value());
    }

    /**
     * @covers BMFlagHasJustTurboed::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('HasJustTurboed__o(X=5)!');
        $this->assertEquals('HasJustTurboed__o(X=5)!', strval($flag));
    }
}
