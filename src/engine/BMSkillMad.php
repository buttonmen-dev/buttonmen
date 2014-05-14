<?php

class BMSkillMad extends BMSkillMood {

    public static function valid_die_sizes(array $swingRange) {
        $dieSizeArray = range($swingRange[0], $swingRange[1]);

        foreach ($dieSizeArray as $sizeIdx => $size) {
            // remove odd values
            if ($size & 1) {
                unset($dieSizeArray[$sizeIdx]);
            }
        }

        return array_values($dieSizeArray);
    }

    public static function add_skill($args) {
        if (!is_array($args) ||
            !($args['die'] instanceof BMDie)) {
            return;
        }

        if (!static::can_have_mood($args['die'])) {
            $args['die']->remove_skill('Mad');
        }
    }
}
