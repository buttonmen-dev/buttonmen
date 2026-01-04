<?php

class BMFlagIsAttackTargetTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('IsAttackTarget');
        $this->assertInstanceOf('BMFlagIsAttackTarget', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('IsAttackTarget');
        $this->assertEquals('IsAttackTarget', strval($flag));
    }
}
