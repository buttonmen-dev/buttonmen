<?php

class BMInterfaceTestAbstract extends PHPUnit_Framework_TestCase {

    /**
     * @var BMInterface
     */
    protected $object;
    protected static $userId1WithoutAutopass;
    protected static $userId2WithoutAutopass;
    protected static $userId3WithAutopass;
    protected static $userId4WithAutopass;
    protected static $userId5WithoutAutoaccept;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        if (file_exists('../test/src/database/mysql.test.inc.php')) {
            require_once '../test/src/database/mysql.test.inc.php';
        } else {
            require_once 'test/src/database/mysql.test.inc.php';
        }
        $this->init();
        $this->newuserObject = new BMInterfaceNewuser(TRUE);
    }

    protected function createObject() {
        $this->object = new BMInterface(TRUE);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    protected static function getMethod($name) {
        $class = new ReflectionClass('BMInterface');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function load_game($gameId) {
        $load_game = self::getMethod('load_game');
        return $load_game->invokeArgs($this->object, array($gameId));
    }

    protected function save_game($game) {
        $save_game = self::getMethod('save_game');
        return $save_game->invokeArgs($this->object, array($game));
    }

    protected function create_game_self_first(
        array $playerIdArray,
        array $buttonNameArray,
        $maxWins = 3,
        $description = '',
        $previousGameId = NULL,
        $autoAccept = TRUE
    ) {
        if (0 == count($playerIdArray)) {
            throw new LogicException('The player ID array cannot be empty');
        }

        return $this->object->game()->create_game(
            $playerIdArray,
            $buttonNameArray,
            $maxWins,
            $description,
            $previousGameId,
            $playerIdArray[0],
            $autoAccept
        );
    }
}
