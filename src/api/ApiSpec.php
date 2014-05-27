<?php

/**
 * ApiSpec: specification of public API functions and args
 *
 * @author chaos
 *
 */
class ApiSpec {

    // expected arguments for every API function:
    // * mandatory: argument which must be present
    // * permitted: additional argument which may be present
    private $functionArgs = array(
        // createForumPost returns (from loadForumThread):
        //   threadId: int,
        //   threadTitle: string,
        //   boardId: int,
        //   boardName: string,
        //   currentPostId: int (nullable),
        //   posts[]: {
        //     postId: int,
        //     posterName: string,
        //     creationTime: int,
        //     lastUpdateTime: int,
        //     isNew: bool,
        //     body: string,
        //     deleted: bool,
        //   },
        //   timestamp: int,
        'createForumPost' => array(
            'mandatory' => array(
                'threadId' => 'number',
                'body' => 'string',
            ),
            'permitted' => array(),
        ),
        // createForumThread returns (from loadForumThread):
        //   threadId: int,
        //   threadTitle: string,
        //   boardId: int,
        //   boardName: string,
        //   currentPostId: int (nullable),
        //   posts[]: {
        //     postId: int,
        //     posterName: string,
        //     creationTime: int,
        //     lastUpdateTime: int,
        //     isNew: bool,
        //     body: string,
        //     deleted: bool,
        //   },
        //   timestamp: int,
        'createForumThread' => array(
            'mandatory' => array(
                'boardId' => 'number',
                'title' => 'string',
                'body' => 'string',
            ),
            'permitted' => array(),
        ),
        'createUser' => array(
            'mandatory' => array(
                'username' => 'alnum',
                'password' => 'string',
                'email'    => 'email',
            ),
            'permitted' => array(),
        ),
        'createGame' => array(
            'mandatory' => array(
                'playerNameArray' => array(
                    'has_keys' => FALSE,
                    'minlength' => 2,
                    'maxlength' => 2,
                    'elem_type' => 'alnum',
                ),
                'buttonNameArray' => array(
                    'has_keys' => FALSE,
                    'minlength' => 2,
                    'maxlength' => 2,
                    'elem_type' => 'button',
                ),
                'maxWins' => 'number',
            ),
            'permitted' => array(),
        ),
        'loadActiveGames' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadButtonNames' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadCompletedGames' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        // loadForumBoard returns:
        //   boardId: int,
        //   boardName: string,
        //   description: string,
        //   threads[]: {
        //     threadId: int,
        //     threadTitle: string,
        //     numberOfPosts: int,
        //     originalPosterName: string,
        //     originalCreationTime: int,
        //     latestPosterName: string,
        //     latestLastUpdateTime: int,
        //     firstNewPostId: int,
        //   },
        //   timestamp: int,
        'loadForumBoard' => array(
            'mandatory' => array(
                'boardId' => 'number',
            ),
            'permitted' => array(),
        ),
        // loadForumOverview returns:
        //   boards[]: {
        //     boardId: int,
        //     boardName: string,
        //     description: string,
        //     numberOfThreads: int,
        //     firstNewPostId: int,
        //     firstNewPostThreadId: int,
        //   },
        //   timestamp: int,
        'loadForumOverview' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        // loadForumThread returns:
        //   threadId: int,
        //   threadTitle: string,
        //   boardId: int,
        //   boardName: string,
        //   currentPostId: int (nullable),
        //   posts[]: {
        //     postId: int,
        //     posterName: string,
        //     creationTime: int,
        //     lastUpdateTime: int,
        //     isNew: bool,
        //     body: string,
        //     deleted: bool,
        //   },
        //   timestamp: int,
        'loadForumThread' => array(
            'mandatory' => array(
                'threadId' => 'number',
            ),
            'permitted' => array(
                'currentPostId' => 'number',
            ),
        ),
        'loadGameData' => array(
            'mandatory' => array(
                'game' => 'number',
            ),
            'permitted' => array(
                'logEntryLimit' => 'number',
            ),
        ),
        'loadNextPendingGame' => array(
            'mandatory' => array(),
            'permitted' => array(
              'currentGameId' => 'number',
            ),
        ),
        'loadPlayerInfo' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadPlayerName' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadPlayerNames' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'login' => array(
            'mandatory' => array(
                'username' => 'alnum',
                'password' => 'string',
            ),
            'permitted' => array(),
        ),
        'logout' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        // markForumBoardRead returns (from loadForumOverview):
        //   boards[]: {
        //     boardId: int,
        //     boardName: string,
        //     description: string,
        //     numberOfThreads: int,
        //     firstNewPostId: int,
        //     firstNewPostThreadId: int,
        //   },
        //   timestamp: int,
        'markForumBoardRead' => array(
            'mandatory' => array(
                'boardId' => 'number',
                'timestamp' => 'number',
            ),
            'permitted' => array(),
        ),
        // markForumThreadRead returns (from loadForumBoard):
        //   boardId: int,
        //   boardName: string,
        //   description: string,
        //   threads[]: {
        //     threadId: int,
        //     threadTitle: string,
        //     numberOfPosts: int,
        //     originalPosterName: string,
        //     originalCreationTime: int,
        //     latestPosterName: string,
        //     latestLastUpdateTime: int,
        //     firstNewPostId: int,
        //   },
        //   timestamp: int,
        'markForumThreadRead' => array(
            'mandatory' => array(
                'threadId' => 'number',
                'boardId' => 'number',
                'timestamp' => 'number',
            ),
            'permitted' => array(),
        ),
        'reactToAuxiliary' => array(
            'mandatory' => array(
                'game' => 'number',
                'action' => 'alnum',
            ),
            'permitted' => array(
                'dieIdx' => 'number',
            ),
        ),
        'reactToInitiative' => array(
            'mandatory' => array(
                'game' => 'number',
                'roundNumber' => 'number',
                'timestamp' => 'number',
                'action' => 'alnum',
            ),
            'permitted' => array(
                'dieIdxArray' => array(
                    'has_keys' => FALSE,
                    'elem_type' => 'number',
                ),
                'dieValueArray' => array(
                    'has_keys' => FALSE,
                    'elem_type' => 'alnum',
                ),
            ),
        ),
        'reactToReserve' => array(
            'mandatory' => array(
                'game' => 'number',
                'action' => 'alnum',
            ),
            'permitted' => array(
                'dieIdx' => 'number',
            ),
        ),
        'savePlayerInfo' => array(
            'mandatory' => array(
                'autopass' => 'boolean',
            ),
            'permitted' => array(),
        ),
        'submitDieValues' => array(
            'mandatory' => array(
                'game' => 'number',
                'roundNumber' => 'number',
                'timestamp' => 'number',
            ),
            'permitted' => array(
                'optionValueArray' => array(
                    'has_keys' => TRUE,
                    'minlength' => 1,
                    'key_type' => 'number',
                    'elem_type' => 'number',
                ),
                'swingValueArray' => array(
                    'has_keys' => TRUE,
                    'minlength' => 1,
                    'key_type' => 'alnum',
                    'elem_type' => 'number',
                ),
            ),
        ),
        'submitChat' => array(
            'mandatory' => array(
                'game' => 'number',
                'chat' => 'string',
            ),
            'permitted' => array(
                'edit' => 'number',
            ),
        ),
        'submitTurn' => array(
            'mandatory' => array(
                'game' => 'number',
                'roundNumber' => 'number',
                'timestamp' => 'number',
                'dieSelectStatus' => array(
                    'has_keys' => TRUE,
                    'key_type' => 'alnum',
                    'elem_type' => 'boolean',
                ),
                'attackType' => 'alnum',
                'attackerIdx' => 'number',
                'defenderIdx' => 'number',
            ),
            'permitted' => array(
                'chat' => 'string',
            ),
        ),
        'verifyUser' => array(
            'mandatory' => array(
                'playerId' => 'number',
                'playerKey' => 'alnum',
            ),
            'permitted' => array(),
        ),
    );

