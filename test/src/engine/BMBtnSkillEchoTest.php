<?php

class BMBtnSkillEchoTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMBtnSkillEcho
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMBtnSkillEcho;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMBtnSkillEcho::specify_recipes
     */
    public function testSpecify_recipes_opp_undefined() {
        $button = new BMButton;
        $button->name = 'Echo';

        $args = array('button' => $button, 'oppbutton' => NULL);

        BMBtnSkillEcho::specify_recipes($args);
        $this->assertEquals('', $button->recipe);
    }

    /**
     * @covers BMBtnSkillEcho::specify_recipes
     */
    public function testSpecify_recipes_opp_Echo() {
        $button = new BMButton;
        $button->name = 'Echo';

        $oppbutton = new BMButton;
        $oppbutton->name = 'Echo';

        $args = array('button' => $button, 'oppbutton' => $oppbutton);

        BMBtnSkillEcho::specify_recipes($args);
        $this->assertEquals('(4) (4) (10) (12) (X)', $button->recipe);
    }

    /**
     * @covers BMBtnSkillEcho::specify_recipes
     */
    public function testSpecify_recipes_opp_defined() {
        $button = new BMButton;
        $button->name = 'Echo';

        $oppbutton = new BMButton;
        $oppbutton->name = 'Hammer';
        $oppbutton->recipe = '(6) (12) (20) (20) (X)';

        $args = array('button' => $button, 'oppbutton' => $oppbutton);

        BMBtnSkillEcho::specify_recipes($args);
        $this->assertEquals('(6) (12) (20) (20) (X)', $button->recipe);
    }
}

