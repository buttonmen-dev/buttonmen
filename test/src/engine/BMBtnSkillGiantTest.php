<?php

class BMBtnSkillGiantTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMBtnSkillGiant
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMBtnSkillGiant;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMBtnSkillGiant::is_button_slow
     */
    public function testIs_button_slow() {
        $retVal = BMBtnSkillGiant::is_button_slow(array());
        $this->assertTrue($retVal['is_button_slow']);
    }
}

