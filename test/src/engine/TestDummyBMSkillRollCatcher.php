<?php

class TestDummyBMSkillRollCatcher extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array("pre_roll");

    public static function pre_roll($args) {
        throw new Exception("roll called");
    }
}

