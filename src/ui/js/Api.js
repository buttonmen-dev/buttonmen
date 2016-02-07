// namespace for this "module"
/* exported Api */
var Api = (function () {

  // all public methods and variables should be defined under 'my'
  var my = {};

  // Valid email match
  // Note: this should match the regex in
  //       ApiSpec->verify_argument_of_type_email()
  my.VALID_EMAIL_REGEX = /^[A-Za-z0-9\._+-]+@[A-Za-z0-9\.-]+$/;

  // Array of the names of the months, indexed from 1-12 (plus a bonus Month 0!)
  my.MONTH_NAMES = [
    'Month',
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
  ];

  my.automatedApiCall = false;

  // private methods and variables should be defined separately
  var activity = {};

  ////////////////////////////////////////////////////////////////////////
  // This module should not layout a page or generate any HTML.  It exists
  // only as a collection of routines which load and parse a particular
  // type of data from the server.
  //
  // Each routine should be defined as: Api.getXData(callbackfunc),
  // and should do these things:
  // * call Env.api_location with arguments which load the requested
  //   type of data from the server
  // * call Api.parseXData(rs) to parse the response from the server
  //   and populate Api.x in whatever way is desired
  // * call the requested callback function no matter what happened with
  //   the data load
  //
  // Notes:
  // * these routines may assume that the login header has already been
  //   loaded, and therefore that the contents of Login.logged_in and
  //   Login.player are available
  // * these routines are not appropriate for form submission of actions
  //   that change the server state --- they're only for loading and
  //   parsing server data so page layout modules can use it
  ////////////////////////////////////////////////////////////////////////

  ////////////////////////////////////////////////////////////////////////
  // Generic routine for API POST used to parse data

  my.apiParsePost = function(
    args,
    apikey,
    parser,
    callback,
    failcallback,
    parserargs
  ) {
    if (typeof my[apikey] === 'undefined') {
      my[apikey] = {};
    }
    my[apikey].load_status = 'failed';
    args.automatedApiCall = my.automatedApiCall;
    $.post(
      Env.api_location,
      args,
      function(rs) {
        if (typeof rs === 'string') {
          Env.message = {
            'type': 'error',
            'text':
              'Internal error: got unparseable response from ' + args.type,
          };
          return failcallback();
        } else if (rs.status == 'ok') {
          if (parser(rs.data, apikey, parserargs)) {
            my[apikey].load_status = 'ok';
            return callback();
          } else {
            Env.message = {
              'type': 'error',
              'text':
                'Internal error: Could not parse ' + apikey +
                'data from server',
            };
            return failcallback();
          }
        } else {
          Env.message = {
            'type': 'error',
            'text': 'Error from ' + args.type + ': ' + rs.message,
          };
          return failcallback();
        }
      }
    ).fail(
      function(XMLHttpRequest) {
        // when the client fails to connect to the server at all, then
        // the request is not initialised (readyState = 0) and there is
        // no response
        if ((0 === XMLHttpRequest.status) &&
            (0 === XMLHttpRequest.readyState)) {
          Env.message = {
            'type': 'error',
            'text': 'Could not connect to Button Men server—are you online?'
          };
        } else {
          Env.message = {
            'type': 'error',
            'text': 'Internal error when calling ' + args.type,
          };
        }
        return failcallback();
      }
    );
  };

  my.apiFormPost = function(
      args, messages, submitButton, callback, failcallback) {
    my.disableSubmitButton(submitButton);
    $.post(
      Env.api_location,
      args,
      function(rs) {
        if (typeof rs === 'string') {
          Env.message = {
            'type': 'error',
            'text':
              'Internal error: got unparseable response from ' + args.type,
          };
          return failcallback();
        } else if (rs.status == 'ok') {
          if (messages.ok.type == 'fixed') {
            Env.message = {
              'type': 'success',
              'text': messages.ok.text,
            };
          } else if (messages.ok.type == 'server') {
            Env.message = {
              'type': 'success',
              'text': rs.message,
            };
          } else if (messages.ok.type == 'function') {
            messages.ok.msgfunc(rs.message, rs.data);
          }
          return callback();
        } else {
          if (messages.notok.type == 'fixed') {
            Env.message = {
              'type': 'error',
              'text': messages.notok.text,
            };
          } else if (messages.notok.type == 'server') {
            Env.message = {
              'type': 'error',
              'text': rs.message,
            };
          } else if (messages.notok.type == 'function') {
            messages.notok.msgfunc(rs.message);
          }
          return failcallback();
        }
      }
    ).fail(
      function(XMLHttpRequest) {
        // when the client fails to connect to the server at all, then
        // the request is not initialised (readyState = 0) and there is
        // no response
        if ((0 === XMLHttpRequest.status) &&
            (0 === XMLHttpRequest.readyState)) {
          Env.message = {
            'type': 'error',
            'text': 'Could not connect to Button Men server—are you online?'
          };
        } else {
          Env.message = {
            'type': 'error',
            'text': 'Internal error when calling ' + args.type,
          };
        }
        return failcallback();
      }
    );
  };

  my.parseGenericData = function(data, apiKey) {
    $.each(data, function(key, value) {
      my[apiKey][key] = value;
    });
    return true;
  };

  // Verifies that the API data loaded correctly and displays the page with
  // an error message otherwise.
  my.verifyApiData = function(apiKey, arrangePageCallback) {
    if (Api[apiKey] !== undefined && Api[apiKey].load_status == 'ok') {
      return true;
    }

    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'Internal error: Could not load ' + apiKey +
                'data from server',
      };
    }
    arrangePageCallback();
    return false;
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse a list of buttons
  my.getButtonData = function(buttonName, callbackfunc) {
    my.apiParsePost(
      {
        'type': 'loadButtonData',
        'buttonName': (buttonName ? buttonName : undefined),
      },
      'button',
      my.parseButtonData,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseButtonData = function(data) {
    my.button.list = {};
    if (!$.isArray(data)) {
      return false;
    }
    var i = 0;
    while (i < data.length) {
      my.button.list[data[i].buttonName] = data[i];
      i++;
    }
    return true;
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse a list of button sets

  my.getButtonSetData = function(buttonSet, callbackfunc) {
    my.apiParsePost(
      {
        'type': 'loadButtonSetData',
        'buttonSet': (buttonSet ? buttonSet : undefined),
      },
      'buttonSet',
      my.parseButtonSetData,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseButtonSetData = function(data) {
    my.buttonSet.list = {};
    if (!$.isArray(data)) {
      return false;
    }
    var i = 0;
    while (i < data.length) {
      my.buttonSet.list[data[i].setName] = data[i];
      i++;
    }
    return true;
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse a list of players

  my.getPlayerData = function(callbackfunc) {
    my.apiParsePost(
      {'type': 'loadPlayerNames', },
      'player',
      my.parsePlayerData,
      callbackfunc,
      callbackfunc
    );
  };

  // Make a dict of player names and status values
  my.parsePlayerData = function(data) {
    my.player.list = {};
    if (!($.isArray(data.nameArray))) {
      return false;
    }
    var i = 0;
    while (i < data.nameArray.length) {
      my.player.list[data.nameArray[i]] = {
        'status': data.statusArray[i],
      };
      i++;
    }
    return true;
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse the current player's list of new games

  my.getNewGamesData = function(callbackfunc) {
    my.new_games = {};
    var parserargs = [];
    parserargs.target = my.new_games;
    parserargs.isSplit = true;
    my.apiParsePost(
      {'type': 'loadNewGames', },
      'new_games',
      my.packageGameData,
      callbackfunc,
      callbackfunc,
      parserargs
    );
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse the current player's list of active games

  my.getActiveGamesData = function(callbackfunc) {
    my.active_games = {};
    var parserargs = [];
    parserargs.target = my.active_games;
    parserargs.isSplit = true;
    my.apiParsePost(
      {'type': 'loadActiveGames', },
      'active_games',
      my.packageGameData,
      callbackfunc,
      callbackfunc,
      parserargs
    );
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse the current player's list of completed games

  my.getCompletedGamesData = function(callbackfunc) {
    my.completed_games = {};
    var parserargs = [];
    parserargs.target = my.completed_games;
    parserargs.isSplit = false;
    my.apiParsePost(
      {'type': 'loadCompletedGames', },
      'completed_games',
      my.packageGameData,
      callbackfunc,
      callbackfunc,
      parserargs
    );
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse the current player's list of cancelled games

  my.getCancelledGamesData = function(callbackfunc) {
    my.cancelled_games = {};
    var parserargs = [];
    parserargs.target = my.cancelled_games;
    parserargs.isSplit = false;
    my.apiParsePost(
      {'type': 'loadCancelledGames', },
      'cancelled_games',
      my.packageGameData,
      callbackfunc,
      callbackfunc,
      parserargs
    );
  };

  ////////////////////////////////////////////////////////////////////////
  // Generic parser and repackager of the data returned from the database

  my.packageGameData = function(data, _, parserargs) {
    // the output format is one of the following:
    // - split into games awaiting the player and games awaiting the opponent
    // - made entirely of games not awaiting action
    if (parserargs.isSplit) {
      parserargs.target.games = {
        'awaitingPlayer': [],
        'awaitingOpponent': [],
      };
    } else {
      parserargs.target.games = [];
    }

    parserargs.target.nGames = data.gameIdArray.length;
    parserargs.target.nGamesAwaitingAction = 0;
    for (var i = 0; i < parserargs.target.nGames; i++) {
      var gameInfo = {
        'gameId': data.gameIdArray[i],
        'gameDescription': data.gameDescriptionArray[i],
        'opponentId': data.opponentIdArray[i],
        'opponentName': data.opponentNameArray[i],
        'playerButtonName': data.myButtonNameArray[i],
        'opponentButtonName': data.opponentButtonNameArray[i],
        'gameScoreDict': {
          'W': data.nWinsArray[i],
          'L': data.nLossesArray[i],
          'D': data.nDrawsArray[i],
        },
        'isAwaitingAction': data.isAwaitingActionArray[i],
        'maxWins': data.nTargetWinsArray[i],
        'gameState': data.gameStateArray[i],
        'status': data.statusArray[i],
        'inactivity': data.inactivityArray[i],
        'inactivityRaw': data.inactivityRawArray[i],
        'playerColor': data.playerColorArray[i],
        'opponentColor': data.opponentColorArray[i],
      };
      if (parserargs.isSplit) {
        if (gameInfo.isAwaitingAction == '1') {
          parserargs.target.games.awaitingPlayer.push(gameInfo);
          parserargs.target.nGamesAwaitingAction++;
        } else {
          parserargs.target.games.awaitingOpponent.push(gameInfo);
        }
      } else {
        parserargs.target.games.push(gameInfo);
      }
    }
    return true;
  };

  my.getUserPrefsData = function(callbackfunc) {
    my.apiParsePost(
      {'type': 'loadPlayerInfo', },
      'user_prefs',
      my.parseUserPrefsData,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseUserPrefsData = function(data) {
    $.each(data.user_prefs, function(key, value) {
      my.user_prefs[key] = value;
    });
    return true;
  };

  my.getGameData = function(game, logEntryLimit, callback) {
    activity.gameId = game;
    Api.apiParsePost(
      { type: 'loadGameData', game: game, logEntryLimit: logEntryLimit },
      'game',
      my.parseGameData,
      callback,
      callback
    );
  };

  // Utility routine to parse the game data returned by the server
  // Adds three types of game data:
  //   Api.game.*: metadata about the entire game
  //   Api.game.player: data about the logged in player (or about
  //                    the first player, if the logged in player
  //                    is not in this game)
  //   Api.game.opponent: data about the opposing player
  // The "player" and "opponent" items should be identically formatted
  my.parseGameData = function(data) {

    // Store some initial high-level game elements
    my.game.gameId = data.gameId;
    my.game.gameState = data.gameState;
    my.game.roundNumber = data.roundNumber;
    my.game.maxWins = data.maxWins;
    my.game.description = data.description;
    my.game.previousGameId = data.previousGameId;
    my.game.validAttackTypeArray = data.validAttackTypeArray;
    my.game.gameSkillsInfo = data.gameSkillsInfo;

    my.game.timestamp = data.timestamp;
    my.game.actionLog = data.gameActionLog;
    my.game.actionLogCount = data.gameActionLogCount;
    my.game.chatLog = data.gameChatLog;
    my.game.chatLogCount = data.gameChatLogCount;
    my.game.chatEditable = data.gameChatEditable;

    // Do some sanity-checking of the data we have

    if (activity.gameId != my.game.gameId) {
      return false;
    }

    if ($.isNumeric(data.currentPlayerIdx)) {
      my.game.isParticipant = true;
    } else {
      my.game.isParticipant = false;
    }

    if (my.game.isParticipant) {
      my.game.playerIdx = data.currentPlayerIdx;
      my.game.opponentIdx = 1 - data.currentPlayerIdx;
    } else {
      my.game.playerIdx = 0;
      my.game.opponentIdx = 1;
    }

    my.game.player = my.parseGamePlayerData(
                       data.playerDataArray[my.game.playerIdx],
                       my.game.playerIdx);
    my.game.opponent = my.parseGamePlayerData(
                         data.playerDataArray[my.game.opponentIdx],
                         my.game.opponentIdx);

    my.game.pendingGameCount = data.pendingGameCount;

    return true;
  };

  // Given a player index, parse all data out of the appropriate arrays,
  // and return it.  This function can be used for either the logged-in
  // player or the opponent.
  my.parseGamePlayerData = function(playerData, playerIdx) {
    var data = playerData;

    // modify the API-provided swing request array to ensure that
    // it is always a dict, and to tag the range values as "min"/"max"
    var modSwingRequestArray = {};
    $.each(playerData.swingRequestArray, function(letter, range) {
      modSwingRequestArray[letter] = {
        'min': parseInt(range[0], 10),
        'max': parseInt(range[1], 10)
      };
    });
    data.swingRequestArray = modSwingRequestArray;

    // store buttonName as a top-level variable for convenience
    // in assembling game tables
    data.buttonName = playerData.button.name;

    // activePlayerIdx may be either player or may be null
    if (my.game.activePlayerIdx == playerIdx) {
      data.isActive = true;
    } else {
      data.isActive = false;
    }

    // playerWithInitiativeIdx may be either player or may be null
    if (my.game.playerWithInitiativeIdx == playerIdx) {
      data.hasInitiative = true;
    } else {
      data.hasInitiative = false;
    }

    return data;
  };

  my.disableSubmitButton = function(button) {
    if (button) {
      $(button).attr('disabled', 'disabled');
    }
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse the ID of the player's next pending game

  my.getNextGameId = function(callbackfunc) {
    var currentGameId;
    if (Api.game !== undefined &&
        Api.game.isParticipant && Api.game.player.waitingOnAction) {
      // If you're viewing a game where it's your turn, pass the ID along as
      // being skipped
      currentGameId = Api.game.gameId;
    }

    my.apiParsePost(
      {
        'type': 'loadNextPendingGame',
        'currentGameId': currentGameId,
      },
      'gameNavigation',
      my.parseNextGameId,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseNextGameId = function(data) {
    if (data.gameId !== null && !$.isNumeric(data.gameId)) {
      return false;
    }
    my.gameNavigation.nextGameId = data.gameId;
    return true;
  };

  ////////////////////////////////////////////////////////////////////////
  // Load the ID's of the next new post and its thread

  my.getNextNewPostId = function(callbackfunc) {
    my.apiParsePost(
      { 'type': 'loadNextNewPost', },
      'forumNavigation',
      my.parseGenericData,
      callbackfunc,
      callbackfunc
    );
  };

  my.getOpenGamesData = function(callbackfunc) {
    my.apiParsePost( { 'type': 'loadOpenGames', },
      'open_games',
      my.parseOpenGames,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseOpenGames = function(data) {
    my.open_games.games = data.games;
    return true;
  };

  my.joinOpenGame = function(gameId, buttonName, callback, failCallback) {
    var parameters = {
      'type': 'joinOpenGame',
      'gameId': gameId,
    };
    if (buttonName !== undefined && buttonName !== null) {
      parameters.buttonName = buttonName;
    }

    my.apiParsePost(
      parameters,
      'join_game_result',
      my.parseJoinGameResult,
      callback,
      failCallback
    );
  };

  my.parseJoinGameResult = function(data) {
    my.join_game_result.success = data;
    return true;
  };

  ////////////////////////////////////////////////////////////
  // Forum-related methods

  my.loadForumOverview = function(callbackfunc) {
    my.apiParsePost(
      { 'type': 'loadForumOverview', },
      'forum_overview',
      my.parseGenericData,
      callbackfunc,
      callbackfunc
    );
  };

  my.loadForumBoard = function(boardId, callbackfunc) {
    my.apiParsePost(
      {
        'type': 'loadForumBoard',
        'boardId': boardId,
      },
      'forum_board',
      my.parseGenericData,
      callbackfunc,
      callbackfunc
    );
  };

  my.loadForumThread = function(threadId, currentPostId, callbackfunc) {
    if (!currentPostId) {
      currentPostId = undefined;
    }
    my.apiParsePost(
      {
        'type': 'loadForumThread',
        'threadId': threadId,
        'currentPostId': currentPostId,
      },
      'forum_thread',
      my.parseGenericData,
      callbackfunc,
      callbackfunc
    );
  };

  // End of Forum-related methods
  ////////////////////////////////////////////////////////////

  ////////////////////////////////////////////////////////////////////////
  // Load and parse the list of recently-active players

  my.getActivePlayers = function(numberOfPlayers, callbackfunc) {
    my.apiParsePost(
      {
        'type': 'loadActivePlayers',
        'numberOfPlayers': numberOfPlayers,
      },
      'active_players',
      my.parseActivePlayers,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseActivePlayers = function(data) {
    my.active_players.players = data.players;
    return true;
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse the profile info for the specified player

  my.loadProfileInfo = function(playerName, callbackfunc) {
    my.apiParsePost(
      {
        'type': 'loadProfileInfo',
        'playerName': playerName,
      },
      'profile_info',
      my.parseProfileInfo,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseProfileInfo = function(data) {
    $.each(data.profile_info, function(key, value) {
      my.profile_info[key] = value;
    });
    return true;
  };

  ////////////////////////////////////////////////////////////////////////
  // Search historic games and parse the result

  my.searchGameHistory = function(searchParameters, callbackfunc) {
    searchParameters.type = 'searchGameHistory';

    my.apiParsePost(
      searchParameters,
      'game_history',
      my.parseSearchResults,

      callbackfunc,
      callbackfunc
    );
  };

  my.parseSearchResults = function(data) {
    my.game_history.games = data.games;
    my.game_history.summary = data.summary;

    return true;
  };

  my.getPendingGameCount = function(callbackfunc) {
    my.apiParsePost(
      { 'type': 'countPendingGames', },
      'pending_games',
      my.parseGenericData,
      callbackfunc,
      callbackfunc
    );
  };

  return my;
}());
