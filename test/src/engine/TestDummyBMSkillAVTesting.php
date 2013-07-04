<?php

require_once "engine/BMDie.php";
require_once "engine/BMSkill.php";
require_once "engine/BMContainer.php";
require_once "engine/BMAttack.php";
require_once "engine/BMAttackSkill.php";

class BMSkillAVTesting extends BMSkill {
    public static $hooked_methods = array("assist_values");

    public static function assist_values($args) {
        $args[3] = array(-1, 1);
    }
}

?>
