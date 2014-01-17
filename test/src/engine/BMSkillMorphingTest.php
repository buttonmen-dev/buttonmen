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
    public function testCapture_normal() {
        // normal morphing die capturing normal die
        $att = BMDie::create(6);
        $att->add_skill('Morphing');
        $att->add_skill('Trip');
        $def = BMDie::create(17);
        $def->add_skill('Konstant');

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att),
                          'defenders' => array($def));
        BMSkillMorphing::capture($parArray);

        $this->assertEquals(17, $att->max);
        $this->assertTrue($att->has_skill('Morphing'));
        $this->assertTrue($att->has_skill('Trip'));
        $this->assertFalse($att->has_skill('Konstant'));
    }

    /**
     * @covers BMSkillMorphing::capture
     */
    public function testCapture_twin() {
        // normal morphing die takes a twin die
        $att1 = BMDie::create(6);
        $att1->add_skill('Morphing');
        $att1->add_skill('Trip');
        $def1 = BMDieTwin::create(array(5,13));

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att1),
                          'defenders' => array($def1));
        BMSkillMorphing::capture($parArray);

        $this->assertInstanceOf('BMDieTwin', $att1);
        $this->assertEquals(2, $att1->min);
        $this->assertEquals(18, $att1->max);
        $this->assertEquals(5, $att1->dice[0]->max);
        $this->assertEquals(13, $att1->dice[1]->max);
        $this->assertTrue($att1->has_skill('Morphing'));
        $this->assertTrue($att1->has_skill('Trip'));
        $this->assertFalse($att1->has_skill('Konstant'));

        // twin morphing die takes a normal die
        $att2 = BMDieTwin::create(array(7,4));
        $att2->add_skill('Morphing');
        $att2->add_skill('Trip');
        $def2 = BMDie::create(15);

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att2),
                          'defenders' => array($def2));
        BMSkillMorphing::capture($parArray);

        $this->assertNotInstanceOf('BMDieTwin', $att2);
        $this->assertEquals(1, $att2->min);
        $this->assertEquals(15, $att2->max);
        $this->assertTrue($att2->has_skill('Morphing'));
        $this->assertTrue($att2->has_skill('Trip'));
        $this->assertFalse($att2->has_skill('Konstant'));
    }

    /**
     * @covers BMSkillMorphing::capture
     */
    public function testCapture_swing() {
        // normal morphing die capturing swing die
        $att1 = BMDie::create(6);
        $att1->add_skill('Morphing');
        $att1->add_skill('Trip');
        $def1 = BMDie::create_from_recipe('(X)');
        $def1->add_skill('Konstant');
        $def1->set_swingValue(array('X' => 5));

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att1),
                          'defenders' => array($def1));
        BMSkillMorphing::capture($parArray);

        $this->assertEquals(5, $att1->max);
        $this->assertNotInstanceOf('BMDieSwing', $att1);
        $this->assertTrue($att1->has_skill('Morphing'));
        $this->assertTrue($att1->has_skill('Trip'));
        $this->assertFalse($att1->has_skill('Konstant'));

        // morphing swing die capturing normal die
        $att2 = BMDie::create_from_recipe('(X)');
        $att2->add_skill('Morphing');
        $att2->add_skill('Trip');
        $att2->set_swingValue(array('X' => 7));
        $def2 = BMDie::create(11);
        $def2->add_skill('Konstant');

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att2),
                          'defenders' => array($def2));
        BMSkillMorphing::capture($parArray);

        $this->assertEquals(11, $att2->max);
        $this->assertNotInstanceOf('BMDieSwing', $att2);
        $this->assertTrue($att2->has_skill('Morphing'));
        $this->assertTrue($att2->has_skill('Trip'));
        $this->assertFalse($att2->has_skill('Konstant'));

        // normal morphing die capturing a twin die with swing subdice
        $att3 = BMDie::create(6);
        $att3->add_skill('Morphing');
        $att3->add_skill('Trip');
        $def3 = BMDie::create_from_recipe('(X,X)');
        $def3->add_skill('Konstant');
        $def3->set_swingValue(array('X' => 5));

        $parArray = array('type' => 'Power',
                          'attackers' => array(&$att3),
                          'defenders' => array($def3));
        BMSkillMorphing::capture($parArray);

        $this->assertEquals(10, $att3->max);
        $this->assertInstanceOf('BMDieTwin', $att3);
        $this->assertNotInstanceOf('BMDieSwing', $att3->dice[0]);
        $this->assertNotInstanceOf('BMDieSwing', $att3->dice[1]);
        $this->assertTrue($att3->has_skill('Morphing'));
        $this->assertTrue($att3->has_skill('Trip'));
        $this->assertFalse($att3->has_skill('Konstant'));
    }
}

?>