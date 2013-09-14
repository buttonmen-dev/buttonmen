<?php

class BMSkillPoison extends BMSkill {

    public static $name = "Poison";
    public static $abbrev = "p";

    public static $hooked_methods = array("scoreValue");

    // the arguments expected are:
    //   &$this->scoreValue, &$mult, &$div, $this->captured
    public static function scoreValue($args) {
        assert(4 == count($args));
        $args[1] = -$args[1];
    }
}

?>
