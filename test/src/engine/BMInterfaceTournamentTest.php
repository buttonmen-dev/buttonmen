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

    /**
     * @covers BMInterfaceTournament::truncate_tournament_description
     */
    public function test_truncate_tournament_description() {
        $reflection = new ReflectionMethod($this->object, 'truncate_tournament_description');
        $reflection->setAccessible(true);

        $roundDescription = 'Tournament Round 1';

        $description = 'short';
        $shortDescription = $reflection->invoke($this->object, $description, $roundDescription);
        $this->assertEquals($description, $shortDescription, 'Short descriptions should not be truncated');

        $description = '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890';
        $shortDescription = $reflection->invoke($this->object, $description, $roundDescription);
        $this->assertEquals(
            '12345678901234567890123456789012345678901234567890' .
            '12345678901234567890123456789012345678901234567890' .
            '12345678901234567890123456789012345678901234567890' .
            '12345678901234567890123456789012345678901234567890' .
            '12345678901234567890123456789012...',
            $shortDescription,
            'Long text descriptions should be truncated appropriately'
        );

        $description = '[forum=1,6]text[/forum]56789012345678901234567890' .
                       '[forum=1,6]text[/forum]56789012345678901234567890' .
                       '[forum=1,6]text[/forum]56789012345678901234567890' .
                       '[forum=1,6]text[/forum]56789012345678901234567890' .
                       '[forum=1,6]text[/forum]56789012345678901234567890' .
                       '12345678901234567890123456789012345678901234567890';
        $shortDescription = $reflection->invoke($this->object, $description, $roundDescription);
        $this->assertEquals(
            'text56789012345678901234567890' .
            'text56789012345678901234567890' .
            'text56789012345678901234567890' .
            'text56789012345678901234567890' .
            'text56789012345678901234567890' .
            '12345678901234567890123456789012345678901234567890',
            $shortDescription,
            'Markup should be removed even with no late BBCode'
        );

        $description = '[forum=1,6]text[/forum]56789012345678901234567890' .
                       '[forum=1,6]text[/forum]56789012345678901234567890' .
                       '[forum=1,6]text[/forum]56789012345678901234567890' .
                       '[forum=1,6]text[/forum]56789012345678901234567890' .
                       '1234567890[forum=1,6]text[/forum]45678901234567890' .
                       '12345678901234567890123456789012345678901234567890';
        $shortDescription = $reflection->invoke($this->object, $description, $roundDescription);
        $this->assertEquals(
            'text56789012345678901234567890' .
            'text56789012345678901234567890' .
            'text56789012345678901234567890' .
            'text56789012345678901234567890' .
            '1234567890text45678901234567890' .
            '12345678901234567890123456789012345678901234567890',
            $shortDescription,
            'Markup should be removed with late BBCode'
        );
    }

    /**
     * @covers BMInterfaceTournament::strip_nonessential_bbcode
     */
    public function test_strip_nonessential_bbcode() {
        $reflection = new ReflectionMethod($this->object, 'strip_nonessential_bbcode');
        $reflection->setAccessible(true);

        $text = '[button=Abe Caine] is [b][i]very[/i] annoying[/b] ' .
                '[spoiler]according to [player=tasha][/spoiler], ' .
                'see [forum=1335,32790]this forum thread[/forum]';
        $strippedText = $reflection->invoke($this->object, $text);
        $this->assertEquals(
            '[button=Abe Caine] is very annoying ' .
            '[spoiler]according to [player=tasha][/spoiler], ' .
            'see this forum thread',
            $strippedText,
            'BBCode stripping should be correct'
        );
    }
}
