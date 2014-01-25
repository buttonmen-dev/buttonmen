// namespace for this "module"
/* exported Api */
var Api = (function () {

  // all public methods and variables should be defined under 'my'
  var my = {};

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

  my.apiFormPost = function(args, messages, callback, failcallback) {
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

  // Right now, we only get a list of names, but make a dict in case
  // there's more data available later
  my.parsePlayerData = function(data) {
    my.player.list = {};
    if (!($.isArray(data.nameArray))) {
      return false;
    }
    var i = 0;
    while (i < data.nameArray.length) {
      my.player.list[data.nameArray[i]] = {
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

  return my;
}());
