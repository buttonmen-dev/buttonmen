<?php

class BMDieSwingTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMDieSwing
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void {
        $this->object = new BMDieSwing;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void {

    }

    /**
     * @covers BMDieSwing::swing_range
     */
    public function testSwing_range() {
        foreach (str_split('RSTUVWXYZ') as $swing) {
            $range = $this->object->swing_range($swing);
            $this->assertNotNull($range);
            $this->assertTrue(is_array($range));
            $this->assertEquals(2, count($range));
            $min = $range[0];
            $max = $range[1];
            $this->assertTrue($min < $max);
        }

        foreach (str_split('QrstuvwxyzA') as $swing) {
            $this->assertNull($this->object->swing_range($swing));
        }
    }

    /**
     * @depends testSwing_range
     * @covers BMDieSwing::init
     */
    public function testInit () {
        $this->object->init('X', array());

        $this->assertEquals(1, $this->object->min);
        $this->assertEquals('X', $this->object->swingType);

        $this->assertEquals(4, $this->object->swingMin);
        $this->assertEquals(20, $this->object->swingMax);

        $this->assertFalse($this->object->has_skill('Testing'));
        $this->assertFalse($this->object->has_skill('Testing2'));

        $this->object->init('Z',
                            array('TestDummyBMSkillTesting2' => 'Testing2'));

        $this->assertEquals(1, $this->object->min);
        $this->assertEquals('Z', $this->object->swingType);

        $this->assertEquals(4, $this->object->swingMin);
        $this->assertEquals(30, $this->object->swingMax);

        $this->assertTrue($this->object->has_skill('Testing2'));
        $this->assertFalse($this->object->has_skill('Testing'));

        $this->object->init('R');

        $this->assertEquals(1, $this->object->min);
        $this->assertEquals('R', $this->object->swingType);

        $this->assertEquals(2, $this->object->swingMin);
        $this->assertEquals(16, $this->object->swingMax);

        try {
            $this->object->init('spoon');
            $this->fail('Bad swing init did not throw an exception');
        } catch (UnexpectedValueException $e) {
        }

        try {
            $this->object->init('Q');
            $this->fail('Bad swing init did not throw an exception');
        } catch (UnexpectedValueException $e) {
        }

        try {
            $this->object->init('p');
            $this->fail('Bad swing init did not throw an exception');
        } catch (UnexpectedValueException $e) {
        }
    }

    /**
     * @depends testInit
     * @covers BMDieSwing::create
     */
    public function testCreate() {

        // Test all valid swing types
        foreach (str_split('RSTUVWXYZ') as $swing) {
            $die = BMDieSwing::create($swing, array());

            $this->assertInstanceOf('BMDie', $die);
            $this->assertInstanceOf('BMDieSwing', $die);
            $this->assertEquals($swing, $die->swingType);
        }


        // try some invalid types
        foreach (str_split('rstuvwxyzaQ') as $swing) {
            try {
                $die = BMDieSwing::create($swing, array());
                $this->fail("Creating with bad swing type '$swing' did not throw an exception.");
            } catch (UnexpectedValueException $e) {
            }
        }

        // try some more bad values
        try {
            $die = BMDieSwing::create(6, array());
            $this->fail('Creating with bad swing type did not throw an exception.');
        } catch (UnexpectedValueException $e) {
        }

        try {
            $die = BMDieSwing::create("RT", array());
            $this->fail('Creating with bad swing type did not throw an exception.');
        } catch (UnexpectedValueException $e) {
        }

        try {
            $die = BMDieSwing::create("0.A", array());
            $this->fail('Creating with bad swing type did not throw an exception.');
        } catch (UnexpectedValueException $e) {
        }
    }

    /*
     * @covers BMDie::create_from_recipe
     */
    public function testCreate_from_recipe() {
        $die = BMDie::create_from_recipe('ps(X)');
        $this->assertInstanceOf('BMDieSwing', $die);
        $this->assertTrue($die->has_skill('Poison'));
        $this->assertTrue($die->has_skill('Shadow'));
        $this->assertNull($die->max);
        $this->assertEquals('X', $die->swingType);
    }

    /**
     * @depends testInit
     * @covers BMDieSwing::activate
     */

    public function testActivate () {
        $game = new TestDummyGame;
        foreach (str_split('RSTUVWXYZ') as $dieIdx => $swing) {
            $this->object->init($swing);

            $this->object->ownerObject = $game;
            $this->object->activate();
            $newDie = $game->dice[$dieIdx][1];

            $this->assertFalse($newDie === $this->object);
            $this->assertTrue($game === $newDie->ownerObject);

            $this->assertEquals($newDie, $game->swingrequest[0]);
            $this->assertEquals($swing, $game->swingrequest[1]);
        }
    }

    /**
     * @depends testInit
     * @coversNothing
     */

    public function testIntegrationActivate () {
        $game = new BMGame;
        $game->activeDieArrayArray = array(array(), array());
        foreach (str_split('RSTUVWXYZ') as $swing) {
            $this->object->init($swing);
            $this->object->ownerObject = $game;
            $this->object->playerIdx = 1;
            $this->object->originalPlayerIdx = 1;
            $this->object->activate();

            $this->assertTrue(array_key_exists($swing, $game->swingRequestArrayArray[1]));
            $this->assertTrue($game->swingRequestArrayArray[1][$swing][0] instanceof BMDieSwing);
            $this->assertEquals($swing, $game->swingRequestArrayArray[1][$swing][0]->swingType);
            $this->assertFalse($this->object === $game->swingRequestArrayArray[1][$swing][0]);
        }
    }

    /**
     * @depends testInit
     * @covers BMDieSwing::split
     */
    public function testSplit() {
        $this->object->init('X');

        // set by hand to avoid a dependency loop with tests for
        // set_swingValue

        $this->object->max = $this->object->swingValue = 20;

        $dice = $this->object->split();

        $this->assertTrue($this->object === $dice[0]);
        $this->assertFalse($this->object === $dice[1]);
        $this->assertTrue($dice[0] instanceof BMDieSwing);
        $this->assertTrue($dice[1] instanceof BMDieSwing);
        $this->assertEquals('X', $dice[0]->swingType);
        $this->assertEquals('X', $dice[1]->swingType);
        $this->assertEquals(10, $dice[0]->max);
        $this->assertEquals(20, $dice[0]->swingValue);
        $this->assertEquals(10, $dice[1]->max);
        $this->assertEquals(20, $dice[1]->swingValue);

        // split again
        $dice = $dice[0]->split();
        $this->assertEquals('X', $dice[0]->swingType);
        $this->assertEquals('X', $dice[1]->swingType);
        $this->assertEquals(5, $dice[0]->max);
        $this->assertEquals(20, $dice[0]->swingValue);
        $this->assertEquals(5, $dice[1]->max);
        $this->assertEquals(20, $dice[1]->swingValue);

        // and again
        $dice = $dice[0]->split();
        $this->assertEquals('X', $dice[0]->swingType);
        $this->assertEquals('X', $dice[1]->swingType);
        $this->assertEquals(3, $dice[0]->max);
        $this->assertEquals(20, $dice[0]->swingValue);
        $this->assertEquals(2, $dice[1]->max);
        $this->assertEquals(20, $dice[1]->swingValue);

        // keep going
        $dice = $dice[0]->split();
        $this->assertEquals('X', $dice[0]->swingType);
        $this->assertEquals('X', $dice[1]->swingType);
        $this->assertEquals(2, $dice[0]->max);
        $this->assertEquals(20, $dice[0]->swingValue);
        $this->assertEquals(1, $dice[1]->max);
        $this->assertEquals(20, $dice[1]->swingValue);
    }

    /**
     * @depends testInit
     * @covers BMDieSwing::set_swingValue
     */
    public function testSet_swingValue() {


        foreach (str_split('RSTUVWXYZ') as $swing) {
            $this->object->init($swing);
            $range = $this->object->swing_range($swing);
            $min = $range[0];
            $max = $range[1];
            for ($i = $min; $i <= $max; $i++) {
                $swingList = array($swing => $i);
                $this->assertTrue($this->object->set_swingValue($swingList));
                $this->assertEquals($i, $this->object->swingValue);
                $this->assertEquals($i, $this->object->max);

            }
        }


        $this->object->init("X");

        // check error checking
        $swingList = array();
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array('R' => 12);
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array('R' => 12,
                           'S' => 10,
                           'U' => 15,
                           'Y' => 1,
                           'Z' => 30);
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array('X' => 3);
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array('X' => 21);
        $this->assertFalse($this->object->set_swingValue($swingList));

        // needle in a haystack
        $swingList = array('R' => 12,
                           'S' => 10,
                           'X' => 15,
                           'Y' => 1,
                           'Z' => 30);

        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 15);
        $this->assertEquals($this->object->max, 15);
    }

    /**
     * @depends testInit
     * @depends testActivate
     * @depends testSet_swingValue
     * @covers BMDieSwing::roll
     */
    public function testRoll() {

        // testing whether it calls the appropriate methods in BMGame

        $this->object->init('X');

        // needs a value, hasn't requested one. Should call
        // request_swing_values before calling require_values
        $game = new TestDummyGame;

        $this->object->ownerObject = $game;

        try {
            $this->object->roll(FALSE);
            $this->fail('dummy require_values not called.');
        } catch (Exception $e) {
        }

        $this->assertNotEmpty($game->swingrequest);

        // Once activated, it should have requested a value, but still
        // needs one.
        // Calls require_values without calling request_swing_values

        $this->object->init('X');

        $game = new TestDummyGame;
        $this->object->ownerObject = $game;

        $this->object->activate('player');
        $newDie = $game->dice[0][1];

        $this->assertNotNull($game->swingrequest);
        $game->swingrequest = array();

        try {
            $newDie->roll(FALSE);
            $this->fail('dummy require_values not called.');
        } catch (Exception $e) {
        }

        $this->assertEmpty($game->swingrequest);

        // And if the swing value is set, it won't call require_values
        $this->object->init('X');

        $game = new TestDummyGame;
        $this->object->ownerObject = $game;

        $this->object->activate('player');
        $newDie = $game->dice[0][1];

        $this->assertNotNull($game->swingrequest);
        $game->swingrequest = array();

        $newDie->set_swingValue(array('X' => '15'));

        // it hasn't rolled yet
        $this->assertFalse(is_numeric($newDie->value));

        try {
            $newDie->roll(FALSE);
            $this->fail('dummy require_values was called.');
        } catch (Exception $e) {
        }
        $this->assertEmpty($game->swingrequest);

        // Does it roll?
        $this->assertTrue(is_numeric($newDie->value));
    }


    /**
     * @depends testInit
     * @depends testActivate
     * @depends testSet_swingValue
     * @coversNothing
     */
    public function testInterfaceRoll() {
        // testing whether it calls the appropriate methods in BMGame
        $this->object->init('X');
        $game = new BMGame;
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 1;

        // needs a value, hasn't requested one. Should call
        // request_swing_values
        $this->object->roll(FALSE);
        $this->assertNotEmpty($game->swingRequestArrayArray[1]);

        // Once activated, it should have requested a value, but still
        // needs one.
        // Calls require_values without calling request_swing_values

        $this->object->init('X');

        $game = new BMGame;
        $game->activeDieArrayArray = array(array(), array());
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 1;

        $this->object->activate();
        $this->assertNotNull($game->swingRequestArrayArray);
        $this->assertCount(1, $game->swingRequestArrayArray[1]['X']);


        // And if the swing value is set, it won't call require_values
        $this->object->init('X');

        $game = new BMGame;
        $game->activeDieArrayArray = array(array(), array());
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 0;

        $this->object->activate();

        $this->assertNotNull($game->swingRequestArrayArray);
        $this->assertCount(1, $game->swingRequestArrayArray[0]['X']);

        $game->activeDieArrayArray[0][0]->set_swingValue(array('X' => '15'));

        $this->assertEquals(15, $game->activeDieArrayArray[0][0]->swingValue);

        // it hasn't rolled yet
        $this->assertFalse(is_numeric($game->activeDieArrayArray[0][0]->value));

        $game->activeDieArrayArray[0][0]->roll(FALSE);

        // Does it roll?
        $this->assertTrue(is_numeric($game->activeDieArrayArray[0][0]->value));
    }

    /**
     * @depends testInit
     * @depends testSet_swingValue
     * @depends testRoll
     * @covers BMDieSwing::make_play_die
     */
    public function testMake_play_die() {
        $game = new TestDummyGame;

        $this->object->init('X');
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
        $newDie->set_swingValue(array('X' => '11'));

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
     * @depends testSet_swingValue
     * @depends testRoll
     * @coversNothing
     */
    public function testIntegrationMake_play_die() {
        $game = new BMGame;
        $game->activeDieArrayArray = array(array(), array());

        $this->object->init('X');
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 1;

        $this->object->activate();
        $newDie = $game->activeDieArrayArray[1][0];

        // No value yet set. It will call game->require_values()
        try {
            $newDie->make_play_die();
            $this->fail('require_values not called.');
        } catch (Exception $e) {
        }

        // If it doesn't need a value, it won't
        $newDie->set_swingValue(array('X' => '11'));

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

    /*
     * @covers BMDieSwing::describe
     */
    public function testDescribe() {
        $this->object->init('Y');
        $this->assertEquals('Y Swing Die', $this->object->describe(TRUE));
        $this->assertEquals('Y Swing Die', $this->object->describe(FALSE));

        $this->object->set_swingValue(array('Y' => 1));
        $this->assertEquals('Y Swing Die (with 1 side)', $this->object->describe(TRUE));
        $this->assertEquals('Y Swing Die (with 1 side)', $this->object->describe(FALSE));

        $this->object->set_swingValue(array('Y' => 5));
        $this->assertEquals('Y Swing Die (with 5 sides)', $this->object->describe(TRUE));
        $this->assertEquals('Y Swing Die (with 5 sides)', $this->object->describe(FALSE));

        $this->object->roll();
        $value = $this->object->value;
        $this->assertEquals(
            "Y Swing Die (with 5 sides) showing {$value}",
            $this->object->describe(TRUE)
        );
        $this->assertEquals('Y Swing Die (with 5 sides)', $this->object->describe(FALSE));

        $this->object->add_skill('Poison');
        $this->object->add_skill('Shadow');
        $this->assertEquals(
            "Poison Shadow Y Swing Die (with 5 sides) showing {$value}",
            $this->object->describe(TRUE)
        );
        $this->assertEquals(
            'Poison Shadow Y Swing Die (with 5 sides)',
            $this->object->describe(FALSE)
        );

        $this->object->add_skill('Mood');
        $this->assertEquals(
            "Poison Shadow Y Mood Swing Die (with 5 sides) showing {$value}",
            $this->object->describe(TRUE)
        );
        $this->assertEquals(
            'Poison Shadow Y Mood Swing Die (with 5 sides)',
            $this->object->describe(FALSE)
        );
    }

    /*
     * @covers BMDieSwing::shrink
     */
    public function testShrink() {
        $die = $this->object;
        $die->init('Z');
        $die->set_swingValue(array('Z' => 30));
        $die->shrink();
        $this->assertTrue($die instanceof BMDieSwing);
        $this->assertEquals('Z', $die->swingType);
        $this->assertEquals(30, $die->swingValue);
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
        $this->assertTrue($die instanceof BMDieSwing);
        $this->assertEquals('Z', $die->swingType);
        $this->assertEquals(30, $die->swingValue);
        $this->assertEquals(1, $die->max);
    }

    /*
     * @covers BMDieSwing::grow
     */
    public function testGrow() {
        $die = $this->object;
        $die->init('Y');
        $die->set_swingValue(array('Y' => 1));
        $die->grow();
        $this->assertTrue($die instanceof BMDieSwing);
        $this->assertEquals('Y', $die->swingType);
        $this->assertEquals(1, $die->swingValue);
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
        $this->assertTrue($die instanceof BMDieSwing);
        $this->assertEquals('Y', $die->swingType);
        $this->assertEquals(1, $die->swingValue);
        $this->assertEquals(30, $die->max);
    }


    /*
     * @covers BMDie::get_recipe
     */
    public function testGet_recipe() {
        $die0 = new BMDieSwing;
        $die0->init('X', array());
        $this->assertEquals('(X)', $die0->get_recipe());

        $die1 = new BMDieSwing;
        $die1->init('Y', array('Poison'));
        $this->assertEquals('p(Y)', $die1->get_recipe());

        $die2 = new BMDieSwing;
        $die2->init('V', array('Shadow'));
        $this->assertEquals('s(V)', $die2->get_recipe());

        $die3 = new BMDieSwing;
        $die3->init('S', array('Poison', 'Shadow'));
        $this->assertEquals('ps(S)', $die3->get_recipe());

        $die4 = new BMDieSwing;
        $die4->init('R', array('Shadow', 'Poison'));
        $this->assertEquals('sp(R)', $die4->get_recipe());

        $die5 = new BMDieSwing;
        $die5->init('X', array());
        $this->assertEquals('(X)', $die5->get_recipe(TRUE));
        $die5->max = '9';
        $this->assertEquals('(X=9)', $die5->get_recipe(TRUE));
    }
}
