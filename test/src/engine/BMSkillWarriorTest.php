<?php

class BMSkillWarriorTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillWarrior
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillWarrior;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillWarrior::initiative_value
     */
    public function testInitiative_value()
    {
        // check that an invalid input fails gracefully
        $a = 15;
        $this->object->initiative_value($a);

        $a = 15;
        $b = array('initiativeValue' => &$a);
        $this->object->initiative_value($b);
        $this->assertEquals(-1, $a);
    }

    /**
     * @covers BMSkillWarrior::attack_list
     */
    public function testAttack_list_invalid() {
        // check that an illegal input is handled gracefully
        $this->object->attack_list(NULL);
    }

    /**
     * @covers BMSkillWarrior::attack_list
     */
    public function testAttack_list_one_attacker() {
        $a = array('Power'   => 'Power',
                   'Skill'   => 'Skill',
                   'Speed'   => 'Speed',
                   'Berserk' => 'Berserk');
        $b = array('attackTypeArray' => &$a,
                   'nAttDice' => 1);
        $this->object->attack_list($b);
        $this->assertEmpty($a);
    }

    /**
     * @covers BMSkillWarrior::attack_list
     */
    public function testAttack_list_two_attackers() {
        $a = array('Power' => 'Power',
                   'Skill' => 'Skill',
                   'Speed' => 'Speed',
                   'Berserk' => 'Berserk');
        $b = array('attackTypeArray' => &$a,
                   'nAttDice' => 2);
        $this->object->attack_list($b);
        $this->assertContains('Skill', $a);
        // Only once
        $this->assertEquals(1, count($a));
    }

    /**
     * @covers BMSkillWarrior::attack_values
     */
    public function testAttack_values() {
        $attackValues = array(-3, -2, -1, 1, 2, 3);

        $args = array('attackType' => 'Skill',
                      'attackValues' => &$attackValues,
                      'minValue' => 1,
                      'value' => 6);

        $this->object->attack_values($args);
        $this->assertEquals(array(6), $args['attackValues']);
    }

    /**
     * @covers BMSkillWarrior::capture
     */
    public function testCapture_invalid_args() {
        $att = BMDie::create(17);
        $att->add_skill('Warrior');

        // test invalid arguments fail gracefully
        $args = $att;
        BMSkillWarrior::capture($args);

        $args = array($att);
        BMSkillWarrior::capture($args);

        $args = array('type' => 'Power');
        BMSkillWarrior::capture($args);

        $args = array('type' => 'Skill');
        BMSkillWarrior::capture($args);

        try {
            $args = array(
                'type' => 'Skill',
                'attackers' => array($att)
            );
            BMSkillWarrior::capture($args);
            $this->fail('Skill attacks should fail if the sole attacker is warrior.');
        } catch (LogicException $e) {
            // expected failure
        }
    }

    /**
     * @covers BMSkillWarrior::capture
     */
    public function testCapture_valid_args() {
        $att1 = BMDie::create(17);
        $att1->add_skill('Warrior');
        $att1->value = 3;

        $att2 = BMDie::create(6);
        $att2->value = 4;

        $def = BMDie::create(8);
        $def->value = 7;

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($att1, $att2), array($def));
        $game->attack = array(0, 1, array(0, 1), array(0), 'Skill');
        $att1->ownerObject = $game;
        $att1->playerIdx = 0;
        $att2->ownerObject = $game;
        $att2->playerIdx = 0;
        $def->ownerObject = $game;
        $parArray = array('type' => 'Skill',
                          'attackers' => array($att1, $att2),
                          'defenders' => array($def),
                          'caller' => $att1);
        BMSkillWarrior::capture($parArray);

        $this->assertFalse($att1->has_skill('Warrior'));
    }

    /**
     * @covers BMSkillWarrior::post_roll
     */
    public function testPost_roll_bad_args() {
        $args = NULL;
        $this->assertFalse(BMSkillWarrior::post_roll($args));
    }

    /**
     * @covers BMSkillWarrior::post_roll
     */
    public function testPost_roll() {
        $die = BMDie::create(6);
        $args = array('die' => $die);
        $this->assertTrue(BMSkillWarrior::post_roll($args));
        $this->assertEquals(6, $die->value);
    }

    /**
     * @covers BMSkillWarrior::post_roll
     */
    public function testPost_roll_Wildcard() {
        // we need a whole BMGame because the information about the deck
        // is held at the BMPlayer level
        $game = new BMGame;
        $die = BMDie::create_from_recipe('`(C)');
        $die->playerIdx = 1;
        $die->ownerObject = $game;
        $args = array('die' => $die);
        $this->assertTrue(BMSkillWarrior::post_roll($args));
        $this->assertEquals(20, $die->value);
    }

    /**
     * @covers BMSkillWarrior::score_value
     */
    public function testScore_value() {
        $die = BMDie::create(4);
        $die->add_skill('Warrior');
        $this->assertEquals(array('BMSkillWarrior'), $die->hookList['score_value']);

        $die->captured = FALSE;
        $this->assertEquals(0, $die->get_scoreValueTimesTen());

        $die->captured = TRUE;
        $this->assertEquals(0, $die->get_scoreValueTimesTen());
    }

    /**
     * @covers BMSkillWarrior::react_to_initiative
     */
    public function testReact_to_initiative() {
        $die = BMDie::create(8);
        $die->add_skill('Warrior');
        $this->assertEquals(array('BMSkillWarrior'), $die->hookList['react_to_initiative']);

        $args = array('activeDieArrayArray' => array(array($die), array(BMDie::create(5))),
                      'playerIdx' => 0);

        $this->assertEquals('forceFalse', $this->object->react_to_initiative($args));
    }
}
