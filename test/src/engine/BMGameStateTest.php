<?php

class BMGameStateTest extends PHPUnit_Framework_TestCase {

    /**
     * @coversNothing
     */
    public function testBMGameStateOrder() {
        $this->assertTrue(BMGameState::startGame <
                          BMGameState::applyHandicaps);
        $this->assertTrue(BMGameState::applyHandicaps <
                          BMGameState::chooseAuxiliaryDice);
        $this->assertTrue(BMGameState::chooseAuxiliaryDice <
                          BMGameState::loadDiceIntoButtons);
        $this->assertTrue(BMGameState::loadDiceIntoButtons <
                          BMGameState::addAvailableDiceToGame);
        $this->assertTrue(BMGameState::addAvailableDiceToGame <
                          BMGameState::specifyDice);
        $this->assertTrue(BMGameState::specifyDice <
                          BMGameState::determineInitiative);
        $this->assertTrue(BMGameState::determineInitiative <
                          BMGameState::startRound);
        $this->assertTrue(BMGameState::startRound <
                          BMGameState::startTurn);
        $this->assertTrue(BMGameState::startTurn <
                          BMGameState::endTurn);
        $this->assertTrue(BMGameState::endTurn <
                          BMGameState::endRound);
        $this->assertTrue(BMGameState::endRound <
                          BMGameState::endGame);
    }

    /**
     * @covers BMGameState::validate_game_state
     */
    public function test_validate_game_state() {
        // valid set
        BMGameState::validate_game_state(BMGameState::startRound);

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