    // This function looks at the provided arguments other than
    // type, and verifies that they are syntactically correct for
    // what the specified type expects.
    public function verify_function_args($args) {
        if (array_key_exists('type', $args) &&
            array_key_exists($args['type'], $this->functionArgs)) {

            $argsExpected = $this->functionArgs[$args['type']];
            foreach ($args as $argname => $argvalue) {
                if ($argname == 'type') {
                    continue;
                }
                if (array_key_exists($argname, $argsExpected['mandatory'])) {
                    $expectedType = $argsExpected['mandatory'][$argname];
                } elseif (array_key_exists($argname, $argsExpected['permitted'])) {
                    $expectedType = $argsExpected['permitted'][$argname];
                } else {
                    return array(
                        'ok' => FALSE,
                        'message' => 'Unexpected argument provided to function ' . $args['type'],
                    );
                }
                if (!($this->verify_argument_type($argvalue, $expectedType))) {
                    return array(
                        'ok' => FALSE,
                        'message' => 'Argument (' . $argname . ') to function ' .
                                     $args['type'] . ' is invalid - ',
                    );
                }
            }

            foreach (array_keys($argsExpected['mandatory']) as $argrequired) {
                if (!(array_key_exists($argrequired, $args))) {
                    return array(
                        'ok' => FALSE,
                        'message' => "Missing mandatory argument $argrequired for function " . $args['type'],
                    );
                }
            }
            return array('ok' => TRUE);
        } else {
            return array(
                'ok' => FALSE,
                'message' => 'Specified API function does not exist',
            );
        }
    }

