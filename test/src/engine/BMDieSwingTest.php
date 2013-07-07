<?php

class BMDieSwingTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMDieSwing
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new BMDieSwing;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers BMDieSwing::swing_range
     */
    public function testSwing_range() {
        foreach (str_split("RSTUVWXYZ") as $swing) {
            $range = $this->object->swing_range($swing);
            $this->assertNotNull($range);
            $this->assertTrue(is_array($range));
            $this->assertEquals(2, count($range));
            $min = $range[0];
            $max = $range[1];
            $this->assertTrue($min < $max);
        }

        foreach (str_split("QrstuvwxyzA") as $swing) {
            $this->assertNull($this->object->swing_range($swing));
        }
    }

    /**
     * @depends testSwing_range
     * @covers BMDieSwing::init
     */
    public function testInit () {
        $this->object->init("X", array());

        $div = PHPUnit_Framework_Assert::readAttribute($this->object, "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($this->object, "remainder");

        $this->assertEquals(1, $div);
        $this->assertEquals(0, $rem);

        $this->assertEquals($this->object->min, 1);
        $this->assertEquals($this->object->swingType, "X");

        $this->assertEquals($this->object->swingMin, 4);
        $this->assertEquals($this->object->swingMax, 20);

        $this->assertFalse($this->object->has_skill("Testing"));
        $this->assertFalse($this->object->has_skill("Testing2"));

        $this->object->init("Z",
                            array("TestDummyBMSkillTesting2" => "Testing2"));

        $this->assertEquals($this->object->min, 1);
        $this->assertEquals($this->object->swingType, "Z");

        $this->assertEquals($this->object->swingMin, 4);
        $this->assertEquals($this->object->swingMax, 30);

        $this->assertTrue($this->object->has_skill("Testing2"));
        $this->assertFalse($this->object->has_skill("Testing"));

        $this->object->init("R");

        $this->assertEquals($this->object->min, 1);
        $this->assertEquals($this->object->swingType, "R");

        $this->assertEquals($this->object->swingMin, 2);
        $this->assertEquals($this->object->swingMax, 16);

        $fail = FALSE;
        try {
            $this->object->init("spoon");
        } catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }
        $this->assertTrue($fail, "Bad swing init didn't throw an exception");

        $fail = FALSE;
        try {
            $this->object->init("Q");
        } catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }
        $this->assertTrue($fail, "Bad swing init didn't throw an exception");

        $fail = FALSE;
        try {
            $this->object->init("p");
        } catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }
        $this->assertTrue($fail, "Bad swing init didn't throw an exception");
    }

    /**
     * @depends testInit
     * @covers BMDieSwing::create
     */
    public function testCreate() {

        // Test all valid swing types
        foreach (str_split("RSTUVWXYZ") as $swing) {
            $die = BMDieSwing::create($swing, array());

            $this->assertInstanceOf('BMDie', $die);
            $this->assertInstanceOf('BMDieSwing', $die);
            $this->assertEquals($swing, $die->swingType);
        }


        // try some invalid types
        foreach (str_split("rstuvwxyzaQ") as $swing) {
            $fail = FALSE;
            try {
                $die = BMDieSwing::create($swing, array());
            } catch (UnexpectedValueException $e) {
                $fail = TRUE;
            }
            $this->assertTrue($fail, "Creating with bad swing type '$swing' didn't throw an exception.");

        }

        $fail = FALSE;

        // try some more bad values
        try {
            $die = BMDieSwing::create(6, array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating with bad swing type didn't throw an exception.");
        $fail = FALSE;

        try {
            $die = BMDieSwing::create("RT", array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating with bad swing type didn't throw an exception.");
        $fail = FALSE;

        try {
            $die = BMDieSwing::create("0.A", array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }
        $this->assertTrue($fail, "Creating with bad swing type didn't throw an exception.");
        $fail = FALSE;


    }

    /**
     * @depends testInit
     * @covers BMDieSwing::activate
     */

    public function testActivate () {
        $game = new TestDummyGame;
        foreach (str_split("RSTUVWXYZ") as $dieIdx => $swing) {
            $this->object->init($swing);

            $this->object->ownerObject = $game;
            $this->object->activate("player");
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
        $button = new BMButton;
        foreach (str_split("RSTUVWXYZ") as $swing) {
            $this->object->init($swing);
            $this->object->ownerObject = $button;
// james           $this->object->activate(0);

// james           $this->assertTrue($game->swingrequest[0] === $newDie);
//            $this->assertEquals($game->swingrequest[1], $swing);
        }

    }

    /**
     * @depends testInit
     * @covers BMDieSwing::split
     */
    public function testSplit() {
        $this->object->init("X");

        $div = PHPUnit_Framework_Assert::readAttribute($this->object, "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($this->object, "remainder");
        $this->assertEquals(1, $div);
        $this->assertEquals(0, $rem);

        // set by hand to avoid a dependency loop with tests for
        // set_swingValue

        $this->object->max = $this->object->swingValue = 20;

        $dice = $this->object->split();

        $this->assertTrue($this->object === $dice[0]);
        $this->assertFalse($this->object === $dice[1]);

        $this->assertEquals($dice[0]->swingValue, 20);
        $this->assertEquals($dice[1]->swingValue, 20);
        $this->assertEquals($dice[0]->max, 10);
        $this->assertEquals($dice[1]->max, 10);

        $div = PHPUnit_Framework_Assert::readAttribute($dice[0], "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($dice[0], "remainder");
        $this->assertEquals(2, $div);
        $this->assertEquals(0, $rem);

        $div = PHPUnit_Framework_Assert::readAttribute($dice[1], "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($dice[1], "remainder");

        $this->assertEquals(2, $div);
        $this->assertEquals(0, $rem);

        // split again
        $dice = $this->object->split();

        $this->assertTrue($this->object === $dice[0]);
        $this->assertFalse($this->object === $dice[1]);

        $this->assertEquals($dice[0]->swingValue, 20);
        $this->assertEquals($dice[1]->swingValue, 20);
        $this->assertEquals($dice[0]->max, 5);
        $this->assertEquals($dice[1]->max, 5);

        $div = PHPUnit_Framework_Assert::readAttribute($dice[0], "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($dice[0], "remainder");
        $this->assertEquals(4, $div);
        $this->assertEquals(0, $rem);

        $div = PHPUnit_Framework_Assert::readAttribute($dice[1], "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($dice[1], "remainder");

        $this->assertEquals(4, $div);
        $this->assertEquals(0, $rem);

        // and again
        $dice = $this->object->split();

        $this->assertTrue($this->object === $dice[0]);
        $this->assertFalse($this->object === $dice[1]);

        $this->assertEquals($dice[0]->swingValue, 20);
        $this->assertEquals($dice[1]->swingValue, 20);
        $this->assertEquals($dice[0]->max, 3);
        $this->assertEquals($dice[1]->max, 2);

        $div = PHPUnit_Framework_Assert::readAttribute($dice[0], "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($dice[0], "remainder");
        $this->assertEquals(8, $div);
        $this->assertEquals(1, $rem);

        $div = PHPUnit_Framework_Assert::readAttribute($dice[1], "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($dice[1], "remainder");

        $this->assertEquals(8, $div);
        $this->assertEquals(0, $rem);

        // keep going
        $dice = $this->object->split();

        $this->assertTrue($this->object === $dice[0]);
        $this->assertFalse($this->object === $dice[1]);

        $this->assertEquals($dice[0]->swingValue, 20);
        $this->assertEquals($dice[1]->swingValue, 20);
        $this->assertEquals($dice[0]->max, 2);
        $this->assertEquals($dice[1]->max, 1);

        $div = PHPUnit_Framework_Assert::readAttribute($dice[0], "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($dice[0], "remainder");
        $this->assertEquals(16, $div);
        $this->assertEquals(1, $rem);

        $div = PHPUnit_Framework_Assert::readAttribute($dice[1], "divisor");
        $rem = PHPUnit_Framework_Assert::readAttribute($dice[1], "remainder");

        // remainder is not preserved across splits
        $this->assertEquals(16, $div);
        $this->assertEquals(0, $rem);

    }

    /**
     * @depends testInit
     * @depends testSplit
     * @covers BMDieSwing::set_swingValue
     */
    public function testSet_swingValue() {


        foreach (str_split("RSTUVWXYZ") as $swing) {
            $this->object->init($swing);
            $range = $this->object->swing_range($swing);
            $min = $range[0];
            $max = $range[1];
            for ($i = $min; $i <= $max; $i++) {
                $swingList = array($swing => $i);
                $this->assertTrue($this->object->set_swingValue($swingList));
                $this->assertEquals($this->object->swingValue, $i);
                $this->assertEquals($this->object->max, $i);

            }
        }


        $this->object->init("X");

        // check error checking
        $swingList = array();
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array("R" => 12);
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array("R" => 12,
                           "S" => 10,
                           "U" => 15,
                           "Y" => 1,
                           "Z" => 30);
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array("X" => 3);
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array("X" => 21);
        $this->assertFalse($this->object->set_swingValue($swingList));

        // needle in a haystack
        $swingList = array("R" => 12,
                           "S" => 10,
                           "X" => 15,
                           "Y" => 1,
                           "Z" => 30);

        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 15);
        $this->assertEquals($this->object->max, 15);

        // correct behavior as dice split in half
        $this->object->split();
        $this->assertEquals($this->object->swingValue, 15);
        $this->assertEquals($this->object->max, 8);

        // persist across value change
        $swingList = array("X" => 20);
        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 20);
        $this->assertEquals($this->object->max, 10);

        $swingList = array("X" => 5);
        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 5);
        $this->assertEquals($this->object->max, 3);

        // remainder preservation?
        $swingList = array("X" => 13);
        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 13);
        $this->assertEquals($this->object->max, 7);

        // multiple splits
        $this->object->split();
        $swingList = array("X" => 20);
        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 20);
        $this->assertEquals($this->object->max, 5);

        $swingList = array("X" => 19);
        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 19);
        $this->assertEquals($this->object->max, 5);

        $swingList = array("X" => 4);
        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 4);
        $this->assertEquals($this->object->max, 1);

        $swingList = array("X" => 5);
        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 5);
        $this->assertEquals($this->object->max, 2);

        // Do we proprly lose the remainder now?
        $this->object->split();
        $swingList = array("X" => 20);
        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->swingValue, 20);
        $this->assertEquals($this->object->max, 2);
    }

    /**
     * @depends testInit
     * @depends testActivate
     * @depends testSet_swingValue
     * @covers BMDieSwing::roll
     */
    public function testRoll() {

        // testing whether it calls the appropriate methods in BMGame

        $this->object->init("X");

        // needs a value, hasn't requested one. Should call
        // request_swing_values before calling require_values
        $game = new TestDummyGame;

        $this->object->ownerObject = $game;

        $ex = FALSE;
        try {
            $this->object->roll(FALSE);
        } catch (Exception $e) {
            $ex = TRUE;
        }

        $this->assertTrue($ex, "dummy require_values not called.");
        $this->assertNotEmpty($game->swingrequest);

        // Once activated, it should have requested a value, but still
        // needs one.
        // Calls require_values without calling request_swing_values

        $this->object->init("X");

        $game = new TestDummyGame;
        $this->object->ownerObject = $game;

        $this->object->activate("player");
        $newDie = $game->dice[0][1];

        $this->assertNotNull($game->swingrequest);
        $game->swingrequest = array();

        $ex = FALSE;
        try {
            $newDie->roll(FALSE);
        } catch (Exception $e) {
            $ex = TRUE;
        }

        $this->assertTrue($ex, "dummy require_values not called.");
        $this->assertEmpty($game->swingrequest);

        // And if the swing value is set, it won't call require_values
        $this->object->init("X");

        $game = new TestDummyGame;
        $this->object->ownerObject = $game;

        $this->object->activate("player");
        $newDie = $game->dice[0][1];

        $this->assertNotNull($game->swingrequest);
        $game->swingrequest = array();

        $newDie->set_swingValue(array("X" => "15"));

        // it hasn't rolled yet
        $this->assertFalse(is_numeric($newDie->value));


        $ex = FALSE;
        try {
            $newDie->roll(FALSE);
        } catch (Exception $e) {
            $ex = TRUE;
        }
        $this->assertFalse($ex, "dummy require_values was called.");
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

        $this->object->init("X");
        $this->object->ownerObject = new BMGame;

        // needs a value, hasn't requested one. Should call
        // request_swing_values before calling require_values

        $ex = FALSE;
        try {
            $this->object->roll(FALSE);
        } catch (Exception $e) {
            $ex = TRUE;
        }

        $this->assertTrue($ex, "dummy require_values not called.");
//        $this->assertNotEmpty($game->swingrequest);

        // Once activated, it should have requested a value, but still
        // needs one.
        // Calls require_values without calling request_swing_values

        $this->object->init("X");

        $game = new BMGame;
        $game->activeDieArrayArray = array(array(), array());
        $this->object->ownerObject = $game;

        $this->object->activate(0);

        $this->assertNotNull($game->swingRequestArrayArray);
//        $game->swingRequestArrayArray = array(array(), array();

        $ex = FALSE;
        try {
            $game->activeDieArrayArray[0][0]->roll(FALSE);
        } catch (Exception $e) {
            $ex = TRUE;
        }

        $this->assertTrue($ex, "dummy require_values not called.");
        $this->assertEmpty($game->swingrequest);

        // And if the swing value is set, it won't call require_values
        $this->object->init("X");

        $game = new BMGame;
        $game->activeDieArrayArray = array(array(), array());
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 0;

        $this->object->activate();

        $this->assertNotNull($game->swingRequestArrayArray);
        $game->swingrequest = array();

        $game->activeDieArrayArray[0][0]->set_swingValue(array("X" => "15"));

        // it hasn't rolled yet
        $this->assertFalse(is_numeric($game->activeDieArrayArray[0][0]->value));

        $ex = FALSE;
        try {
            $game->activeDieArrayArray[0][0]->roll(FALSE);
        } catch (Exception $e) {
            $ex = TRUE;
        }
        $this->assertFalse($ex, "dummy require_values was called.");

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

        $this->object->init("X");
        $this->object->ownerObject = $game;

        $this->object->activate("player");
        $newDie = $game->dice[0][1];

        // No value yet set. It will call game->require_values()

        $ex = FALSE;
        try {
            $newDie->make_play_die();
        } catch (Exception $e) {
            $ex = TRUE;
        }

        $this->assertTrue($ex, "require_values not called.");

        // If it doesn't need a value, it won't
        $newDie->set_swingValue(array("X" => "11"));

        // newDie shouldn't have a value yet
        $this->assertFalse(is_numeric($newDie->value));

        $ex = FALSE;
        try {
            $rolledDie = $newDie->make_play_die();
        } catch (Exception $e) {
            $ex = TRUE;
        }

        $this->assertFalse($ex, "require_values called.");

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

        $this->object->init("X");
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 1;

        $this->object->activate();
        $newDie = $game->activeDieArrayArray[1][0];

        // No value yet set. It will call game->require_values()

        $ex = FALSE;
        try {
            $newDie->make_play_die();
        } catch (Exception $e) {
            $ex = TRUE;
        }

        $this->assertTrue($ex, "require_values not called.");

        // If it doesn't need a value, it won't
        $newDie->set_swingValue(array("X" => "11"));

        // newDie shouldn't have a value yet
        $this->assertFalse(is_numeric($newDie->value));

        $ex = FALSE;
        try {
            $rolledDie = $newDie->make_play_die();
        } catch (Exception $e) {
            $ex = TRUE;
        }

        $this->assertFalse($ex, "require_values called.");

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
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );

    }

}
