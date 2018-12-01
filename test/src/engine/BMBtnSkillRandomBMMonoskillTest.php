<?php

class BMBtnSkillRandomBMMonoskillTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMBtnSkillRandomBMMonoskill
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMBtnSkillRandomBMMonoskill;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMBtnSkillRandomBMMonoskill::specify_recipes
     */
    public function testSpecify_recipes_valid_args() {
        global $BM_RAND_VALS, $BM_SKILL_RAND_VALS;

        $button = new BMButton;
        $args = array('button' => $button);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEmpty($button->recipe);

        $BM_RAND_VALS = array(
            1, 5, 3, 4, // die sizes       6, 20, 10, 12
            6,          // swing type      X
            3, 3, 2     // skill position  4th and 3rd die
        );
        $BM_SKILL_RAND_VALS = array(
            8,          // skill type      H
        );

        $retval = BMBtnSkillRandomBMMonoskill::specify_recipes($args);
        $this->assertTrue($retval);
        $this->assertTrue($button->hasAlteredRecipe);
        $this->assertNotEmpty($button->recipe);
        $this->assertEquals('(6) (10) H(12) H(20) (X)', $button->recipe);
    }

    /**
     * @covers BMBtnSkillRandomBMMonoskill::specify_recipes
     */
    public function testSpecify_recipes_valid_args_already_specified() {
        $button = new BMButton;
        $button->recipe = '(4) (X)';
        $args = array('button' => $button);
        $retval = BMBtnSkillRandomBMMonoskill::specify_recipes($args);
        $this->assertFalse($retval);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEquals('(4) (X)', $button->recipe);
    }
}

