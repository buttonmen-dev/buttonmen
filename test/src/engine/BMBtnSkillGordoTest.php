<?php

class BMBtnSkillGordoTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers BMBtnSkillGordo::are_unique_swing_values_valid
     */
    public function testAre_unique_swing_values_valid() {
        $die1 = BMDieSwing::create('V');
        $die2 = BMDieSwing::create('W');
        $die3 = BMDieSwing::create('X');
        $die4 = BMDieSwing::create('Y');
        $die5 = BMDieSwing::create('Z');

        $die6 = BMDie::create(18);

        // test with swing dice only and repeated swing values
        $retVal = BMBtnSkillGordo::are_unique_swing_values_valid(array(
            'activeDieArray' => array($die1, $die2, $die3, $die4, $die5),
            'swingValueArray' => array(
                'V' => 6,
                'W' => 4,
                'X' => 4,
                'Y' => 1,
                'Z' => 4
            )
        ));
        $this->assertFalse($retVal['are_unique_swing_values_valid']);

        // test with swing dice only and no repeated swing values
        $retVal = BMBtnSkillGordo::are_unique_swing_values_valid(array(
            'activeDieArray' => array($die1, $die2, $die3, $die4, $die5),
            'swingValueArray' => array(
                'V' => 6,
                'W' => 4,
                'X' => 5,
                'Y' => 1,
                'Z' => 18
            )
        ));
        $this->assertTrue($retVal['are_unique_swing_values_valid']);

        // test with swing dice and an 18-sider and repeated swing values
        $retVal = BMBtnSkillGordo::are_unique_swing_values_valid(array(
            'activeDieArray' => array($die1, $die2, $die3, $die4, $die5, $die6),
            'swingValueArray' => array(
                'V' => 6,
                'W' => 4,
                'X' => 4,
                'Y' => 1,
                'Z' => 4
            )
        ));
        $this->assertFalse($retVal['are_unique_swing_values_valid']);

        // test with swing dice and an 18-sider and a swing value that is 18
        $retVal = BMBtnSkillGordo::are_unique_swing_values_valid(array(
            'activeDieArray' => array($die1, $die2, $die3, $die4, $die5, $die6),
            'swingValueArray' => array(
                'V' => 6,
                'W' => 4,
                'X' => 5,
                'Y' => 1,
                'Z' => 18
            )
        ));
        $this->assertFalse($retVal['are_unique_swing_values_valid']);

        // test with swing dice and an 18-sider and no repeated swing values
        $retVal = BMBtnSkillGordo::are_unique_swing_values_valid(array(
            'activeDieArray' => array($die1, $die2, $die3, $die4, $die5, $die6),
            'swingValueArray' => array(
                'V' => 6,
                'W' => 4,
                'X' => 5,
                'Y' => 1,
                'Z' => 7
            )
        ));
        $this->assertTrue($retVal['are_unique_swing_values_valid']);
    }

    /**
     * @covers BMBtnSkillGordo::can_button_add_this_aux_die
     */
    public function testCan_button_add_this_aux_die() {
        $die1 = BMDie::create(6);
        $die2 = BMDieSwing::create('X');
        $die3 = BMDieSwing::create('R');

        $retVal = BMBtnSkillGordo::can_button_add_this_aux_die(array('die' => $die1));
        $this->assertTrue($retVal['can_button_add_this_aux_die']);

        $retVal = BMBtnSkillGordo::can_button_add_this_aux_die(array('die' => $die2));
        $this->assertFalse($retVal['can_button_add_this_aux_die']);

        $retVal = BMBtnSkillGordo::can_button_add_this_aux_die(array('die' => $die3));
        $this->assertTrue($retVal['can_button_add_this_aux_die']);
    }

    /**
     * @covers BMBtnSkillGordo::unique_swing_setting_fail_message
     */
    public function testUnique_swing_setting_fail_message() {
        $retVal = BMBtnSkillGordo::unique_swing_setting_fail_message(array());
        $this->assertEquals(
            'Cannot have multiple dice with the same size',
            $retVal['unique_swing_setting_fail_message']
        );
    }
}
