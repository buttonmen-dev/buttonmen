<?php
/**
 * BMBtnSkillArtificialReenable: Used to artificially re-enable buttons
 *
 * @author: james
 */

/**
 * This is a placeholder class that allows buttons to be re-enabled at
 * button selection, even when there are unimplemented iconic button skills.
 */
class BMBtnSkillArtificialReenable extends BMBtnSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array();

    /**
     * Description of skill
     *
     * @return string
     */
    public static function get_description() {
        return 'The special button skills for this button have not yet been implemented.';
    }
}
