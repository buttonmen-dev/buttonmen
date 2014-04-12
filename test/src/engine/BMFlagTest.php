<?php

class BMFlagTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMFlag
     */
    protected $object;

    /*
     * @covers BMFlag::as_string
     */
    public function testAs_string()
    {
        $flag = BMFlag::create_from_string('WasJustCaptured');
        $this->assertEquals('WasJustCaptured', $flag->as_string());
    }

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
}
