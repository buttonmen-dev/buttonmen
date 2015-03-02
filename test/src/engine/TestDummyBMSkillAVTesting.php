<?php

class TestDummyBMSkillAVTesting extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array("assist_values");

    public static function assist_values($args) {
        $args['possibleAssistValues'] = array(-1, 1);
    }
}
