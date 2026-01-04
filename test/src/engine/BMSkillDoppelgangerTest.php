<?php

class BMSkillDoppelgangerTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillDoppelganger
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillDoppelganger;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillDoppelganger::capture
     */
    public function testCapture_power_normal() {
        $att = BMDie::create(6);
        $att->add_skill('Doppelganger');
        $att->add_skill('Trip');
        $def = BMDie::create(17);
        $def->add_skill('Konstant');
        $def->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($att), array($def));
        $game->attack = array(0, 1, array(0), array(0), 'Power');

        $att->ownerObject = $game;
        $att->playerIdx = 0;
        $def->ownerObject = $game;

        $parArray = array('type' => 'Power',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        BMSkillDoppelganger::capture($parArray);

        $newDie = $parArray['attackers'][0];

        $this->assertEquals(17, $newDie->max);
        $this->assertFalse($newDie->has_skill('Doppelganger'));
        $this->assertFalse($newDie->has_skill('Trip'));
        $this->assertTrue($newDie->has_skill('Konstant'));
    }

    /**
     * @covers BMSkillDoppelganger::capture
     */
    public function testCapture_skill_normal() {
        $att = BMDie::create(6);
        $att->add_skill('Doppelganger');
        $att->add_skill('Trip');
        $def = BMDie::create(17);
        $def->add_skill('Konstant');

        $parArray = array('type' => 'Skill',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        $newDie = BMSkillDoppelganger::capture($parArray);

        $this->assertNull($newDie);
    }

    /**
     * @covers BMSkillDoppelganger::capture
     */
    public function testCapture_twin() {
        // normal doppelganger die takes a twin die
        $att1 = BMDie::create(6);
        $att1->add_skill('Doppelganger');
        $att1->add_skill('Trip');
        $def1 = BMDieTwin::create(array(5,13));
        $def1->add_skill('Konstant');
        $def1->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($att1), array($def1));
        $game->attack = array(0, 1, array(0), array(0), 'Power');

        $att1->ownerObject = $game;
        $att1->playerIdx = 0;
        $def1->ownerObject = $game;

        $parArray = array('type' => 'Power',
                          'attackers' => array($att1),
                          'defenders' => array($def1),
                          'caller' => $att1);
        BMSkillDoppelganger::capture($parArray);

        $newDie1 = $parArray['attackers'][0];

        $this->assertInstanceOf('BMDieTwin', $newDie1);
        $this->assertEquals(2, $newDie1->min);
        $this->assertEquals(18, $newDie1->max);
        $this->assertEquals(5, $newDie1->dice[0]->max);
        $this->assertEquals(13, $newDie1->dice[1]->max);
        $this->assertFalse($newDie1->has_skill('Doppelganger'));
        $this->assertFalse($newDie1->has_skill('Trip'));
        $this->assertTrue($newDie1->has_skill('Konstant'));

        // twin doppelganger die takes a normal die
        $att2 = BMDieTwin::create(array(7,4));
        $att2->add_skill('Doppelganger');
        $att2->add_skill('Trip');
        $def2 = BMDie::create(15);
        $def2->add_skill('Konstant');
        $def2->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($att2), array($def2));
        $game->attack = array(0, 1, array(0), array(0), 'Power');

        $att2->ownerObject = $game;
        $att2->playerIdx = 0;
        $def2->ownerObject = $game;

        $parArray = array('type' => 'Power',
                          'attackers' => array($att2),
                          'defenders' => array($def2),
                          'caller' => $att2);
        BMSkillDoppelganger::capture($parArray);

        $newDie2 = $parArray['attackers'][0];

        $this->assertNotInstanceOf('BMDieTwin', $newDie2);
        $this->assertEquals(1, $newDie2->min);
        $this->assertEquals(15, $newDie2->max);
        $this->assertFalse($newDie2->has_skill('Doppelganger'));
        $this->assertFalse($newDie2->has_skill('Trip'));
        $this->assertTrue($newDie2->has_skill('Konstant'));
    }

    /**
     * @covers BMSkillDoppelganger::capture
     */
    public function testCapture_swing() {
        // normal doppelganger die capturing swing die
        $att1 = BMDie::create(6);
        $att1->add_skill('Doppelganger');
        $att1->add_skill('Trip');
        $def1 = BMDie::create_from_recipe('(X)');
        $def1->add_skill('Konstant');
        $def1->set_swingValue(array('X' => 5));
        $def1->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($att1), array($def1));
        $game->attack = array(0, 1, array(0), array(0), 'Power');

        $att1->ownerObject = $game;
        $att1->playerIdx = 0;
        $def1->ownerObject = $game;

        $parArray = array('type' => 'Power',
                          'attackers' => array($att1),
                          'defenders' => array($def1),
                          'caller' => $att1);
        BMSkillDoppelganger::capture($parArray);

        $newDie1 = $parArray['attackers'][0];

        $this->assertEquals(5, $newDie1->max);
        $this->assertInstanceOf('BMDieSwing', $newDie1);
        $this->assertEquals('X', $newDie1->swingType);
        $this->assertFalse($newDie1->has_skill('Doppelganger'));
        $this->assertFalse($newDie1->has_skill('Trip'));
        $this->assertTrue($newDie1->has_skill('Konstant'));

        // doppelganger swing die capturing normal die
        $att2 = BMDie::create_from_recipe('(X)');
        $att2->add_skill('Doppelganger');
        $att2->add_skill('Trip');
        $att2->set_swingValue(array('X' => 7));
        $def2 = BMDie::create(11);
        $def2->add_skill('Konstant');
        $def2->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($att2), array($def2));
        $game->attack = array(0, 1, array(0), array(0), 'Power');

        $att2->ownerObject = $game;
        $att2->playerIdx = 0;
        $def2->ownerObject = $game;

        $parArray = array('type' => 'Power',
                          'attackers' => array($att2),
                          'defenders' => array($def2),
                          'caller' => $att2);
        BMSkillDoppelganger::capture($parArray);

        $newDie2 = $parArray['attackers'][0];

        $this->assertEquals(11, $newDie2->max);
        $this->assertNotInstanceOf('BMDieSwing', $newDie2);
        $this->assertFalse($newDie2->has_skill('Morphing'));
        $this->assertFalse($newDie2->has_skill('Trip'));
        $this->assertTrue($newDie2->has_skill('Konstant'));

        // normal doppelganger die capturing a twin die with swing subdice
        $att3 = BMDie::create(6);
        $att3->add_skill('Doppelganger');
        $att3->add_skill('Trip');
        $def3 = BMDie::create_from_recipe('(X,X)');
        $def3->add_skill('Konstant');
        $def3->set_swingValue(array('X' => 5));
        $def3->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($att3), array($def3));
        $game->attack = array(0, 1, array(0), array(0), 'Power');

        $att3->ownerObject = $game;
        $att3->playerIdx = 0;
        $def3->ownerObject = $game;

        $parArray = array('type' => 'Power',
                          'attackers' => array($att3),
                          'defenders' => array($def3),
                          'caller' => $att3);
        BMSkillDoppelganger::capture($parArray);

        $newDie3 = $parArray['attackers'][0];

        $this->assertEquals(10, $newDie3->max);
        $this->assertInstanceOf('BMDieTwin', $newDie3);
        $this->assertInstanceOf('BMDieSwing', $newDie3->dice[0]);
        $this->assertEquals('X', $newDie3->dice[0]->swingType);
        $this->assertInstanceOf('BMDieSwing', $newDie3->dice[1]);
        $this->assertEquals('X', $newDie3->dice[1]->swingType);
        $this->assertFalse($newDie3->has_skill('Morphing'));
        $this->assertFalse($newDie3->has_skill('Trip'));
        $this->assertTrue($newDie3->has_skill('Konstant'));
    }
}
