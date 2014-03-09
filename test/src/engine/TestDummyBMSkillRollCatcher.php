<?php

class TestDummyBMSkillRollCatcher extends BMSkill {
    public static $hooked_methods = array("roll");

    public static function roll($args) {
        throw new Exception("roll called");
    }
}

