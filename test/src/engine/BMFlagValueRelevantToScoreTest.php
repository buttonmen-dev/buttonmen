<?php

class BMFlagValueRelevantToScoreTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlag::create_from_string
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('ValueRelevantToScore');
        $this->assertInstanceOf('BMFlagValueRelevantToScore', $flag);
    }

    /**
     * @covers BMFlag::__toString
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('ValueRelevantToScore');
        $this->assertEquals('ValueRelevantToScore', strval($flag));
    }
}
