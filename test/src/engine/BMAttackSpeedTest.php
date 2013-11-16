<?php

class BMAttackSpeedTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMAttackSpeed
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = BMAttackSpeed::get_instance();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMAttackSpeed::validate_attack
     */
    public function testValidate_attack()
    {
        $game = new TestDummyGame;

        $sk = $this->object;

        $die1 = BMDie::create(6);
        $die1->add_skill('Speed');
        $die1->value = 6;

        $game->attackerAllDieArray[] = $die1;

        // Basic error testing
        $this->assertFalse($sk->validate_attack($game, array(), array()));
        $this->assertFalse($sk->validate_attack($game, array($die1), array()));
        $this->assertFalse($sk->validate_attack($game, array(), array($die1)));

        // Successful attacks
        $target1 = BMDie::create(20);
        $target2 = BMDie::create(20);
        $target3 = BMDie::create(20);

        $target1->value = 6;
        $this->assertTrue($sk->validate_attack($game,
                                               array($die1),
                                               array($target1)));

        $target1->value = 2;
        $target2->value = 4;
        $this->assertTrue($sk->validate_attack($game,
                                               array($die1),
                                               array($target1, $target2)));

        $target1->value = 2;
        $target2->value = 3;
        $target3->value = 1;
        $this->assertTrue($sk->validate_attack($game,
                                               array($die1),
                                               array($target1, $target2, $target3)));

        // Failures

        // Can't take subsets
        $target1->value = 3;
        $target2->value = 3;
        $target3->value = 3;
        $this->assertFalse($sk->validate_attack($game,
                                                array($die1),
                                                array($target1, $target2, $target3)));

        $target1->value = 5;
        $this->assertFalse($sk->validate_attack($game, array($die1), array($target1)));

        $target1->value = 7;
        $this->assertFalse($sk->validate_attack($game, array($die1), array($target1)));

        $target1->value = 3;
        $target2->value = 4;
        $this->assertFalse($sk->validate_attack($game,
                                                array($die1),
                                                array($target1, $target2)));

        $target1->value = 3;
        $target2->value = 2;
        $this->assertFalse($sk->validate_attack($game,
                                                array($die1),
                                                array($target1, $target2)));
    }

    /**
     * @covers BMAttackSpeed::find_attack
     * @depends testValidate_attack
     * @todo   Implement testFind_attack().
     */
    public function testFind_attack()
    {
//        $game = new TestDummyGame;
//
//        $sk = $this->object;
//
//        // we find nothing when there are no attackers
//        $this->assertFalse($sk->find_attack($game));
//
//        // Load some dice into the attack.
//        $die1 = BMDie::create(6);
//        $die1->value = 6;
//
//        $die2 = BMDie::create(6);
//        $die2->value = 2;
//
//        $die3 = BMDie::create(6);
//        $die3->value = 4;
//
//        $die4 = BMDie::create(6);
//        $die4->value = 5;
//
//        $sk->reset();
//
//        $sk->add_die($die1);
//        $sk->add_die($die2);
//
//
//        // we find nothing when there are no defenders
//        $this->assertFalse($sk->find_attack($game));
//
//        // Basic attacks
//        $game->defenderAllDieArray[] = $die3;
//
//        // 6, 2 vs 4
//        $this->assertFalse($sk->find_attack($game));
//
//        // success
//        $die3->value = 2;
//        $this->assertTrue($sk->find_attack($game));
//
//        $die3->value = 6;
//        $this->assertTrue($sk->find_attack($game));
//
//        $die3->value = 8;
//        $this->assertTrue($sk->find_attack($game));
//
//        // Find targets among more options
//        $game->defenderAllDieArray[] = $die4;
//
//        $this->assertTrue($sk->find_attack($game));
//
//        // Attacks with helpers
//        $sk->reset();
//
//        $die5 = BMDie::create(6,
//                    array("TestDummyBMSkillAVTesting" => "AVTesting"));
//        $die5->value = 1;
//
//        $sk->add_die($die1);
//        $game->attackerAllDieArray[] = $die1;
//        $sk->add_die($die5);
//        $game->attackerAllDieArray[] = $die5;
//        $sk->add_die($die2);
//        $game->attackerAllDieArray[] = $die2;
//
//        $die3->value = 20;
//        $this->assertTrue($sk->find_attack($game));
//        $die4->value = 20;
//        $this->assertFalse($sk->find_attack($game));
//        $die4->value = 4;
//        $this->assertFalse($sk->find_attack($game));
//
//        // Multi-value dice
//        $sk->reset();
//
//        $die5->remove_skill("AVTesting");
//        $die5->value = 6;
//
//        $die1->value = 6;
//        $die1->add_skill("TestStinger", "TestDummyBMSkillTestStinger");
//
//        $die2->value = 4;
//
//        $sk->add_die($die1);
//        $sk->add_die($die5);
//        $sk->add_die($die2);
//
//        $die3->value = 20;
//        $die4->value = 20;
//        $this->assertFalse($sk->find_attack($game));
//
//        $die3->value = 6;
//        $this->assertTrue($sk->find_attack($game));
//        $die3->value = 10;
//        $this->assertTrue($sk->find_attack($game));
//        $die3->value = 16;
//        $this->assertTrue($sk->find_attack($game));
//
//        for ($i = 1; $i <= 5; $i++) {
//            $die3->value = $i;
//            $this->assertTrue($sk->find_attack($game));
//            $die3->value = $i+6;
//            $this->assertTrue($sk->find_attack($game));
//            $die3->value = $i+10;
//            $this->assertTrue($sk->find_attack($game));
//        }
    }
}

