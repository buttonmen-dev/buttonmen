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
        // normal morphing die capturing normal die
        $att1 = BMDie::create(6);
        $att1->add_skill('Morphing');
        $att1->add_skill('Trip');
        $def1 = BMDie::create(17);
        $def1->add_skill('Konstant');

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att1),
                          'defenders' => array($def1));
        BMSkillMorphing::capture($parArray);

        $this->assertEquals(17, $att1->max);
        $this->assertTrue($att1->has_skill('Morphing'));
        $this->assertTrue($att1->has_skill('Trip'));
        $this->assertFalse($att1->has_skill('Konstant'));

        // normal morphing die takes a twin die
        $att2 = BMDie::create(6);
        $att2->add_skill('Morphing');
        $att2->add_skill('Trip');
        $def2 = BMDieTwin::create(array(5,13));

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att2),
                          'defenders' => array($def2));
        BMSkillMorphing::capture($parArray);

        $this->assertTrue($att2 instanceof BMDieTwin);
        $this->assertEquals(2, $att2->min);
        $this->assertEquals(18, $att2->max);
        $this->assertEquals(5, $att2->dice[0]->max);
        $this->assertEquals(13, $att2->dice[1]->max);
        $this->assertTrue($att2->has_skill('Morphing'));
        $this->assertTrue($att2->has_skill('Trip'));
        $this->assertFalse($att2->has_skill('Konstant'));

        // twin morphing die takes a normal die
        $att3 = BMDieTwin::create(array(7,4));
        $att3->add_skill('Morphing');
        $att3->add_skill('Trip');
        $def3 = BMDie::create(15);

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att3),
                          'defenders' => array($def3));
        BMSkillMorphing::capture($parArray);

        $this->assertFalse($att3 instanceof BMDieTwin);
        $this->assertEquals(1, $att3->min);
        $this->assertEquals(15, $att3->max);
        $this->assertTrue($att3->has_skill('Morphing'));
        $this->assertTrue($att3->has_skill('Trip'));
        $this->assertFalse($att3->has_skill('Konstant'));
    }
}

?>