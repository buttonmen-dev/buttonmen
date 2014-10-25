<?php
/**
 * BMSkillValue: Code specific to the value die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the value die skill
 */
class BMSkillValue extends BMSkill {
    public static $hooked_methods =
        array('add_skill', 'remove_skill', 'score_value', 'capture');

    public static function add_skill($args) {
        assert(array_key_exists('die', $args));

        $die = $args['die'];
        $die->add_flag('ValueRelevantToScore');
    }

    public static function remove_skill($args) {
        assert(array_key_exists('die', $args));

        // currently, the only skill that forces the display of the value is the
        // Value die skill, thus when value is removed, the flag should also be
        // removed
        $die = $args['die'];
        $die->remove_flag('ValueRelevantToScore');
    }

    public static function score_value($args) {
        assert(array_key_exists('scoreValue', $args));
        assert(array_key_exists('value', $args));

        if (is_null($args['value'])) {
            $args['scoreValue'] = NULL;
        } else {
            $args['scoreValue'] = $args['value'];
        }
    }

    public static function capture($args) {
        assert(array_key_exists('defenders', $args));

        foreach ($args['defenders'] as $defender) {
            $defender->add_skill('Value');
        }
    }

    protected static function get_description() {
        return 'These dice are not scored like normal dice. Instead, a Value Die is scored as if the number of ' .
               'sides it has is equal to the value that it is currently showing. If a Value Die is ever part of an ' .
               'attack, all dice that are captured become Value Dice (i.e. They are scored by the current value ' .
               'they are showing when they are captured, not by their size).';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Null' => 'Dice with both Null and Value skills are Null',
            'Poison' => 'Dice with both Poison and Value skills are Poison dice that score based on the negative of ' .
                        'their current value rather than on their number of sides',
        );
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
