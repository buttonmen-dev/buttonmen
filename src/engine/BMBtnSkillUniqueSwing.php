<?php
/**
 * BMBtnSkillUniqueSwing: Code to ensure that chosen swing values are all distinct
 *
 * @author: james
 */

/**
 * This class is intended to be a base class for any button special requiring
 * unique swing values to be selected, like BMBtnSkillGordo and BMBtnSkillGuillermo
 */
class BMBtnSkillUniqueSwing extends BMBtnSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('are_unique_swing_values_valid');

    /**
     * Hooked method applied when checking if a button has unique swing
     *
     * @param array $args
     * @return array
     */
    public static function are_unique_swing_values_valid(array $args) {
        return array(__FUNCTION__ =>
            count(array_unique($args['swingValueArray'])) == count($args['swingValueArray'])
        );
    }

    /**
     * Description of skill
     *
     * @return string
     */
    public static function get_description() {
        return 'Different swing types must be assigned unique values';
    }
}
