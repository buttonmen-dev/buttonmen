<?php

class BMFlagTwinTest extends PHPUnit\Framework\TestCase {

    /**
     * @covers BMFlagTwin::__construct
     * @covers BMFlagTwin::value
     */
    public function testConstruct() {
        $flag = BMFlag::create_from_string('Twin__{"sides":[2,4],"values":[1,3]}');
        $this->assertInstanceOf('BMFlagTwin', $flag);
        $this->assertEquals(array('sides' => array(2,4), 'values' => array(1,3)), $flag->value());
    }

    /**
     * @covers BMFlagTwin::__construct
     * @covers BMFlagTwin::value
     */
    public function testToString() {
        $flag = BMFlag::create_from_string('Twin__{"sides":[2,4],"values":[1,3]}');
        $this->assertEquals('Twin__{"sides":[2,4],"values":[1,3]}', strval($flag));
    }
}
