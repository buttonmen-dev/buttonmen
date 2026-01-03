<?php

class BMExceptionDieRecipeTest extends PHPUnit\Framework\TestCase  {
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMExceptionDieRecipe
     */
    public function testBMExceptionDieRecipe()
    {
        try {
            throw new BMExceptionDieRecipe('Die recipe exception thrown');
        } catch (BMExceptionDieRecipe $e) {
            $this->assertTrue(TRUE);
        } catch (Exception $e) {
            $this->assertTrue(FALSE);
        }
    }
}
