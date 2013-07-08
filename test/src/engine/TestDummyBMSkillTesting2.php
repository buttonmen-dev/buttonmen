<?php

class TestDummyBMSkillTesting2 extends BMSkill {
    public static $hooked_methods = array("test");

    public static function test($args) {
        $args[0] .= "still testing";
    }
}

?>
