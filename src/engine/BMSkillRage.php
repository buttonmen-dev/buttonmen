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
                                          'pre_be_captured');

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
     * Hooked method applied to defending dice before capture
     *
     * @param array $args
     */
    public static function pre_be_captured(&$args) {
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

        if (!$def->has_skill('Rage')) {
            return;
        }

        if (!$def->captured) {
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

        // james:
        // Note that the replacement is always assumed to be an array if possible.
        // Thus, an object needs to be wrapped in an array, otherwise it will be
        // decomposed as if it were an array.
        //
        // From http://php.net/manual/en/function.array-splice.php
        // If replacement is just one element it is not necessary to put array()
        // around it, unless the element is an array itself, an object or NULL.

        array_splice(
            $activeDieArrayArray[$def->playerIdx],
            $def->activeDieIdx + 1,
            0,
            array($newDefenderDie)
        );

        array_splice(
            $args['defenders'],
            $defenderDieIdx + 1,
            0,
            array($newDefenderDie)
        );

        $game->activeDieArrayArray = $activeDieArrayArray;
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

        if (!empty_value($def->flagList)) {
            foreach ($def->flagList as $flagType => $flag) {
                $newDie->add_flag($flagType, $flag->value());
            }
        }

        $newDie->copy_skills_from_die($def);
        $newDie->remove_skill('Rage');
        $newDie->captured = FALSE;
        $newDie->remove_flag('WasJustCaptured');
        $newDie->add_flag('IsRageTargetReplacement');

        return $newDie;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'If a Rage die is captured, then the owner of the Rage Die ' .
               'adds a new die to their pool of the same size and ability of ' .
               'the Rage die that was taken, except that it loses the Rage ' .
               'ability. If a Rage Die participates in an Attack, it loses ' .
               'its Rage ability. IMPORTANT: Rage dice do not count for ' .
               'determining who goes first.';
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
        return array(
            'Doppelganger' => 'A Doppelganger die that captures a Rage die with a Power attack will ' .
                              'retain Rage after it transforms',
        );
    }

    /**
     * Does this skill prevent the determination of whether a player can win?
     *
     * @return bool
     */
    public static function prevents_win_determination() {
        return TRUE;
    }
}
