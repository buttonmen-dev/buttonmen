<?php
/**
 * BMFlagTwin: Used to hold properties of the subdice of a twin die
 *
 * @author: james
 */

/**
 * This class is a flag that holds properties of the subdice of a twin die.
 * It stores the die sizes and values of the two subdice.
 */
class BMFlagTwin extends BMFlag {
    /**
     * Array of subdie properties, stored in flag
     *
     * @var array
     */
    protected $subdiePropertyArray;

    /**
     * Value stored in flag
     *
     * @return mixed
     */
    public function value() {
        return $this->subdiePropertyArray;
    }

    /**
     * Constructor
     *
     * @param string $subdiePropertyString
     */
    public function __construct($subdiePropertyString) {
        $this->subdiePropertyArray = NULL;
        if (isset($subdiePropertyString)) {
            $subdiePropertyArray = json_decode($subdiePropertyString, TRUE);

            if (!is_array($subdiePropertyArray)) {
                throw new LogicException('Encoded subdie properties must be contained in an array.');
            }

            if (2 != count($subdiePropertyArray['sides']) ||
                2 != count($subdiePropertyArray['values'])) {
                throw new LogicException('Twin is only meant for twin dice.');
            }

            $this->subdiePropertyArray = $subdiePropertyArray;
        }
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString() {
        $string = $this->type();
        if (!empty($this->subdiePropertyArray)) {
            $string .= '__' . json_encode($this->subdiePropertyArray);
        }

        return $string;
    }
}
