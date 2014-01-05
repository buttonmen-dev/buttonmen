<?php

class BMGameState {
    // pre-game
    const startGame = 10;
    const applyHandicaps = 13;
    const chooseAuxiliaryDice = 16;

    // pre-round
    const loadDiceIntoButtons = 20;
    // const addReserveDice = 21;
    const addAvailableDiceToGame = 22;
    const specifyDice = 24;
    const determineInitiative = 26;
    const reactToInitiative = 27;

    // start round
    const startRound = 30;

    // turn
    const startTurn = 40;
    const endTurn = 48;

    // end round
    const endRound = 50;

    // end game
    const endGame = 60;

    public static function validate_game_state($value) {
        if (FALSE === filter_var($value, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException(
                'Game state must be an integer.');
        }
        if (!in_array($value, array(BMGameState::startGame,
                                    BMGameState::applyHandicaps,
                                    BMGameState::chooseAuxiliaryDice,
                                    BMGameState::loadDiceIntoButtons,
                                    BMGameState::addAvailableDiceToGame,
                                    BMGameState::specifyDice,
                                    BMGameState::determineInitiative,
                                    BMGameState::reactToInitiative,
                                    BMGameState::startRound,
                                    BMGameState::startTurn,
                                    BMGameState::endTurn,
                                    BMGameState::endRound,
                                    BMGameState::endGame))) {
            throw new InvalidArgumentException(
                'Invalid game state.');
        }
    }
}
