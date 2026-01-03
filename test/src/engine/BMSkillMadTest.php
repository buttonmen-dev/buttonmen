<?php

class BMSkillMadTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillMad
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillMad;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillMad::pre_roll
     */
    public function testPre_roll_swing_attack() {
        $die = BMDie::create_from_recipe('(X)');
        $this->assertInstanceOf('BMDieSwing', $die);

        $die->set_swingValue(array('X' => 6));
        $args = array('die' => $die);
        // check that no resizing occurs when the die has no value
        $this->assertFalse(BMSkillMad::pre_roll($args));
        $this->assertEquals(6, $die->max);
        $die->value = 2;
        // check that resizing can occur when the die has a value
        $this->assertTrue(BMSkillMad::pre_roll($args));

        $nRep = 200;
        $maxArray = array_fill(1, $nRep, 0);
        for ($idx = 1; $idx <= $nRep; $idx++) {
            $die->set_swingValue(array('X' => 6));
            $die->value = 2;
            $args = array('die' => $die);
            BMSkillMad::pre_roll($args);
            $maxArray[$idx] = $die->max;
        }

        $counts = array_count_values($maxArray);
        $values = array_keys($counts);
        asort($values);
        // reset keys
        $values = array_values($values);

        $this->assertEquals(array(4, 6, 8, 10, 12, 14, 16, 18, 20), $values);
    }

    /**
     * @covers BMSkillMad::pre_roll
     */
    public function testPre_roll_twin_swing_attack() {
        $die = BMDie::create_from_recipe('(X,X)');
        $this->assertInstanceOf('BMDieTwin', $die);
        $this->assertInstanceOf('BMDieSwing', $die->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $die->dice[1]);

        $die->set_swingValue(array('X' => 6));
        $args = array('die' => $die);
        // check that no resizing occurs when the die has no value
        $this->assertFalse(BMSkillMad::pre_roll($args));
        $this->assertEquals(12, $die->max);
        $die->dice[0]->value = 1;
        $die->dice[1]->value = 1;
        $die->value = 2;
        // check that resizing can occur when the die has a value
        $this->assertTrue(BMSkillMad::pre_roll($args));
        $this->assertEquals($die->dice[0]->max, $die->dice[1]->max);

        $nRep = 100;
        $maxArray = array_fill(1, $nRep, 0);
        for ($idx = 1; $idx <= $nRep; $idx++) {
            $die->set_swingValue(array('V' => 6));
            $die->dice[0]->value = 1;
            $die->dice[1]->value = 1;
            $die->value = 2;
            $args = array('die' => $die);
            BMSkillMad::pre_roll($args);
            $maxArray[$idx] = $die->max;
        }

        $counts = array_count_values($maxArray);
        $values = array_keys($counts);
        asort($values);
        // reset keys
        $values = array_values($values);

        $this->assertEquals(array(8, 12, 16, 20, 24, 28, 32, 36, 40), $values);
    }

    /**
     * @covers BMSkillMad::valid_die_sizes
     */
    public function testValid_die_sizes() {
        $this->assertEquals(array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30),
                            BMSkillMad::valid_die_sizes(array(1, 30)));

        $this->assertEquals(array(4, 6, 8, 10, 12, 14, 16, 18, 20),
                            BMSkillMad::valid_die_sizes(array(4, 20)));
    }
}
