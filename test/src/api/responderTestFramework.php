<?php

/**
 * responderTestFramework: framework and common functionality for responder tests
 *
 * This file contains no tests itself, but is the parent class used
 * by all responder*Test.php files, and should define all helper
 * routines and variables for use by those files.
 */

// Mock auth_session_exists() for unit test use
$dummyUserLoggedIn = FALSE;
function auth_session_exists() {
    global $dummyUserLoggedIn;
    return $dummyUserLoggedIn;
}

// Dieroller values which may change over time

// RandomBM skills (used by all RandomBM types except RandomBMFixed)
$RANDOMBM_SKILL_ARRAY = array(
  'B', // Berserk
  'b', // Boom
  'c', // Chance
  'f', // Focus
  'I', // Insult
  'J', // Jolt
  'k', // Konstant
  'M', // Maximum
  'H', // Mighty
  'n', // Null
  'o', // Ornery
  'p', // Poison
  'q', // Queer
  'G', // Rage
  '#', // Rush
  's', // Shadow
  'z', // Speed
  'd', // Stealth
  'g', // Stinger
  '^', // TimeAndSpace
  't', // Trip
  'v', // Value
  'h', // Weak
);
$RANDOMBM_SKILL = array_flip($RANDOMBM_SKILL_ARRAY);

class responderTestFramework extends PHPUnit_Framework_TestCase {

    /**
     * @var spec         ApiSpec object which will be used as a helper
     * @var game_number  a static number for each full-game test, so test game data can be used by UI tests
     * @var move_number  an increment counter for each move in each full-game test, so test game data can be used by UI tests
     */
    protected $spec;
    protected $user_ids;
    protected $game_number;
    protected $move_number;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {

        // setup the test interface
        //
        // The multiple paths are to deal with the many diverse testing
        // environments that we have, and their different assumptions about
        // which directory is the unit test run directory.
        if (file_exists('../test/src/database/mysql.test.inc.php')) {
            require_once '../test/src/database/mysql.test.inc.php';
        } else {
            require_once 'test/src/database/mysql.test.inc.php';
        }

        if (file_exists('../src/api/ApiResponder.php')) {
            require_once '../src/api/ApiResponder.php';
            require_once '../src/api/ApiSpec.php';
        } else {
            require_once 'src/api/ApiResponder.php';
            require_once 'src/api/ApiSpec.php';
        }
        $this->spec = new ApiSpec();

        // Cache user IDs parsed from the DB for use within a test
        $this->user_ids = array();

        // Reset game_number and move_number at the beginning of each test
        $this->game_number = 0;
        $this->move_number = 0;

        // Directory to cache JSON output for UI tests to use
        $this->jsonApiRoot = BW_PHP_ROOT . "/api/dummy_data/";

        // Parent directory for cached JSON API output
        if (!file_exists($this->jsonApiRoot)) {
            mkdir($this->jsonApiRoot);
        }

        // Tests in this file should override randomization, so
        // force overrides and reset the queue at the beginning of each test
        global $BM_RAND_VALS, $BM_RAND_REQUIRE_OVERRIDE;
        $BM_RAND_VALS = array();
        $BM_RAND_REQUIRE_OVERRIDE = TRUE;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

        // By default, tests use normal randomization, so always
        // reset overrides and empty the queue between tests
        global $BM_RAND_VALS, $BM_RAND_REQUIRE_OVERRIDE;
        $BM_RAND_VALS = array();
        $BM_RAND_REQUIRE_OVERRIDE = FALSE;
    }

