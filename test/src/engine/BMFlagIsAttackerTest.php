<?php

class BMFlagIsAttackerTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('IsAttacker');
        $this->assertInstanceOf('BMFlagIsAttacker', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('IsAttacker');
        $this->assertEquals('IsAttacker', strval($flag));
    }
}
