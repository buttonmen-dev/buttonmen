<?php
/**
 * BMBtnSkillTheJapaneseBeetle: Code specific to The Japanese Beetle
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of The Japanese Beetle
 */
class BMBtnSkillTheJapaneseBeetle extends BMBtnSkill {
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
        return 'Cannot be attacked by skill attacks.';
    }
}
