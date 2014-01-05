<?php

class BMGameState {
    // pre-game
    const START_GAME = 10;
    const APPLY_HANDICAPS = 13;
    const CHOOSE_AUXILIARY_DICE = 16;

    // pre-round
    const LOAD_DICE_INTO_BUTTONS = 20;
    // const addReserveDice = 21;
    const ADD_AVAILABLE_DICE_TO_GAME = 22;
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

    public static function validate_game_state($value) {
        if (FALSE === filter_var($value, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException(
                'Game state must be an integer.'
            );
        }
        if (!in_array($value, array(BMGameState::START_GAME,
                                    BMGameState::APPLY_HANDICAPS,
                                    BMGameState::CHOOSE_AUXILIARY_DICE,
                                    BMGameState::LOAD_DICE_INTO_BUTTONS,
                                    BMGameState::ADD_AVAILABLE_DICE_TO_GAME,
                                    BMGameState::SPECIFY_DICE,
                                    BMGameState::DETERMINE_INITIATIVE,
                                    BMGameState::REACT_TO_INITIATIVE,
                                    BMGameState::START_ROUND,
                                    BMGameState::START_TURN,
                                    BMGameState::END_TURN,
                                    BMGameState::END_ROUND,
                                    BMGameState::END_GAME))) {
            throw new InvalidArgumentException(
                'Invalid game state.'
            );
        }
    }
}
