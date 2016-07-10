<?php

class BMBtnSkillRandomBMTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMBtnSkillRandomBM
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMBtnSkillRandomBM;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMBtnSkillRandomBM::specify_recipes
     */
    public function testSpecify_recipes_invalid_args() {
        $args = array();

        try {
            BMBtnSkillRandomBM::specify_recipes($args);
            $this->fail('specify_recipes should fail with empty args');
        } catch (LogicException $ex) {
            // expected
        }

        $args = array('button' => 'X');

        try {
            BMBtnSkillRandomBM::specify_recipes($args);
            $this->fail('specify_recipes should fail with invalid args');
        } catch (LogicException $ex) {
            // expected
        }
    }

    /**
     * @covers BMBtnSkillRandomBM::specify_recipes
     */
    public function testSpecify_recipes_valid_args() {
        $button = new BMButton;
        $args = array('button' => $button);
        $this->assertNull($button->hasAlteredRecipe);
        $retval = BMBtnSkillRandomBM::specify_recipes($args);
        $this->assertTrue($retval);
    }

    /**
     * @covers BMBtnSkillRandomBM::specify_recipes
     */
    public function testSpecify_recipes_valid_args_already_specified() {
        $button = new BMButton;
        $button->recipe = '(4) (X)';
        $args = array('button' => $button);
        $this->assertNull($button->hasAlteredRecipe);
        $retval = BMBtnSkillRandomBM::specify_recipes($args);
        $this->assertFalse($retval);
    }

    public function testRandomly_select_skills() {
        global $BM_RAND_VALS;

        $skillArray = array('s1', 's2', 's3', 's4');

        $BM_RAND_VALS = array(2, 2, 1);
        $retval = BMBtnSkillRandomBM::randomly_select_skills(2, $skillArray);
        $this->assertEquals(array('s3', 's2'), $retval);
    }

    public function testRandomly_select_swing_types() {
        global $BM_RAND_VALS;

        $BM_RAND_VALS = array(5);
        $retval = BMBtnSkillRandomBM::randomly_select_swing_types();
        $this->assertEquals(array('W'), $retval);
    }
}

