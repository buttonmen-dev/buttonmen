<?php

abstract class BMFlag {
    // factory method to enable loading flags from database
    public static function create_from_string($string) {
        if (empty($string)) {
            return;
        }

        $fullString = 'BMFlag'.$string;

        if (class_exists($fullString)) {
            return new $fullString;
        }
    }

    public function __toString() {
        $name = get_class($this);

        return str_replace('BMFlag', '', $name);
    }
}

