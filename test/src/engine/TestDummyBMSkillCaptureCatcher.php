<?php

class TestDummyBMSkillCaptureCatcher extends BMSkill {
    public static $hooked_methods = array("capture", "be_captured");

    public static function capture($args) {
        throw new Exception("capture called");
    }

    public static function be_captured($args) {
        throw new Exception("be_captured called");
    }

}

?>
