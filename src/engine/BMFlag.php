<?php

abstract class BMFlag {
    // factory method to enable loading flags from database
    public static function create_from_string($string) {
        if (empty($string)) {
            return;
        }

        $flagComponents = explode('__', $string);

        $flagName = 'BMFlag'.$flagComponents[0];

        $flagValue = NULL;
        if (count($flagComponents) > 1) {
            $flagValue = $flagComponents[1];
        }

        $flag = NULL;
        if (class_exists($flagName)) {
            $flag = new $flagName($flagValue);
        }

        return $flag;
    }

    public function value() {
        return NULL;
    }

    public function __toString() {
        $name = get_class($this);

        return str_replace('BMFlag', '', $name);
    }
}
