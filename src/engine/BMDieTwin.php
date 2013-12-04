<?php

class BMDieTwin extends BMDie {
    public $die1_max;
    public $die1_min;
    public $die1_value;

    public $die2_max;
    public $die2_max;
    public $die2_value;

    public function init($sides, array $skills = NULL)
    {
        $this->min = 1;
        $this->max = $sides;

        $this->add_multiple_skills($skills);
    }

}

?>
