<?php

class BMBtnSkillRandomBMFixedTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMBtnSkillRandomBMFixed
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMBtnSkillRandomBMFixed;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMBtnSkillRandomBMFixed::specify_recipes
     */
    public function testSpecify_recipes_valid_args() {
        global $BM_RAND_VALS, $BM_SKILL_RAND_VALS;

        $button = new BMButton;
        $args = array('button' => $button);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEmpty($button->recipe);

        // choose dice with 6, 20, 10, 12, 6 sides
        // and then choose 'f' on the third die and 'c' on the fifth die
        $BM_RAND_VALS = array(1, 5, 3, 4, 1,    // die sizes
                              2, 0);            // dice that have skill
        $BM_SKILL_RAND_VALS = array(
                              1,                // choose focus skill
        );

        $retval = BMBtnSkillRandomBMFixed::specify_recipes($args);
        $this->assertTrue($retval);
        $this->assertTrue($button->hasAlteredRecipe);
        $this->assertNotEmpty($button->recipe);
        $this->assertEquals('f(6) (6) f(10) (12) (20)', $button->recipe);
    }

    /**
     * @covers BMBtnSkillRandomBMFixed::specify_recipes
     */
    public function testSpecify_recipes_valid_args_already_specified() {
        $button = new BMButton;
        $button->recipe = '(4) (X)';
        $args = array('button' => $button);
        $retval = BMBtnSkillRandomBMFixed::specify_recipes($args);
        $this->assertFalse($retval);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEquals('(4) (X)', $button->recipe);
    }
}

