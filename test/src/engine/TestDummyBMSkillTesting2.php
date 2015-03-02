<?php

class TestDummyBMSkillTesting2 extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array("test");

    public static function test($args) {
        $args[0] .= "still testing";
    }
}
