<?php

class BMBtnSkillUniqueSwingTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMBtnSkillUniqueSwing
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMBtnSkillUniqueSwing;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMBtnSkillUniqueSwing::are_unique_swing_values_valid
     */
    public function testAre_unique_swing_values_valid() {
        $retVal = BMBtnSkillUniqueSwing::are_unique_swing_values_valid(array(
            'swingValueArray' => array(
                'V' => 6,
                'W' => 4,
                'X' => 4,
                'Y' => 1,
                'Z' => 4
            )
        ));
        $this->assertFalse($retVal['are_unique_swing_values_valid']);

        $retVal = BMBtnSkillUniqueSwing::are_unique_swing_values_valid(array(
            'swingValueArray' => array(
                'V' => 6,
                'W' => 4,
                'X' => 5,
                'Y' => 1,
                'Z' => 7
            )
        ));
        $this->assertTrue($retVal['are_unique_swing_values_valid']);
    }
}
