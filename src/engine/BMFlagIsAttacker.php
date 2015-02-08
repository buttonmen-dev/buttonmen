<?php
/**
 * BMFlagIsAttacker: Used to signal that a die is an attacker
 *
 * @author: james
 */

/**
 *  This class is a flag that signals that a die is an attacker.
 *  It stores the attack type.
 */
class BMFlagIsAttacker extends BMFlag {
    /**
     * Attack type, stored in flag
     *
     * @var string
     */
    protected $attackType;

    /**
     * Value stored in flag
     *
     * @return mixed
     */
    public function value() {
        return $this->attackType;
    }

    /**
     * Constructor
     *
     * @param string $attackType
     */
    public function __construct($attackType) {
        $this->attackType = NULL;
        if (isset($attackType)) {
            $this->attackType = $attackType;
        }
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString() {
        $string = $this->type();
        if (!empty($this->attackType)) {
            $string .= '__' . $this->attackType;
        }

        return $string;
    }
}
