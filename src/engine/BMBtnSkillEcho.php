<?php

class BMBtnSkillEcho extends BMBtnSkill {
    public static $hooked_methods = array('load_buttons');

    public static function load_buttons($args) {
        var_dump('success');
        return FALSE;
    }
}
