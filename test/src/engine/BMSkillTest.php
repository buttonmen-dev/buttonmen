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

        // check that the comparator works in the correct direction
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillDoppleganger',
                                            'BMSkillNull'));

        $this->assertEquals(1,
            BMSkill::skill_order_comparator('BMSkillNull',
                                            'BMSkillDoppleganger'));

        // check that unknown skills are applied last
        $this->assertEquals(1,
            BMSkill::skill_order_comparator('Test',
                                            'BMSkillDoppleganger'));

        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillDoppleganger',
                                            'Test'));

        // check specific orderings of skills
        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillDoppleganger',
                                            'BMSkillValue'));

        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillShadow',
                                            'BMSkillStealth'));

        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillQueer',
                                            'BMSkillStealth'));

        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillSpeed',
                                            'BMSkillStealth'));

        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillBerserk',
                                            'BMSkillStealth'));

        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillTrip',
                                            'BMSkillStealth'));
    }
}

?>
