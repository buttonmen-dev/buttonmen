<?php

class BMExceptionUnimplementedSkillTest extends PHPUnit\Framework\TestCase  {
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
     * @covers BMExceptionUnimplementedSkill
     */
    public function testBMExceptionUnimplementedSkill()
    {
        try {
            throw new BMExceptionUnimplementedSkill("Skill not implemented");
        } catch (BMExceptionUnimplementedSkill $e) {
            $this->assertTrue(TRUE);
        } catch (Exception $e) {
            $this->assertTrue(FALSE);
        }
    }
}