    /**
     * Utility function to get skill information for use in games
     */
    protected function get_skill_info($skillNames) {
        $skillInfo = array(
            'Auxiliary' => array(
                'code' => '+',
                'description' => 'These are optional extra dice. Before each game, both players decide whether or not to play with their Auxiliary Dice. Only if both players choose to have them will they be in play.',
                'interacts' => array(),
            ),
            'Berserk' => array(
                'code' => 'B',
                'description' => 'These dice cannot participate in Skill Attacks; instead they can make a Berserk Attack. These work exactly like Speed Attacks - one Berserk Die can capture any number of dice which add up exactly to its value. Once a Berserk Die performs a Berserk Attack, it is replaced with a non-berserk die with half the number of sides it previously had, rounding up.',
                'interacts' => array(
                    'Mighty' => 'Dice with both Berserk and Mighty skills will first halve in size, and then grow',
                    'Radioactive' => 'Dice with both Radioactive and Berserk skills making a berserk attack targeting a SINGLE die are first replaced with non-berserk dice with half their previous number of sides, rounding up, and then decay',
                    'Speed' => 'Dice with both Berserk and Speed skills may choose to make either kind of attack',
                    'Turbo' => 'Dice with both Berserk and Turbo making a berserk attack will first halve in size and then change to the size specified by the Turbo skill',
                    'Weak' => 'Dice with both Berserk and Weak skills will first halve in size, and then shrink',
                ),
            ),
            'Boom' => array(
                'code' => 'b',
                'description' => 'Boom Dice are like normal dice with an additional attack, the Boom attack. To make a Boom Attack, remove one of your Boom Dice from play (neither player will score it). Choose one of your opponent\'s dice, and reroll it. Note that the targeted die is never captured, just re-rolled.',
                'interacts' => array(
                    'Stealth' => 'Stealth dice may be targeted by boom attacks',
                ),
            ),
            'Chance' => array(
                'code' => 'c',
                'description' => 'If you do not have the initiative at the start of a round you may re-roll one of your Chance Dice. If this results in you gaining the initiative, your opponent may re-roll one of their Chance Dice. This can continue with each player re-rolling Chance Dice, even re-rolling the same die, until one person fails to gain the initiative or lets their opponent go first. Re-rolling Chance Dice is not only a way to gain the initiative; it can also be useful in protecting your larger dice, or otherwise improving your starting roll. Unlike Focus Dice, Chance Dice can be immediately re-used in an attack even if you do gain the initiative with them.',
                'interacts' => array(
                    'Focus' => 'Dice with both Chance and Focus skills may choose either skill to gain initiative',
                    'Konstant' => 'Dice with both Chance and Konstant skills retain their current value ' .
                                  'when rerolled due to Chance',
                    'Mighty' => 'A reroll of a Chance Mighty die due to the Chance skill triggers the Mighty skill',
                    'Weak' => 'A reroll of a Chance Weak die due to the Chance skill triggers the Weak skill',
                ),
            ),
            'Doppelganger' => array(
                'code' => 'D',
                'description' => 'When a Doppelganger Die performs a Power Attack on another die, the Doppelganger Die becomes an exact copy of the die it captured. The newly copied die is then rerolled, and has all the abilities of the captured die. For instance, if a Doppelganger Die copies a Turbo Swing Die, then it may change its size as per the rules of Turbo Swing Dice. Usually a Doppelganger Die will lose its Doppelganger ability when it copies another die, unless that die is itself a Doppelganger Die.',
                'interacts' => array(
                    'Radioactive' => 'Dice with both Radioactive and Doppelganger first decay, then each of the "decay products" are replaced by exact copies of the die they captured',
                    'Rage' => 'A Doppelganger die that captures a Rage die with a Power attack will retain Rage after it transforms',
                ),
            ),
            'Echo' => array(
                'code' => '',
                'description' => 'Copies the opponent\'s button recipe.',
                'interacts' => array(),
            ),
            'Fire' => array(
                'code' => 'F',
                'description' => 'Fire Dice cannot make Power Attacks. Instead, they can assist other Dice in making Skill and Power Attacks. Before making a Skill or Power Attack, you may increase the value showing on any of the attacking dice, and decrease the values showing on one or more of your Fire Dice by the same amount. For example, if you wish to increase the value of an attacking die by 5 points, you can take 5 points away from one or more of your Fire Dice. Turn the Fire Dice to show the adjusted values, and then make the attack as normal. Dice can never be increased or decreased outside their normal range, i.e., a 10-sided die can never show a number lower than 1 or higher than 10. Also, Fire Dice cannot assist other dice in making attacks other than normal Skill and Power Attacks.',
                'interacts' => array(
                    'Mighty' => 'Dice with both Fire and Mighty skills do not grow when firing, only when actually rolling',
                    'Weak' => 'Dice with both Fire and Weak skills do not shrink when firing, only when actually rolling',
                ),
            ),
            'Focus' => array(
                'code' => 'f',
                'description' => 'If you do not have the initiative at the start of a round you may reduce the values showing on one or more of your Focus Dice. You may only do this if it results in your gaining the initiative. If your opponent has Focus Dice, they may now do the same, and each player may respond by turning down one or more Focus Dice until no further moves are legal, or until one player allows the other to take the first turn. IMPORTANT: If you go first, any Focus Dice you have reduced may not be used as part of your first attack. (The second player has no such restriction.)',
                'interacts' => array(
                    'Chance' => 'Dice with both Chance and Focus skills may choose either skill to gain initiative',
                    'Konstant' => 'Dice with both Focus and Konstant skills may be turned down to gain initiative',
                ),
            ),
            'Giant' => array(
                'code' => '',
                'description' => 'Cannot win initiative.',
                'interacts' => array(),
            ),
            'Gordo' => array(
                'code' => '',
                'description' => 'None of Gordo\'s dice can be the same size. Note that a player playing Gordo is required to decline an auxiliary die that would force two of Gordo\'s dice to be the same size',
                'interacts' => array(),
            ),
            'Guillermo' => array(
                'code' => '',
                'description' => 'Different swing types must be assigned unique values',
                'interacts' => array(),
            ),
            'Insult' => array(
                'code' => 'I',
                'description' => 'Cannot be attacked by skill attacks.',
                'interacts' => array(),
            ),
            'Jolt' => array(
                'code' => 'J',
                'description' => 'If a Jolt Die participates in an attack, then the player will take another turn and the Jolt die loses the Jolt skill. If a Jolt die is captured, then the player who captured it takes another turn.',
                'interacts' => array(
                    'TimeAndSpace' => 'If a die with both the Jolt and TimeAndSpace skills rerolls to an odd number, it still only gives one extra turn.',
                ),
            ),
            'Konstant' => array(
                'code' => 'k',
                'description' => 'These dice do not reroll after an attack; they keep their current value. Konstant dice can not Power Attack, and cannot perform a Skill Attack by themselves, but they can add OR subtract their value in a multi-dice Skill Attack. If another skill causes a Konstant die to reroll (e.g., Chance, Trip, Ornery), it continues to show the same value. If another skill causes the die to change its value without rerolling (e.g., Focus, Fire), the die\'s value does change and then continues to show that new value.',
                'interacts' => array(
                    'Chance' => 'Dice with both Chance and Konstant skills retain their current value ' .
                                'when rerolled due to Chance',
                    'Focus' => 'Dice with both Focus and Konstant skills may be turned down to gain initiative',
                    'Maximum' => 'Dice with both Konstant and Maximum retain their current value when rerolled',
                    'Ornery' => 'Dice with both Konstant and Ornery skills retain their current value when rerolled',
                    'TimeAndSpace' => 'Attacking Konstant TimeAndSpace dice do not trigger the TimeAndSpace skill because they do not reroll',
                    'Trip' => 'Dice with both Konstant and Trip skills retain their current value when rerolled',
                ),
            ),
            'Mad' => array(
                'code' => '&',
                'description' => 'These are a subcategory of Swing dice, whose size changes randomly when rerolled. At the very start of the game (and again after any round they lose, just as with normal Swing dice) the player sets the initial size of Mad Swing dice, but from then on whenever they are rolled their size is set randomly to any even-numbered legal size for that Swing type. The initial size of a Mad Swing die may be set to an odd number.',
                'interacts' => array(
                    'Ornery' => 'Dice with both Ornery and Mad Swing have their sizes randomized during ornery rerolls',
                    'Radioactive' => 'Dice with the Mad skill lose Mad when they decay due to Radioactive',
                ),
            ),
            'Maximum' => array(
                'code' => 'M',
                'description' => 'Maximum dice always roll their maximum value.',
                'interacts' => array(
                    'Konstant' => 'Dice with both Konstant and Maximum retain their current value when rerolled',
                ),
            ),
            'Mighty' => array(
                'code' => 'H',
                'description' => 'When a Mighty Die rerolls for any reason, it first grows from its current size to the next larger size in the list of "standard" die sizes (1, 2, 4, 6, 8, 10, 12, 16, 20, 30).',
                'interacts' => array(
                    'Berserk' => 'Dice with both Berserk and Mighty skills will first halve in size, and then grow',
                    'Chance' => 'A reroll of a Chance Mighty die due to the Chance skill triggers the Mighty skill',
                    'Fire' => 'Dice with both Fire and Mighty skills do not grow when firing, only when actually rolling',
                ),
            ),
            'Mood' => array(
                'code' => '?',
                'description' => 'These are a subcategory of Swing dice, whose size changes randomly when rerolled. At the very start of the game (and again after any round they lose, just as with normal Swing dice) the player sets the initial size of Mood Swing dice, but from then on whenever they are rolled their size is set randomly to that of a "real-world" die (i.e. 1, 2, 4, 6, 8, 10, 12, 20, or 30 sides) within the range allowable for that Swing type.',
                'interacts' => array(
                    'Ornery' => 'Dice with both Ornery and Mood Swing have their sizes randomized during ornery rerolls',
                    'Radioactive' => 'Dice with the Mood skill lose Mood when they decay due to Radioactive',
                ),
            ),
            'Morphing' => array(
                'code' => 'm',
                'description' => 'When a Morphing Die is used in any attack against a single target die, it changes size, becoming the same size as the die that was captured. It is then re-rolled. Morphing Dice change size every time they capture another die. If a Morphing die is captured, its scoring value is based on its size at the time of capture; likewise, if it is not captured during a round, its scoring value is based on its size at the end of the round',
                'interacts' => array(
                     'Radioactive' => 'Dice with both Radioactive and Morphing skills first morph into the size of the captured die, and then decay',
                ),
            ),
            'Null' => array(
                'code' => 'n',
                'description' => 'When a Null Die participates in any attack, the dice that are captured are worth zero points. Null Dice themselves are worth zero points.',
                'interacts' => array(
                     'Poison' => 'Dice with both Null and Poison skills are Null',
                     'Value' => 'Dice with both Null and Value skills are Null',
                ),
            ),
            'Oregon' => array(
                'code' => '',
                'description' => 'Different swing types must be assigned unique values',
                'interacts' => array(),
            ),
            'Ornery' => array(
                'code' => 'o',
                'description' => 'Ornery dice reroll every time the player makes any attack - whether the Ornery dice participated in it or not. The only time they don\'t reroll is if the player passes, making no attack whatsoever.',
                'interacts' => array(
                    'Konstant' => 'Dice with both Konstant and Ornery skills retain their current value when rerolled',
                    'Mad' => 'Dice with both Ornery and Mad Swing have their sizes randomized during ornery rerolls',
                    'Mood' => 'Dice with both Ornery and Mood Swing have their sizes randomized during ornery rerolls',
                ),
            ),
            'Poison' => array(
                'code' => 'p',
                'description' => 'These dice are worth negative points. If you keep a Poison Die of your own at the end of a round, subtract its full value from your score. If you capture a Poison Die from someone else, subtract half its value from your score.',
                'interacts' => array(
                     'Null' => 'Dice with both Null and Poison skills are Null',
                     'Value' => 'Dice with both Poison and Value skills are Poison dice that score based on the negative of their current value rather than on their number of sides',
                ),
            ),
            'Queer' => array(
                'code' => 'q',
                'description' => 'These dice behave like normal dice when they show an even number, and like Shadow Dice when they show an odd number.',
                'interacts' => array(
                    'Trip' => 'Dice with both Queer and Trip skills always determine their success or failure at Trip Attacking via a Power Attack',
                ),
            ),
            'Radioactive' => array(
                'code' => '%',
                'description' => 'If a radioactive die is either the attacking die or the target die in an attack with a single attacking die and a single target die, the attacking die splits, or "decays", into two as-close-to-equal-sized-as-possible dice that add up to its original size. All dice that decay lose the following skills: Radioactive (%), Turbo (!), Mad Swing (&), Mood Swing (?), Time and Space (^), [and, not yet implemented: Jolt (J)]. For example, a s(X=15)! (Shadow Turbo X Swing with 15 sides) that shadow attacked a radioactive die would decay into a s(X=7) die and a s(X=8) die, losing the turbo skill. A %p(7,13) on a power attack would decay into a p(3,7) and a p(4,6), losing the radioactive skill.',
                'interacts' => array(
                    'Berserk' => 'Dice with both Radioactive and Berserk skills making a berserk attack targeting a SINGLE die are first replaced with non-berserk dice with half their previous number of sides, rounding up, and then decay',
                    'Doppelganger' => 'Dice with both Radioactive and Doppelganger first decay, then each of the "decay products" are replaced by exact copies of the die they captured',
                    'Mad' => 'Dice with the Mad skill lose Mad when they decay due to Radioactive',
                    'Mood' => 'Dice with the Mood skill lose Mood when they decay due to Radioactive',
                    'Morphing' => 'Dice with both Radioactive and Morphing skills first morph into the size of the captured die, and then decay',
                    'TimeAndSpace' => 'Dice with the TimeAndSpace skill lose TimeAndSpace when they decay due to Radioactive',
                    'Turbo' => 'Dice with the Turbo skill lose Turbo when they decay due to Radioactive',
                ),
            ),
            'TheFlyingSquirrel' => array(
                'code' => '',
                'description' => 'Cannot perform skill attacks.',
                'interacts' => array(),
            ),
            'TheJapaneseBeetle' => array(
                'code' => '',
                'description' => 'Cannot be attacked by skill attacks.',
                'interacts' => array(),
            ),
            'RandomBMAnime' => array(
                'code' => '',
                'description' => '4 normal dice and 4 reserve dice, chosen from standard die sizes.',
                'interacts' => array(),
            ),
            'RandomBMDuoskill' => array(
                'code' => '',
                'description' => 'Four regular dice and one swing die, and 2 skills each appearing a total of 2 times on various dice.',
                'interacts' => array(),
            ),
            'RandomBMFixed' => array(
                'code' => '',
                'description' => '5 dice, no swing dice, two of them having a single skill chosen from c, f, and d (the same skill on both).',
                'interacts' => array(),
            ),
            'RandomBMMixed' => array(
                'code' => '',
                'description' => '5 dice, no swing dice, three skills chosen from all existing skills except !%&+?DF`mrw, with each skill dealt out twice randomly and independently over all dice.',
                'interacts' => array(),
            ),
            'RandomBMMonoskill' => array(
                'code' => '',
                'description' => 'Four regular dice and one swing die, and 1 skill appearing a total of 2 times on various dice.',
                'interacts' => array(),
            ),
            'RandomBMPentaskill' => array(
                'code' => '',
                'description' => 'Four regular dice and one swing die, and 5 skills each appearing a total of 2 times on various dice.',
                'interacts' => array(),
            ),
            'RandomBMSoldiers' => array(
                'code' => '',
                'description' => 'A recipe similar to the Soldiers set: Four regular dice and one X swing die, no skills.',
                'interacts' => array(),
            ),
            'RandomBMTetraskill' => array(
                'code' => '',
                'description' => 'Four regular dice and one swing die, and 4 skills each appearing a total of 2 times on various dice.',
                'interacts' => array(),
            ),
            'RandomBMTriskill' => array(
                'code' => '',
                'description' => 'Four regular dice and one swing die, and 3 skills each appearing a total of 2 times on various dice.',
                'interacts' => array(),
            ),
            'RandomBMVanilla' => array(
                'code' => '',
                'description' => '5 dice, no swing dice, no skills.',
                'interacts' => array(),
            ),
            'Rage' => array(
                'code' => 'G',
                'description' => 'If a Rage die is captured, then the owner of the Rage Die adds a new die to their pool of the same size and ability of the Rage die that was taken, except that it loses the Rage ability. If a Rage Die participates in an Attack, it loses its Rage ability. IMPORTANT: Rage dice do not count for determining who goes first.',
                'interacts' => array(
                    'Doppelganger' => 'A Doppelganger die that captures a Rage die with a Power attack will retain Rage after it transforms',
                ),
            ),
            'Reserve' => array(
                'code' => 'r',
                'description' => 'These are extra dice which may be brought into play part way through a game. Each time you lose a round you may choose another of your Reserve Dice; it will then be in play for all future rounds.',
                'interacts' => array(),
            ),
            'Rush' => array(
                'code' => '#',
                'description' => 'A Rush Die can perform a Rush Attack, in which it can capture two enemy dice with values that add up exactly to its value. However, Rush Dice are also vulnerable to the same kind of attack. Any die can make a Rush Attack if the target dice include at least one Rush Die.',
                'interacts' => array(),
            ),
            'Shadow' => array(
                'code' => 's',
                'description' => 'These dice are normal in all respects, except that they cannot make Power Attacks. Instead, they make inverted Power Attacks, called "Shadow Attacks." To make a Shadow Attack, use one of your Shadow Dice to capture one of your opponent\'s dice. The number showing on the die you capture must be greater than or equal to the number showing on your die, but within its range. For example, a shadow 10-sided die showing a 2 can capture a die showing any number from 2 to 10.',
                'interacts' => array(
                    'Stinger' => 'Dice with both Shadow and Stinger skills can singly attack with any value from the min to the max of the die (making a shadow attack against a die whose value is greater than or equal to their own, or a skill attack against a die whose value is lower than or equal to their own)',
                    'Trip' => 'Dice with both Shadow and Trip skills always determine their success or failure at Trip Attacking via a Power Attack',
                ),
            ),
            'Slow' => array(
                'code' => 'w',
                'description' => 'These dice are not counted for the purposes of initiative.',
                'interacts' => array(),
            ),
            'Speed' => array(
                'code' => 'z',
                'description' => 'These dice can also make Speed Attacks, which are the equivalent of inverted Skill Attacks. In a Speed Attack, one Speed Die can capture any number of dice which add up exactly to its value.',
                'interacts' => array(
                    'Berserk' => 'Dice with both Berserk and Speed skills may choose to make either kind of attack',
                ),
            ),
            'Stealth' => array(
                'code' => 'd',
                'description' => 'These dice cannot perform any type of attack other than Multi-die Skill Attacks, meaning two or more dice participating in a Skill Attack. In addition, Stealth Dice cannot be captured by any attack other than a Multi-die Skill Attack.',
                'interacts' => array(
                    'Boom' => 'Stealth dice may be targeted by boom attacks',
                ),
            ),
            'Stinger' => array(
                'code' => 'g',
                'description' => 'When a Stinger Die participates in a Skill Attack, it can be used as any number between its minimum possible value and the value it currently shows. Thus, a normal die showing 4 and a Stinger Die showing 6 can make a Skill Attack on any die showing 5 through 10. Two Stinger Dice showing 10 can Skill Attack any die between 2 and 20. IMPORTANT: Stinger Dice do not count for determining who goes first.',
                'interacts' => array(
                    'Shadow' => 'Dice with both Shadow and Stinger skills can singly attack with any value from the min to the max of the die (making a shadow attack against a die whose value is greater than or equal to their own, or a skill attack against a die whose value is lower than or equal to their own)',
                ),
            ),
            'TimeAndSpace' => array(
                'code' => '^',
                'description' => 'If a Time and Space Die participates in an attack and rerolls an odd number, then the player will take another turn. If multiple Time and Space dice are rerolled and show odd, only one extra turn is given per reroll.',
                'interacts' => array(
                    'Jolt' => 'If a die with both the Jolt and TimeAndSpace skills rerolls to an odd number, it still only gives one extra turn.',
                    'Radioactive' => 'Dice with the TimeAndSpace skill lose TimeAndSpace when they decay due to Radioactive',
                    'Konstant' => 'Attacking Konstant TimeAndSpace dice do not trigger the TimeAndSpace skill because they do not reroll',
                ),
            ),
            'Trip' => array(
                'code' => 't',
                'description' => 'These dice can also make Trip Attacks. To make a Trip Attack, choose any one opposing die as the Target. Roll both the Trip Die and the Target, then compare the numbers they show. If the Trip Die now shows an equal or greater number than the Target, the Target is captured. Otherwise, the attack merely has the effect of re-rolling both dice. A Trip Attack is illegal if it has no chance of capturing (this is possible in the case of a Trip-1 attacking a Twin Die). IMPORTANT: Trip Dice do not count for determining who goes first.',
                'interacts' => array(
                    'Konstant' => 'Dice with both Konstant and Trip skills retain their current value when rerolled',
                    'Queer' => 'Dice with both Queer and Trip skills always determine their success or failure at Trip Attacking via a Power Attack',
                    'Shadow' => 'Dice with both Shadow and Trip skills always determine their success or failure at Trip Attacking via a Power Attack',
                    'Turbo' => 'If a Turbo Die is rerolled because it is the target of a Trip attack, then the size cannot be changed.',
                ),
            ),
            'Turbo' => array(
                'code' => '!',
                'description' => 'After your starting roll, you may change the size of your own Turbo Swing or Option die every time you roll it as part of your attack. Decide on a size first that is valid for the Swing or Option type, then roll the new die as usual.',
                'interacts' => array(
                    'Berserk' => 'Dice with both Berserk and Turbo making a berserk attack will first halve in size and then change to the size specified by the Turbo skill',
                    'Radioactive' => 'Dice with the Turbo skill lose Turbo when they decay due to Radioactive',
                ),
            ),
            'Value' => array(
                'code' => 'v',
                'description' => 'These dice are not scored like normal dice. Instead, a Value Die is scored as if the number of sides it has is equal to the value that it is currently showing. If a Value Die is ever part of an attack, all dice that are captured become Value Dice (i.e. They are scored by the current value they are showing when they are captured, not by their size).',
                'interacts' => array(
                     'Null' => 'Dice with both Null and Value skills are Null',
                     'Poison' => 'Dice with both Poison and Value skills are Poison dice that score based on the negative of their current value rather than on their number of sides',
                ),
            ),
            'Warrior' => array(
                'code' => '`',
                'description' => 'These are extra dice which may be brought into play during a round, by using one of them in a multi-die Skill Attack. Once a Warrior die is brought into play, it loses the Warrior skill for the rest of the round. After the round, the die regains the Warrior skill to start the next round. Dice with the Warrior skill are completely out of play: They aren\'t part of your starting dice, they don\'t count for initiative, they can\'t be attacked, none of their other skills can be used, they don\'t count for scoring purposes, etc. At the start of the round, each Warrior die shows its maximum value; when it\'s brought into play, it\'s rolled as usual. Only one Warrior Die may be used in any given Skill Attack. Adding a Warrior die to a Skill Attack is always optional; even if you have no other legal attack, you can choose to pass rather than using a Warrior die.',
                'interacts' => array(),
            ),
            'Weak' => array(
                'code' => 'h',
                'description' => 'When a Weak Die rerolls for any reason, it first shrinks from its current size to the next smaller size in the list of "standard" die sizes (1, 2, 4, 6, 8, 10, 12, 16, 20, 30).',
                'interacts' => array(
                    'Berserk' => 'Dice with both Berserk and Weak skills will first halve in size, and then shrink',
                    'Chance' => 'A reroll of a Chance Weak die due to the Chance skill triggers the Weak skill',
                    'Fire' => 'Dice with both Fire and Weak skills do not shrink when firing, only when actually rolling',
                ),
            ),
            'Zero' => array(
                'code' => '',
                'description' => 'Copies the opponent\'s button recipe.',
                'interacts' => array(),
            ),
        );
        $retval = array();
        foreach ($skillNames as $skillName) {
            $retval[$skillName] = $skillInfo[$skillName];
            $retval[$skillName]['interacts'] = array();
            foreach ($skillNames as $otherSkill) {
                if (array_key_exists($otherSkill, $skillInfo[$skillName]['interacts'])) {
                    $retval[$skillName]['interacts'][$otherSkill] = $skillInfo[$skillName]['interacts'][$otherSkill];
                }
            }
        }
        return $retval;
    }

