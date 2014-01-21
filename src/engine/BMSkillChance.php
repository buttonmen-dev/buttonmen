<?php

class BMSkillChance extends BMSkill {
    public static $hooked_methods = array("react_to_initiative");

    public static function react_to_initiative() {
        return TRUE;
    }
}
