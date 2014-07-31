<?php
/**
 * BMFlag: Database-storable flags that contain information about dice
 *
 * @author james
 */

/**
 * This class contains all the logic for die information flags
 */
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

    public function type() {
        $type = get_class($this);

        return str_replace('BMFlag', '', $type);
    }

    public function value() {
        return NULL;
    }

    public function __toString() {
        return $this->type();
    }
}
