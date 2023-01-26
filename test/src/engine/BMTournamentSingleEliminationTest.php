<?php

class BMTournamentSingleEliminationTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMTournamentSingleElimination
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMTournamentSingleElimination;
        $this->object->isTest = true;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @covers BMTournament
     * @covers BMTournamentSingleElimination
     */
    public function testFull() {
        $this->object->tournamentId = 1001;
        $this->object->creatorId = 3;
        $this->object->name = 'Test tournament';
        $this->object->description = 'Description of test tournament';
        $this->object->nPlayers = 8;
        $this->object->doShufflePlayers = FALSE;

        $this->assertEquals(BMTournamentState::START_TOURNAMENT, $this->object->tournamentState);
        $this->assertEquals(array(), $this->object->playerIdArray);
        $this->assertEquals(array(), $this->object->buttonIdArrayArray);
        $this->assertEquals(array(), $this->object->gameIdArrayArray);

        $this->object->proceed_to_next_user_action();

        $this->assertEquals(BMTournamentState::JOIN_TOURNAMENT, $this->object->tournamentState);
        $this->assertEquals(array(), $this->object->playerIdArray);
        $this->assertEquals(array(), $this->object->buttonIdArrayArray);
        $this->assertEquals(array(), $this->object->gameIdArrayArray);

        $this->object->add_player(45, array(103));

        $this->object->proceed_to_next_user_action();

        $this->assertEquals(BMTournamentState::JOIN_TOURNAMENT, $this->object->tournamentState);
        $this->assertEquals(array(45), $this->object->playerIdArray);
        $this->assertEquals(array(45 => array(103)), $this->object->buttonIdArrayArray);
        $this->assertEquals(array(), $this->object->gameIdArrayArray);

        $this->object->add_player(57, array(26));

        $this->object->proceed_to_next_user_action();

        $this->assertEquals(BMTournamentState::JOIN_TOURNAMENT, $this->object->tournamentState);
        $this->assertEquals(array(45, 57), $this->object->playerIdArray);
        $this->assertEquals(
            array(
                45 => array(103),
                57 => array(26)
            ),
            $this->object->buttonIdArrayArray
        );
        $this->assertEquals(array(), $this->object->gameIdArrayArray);

        $this->object->add_player(23, array(302));
        $this->object->add_player(101, array(2));
        $this->object->add_player(102, array(53));
        $this->object->add_player(150, array(11));
        $this->object->add_player(3, array(74));

        $this->object->proceed_to_next_user_action();

        $this->assertEquals(BMTournamentState::JOIN_TOURNAMENT, $this->object->tournamentState);
        $this->assertEquals(array(45, 57, 23, 101, 102, 150, 3), $this->object->playerIdArray);
        $this->assertEquals(
            array(
                  3 => array(74),
                 23 => array(302),
                 45 => array(103),
                 57 => array(26),
                101 => array(2),
                102 => array(53),
                150 => array(11)
            ),
            $this->object->buttonIdArrayArray
        );
        $this->assertEquals(array(), $this->object->gameIdArrayArray);

        // try adding a player who has already joined the tournament
        $this->object->add_player(23, array(197));

        $this->assertEquals(BMTournamentState::JOIN_TOURNAMENT, $this->object->tournamentState);
        $this->assertEquals(array(45, 57, 23, 101, 102, 150, 3), $this->object->playerIdArray);
        $this->assertEquals(
            array(
                  3 => array(74),
                 23 => array(302),
                 45 => array(103),
                 57 => array(26),
                101 => array(2),
                102 => array(53),
                150 => array(11)
            ),
            $this->object->buttonIdArrayArray
        );
        $this->assertEquals(array(), $this->object->gameIdArrayArray);

        // now add the last player needed to start the tournament
        $this->object->add_player(26, array(197));
        
        $this->gameArrayArray = array(
            array(
                'activePlayerIdx' => 0,
                'gameState' => BMGameState::END_GAME,
                'maxWins' => 3,
                'playerArray' => array(
                    array('playerId' => 45),
                    array('playerId' => 57)
                )
            ),
            array(
                'activePlayerIdx' => 0,
                'gameState' => BMGameState::END_GAME,
                'maxWins' => 3,
                'playerArray' => array(
                    array('playerId' => 23),
                    array('playerId' => 101)
                )
            ),
            array(
                'activePlayerIdx' => 0,
                'gameState' => BMGameState::END_GAME,
                'maxWins' => 3,
                'playerArray' => array(
                    array('playerId' => 102),
                    array('playerId' => 150)
                )
            ),
            array(
                'activePlayerIdx' => 0,
                'gameState' => BMGameState::END_GAME,
                'maxWins' => 3,
                'playerArray' => array(
                    array('playerId' => 3),
                    array('playerId' => 26)
                )
            )
        );

        $this->object->proceed_to_next_user_action();

        $this->assertEquals(BMTournamentState::PLAY_GAMES, $this->object->tournamentState);
        $this->assertCount(8, $this->object->playerIdArray);
        $this->assertEquals(array(45, 57, 23, 101, 102, 150, 3, 26), $this->object->playerIdArray);
        $this->assertEquals(
            array(
                  3 => array(74),
                 23 => array(302),
                 26 => array(197),
                 45 => array(103),
                 57 => array(26),
                101 => array(2),
                102 => array(53),
                150 => array(11)
            ),
            $this->object->buttonIdArrayArray
        );
        $this->assertCount(1, $this->object->gameIdArrayArray);
        $this->assertCount(4, $this->object->gameIdArrayArray[0]);

//        // allow access to protected methods load_game and save_game via reflection
//        $interfaceClassGame = new ReflectionClass('BMInterface');
//        $interfaceGameInstance = $interfaceClassGame->newInstanceArgs(array(TRUE));
//        $loadGameMethod = $interfaceClassGame->getMethod('load_game');
//        $loadGameMethod->setAccessible(true);
//        $saveGameMethod = $interfaceClassGame->getMethod('save_game');
//        $saveGameMethod->setAccessible(true);
//
//        foreach ($this->object->gameIdArrayArray[0] as $gameId) {
//            $game = $loadGameMethod->invokeArgs($interfaceGameInstance, array($gameId));
//            $game->gameState = BMGameState::END_GAME;
//            $game->gameScoreArrayArray = array(
//                array('W' => 0, 'L' => 3, 'D' => 0),
//                array('W' => 3, 'L' => 0, 'D' => 0)
//            );
//
//            $saveGameMethod->invokeArgs($interfaceGameInstance, array($game));
//        }
        
        $this->gameArrayArray = array(
            array(
                'activePlayerIdx' => 0,
                'gameState' => BMGameState::END_GAME,
                'maxWins' => 3,
                'playerArray' => array(
                    array('playerId' => 45),
                    array('playerId' => 57)
                )
            ),
            array(
                'activePlayerIdx' => 0,
                'gameState' => BMGameState::END_GAME,
                'maxWins' => 3,
                'playerArray' => array(
                    array('playerId' => 23),
                    array('playerId' => 101)
                )
            ),
            array(
                'activePlayerIdx' => 0,
                'gameState' => BMGameState::END_GAME,
                'maxWins' => 3,
                'playerArray' => array(
                    array('playerId' => 102),
                    array('playerId' => 150)
                )
            ),
            array(
                'activePlayerIdx' => 0,
                'gameState' => BMGameState::END_GAME,
                'maxWins' => 3,
                'playerArray' => array(
                    array('playerId' => 3),
                    array('playerId' => 26)
                )
            )
        );

        $this->object->proceed_to_next_user_action();

        $this->assertCount(2, $this->object->gameIdArrayArray);
        $this->assertCount(2, $this->object->gameIdArrayArray[1]);
        $this->assertEquals(array(57, 101, 150, 26), $this->object->remainingPlayerIdArray());

        foreach ($this->object->gameIdArrayArray[1] as $gameId) {
            $game = $loadGameMethod->invokeArgs($interfaceGameInstance, array($gameId));
            $game->gameState = BMGameState::END_GAME;
            $game->gameScoreArrayArray = array(
                array('W' => 3, 'L' => 1, 'D' => 0),
                array('W' => 1, 'L' => 3, 'D' => 0)
            );

            $saveGameMethod->invokeArgs($interfaceGameInstance, array($game));
        }

        $this->object->proceed_to_next_user_action();

        $this->assertCount(3, $this->object->gameIdArrayArray);
        $this->assertCount(1, $this->object->gameIdArrayArray[2]);
        $this->assertEquals(array(57, 150), $this->object->remainingPlayerIdArray());

        // this should only be one game, but keep structure to ensure that the
        // same logic as before is being maintained
        foreach ($this->object->gameIdArrayArray[2] as $gameId) {
            $game = $loadGameMethod->invokeArgs($interfaceGameInstance, array($gameId));
            $game->gameState = BMGameState::END_GAME;
            $game->gameScoreArrayArray = array(
                array('W' => 2, 'L' => 3, 'D' => 1),
                array('W' => 3, 'L' => 2, 'D' => 1)
            );

            $saveGameMethod->invokeArgs($interfaceGameInstance, array($game));
        }

        $this->object->proceed_to_next_user_action();

        $this->assertCount(3, $this->object->gameIdArrayArray);
        $this->assertEquals(array(150), $this->object->remainingPlayerIdArray());
        $this->assertEquals(BMTournamentState::END_TOURNAMENT, $this->object->tournamentState);
    }
}
