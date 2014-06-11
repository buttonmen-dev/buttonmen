<?php

class BMBtnSkillGiant extends BMBtnSkill {
    public static $hooked_methods = array('is_button_slow');

    public static function is_button_slow() {
        return array('is_button_slow' => TRUE);
    }
}
