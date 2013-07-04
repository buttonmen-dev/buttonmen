<?php

require_once "engine/BMDie.php";
require_once "engine/BMSkill.php";
require_once "engine/BMContainer.php";
require_once "engine/BMAttack.php";
require_once "engine/BMAttackSkill.php";

class BMSkillCaptureCatcher extends BMSkill {
    public static $hooked_methods = array("capture", "be_captured");

    public static function capture($args) {
        throw new Exception("capture called");
    }

    public static function be_captured($args) {
        throw new Exception("be_captured called");
    }

}

?>
