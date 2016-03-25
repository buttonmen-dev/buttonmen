<?php

class BMGameStateTest extends PHPUnit_Framework_TestCase {

    /**
     * @coversNothing
     */
    public function testBMGameStateOrder() {
        $this->assertTrue(BMGameState::START_GAME <
                          BMGameState::APPLY_HANDICAPS);
        $this->assertTrue(BMGameState::APPLY_HANDICAPS <
                          BMGameState::CHOOSE_JOIN_GAME);
        $this->assertTrue(BMGameState::CHOOSE_JOIN_GAME <
                          BMGameState::SPECIFY_RECIPES);
        $this->assertTrue(BMGameState::SPECIFY_RECIPES <
                          BMGameState::LOAD_DICE_INTO_BUTTONS);
        $this->assertTrue(BMGameState::LOAD_DICE_INTO_BUTTONS <
                          BMGameState::ADD_AVAILABLE_DICE_TO_GAME);
        $this->assertTrue(BMGameState::ADD_AVAILABLE_DICE_TO_GAME <
                          BMGameState::CHOOSE_AUXILIARY_DICE);
        $this->assertTrue(BMGameState::CHOOSE_AUXILIARY_DICE <
                          BMGameState::CHOOSE_RESERVE_DICE);
        $this->assertTrue(BMGameState::CHOOSE_RESERVE_DICE <
                          BMGameState::SPECIFY_DICE);
        $this->assertTrue(BMGameState::SPECIFY_DICE <
                          BMGameState::DETERMINE_INITIATIVE);
        $this->assertTrue(BMGameState::DETERMINE_INITIATIVE <
                          BMGameState::REACT_TO_INITIATIVE);
        $this->assertTrue(BMGameState::REACT_TO_INITIATIVE <
                          BMGameState::START_ROUND);
        $this->assertTrue(BMGameState::START_ROUND <
                          BMGameState::START_TURN);
        $this->assertTrue(BMGameState::START_TURN <
                          BMGameState::CHOOSE_TURBO_SWING);
        $this->assertTrue(BMGameState::CHOOSE_TURBO_SWING <
                          BMGameState::END_TURN);
        $this->assertTrue(BMGameState::END_TURN <
                          BMGameState::END_ROUND);
        $this->assertTrue(BMGameState::END_ROUND <
                          BMGameState::END_GAME);
        $this->assertTrue(BMGameState::END_GAME <
                          BMGameState::CANCELLED);
    }

    /**
     * @covers BMGameState::validate_game_state
     */
    public function test_validate_game_state() {
        // valid set
        BMGameState::validate_game_state(BMGameState::START_ROUND);

        // invalid set
        try {
            BMGameState::validate_game_state('abcd');
            $this->fail('Game state must be an integer.');
        }
        catch (InvalidArgumentException $expected) {
        }

        try {
            BMGameState::validate_game_state(0);
            $this->fail('Invalid game state.');
        }
        catch (InvalidArgumentException $expected) {
        }
    }
}
