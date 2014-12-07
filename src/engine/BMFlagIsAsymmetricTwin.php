<?php
/**
 * BMFlagHasJustSplit: Used to signal that a twin die is asymmetric
 *
 * @author: james
 */

/**
 * This class is a flag that signals that a twin die has two subdice that have different sizes.
 * It stores the die sizes of the two subdice.
 */
class BMFlagIsAsymmetricTwin extends BMFlag {
// properties
    protected $dieSizeArray;

    public function value() {
        return $this->dieSizeArray;
    }

    /**
     * Constructor
     *
     * @param string $dieSizeString
     */
    public function __construct($dieSizeString) {
        $this->dieSizeArray = NULL;
        if (isset($dieSizeString)) {
            $dieSizeArray = json_decode($dieSizeString);
            if (!is_array($dieSizeArray)) {
                throw new LogicException('Encoded die sizes must be contained in an array.');
            }

            if (2 != count($dieSizeArray)) {
                throw new LogicException('IsAsymmetricTwin is only meant for twin dice.');
            }

            if ($dieSizeArray[0] == $dieSizeArray[1]) {
                throw new LogicException('IsAsymmetricTwin is only meant for asymmetric twin dice.');
            }

            $this->dieSizeArray = $dieSizeArray;
        }
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString() {
        $string = $this->type();
        if (!empty($this->dieSizeArray)) {
            $string .= '__' . json_encode($this->dieSizeArray);
        }

        return $string;
    }
}
