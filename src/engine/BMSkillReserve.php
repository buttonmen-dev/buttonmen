<?php

class BMSkillReserve extends BMSkill {
    public static $hooked_methods = array('activate');

    public static function activate() {
        return 'skipSwingRequest';
    }
}
