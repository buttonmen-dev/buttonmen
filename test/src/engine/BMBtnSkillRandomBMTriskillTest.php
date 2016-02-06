<?php

class BMBtnSkillRandomBMTriskillTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMBtnSkillRandomBMTriskill
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMBtnSkillRandomBMTriskill;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMBtnSkillRandomBMTriskill::specify_recipes
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
            7, 2, 5,           // skill type      H, c, k
            3, 3, 2,           // skill positions -- all skills once
            2, 3,              // skill #4
            2, 4,              // skill #5
            2, 1,              // skill #6
            0, 1,              // skill #7
        );

        $retval = BMBtnSkillRandomBMTriskill::specify_recipes($args);
        $this->assertTrue($retval);
        $this->assertTrue($button->hasAlteredRecipe);
        $this->assertNotEmpty($button->recipe);
        $this->assertEquals('(6) Hk(10) k(12) kcH(20) k(X)', $button->recipe);
    }

    /**
     * @covers BMBtnSkillRandomBMTriskill::specify_recipes
     */
    public function testSpecify_recipes_valid_args_already_specified() {
        $button = new BMButton;
        $button->recipe = '(4) (X)';
        $args = array('button' => $button);
        $retval = BMBtnSkillRandomBMTriskill::specify_recipes($args);
        $this->assertFalse($retval);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEquals('(4) (X)', $button->recipe);
    }
}

