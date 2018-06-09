<?php

/**
 * BMInterfaceNewuser: new user interface between GUI and BMGame
 *
 * @author chaos
 */

/**
 * This class should contain all interface functions which may be
 * accessed by an unauthenticated user.  Its response format and
 * database usage should mirror that of BMInterface.
 *
 * @property-read string $message                Message intended for GUI
 * @property-read DateTime $timestamp            Timestamp of last game action
 *
 *
 */
class BMInterfaceNewuser {
    // constants
    const USERNAME_MAX_LENGTH = 25;
    const EMAIL_MAX_LENGTH = 254;

    // properties

    /**
     * Message intended for GUI
     *
     * @var string
     */
    private $message;

    /**
     * Connection to database
     *
     * @var PDO
     */
    private static $conn = NULL;

    /**
     * Indicates if the interface is for testing
     *
     * @var bool
     */
    private $isTest;

    /**
     * Constructor
     *
     * @param bool $isTest
     */
    public function __construct($isTest = FALSE) {
        if (!is_bool($isTest)) {
            throw new InvalidArgumentException('isTest must be boolean.');
        }

        $this->isTest = $isTest;

        if ($isTest) {
            if (file_exists('../test/src/database/mysql.test.inc.php')) {
                require_once '../test/src/database/mysql.test.inc.php';
            } else {
                require_once 'test/src/database/mysql.test.inc.php';
            }
        } else {
            require_once '../database/mysql.inc.php';
        }
        self::$conn = conn();
    }

    // methods

    /**
     * Create a new user
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return NULL|array
     */
    public function create_user($username, $password, $email) {
        try {
            if (strlen($username) > BMInterfaceNewuser::USERNAME_MAX_LENGTH) {
                $this->message = 'Usernames cannot be longer than 25 characters';
                return NULL;
            }
            if (strlen($email) > BMInterfaceNewuser::EMAIL_MAX_LENGTH) {
                $this->message = 'Email addresses cannot be longer than 254 characters';
                return NULL;
            }

            // if this is a remote connection, check whether this
            // IP already has numerous player_verification entries
            if (isset($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER)) {
                $query = 'SELECT player_id FROM player_verification WHERE ipaddr = :ipaddr';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':ipaddr' => $_SERVER['REMOTE_ADDR']));
                $fetchResult = $statement->fetchAll();
                if (count($fetchResult) >= 5) {
                    $this->message = 'Too many new user requests from IP ' . $_SERVER['REMOTE_ADDR'];
                    return NULL;
                }
            }

