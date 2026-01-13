<?php

class BMSkillRadioactiveTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillRadioactive
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillRadioactive;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillRadioactive::capture
     * @covers BMSkillRadioactive::radioactive_split
     */
    public function testCapture_invalid_args() {
        $att = BMDie::create(17);
        $att->add_skill('Radioactive');
        $def = BMDie::create(8);

        // test invalid arguments fail gracefully
        $args = $att;
        BMSkillRadioactive::capture($args);

        $args = array('attackers' => array($att));
        BMSkillRadioactive::capture($args);

        $args = array('defenders' => array($def));
        BMSkillRadioactive::capture($args);

        $args = array('attackers' => array($att, $att),
                      'defenders' => array($def));
        BMSkillRadioactive::capture($args);

        $args = array('attackers' => array($att),
                      'defenders' => array($def, $def));
        BMSkillRadioactive::capture($args);
    }

    /**
     * @covers BMSkillRadioactive::capture
     * @covers BMSkillRadioactive::radioactive_split
     */
    public function testCapture_valid_args() {
        $dieLeft = BMDie::create(6);
        $dieRight = BMDie::create(30);

        $att = BMDie::create(17);
        $att->add_skill('Radioactive');
        $att->value = 9;
        $def = BMDie::create(8);
        $def->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($dieLeft, $att, $dieRight), array($def));
        $game->attack = array(0, 1, array(1), array(0), 'Power');
        $att->ownerObject = $game;
        $att->playerIdx = 0;
        $def->ownerObject = $game;
        $parArray = array('type' => 'Power',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        BMSkillRadioactive::capture($parArray);

        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertTrue($dieLeft === $game->activeDieArrayArray[0][0]);
        $this->assertTrue($att === $game->activeDieArrayArray[0][1]);
        $this->assertTrue($dieRight === $game->activeDieArrayArray[0][3]);

        $this->assertEquals(6, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(9, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(30, $game->activeDieArrayArray[0][3]->max);

        $this->assertFalse($game->activeDieArrayArray[0][1]->has_skill('Radioactive'));
        $this->assertFalse($game->activeDieArrayArray[0][2]->has_skill('Radioactive'));

        $this->assertCount(2, $parArray['attackers']);
        $this->assertTrue($game->activeDieArrayArray[0][1] === $parArray['attackers'][0]);
        $this->assertTrue($game->activeDieArrayArray[0][2] === $parArray['attackers'][1]);
    }

    /**
     * @covers BMSkillRadioactive::capture
     * @covers BMSkillRadioactive::radioactive_split
     */
    public function testCapture_mood_swing() {
        $dieLeft = BMDie::create(6);
        $dieRight = BMDie::create(30);

        $att = BMDieSwing::create('X');
        $att->add_skill('Radioactive');
        $att->add_skill('Mood');
        $att->set_swingValue(array('X' => 17));
        $att->value = 9;
        $def = BMDie::create(8);
        $def->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($dieLeft, $att, $dieRight), array($def));
        $game->attack = array(0, 1, array(1), array(0), 'Power');
        $att->ownerObject = $game;
        $att->playerIdx = 0;
        $def->ownerObject = $game;
        $parArray = array('type' => 'Power',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        BMSkillRadioactive::capture($parArray);

        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertTrue($dieLeft === $game->activeDieArrayArray[0][0]);
        $this->assertTrue($att === $game->activeDieArrayArray[0][1]);
        $this->assertTrue($dieRight === $game->activeDieArrayArray[0][3]);

        $this->assertEquals(6, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(9, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(30, $game->activeDieArrayArray[0][3]->max);

        $this->assertFalse($game->activeDieArrayArray[0][1]->has_skill('Radioactive'));
        $this->assertFalse($game->activeDieArrayArray[0][2]->has_skill('Radioactive'));

        $this->assertTrue($game->activeDieArrayArray[0][1] instanceof BMDie);
        $this->assertTrue($game->activeDieArrayArray[0][2] instanceof BMDie);
        $this->assertTrue($game->activeDieArrayArray[0][1] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[0][2] instanceof BMDieSwing);

        $this->assertFalse($game->activeDieArrayArray[0][1]->has_skill('Mood'));
        $this->assertFalse($game->activeDieArrayArray[0][2]->has_skill('Mood'));

        $this->assertCount(2, $parArray['attackers']);
        $this->assertTrue($game->activeDieArrayArray[0][1] === $parArray['attackers'][0]);
        $this->assertTrue($game->activeDieArrayArray[0][2] === $parArray['attackers'][1]);
    }

    /**
     * @covers BMSkillRadioactive::be_captured
     * @covers BMSkillRadioactive::radioactive_split
     */
    public function testBe_captured_invalid_args() {
        $att = BMDie::create(17);

        $def = BMDie::create(8);
        $def->add_skill('Radioactive');

        // test invalid arguments fail gracefully
        $args = $att;
        BMSkillRadioactive::be_captured($args);

        $args = array('attackers' => array($att));
        BMSkillRadioactive::be_captured($args);

        $args = array('defenders' => array($def));
        BMSkillRadioactive::be_captured($args);

        $args = array('attackers' => array($att, $att),
                      'defenders' => array($def));
        BMSkillRadioactive::be_captured($args);

        $args = array('attackers' => array($att),
                      'defenders' => array($def, $def));
        BMSkillRadioactive::be_captured($args);
    }

    /**
     * @covers BMSkillRadioactive::be_captured
     * @covers BMSkillRadioactive::radioactive_split
     */
    public function testBe_captured_valid_args() {
        $dieLeft = BMDie::create(6);
        $dieRight = BMDie::create(30);

        $att = BMDie::create(17);
        $att->value = 9;

        $def = BMDie::create(8);
        $def->add_skill('Radioactive');
        $def->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($dieLeft, $att, $dieRight), array($def));
        $game->attack = array(0, 1, array(1), array(0), 'Power');
        $att->ownerObject = $game;
        $att->playerIdx = 0;
        $def->ownerObject = $game;
        $parArray = array('type' => 'Power',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        BMSkillRadioactive::be_captured($parArray);

        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertTrue($dieLeft === $game->activeDieArrayArray[0][0]);
        $this->assertTrue($att === $game->activeDieArrayArray[0][1]);
        $this->assertTrue($dieRight === $game->activeDieArrayArray[0][3]);

        $this->assertEquals(6, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(9, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(30, $game->activeDieArrayArray[0][3]->max);

        $this->assertFalse($game->activeDieArrayArray[0][1]->has_skill('Radioactive'));
        $this->assertFalse($game->activeDieArrayArray[0][2]->has_skill('Radioactive'));

        $this->assertCount(2, $parArray['attackers']);
        $this->assertTrue($game->activeDieArrayArray[0][1] === $parArray['attackers'][0]);
        $this->assertTrue($game->activeDieArrayArray[0][2] === $parArray['attackers'][1]);
    }

    /**
     * @covers BMSkillRadioactive::be_captured
     * @covers BMSkillRadioactive::radioactive_split
     */
    public function testBe_captured_mood_swing() {
        $dieLeft = BMDie::create(6);
        $dieRight = BMDie::create(30);

        $att = BMDieSwing::create('X');
        $att->add_skill('Mood');
        $att->set_swingValue(array('X' => 17));
        $att->value = 9;

        $def = BMDie::create(8);
        $def->add_skill('Radioactive');
        $def->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($dieLeft, $att, $dieRight), array($def));
        $game->attack = array(0, 1, array(1), array(0), 'Power');
        $att->ownerObject = $game;
        $att->playerIdx = 0;
        $def->ownerObject = $game;
        $parArray = array('type' => 'Power',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        BMSkillRadioactive::be_captured($parArray);

        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertTrue($dieLeft === $game->activeDieArrayArray[0][0]);
        $this->assertTrue($att === $game->activeDieArrayArray[0][1]);
        $this->assertTrue($dieRight === $game->activeDieArrayArray[0][3]);

        $this->assertEquals(6, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(9, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(30, $game->activeDieArrayArray[0][3]->max);

        $this->assertFalse($game->activeDieArrayArray[0][1]->has_skill('Radioactive'));
        $this->assertFalse($game->activeDieArrayArray[0][2]->has_skill('Radioactive'));

        $this->assertTrue($game->activeDieArrayArray[0][1] instanceof BMDie);
        $this->assertTrue($game->activeDieArrayArray[0][2] instanceof BMDie);
        $this->assertTrue($game->activeDieArrayArray[0][1] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[0][2] instanceof BMDieSwing);

        $this->assertFalse($game->activeDieArrayArray[0][1]->has_skill('Mood'));
        $this->assertFalse($game->activeDieArrayArray[0][2]->has_skill('Mood'));

        $this->assertCount(2, $parArray['attackers']);
        $this->assertTrue($game->activeDieArrayArray[0][1] === $parArray['attackers'][0]);
        $this->assertTrue($game->activeDieArrayArray[0][2] === $parArray['attackers'][1]);
    }

    /**
     * @covers BMSkillRadioactive::capture
     * @covers BMSkillRadioactive::be_captured
     * @covers BMSkillRadioactive::radioactive_split
     */
    public function testRadioactive_captures_radioactive() {
        $dieLeft = BMDie::create(6);
        $dieRight = BMDie::create(30);

        $att = BMDie::create(17);
        $att->add_skill('Radioactive');
        $att->value = 9;

        $def = BMDie::create(8);
        $def->add_skill('Radioactive');
        $def->captured = TRUE;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($dieLeft, $att, $dieRight), array($def));
        $game->attack = array(0, 1, array(1), array(0), 'Power');
        $att->ownerObject = $game;
        $att->playerIdx = 0;
        $def->ownerObject = $game;
        $parArray = array('type' => 'Power',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        BMSkillRadioactive::capture($parArray);
        BMSkillRadioactive::be_captured($parArray);

        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertTrue($dieLeft === $game->activeDieArrayArray[0][0]);
        $this->assertTrue($att === $game->activeDieArrayArray[0][1]);
        $this->assertTrue($dieRight === $game->activeDieArrayArray[0][3]);

        $this->assertEquals(6, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(9, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(30, $game->activeDieArrayArray[0][3]->max);

        $this->assertTrue(is_null($game->activeDieArrayArray[0][1]->value));
        $this->assertTrue(is_null($game->activeDieArrayArray[0][2]->value));

        $this->assertFalse($game->activeDieArrayArray[0][1]->has_skill('Radioactive'));
        $this->assertFalse($game->activeDieArrayArray[0][2]->has_skill('Radioactive'));

        $this->assertCount(2, $parArray['attackers']);
        $this->assertTrue($game->activeDieArrayArray[0][1] === $parArray['attackers'][0]);
        $this->assertTrue($game->activeDieArrayArray[0][2] === $parArray['attackers'][1]);
    }
}

