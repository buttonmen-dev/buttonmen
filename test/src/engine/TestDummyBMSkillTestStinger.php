<?php

require_once "engine/BMDie.php";
require_once "engine/BMSkill.php";
require_once "engine/BMContainer.php";
require_once "engine/BMAttack.php";
require_once "engine/BMAttackSkill.php";

class TestDummyBMSkillTestStinger extends BMSkill {
    public static $hooked_methods = array("attack_values");

    public static function attack_values($args) {
        $alist = &$args[1];

        for ($i = $alist[0] - 1; $i > 0; $i--) {
            $alist[] = $i;
        }
    }
}

?>
