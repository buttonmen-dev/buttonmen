<?php
/**
 *  BMBtnSkillGiant: Code specific to Giant
 *
 *  @author: james
 */

/**
 *  This class currently supports the special skills of Giant
 */
class BMBtnSkillGiant extends BMBtnSkill {
    public static $hooked_methods = array('is_button_slow');

    public static function is_button_slow() {
        return array('is_button_slow' => TRUE);
    }
}
