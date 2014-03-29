<?php

class BMSkillMoodTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillMood
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillMood;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillMood::pre_roll
     */
    public function testPre_roll_bad_args() {
        $args = NULL;
        $this->assertFalse(BMSkillMood::pre_roll($args));
    }

    /**
     * @covers BMSkillMood::pre_roll
     */
    public function testPre_roll_non_swing() {
        $die = BMDie::create(6);
        $args = array('die' => $die,
                      'isTriggeredByAttack' => TRUE);
        $this->assertFalse(BMSkillMood::pre_roll($args));

        $die->swingType = 'X';
        $args = array('die' => $die,
                      'isTriggeredByAttack' => TRUE);
        try {
            BMSkillMood::pre_roll($args);
            fail('Mood should throw exception with a non-valid swing die.');
        } catch (LogicException $e) {
        }
    }

    /**
     * @covers BMSkillMood::pre_roll
     */
    public function testPre_roll_swing_non_attack() {
        $die = BMDie::create_from_recipe('(X)');
        $args = array('die' => $die,
                      'isTriggeredByAttack' => FALSE);
        $this->assertFalse(BMSkillMood::pre_roll($args));
    }

    /**
     * @covers BMSkillMood::pre_roll
     */
    public function testPre_roll_swing_attack() {
        $die = BMDie::create_from_recipe('(V)');
        $this->assertInstanceOf('BMDieSwing', $die);

        $die->set_swingValue(array('V' => 6));
        $args = array('die' => $die,
                      'isTriggeredByAttack' => TRUE);
        $this->assertTrue(BMSkillMood::pre_roll($args));

        $nRep = 100;
        $maxArray = array_fill(1, $nRep, 0);
        for ($idx = 1; $idx <= $nRep; $idx++) {
            $die->set_swingValue(array('V' => 6));
            $args = array('die' => $die,
                          'isTriggeredByAttack' => TRUE);
            BMSkillMood::pre_roll($args);
            $maxArray[$idx] = $die->max;
        }

        $this->assertNotEquals(min($maxArray), max($maxArray));
        $this->assertEquals(6, min($maxArray));
        $this->assertEquals(12, max($maxArray));
    }

    /**
     * @covers BMSkillMood::pre_roll
     */
    public function testPre_roll_twin_swing_attack() {
        $die = BMDie::create_from_recipe('(V,V)');
        $this->assertInstanceOf('BMDieTwin', $die);
        $this->assertInstanceOf('BMDieSwing', $die->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $die->dice[1]);

        $die->set_swingValue(array('V' => 6));
        $args = array('die' => $die,
                      'isTriggeredByAttack' => TRUE);
        $this->assertTrue(BMSkillMood::pre_roll($args));
        $this->assertEquals($die->dice[0]->max, $die->dice[1]->max);

        $nRep = 100;
        $maxArray = array_fill(1, $nRep, 0);
        for ($idx = 1; $idx <= $nRep; $idx++) {
            $die->set_swingValue(array('V' => 6));
            $args = array('die' => $die,
                          'isTriggeredByAttack' => TRUE);
            BMSkillMood::pre_roll($args);
            $maxArray[$idx] = $die->max;
        }

        $this->assertNotEquals(min($maxArray), max($maxArray));
        $this->assertEquals(12, min($maxArray));
        $this->assertEquals(24, max($maxArray));

    }
}
