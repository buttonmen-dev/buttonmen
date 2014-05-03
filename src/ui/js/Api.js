// namespace for this "module"
/* exported Api */
var Api = (function () {

  // all public methods and variables should be defined under 'my'
  var my = {};

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

  my.apiParsePost = function(args, apikey, parser, callback, failcallback) {
    my[apikey] = {
      'load_status': 'failed',
    };
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
          if (parser(rs.data)) {
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
      function() {
        Env.message = {
          'type': 'error',
          'text': 'Internal error when calling ' + args.type,
        };
        return failcallback();
      }
    );
  };

  my.apiFormPost = function(args, messages, submitid, callback, failcallback) {
    my.disableSubmitButton(submitid);
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
      function() {
        Env.message = {
          'type': 'error',
          'text': 'Internal error when calling ' + args.type,
        };
        return failcallback();
      }
    );
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse a list of buttons

  my.getButtonData = function(callbackfunc) {
    my.apiParsePost(
      {'type': 'loadButtonNames', },
      'button',
      my.parseButtonData,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseButtonData = function(data) {
    my.button.list = {};
    if ((!($.isArray(data.buttonNameArray))) ||
        (!($.isArray(data.recipeArray))) ||
        (!($.isArray(data.hasUnimplementedSkillArray)))) {
      return false;
    }
    var i = 0;
    while (i < data.buttonNameArray.length) {
      my.button.list[data.buttonNameArray[i]] = {
        'recipe': data.recipeArray[i],
        'hasUnimplementedSkill': data.hasUnimplementedSkillArray[i],
      };
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
  // Load and parse the current player's list of active games

  my.getActiveGamesData = function(callbackfunc) {
    my.apiParsePost(
      {'type': 'loadActiveGames', },
      'active_games',
      my.parseActiveGamesData,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseActiveGamesData = function(data) {
    my.active_games.games = {
      'awaitingPlayer': [],
      'awaitingOpponent': [],
    };
    my.active_games.nGames = data.gameIdArray.length;
    var i = 0;
    while (i < my.active_games.nGames) {
      var gameInfo = {
        'gameId': data.gameIdArray[i],
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
      };
      if (gameInfo.isAwaitingAction == '1') {
        my.active_games.games.awaitingPlayer.push(gameInfo);
      } else {
        my.active_games.games.awaitingOpponent.push(gameInfo);
      }
      i += 1;
    }
    return true;
  };

  ////////////////////////////////////////////////////////////////////////
  // Load and parse the current player's list of active games

  my.getCompletedGamesData = function(callbackfunc) {
    my.apiParsePost(
      {'type': 'loadCompletedGames', },
      'completed_games',
      my.parseCompletedGamesData,
      callbackfunc,
      callbackfunc
    );
  };

  my.parseCompletedGamesData = function(data) {
    my.completed_games.games = [];
    my.completed_games.nGames = data.gameIdArray.length;
    var i = 0;
    while (i < my.completed_games.nGames) {
      var gameInfo = {
        'gameId': data.gameIdArray[i],
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
      };
      my.completed_games.games.push(gameInfo);
      i += 1;
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
    my.user_prefs.autopass = data.autopass;
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
    my.game.gameData = data.gameData;
    my.game.timestamp = data.timestamp;
    my.game.actionLog = data.gameActionLog;
    my.game.chatLog = data.gameChatLog;
    my.game.chatEditable = data.gameChatEditable;

    // Do some sanity-checking of the gameData object we have

    // This is not the same as rs.status --- it's a second status
    // value within the gameData object
    if (my.game.gameData.status != 'ok') {
      return false;
    }
    if (activity.gameId != my.game.gameData.data.gameId) {
      return false;
    }

    if ($.isNumeric(data.currentPlayerIdx)) {
      my.game.isParticipant = true;
    } else {
      my.game.isParticipant = false;
    }

    // Parse some top-level items from gameData
    my.game.gameId =  my.game.gameData.data.gameId;
    my.game.roundNumber = my.game.gameData.data.roundNumber;
    my.game.maxWins = my.game.gameData.data.maxWins;
    my.game.gameState = my.game.gameData.data.gameState;
    my.game.validAttackTypeArray = my.game.gameData.data.validAttackTypeArray;

    if (my.game.isParticipant) {
      my.game.playerIdx = data.currentPlayerIdx;
      my.game.opponentIdx = 1 - data.currentPlayerIdx;
    } else {
      my.game.playerIdx = 0;
      my.game.opponentIdx = 1;
    }

    my.game.player = my.parseGamePlayerData(
                       my.game.playerIdx, data.playerNameArray);
    my.game.opponent = my.parseGamePlayerData(
                         my.game.opponentIdx, data.playerNameArray);

    // Parse game WLT text into a string for convenience
    my.game.player.gameScoreStr = my.playerWLTText('player');
    my.game.opponent.gameScoreStr = my.playerWLTText('opponent');

    return true;
  };

  // Given a player index, parse all data out of the appropriate arrays,
  // and return it.  This function can be used for either the logged-in
  // player or the opponent.
  my.parseGamePlayerData = function(playerIdx, playerNameArray) {
    var data = {
      'playerId': my.game.gameData.data.playerIdArray[playerIdx],
      'playerName': playerNameArray[playerIdx],
      'buttonName': my.game.gameData.data.buttonNameArray[playerIdx],
      'buttonRecipe': my.game.gameData.data.buttonRecipeArray[playerIdx],
      'waitingOnAction':
        my.game.gameData.data.waitingOnActionArray[playerIdx],
      'roundScore': my.game.gameData.data.roundScoreArray[playerIdx],
      'sideScore': my.game.gameData.data.sideScoreArray[playerIdx],
      'gameScoreDict':
        my.game.gameData.data.gameScoreArrayArray[playerIdx],
      'lastActionTime':
        my.game.gameData.data.lastActionTimeArray[playerIdx],
      'nDie': my.game.gameData.data.nDieArray[playerIdx],
      'valueArray': my.game.gameData.data.valueArrayArray[playerIdx],
      'sidesArray': my.game.gameData.data.sidesArrayArray[playerIdx],
      'dieRecipeArray':
        my.game.gameData.data.dieRecipeArrayArray[playerIdx],
      'dieSkillsArray':
        my.game.gameData.data.dieSkillsArrayArray[playerIdx],
      'diePropertiesArray':
        my.game.gameData.data.diePropertiesArrayArray[playerIdx],
      'dieDescriptionArray':
        my.game.gameData.data.dieDescriptionArrayArray[playerIdx],

       // N.B. These arrays describe the other player's dice which this
       // player has captured
      'nCapturedDie': my.game.gameData.data.nCapturedDieArray[playerIdx],
      'capturedValueArray':
        my.game.gameData.data.capturedValueArrayArray[playerIdx],
      'capturedSidesArray':
        my.game.gameData.data.capturedSidesArrayArray[playerIdx],
      'capturedRecipeArray':
        my.game.gameData.data.capturedRecipeArrayArray[playerIdx],

      'swingRequestArray': {},
      'optRequestArray':
        my.game.gameData.data.optRequestArrayArray[playerIdx],
    };

    $.each(
      my.game.gameData.data.swingRequestArrayArray[playerIdx],
      function(letter, range) {
        data.swingRequestArray[letter] = {
          'min': parseInt(range[0], 10),
          'max': parseInt(range[1], 10)
        };
      }
    );

    // activePlayerIdx may be either player or may be null
    if (my.game.gameData.data.activePlayerIdx == playerIdx) {
      data.isActive = true;
    } else {
      data.isActive = false;
    }

    // playerWithInitiativeIdx may be either player or may be null
    if (my.game.gameData.data.playerWithInitiativeIdx == playerIdx) {
      data.hasInitiative = true;
    } else {
      data.hasInitiative = false;
    }

    return data;
  };

  my.playerWLTText = function(player) {
    var text = 'W/L/T: ' + Api.game[player].gameScoreDict.W +
               '/' + Api.game[player].gameScoreDict.L +
               '/' + Api.game[player].gameScoreDict.D +
               ' (' + Api.game.maxWins + ')';
    return text;
  };

  my.disableSubmitButton = function(button_id) {
    if (button_id) {
      $('#' + button_id).attr('disabled', 'disabled');
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

  return my;
}());
