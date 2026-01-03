<?php

class BMBtnSkillRandomBMAnimeTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMBtnSkillRandomBMAnime
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMBtnSkillRandomBMAnime;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMBtnSkillRandomBMAnime::specify_recipes
     */
    public function testSpecify_recipes_valid_args() {
        global $BM_RAND_VALS;

        $button = new BMButton;
        $args = array('button' => $button);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEmpty($button->recipe);

        // choose dice with 6, 20, 10, 12, 8, 8, 4, 20 sides
        $BM_RAND_VALS = array(1, 5, 3, 4, 2, 2, 0, 5);    // die sizes

        $retval = BMBtnSkillRandomBMAnime::specify_recipes($args);
        $this->assertTrue($retval);
        $this->assertTrue($button->hasAlteredRecipe);
        $this->assertNotEmpty($button->recipe);
        $this->assertEquals('(6) (10) (12) (20) r(4) r(8) r(8) r(20)', $button->recipe);
    }

    /**
     * @covers BMBtnSkillRandomBMAnime::specify_recipes
     */
    public function testSpecify_recipes_valid_args_already_specified() {
        $button = new BMButton;
        $button->recipe = '(4) (X)';
        $args = array('button' => $button);
        $retval = BMBtnSkillRandomBMAnime::specify_recipes($args);
        $this->assertFalse($retval);
        $this->assertNull($button->hasAlteredRecipe);
        $this->assertEquals('(4) (X)', $button->recipe);
    }
}

