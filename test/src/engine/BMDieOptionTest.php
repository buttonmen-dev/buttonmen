<?php

class BMDieOptionTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMDieOption
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void {
        $this->object = new BMDieOption;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void {

    }

    /**
     * @covers BMDieOption::init
     */
    public function testInit () {
        $this->object->init(array(4,6), array());

        $this->assertEquals(1, $this->object->min);
        $this->assertEquals(array(4,6), $this->object->optionValueArray);
        $this->assertTrue(is_null($this->object->max));

        $this->assertFalse($this->object->has_skill('Testing'));
        $this->assertFalse($this->object->has_skill('Testing2'));

        $this->object->init(array(5,8),
                            array('TestDummyBMSkillTesting2' => 'Testing2'));

        $this->assertEquals(1, $this->object->min);
        $this->assertEquals(array(5,8), $this->object->optionValueArray);
        $this->assertTrue(is_null($this->object->max));

        $this->assertTrue($this->object->has_skill('Testing2'));
        $this->assertFalse($this->object->has_skill('Testing'));

        try {
            $this->object->init('spoon');
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }

        try {
            $this->object->init(4);
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }

        try {
            $this->object->init(array(4));
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }

        try {
            $this->object->init(array(4,6,8));
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }

        try {
            $this->object->init('4/6');
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }
    }

    /**
     * @depends testInit
     * @covers BMDieOption::create
     */
    public function testCreate() {

        $die = BMDieOption::create(array(4,6), array());

        $this->assertEquals(1, $die->min);
        $this->assertEquals(array(4,6), $die->optionValueArray);
        $this->assertTrue(is_null($die->max));

        $this->assertFalse($die->has_skill('Testing'));
        $this->assertFalse($die->has_skill('Testing2'));

        $die = BMDieOption::create(array(5,8),
                                   array('TestDummyBMSkillTesting2' => 'Testing2'));

        $this->assertEquals(1, $die->min);
        $this->assertEquals(array(5,8), $die->optionValueArray);
        $this->assertTrue(is_null($die->max));

        $this->assertTrue($die->has_skill('Testing2'));
        $this->assertFalse($die->has_skill('Testing'));

        try {
            $die = BMDieOption::create('spoon');
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }

        try {
            $die = BMDieOption::create(4);
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }

        try {
            $die = BMDieOption::create(array(4));
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }

        try {
            $die = BMDieOption::create(array(4,6,8));
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }

        try {
            $die = BMDieOption::create('4/6');
            $this->fail('Bad option init did not throw an exception');
        } catch (InvalidArgumentException $e) {
        }
    }

    /*
     * @covers BMDie::create_from_recipe
     */
    public function testCreate_from_recipe() {
        $die = BMDie::create_from_recipe('ps(7/9)');
        $this->assertInstanceOf('BMDieOption', $die);
        $this->assertTrue($die->has_skill('Poison'));
        $this->assertTrue($die->has_skill('Shadow'));
        $this->assertNull($die->max);
        $this->assertEquals(array(7,9), $die->optionValueArray);
    }

    /**
     * @depends testInit
     * @covers BMDieOption::activate
     */

    public function testActivate () {
        $game = new TestDummyGame;
        $this->object->init(array(6,2));

        $this->object->ownerObject = $game;
        $this->object->activate();
        $newDie = $game->dice[0][1];

        $this->assertFalse($newDie === $this->object);
        $this->assertTrue($game === $newDie->ownerObject);

        $this->assertEquals($newDie, $game->optionrequest[0]);
        $this->assertEquals(array(6,2), $game->optionrequest[1]);
    }

    /**
     * @depends testInit
     * @coversNothing
     */

    public function testIntegrationActivate () {
        $game = new BMGame;
        $game->activeDieArrayArray = array(array(new BMDie), array(new BMDie, new BMDie));
        $this->object->init(array(8,3));
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 1;
        $this->object->originalPlayerIdx = 1;
        $this->object->activate();

        $this->assertTrue(array_key_exists(2, $game->optRequestArrayArray[1]));
        $this->assertEquals(array(8,3), $game->optRequestArrayArray[1][2]);
    }

    /**
     * @depends testInit
     * @covers BMDieOption::split
     */
    public function testSplit() {
        $this->object->init(array(4, 6));
        $this->object->max = 6;

        $dice = $this->object->split();

        $this->assertTrue($this->object === $dice[0]);
        $this->assertFalse($this->object === $dice[1]);
        $this->assertTrue($dice[0] instanceof BMDieOption);
        $this->assertEquals(array(4, 6), $dice[0]->optionValueArray);
        $this->assertTrue($dice[1] instanceof BMDieOption);
        $this->assertEquals(array(4, 6), $dice[0]->optionValueArray);

        $this->assertEquals($dice[0]->max, 3);
        $this->assertEquals($dice[1]->max, 3);
    }

    /**
     * @depends testInit
     * @covers BMDieOption::__set
     */
    public function testSet_optionValue() {
        $this->object->init(array(5,7));

        // try setting the option value to an invalid value
        try {
            $this->object->max = 6;
            $this->fail('Invalid option value set should fail.');
        } catch (Exception $e) {
        }

        $this->object->max = 7;
        $this->assertEquals(7, $this->object->max);
    }

    /**
     * @depends testInit
     * @covers BMDieOption::set_optionValue
     */
    public function testSet_optionValue_including_zero() {
        $this->object->init(array(0,7));

        $this->assertFalse($this->object->set_optionValue(5), 'Invalid option value set should fail.');

        $this->assertTrue($this->object->set_optionValue(7), 'Valid nonzero option value set should pass.');
        $this->assertEquals(7, $this->object->max);
        $this->assertEquals(1, $this->object->min);

        $this->assertTrue($this->object->set_optionValue(0), 'Valid zero option value set should pass.');

        $this->assertEquals(0, $this->object->max);
        $this->assertEquals(0, $this->object->min);
    }

    /**
     * @depends testInit
     * @depends testActivate
     * @depends testSet_optionValue
     * @covers BMDieOption::roll
     */
    public function testRoll() {

        // testing whether it calls the appropriate methods in BMGame

        $this->object->init(array(4, 10));

        // needs a value, hasn't requested one. Should call
        // request_option_values before calling require_values
        $game = new TestDummyGame;

        $this->object->ownerObject = $game;

        try {
            $this->object->roll(FALSE);
            $this->fail('dummy require_values not called.');
        } catch (Exception $e) {
        }

        $this->assertNotEmpty($game->optionrequest);

        // Once activated, it should have requested a value, but still
        // needs one.
        // Calls require_values without calling request_option_values

        $this->object->init(array(5, 11));

        $game = new TestDummyGame;
        $this->object->ownerObject = $game;

        $this->object->activate('player');
        $newDie = $game->dice[0][1];

        $this->assertNotNull($game->optionrequest);
        $game->optionrequest = array();

        try {
            $newDie->roll(FALSE);
            $this->fail('dummy require_values not called.');
        } catch (Exception $e) {
        }

        $this->assertEmpty($game->optionrequest);

        // And if the option value is set, it won't call require_values
        $this->object->init(array(6,12));

        $game = new TestDummyGame;
        $this->object->ownerObject = $game;

        $this->object->activate('player');
        $newDie = $game->dice[0][1];

        $this->assertNotNull($game->optionrequest);
        $game->optionrequest = array();

        $newDie->set_optionValue(12);

        // it hasn't rolled yet
        $this->assertFalse(is_numeric($newDie->value));

        try {
            $newDie->roll(FALSE);
            $this->fail('dummy require_values was called.');
        } catch (Exception $e) {
        }
        $this->assertEmpty($game->optionrequest);

        // Does it roll?
        $this->assertTrue(is_numeric($newDie->value));
    }


    /**
     * @depends testInit
     * @depends testActivate
     * @depends testSet_optionValue
     * @coversNothing
     */
    public function testInterfaceRoll() {
        // testing whether it calls the appropriate methods in BMGame
        $this->object->init(array(5,7));
        $game = new BMGame;
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 1;
        $game->activeDieArrayArray = array(array(), array($this->object));

        // needs a value, hasn't requested one. Should call
        // request_option_values
        $this->object->roll(FALSE);
        $this->assertNotEmpty($game->optRequestArrayArray[1]);

        $game->activeDieArrayArray[1][0]->set_optionValue(7);

        // it hasn't rolled yet
        $this->assertFalse(is_numeric($game->activeDieArrayArray[1][0]->value));

        $game->activeDieArrayArray[1][0]->roll(FALSE);

        // Does it roll?
        $this->assertTrue(is_numeric($game->activeDieArrayArray[1][0]->value));
    }

    /**
     * @depends testInit
     * @depends testSet_optionValue
     * @depends testRoll
     * @covers BMDieOption::make_play_die
     */
    public function testMake_play_die() {
        $game = new TestDummyGame;

        $this->object->init(array(4,7));
        $this->object->ownerObject = $game;

        $this->object->activate("player");
        $newDie = $game->dice[0][1];

        // No value yet set. It will call game->require_values()
        try {
            $newDie->make_play_die();
            $this->fail('require_values not called.');
        } catch (Exception $e) {
        }

        // If it doesn't need a value, it won't
        $newDie->set_optionValue(7);

        // newDie shouldn't have a value yet
        $this->assertFalse(is_numeric($newDie->value));

        try {
            $rolledDie = $newDie->make_play_die();
            $this->fail('require_values called.');
        } catch (Exception $e) {
        }

        // the die it returns should have a value, and not be the
        // previous die
        $this->assertFalse($newDie === $rolledDie);
        $this->assertTrue(is_numeric($rolledDie->value));
        $this->assertFalse(is_numeric($newDie->value));
    }


    /**
     * @depends testInit
     * @depends testSet_optionValue
     * @depends testRoll
     * @coversNothing
     */
    public function testIntegrationMake_play_die() {
        $game = new BMGame;
        $game->activeDieArrayArray = array(array(), array());

        $this->object->init(array(4,7));
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 1;

        $this->object->activate();
        $newDie = $game->activeDieArrayArray[1][0];

        $newDie->make_play_die();
        $newDie->set_optionValue(7);

        // newDie shouldn't have a value yet
        $this->assertFalse(is_numeric($newDie->value));
        $rolledDie = $newDie->make_play_die();

        // the die it returns should have a value, and not be the
        // previous die
        $this->assertFalse($newDie === $rolledDie);
        $this->assertTrue(is_numeric($rolledDie->value));
        $this->assertFalse(is_numeric($newDie->value));
    }

    /*
     * @covers BMDieOption::describe
     */
    public function testDescribe() {
        $this->object->init(array(6,8));
        $this->assertEquals('Option Die (with 6 or 8 sides)', $this->object->describe(TRUE));
        $this->assertEquals('Option Die (with 6 or 8 sides)', $this->object->describe(FALSE));

        $this->object->set_optionValue(8);
        $this->assertEquals('Option Die (with 8 sides)', $this->object->describe(TRUE));
        $this->assertEquals('Option Die (with 8 sides)', $this->object->describe(FALSE));

        $this->object->roll();
        $value = $this->object->value;
        $this->assertEquals(
            "Option Die (with 8 sides) showing {$value}",
            $this->object->describe(TRUE)
        );
        $this->assertEquals('Option Die (with 8 sides)', $this->object->describe(FALSE));

        $this->object->add_skill('Poison');
        $this->object->add_skill('Shadow');
        $this->assertEquals(
            "Poison Shadow Option Die (with 8 sides) showing {$value}",
            $this->object->describe(TRUE)
        );
        $this->assertEquals(
            'Poison Shadow Option Die (with 8 sides)',
            $this->object->describe(FALSE)
        );
    }

    /*
     * @covers BMDieOption::describe
     */
    public function testDescribeSingular() {
        $this->object->init(array(1,30));
        $this->assertEquals('Option Die (with 1 or 30 sides)', $this->object->describe(TRUE));
        $this->assertEquals('Option Die (with 1 or 30 sides)', $this->object->describe(FALSE));

        $this->object->set_optionValue(1);
        $this->assertEquals('Option Die (with 1 side)', $this->object->describe(TRUE));
        $this->assertEquals('Option Die (with 1 side)', $this->object->describe(FALSE));

        $this->object->roll();
        $this->assertEquals(
            "Option Die (with 1 side) showing 1",
            $this->object->describe(TRUE)
        );
        $this->assertEquals('Option Die (with 1 side)', $this->object->describe(FALSE));

        $this->object->add_skill('Poison');
        $this->object->add_skill('Shadow');
        $this->assertEquals(
            "Poison Shadow Option Die (with 1 side) showing 1",
            $this->object->describe(TRUE)
        );
        $this->assertEquals(
            'Poison Shadow Option Die (with 1 side)',
            $this->object->describe(FALSE)
        );
    }

    /*
     * @covers BMDieOption::shrink
     */
    public function testShrink() {
        $die = $this->object;
        $die->init(array(1, 99));
        $die->set_optionValue(99);
        $die->shrink();
        $this->assertTrue($die instanceof BMDieOption);
        $this->assertEquals(array(1, 99), $die->optionValueArray);
        $this->assertEquals(30, $die->max);
        $die->shrink();
        $this->assertEquals(20, $die->max);
        $die->shrink();
        $this->assertEquals(16, $die->max);
        $die->shrink();
        $this->assertEquals(12, $die->max);
        $die->shrink();
        $this->assertEquals(10, $die->max);
        $die->shrink();
        $this->assertEquals(8, $die->max);
        $die->shrink();
        $this->assertEquals(6, $die->max);
        $die->shrink();
        $this->assertEquals(4, $die->max);
        $die->shrink();
        $this->assertEquals(2, $die->max);
        $die->shrink();
        $this->assertEquals(1, $die->max);
        $die->shrink();
        $this->assertTrue($die instanceof BMDieOption);
        $this->assertEquals(array(1, 99), $die->optionValueArray);
        $this->assertEquals(1, $die->max);
    }

    /*
     * @covers BMDieSwing::grow
     */
    public function testGrow() {
        $die = $this->object;
        $die->init(array(1, 99));
        $die->set_optionValue(1);
        $die->grow();
        $this->assertTrue($die instanceof BMDieOption);
        $this->assertEquals(array(1, 99), $die->optionValueArray);
        $this->assertEquals(2, $die->max);
        $die->grow();
        $this->assertEquals(4, $die->max);
        $die->grow();
        $this->assertEquals(6, $die->max);
        $die->grow();
        $this->assertEquals(8, $die->max);
        $die->grow();
        $this->assertEquals(10, $die->max);
        $die->grow();
        $this->assertEquals(12, $die->max);
        $die->grow();
        $this->assertEquals(16, $die->max);
        $die->grow();
        $this->assertEquals(20, $die->max);
        $die->grow();
        $this->assertEquals(30, $die->max);
        $die->grow();
        $this->assertTrue($die instanceof BMDieOption);
        $this->assertEquals(array(1, 99), $die->optionValueArray);
        $this->assertEquals(30, $die->max);
    }

    /*
     * @covers BMDie::get_recipe
     */
    public function testGet_recipe() {
        $die0 = new BMDieOption;
        $die0->init(array(4,8), array());
        $this->assertEquals('(4/8)', $die0->get_recipe());

        $die1 = new BMDieOption;
        $die1->init(array(4,8), array('Poison'));
        $this->assertEquals('p(4/8)', $die1->get_recipe());

        $die2 = new BMDieOption;
        $die2->init(array(4,8), array('Shadow'));
        $this->assertEquals('s(4/8)', $die2->get_recipe());

        $die3 = new BMDieOption;
        $die3->init(array(4,8), array('Poison', 'Shadow'));
        $this->assertEquals('ps(4/8)', $die3->get_recipe());

        $die4 = new BMDieOption;
        $die4->init(array(4,8), array('Shadow', 'Poison'));
        $this->assertEquals('sp(4/8)', $die4->get_recipe());

        $die5 = new BMDieOption;
        $die5->init(array(4,8), array());
        $this->assertEquals('(4/8)', $die5->get_recipe(TRUE));
        $die5->max = '4';
        $this->assertEquals('(4/8=4)', $die5->get_recipe(TRUE));
    }
}
