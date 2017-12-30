<?php
/**
 * BMBtnSkillGordo: Code specific to Gordo
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of Gordo
 */
class BMBtnSkillGordo extends BMBtnSkillUniqueSwing {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array(
        'are_unique_swing_values_valid',
        'can_button_add_this_aux_die',
        'unique_swing_setting_fail_message'
    );

    /**
     * Hooked method applied when checking if a button has unique swing
     *
     * @param array $args
     * @return array
     */
    public static function are_unique_swing_values_valid(array $args) {
        $parent_result = parent::are_unique_swing_values_valid($args);

        if (!$parent_result[__FUNCTION__]) {
            return array(__FUNCTION__ => FALSE);
        }

        foreach ($args['activeDieArray'] as $die) {
            if (!($die instanceof BMDieSwing)) {
                if (in_array($die->max, $args['swingValueArray'])) {
                    return array(__FUNCTION__ => FALSE);
                }
            }
        }

        return array(__FUNCTION__ => TRUE);
    }

    /**
     * Hooked method applied when checking if a button restricts decisions on
     * auxiliary dice
     *
     * @param array $args
     * @return array
     */
    public static function can_button_add_this_aux_die($args) {
        return array(__FUNCTION__ =>
            (!($args['die'] instanceof BMDieSwing)) ||
             !in_array($args['die']->swingType, array('V', 'W', 'X', 'Y', 'Z'))
        );
    }

    /**
     * Hooked method applied when swing setting has failed because die sizes
     * would end up not unique
     *
     * @param array $args
     * @return array
     */
    public static function unique_swing_setting_fail_message() {
        return array(__FUNCTION__ => 'Cannot have multiple dice with the same size');
    }

    /**
     * Description of skill
     *
     * @return string
     */
    public static function get_description() {
        return 'None of Gordo\'s dice can be the same size. ' .
               'Note that a player playing Gordo is required to decline an auxiliary die ' .
               'that would force two of Gordo\'s dice to be the same size';
    }
}
