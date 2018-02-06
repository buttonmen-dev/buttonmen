<?php

class BMAttackRushTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMAttackRush
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMAttackRush;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMAttackRush::validate_attack
     */
    public function testValidate_attack()
    {
        $game = new TestDummyGame;

        $sk = $this->object;

        $die1 = BMDie::create(6);
        $die1->add_skill('Rush');
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

        $target1->value = 2;
        $target2->value = 4;
        $this->assertTrue($sk->validate_attack($game,
                                               array($die1),
                                               array($target1, $target2)));

        // Failures

        $target1->value = 6;
        $this->assertFalse($sk->validate_attack($game,
                                                array($die1),
                                                array($target1)));

        $target1->value = 2;
        $target2->value = 3;
        $target3->value = 1;
        $this->assertFalse($sk->validate_attack($game,
                                                array($die1),
                                                array($target1, $target2, $target3)));

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
     * @covers BMAttackRush::find_attack
     * @depends testValidate_attack
     */
    public function testFind_attack()
    {
        $game = new TestDummyGame;

        $sk = $this->object;

        // we find nothing when there are no attackers
        $this->assertFalse($sk->find_attack($game));

        // Load some dice into the attack.
        $die1 = BMDie::create(20);
        $die1->value = 6;
        $die1->add_skill('Rush');

        $die2 = BMDie::create(6);
        $die2->value = 1;

        $die3 = BMDie::create(6);
        $die3->value = 4;

        $die4 = BMDie::create(6);
        $die4->value = 1;

        $die5 = BMDie::create(6);
        $die5->value = 1;

        $die6 = BMDie::create(6);
        $die6->value = 2;

        $sk->add_die($die1);
        $sk->add_die($die2);


        // we find nothing when there are no defenders
        $this->assertFalse($sk->find_attack($game));

        // Speed attacks
        $game->attackerAllDieArray = array($die1, $die2);
        $game->defenderAllDieArray = array($die3);

        // 6 vs 4
        $this->assertFalse($sk->find_attack($game));

        $game->defenderAllDieArray = array($die2, $die3, $die4);

        // no valid Rush Attack
        $this->assertFalse($sk->find_attack($game));

        // success

        $game->defenderAllDieArray = array($die3, $die4, $die6);
        $this->assertTrue($sk->find_attack($game));
    }
}
