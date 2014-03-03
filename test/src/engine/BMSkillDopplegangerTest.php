<?php

class BMSkillDopplegangerTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillDoppleganger
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillDoppleganger;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillDoppleganger::capture
     */
    public function testCapture_power_normal() {
        // normal morphing die capturing normal die
        $att = BMDie::create(6);
        $att->add_skill('Doppleganger');
        $att->add_skill('Trip');
        $def = BMDie::create(17);
        $def->add_skill('Konstant');

        $parArray = array('type' => 'Power',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        $newDie = BMSkillDoppleganger::capture($parArray);

        $this->assertEquals(17, $newDie->max);
        $this->assertFalse($newDie->has_skill('Doppleganger'));
        $this->assertFalse($newDie->has_skill('Trip'));
        $this->assertTrue($newDie->has_skill('Konstant'));
    }

    /**
     * @covers BMSkillDoppleganger::capture
     */
    public function testCapture_skill_normal() {
        // normal morphing die capturing normal die
        $att = BMDie::create(6);
        $att->add_skill('Doppleganger');
        $att->add_skill('Trip');
        $def = BMDie::create(17);
        $def->add_skill('Konstant');

        $parArray = array('type' => 'Skill',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        $newDie = BMSkillDoppleganger::capture($parArray);

        $this->assertNull($newDie);
    }

    /**
     * @covers BMSkillDoppleganger::capture
     */
    public function testCapture_twin() {
        // normal doppleganger die takes a twin die
        $att1 = BMDie::create(6);
        $att1->add_skill('Doppleganger');
        $att1->add_skill('Trip');
        $def1 = BMDieTwin::create(array(5,13));
        $def1->add_skill('Konstant');

        $parArray = array('type' => 'Power',
                          'attackers' => array($att1),
                          'defenders' => array($def1),
                          'caller' => $att1);
        $newDie1 = BMSkillDoppleganger::capture($parArray);

        $this->assertInstanceOf('BMDieTwin', $newDie1);
        $this->assertEquals(2, $newDie1->min);
        $this->assertEquals(18, $newDie1->max);
        $this->assertEquals(5, $newDie1->dice[0]->max);
        $this->assertEquals(13, $newDie1->dice[1]->max);
        $this->assertFalse($newDie1->has_skill('Doppleganger'));
        $this->assertFalse($newDie1->has_skill('Trip'));
        $this->assertTrue($newDie1->has_skill('Konstant'));

        // twin doppleganger die takes a normal die
        $att2 = BMDieTwin::create(array(7,4));
        $att2->add_skill('Doppleganger');
        $att2->add_skill('Trip');
        $def2 = BMDie::create(15);
        $def2->add_skill('Konstant');

        $parArray = array('type' => 'Power',
                          'attackers' => array($att2),
                          'defenders' => array($def2),
                          'caller' => $att2);
        $newDie2 = BMSkillDoppleganger::capture($parArray);

        $this->assertNotInstanceOf('BMDieTwin', $newDie2);
        $this->assertEquals(1, $newDie2->min);
        $this->assertEquals(15, $newDie2->max);
        $this->assertFalse($newDie2->has_skill('Doppleganger'));
        $this->assertFalse($newDie2->has_skill('Trip'));
        $this->assertTrue($newDie2->has_skill('Konstant'));
    }

    /**
     * @covers BMSkillDoppleganger::capture
     */
    public function testCapture_swing() {
        // normal doppleganger die capturing swing die
        $att1 = BMDie::create(6);
        $att1->add_skill('Doppleganger');
        $att1->add_skill('Trip');
        $def1 = BMDie::create_from_recipe('(X)');
        $def1->add_skill('Konstant');
        $def1->set_swingValue(array('X' => 5));

        $parArray = array('type' => 'Power',
                          'attackers' => array($att1),
                          'defenders' => array($def1),
                          'caller' => $att1);
        $newDie1 = BMSkillDoppleganger::capture($parArray);

        $this->assertEquals(5, $newDie1->max);
        $this->assertNotInstanceOf('BMDieSwing', $newDie1);
        $this->assertFalse($newDie1->has_skill('Doppleganger'));
        $this->assertFalse($newDie1->has_skill('Trip'));
        $this->assertTrue($newDie1->has_skill('Konstant'));

        // doppleganger swing die capturing normal die
        $att2 = BMDie::create_from_recipe('(X)');
        $att2->add_skill('Doppleganger');
        $att2->add_skill('Trip');
        $att2->set_swingValue(array('X' => 7));
        $def2 = BMDie::create(11);
        $def2->add_skill('Konstant');

        $parArray = array('type' => 'Power',
                          'attackers' => array($att2),
                          'defenders' => array($def2),
                          'caller' => $att2);
        $newDie2 = BMSkillDoppleganger::capture($parArray);

        $this->assertEquals(11, $newDie2->max);
        $this->assertNotInstanceOf('BMDieSwing', $newDie2);
        $this->assertFalse($newDie2->has_skill('Morphing'));
        $this->assertFalse($newDie2->has_skill('Trip'));
        $this->assertTrue($newDie2->has_skill('Konstant'));

        // normal doppleganger die capturing a twin die with swing subdice
        $att3 = BMDie::create(6);
        $att3->add_skill('Doppleganger');
        $att3->add_skill('Trip');
        $def3 = BMDie::create_from_recipe('(X,X)');
        $def3->add_skill('Konstant');
        $def3->set_swingValue(array('X' => 5));

        $parArray = array('type' => 'Power',
                          'attackers' => array($att3),
                          'defenders' => array($def3),
                          'caller' => $att3);
        $newDie3 = BMSkillDoppleganger::capture($parArray);

        $this->assertEquals(10, $newDie3->max);
        $this->assertInstanceOf('BMDieTwin', $newDie3);
        $this->assertNotInstanceOf('BMDieSwing', $newDie3->dice[0]);
        $this->assertNotInstanceOf('BMDieSwing', $newDie3->dice[1]);
        $this->assertFalse($newDie3->has_skill('Morphing'));
        $this->assertFalse($newDie3->has_skill('Trip'));
        $this->assertTrue($newDie3->has_skill('Konstant'));
    }
}
