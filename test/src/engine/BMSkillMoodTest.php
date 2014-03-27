<?php

class BMSkillMoodTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillMood
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillMood;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

//    /**
//     * @covers BMSkillMood::capture
//     */
    public function testCapture_normal() {
//        // normal morphing die capturing normal die
//        $att = BMDie::create(6);
//        $att->add_skill('Morphing');
//        $att->add_skill('Trip');
//        $def = BMDie::create(17);
//        $def->add_skill('Konstant');
//
//        $parArray = array('type' => 'Power',
//                          'attackers' => array($att),
//                          'defenders' => array($def),
//                          'caller' => $att);
//        $newDie = BMSkillMorphing::capture($parArray);
//
//        $this->assertEquals(17, $newDie->max);
//        $this->assertTrue($newDie->has_skill('Morphing'));
//        $this->assertTrue($newDie->has_skill('Trip'));
//        $this->assertFalse($newDie->has_skill('Konstant'));
    }
//
//    /**
//     * @covers BMSkillMorphing::capture
//     */
//    public function testCapture_twin() {
//        // normal morphing die takes a twin die
//        $att1 = BMDie::create(6);
//        $att1->add_skill('Morphing');
//        $att1->add_skill('Trip');
//        $def1 = BMDieTwin::create(array(5,13));
//        $def1->add_skill('Konstant');
//
//        $parArray = array('type' => 'Power',
//                          'attackers' => array($att1),
//                          'defenders' => array($def1),
//                          'caller' => $att1);
//        $newDie1 = BMSkillMorphing::capture($parArray);
//
//        $this->assertInstanceOf('BMDieTwin', $newDie1);
//        $this->assertEquals(2, $newDie1->min);
//        $this->assertEquals(18, $newDie1->max);
//        $this->assertEquals(5, $newDie1->dice[0]->max);
//        $this->assertEquals(13, $newDie1->dice[1]->max);
//        $this->assertTrue($newDie1->has_skill('Morphing'));
//        $this->assertTrue($newDie1->has_skill('Trip'));
//        $this->assertFalse($newDie1->has_skill('Konstant'));
//
//        // twin morphing die takes a normal die
//        $att2 = BMDieTwin::create(array(7,4));
//        $att2->add_skill('Morphing');
//        $att2->add_skill('Trip');
//        $def2 = BMDie::create(15);
//        $def2->add_skill('Konstant');
//
//        $parArray = array('type' => 'Power',
//                          'attackers' => array($att2),
//                          'defenders' => array($def2),
//                          'caller' => $att2);
//        $newDie2 = BMSkillMorphing::capture($parArray);
//
//        $this->assertNotInstanceOf('BMDieTwin', $newDie2);
//        $this->assertEquals(1, $newDie2->min);
//        $this->assertEquals(15, $newDie2->max);
//        $this->assertTrue($newDie2->has_skill('Morphing'));
//        $this->assertTrue($newDie2->has_skill('Trip'));
//        $this->assertFalse($newDie2->has_skill('Konstant'));
//    }
//
//    /**
//     * @covers BMSkillMorphing::capture
//     * @covers BMSkillMorphing::create_morphing_clone_target
//     * @covers BMDie::cast_as_BMDie
//     */
//    public function testCapture_swing() {
//        // normal morphing die capturing swing die
//        $att1 = BMDie::create(6);
//        $att1->add_skill('Morphing');
//        $att1->add_skill('Trip');
//        $def1 = BMDie::create_from_recipe('(X)');
//        $def1->add_skill('Konstant');
//        $def1->set_swingValue(array('X' => 5));
//
//        $parArray = array('type' => 'Power',
//                          'attackers' => array($att1),
//                          'defenders' => array($def1),
//                          'caller' => $att1);
//        $newDie1 = BMSkillMorphing::capture($parArray);
//
//        $this->assertEquals(5, $newDie1->max);
//        $this->assertNotInstanceOf('BMDieSwing', $newDie1);
//        $this->assertTrue($newDie1->has_skill('Morphing'));
//        $this->assertTrue($newDie1->has_skill('Trip'));
//        $this->assertFalse($newDie1->has_skill('Konstant'));
//
//        // morphing swing die capturing normal die
//        $att2 = BMDie::create_from_recipe('(X)');
//        $att2->add_skill('Morphing');
//        $att2->add_skill('Trip');
//        $att2->set_swingValue(array('X' => 7));
//        $def2 = BMDie::create(11);
//        $def2->add_skill('Konstant');
//
//        $parArray = array('type' => 'Power',
//                          'attackers' => array($att2),
//                          'defenders' => array($def2),
//                          'caller' => $att2);
//        $newDie2 = BMSkillMorphing::capture($parArray);
//
//        $this->assertEquals(11, $newDie2->max);
//        $this->assertNotInstanceOf('BMDieSwing', $newDie2);
//        $this->assertTrue($newDie2->has_skill('Morphing'));
//        $this->assertTrue($newDie2->has_skill('Trip'));
//        $this->assertFalse($newDie2->has_skill('Konstant'));
//
//        // normal morphing die capturing a twin die with swing subdice
//        $att3 = BMDie::create(6);
//        $att3->add_skill('Morphing');
//        $att3->add_skill('Trip');
//        $def3 = BMDie::create_from_recipe('(X,X)');
//        $def3->add_skill('Konstant');
//        $def3->set_swingValue(array('X' => 5));
//
//        $parArray = array('type' => 'Power',
//                          'attackers' => array($att3),
//                          'defenders' => array($def3),
//                          'caller' => $att3);
//        $newDie3 = BMSkillMorphing::capture($parArray);
//
//        $this->assertEquals(10, $newDie3->max);
//        $this->assertInstanceOf('BMDieTwin', $newDie3);
//        $this->assertNotInstanceOf('BMDieSwing', $newDie3->dice[0]);
//        $this->assertNotInstanceOf('BMDieSwing', $newDie3->dice[1]);
//        $this->assertTrue($newDie3->has_skill('Morphing'));
//        $this->assertTrue($newDie3->has_skill('Trip'));
//        $this->assertFalse($newDie3->has_skill('Konstant'));
//    }
}