    /**
     * Utility function to suppress non-zero timestamps in a game data option.
     * This function shouldn't do any assertions itself; that's the caller's job.
     */
    protected function squash_game_data_timestamps($gameData) {
        $modData = $gameData;
        if (is_array($modData)) {
            if (array_key_exists('timestamp', $modData) && is_int($modData['timestamp']) && $modData['timestamp'] > 0) {
                $modData['timestamp'] = 'TIMESTAMP';
            }
            if (array_key_exists('gameChatEditable', $modData) && is_int($modData['gameChatEditable']) && $modData['gameChatEditable'] > 0) {
                $modData['gameChatEditable'] = 'TIMESTAMP';
            }
            if (count($modData['gameActionLog']) > 0) {
                foreach ($modData['gameActionLog'] as $idx => $value) {
                    if (array_key_exists('timestamp', $value) && is_int($value['timestamp']) && $value['timestamp'] > 0) {
                        $modData['gameActionLog'][$idx]['timestamp'] = 'TIMESTAMP';
                    }
                }
            }
            if (count($modData['gameChatLog']) > 0) {
                foreach ($modData['gameChatLog'] as $idx => $value) {
                    if (array_key_exists('timestamp', $value) && is_int($value['timestamp']) && $value['timestamp'] > 0) {
                        $modData['gameChatLog'][$idx]['timestamp'] = 'TIMESTAMP';
                    }
                }
            }
            if (count($modData['playerDataArray']) > 0) {
                foreach ($modData['playerDataArray'] as $idx => $value) {
                    if (array_key_exists('lastActionTime', $value) && is_int($value['lastActionTime']) && $value['lastActionTime'] > 0) {
                        $modData['playerDataArray'][$idx]['lastActionTime'] = 'TIMESTAMP';
                    }
                }
            }
        }
        return $modData;
    }

