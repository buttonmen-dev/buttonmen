<?php

class BMSkillMaximum extends BMSkill {
    public static $hooked_methods = array('post_roll');

    public static function post_roll(&$args) {
        if (!($args['die'] instanceof BMDie)) {
            return FALSE;
        }
        
        $die = $args['die'];
        $die->value = $die->max;
        return TRUE;
    }

    protected static function get_description() {
        return 'Maximum dice always roll their maximum value.';
    }

    protected static function get_interaction_descriptions() {
        return array();
    }
}
