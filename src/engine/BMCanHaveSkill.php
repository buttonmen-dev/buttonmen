<?php
/**
 * BMCanHaveSkill: convenience class to hold logic shared by die skills and button skills
 *
 * @author james
 */

/**
 * This class is the parent class for BMSkill and BMBtnSkill
 */
class BMCanHaveSkill {
    // properties

    /**
     * An array keyed by function name. Value is an array of the skills
     * that are modifying that function.
     *
     * @var array
     */
    protected $hookList = array();
    /**
     * An array keyed by the names of the skills that the die has, with
     * values of the skill class's name
     *
     * @var array
     */
    protected $skillList = array();

    // methods

    // unhooked methods

    /**
     * Run the skill hooks for a given function. $args is an array of
     * arguments for the function.
     *
     * @param string $func
     * @param array $args
     * @return array
     */
    public function run_hooks($func, $args) {
        // get the hooks for the calling function
        if (!array_key_exists($func, $this->hookList)) {
            return;
        }

        $resultArray = array();

        $hookList = $this->hookList[$func];

        if (isset($hookList) && (count($hookList) > 1)) {
            usort($hookList, 'BMSkill::skill_order_comparator');
        }

        foreach ($hookList as $skillClass) {
            $resultArray[$skillClass] = $skillClass::$func($args);
        }

        return $resultArray;
    }

    /**
     * Add a skill to a die or button.
     *
     * Other code inside engine must never set $skillClass, but
     * instead name skill classes according to the expected pattern.
     * The optional argument is only for outside code which needs
     * to add skills (currently, it's used for unit testing).
     *
     * @param string $skill
     * @param string $skillClass
     * @return void
     */
    public function add_skill($skill, $skillClass = '') {
        if (!$skill) {
            return;
        }

        if (!$skillClass) {
            if (is_a($this, 'BMButton')) {
                $skillClass = "BMBtnSkill$skill";
            } else {
                $skillClass = "BMSkill$skill";
            }
        }

        // Don't add skills that are already added
        if (!$this->has_skill($skill)) {
            $this->skillList[$skill] = $skillClass;

            foreach ($skillClass::$hooked_methods as $func) {
                $this->hookList[$func][] = $skillClass;
            }
        }

        $this->run_hooks(__FUNCTION__, array('die' => &$this));
    }

    /**
     * Add multiple skills at once
     *
     * @param array $skills
     * @return void
     */
    protected function add_multiple_skills($skills) {
        if ($skills) {
            foreach ($skills as $skillClass => $skill) {
                if (is_string($skillClass)) {
                    $this->add_skill($skill, $skillClass);
                } else {
                    $this->add_skill($skill);
                }
            }
        }
    }

    /**
     * Remove a skill from a die or button
     *
     * @param string $skill
     * @return bool
     */
    public function remove_skill($skill) {
        if (!$this->has_skill($skill)) {
            return FALSE;
        }

        $skillClass = $this->skillList[$skill];

        unset($this->skillList[$skill]);

        foreach ($skillClass::$hooked_methods as $func) {
            $key = array_search($skillClass, $this->hookList[$func], TRUE);
            if ($key === FALSE) {
                // should never happen, and we should error hard if it does
            }
            unset($this->hookList[$func][$key]);
        }

        $this->run_hooks(__FUNCTION__, array('die' => &$this));

        return TRUE;
    }

    /**
     * Remove all skills at once
     *
     * @return void
     */
    public function remove_all_skills() {
        if (!isset($this->skillList) ||
            0 == count($this->skillList)) {
            return;
        }

        foreach (array_keys($this->skillList) as $skill) {
            $this->remove_skill($skill);
        }
    }

    /**
     * Copy skills from one die to another
     *
     * @param BMDie $die
     * @return void
     */
    public function copy_skills_from_die($die) {
        $this->remove_all_skills();

        if (!isset($die->skillList) ||
            0 == count($die->skillList)) {
            return;
        }

        foreach (array_keys($die->skillList) as $skill) {
            $this->add_skill($skill);
        }
    }

    /**
     * Check if a die or button has a skill
     *
     * @param string $skill
     * @return bool
     */
    public function has_skill($skill) {
        return array_key_exists($skill, $this->skillList);
    }

    /**
     * Check if a die or button has no skills
     *
     * @return bool
     */
    public function has_no_skills() {
        return 0 === count($this->skillList);
    }
}
