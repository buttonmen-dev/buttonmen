<?php

class BMSkillMoodTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillMood
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillMood;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
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
        $args = array('die' => $die);
        $this->assertFalse(BMSkillMood::pre_roll($args));
    }

    /**
     * @covers BMSkillMood::pre_roll
     */
    public function testPre_roll_swing_non_attack() {
        $die = BMDie::create_from_recipe('(X)');
        $args = array('die' => $die);
        $this->assertFalse(BMSkillMood::pre_roll($args));
    }

    /**
     * @covers BMSkillMood::pre_roll
     */
    public function testPre_roll_swing_attack() {
        $die = BMDie::create_from_recipe('(V)');
        $this->assertInstanceOf('BMDieSwing', $die);

        $die->set_swingValue(array('V' => 6));
        $args = array('die' => $die);
        // check that no resizing occurs when the die has no value
        $this->assertFalse(BMSkillMood::pre_roll($args));
        $this->assertEquals(6, $die->max);
        $die->value = 2;
        // check that resizing can occur when the die has a value
        $this->assertTrue(BMSkillMood::pre_roll($args));

        $nRep = 100;
        $maxArray = array_fill(1, $nRep, 0);
        for ($idx = 1; $idx <= $nRep; $idx++) {
            $die->set_swingValue(array('V' => 6));
            $die->value = 2;
            $args = array('die' => $die);
            BMSkillMood::pre_roll($args);
            $maxArray[$idx] = $die->max;
        }

        $counts = array_count_values($maxArray);
        $values = array_keys($counts);
        asort($values);
        // reset keys
        $values = array_values($values);

        $this->assertEquals(array(6, 8, 10, 12), $values);
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
        $args = array('die' => $die);
        // check that no resizing occurs when the die has no value
        $this->assertFalse(BMSkillMood::pre_roll($args));
        $this->assertEquals(12, $die->max);
        $die->dice[0]->value = 1;
        $die->dice[1]->value = 1;
        $die->value = 2;
        // check that resizing can occur when the die has a value
        $this->assertTrue(BMSkillMood::pre_roll($args));
        $this->assertEquals($die->dice[0]->max, $die->dice[1]->max);

        $nRep = 100;
        $maxArray = array_fill(1, $nRep, 0);
        for ($idx = 1; $idx <= $nRep; $idx++) {
            $die->set_swingValue(array('V' => 6));
            $die->dice[0]->value = 1;
            $die->dice[1]->value = 1;
            $die->value = 2;
            $args = array('die' => $die);
            BMSkillMood::pre_roll($args);
            $maxArray[$idx] = $die->max;
        }

        $counts = array_count_values($maxArray);
        $values = array_keys($counts);
        asort($values);
        // reset keys
        $values = array_values($values);

        $this->assertEquals(array(12, 16, 20, 24), $values);
    }

    public function testPre_roll_with_konstant() {
        $die = BMDie::create_from_recipe('k(V)');
        $this->assertInstanceOf('BMDieSwing', $die);
        $this->assertTrue($die->has_skill('Konstant'));

        $die->set_swingValue(array('V' => 6));
        $args = array('die' => $die);
        // check that no resizing occurs when the die has no value
        $this->assertFalse(BMSkillMood::pre_roll($args));
        $this->assertEquals(6, $die->max);
        $die->value = 2;
        // check that no resizing occurs even when the die has a value
        $this->assertFalse(BMSkillMood::pre_roll($args));
        $this->assertEquals(6, $die->max);
        $die->value = 2;
    }

    public function testPre_add_skill_with_mood_no_swing() {
        $die = BMDie::create_from_recipe('(6)?');
        $this->assertTrue($die->has_skill('Mood'));
        $this->assertEquals('(6)?', $die->recipe);
    }

    public function testPre_add_skill_with_mood_and_swing() {
        $die = BMDie::create_from_recipe('(X)?');
        $this->assertTrue($die->has_skill('Mood'));
        $this->assertEquals('(X)?', $die->recipe);
    }

    /**
     * @covers BMSkillMood::valid_die_sizes
     */
    public function testValid_die_sizes() {
        $this->assertEquals(array(1, 2, 4, 6, 8, 10, 12, 20, 30),
                            BMSkillMood::valid_die_sizes(array(1, 30)));

        $this->assertEquals(array(4, 6, 8, 10, 12, 20),
                            BMSkillMood::valid_die_sizes(array(4, 20)));
    }
}
