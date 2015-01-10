<?php

class TestDummyBMSkillTestStinger extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array("attack_values");

    public static function attack_values($args) {
        $alist = &$args['attackValues'];

        for ($i = $alist[0] - 1; $i > 0; $i--) {
            $alist[] = $i;
        }
    }
}