    /**
     * Utility function to store generated JSON output in a place
     * where the dummy responder can find it and use it for UI tests
     */
    protected function cache_json_api_output($apiFunction, $objname, $objdata) {
        if (!file_exists($this->jsonApiRoot . $apiFunction)) {
            mkdir($this->jsonApiRoot . $apiFunction);
        }
        $jsonApiFile = $this->jsonApiRoot . $apiFunction . "/" . $objname . ".json";
        $fh = fopen($jsonApiFile, "w");
        fwrite($fh, json_encode($objdata) . "\n");
        fclose($fh);
    }

    /**
     * Check two PHP arrays to see if their structures match to a depth of one level:
     * * Do the arrays have the same sets of keys?
     * * Does each key have the same type of value for each array?
     */
    protected function object_structures_match($obja, $objb, $inspect_child_arrays=False) {
        foreach (array_keys($obja) as $akey) {
            if (!(array_key_exists($akey, $objb))) {
                $this->output_mismatched_objects($obja, $objb);
                return False;
            }
            if (gettype($obja[$akey]) != gettype($objb[$akey])) {
                $this->output_mismatched_objects($obja, $objb);
                return False;
            }
            if (($inspect_child_arrays) and (gettype($obja[$akey]) == 'array')) {
                if ((array_key_exists(0, $obja[$akey])) || (array_key_exists(0, $objb[$akey]))) {
                    if (gettype($obja[$akey][0]) != gettype($objb[$akey][0])) {
                        $this->output_mismatched_objects($obja, $objb);
                        return False;
                    }
                }
            }
        }
        foreach (array_keys($objb) as $bkey) {
            if (!(array_key_exists($bkey, $obja))) {
                $this->output_mismatched_objects($obja, $objb);
                return False;
            }
        }
        return True;
    }

