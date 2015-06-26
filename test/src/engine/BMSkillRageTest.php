<?php

class BMSkillRageTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillRage
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillRage;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
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
     * @covers BMSkillRage::be_captured
     */
    public function testBe_captured() {
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
        $this->object->be_captured($args);
        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        $this->assertEquals(99, $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[1][1]->max);
        $this->assertTrue($game->activeDieArrayArray[1][1]->captured);
        $this->assertEquals(2, $game->activeDieArrayArray[1][1]->value);
        $this->assertEquals(8, $game->activeDieArrayArray[1][2]->max);
        $this->assertFalse($game->activeDieArrayArray[1][2]->captured);
        $this->assertNotNull($game->activeDieArrayArray[1][2]->value);
        $this->assertEquals(99, $game->activeDieArrayArray[1][3]->max);
    }
}
