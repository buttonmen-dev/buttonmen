<?php

class BMSkillFireTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillFire
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new BMSkillFire;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     * @covers BMSkillFire::assist_values
     */
    public function testAssist_values() {
        // test with invalid input parameters
        $par = 'hello';
        BMSkillFire::assist_values($par);

        $par = array(1, 2);
        BMSkillFire::assist_values($par);

        // test for die without Fire
        $die1 = BMDie::create(6);
        $die1->value = 2;
        $helpVals = array();
        $par = array('attackType'           => 'Power',
                     'assistingDie'         => $die1,
                     'possibleAssistValues' => $helpVals);
        BMSkillFire::assist_values($par);
        $this->assertEquals(array(), $par['possibleAssistValues']);

        // test for die with Fire that cannot be turned down
        $die2 = BMDie::create(4);
        $die2->value = 1;
        $die2->add_skill('Fire');
        $helpVals = array();
        $par = array('attackType'           => 'Power',
                     'assistingDie'         => $die2,
                     'possibleAssistValues' => $helpVals);
        BMSkillFire::assist_values($par);
        $this->assertEquals(array(), $par['possibleAssistValues']);

        // test for die with Fire that can be turned down
        $die3 = BMDie::create(4);
        $die3->value = 3;
        $die3->add_skill('Fire');

        // with invalid attack type
        $helpVals = array();
        $par = array('attackType'           => 'Shadow',
                     'assistingDie'         => $die3,
                     'possibleAssistValues' => $helpVals);
        BMSkillFire::assist_values($par);
        $this->assertEquals(array(), $par['possibleAssistValues']);

        // with valid attack types
        $helpVals = array();
        $par = array('attackType'           => 'Power',
                     'assistingDie'         => $die3,
                     'possibleAssistValues' => $helpVals);
        BMSkillFire::assist_values($par);
        $this->assertEquals(array(1, 2), $par['possibleAssistValues']);

        $helpVals = array();
        $par = array('attackType'           => 'Skill',
                     'assistingDie'         => $die3,
                     'possibleAssistValues' => $helpVals);
        BMSkillFire::assist_values($par);
        $this->assertEquals(array(1, 2), $par['possibleAssistValues']);
    }
}
