<?php
/**
 * ApiSpec: specification of public API functions and args
 *
 * @author chaos
 *
 */

/**
 * This class specifies the public API functions and what sort
 * of arguments are mandatory and optional
 */
class ApiSpec {
    // constants
    const GAME_CHAT_MAX_LENGTH = 2000;
    const FORUM_BODY_MAX_LENGTH = 16000;
    const FORUM_TITLE_MAX_LENGTH = 100;
    const GENDER_MAX_LENGTH = 100;
    const DIE_BACKGROUND_MAX_LENGTH = 10;
    const VACATION_MESSAGE_MAX_LENGTH = 255;
    const GAME_DESCRIPTION_MAX_LENGTH = 255;

    /**
     * These are API methods that might get called automatically, e.g. via the monitor
     *
     * @var array
     */
    private $automatableApiCalls = array(
        'loadNextPendingGame',
        'loadNextNewPost',
        'loadNewGames',
        'loadActiveGames',
        'loadCompletedGames',
        'loadCancelledGames',
        'loadPlayerInfo',
        'loadForumThread',
        'countPendingGames',
        'loadGameData',
        'loadPlayerName',
    );

    /**
     * expected arguments for every API function:
     * - mandatory: argument which must be present
     * - permitted: additional argument which may be present
     *
     * @var array
     */
    private $functionArgs = array(
        'adjustFire' => array(
            'mandatory' => array(
                'game' => 'number',
                'roundNumber' => 'number',
                'timestamp' => 'number',
                'action' => 'alnum',
            ),
            'permitted' => array(
                'dieIdxArray' => array(
                    'arg_type' => 'array',
                    'has_keys' => FALSE,
                    'elem_type' => 'number',
                ),
                'dieValueArray' => array(
                    'arg_type' => 'array',
                    'has_keys' => FALSE,
                    'elem_type' => 'number',
                ),
            ),
        ),
        // countPendingGames returns:
        //   count: int,
        'countPendingGames' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        // createForumPost returns (from loadForumThread):
        //   threadId: int,
        //   threadTitle: string,
        //   boardId: int,
        //   boardName: string,
        //   boardColor: string,
        //   boardThreadColor: string,
        //   currentPostId: int (nullable),
        //   posts[]: {
        //     postId: int,
        //     posterName: string,
        //     posterColor: string,
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
                'body' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::FORUM_BODY_MAX_LENGTH,
                ),
            ),
            'permitted' => array(),
        ),
        // createForumThread returns (from loadForumThread):
        //   threadId: int,
        //   threadTitle: string,
        //   boardId: int,
        //   boardName: string,
        //   boardColor: string,
        //   boardThreadColor: string,
        //   currentPostId: int (nullable),
        //   posts[]: {
        //     postId: int,
        //     posterName: string,
        //     posterColor: string,
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
                'title' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::FORUM_TITLE_MAX_LENGTH,
                ),
                'body' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::FORUM_BODY_MAX_LENGTH,
                ),
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
                'playerInfoArray' => array(
                    'arg_type' => 'array',
                    'has_keys' => TRUE,
                    'minlength' => 2,
                    'maxlength' => 2,
                    'key_type' => 'number',
                    'elem_type' => array('arg_type' => 'array',
                                         'has_keys' => TRUE,
                                         'minlength' => 0,
                                         'maxlength' => 2,
                                         'key_type' => 'number',
                                         'elem_type' => 'string'),
                ),
                'maxWins' => 'number',
            ),
            'permitted' => array(
                'description' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::GAME_DESCRIPTION_MAX_LENGTH,
                ),
                'previousGameId' => 'number',
            ),
        ),
        'reactToNewGame' => array(
            'mandatory' => array(
                'gameId' => 'number',
                'action' => 'string',
            ),
            'permitted' => array(),
        ),
        'dismissGame' => array(
            'mandatory' => array(
                'gameId' => 'number',
            ),
            'permitted' => array(),
        ),
        // editForumPost returns (from loadForumThread):
        //   threadId: int,
        //   threadTitle: string,
        //   boardId: int,
        //   boardName: string,
        //   boardColor: string,
        //   boardThreadColor: string,
        //   currentPostId: int (nullable),
        //   posts[]: {
        //     postId: int,
        //     posterName: string,
        //     posterColor: string,
        //     creationTime: int,
        //     lastUpdateTime: int,
        //     isNew: bool,
        //     body: string,
        //     deleted: bool,
        //   },
        //   timestamp: int,
        'editForumPost' => array(
            'mandatory' => array(
                'postId' => 'number',
                'body' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::FORUM_BODY_MAX_LENGTH,
                ),
            ),
            'permitted' => array(),
        ),
        'forgotPassword' => array(
            'mandatory' => array(
                'username' => 'alnum',
            ),
            'permitted' => array(),
        ),
        'joinOpenGame' => array(
            'mandatory' => array(
                'gameId' => 'number',
            ),
            'permitted' => array(
                'buttonName' => 'button',
            ),
        ),
        'cancelOpenGame' => array(
            'mandatory' => array(
                'gameId' => 'number',
            ),
            'permitted' => array(),
        ),
        'loadActiveGames' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadActivePlayers' => array(
            'mandatory' => array(
                'numberOfPlayers' => 'number',
            ),
            'permitted' => array(),
        ),
        'searchGameHistory' => array(
            'mandatory' => array(
                'sortColumn' => array(
                    'arg_type' => 'exactString',
                    'values' => array(
                        'gameId',
                        'playerNameA',
                        'buttonNameA',
                        'playerNameB',
                        'buttonNameB',
                        'gameStart',
                        'lastMove',
                        'winningPlayer',
                        'status',
                    ),
                ),
                'sortDirection' => array(
                    'arg_type' => 'exactString',
                    'values' => array('ASC', 'DESC'),
                ),
                'numberOfResults' => 'number',
                'page' => 'number'
            ),
            'permitted' => array(
                'gameId' => 'number',
                'playerNameA' => 'alnum',
                'buttonNameA' => 'button',
                'playerNameB' => 'alnum',
                'buttonNameB' => 'button',
                'gameStartMin' => 'number',
                'gameStartMax' => 'number',
                'lastMoveMin' => 'number',
                'lastMoveMax' => 'number',
                'winningPlayer' => array(
                    'arg_type' => 'exactString',
                    'values' => array('A', 'B', 'Tie'),
                ),
                'status' => array(
                    'arg_type' => 'exactString',
                    'values' => array('ACTIVE', 'UNSTARTED', 'COMPLETE', 'CANCELLED'),
                ),
            ),
        ),
        'loadButtonData' => array(
            'mandatory' => array(),
            'permitted' => array(
                'buttonName' => 'button',
                'buttonSet' => 'string',
                'tagArray' => array(
                    'arg_type' => 'array',
                    'minlength' => 1,
                    'has_keys' => TRUE,
                    'key_type' => 'alnum',
                    'elem_type' => 'boolean',
                ),
            ),
        ),
        'loadButtonSetData' => array(
            'mandatory' => array(),
            'permitted' => array(
                'buttonSet' => 'string',
            ),
        ),
        'loadCompletedGames' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadCancelledGames' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadDieSkillsData' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadDieTypesData' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        // loadForumBoard returns:
        //   boardId: int,
        //   boardName: string,
        //   boardColor: string,
        //   threadColor: string,
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
        //     boardColor: string,
        //     threadColor: string,
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
        //   boardColor: string,
        //   boardThreadColor: string,
        //   currentPostId: int (nullable),
        //   posts[]: {
        //     postId: int,
        //     posterName: string,
        //     posterColor: string,
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
        'loadNewGames' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadNextPendingGame' => array(
            'mandatory' => array(),
            'permitted' => array(
              'currentGameId' => 'number',
            ),
        ),
        // loadNextNewPost returns:
        //   nextNewPostId (nullable),
        //   nextNewPostThreadId (nullable),
        'loadNextNewPost' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        'loadOpenGames' => array(
            'mandatory' => array(),
            'permitted' => array(),
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
        'loadProfileInfo' => array(
            'mandatory' => array(
                'playerName' => 'alnum',
            ),
            'permitted' => array(),
        ),
        'login' => array(
            'mandatory' => array(
                'username' => 'alnum',
                'password' => 'string',
            ),
            'permitted' => array(
                'doStayLoggedIn' => 'boolean',
            ),
        ),
        'logout' => array(
            'mandatory' => array(),
            'permitted' => array(),
        ),
        // markForumBoardRead returns (from loadForumOverview):
        //   boards[]: {
        //     boardId: int,
        //     boardName: string,
        //     boardColor: string,
        //     threadColor: string,
        //     description: string,
        //     numberOfThreads: int,
        //     firstNewPostId: int,
        //     firstNewPostThreadId: int,
        //   },
        //   timestamp: int,
        'markForumRead' => array(
            'mandatory' => array(
                'timestamp' => 'number',
            ),
            'permitted' => array(),
        ),
        // markForumBoardRead returns (from loadForumOverview):
        //   boards[]: {
        //     boardId: int,
        //     boardName: string,
        //     boardColor: string,
        //     threadColor: string,
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
        //   boardColor: string,
        //   threadColor: string,
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
                    'arg_type' => 'array',
                    'has_keys' => FALSE,
                    'elem_type' => 'number',
                ),
                'dieValueArray' => array(
                    'arg_type' => 'array',
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
        'resetPassword' => array(
            'mandatory' => array(
                'playerId' => 'number',
                'playerKey' => 'alnum',
                'password' => 'string',
            ),
            'permitted' => array(),
        ),
        'savePlayerInfo' => array(
            'mandatory' => array(
                'name_irl' => 'string',
                'is_email_public' => 'boolean',
                'dob_month' => 'number',
                'dob_day' => 'number',
                'gender' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::GENDER_MAX_LENGTH,
                ),
                'comment' => 'string',
                'homepage' => array(
                    'arg_type' => 'string',
                    'maxlength' => 100,
                ),
                'autoaccept' => 'boolean',
                'autopass' => 'boolean',
                'fire_overshooting' => 'boolean',
                'monitor_redirects_to_game' => 'boolean',
                'monitor_redirects_to_forum' => 'boolean',
                'automatically_monitor' => 'boolean',
                'die_background' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::DIE_BACKGROUND_MAX_LENGTH,
                ),
                'player_color' => 'color',
                'opponent_color' => 'color',
                'neutral_color_a' => 'color',
                'neutral_color_b' => 'color',
            ),
            'permitted' => array(
                'favorite_button' => 'button',
                'favorite_buttonset' => 'string',
                'image_size' => array(
                    'arg_type' => 'number',
                    'maxvalue' => 200,
                    'minvalue' => 80,
                ),
                'uses_gravatar' => 'boolean',
                'vacation_message' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::VACATION_MESSAGE_MAX_LENGTH,
                ),
                'current_password' => 'string',
                'new_password' => 'string',
                'new_email' => 'email',
            ),
        ),
        'setChatVisibility' => array(
            'mandatory' => array(
                'game' => 'number',
                'private' => 'boolean',
            ),
        ),
        'submitDieValues' => array(
            'mandatory' => array(
                'game' => 'number',
                'roundNumber' => 'number',
                'timestamp' => 'number',
            ),
            'permitted' => array(
                'optionValueArray' => array(
                    'arg_type' => 'array',
                    'has_keys' => TRUE,
                    'minlength' => 0,
                    'key_type' => 'number',
                    'elem_type' => 'number',
                ),
                'swingValueArray' => array(
                    'arg_type' => 'array',
                    'has_keys' => TRUE,
                    'minlength' => 0,
                    'key_type' => 'alnum',
                    'elem_type' => 'number',
                ),
            ),
        ),
        'submitChat' => array(
            'mandatory' => array(
                'game' => 'number',
                'chat' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::GAME_CHAT_MAX_LENGTH,
                ),
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
                    'arg_type' => 'array',
                    'has_keys' => TRUE,
                    'key_type' => 'alnum',
                    'elem_type' => 'boolean',
                ),
                'attackType' => 'alnum',
                'attackerIdx' => 'number',
                'defenderIdx' => 'number',
            ),
            'permitted' => array(
                'turboVals' => array(
                    'arg_type' => 'array',
                    'has_keys' => TRUE,
                    'key_type' => 'number',
                    'elem_type' => 'number',
                ),
                'chat' => array(
                    'arg_type' => 'string',
                    'maxlength' => self::GAME_CHAT_MAX_LENGTH,
                ),
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

    /**
     * This function looks at the provided arguments other than
     * type, and verifies that they are syntactically correct for
     * what the specified type expects.
     *
     * @param array $args
     * @return array
     */
    public function verify_function_args($args) {
        if (array_key_exists('type', $args) &&
            array_key_exists($args['type'], $this->functionArgs)) {
            $argsExpected = $this->functionArgs[$args['type']];
            foreach ($args as $argname => $argvalue) {
                if ($argname == 'type') {
                    continue;
                }
                if ($argname == 'automatedApiCall') {
                    if ($args[$argname] == 'true' && !in_array($args['type'], $this->automatableApiCalls)) {
                        return array(
                            'ok' => FALSE,
                            'message' => $args['type'] . ' can\'t be treated as an automated API call',
                        );
                    }
                    continue;
                }
                $expectedType = $this->determineExpectedType($argname, $argsExpected);
                if (!$expectedType) {
                    return array(
                        'ok' => FALSE,
                        'message' => 'Unexpected argument provided to function ' . $args['type'],
                    );
                }
                if (!($this->verify_argument_type($argvalue, $expectedType))) {
                    return array(
                        'ok' => FALSE,
                        'message' => 'Argument (' . $argname . ') to function ' .
                                     $args['type'] . ' is invalid',
                    );
                }
            }

            $missingArg = $this->find_missing_mandatory_arguments($argsExpected, $args);
            if ($missingArg) {
                return array(
                    'ok' => FALSE,
                    'message' => "Missing mandatory argument $missingArg for function " . $args['type'],
                );
            }
            return array('ok' => TRUE);
        } else {
            return array(
                'ok' => FALSE,
                'message' => 'Specified API function does not exist',
            );
        }
    }

    /**
     * This function sanitizes the provided arguments into a
     * standard format for internal use.  It is run after
     * verify_function_args() has already passed on these arguments,
     * so it assumes the args are sane.
     *
     * @param array $args
     * @return array
     */
    public function sanitize_function_args($args) {
        $sanitizedArgs = array();
        $argsExpected = $this->functionArgs[$args['type']];
        foreach ($args as $argname => $argvalue) {
            if (($argname == 'type') || ($argname == 'automatedApiCall')) {
                $sanitizedArgs[$argname] = $argvalue;
                continue;
            }

            $expectedType = $this->determineExpectedType($argname, $argsExpected);
            if (is_array($expectedType)) {
                $realArgtype = $expectedType['arg_type'];
            } else {
                $realArgtype = $expectedType;
            }

            // Use a sanitization function if one exists, or just return the initial arg
            $sanitizeFunc = 'sanitize_argument_of_type_' . $realArgtype;
            if (method_exists($this, $sanitizeFunc)) {
                $sanitizedArgs[$argname] = $this->$sanitizeFunc($argvalue);
            } else {
                $sanitizedArgs[$argname] = $argvalue;
            }
        }
        return $sanitizedArgs;
    }

    /**
     * Determine expected type of an argument
     *
     * @param string $argName
     * @param array $argsExpected
     * @return string
     */
    private function determineExpectedType($argName, $argsExpected) {
        if (array_key_exists($argName, $argsExpected['mandatory'])) {
            return $argsExpected['mandatory'][$argName];
        }
        if (array_key_exists($argName, $argsExpected['permitted'])) {
            return $argsExpected['permitted'][$argName];
        }
        return NULL;
    }

    /**
     * Returns the missing argument name if one is missing or NULL if all are present
     *
     * @param array $argsExpected
     * @param array $args
     * @return string
     */
    private function find_missing_mandatory_arguments($argsExpected, $args) {
        foreach (array_keys($argsExpected['mandatory']) as $argRequired) {
            if (!(array_key_exists($argRequired, $args))) {
                return $argRequired;
            }
        }
    }

    /**
     * landing function for verifying that an argument is of the correct type
     *
     * @param mixed $arg
     * @param array|string $argtype
     * @return bool
     */
    protected function verify_argument_type($arg, $argtype) {
        if (is_array($argtype)) {
            switch ($argtype['arg_type']) {
                case 'exactString':
                    return $this->verify_argument_exact_string_type($arg, $argtype['values']);
                case 'array':
                    return $this->verify_argument_array_type($arg, $argtype);
                default:
                    $checkfunc = 'verify_argument_of_type_' . $argtype['arg_type'];

                    if (method_exists($this, $checkfunc)) {
                        return $this->$checkfunc($arg, $argtype);
                    }
                    return FALSE;
            }
        } else {
            $checkfunc = 'verify_argument_of_type_' . $argtype;

            if (method_exists($this, $checkfunc)) {
                return $this->$checkfunc($arg);
            }
            return FALSE;
        }
    }

    /**
     * sanitize an array argument by sorting it by key
     *
     * @param array $arg
     * @return array
     */
    protected function sanitize_argument_of_type_array($arg) {
        ksort($arg);
        return $arg;
    }

    /**
     * verify that the argument is an array, and verify the types
     * of each of its elements
     *
     * @param array $arg
     * @param array $argtype
     * @return bool
     */
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

    /**
     * verify that the argument is one of the exact strings permitted
     *
     * @param string $arg
     * @param array $values
     * @return bool
     */
    protected function verify_argument_exact_string_type($arg, $values) {
        return (is_string($arg) && in_array($arg, $values));
    }

    /**
     * verify that the argument is an alphanumeric string (allow underscores)
     *
     * @param string $arg
     * @return boolean
     */
    protected function verify_argument_of_type_alnum($arg) {
        if (is_string($arg) &&
            preg_match('/^[a-zA-Z0-9_]+$/', $arg)) {
            return TRUE;
        }
        if (is_int($arg) && $arg >= 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * verify that the argument is a string containing a boolean
     *
     * @param string $arg
     * @return boolean
     */
    protected function verify_argument_of_type_boolean($arg) {
        if (is_bool($arg)) {
            return TRUE;
        }
        if (is_string($arg) &&
            in_array(strtolower($arg), array("true", "false"))) {
            return TRUE;
        }
        return FALSE;
    }


    /**
     * verify that the argument is a string containing a valid button name
     * In general, it's probably fine for button names to contain
     * most ASCII characters --- it'd be nice to avoid backtick and semicolon.
     * This regexp should be kept in sync with data.button.sql.
     * Currently, it is intended to match:
     * - alphanumeric characters
     * - space
     * - these special characters: . ' ( ) ! & + _ -
     *
     * @param string $arg
     * @return bool
     */
    protected function verify_argument_of_type_button($arg) {
        if (is_string($arg) &&
            preg_match('/^[ a-zA-Z0-9\.\'()!&+_-]+$/', $arg)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * verify that the argument is a string containing a valid e-mail address
     *
     * @param string $arg
     * @return bool
     */
    protected function verify_argument_of_type_email($arg) {
        if (is_string($arg) &&
            preg_match('/^[A-Za-z0-9\._+-]+@[A-Za-z0-9\.-]+$/', $arg)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * verify that the argument is a nonnegative integer
     *
     * @param int|string $arg
     * @param array $argtype
     * @return boolean
     */
    protected function verify_argument_of_type_number($arg, $argtype = array()) {
        if ((is_int($arg) && $arg >= 0) ||
            (is_string($arg) && ctype_digit($arg))) {
            $arg = (int)$arg;
            if (isset($argtype['maxvalue']) && $arg > $argtype['maxvalue']) {
                return FALSE;
            }
            if (isset($argtype['minvalue']) && $arg < $argtype['minvalue']) {
                return FALSE;
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * verify that the argument is a string
     *
     * @param string $arg
     * @param array $argtype
     * @return bool
     */
    protected function verify_argument_of_type_string($arg, $argtype = array()) {
        if (is_string($arg)) {
            $length = mb_strlen($arg, mb_detect_encoding($arg));
            if (isset($argtype['maxlength']) && $length > $argtype['maxlength']) {
                return FALSE;
            }
            if (isset($argtype['minlength']) && $length < $argtype['minlength']) {
                return FALSE;
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * verify that the argument is a string
     *
     * @param string $arg
     * @return bool
     */
    protected function verify_argument_of_type_color($arg) {
        if (is_string($arg) &&
            preg_match('/^#[0-9a-f]{6}$/i', $arg)) {
            return TRUE;
        }
        return FALSE;
    }
}
