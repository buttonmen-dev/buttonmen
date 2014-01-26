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
        $this->assertEquals(0,
            BMSkill::skill_order_comparator('BMSkillPoison',
                                            'BMSkillPoison'));

        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillDoppleganger',
                                            'BMSkillNull'));

        $this->assertEquals(1,
            BMSkill::skill_order_comparator('BMSkillNull',
                                            'BMSkillDoppleganger'));

        $this->assertEquals(1,
            BMSkill::skill_order_comparator('Test',
                                            'BMSkillDoppleganger'));

        $this->assertEquals(-1,
            BMSkill::skill_order_comparator('BMSkillDoppleganger',
                                            'Test'));
    }
}

?>
