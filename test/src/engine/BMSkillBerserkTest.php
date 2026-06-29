<?php

class BMSkillBerserkTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillBerserk
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillBerserk;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillBerserk::attack_list
     */
    public function testAttack_list()
    {
        $a = array();
        $b = array('attackTypeArray' => &$a);

        $this->object->attack_list($b);
        // Test adding Berserk
        $this->assertNotEmpty($a);
        $this->assertContains('Berserk', $a);
        // Only once
        $this->assertEquals(1, count($a));

        // Test adding Berserk to a non-empty array
        $a = array('Power' => 'Power', 'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertEquals(2, count($a));
        $this->assertContains('Berserk', $a);
        // Confirm other contents intact
        $this->assertContains('Power', $a);
        $this->assertNotContains('Skill', $a);

        // Check adding Berserk to an array already containing Berserk
        $a = array('Power' => 'Power', 'Berserk' => 'Berserk');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertContains('Berserk', $a);
        $this->assertEquals(2, count($a));
    }

    /**
     * @covers BMSkillBerserk::capture
     */
    public function testCapture_invalid_args() {
        $att = BMDie::create(17);
        $att->add_skill('Berserk');

        // test invalid arguments fail gracefully
        $args = $att;
        BMSkillBerserk::capture($args);

        $args = array($att);
        BMSkillBerserk::capture($args);

        $args = array('type' => 'Power');
        BMSkillBerserk::capture($args);

        $args = array('type' => 'Berserk');
        BMSkillBerserk::capture($args);

        try {
            $args = array(
                'type' => 'Berserk',
                'attackers' => array($att, $att)
            );
            BMSkillBerserk::capture($args);
            $this->fail('Berserk attacks should fail with more than one attacker.');
        } catch (LogicException $e) {
            // expected failure
        }
    }

    /**
     * @covers BMSkillBerserk::capture
     */
    public function testCapture_valid_args() {
        $att = BMDie::create(17);
        $att->add_skill('Berserk');
        $att->value = 9;
        $def = BMDie::create(8);

        $game = new BMGame;
        $game->activeDieArrayArray = array(array($att), array($def));
        $game->attack = array(0, 1, array(0), array(0), 'Berserk');
        $att->ownerObject = $game;
        $att->playerIdx = 0;
        $def->ownerObject = $game;
        $parArray = array('type' => 'Berserk',
                          'attackers' => array($att),
                          'defenders' => array($def),
                          'caller' => $att);
        BMSkillBerserk::capture($parArray);

        $newDie = $game->activeDieArrayArray[0][0];
        $this->assertTrue($newDie === $att);

        $this->assertEquals(9, $newDie->max);
        $this->assertTrue(is_null($newDie->value));
        $this->assertFalse($newDie->has_skill('Berserk'));
        $this->assertTrue($att === $newDie);
        $this->assertTrue($newDie->has_flag('JustPerformedBerserkAttack'));

        $this->assertTrue($newDie === $parArray['attackers'][0]);
    }
}

