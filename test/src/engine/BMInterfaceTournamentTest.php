<?php

require_once 'BMInterfaceTestAbstract.php';

class BMInterfaceTournamentTest extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterfaceTournament(TRUE);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceTournament::create_tournament
     */
    public function test_create_tournament(

    ) {

    }

}
