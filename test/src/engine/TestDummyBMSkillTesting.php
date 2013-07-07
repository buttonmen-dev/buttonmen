<?php

// well defined skill classes with which to test
class TestDummyBMSkillTesting extends BMSkill {
    public static $hooked_methods = array("test");

    public static function test($args) {
        $args[0] .= "testing";
    }
}

?>