    // landing function for verifying that an argument is of the correct type
    protected function verify_argument_type($arg, $argtype) {
        if (is_array($argtype)) {
            return $this->verify_argument_array_type($arg, $argtype);
        } else {
            $checkfunc = 'verify_argument_of_type_' . $argtype;

            if (method_exists($this, $checkfunc)) {
                return $this->$checkfunc($arg);
            }
            return FALSE;
        }
    }

    // verify that the argument is an array, and verify the types
    // of each of its elements
    protected function verify_argument_array_type($arg, $argtype) {
        if (is_array($arg)) {
            if (array_key_exists('minlength', $argtype)) {
                if (count($arg) < $argtype['minlength']) {
                    return FALSE;
                }
            }
            if (array_key_exists('maxlength', $argtype)) {
                if (count($arg) > $argtype['maxlength']) {
                    return FALSE;
                }
            }
            if ($argtype['has_keys']) {
                foreach ($arg as $eltkey => $eltvalue) {
                    if (!($this->verify_argument_type($eltkey, $argtype['key_type']))) {
                        return FALSE;
                    }
                    if (!($this->verify_argument_type($eltvalue, $argtype['elem_type']))) {
                        return FALSE;
                    }
                }
            } else {
                foreach ($arg as $arrayelt) {
                    if (!($this->verify_argument_type($arrayelt, $argtype['elem_type']))) {
                        return FALSE;
                    }
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    // verify that the argument is an alphanumeric string (allow underscores)
    protected function verify_argument_of_type_alnum($arg) {
        if (is_string($arg) &&
            preg_match('/^[a-zA-Z0-9_]+$/', $arg)) {
            return TRUE;
        }
        return FALSE;
    }

    // verify that the argument is a string containing a boolean
    protected function verify_argument_of_type_boolean($arg) {
        if (is_string($arg) &&
            in_array(strtolower($arg), array("true", "false"))) {
            return TRUE;
        }
        return FALSE;
    }

    // verify that the argument is a string containing a valid button name
    // In general, it's probably fine for button names to contain
    // most ASCII characters --- it'd be nice to avoid backtick and semicolon.
    // This regexp should be kept in sync with data.button.sql.
    // Currently, it is intended to match:
    // * alphanumeric characters
    // * space
    // * these special characters: . ' ( ) ! & + _ -
    protected function verify_argument_of_type_button($arg) {
        if (is_string($arg) &&
            preg_match('/^[ a-zA-Z0-9\.\'()!&+_-]+$/', $arg)) {
            return TRUE;
        }
        return FALSE;
    }

    // verify that the argument is a string containing a valid e-mail address
    protected function verify_argument_of_type_email($arg) {
        if (is_string($arg) &&
            preg_match('/^[A-Za-z0-9\._+-]+@[A-Za-z0-9\.-]+$/', $arg)) {
            return TRUE;
        }
        return FALSE;
    }

    // verify that the argument is a nonnegative integer
    protected function verify_argument_of_type_number($arg) {
        if ((is_int($arg) && $arg >= 0) ||
            (is_string($arg) && ctype_digit($arg))) {
            return TRUE;
        }
        return FALSE;
    }

    // verify that the argument is a string
    protected function verify_argument_of_type_string($arg) {
        if (is_string($arg)) {
            return TRUE;
        }
        return FALSE;
    }
}
