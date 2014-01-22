// namespace for this "module"
/* exported Api */
var Api = (function () {

  // all public methods and variables should be defined under 'my'
  var my = {};

  // Duplicate the game settings we need from Game.js for now
  var GAME_STATE_END_GAME = 60;

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
  // Load and parse a list of buttons

  my.getButtonData = function(callbackfunc) {
    my.button = {
      'load_status': 'failed',
    };
    $.post(
      Env.api_location,
      { type: 'loadButtonNames', },
      function(rs) {
        if (rs.status == 'ok') {
          if (my.parseButtonData(rs.data)) {
            my.button.load_status = 'ok';
          } else {
            Env.message = {
              'type': 'error',
              'text': 'Could not parse button list from server',
            };
          }
        } else {
          Env.message = {
            'type': 'error',
            'text': rs.message,
          };
        }
        return callbackfunc();
      }
    ).fail(
      function() {
        Env.message = {
          'type': 'error',
          'text': 'Internal error when calling loadButtonNames',
        };
        return callbackfunc();
      }
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
    my.player = {
      'load_status': 'failed',
    };
    $.post(
      Env.api_location,
      { type: 'loadPlayerNames', },
      function(rs) {
        if (rs.status == 'ok') {
          if (my.parsePlayerData(rs.data)) {
            my.player.load_status = 'ok';
          } else {
            Env.message = {
              'type': 'error',
              'text': 'Could not parse player list from server',
            };
          }
        } else {
          Env.message = {
            'type': 'error',
            'text': rs.message,
          };
        }
        return callbackfunc();
      }
    ).fail(
      function() {
        Env.message = {
          'type': 'error',
          'text': 'Internal error when calling loadPlayerNames',
        };
        return callbackfunc();
      }
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
    my.active_games = {
      'load_status': 'failed',
    };

    $.post(
      Env.api_location,
      { type: 'loadActiveGames', },
      function(rs) {
        if (rs.status == 'ok') {
          if (my.parseActiveGamesData(rs.data)) {
            my.active_games.load_status = 'ok';
          } else {
            Env.message = {
              'type': 'error',
              'text':
                'Active game list received from server could not be parsed!',
            };
          }
        } else {
          Env.message = {
            'type': 'error',
            'text': rs.message,
          };
        }
        return callbackfunc();
      }
    ).fail(
      function() {
        Env.message = {
          'type': 'error',
          'text': 'Internal error when calling loadActiveGames',
        };
        return callbackfunc();
      }
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
    my.completed_games = {
      'load_status': 'failed',
    };

    $.post(
      Env.api_location,
      { type: 'loadCompletedGames', },
      function(rs) {
        if (rs.status == 'ok') {
          if (my.parseCompletedGamesData(rs.data)) {
            my.completed_games.load_status = 'ok';
          } else if (my.completed_games.load_status != 'nogames') {
            Env.message = {
              'type': 'error',
              'text':
                'Completed game list received from server could not be parsed!',
            };
          }
        } else {
          Env.message = {
            'type': 'error',
            'text': rs.message,
          };
        }
        return callbackfunc();
      }
    ).fail(
      function() {
        Env.message = {
          'type': 'error',
          'text': 'Internal error when calling loadCompletedGames',
        };
        return callbackfunc();
      }
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

  return my;
}());
