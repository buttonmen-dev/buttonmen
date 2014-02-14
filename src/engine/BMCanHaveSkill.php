<?php

/**
 * BMCanHaveSkill: convenience class to hold skill logic
 *
 * @author james
 *
 */

class BMCanHaveSkill {
    // properties

    // an array keyed by function name. Value is an array of the skills
    //  that are modifying that function
    protected $hookList = array();
    // keyed by the Names of the skills that the die has, with values of
    // the skill class's name
    protected $skillList = array();

    // methods

    // unhooked methods

    // Run the skill hooks for a given function. $args is an array of
    //  argumentsfor the function.
    //
    // Important note on PHP references, since they make no bloody sense:
    //
    // To put a reference into the args array and have it still be such
    // when you take it out again, you must:
    //
    // Put it into the args array as a reference: $args = array(&$foo)
    // --AND--
    // Take it out as a reference: $thing = &$args[0]

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

    // Other code inside engine must never set $skillClass, but
    // instead name skill classes according to the expected pattern.
    // The optional argument is only for outside code which needs
    // to add skills (currently, it's used for unit testing).
    public function add_skill($skill, $skillClass = FALSE) {
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

// This one may need to be hookable. So might add_skill, depending on
//  how Chaotic shakes out.
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

        return TRUE;
    }

    public function remove_all_skills() {
        if (!isset($this->skillList) ||
            0 == count($this->skillList)) {
            return;
        }

        foreach (array_keys($this->skillList) as $skill) {
            $this->remove_skill($skill);
        }
    }

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

    public function has_skill($skill) {
        return array_key_exists($skill, $this->skillList);
    }
}