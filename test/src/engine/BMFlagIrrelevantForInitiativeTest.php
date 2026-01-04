<?php

class BMFlagIrrelevantForInitiativeTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
     * @covers BMFlag::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('IrrelevantForInitiative');
        $this->assertInstanceOf('BMFlagIrrelevantForInitiative', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('IrrelevantForInitiative');
        $this->assertEquals('IrrelevantForInitiative', strval($flag));
    }
}
