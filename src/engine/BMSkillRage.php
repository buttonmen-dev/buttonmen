<?php
/**
 * BMSkillRage: Code specific to the rage die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the rage die skill
 */
class BMSkillRage extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('initiative_value',
                                          'capture',
                                          'be_captured');

    /**
     * Hooked method applied when determining the initiative value of a die
     *
     * @param array $args
     */
    public static function initiative_value(&$args) {
        if (!is_array($args)) {
            return;
        }

        // rage dice don't contribute to initiative
        $args['initiativeValue'] = -1;
    }

    /**
     * Hooked method applied to attacking dice during capture
     *
     * @param array $args
     */
    public static function capture(&$args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('caller', $args)) {
            return;
        }

        $args['caller']->remove_skill('Rage');
    }

    /**
     * Hooked method applied to defending dice during capture
     *
     * @param array $args
     */
    public static function be_captured(&$args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('caller', $args)) {
            return;
        }

        $def = $args['caller'];

        if (!($def->ownerObject instanceof BMGame)) {
            return;
        }

        $game = $def->ownerObject;
        $activeDieArrayArray = $game->activeDieArrayArray;

        $defenderDieIdx = array_search(
            $def,
            $args['defenders'],
            TRUE
        );
        assert(FALSE !== $defenderDieIdx);

        $newDefenderDie = self::create_rage_clone_target($def);
        $newDefenderDie->copy_skills_from_die($def);
        $newDefenderDie->remove_skill('Rage');

        array_splice($activeDieArrayArray[$def->playerIdx], $def->activeDieIdx + 1, 0, $newDefenderDie);
        $game->activeDieArrayArray = $activeDieArrayArray;

        $args['caller']->remove_skill('Rage');
    }

    /**
     * Create a die clone due to rage
     *
     * @param BMDie $def
     * @return BMDie
     */
    protected static function create_rage_clone_target($def) {
        $newDie = clone $def;
        unset($newDie->value);
        $newDie->remove_all_flags();

        $newDie->captured = FALSE;
        $newDie->ownerObject = $def->ownerObject;
        $newDie->playerIdx = $def->playerIdx;
        $newDie->originalPlayerIdx = $def->originalPlayerIdx;

        if (!empty($def->flagList)) {
            foreach ($def->flagList as $flagType => $flag) {
                $newDie->add_flag($flagType, $flag->value());
            }
        }

        return $newDie;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return '';
    }

    /**
     * Descriptions of interactions between this skill and other skills
     *
     * An array, indexed by other skill name, whose values are descriptions of
     * interactions between the relevant skills
     *
     * @return array
     */
    protected static function get_interaction_descriptions() {
        return array();
    }
}
