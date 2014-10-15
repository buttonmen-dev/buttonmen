<?php

class BMSkillTest extends PHPUnit_Framework_TestCase {

    /**
     * BMSkill doesn't use any native objects, so no setup is required
     */

    public function test_expand_skill_string() {
        $result = BMSkill::expand_skill_string('ps');
        $this->assertEquals($result, array('Poison', 'Shadow'));
    }

    public function test_unimplemented_skill_in_string() {
        $result = BMSkill::unimplemented_skill_in_string('');
        $this->assertFalse($result);
        $result = BMSkill::unimplemented_skill_in_string('ps');
        $this->assertFalse($result);
        $result = BMSkill::unimplemented_skill_in_string('bps');
        $this->assertTrue($result);
    }

    public function test_skill_order_comparator() {
        // check equality
        $this->assertEquals(0,
            BMSkill::skill_order_comparator('BMSkillPoison',
                                            'BMSkillPoison'));

        // check that unknown skills are applied last
        $this->assertEquals(1,
            BMSkill::skill_order_comparator('Test',
                                            'BMSkillDoppelganger'));

        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillDoppelganger',
                                            'Test'));
    }

    public function test_attack_list_order() {
        // skills that set attack types must come before skills that
        // unset attack types
        //
        // skills that only set attack types:
        //   Speed, Trip
        //
        // Queer sets Power or Skill, unsets Power or Skill
        //
        // Shadow sets Skill, unsets Power
        //
        // Berserk sets Berserk, unsets Skill
        //
        // skills that only unset attack types:
        //   Fire, Konstant, Stealth

        // Speed
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillQueer'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillShadow'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillBerserk'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillFire'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillKonstant'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillStealth'));

        // Trip
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillQueer'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillShadow'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillBerserk'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillFire'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillKonstant'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillStealth'));

        // Queer
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillQueer',
                                            'BMSkillBerserk'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillQueer',
                                            'BMSkillFire'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillQueer',
                                            'BMSkillKonstant'));

        // current behaviour is that queer shadow dice cannot ever perform
        // power attacks, thus resolve Queer before Shadow
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillQueer',
                                            'BMSkillShadow'));

        // Shadow
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillShadow',
                                            'BMSkillFire'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillShadow',
                                            'BMSkillKonstant'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillShadow',
                                            'BMSkillStealth'));

        // Berserk
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillBerserk',
                                            'BMSkillFire'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillBerserk',
                                            'BMSkillKonstant'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillBerserk',
                                            'BMSkillStealth'));

        // Fire
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillFire'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillFire'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillQueer',
                                            'BMSkillFire'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillShadow',
                                            'BMSkillFire'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillBerserk',
                                            'BMSkillFire'));

        // Konstant
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillKonstant'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillKonstant'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillQueer',
                                            'BMSkillKonstant'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillShadow',
                                            'BMSkillKonstant'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillBerserk',
                                            'BMSkillKonstant'));

        // Stealth
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillStealth'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillStealth'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillQueer',
                                            'BMSkillStealth'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillShadow',
                                            'BMSkillStealth'));
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillBerserk',
                                            'BMSkillStealth'));
    }

    public function test_attack_values_order() {
        // Konstant has to fire late, since it takes all possible
        // attack values and then allows also the negative of them
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillStinger',
                                            'BMSkillKonstant'));
    }

    public function test_capture_order() {
        // berserk occurs before radioactive
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillBerserk',
                                            'BMSkillRadioactive'));

        // morphing occurs before radioactive
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillMorphing',
                                            'BMSkillRadioactive'));

        // radioactive occurs before doppelganger
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillRadioactive',
                                            'BMSkillDoppelganger'));

// james: these tests need to be reactivated when Mighty and Weak have been
//        implemented
//        
//        // radioactive occurs before mighty
//        $this->assertEquals(-1,
//            BMSkill::skill_order_comparator('BMSkillRadioactive',
//                                            'BMSkillMighty'));
//
//        // radioactive occurs before weak
//        $this->assertEquals(-1,
//            BMSkill::skill_order_comparator('BMSkillRadioactive',
//                                            'BMSkillWeak'));
    }

    public function test_describe() {
        $desc = BMSkill::describe('Stinger');
        $this->assertEquals($desc['code'], 'g',
            'describe() sets the correct one-letter code for a skill type');
        $this->assertTrue(array_key_exists('Shadow', $desc['interacts']),
            'When no interaction list is specified, the description of a known interaction is returned');

        $desc = BMSkill::describe('Stinger', array('Berserk', 'Shadow', 'Stinger', 'Value'));
        $this->assertTrue(array_key_exists('Shadow', $desc['interacts']),
            'When an interaction list containing a known interaction is specified, the description of that interaction is returned');

        $desc = BMSkill::describe('Stinger', array('Berserk', 'Stinger', 'Value'));
        $this->assertFalse(array_key_exists('Shadow', $desc['interacts']),
            'When an interaction list without a given known interaction is specified, the description of that interaction is not returned');
    }
}
