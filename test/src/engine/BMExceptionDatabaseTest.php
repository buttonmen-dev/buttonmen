<?php

class BMExceptionDatabaseTest extends PHPUnit\Framework\TestCase  {
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
     * @covers BMExceptionDatabase
     */
    public function testBMExceptionDatabase()
    {
        try {
            throw new BMExceptionDatabase("Received unexpected response to database query");
        } catch (BMExceptionDatabase $e) {
            $this->assertTrue(TRUE);
        } catch (Exception $e) {
            $this->assertTrue(FALSE);
        }
    }
}
