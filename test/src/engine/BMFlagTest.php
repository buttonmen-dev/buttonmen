<?php

class BMFlagTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMFlag
     */
    protected $object;

    /**
     * @covers BMFlag::create_from_string
     */
    public function testCreate_from_string()
    {
        $flag1 = BMFlag::create_from_string('');
        $this->assertNull($flag1);

        $flag2 = BMFlag::create_from_string('WasJustCaptured');
        $this->assertInstanceOf('BMFlag', $flag2);
        $this->assertInstanceOf('BMFlagWasJustCaptured', $flag2);

        $flag3 = BMFlag::create_from_string('rubbish');
        $this->assertNull($flag3);
    }

    /*
     * @covers BMFlag::__toString
     */
    public function test__toString()
    {
        $flag = BMFlag::create_from_string('WasJustCaptured');
        $this->assertEquals('WasJustCaptured', ''.$flag);
    }
}
