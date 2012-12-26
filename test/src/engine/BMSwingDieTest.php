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

    public function testInit () {
        $this->object->init("X", array());

        $this->assertEquals($this->object->min, 1);
        $this->assertEquals($this->object->swingType, "X");

        $this->assertFalse($this->object->has_skill("Testing"));
        $this->assertFalse($this->object->has_skill("Testing2"));

        $this->object->init("Z", array("Testing2"));

        $this->assertEquals($this->object->min, 1);
        $this->assertEquals($this->object->swingType, "Z");


        $this->assertTrue($this->object->has_skill("Testing2"));
        $this->assertFalse($this->object->has_skill("Testing"));

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

    public function testActivate () {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );

    }

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

    public function testSplit() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );

    }

    public function testSet_swingValue() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );

    }

}
