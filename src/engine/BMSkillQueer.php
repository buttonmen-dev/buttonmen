<?php

class BMSkillQueer extends BMSkill {
    public static $hooked_methods = array('attack_list');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];
        $value = $args['value'];

        if (!is_int($value)) {
            return;
        }

        if (0 == $value % 2) {
            $attackTypeArray['Power'] = 'Power';
        } else {
            $attackTypeArray['Shadow'] = 'Shadow';
        }
        
        foreach (BMSkillQueer::incompatible_attack_types(array('value' => $value)) as $attackType) {
            if (array_key_exists($attackType, $attackTypeArray)) {
                unset($attackTypeArray[$attackType]);
            }
        }
    }
    
    public static function incompatible_attack_types($args = NULL) {
        if (!isset($args) || !array_key_exists('value', $args)) {
            return array();
        }
        
        if (FALSE ===
            filter_var(
                $args['value'],
                FILTER_VALIDATE_INT,
                array("options"=>
                      array("min_range"=>1))
            )) {
            throw new InvalidArgumentException('Invalid value.');
        }
        
        if (0 == $args['value'] % 2) {
            return array('Shadow');
        } else {
            return array('Power');
        }
    }

    protected function get_description() {
        return 'These dice behave like normal dice when they show ' .
               'an even number, and like Shadow Dice when they show an odd ' .
               'number.';
    }

    protected function get_interaction_descriptions() {
        return array(
            'Trip' => 'Dice with both Queer and Trip skills always ' .
                      'determine their success or failure at Trip Attacking ' .
                      'via a Power Attack',
        );
    }
}
