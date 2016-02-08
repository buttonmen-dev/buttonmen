<?php
/**
 * BMGameState: The various game states possible in a game
 *
 * @author james
 */

/**
 * This class defines the game states possible in a game
 */
class BMGameState {
    // pre-game
    const START_GAME = 10;
    const APPLY_HANDICAPS = 12;
    const CHOOSE_JOIN_GAME = 13;

    // pre-round
    const SPECIFY_RECIPES = 14;
    const LOAD_DICE_INTO_BUTTONS = 15;
    const ADD_AVAILABLE_DICE_TO_GAME = 17;
    const CHOOSE_AUXILIARY_DICE = 20;
    const CHOOSE_RESERVE_DICE = 22;
    const SPECIFY_DICE = 24;
    const DETERMINE_INITIATIVE = 26;
    const REACT_TO_INITIATIVE = 27;

    // start round
    const START_ROUND = 30;

    // turn
    const START_TURN = 40;
    const ADJUST_FIRE_DICE = 42;
    const COMMIT_ATTACK = 44;
    const CHOOSE_TURBO_SWING = 46;

    const END_TURN = 48;

    // end round
    const END_ROUND = 50;

    // end game
    const END_GAME = 60;

    // special states
    const CANCELLED = 251;

    /**
     * All possible game state strings
     *
     * @return array
     */
    public static function all_game_state_strings() {
        return array('START_GAME',
                     'APPLY_HANDICAPS',
                     'CHOOSE_JOIN_GAME',
                     'SPECIFY_RECIPES',
                     'CHOOSE_AUXILIARY_DICE',
                     'CHOOSE_RESERVE_DICE',
                     'LOAD_DICE_INTO_BUTTONS',
                     'ADD_AVAILABLE_DICE_TO_GAME',
                     'SPECIFY_DICE',
                     'DETERMINE_INITIATIVE',
                     'REACT_TO_INITIATIVE',
                     'START_ROUND',
                     'START_TURN',
                     'ADJUST_FIRE_DICE',
                     'COMMIT_ATTACK',
                     'CHOOSE_TURBO_SWING',
                     'END_TURN',
                     'END_ROUND',
                     'END_GAME',
                     'CANCELLED');
    }

    /**
     * All possible game state values
     *
     * @return array
     */
    public static function all_game_state_values() {
        $gameStateValueArray = array();
        foreach (BMGameState::all_game_state_strings() as $gameStateStr) {
            $gameStateValueArray[] = constant('BMGameState::'.$gameStateStr);
        }
        return $gameStateValueArray;
    }

    /**
     * Convert numerical game state into a string
     *
     * @param int $gameState
     * @return string
     */
    public static function as_string($gameState) {
        $gameStateStrings = BMGameState::all_game_state_strings();
        $gameStateValues = BMGameState::all_game_state_values();

        $gameStateIdx = array_search($gameState, $gameStateValues);

        $gameStateString = '';
        if (FALSE !== $gameStateIdx) {
            $gameStateString = $gameStateStrings[$gameStateIdx];
        }

        return $gameStateString;
    }

    /**
     * Check that a provided game state is valid
     *
     * @param mixed $value
     */
    public static function validate_game_state($value) {
        if (FALSE === filter_var($value, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException(
                'Game state must be an integer.'
            );
        }

        if (!in_array($value, BMGameState::all_game_state_values())) {
            throw new InvalidArgumentException(
                'Invalid game state.'
            );
        }
    }
}
