<?php

class BMDieOptionTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMDieOption
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new BMDieOption;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers BMDieOption::init
     */
    public function testInit () {
        $this->object->init(array(4,6), array());

        $this->assertEquals(1, $this->object->min);
        $this->assertEquals(array(4,6), $this->object->optionValueArray);
        $this->assertFalse(isset($this->object->optionValue));

        $this->assertFalse($this->object->has_skill('Testing'));
        $this->assertFalse($this->object->has_skill('Testing2'));

        $this->object->init(array(5,8),
                            array('TestDummyBMSkillTesting2' => 'Testing2'));

        $this->assertEquals(1, $this->object->min);
        $this->assertEquals(array(5,8), $this->object->optionValueArray);
        $this->assertFalse(isset($this->object->optionValue));

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
        $this->assertFalse(isset($die->optionValue));

        $this->assertFalse($die->has_skill('Testing'));
        $this->assertFalse($die->has_skill('Testing2'));

        $die = BMDieOption::create(array(5,8),
                                   array('TestDummyBMSkillTesting2' => 'Testing2'));

        $this->assertEquals(1, $die->min);
        $this->assertEquals(array(5,8), $die->optionValueArray);
        $this->assertFalse(isset($die->optionValue));

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
        $this->object->init(array(4,6));
        $this->object->max = $this->object->swingValue = 6;

        $dice = $this->object->split();

        $this->assertFalse($this->object === $dice[0]);
        $this->assertFalse($this->object === $dice[1]);
        $this->assertFalse($dice[0] instanceof BMDieOption);
        $this->assertFalse($dice[1] instanceof BMDieOption);

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
            $this->object->optionValue = 6;
            $this->fail('Invalid option value set should fail.');
        } catch (Exception $e) {
        }

        $this->object->optionValue = 7;
        $this->assertEquals(7, $this->object->optionValue);
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
//
//    /**
//     * @depends testInit
//     * @depends testSet_optionValue
//     * @depends testRoll
//     * @covers BMDieOption::make_play_die
//     */
//    public function testMake_play_die() {
//        $game = new TestDummyGame;
//
//        $this->object->init('X');
//        $this->object->ownerObject = $game;
//
//        $this->object->activate("player");
//        $newDie = $game->dice[0][1];
//
//        // No value yet set. It will call game->require_values()
//        try {
//            $newDie->make_play_die();
//            $this->fail('require_values not called.');
//        } catch (Exception $e) {
//        }
//
//        // If it doesn't need a value, it won't
//        $newDie->set_swingValue(array('X' => '11'));
//
//        // newDie shouldn't have a value yet
//        $this->assertFalse(is_numeric($newDie->value));
//
//        try {
//            $rolledDie = $newDie->make_play_die();
//            $this->fail('require_values called.');
//        } catch (Exception $e) {
//        }
//
//        // the die it returns should have a value, and not be the
//        // previous die
//        $this->assertFalse($newDie === $rolledDie);
//        $this->assertTrue(is_numeric($rolledDie->value));
//        $this->assertFalse(is_numeric($newDie->value));
//    }
//
//
//    /**
//     * @depends testInit
//     * @depends testSet_optionValue
//     * @depends testRoll
//     * @coversNothing
//     */
//    public function testIntegrationMake_play_die() {
//        $game = new BMGame;
//        $game->activeDieArrayArray = array(array(), array());
//
//        $this->object->init('X');
//        $this->object->ownerObject = $game;
//        $this->object->playerIdx = 1;
//
//        $this->object->activate();
//        $newDie = $game->activeDieArrayArray[1][0];
//
//        // No value yet set. It will call game->require_values()
//        try {
//            $newDie->make_play_die();
//            $this->fail('require_values not called.');
//        } catch (Exception $e) {
//        }
//
//        // If it doesn't need a value, it won't
//        $newDie->set_swingValue(array('X' => '11'));
//
//        // newDie shouldn't have a value yet
//        $this->assertFalse(is_numeric($newDie->value));
//        try {
//            $rolledDie = $newDie->make_play_die();
//            $this->fail('require_values called.');
//        } catch (Exception $e) {
//        }
//
//        // the die it returns should have a value, and not be the
//        // previous die
//        $this->assertFalse($newDie === $rolledDie);
//        $this->assertTrue(is_numeric($rolledDie->value));
//        $this->assertFalse(is_numeric($newDie->value));
//    }
//
//    /*
//     * @covers BMDieOption::describe
//     */
//    public function testDescribe() {
//        $this->object->init('X');
//        $this->assertEquals('X Swing Die', $this->object->describe(TRUE));
//        $this->assertEquals('X Swing Die', $this->object->describe(FALSE));
//
//        $this->object->set_swingValue(array('X' => 5));
//        $this->assertEquals('X Swing Die (with 5 sides)', $this->object->describe(TRUE));
//        $this->assertEquals('X Swing Die (with 5 sides)', $this->object->describe(FALSE));
//
//        $this->object->roll();
//        $value = $this->object->value;
//        $this->assertEquals(
//            "X Swing Die (with 5 sides) showing {$value}",
//            $this->object->describe(TRUE)
//        );
//        $this->assertEquals('X Swing Die (with 5 sides)', $this->object->describe(FALSE));
//
//        $this->object->add_skill('Poison');
//        $this->object->add_skill('Shadow');
//        $this->assertEquals(
//            "Poison Shadow X Swing Die (with 5 sides) showing {$value}",
//            $this->object->describe(TRUE)
//        );
//        $this->assertEquals(
//            'Poison Shadow X Swing Die (with 5 sides)',
//            $this->object->describe(FALSE)
//        );
//    }
//
//    /*
//     * @covers BMDie::get_recipe
//     */
//    public function testGet_recipe() {
//        $die0 = new BMDieSwing;
//        $die0->init('X', array());
//        $this->assertEquals('(X)', $die0->get_recipe());
//
//        $die1 = new BMDieSwing;
//        $die1->init('Y', array('Poison'));
//        $this->assertEquals('p(Y)', $die1->get_recipe());
//
//        $die2 = new BMDieSwing;
//        $die2->init('V', array('Shadow'));
//        $this->assertEquals('s(V)', $die2->get_recipe());
//
//        $die3 = new BMDie;
//        $die3->init('S', array('Poison', 'Shadow'));
//        $this->assertEquals('ps(S)', $die3->get_recipe());
//
//        $die4 = new BMDie;
//        $die4->init('R', array('Shadow', 'Poison'));
//        $this->assertEquals('sp(R)', $die4->get_recipe());
//    }
}
