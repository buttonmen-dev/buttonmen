<?php

class BMBtnSkillEchoTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMBtnSkillEcho
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMBtnSkillEcho;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMBtnSkillEcho::load_buttons
     */
    public function testLoad_buttons_opp_undefined() {
        $retVal = BMBtnSkillEcho::load_buttons(array('name' => 'Echo',
                                                     'recipe' => '',
                                                     'oppname' => 'Echo',
                                                     'opprecipe' => ''));
        $this->assertEquals('(4) (4) (10) (12) (X)', $retVal['recipe']);
    }

    /**
     * @covers BMBtnSkillEcho::load_buttons
     */
    public function testLoad_buttons_opp_defined() {
        $retVal = BMBtnSkillEcho::load_buttons(array('name' => 'Echo',
                                                     'recipe' => '',
                                                     'oppname' => 'Hammer',
                                                     'opprecipe' => '(6) (12) (20) (20) (X)'));
        $this->assertEquals('(6) (12) (20) (20) (X)', $retVal['recipe']);
    }
}

