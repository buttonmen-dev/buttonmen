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
            3, 3, 2,           // skill positions -- first skill twice
            0, 0, 4,           // skill positions -- second skill twice
            4, 1,              // skill positions -- third skill twice
        );

        $retval = BMBtnSkillRandomBMTriskill::specify_recipes($args);
        $this->assertTrue($retval);
        $this->assertTrue($button->hasAlteredRecipe);
        $this->assertNotEmpty($button->recipe);
        $this->assertEquals('c(6) k(10) H(12) H(20) ck(X)', $button->recipe);
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

