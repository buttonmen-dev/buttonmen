<?php

class BMBtnSkillRandomBMTetraskillTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMBtnSkillRandomBMTetraskill
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMBtnSkillRandomBMTetraskill;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMBtnSkillRandomBMTetraskill::specify_recipes
     */
    public function testSpecify_recipes_valid_args() {
        global $BM_RAND_VALS;

        $button = new BMButton;
        $args = array('button' => $button);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEmpty($button->recipe);

        $BM_RAND_VALS = array(
            1, 5, 3, 4,        // die sizes       6, 20, 10, 12
            6,                 // swing type      X
            7, 2, 5, 2, 0,     // skill type      H, c, k, ignored, B
            3, 3, 2, 0,        // skill positions -- all skills once
            2, 3,              // skill #5
            2, 4,              // skill #6
            2, 1,              // skill #7
            3, 4,              // skill #8
            3, 1,              // skill #9
            0, 1,              // skill #10
        );

        $retval = BMBtnSkillRandomBMTetraskill::specify_recipes($args);
        $this->assertTrue($retval);
        $this->assertTrue($button->hasAlteredRecipe);
        $this->assertNotEmpty($button->recipe);
        $this->assertEquals('B(6) HBk(10) k(12) kcH(20) Bk(X)', $button->recipe);
    }

    /**
     * @covers BMBtnSkillRandomBMTetraskill::specify_recipes
     */
    public function testSpecify_recipes_valid_args_already_specified() {
        $button = new BMButton;
        $button->recipe = '(4) (X)';
        $args = array('button' => $button);
        $retval = BMBtnSkillRandomBMTetraskill::specify_recipes($args);
        $this->assertFalse($retval);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEquals('(4) (X)', $button->recipe);
    }
}

