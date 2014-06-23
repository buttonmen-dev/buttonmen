<?php

class BMSkillStinger extends BMSkill {
    public static $hooked_methods = array('assist_values');

    public static function assist_values(&$args) {
        if (!is_array($args)) {
            return;
        }

        // still incomplete
    }
}
