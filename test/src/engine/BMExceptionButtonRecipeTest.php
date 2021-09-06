<?php

class BMExceptionButtonRecipeTest extends PHPUnit_Framework_TestCase  {
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMExceptionButtonRecipe
     */
    public function testBMExceptionButtonRecipe()
    {
        try {
            throw new BMExceptionButtonRecipe('Button recipe exception thrown');
        } catch (BMExceptionButtonRecipe $e) {
            $this->assertTrue(TRUE);
        } catch (Exception $e) {
            $this->assertTrue(FALSE);
        }
    }
}
