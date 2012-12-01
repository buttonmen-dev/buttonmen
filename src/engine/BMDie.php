<?php
class BMDie
{
    // properties
    private $mSides;

    // methods
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

    }

    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            switch ($property) {
                case '$mSides':
                    if (is_int($value)) {
                        $this->$mSides = $value;
                    } else {
                        unset($mSides);
                    }
                    break;

                default:
                    $this->$property = $value;
                    break;
            }
        }
    }

    public function __toString() {
        ;
    }

    public function Roll() {}

    public function Display() {}
}
?>
