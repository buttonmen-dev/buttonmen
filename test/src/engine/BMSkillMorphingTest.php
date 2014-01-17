<?php

class BMSkillMorphingTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillMorphing
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillMorphing;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillMorphing::capture
     */
    public function testCapture() {
        $att = BMDie::create(6);
        $att->add_skill('Morphing');

        $def = BMDie::create(17);

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att),
                          'defenders' => array($def));
        BMSkillMorphing::capture($parArray);

        $this->assertEquals(17, $att->max);
    }
}

?>