            // check to see whether this username already exists
            $query = 'SELECT id FROM player WHERE name_ingame = :username';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':username' => $username));
            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) > 0) {
                $user_id = $fetchResult[0]['id'];
                $this->message = $username . ' already exists (id=' . $user_id . ')';
                return NULL;
            }

            // check to see whether this email address already exists
            $query = 'SELECT id FROM player WHERE email = :email';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':email' => $email));
            $fetchResult = $statement->fetchAll();

            if (count($fetchResult) > 0) {
                $user_id = $fetchResult[0]['id'];
                $this->message = 'Email address ' . $email . ' already exists (id=' .  $user_id . ')';
                return NULL;
            }

            // create user
            $query =
                'INSERT INTO player (name_ingame, password_hashed, email, status_id) ' .
                'VALUES (' .
                    ':username, ' .
                    ':password, ' .
                    ':email, ' .
                    '(SELECT ps.id FROM player_status ps WHERE ps.name = :status)' .
                ');';
            $statement = self::$conn->prepare($query);

            // support versions of PHP older than 5.5.0
            if (version_compare(phpversion(), "5.5.0", "<")) {
                $passwordHash = crypt($password);
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            }

            $statement->execute(array(':username' => $username,
                                      ':password' => $passwordHash,
                                      ':email' => $email,
                                      ':status' => 'UNVERIFIED'));

            // select the player ID to make sure insert succeeded
            $query = 'SELECT id,email FROM player WHERE name_ingame = :name';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':name' => $username));
            $fetchResult = $statement->fetchAll();

            if (count($fetchResult) != 1) {
                $this->message = 'User creation failed';
                return NULL;
            }
            $playerId = $fetchResult[0]['id'];
            $playerEmail = $fetchResult[0]['email'];
            $result = array('userName' => $username, 'playerId' => $playerId);

            // now generate a verification code and e-mail it to the user
            $this->send_email_verification($playerId, $username, $playerEmail);
            $this->message = 'User ' . $username . ' created successfully.  ' .
                             'A verification code has been e-mailed to ' . $playerEmail . '.  ' .
                             'Follow the link in that message to start beating people up! ' .
                             '(Note: If you don\'t see the email shortly, be sure to check ' .
                             'your spam folder.)';

            return $result;
        } catch (Exception $e) {
            $errorData = $statement->errorInfo();
            $this->message = 'User create failed: ' . $errorData[2];
            return NULL;
        }
    }

    /**
     * Verify a user on first login using the verification key sent by email
     *
     * @param int $playerId
     * @param string $playerKey
     * @return bool
     */
    public function verify_user($playerId, $playerKey) {
        try {
            // Check for a user with this id
            $query = 'SELECT name_ingame, status FROM player_view WHERE id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));
            $fetchResult = $statement->fetchAll();

            // Make sure that user ID exists and is waiting to be verified
            if (count($fetchResult) != 1) {
                $this->message = 'Could not lookup user ID ' . $playerId;
                return NULL;
            }
            $username = $fetchResult[0]['name_ingame'];
            $status = $fetchResult[0]['status'];
            if ($status != 'UNVERIFIED') {
                $this->message = 'User with ID ' . $playerId . ' is not waiting to be verified';
                return NULL;
            }

            // Find the verification key for the specified user ID
            $query = 'SELECT verification_key FROM player_verification WHERE player_id = :player_id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':player_id' => $playerId));
            $fetchResult = $statement->fetchAll();

            if (count($fetchResult) != 1) {
                $this->message = 'Could not find verification key for user ID ' . $playerId;
                return NULL;
            }
            $databaseKey = $fetchResult[0]['verification_key'];

            // Now check that the provided key matches the one in the database
            if ($playerKey != $databaseKey) {
                $this->message = 'Wrong verification key!  Make sure you pasted the URL from the e-mail exactly.';
                return NULL;
            }

            // Everything checked out okay.  Activate the account
            $query = 'UPDATE player ' .
                     'SET status_id = (SELECT ps.id FROM player_status ps WHERE ps.name = "ACTIVE") ' .
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));
            $this->message = 'Account activated for player ' . $username . '!';
            $result = TRUE;
            return $result;
        } catch (Exception $e) {
            $errorData = $statement->errorInfo();
            $this->message = 'User create failed: ' . $errorData[2];
            return NULL;
        }
    }

    /**
     * Create and send email allowing new users to login for the first time
     *
     * @param int $playerId
     * @param string $username
     * @param string $playerEmail
     */
    public function send_email_verification($playerId, $username, $playerEmail) {

        // a given player should only have one verification code at a time, so delete any old ones
        $query = 'DELETE FROM player_verification WHERE player_id = :playerId';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':playerId' => $playerId));

        // generate a new verification code and insert it into the table
        $playerKey = md5(bm_rand());
        if (isset($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $ipaddr = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddr = NULL;
        }
        $query = 'INSERT INTO player_verification (player_id, verification_key, ipaddr)
                  VALUES (:player_id, :player_key, :ipaddr)';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':player_id' => $playerId,
                                  ':player_key' => $playerKey,
                                  ':ipaddr' => $ipaddr));

        // send the e-mail message
        $email = new BMEmail($playerEmail, $this->isTest);
        $email->send_verification_link($playerId, $username, $playerKey);
    }

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                default:
                    return $this->$property;
            }
        }
    }

    /**
     * Setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        switch ($property) {
            case 'message':
                throw new LogicException(
                    'message can only be read, not written.'
                );
            default:
                $this->$property = $value;
        }
    }
}