    /**
     * Utility function to construct a valid array of participating
     * dice, given loadGameData output and the set of dice
     * which should be selected for the attack.  Each attacker
     * should be specified as array(playerIdx, dieIdx)
     */
    protected function generate_valid_attack_array($gameData, $participatingDice) {
        $attack = array();
        foreach ($gameData['playerDataArray'] as $playerIdx => $playerData) {
            if (count($playerData['activeDieArray']) > 0) {
                foreach (array_keys($playerData['activeDieArray']) as $dieIdx) {
                    $attack['playerIdx_' . $playerIdx . '_dieIdx_' . $dieIdx] = 'false';
                }
            }
        }
        if (count($participatingDice) > 0) {
            foreach ($participatingDice as $participatingDie) {
                $playerIdx = $participatingDie[0];
                $dieIdx = $participatingDie[1];
                $attack['playerIdx_' . $playerIdx . '_dieIdx_' . $dieIdx] = 'true';
            }
        }
        return $attack;
    }

    /**
     * Utility function to initialize a data array, just because
     * there's a lot of stuff in this, and a lot of it is always
     * the same at the beginning of a game, so save some typing.
     * This does *not* initialize buttons or active dice --- you
     * need to do that
     */
    protected function generate_init_expected_data_array(
        $gameId, $username1, $username2, $maxWins, $gameState
    ) {
        $playerId1 = $this->user_ids[$username1];
        $playerId2 = $this->user_ids[$username2];
        $expData = array(
            'gameId' => $gameId,
            'gameState' => $gameState,
            'activePlayerIdx' => NULL,
            'playerWithInitiativeIdx' => NULL,
            'roundNumber' => 1,
            'maxWins' => $maxWins,
            'description' => '',
            'previousGameId' => NULL,
            'currentPlayerIdx' => 0,
            'timestamp' => 'TIMESTAMP',
            'validAttackTypeArray' => array(),
            'gameSkillsInfo' => array(),
            'playerDataArray' => array(
                array(
                    'playerId' => $playerId1,
                    'capturedDieArray' => array(),
                    'outOfPlayDieArray' => array(),
                    'swingRequestArray' => array(),
                    'optRequestArray' => array(),
                    'prevSwingValueArray' => array(),
                    'prevOptValueArray' => array(),
                    'turboSizeArray' => array(),
                    'waitingOnAction' => TRUE,
                    'roundScore' => NULL,
                    'sideScore' => NULL,
                    'gameScoreArray' => array('W' => 0, 'L' => 0, 'D' => 0),
                    'lastActionTime' => 'TIMESTAMP',
                    'hasDismissedGame' => FALSE,
                    'canStillWin' => NULL,
                    'playerName' => $username1,
                    'playerColor' => '#dd99dd',
                    'isOnVacation' => false,
                    'isChatPrivate' => false,
                ),
                array(
                    'playerId' => $playerId2,
                    'capturedDieArray' => array(),
                    'outOfPlayDieArray' => array(),
                    'swingRequestArray' => array(),
                    'optRequestArray' => array(),
                    'prevSwingValueArray' => array(),
                    'prevOptValueArray' => array(),
                    'turboSizeArray' => array(),
                    'waitingOnAction' => TRUE,
                    'roundScore' => NULL,
                    'sideScore' => NULL,
                    'gameScoreArray' => array('W' => 0, 'L' => 0, 'D' => 0),
                    'lastActionTime' => 'TIMESTAMP',
                    'hasDismissedGame' => FALSE,
                    'canStillWin' => NULL,
                    'playerName' => $username2,
                    'playerColor' => '#ddffdd',
                    'isOnVacation' => false,
                    'isChatPrivate' => false,
                ),
            ),
            'creatorDataArray' => array(
                'creatorId' => $playerId1,
                'creatorName' => $username1,
            ),
            'gameActionLog' => array(
                array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'Game created by ' . $username1),
            ),
            'gameActionLogCount' => 1,
            'gameChatLog' => array(),
            'gameChatLogCount' => 0,
            'gameChatEditable' => FALSE,
            'dieBackgroundType' => 'realistic',
        );
        return $expData;
    }

    /**
     * Utility function to handle a normal attack in which one
     * player has captured some dice and it is now the other player's
     * turn
     */
    function update_expected_data_after_normal_attack(
        &$expData,
        $nextActivePlayer,
        $nextValidAttackTypes,
        $roundAndSideScores,
        $changeActiveDice,
        $spliceActiveDice,
        $clearPropsCapturedDice,
        $addCapturedDice
    ) {

        // Set waitingOnAction and activePlayerIdx based on whose turn it now is (after the attack)
        if ($nextActivePlayer == 0) {
            $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
            $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
            $expData['activePlayerIdx'] = 0;
        } else {
            $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
            $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
            $expData['activePlayerIdx'] = 1;
        }

        // Set valid attack types for the next attack
        $expData['validAttackTypeArray'] = $nextValidAttackTypes;

        // Set round and side scores
        $expData['playerDataArray'][0]['roundScore'] = $roundAndSideScores[0];
        $expData['playerDataArray'][1]['roundScore'] = $roundAndSideScores[1];
        $expData['playerDataArray'][0]['sideScore'] = $roundAndSideScores[2];
        $expData['playerDataArray'][1]['sideScore'] = $roundAndSideScores[3];

        // Change active dice (e.g. due to attacker rerolls)
        foreach ($changeActiveDice as $changeActiveDie) {
            $playerIdx = $changeActiveDie[0];
            $dieIdx = $changeActiveDie[1];
            $dieChanges = $changeActiveDie[2];
            foreach ($dieChanges as $key => $value) {
                $expData['playerDataArray'][$playerIdx]['activeDieArray'][$dieIdx][$key] = $value;
            }
        }

        // Splice out dice which have now been captured
        foreach ($spliceActiveDice as $spliceActiveDie) {
            $playerIdx = $spliceActiveDie[0];
            $dieIdx = $spliceActiveDie[1];
            array_splice($expData['playerDataArray'][$playerIdx]['activeDieArray'], $dieIdx, 1);
        }

        foreach ($addCapturedDice as $addCapturedDie) {
            $playerIdx = $addCapturedDie[0];
            $dieInfo = $addCapturedDie[1];
            $dieInfo['properties'] = array('WasJustCaptured');
            $expData['playerDataArray'][$playerIdx]['capturedDieArray'][] = $dieInfo;
        }

        // Make the most common update on previously-captured dice --- clear properties (i.e. WasJustCaptured)
        foreach ($clearPropsCapturedDice as $clearPropsCapturedDie) {
            $playerIdx = $clearPropsCapturedDie[0];
            $dieIdx = $clearPropsCapturedDie[1];
            $expData['playerDataArray'][$playerIdx]['capturedDieArray'][$dieIdx]['properties'] = array();
        }
    }

    /**
     * Hack warning: there is no clean interface to BMInterface's
     * random button selection, so we basically have to duplicate it
     * here.  The logic for excluding unimplemented buttons has to
     * match assemble_button_data() and the logic for picking a
     * random button from the array has to match resolve_random_button_selection()
     */
    protected function find_button_random_indices($lookForButtons) {

        // Start with the output of loadButtonData
        $retval = $this->verify_api_success(
            array(
                'type' => 'loadButtonData',
                'tagArray' => array('exclude_from_random' => 'false'),
            )
        );

        // Now exclude unimplemented buttons the way assemble_button_data() does
        $implementedButtons = array();
        foreach ($retval['data'] as $buttonData) {
            if (!$buttonData['hasUnimplementedSkill']) {
                $implementedButtons[]= $buttonData;
            }
        }

        // Now that our indices should match the ones the real
        // randomization code uses, actually look for the buttons we want,
        // producing indices which resolve_random_button_selection() should accept
        $buttonIds = array();
        foreach ($implementedButtons as $buttonIdx => $buttonData) {
            if (in_array($buttonData['buttonName'], $lookForButtons)) {
                $buttonIds[$buttonData['buttonName']] = $buttonIdx;
            }
        }

        // Now make sure we found everything we were looking for
        foreach ($lookForButtons as $lookForButton) {
            $this->assertTrue(array_key_exists($lookForButton, $buttonIds), "Could not find random choice button ID for " . $lookForButton);
        }
        return $buttonIds;
    }


    /**
     * Helper method used by object_structures_match() to provide debugging
     * feedback if the check fails.
     */
    private function output_mismatched_objects($obja, $objb) {
        var_dump('First object: ');
        var_dump($obja);
        var_dump('Second object: ');
        var_dump($objb);
    }

    /**
     * Get a unique faked random value to be used in the verification key
     * of a responderNNN user being created
     */
    protected function get_fake_verification_randval($username) {
        $matches = array();
        preg_match('/responder(\d+)/', $username, $matches);
        return ($matches[1] * 0.001);
    }

    /**
     * Make sure five users, responder001-005, exist, and return
     * fake session data for whichever one was requested.
     */
    protected function mock_test_user_login($username = 'responder003') {
        global $BM_RAND_VALS;

        $responder = new ApiResponder($this->spec, TRUE);

        $args = array('type' => 'createUser', 'password' => 't');

        $userarray = array(
            'responder001',
            'responder002',
            'responder003',
            'responder004',
            'responder005',
            'responder006'
        );

        // Hack: we don't know in advance whether each user creation
        // will succeed or fail.  Therefore, repeat each one so we
        // know it must fail the second time, and parse the user
        // ID from the message that second time.
        foreach ($userarray as $newuser) {
            if (!(array_key_exists($newuser, $this->user_ids))) {
                $args['username'] = $newuser;
                $args['email'] = $newuser . '@example.com';
                $BM_RAND_VALS = array($this->get_fake_verification_randval($newuser));
                $ret1 = $responder->process_request($args);
                if ($ret1['data']) {
                    $ret1 = $responder->process_request($args);
                } else {
                    $BM_RAND_VALS = array();
                }
                $matches = array();
                preg_match('/id=(\d+)/', $ret1['message'], $matches);
                $this->user_ids[$newuser] = (int)$matches[1];
            }
        }

        // now set dummy "logged in" variable and return $_SESSION variable style data for requested user
        global $dummyUserLoggedIn;
        $dummyUserLoggedIn = TRUE;
        return array('user_name' => $username, 'user_id' => $this->user_ids[$username]);
    }

    protected function verify_login_required($type) {
        $args = array('type' => $type);
        $this->verify_api_failure($args, "You need to login before calling API function $type");
    }

    protected function verify_invalid_arg_rejected($type) {
        $args = array('type' => $type, 'foobar' => 'foobar');
        $this->verify_api_failure($args, "Unexpected argument provided to function $type");
    }

    protected function verify_mandatory_args_required($type, $required_args) {
        foreach (array_keys($required_args) as $missing) {
            $args = array('type' => $type);
            foreach ($required_args as $notmissing => $value) {
                if ($missing != $notmissing) {
                    $args[$notmissing] = $value;
                }
            }
            $this->verify_api_failure($args, "Missing mandatory argument $missing for function $type");
        }
    }

    /**
     * verify_api_failure() - helper routine which invokes a live
     * responder to process a given set of arguments, and asserts that the
     * API returns a clean failure.
     */
    protected function verify_api_failure($args, $expMessage) {
        $responder = new ApiResponder($this->spec, True);
        $retval = $responder->process_request($args);
        // unexpected behavior may manifest as API successes which should be failures,
        // so help debugging by printing the full API args and response if it comes to that
        $this->assertEquals('failed', $retval['status'],
            "API call should fail:\nARGS: " . var_export($args, $return=TRUE) . "\nRETURN: " . var_export($retval, $return=TRUE));
        $this->assertEquals(NULL, $retval['data']);
        $this->assertEquals($expMessage, $retval['message']);
        return $retval;
    }

    /**
     * verify_api_success() - helper routine which invokes a live
     * responder to process a given set of arguments, and asserts that the
     * API returns a success, and doesn't leave any random values unused.
     *
     * Unlike in the failure case, here it's the caller's responsibility
     * to test the contents of $retval['data'] and $retval['message']
     */
    protected function verify_api_success($args) {
        global $BM_RAND_VALS;
        $responder = new ApiResponder($this->spec, True);
        $retval = $responder->process_request($args);
        // unexpected regressions may manifest as API failures, so help debugging
        // by printing the full API args and response if it comes to that
        $this->assertEquals('ok', $retval['status'],
            "API call should succeed:\nARGS: " . var_export($args, $return=TRUE) . "\nRETURN: " . var_export($retval, $return=TRUE));
        $this->assertEquals(0, count($BM_RAND_VALS));
        return $retval;
    }

    /**
     * verify_api_createGame() - helper routine which calls the API
     * createGame method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_createGame(
        $bmRandValArray, $player1, $player2, $button1, $button2, $maxWins, $description='', $prevGame=NULL, $returnType='gameId'
    ) {
        global $BM_RAND_VALS, $BM_SKILL_RAND_VALS;
        // Allow the caller to provide either a flat array of just miscellaneous random values,
        // or a dict with both miscellaneous random values and skill selection random values
        if (array_key_exists('bm_rand', $bmRandValArray)) {
            $BM_RAND_VALS = $bmRandValArray['bm_rand'];
            $BM_SKILL_RAND_VALS = $bmRandValArray['bm_skill_rand'];
        } else {
            $BM_RAND_VALS = $bmRandValArray;
            $BM_SKILL_RAND_VALS = array();
        }
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array($player1, $button1), array($player2, $button2)),
            'maxWins' => $maxWins,
            'description' => $description,
        );
        if ($prevGame) {
            $args['previousGameId'] = $prevGame;
        }
        $retval = $this->verify_api_success($args);
        $gameId = $retval['data']['gameId'];
        $this->assertEquals('Game ' . $gameId . ' created successfully.', $retval['message']);
        $this->assertEquals(array('gameId' => $gameId), $retval['data']);
        if ($returnType == 'gameId') {
            return $gameId;
        } else {
            return $retval;
        }
    }

    /**
     * verify_api_joinOpenGame() - helper routine which calls the API
     * joinOpenGame method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_joinOpenGame($postJoinDieRolls, $gameId) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postJoinDieRolls;
        $args = array(
            'type' => 'joinOpenGame',
            'gameId' => $gameId,
        );
        $retval = $this->verify_api_success($args);
        $this->assertEquals('Successfully joined game ' . $gameId, $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        $fakeGameNumber = $this->generate_fake_game_id();

        // Fill in the fake number before caching the output
        $retval['message'] = str_replace($gameId, $fakeGameNumber, $retval['message']);

        $this->cache_json_api_output('joinOpenGame', $fakeGameNumber, $retval);

        return $retval['data'];
    }

    /**
     * verify_api_cancelOpenGame() - helper routine which calls the API
     * cancelOpenGame method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_cancelOpenGame($postCancelDieRolls, $gameId) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postCancelDieRolls;
        $args = array(
            'type' => 'cancelOpenGame',
            'gameId' => $gameId,
        );
        $retval = $this->verify_api_success($args);
        $this->assertEquals('Successfully cancelled game ' . $gameId, $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        $fakeGameNumber = $this->generate_fake_game_id();

        // Fill in the fake number before caching the output
        $retval['message'] = str_replace($gameId, $fakeGameNumber, $retval['message']);

        $this->cache_json_api_output('cancelOpenGame', $fakeGameNumber, $retval);

        return $retval['data'];
    }

    /**
     * verify_api_reactToNewGame() - helper routine which calls the API
     * reactToNewGame method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_reactToNewGame($postJoinDieRolls, $gameId, $action) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postJoinDieRolls;
        $args = array(
            'type' => 'reactToNewGame',
            'gameId' => $gameId,
            'action' => $action,
        );
        $retval = $this->verify_api_success($args);
        if ('accept' == $action) {
            $this->assertEquals('Joined game ' . $gameId, $retval['message']);
        } elseif ('reject' == $action) {
            $this->assertEquals('Rejected game ' . $gameId, $retval['message']);
        }
        $this->assertEquals(TRUE, $retval['data']);

        // For now, just use the action as a key when caching the test API response
        $this->cache_json_api_output('reactToNewGame', $action, $retval);

        return $retval['data'];
    }

    /*
     * verify_api_countPendingGames() - helper routine which calls
     * the API routine countPendingGames and returns the count
     */
    protected function verify_api_countPendingGames() {
        $retval = $this->verify_api_success(array('type' => 'countPendingGames'));
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Pending game count succeeded.');
        return $retval['data']['count'];
    }

    /*
     * By convention, treat the game number plus two-digit move number as a fake game number that the
     * UI tests can reference
     */
    protected function generate_fake_game_id() {
        return sprintf("%d%02d", $this->game_number, $this->move_number);
    }

    /*
     * verify_api_loadGameData() - helper routine which calls the API
     * loadGameData method, makes standard assertions about its
     * return value which shouldn't change, and compares its return
     * value to an expected game state object compiled by the caller.
     */
    protected function verify_api_loadGameData($expData, $gameId, $logEntryLimit, $check=TRUE) {
        $args = array(
            'type' => 'loadGameData',
            'game' => $gameId,
        );
        if ($logEntryLimit) {
            $args['logEntryLimit'] = $logEntryLimit;
        }
        $retval = $this->verify_api_success($args);
        $this->assertEquals('Loaded data for game ' . $gameId . '.', $retval['message']);
        if ($check) {
            $cleanedData = $this->squash_game_data_timestamps($retval['data']);

            $this->assertEquals($expData, $cleanedData);

            // caller must set the game number so we can save data --- it's a bad test otherwise
            assert($this->game_number > 0);
            $this->move_number += 1;
            assert($this->move_number <= 99);

            $fakeGameNumber = $this->generate_fake_game_id();

            // Fill in the fake number before caching the output
            $retval['data']['gameId'] = $fakeGameNumber;
            $retval['message'] = str_replace($gameId, $fakeGameNumber, $retval['message']);

            $this->cache_json_api_output('loadGameData', $fakeGameNumber, $retval);
        }
        return $retval['data'];
    }

    /*
     * verify_api_loadGameData_failure() - helper routine which calls the API
     * loadGameData method and asserts that it fails with the expected message
     */
    protected function verify_api_loadGameData_failure($gameId, $expMessage) {
        $args = array(
            'type' => 'loadGameData',
            'game' => $gameId,
        );
        $retval = $this->verify_api_failure($args, $expMessage);

        $fakeGameNumber = $this->generate_fake_game_id();

        // Fill in the fake number before caching the output
        $retval['message'] = str_replace($gameId, $fakeGameNumber, $retval['message']);

        $this->cache_json_api_output('loadGameData', $fakeGameNumber, $retval);
    }

    /*
     * verify_api_loadGameData_as_nonparticipant()
     * Wrapper for verify_api_loadGameData() which saves and restores
     * things which should be different when a non-participant views a game.
     * Don't actually bother to return the game data, since the
     * test shouldn't need to use it for anything.
     */
    protected function verify_api_loadGameData_as_nonparticipant($expData, $gameId, $logEntryLimit) {
        $oldCurrentPlayerIdx = $expData['currentPlayerIdx'];
        $oldPlayerZeroColor = $expData['playerDataArray'][0]['playerColor'];
        $oldPlayerOneColor = $expData['playerDataArray'][1]['playerColor'];

        $expData['currentPlayerIdx'] = FALSE;
        $expData['playerDataArray'][0]['playerColor'] = '#cccccc';
        $expData['playerDataArray'][1]['playerColor'] = '#dddddd';

        $this->verify_api_loadGameData($expData, $gameId, $logEntryLimit);

        $expData['currentPlayerIdx'] = $oldCurrentPlayerIdx;
        $expData['playerDataArray'][0]['playerColor'] = $oldPlayerZeroColor;
        $expData['playerDataArray'][1]['playerColor'] = $oldPlayerOneColor;
    }

    /**
     * verify_api_reactToAuxiliary() - helper routine which calls the API
     * reactToAuxiliary method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_reactToAuxiliary($postSubmitDieRolls, $expMessage, $gameId, $action, $dieIdx=NULL) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $args = array(
            'type' => 'reactToAuxiliary',
            'game' => $gameId,
            'action' => $action,
        );
        if (!is_null($dieIdx)) {
            $args['dieIdx'] = $dieIdx;
        }
        $retval = $this->verify_api_success($args);
        $this->assertEquals($expMessage, $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        // Construct a fake game ID as we do for loadGameData
        $fakeGameNumber = $this->generate_fake_game_id();
        $this->cache_json_api_output('reactToAuxiliary', $fakeGameNumber, $retval);
    }

    /**
     * verify_api_reactToReserve() - helper routine which calls the API
     * reactToReserve method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_reactToReserve($postSubmitDieRolls, $expMessage, $gameId, $action, $dieIdx=NULL) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $args = array(
            'type' => 'reactToReserve',
            'game' => $gameId,
            'action' => $action,
        );
        if (!is_null($dieIdx)) {
            $args['dieIdx'] = $dieIdx;
        }
        $retval = $this->verify_api_success($args);
        $this->assertEquals($expMessage, $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        // Construct a fake game ID as we do for loadGameData
        $fakeGameNumber = $this->generate_fake_game_id();
        $this->cache_json_api_output('reactToReserve', $fakeGameNumber, $retval);
    }

    /**
     * verify_api_reactToInitiative() - helper routine which calls the API
     * reactToInitiative method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_reactToInitiative(
        $postSubmitDieRolls, $expMessage, $expData, $prevData, $gameId,
        $roundNum, $action, $dieIdxArray=NULL, $dieValueArray=NULL
    ) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $args = array(
            'type' => 'reactToInitiative',
            'game' => $gameId,
            'roundNumber' => $roundNum,
            'timestamp' => $prevData['timestamp'],
            'action' => $action,
        );
        if ($dieIdxArray) {
            $args['dieIdxArray'] = $dieIdxArray;
        }
        if ($dieValueArray) {
            $args['dieValueArray'] = $dieValueArray;
        }
        $retval = $this->verify_api_success($args);
        $this->assertEquals($expMessage, $retval['message']);
        $this->assertEquals($expData, $retval['data']);

        // Construct a fake game ID as we do for loadGameData
        $fakeGameNumber = $this->generate_fake_game_id();
        $this->cache_json_api_output('reactToInitiative', $fakeGameNumber, $retval);
    }

    /**
     * verify_api_adjustFire() - helper routine which calls the API
     * adjustFire method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_adjustFire(
        $postSubmitDieRolls, $expMessage, $prevData, $gameId,
        $roundNum, $action, $dieIdxArray=NULL, $dieValueArray=NULL
    ) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $args = array(
            'type' => 'adjustFire',
            'game' => $gameId,
            'roundNumber' => $roundNum,
            'timestamp' => $prevData['timestamp'],
            'action' => $action,
        );
        if ($dieIdxArray) {
            $args['dieIdxArray'] = $dieIdxArray;
        }
        if ($dieValueArray) {
            $args['dieValueArray'] = $dieValueArray;
        }
        $retval = $this->verify_api_success($args);
        $this->assertEquals($expMessage, $retval['message']);

        // Construct a fake game ID as we do for loadGameData
        $fakeGameNumber = $this->generate_fake_game_id();
        $this->cache_json_api_output('adjustFire', $fakeGameNumber, $retval);
    }

    /**
     * verify_api_submitDieValues() - helper routine which calls the API
     * submitDieValues method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_submitDieValues($postSubmitDieRolls, $gameId, $roundNum, $swingArray=NULL, $optionArray=NULL) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $args = array(
            'type' => 'submitDieValues',
            'game' => $gameId,
            'roundNumber' => $roundNum,
            // BUG: this argument will no longer be needed when #1275 is fixed
            'timestamp' => 1234567890,
        );
        if ($swingArray) {
            $args['swingValueArray'] = $swingArray;
        }
        if ($optionArray) {
            $args['optionValueArray'] = $optionArray;
        }
        $retval = $this->verify_api_success($args);
        $this->assertEquals('Successfully set die sizes', $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        // Construct a fake game ID as we do for loadGameData, but make allowances for
        // callers which don't use a fake game number, and don't cache the output in that case
        if ($this->game_number > 0) {
            $fakeGameNumber = $this->generate_fake_game_id();
            $this->cache_json_api_output('submitDieValues', $fakeGameNumber, $retval);
        }

        return $retval;
    }

    /**
     * verify_api_submitTurn() - helper routine which calls the API
     * submitTurn method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_submitTurn(
        $postSubmitDieRolls, $expMessage, $prevData, $participatingDice,
        $gameId, $roundNum, $attackType, $attackerIdx, $defenderIdx, $chat,
        $turboVals = NULL
    ) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $dieSelects = $this->generate_valid_attack_array($prevData, $participatingDice);
        $args = array(
            'type' => 'submitTurn',
            'game' => $gameId,
            'roundNumber' => $roundNum,
            'timestamp' => $prevData['timestamp'],
            'dieSelectStatus' => $dieSelects,
            'attackType' => $attackType,
            'attackerIdx' => $attackerIdx,
            'defenderIdx' => $defenderIdx,
            'chat' => $chat,
        );
        if ($turboVals != NULL) {
            $args['turboVals'] = $turboVals;
        }

        $retval = $this->verify_api_success($args);
        $this->assertEquals($expMessage, $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        // Construct a fake game ID as we do for loadGameData
        $fakeGameNumber = $this->generate_fake_game_id();
        $this->cache_json_api_output('submitTurn', $fakeGameNumber, $retval);

        return $retval;
    }

    /**
     * verify_api_submitTurn_failure() - helper routine which calls the API
     * submitTurn method with arguments which *should* lead to a
     * failure condition, and verifies that the call fails with the expected parameters
     */
    protected function verify_api_submitTurn_failure(
        $postSubmitDieRolls, $expMessage, $prevData, $participatingDice,
        $gameId, $roundNum, $attackType, $attackerIdx, $defenderIdx, $chat
    ) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $dieSelects = $this->generate_valid_attack_array($prevData, $participatingDice);
        $args = array(
            'type' => 'submitTurn',
            'game' => $gameId,
            'roundNumber' => $roundNum,
            'timestamp' => $prevData['timestamp'],
            'dieSelectStatus' => $dieSelects,
            'attackType' => $attackType,
            'attackerIdx' => $attackerIdx,
            'defenderIdx' => $defenderIdx,
            'chat' => $chat,
        );
        $retval = $this->verify_api_failure($args, $expMessage);
        return $retval;
    }

    /**
     * verify_api_submitChat() - helper routine which calls the API submitChat method
     */
    protected function verify_api_submitChat($gameId, $chat, $expMessage, $edit=NULL) {
        $args = array(
            'type' => 'submitChat',
            'game' => $gameId,
            'chat' => $chat,
        );
        if ($edit) {
            $args['edit'] = $edit;
        }
        $retval = $this->verify_api_success($args);
        $this->assertEquals($expMessage, $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        $fakeGameNumber = $this->generate_fake_game_id();
        $this->cache_json_api_output('submitChat', $fakeGameNumber, $retval);
    }

    /**
     * verify_api_setChatVisibility() - helper routine which calls the API setChatVisibility method
     */
    protected function verify_api_setChatVisibility($expMessage, $gameId, $private) {
        $args = array(
            'type' => 'setChatVisibility',
            'game' => $gameId,
            'private' => $private,
        );
        $retval = $this->verify_api_success($args);
        $this->assertEquals($expMessage, $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        $fakeGameNumber = $this->generate_fake_game_id();
        $this->cache_json_api_output('setChatVisibility', $fakeGameNumber, $retval);
    }
}
