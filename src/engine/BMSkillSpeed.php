<?php

class BMSkillSpeed extends BMSkill {
    public static $name = 'Speed';
    public static $type = 'Speed';
    public static $abbrev = 'z';

    public static $hooked_methods = array('attack_list');

    public static function attack_list($args) {
        $list = &$args[0];

        $redundant = FALSE;

        foreach ($list as $i => $att) {
            if ($att == 'Power') {
                unset($list[$i]);
            }
            if ($att == 'Shadow') {
                $redundant = TRUE;
            }
        }

        if (!$redundant) {
            $list[] = 'Shadow';
        }
    }
}

?>
