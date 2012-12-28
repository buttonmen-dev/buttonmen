<?php

require_once "engine/BMDie.php";
require_once "testdummies.php";


class BMSwingDieTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSwingDie
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new BMSwingDie;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

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

        $this->object->init("Z", array("Testing2"));

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
     */
    public function testCreate() {

        // Test all valid swing types
        foreach (str_split("RSTUVWXYZ") as $swing) {
            $die = BMSwingDie::create($swing, array());

            $this->assertInstanceOf('BMDie', $die);
            $this->assertInstanceOf('BMSwingDie', $die);
            $this->assertEquals($swing, $die->swingType);
        }


        // try some invalid types
        foreach (str_split("rstuvwxyzaQ") as $swing) {
            $fail = FALSE;
            try {
                $die = BMSwingDie::create($swing, array());
            } catch (UnexpectedValueException $e) {
                $fail = TRUE;
            }
            $this->assertTrue($fail, "Creating with bad swing type '$swing' didn't throw an exception.");

        }

        $fail = FALSE;

        // try some more bad values
        try {
            $die = BMSwingDie::create(6, array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating with bad swing type didn't throw an exception.");
        $fail = FALSE;

        try {
            $die = BMSwingDie::create("RT", array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating with bad swing type didn't throw an exception.");
        $fail = FALSE;

        try {
            $die = BMSwingDie::create("0.A", array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }
        $this->assertTrue($fail, "Creating with bad swing type didn't throw an exception.");
        $fail = FALSE;


    }

    /**
     * @depends testInit
     */

    public function testActivate () {
        $game = new DummyGame;
        foreach (str_split("RSTUVWXYZ") as $swing) {
            $this->object->init($swing);

            $newDie = $this->object->activate($game, "player");

            $this->assertFalse($newDie === $this->object);
            $this->assertTrue($newDie->game === $game);
            $this->assertTrue($newDie->owner === "player");

            $this->assertTrue($game->swingrequest[0] === $newDie);
            $this->assertEquals($game->swingrequest[1], $swing);
        }

    }

    /**
     * @depends testInit
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
     * @depends testSet_swingValue
     */
    public function testRoll() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );

    }


    /**
     * @depends testSet_swingValue
     * @depends testRoll
     */
    public function testFirst_roll() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );

    }

    public function testDescribe() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );

    }

}
