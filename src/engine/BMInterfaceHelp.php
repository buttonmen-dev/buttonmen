<?php

/**
 * BMInterfaceHelp: interface between GUI and BMGame for help-related requests
 *
 * @author james
 */

/**
 * This class deals with communication between the UI, the game code, and the database
 * pertaining to help-related information.
 *
 * This class is designed to work without any calls to database, since it should also
 * be usable when not logged in. Also, there are no calls to set_message(), since these
 * fail with BMInterfaceNewUser.
 */
class BMInterfaceHelp extends BMInterface {
    /**
     * Retrieves information about all die skills
     *
     * @return array|NULL
     */
    public function load_die_skills_data() {
        try {
            $results = array();

            $skillMapping = BMSkill::skill_name_abbreviation_mapping();

            foreach ($skillMapping as $name => $code) {
                $fullDescription = BMSkill::describe($name);
                $results[$code] = array(
                    'name' => $name,
                    'description' => $fullDescription['description'],
                    'interacts' => $fullDescription['interacts']
                );
            }

            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterfaceHelp::load_die_skills_data: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Retrieves information about all die types
     *
     * @return array|NULL
     */
    public function load_die_types_data() {
        try {
            $results = array();

            $results['(C)'] = array(
                'name' => 'Wildcard',
                'description' => BMDieWildcard::getDescription()
            );

            $swingInfo = BMDieSwing::$swingRanges;
            foreach (array_keys($swingInfo) as $swingLetter) {
                $recipe = '(' . $swingLetter . ')';
                $die = BMDieSwing::create_from_recipe($recipe);
                $results[$recipe] = array(
                    'name' => $swingLetter . ' Swing',
                    'description' => $die->getDescription()
                );
            }

            $results['( , )'] = array(
                'name' => 'Twin',
                'description' => BMDieTwin::getDescription()
            );
            $results['( / )'] = array(
                'name' => 'Option',
                'description' => BMDieOption::getDescription()
            );

            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterfaceHelp::load_die_skills_data: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }
}
