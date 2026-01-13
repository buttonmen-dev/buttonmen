<?php

class BMSkillRageTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillRage
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillRage;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillRage::initiative_value
     */
    public function testInitiative_value() {
        $a = 15;
        $b = array('initiativeValue' => &$a);
        $this->object->initiative_value($b);
        $this->assertEquals(-1, $a);
    }

    /**
     * @covers BMSkillRage::capture
     */
    public function testCapture() {
        $att1 = BMDie::create(6);
        $att1->add_skill('Rage');
        $att1->value = 5;

        $att2 = BMDie::create(4);
        $att2->value = 2;

        $att3 = BMDie::create(2);
        $att3->add_skill('Rage');
        $att3->value = 1;

        $def = BMDie::create(10);
        $def->value = 8;

        $attackers = array($att1, $att2, $att3);
        $defenders = array($def);

        $args = array('type' => 'Skill',
                      'attackers' => &$attackers,
                      'defenders' => &$defenders,
                      'caller' => $att3);

        $this->assertTrue($att1->has_skill('Rage'));
        $this->assertFalse($att2->has_skill('Rage'));
        $this->assertTrue($att3->has_skill('Rage'));
        $this->object->capture($args);
        $this->assertTrue($att1->has_skill('Rage'));
        $this->assertFalse($att2->has_skill('Rage'));
        $this->assertFalse($att3->has_skill('Rage'));
    }

    /**
     * @covers BMSkillRage::pre_be_captured
     */
    public function testPre_be_captured() {
        $game = new BMGame;

        $dummy = BMDie::create(99);
        $dummy->value = 5;
        $dummy->ownerObject = $game;

        $dummy1 = clone $dummy;
        $dummy2 = clone $dummy;
        $dummy3 = clone $dummy;

        $att = BMDie::create(6);
        $att->value = 5;
        $att->ownerObject = $game;
        $att->playerIdx = 0;

        $def = BMDie::create(8);
        $def->add_skill('Rage');
        $def->value = 2;
        $def->ownerObject = $game;
        $def->playerIdx = 1;
        $def->captured = TRUE;
        $def->add_flag('WasJustCaptured');

        $this->assertInstanceOf('BMDie', $dummy1);
        $this->assertInstanceOf('BMDie', $dummy2);
        $this->assertInstanceOf('BMDie', $dummy3);
        $this->assertInstanceOf('BMDie', $att);
        $this->assertInstanceOf('BMDie', $def);

        $game->activeDieArrayArray = array(array($dummy1, $att), array($dummy2, $def, $dummy3));

        $attackers = array($att);
        $defenders = array($def);

        $args = array('type' => 'Skill',
                      'attackers' => &$attackers,
                      'defenders' => &$defenders,
                      'caller' => $def);

        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(3, $game->activeDieArrayArray[1]);
        $this->assertEquals(99, $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals(99, $game->activeDieArrayArray[1][2]->max);
        $this->assertTrue($def->has_skill('Rage'));
        $this->object->pre_be_captured($args);
        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        $this->assertEquals(99, $game->activeDieArrayArray[1][0]->max);

        // original captured die
        $this->assertEquals(8, $game->activeDieArrayArray[1][1]->max);
        $this->assertTrue($game->activeDieArrayArray[1][1]->captured);
        $this->assertEquals(2, $game->activeDieArrayArray[1][1]->value);
        $this->assertFalse($game->activeDieArrayArray[1][1]->has_flag('IsRageTargetReplacement'));
        $this->assertTrue($game->activeDieArrayArray[1][1]->has_flag('WasJustCaptured'));

        // rage replacement die
        $this->assertEquals(8, $game->activeDieArrayArray[1][2]->max);
        $this->assertFalse($game->activeDieArrayArray[1][2]->captured);
        $this->assertTrue(is_null($game->activeDieArrayArray[1][2]->value));
        $this->assertTrue($game->activeDieArrayArray[1][2]->has_flag('IsRageTargetReplacement'));
        $this->assertFalse($game->activeDieArrayArray[1][2]->has_flag('WasJustCaptured'));

        $this->assertEquals(99, $game->activeDieArrayArray[1][3]->max);

        $this->assertCount(2, $defenders);
        $this->assertEquals(8, $defenders[0]->max);
        $this->assertTrue($defenders[0]->captured);
        $this->assertEquals(2, $defenders[0]->value);
        $this->assertFalse($defenders[0]->has_flag('IsRageTargetReplacement'));
        $this->assertTrue($defenders[0]->has_flag('WasJustCaptured'));
        $this->assertEquals(8, $defenders[1]->max);
        $this->assertFalse($defenders[1]->captured);
        $this->assertFalse(isset($defenders[1]->value));
        $this->assertTrue($defenders[1]->has_flag('IsRageTargetReplacement'));
        $this->assertFalse($defenders[1]->has_flag('WasJustCaptured'));
    }

    /**
     * @covers BMSkillRage::pre_be_captured
     */
    public function testPre_be_captured_multiple() {
        $game = new BMGame;

        $dummy = BMDie::create(99);
        $dummy->value = 5;
        $dummy->ownerObject = $game;

        $dummy1 = clone $dummy;
        $dummy2 = clone $dummy;
        $dummy3 = clone $dummy;

        $att = BMDie::create(6);
        $att->add_skill('Speed');
        $att->value = 5;
        $att->ownerObject = $game;
        $att->playerIdx = 0;

        $def1 = BMDie::create(8);
        $def1->add_skill('Rage');
        $def1->value = 2;
        $def1->ownerObject = $game;
        $def1->playerIdx = 1;
        $def1->captured = TRUE;
        $def1->add_flag('WasJustCaptured');

        $def2 = BMDie::create(10);
        $def2->add_skill('Rage');
        $def2->value = 3;
        $def2->ownerObject = $game;
        $def2->playerIdx = 1;
        $def2->captured = TRUE;
        $def2->add_flag('WasJustCaptured');

        $this->assertInstanceOf('BMDie', $dummy1);
        $this->assertInstanceOf('BMDie', $dummy2);
        $this->assertInstanceOf('BMDie', $dummy3);
        $this->assertInstanceOf('BMDie', $att);
        $this->assertInstanceOf('BMDie', $def1);
        $this->assertInstanceOf('BMDie', $def2);

        $game->activeDieArrayArray = array(array($dummy1, $att), array($dummy2, $def1, $def2, $dummy3));

        $attackers = array($att);
        $defenders = array($def1, $def2);

        $args = array('type' => 'Speed',
                      'attackers' => &$attackers,
                      'defenders' => &$defenders,
                      'caller' => $def1);

        // apply BMSkillRage->be_captured() to the actual target die
        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        $this->assertEquals(99, $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals(10, $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals(99, $game->activeDieArrayArray[1][3]->max);
        $this->assertTrue($def1->has_skill('Rage'));
        $this->object->pre_be_captured($args);
        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
        $this->assertEquals(99, $game->activeDieArrayArray[1][0]->max);

        // original captured die
        $this->assertEquals(8, $game->activeDieArrayArray[1][1]->max);
        $this->assertTrue($game->activeDieArrayArray[1][1]->captured);
        $this->assertEquals(2, $game->activeDieArrayArray[1][1]->value);
        $this->assertTrue($game->activeDieArrayArray[1][1]->has_skill('Rage'));
        $this->assertFalse($game->activeDieArrayArray[1][1]->has_flag('IsRageTargetReplacement'));
        $this->assertTrue($game->activeDieArrayArray[1][1]->has_flag('WasJustCaptured'));

        // rage replacement die
        $this->assertEquals(8, $game->activeDieArrayArray[1][2]->max);
        $this->assertFalse($game->activeDieArrayArray[1][2]->captured);
        $this->assertTrue(is_null($game->activeDieArrayArray[1][2]->value));
        $this->assertFalse($game->activeDieArrayArray[1][2]->has_skill('Rage'));
        $this->assertTrue($game->activeDieArrayArray[1][2]->has_flag('IsRageTargetReplacement'));
        $this->assertFalse($game->activeDieArrayArray[1][2]->has_flag('WasJustCaptured'));

        $this->assertEquals(10, $game->activeDieArrayArray[1][3]->max);
        $this->assertTrue($game->activeDieArrayArray[1][3]->has_skill('Rage'));

        $this->assertEquals(99, $game->activeDieArrayArray[1][4]->max);

        $this->assertCount(3, $defenders);

        // initial $def1
        $this->assertEquals(8, $defenders[0]->max);
        $this->assertTrue($defenders[0]->captured);
        $this->assertEquals(2, $defenders[0]->value);
        $this->assertTrue($defenders[0]->has_skill('Rage'));
        $this->assertFalse($defenders[0]->has_flag('IsRageTargetReplacement'));
        $this->assertTrue($defenders[0]->has_flag('WasJustCaptured'));

        // replacement for $def1
        $this->assertEquals(8, $defenders[1]->max);
        $this->assertFalse($defenders[1]->captured);
        $this->assertFalse(isset($defenders[1]->value));
        $this->assertFalse($defenders[1]->has_skill('Rage'));
        $this->assertTrue($defenders[1]->has_flag('IsRageTargetReplacement'));
        $this->assertFalse($defenders[1]->has_flag('WasJustCaptured'));

        // initial $def2
        $this->assertEquals(10, $defenders[2]->max);
        $this->assertTrue($defenders[2]->captured);
        $this->assertEquals(3, $defenders[2]->value);
        $this->assertTrue($defenders[2]->has_skill('Rage'));
        $this->assertTrue($defenders[2]->has_skill('Rage'));
        $this->assertFalse($defenders[2]->has_flag('IsRageTargetReplacement'));
        $this->assertTrue($defenders[2]->has_flag('WasJustCaptured'));

        // now check that BMSkillRage->be_captured() doesn't affect the replacement die
        $rep1 = $defenders[1];

        $args = array('type' => 'Speed',
                      'attackers' => &$attackers,
                      'defenders' => &$defenders,
                      'caller' => $rep1);

        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
        $this->assertEquals(99, $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals(10, $game->activeDieArrayArray[1][3]->max);
        $this->assertEquals(99, $game->activeDieArrayArray[1][4]->max);
        $this->assertFalse($rep1->has_skill('Rage'));
        $this->object->pre_be_captured($args);
        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
    }

    /**
     * @covers BMSkillRage::pre_be_captured
     */
    public function testPre_be_captured_trip_boom_interaction() {
        $game = new BMGame;

        $att = BMDie::create(6);
        $att->add_skill('Trip');
        $att->add_skill('Boom');
        $att->value = 5;
        $att->ownerObject = $game;
        $att->playerIdx = 0;

        $def = BMDie::create(8);
        $def->add_skill('Rage');
        $def->value = 2;
        $def->ownerObject = $game;
        $def->playerIdx = 1;
        $def->captured = FALSE;

        $this->assertInstanceOf('BMDie', $att);
        $this->assertInstanceOf('BMDie', $def);

        $game->activeDieArrayArray = array(array($att), array($def));

        $attackers = array($att);
        $defenders = array($def);

        // james: note that this trip attack has been unsuccessful because
        // $def->captured is FALSE (which is set in BMDie->capture())
        $args = array('type' => 'Trip',
                      'attackers' => &$attackers,
                      'defenders' => &$defenders,
                      'caller' => $def);

        $this->assertCount(1, $game->activeDieArrayArray[0]);
        $this->assertCount(1, $game->activeDieArrayArray[1]);
        $this->assertTrue($def->has_skill('Rage'));
        $this->object->pre_be_captured($args);
        $this->assertCount(1, $game->activeDieArrayArray[0]);
        $this->assertCount(1, $game->activeDieArrayArray[1]);
        $this->assertTrue($def->has_skill('Rage'));

        $args = array('type' => 'Boom',
                      'attackers' => &$attackers,
                      'defenders' => &$defenders,
                      'caller' => $def);

        $this->assertCount(1, $game->activeDieArrayArray[0]);
        $this->assertCount(1, $game->activeDieArrayArray[1]);
        $this->assertTrue($def->has_skill('Rage'));
        $this->object->pre_be_captured($args);
        $this->assertCount(1, $game->activeDieArrayArray[0]);
        $this->assertCount(1, $game->activeDieArrayArray[1]);
        $this->assertTrue($def->has_skill('Rage'));
    }
}
