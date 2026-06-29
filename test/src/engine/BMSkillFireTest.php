<?php

class BMSkillFireTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillFire
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void {
        $this->object = new BMSkillFire;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void {
    }

    /**
     * @covers BMSkillFire::attack_list
     */
    public function testAttack_list()
    {
        // check for graceful failure
        $this->object->attack_list(NULL);

        // Test Power removal
        $a = array('Power' => 'Power', 'Skill' => 'Skill');
        $b = array('attackTypeArray' => &$a);

        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertNotContains('Power', $a);
        // Check proper behavior not disrupted when removing Power
        $this->assertEquals(1, count($a));
        $this->assertContains('Skill', $a);

        // Check removing Power from the middle of longer lists
        $a = array('Speed' => 'Speed',
                   'Trip'  => 'Trip',
                   'Power' => 'Power',
                   'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertNotContains('Power', $a);
        $this->assertEquals(3, count($a));
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

    /**
     * @covers BMSkillFire::assist_values
     */
    public function testAssist_values_Wildcard() {
        // we need a whole BMGame because the information about the deck
        // is held at the BMPlayer level
        $game = new BMGame;
        $die = BMDie::create_from_recipe('F(C)');
        $die->playerIdx = 1;
        $die->ownerObject = $game;

        $die->set_value(1);
        $helpVals = array();
        $par = array('attackType'           => 'Power',
                     'assistingDie'         => $die,
                     'possibleAssistValues' => $helpVals);
        BMSkillFire::assist_values($par);
        $this->assertEquals(array(), $par['possibleAssistValues']);

        $die->set_value(13);
        $helpVals = array();
        $par = array('attackType'           => 'Power',
                     'assistingDie'         => $die,
                     'possibleAssistValues' => $helpVals);
        BMSkillFire::assist_values($par);
        $this->assertEquals(range(1, 12), $par['possibleAssistValues']);

        $die->set_value(20);
        $helpVals = array();
        $par = array('attackType'           => 'Power',
                     'assistingDie'         => $die,
                     'possibleAssistValues' => $helpVals);
        BMSkillFire::assist_values($par);
        $this->assertEquals(array(7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19), $par['possibleAssistValues']);
    }
}
