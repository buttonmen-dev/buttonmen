<?php
/**
 * BMFlagDecision: Used to signal that a decision has been made
 *
 * @author: james
 */

/**
 * This class is a flag that signals that a decision has been made about an
 * die. It stores the decision as a boolean.
 */
class BMFlagDecision extends BMFlag {

    // properties
    protected $decision;

    public function value() {
        return $this->decision;
    }

    /**
     * Constructor
     *
     * @param bool $decision
     */
    public function __construct($decision) {
        $this->decision = NULL;
        if (isset($decision)) {
            $this->decision = (bool)$decision;
        }
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString() {
        $string = $this->type();
        if (is_bool($this->decision)) {
            $string .= '__' . (int)$this->decision;
        }

        return $string;
    }
}
