<?php

require_once "engine/BMDie.php";
require_once "engine/BMSkill.php";
require_once "engine/BMContainer.php";
require_once "engine/BMAttack.php";
require_once "engine/BMAttackSkill.php";

class BMSkillTesting2 extends BMSkill {
    public static $hooked_methods = array("test");

    public static function test($args) {
        $args[0] .= "still testing";
    }
}

?>
