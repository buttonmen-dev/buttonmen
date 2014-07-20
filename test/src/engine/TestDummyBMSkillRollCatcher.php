<?php

class TestDummyBMSkillRollCatcher extends BMSkill {
    public static $hooked_methods = array("pre_roll");

    public static function pre_roll($args) {
        throw new Exception("roll called");
    }
}

