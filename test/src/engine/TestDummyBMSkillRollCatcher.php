<?php

require_once "engine/BMDie.php";
require_once "engine/BMSkill.php";
require_once "engine/BMContainer.php";
require_once "engine/BMAttack.php";
require_once "engine/BMAttackSkill.php";

class TestDummyBMSkillRollCatcher extends BMSkill {
    public static $hooked_methods = array("roll");

    public static function roll($args) {
        throw new Exception("roll called");
    }
}

?>
