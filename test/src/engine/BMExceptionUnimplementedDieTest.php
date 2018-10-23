<?php

class BMExceptionUnimplementedDieTest extends PHPUnit_Framework_TestCase  {
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
     * @covers BMExceptionUnimplementedDie
     */
    public function testBMExceptionUnimplementedDie()
    {
        try {
            throw new BMExceptionUnimplementedDie("Wildcard skill not implemented");
        } catch (BMExceptionUnimplementedDie $e) {
            $this->assertTrue(TRUE);
        } catch (Exception $e) {
            $this->assertTrue(FALSE);
        }
    }
}
