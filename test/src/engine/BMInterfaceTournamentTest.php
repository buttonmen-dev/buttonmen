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

    public function test_truncate_tournament_description() {
        $reflection = new ReflectionMethod($this->object, 'truncate_tournament_description');
        $reflection->setAccessible(true);

        $description = 'short';
        $shortDescription = $reflection->invoke($this->object, $description);
        $this->assertEquals($description, $shortDescription, 'Short descriptions should not be truncated');

        $description = '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890';
        $shortDescription = $reflection->invoke($this->object, $description);
        $this->assertEquals(
            '12345678901234567890123456789012345678901234567890' .
            '12345678901234567890123456789012345678901234567890' .
            '12345678901234567890123456789012345678901234567890' .
            '12345678901234567890123456789012345678901234567890' .
            '123456789012345678901234567890...',
            $shortDescription,
            'Long text descriptions should be truncated appropriately'
        );

        $description = '[forum=1,6]text[/forum]456789012345678901234567890' .
                       '[forum=1,6]text[/forum]456789012345678901234567890' .
                       '[forum=1,6]text[/forum]456789012345678901234567890' .
                       '[forum=1,6]text[/forum]456789012345678901234567890' .
                       '[forum=1,6]text[/forum]456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890';
        $shortDescription = $reflection->invoke($this->object, $description);
        $this->assertEquals(
            '[forum=1,6]text[/forum]456789012345678901234567890' .
            '[forum=1,6]text[/forum]456789012345678901234567890' .
            '[forum=1,6]text[/forum]456789012345678901234567890' .
            '[forum=1,6]text[/forum]456789012345678901234567890' .
            '[forum=1,6]text[/forum]4567890...',
            $shortDescription,
            'Early markup should not be removed'
        );

        $description = '[forum=1,6]text[/forum]456789012345678901234567890' .
                       '[forum=1,6]text[/forum]456789012345678901234567890' .
                       '[forum=1,6]text[/forum]456789012345678901234567890' .
                       '[forum=1,6]text[/forum]456789012345678901234567890' .
                       '1234567890[forum=1,6]text[/forum]45678901234567890' .
                       '12345678901234567890123456789012345678901234567890';
        $shortDescription = $reflection->invoke($this->object, $description);
        $this->assertEquals(
            'text456789012345678901234567890' .
            'text456789012345678901234567890' .
            'text456789012345678901234567890' .
            'text456789012345678901234567890' .
            '1234567890text4567890123456789012345678901234567890' .
            '123456789012345678901234567890...',
            $shortDescription,
            'Late square close brackets should trigger BBCode removal'
        );
    }
}
