<?php

class BMGameState {
    // pre-game
    const START_GAME = 10;
    const APPLY_HANDICAPS = 12;

    // pre-round
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
    const END_TURN = 48;

    // end round
    const END_ROUND = 50;

    // end game
    const END_GAME = 60;

    public static function all_game_state_strings() {
        return array('START_GAME',
                     'APPLY_HANDICAPS',
                     'CHOOSE_AUXILIARY_DICE',
                     'CHOOSE_RESERVE_DICE',
                     'LOAD_DICE_INTO_BUTTONS',
                     'ADD_AVAILABLE_DICE_TO_GAME',
                     'SPECIFY_DICE',
                     'DETERMINE_INITIATIVE',
                     'REACT_TO_INITIATIVE',
                     'START_ROUND',
                     'START_TURN',
                     'END_TURN',
                     'END_ROUND',
                     'END_GAME');
    }

    public static function all_game_state_values() {
        $gameStateValueArray = array();
        foreach (BMGameState::all_game_state_strings() as $gameStateStr) {
            $gameStateValueArray[] = constant('BMGameState::'.$gameStateStr);
        }
        return $gameStateValueArray;
    }

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
