// namespace for this "module"
var Api = {
  'data': {},
};

// Duplicate the game settings we need from Game.js for now
Api.GAME_STATE_END_GAME = 60;


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

Api.getButtonData = function(callbackfunc) {
  Api.button = {
    'load_status': 'failed',
  };
  $.post(
    Env.api_location,
    { type: 'loadButtonNames', },
    function(rs) {
      if (rs.status == 'ok') {
        if (Api.parseButtonData(rs.data)) {
          Api.button.load_status = 'ok';
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

Api.parseButtonData = function(data) {
  Api.button.list = {};
  if ((data.buttonNameArray == null) ||
      (data.recipeArray == null) ||
      (data.hasUnimplementedSkillArray == null)) {
    return false;
  }
  var i = 0;
  while (i < data.buttonNameArray.length) {
    Api.button.list[data.buttonNameArray[i]] = {
      'recipe': data.recipeArray[i],
      'hasUnimplementedSkill': data.hasUnimplementedSkillArray[i],
    };
    i++;
  }
  return true;
};

////////////////////////////////////////////////////////////////////////
// Load and parse a list of players

Api.getPlayerData = function(callbackfunc) {
  Api.player = {
    'load_status': 'failed',
  };
  $.post(
    Env.api_location,
    { type: 'loadPlayerNames', },
    function(rs) {
      if (rs.status == 'ok') {
        if (Api.parsePlayerData(rs.data)) {
          Api.player.load_status = 'ok';
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
Api.parsePlayerData = function(data) {
  Api.player.list = {};
  if (data.nameArray == null) {
    return false;
  }
  var i = 0;
  while (i < data.nameArray.length) {
    Api.player.list[data.nameArray[i]] = {
    };
    i++;
  }
  return true;
};

////////////////////////////////////////////////////////////////////////
// Load and parse the current player's list of active games

Api.getActiveGamesData = function(callbackfunc) {
  Api.active_games = {
    'load_status': 'failed',
  };

  $.post(
    Env.api_location,
    { type: 'loadActiveGames', },
    function(rs) {
      if (rs.status == 'ok') {
        if (Api.parseActiveGamesData(rs.data)) {
          Api.active_games.load_status = 'ok';
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

Api.parseActiveGamesData = function(data) {
  Api.active_games.games = {
    'awaitingPlayer': [],
    'awaitingOpponent': [],
  };
  Api.active_games.nGames = data.gameIdArray.length;
  i = 0;
  while (i < Api.active_games.nGames) {
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
      Api.active_games.games.awaitingPlayer.push(gameInfo);
    } else {
      if (gameInfo.gameState == Api.GAME_STATE_END_GAME) {
        Api.active_games.games.finished.push(gameInfo);
      } else {
        Api.active_games.games.awaitingOpponent.push(gameInfo);
      }
    }
    i += 1;
  }
  return true;
};

////////////////////////////////////////////////////////////////////////
// Load and parse the current player's list of active games

Api.getCompletedGamesData = function(callbackfunc) {
  Api.completed_games = {
    'load_status': 'failed',
  };

  $.post(
    Env.api_location,
    { type: 'loadCompletedGames', },
    function(rs) {
      if (rs.status == 'ok') {
        if (Api.parseCompletedGamesData(rs.data)) {
          Api.completed_games.load_status = 'ok';
        } else if (!(Api.completed_games.load_status == 'nogames')) {
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

Api.parseCompletedGamesData = function(data) {
  Api.completed_games.games = [];
  Api.completed_games.nGames = data.gameIdArray.length;
  i = 0;
  while (i < Api.completed_games.nGames) {
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
    Api.completed_games.games.push(gameInfo);
    i += 1;
  }
  return true;
};
