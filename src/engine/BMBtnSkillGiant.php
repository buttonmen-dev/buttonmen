<?php
/**
 * BMBtnSkillGiant: Code specific to Giant
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of Giant
 */
class BMBtnSkillGiant extends BMBtnSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('is_button_slow');

    /**
     * Hooked method applied when checking initiative
     *
     * @return array
     */
    public static function is_button_slow() {
        return array('is_button_slow' => TRUE);
    }
}
