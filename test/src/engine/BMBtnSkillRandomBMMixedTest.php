<?php

class BMBtnSkillRandomBMMixedTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMBtnSkillRandomBMMixed
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMBtnSkillRandomBMMixed;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMBtnSkillRandomBMMixed::specify_recipes
     */
    public function testSpecify_recipes_valid_args() {
        global $BM_RAND_VALS, $BM_SKILL_RAND_VALS;

        $button = new BMButton;
        $args = array('button' => $button);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEmpty($button->recipe);

        // choose dice with 6, 20, 10, 12, 6 sides
        // then choose skills 'k', 'c', and 'z'
        // then place 'k' on the 2nd die and the 1st die
        // then place 'c' on the 5th die and the 4th die
        // then place 'z' on the 2nd die and the 4th die
        //
        // Note: the values used to select a skill may change when new skills
        //       become available
        $BM_RAND_VALS = array(1, 5, 3, 4, 1,      // die sizes
                              1, 0,               // die indices for skill type 1
                              4, 4, 3,            // die indices for skill type 2, including repeat
                              1, 3);              // die indices for skill type 3
        $BM_SKILL_RAND_VALS = array(
                              6, 2, 18,           // skill types
        );

        $retval = BMBtnSkillRandomBMMixed::specify_recipes($args);
        $this->assertTrue($retval);
        $this->assertTrue($button->hasAlteredRecipe);
        $this->assertNotEmpty($button->recipe);

        $this->assertEquals('k(6) kz(6) (10) cz(12) c(20)', $button->recipe);
    }

    /**
     * @covers BMBtnSkillRandomBMMixed::specify_recipes
     */
    public function testSpecify_recipes_valid_args_already_specified() {
        $button = new BMButton;
        $button->recipe = '(4) (X)';
        $args = array('button' => $button);
        $retval = BMBtnSkillRandomBMMixed::specify_recipes($args);
        $this->assertFalse($retval);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEquals('(4) (X)', $button->recipe);
    }
}

