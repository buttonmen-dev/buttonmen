<?php

class TestDummyBMSkillAVTesting extends BMSkill {
    public static $hooked_methods = array("assist_values");

    public static function assist_values($args) {
        $args[3] = array(-1, 1);
    }
}

?>
