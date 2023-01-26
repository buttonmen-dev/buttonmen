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

        $this->object->proceed_to_next_user_action();

        // manually add the games, which would normally happen in BMInterfaceTournament->save_tournament()
                $game1 = new BMGame;
        $game1->activePlayerIdx = 0;
        $game1->gameState = BMGameState::END_GAME;
        $game1->maxWins = 3;

        $game2 = clone $game1;
        $game3 = clone $game1;
        $game4 = clone $game1;

        $playerArray1 = array(new BMPlayer, new BMPlayer);
        $playerArray1[0]->playerId = 45;
        $playerArray1[1]->playerId = 57;
        $game1->playerArray = $playerArray1;
        $game1->gameScoreArrayArray = array(
            array('W' => 0, 'L' => 3, 'D' => 0),
            array('W' => 3, 'L' => 0, 'D' => 0)
        );

        $playerArray2 = array(new BMPlayer, new BMPlayer);
        $playerArray2[0]->playerId = 23;
        $playerArray2[1]->playerId = 101;
        $game2->playerArray = $playerArray2;
        $game2->gameScoreArrayArray = array(
            array('W' => 0, 'L' => 3, 'D' => 0),
            array('W' => 3, 'L' => 0, 'D' => 0)
        );

        $playerArray3 = array(new BMPlayer, new BMPlayer);
        $playerArray3[0]->playerId = 102;
        $playerArray3[1]->playerId = 150;
        $game3->playerArray = $playerArray3;
        $game3->gameScoreArrayArray = array(
            array('W' => 0, 'L' => 3, 'D' => 0),
            array('W' => 3, 'L' => 0, 'D' => 0)
        );

        $playerArray4 = array(new BMPlayer, new BMPlayer);
        $playerArray4[0]->playerId = 3;
        $playerArray4[1]->playerId = 26;
        $game4->playerArray = $playerArray4;
        $game4->gameScoreArrayArray = array(
            array('W' => 0, 'L' => 3, 'D' => 0),
            array('W' => 3, 'L' => 0, 'D' => 0)
        );

        $this->object->gameArrayArray = array(
            array($game1, $game2, $game3, $game4)
        );

        $this->object->gameIdArrayArray = array(
            array(55, 56, 57, 58)
        );

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

        $this->object->proceed_to_next_user_action();

        $this->assertCount(2, $this->object->gameIdArrayArray);

        // manually add the games, which would normally happen in BMInterfaceTournament->save_tournament()
        $game5 = new BMGame;
        $game5->activePlayerIdx = 0;
        $game5->gameState = BMGameState::END_GAME;
        $game5->maxWins = 3;

        $game6 = clone $game5;

        $playerArray5 = array(new BMPlayer, new BMPlayer);
        $playerArray5[0]->playerId = 57;
        $playerArray5[1]->playerId = 101;
        $game5->playerArray = $playerArray5;
        $game5->gameScoreArrayArray = array(
            array('W' => 3, 'L' => 1, 'D' => 0),
            array('W' => 1, 'L' => 3, 'D' => 0)
        );

        $playerArray6 = array(new BMPlayer, new BMPlayer);
        $playerArray6[0]->playerId = 150;
        $playerArray6[1]->playerId = 26;
        $game6->playerArray = $playerArray6;
        $game6->gameScoreArrayArray = array(
            array('W' => 3, 'L' => 1, 'D' => 0),
            array('W' => 1, 'L' => 3, 'D' => 0)
        );

        $this->object->gameArrayArray = array(
            array($game1, $game2, $game3, $game4),
            array($game5, $game6)
        );

        $this->object->gameIdArrayArray = array(
            array(55, 56, 57, 58),
            array(67, 68)
        );

        $this->assertCount(2, $this->object->gameIdArrayArray[1]);
        $this->assertEquals(array(57, 101, 150, 26), $this->object->remainingPlayerIdArray());

        $this->object->proceed_to_next_user_action();

        $this->assertCount(3, $this->object->gameIdArrayArray);

        // manually add the games, which would normally happen in BMInterfaceTournament->save_tournament()
        $game7 = new BMGame;
        $game7->activePlayerIdx = 0;
        $game7->gameState = BMGameState::END_GAME;
        $game7->maxWins = 3;

        $playerArray7 = array(new BMPlayer, new BMPlayer);
        $playerArray7[0]->playerId = 57;
        $playerArray7[1]->playerId = 150;
        $game7->playerArray = $playerArray7;
        $game7->gameScoreArrayArray = array(
            array('W' => 2, 'L' => 3, 'D' => 1),
            array('W' => 3, 'L' => 2, 'D' => 1)
        );

        $this->object->gameArrayArray = array(
            array($game1, $game2, $game3, $game4),
            array($game5, $game6),
            array($game7)
        );

        $this->object->gameIdArrayArray = array(
            array(55, 56, 57, 58),
            array(67, 68),
            array(90)
        );

        $this->assertCount(1, $this->object->gameIdArrayArray[2]);
        $this->assertEquals(array(57, 150), $this->object->remainingPlayerIdArray());

        $this->object->proceed_to_next_user_action();

        $this->assertCount(3, $this->object->gameIdArrayArray);
        $this->assertEquals(array(150), $this->object->remainingPlayerIdArray());
        $this->assertEquals(BMTournamentState::END_TOURNAMENT, $this->object->tournamentState);
    }
}
