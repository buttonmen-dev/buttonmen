// namespace for this "module"
var Game = {};

// Game states must match those reported by the API
Game.GAME_STATE_START_GAME = 10;
Game.GAME_STATE_APPLY_HANDICAPS = 13;
Game.GAME_STATE_CHOOSE_AUXILIARY_DICE = 16;
Game.GAME_STATE_LOAD_DICE_INTO_BUTTONS = 20;
Game.GAME_STATE_ADD_AVAILABLE_DICE_TO_GAME = 22;
Game.GAME_STATE_SPECIFY_DICE = 24;
Game.GAME_STATE_DETERMINE_INITIATIVE = 26;
Game.GAME_STATE_REACT_TO_INITIATIVE = 27;
Game.GAME_STATE_START_ROUND = 30;
Game.GAME_STATE_START_TURN = 40;
Game.GAME_STATE_END_TURN = 48;
Game.GAME_STATE_END_ROUND = 50;
Game.GAME_STATE_END_GAME = 60;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Game.showGamePage() is the landing function.  Always call this first
// * Game.getCurrentGame() asks the API for information about the
//   requested game.  It clobbers Game.api.  If successful, it calls
// * Game.showStatePage() determines what action to take next based on
//   the received data from getCurrentGame().  It calls one of several
//   functions, Game.action<SomeAction>()
// * each Game.action<SomeAction>() function must set Game.page and
//   Game.form, then call Game.layoutPage()
// * Game.layoutPage() sets the contents of <div id="game_page"> on the
//   live page
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Game.showGamePage = function() {

  // Setup necessary elements for displaying status messages
  $.getScript('js/Env.js');
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#game_page').length === 0) {
    $('body').append($('<div>', {'id': 'game_page', }));
  }

  // Find the current game, and invoke that with the "parse game state"
  // callback
  Game.getCurrentGame(Game.showStatePage);
};

// the current game should be provided as a GET parameter to the page
Game.getCurrentGame = function(callbackfunc) {
  Game.api = {
    'load_status': 'failed',
  };
  Game.game = Env.getParameterByName('game');
  if (Game.game === null) {
    Env.message = {
      'type': 'error',
      'text': 'No game specified.  Nothing to do.'
    };
    return callbackfunc();
  }
  if ($.isNumeric(Game.game) === false) {
    Env.message = {
      'type': 'error',
      'text': 'Specified game is not a valid number.  Nothing to do.'
    };
    return callbackfunc();
  }
  if (Login.logged_in === false) {
    Env.message = {
      'type': 'error',
      'text': 'Not logged in.  Nothing to do.'
    };
    return callbackfunc();
  }

  $.post(
    Env.api_location,
    { type: 'loadGameData', game: Game.game, },
    function(rs) {
      if (rs.status == 'ok') {
        Game.api.gameData = rs.data.gameData;
        Game.api.timestamp = rs.data.timestamp;
        Game.api.actionLog = rs.data.gameActionLog;
        Game.api.chatLog = rs.data.gameChatLog;
        if (Game.parseGameData(rs.data.currentPlayerIdx,
                               rs.data.playerNameArray)) {
          Game.api.load_status = 'ok';
        } else {
          Env.message = {
            'type': 'error',
            'text': 'Game data received from server could not be parsed!',
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
  ).fail(function() {
    Env.message = {
      'type': 'error',
      'text': 'Internal error when calling loadGameData',
    };
    return callbackfunc();
  });
};

// Assemble and display the game portion of the page
Game.showStatePage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  // Figure out what to do next based on the game state
  if (Game.api.load_status == 'ok') {
    if (Game.api.gameState == Game.GAME_STATE_SPECIFY_DICE) {
      if (Game.api.player.waitingOnAction) {
        Game.actionChooseSwingActive();
      } else {
        Game.actionChooseSwingInactive();
      }
    } else if (Game.api.gameState == Game.GAME_STATE_REACT_TO_INITIATIVE) {
      if (Game.api.player.waitingOnAction) {
        Game.actionReactToInitiativeActive();
      } else {
        Game.actionReactToInitiativeInactive();
      }
    } else if (Game.api.gameState == Game.GAME_STATE_START_TURN) {
      if (Game.api.player.waitingOnAction) {
        Game.actionPlayTurnActive();
      } else {
        Game.actionPlayTurnInactive();
      }
    } else if (Game.api.gameState == Game.GAME_STATE_END_GAME) {
      Game.actionShowFinishedGame();
    } else {
      Game.page =
        $('<p>', {'text': 'Can\'t figure out what action to take next', });
      Game.form = null;
      Game.layoutPage();
    }
  } else {

    // Game retrieval failed, so just layout the page with no contents
    // and whatever message was received while trying to load the game
    Game.page = null;
    Game.form = null;
    Game.layoutPage();
  }
};

Game.layoutPage = function() {
  if ($('#game_page').length === 0) {
    throw('Internal error: #game_page not defined in layoutPage()');
  }

  $('#game_page').empty();
  $('#game_page').append(Game.page);

  if (Game.form) {
    $('#game_action_button').click(Game.form);
  }
};

/////
// HELPERS for generic functions

// Utility routine to parse the game data returned by the server
// Adds three types of game data:
//   Game.api.*: metadata about the entire game
//   Game.api.player: data about the logged in player
//   Game.api.opponent: data about the opposing player
// The "player" and "opponent" items should be identically formatted
Game.parseGameData = function(currentPlayerIdx, playerNameArray) {

  // Do some sanity-checking of the gameData object we have
  if (Game.api.gameData.status != 'ok') {
    return false;
  }
  if (Game.game != Game.api.gameData.data.gameId) {
    return false;
  }
  if (!($.isNumeric(currentPlayerIdx))) {
    return false;
  }

  Game.api.gameId =  Game.api.gameData.data.gameId;
  Game.api.roundNumber = Game.api.gameData.data.roundNumber;
  Game.api.maxWins = Game.api.gameData.data.maxWins;
  Game.api.gameState = Game.api.gameData.data.gameState;
  Game.api.playerIdx = currentPlayerIdx;
  Game.api.opponentIdx = 1 - currentPlayerIdx;
  Game.api.validAttackTypeArray =
    Game.api.gameData.data.validAttackTypeArray;

  // Set defaults for both players
  Game.api.player = Game.parsePlayerData(
                      Game.api.playerIdx, playerNameArray);
  Game.api.opponent = Game.parsePlayerData(
                        Game.api.opponentIdx, playerNameArray);

  // Parse game WLT text into a string for convenience
  Game.api.player.gameScoreStr = Game.playerWLTText('player');
  Game.api.opponent.gameScoreStr = Game.playerWLTText('opponent');

  return true;
};

// Given a player index, parse all data out of the appropriate arrays,
// and return it.  This function can be used for either the logged-in
// player or the opponent.
Game.parsePlayerData = function(playerIdx, playerNameArray) {
  var data = {
    'playerId': Game.api.gameData.data.playerIdArray[playerIdx],
    'playerName': playerNameArray[playerIdx],
    'buttonName': Game.api.gameData.data.buttonNameArray[playerIdx],
    'waitingOnAction':
      Game.api.gameData.data.waitingOnActionArray[playerIdx],
    'roundScore': Game.api.gameData.data.roundScoreArray[playerIdx],
    'gameScoreDict':
      Game.api.gameData.data.gameScoreArrayArray[playerIdx],
    'nDie': Game.api.gameData.data.nDieArray[playerIdx],
    'valueArray': Game.api.gameData.data.valueArrayArray[playerIdx],
    'sidesArray': Game.api.gameData.data.sidesArrayArray[playerIdx],
    'dieRecipeArray':
      Game.api.gameData.data.dieRecipeArrayArray[playerIdx],

     // N.B. These arrays describe the other player's dice which this
     // player has captured
    'nCapturedDie': Game.api.gameData.data.nCapturedDieArray[playerIdx],
    'capturedValueArray':
      Game.api.gameData.data.capturedValueArrayArray[playerIdx],
    'capturedSidesArray':
      Game.api.gameData.data.capturedSidesArrayArray[playerIdx],
    'capturedRecipeArray':
      Game.api.gameData.data.capturedRecipeArrayArray[playerIdx],

    'swingRequestArray': {},
  };

  $.each(
    Game.api.gameData.data.swingRequestArrayArray[playerIdx],
    function(letter, range) {
      data.swingRequestArray[letter] = {
        'min': parseInt(range[0], 10),
        'max': parseInt(range[1], 10)
      };
    }
  );

  // activePlayerIdx may be either player or may be null
  if (Game.api.gameData.data.activePlayerIdx == playerIdx) {
    data.isActive = true;
  } else {
    data.isActive = false;
  }

  // playerWithInitiativeIdx may be either player or may be null
  if (Game.api.gameData.data.playerWithInitiativeIdx == playerIdx) {
    data.hasInitiative = true;
  } else {
    data.hasInitiative = false;
  }

  return data;
};

// What actions can this player take during the react to initiative phase
Game.parseValidInitiativeActions = function() {
  Game.api.player.initiativeActions = {};
  if (Game.api.gameState == Game.GAME_STATE_REACT_TO_INITIATIVE) {
    var focus = {};
    var chance = {};

    $.each(Game.api.player.dieRecipeArray, function(i) {
      var tdvals = Game.dieValidTurndownValues(
        Game.api.player.dieRecipeArray[i],
        Game.api.player.valueArray[i]);
      if (tdvals.length > 0) {
        focus[i] = tdvals;
      }

      if (Game.dieCanRerollForInitiative(Game.api.player.dieRecipeArray[i])) {
        chance[i] = true;
      }
    });
    if (Object.keys(focus).length > 0) {
      Game.api.player.initiativeActions.focus = focus;
    }
    if (Object.keys(chance).length > 0) {
      Game.api.player.initiativeActions.chance = chance;
    }
    Game.api.player.initiativeActions.decline = true;
  }
};

////////////////////////////////////////////////////////////////////////
// Routines for each type of game action that could be taken

// It is time to choose swing dice, and the current player has dice to choose
Game.actionChooseSwingActive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader('Your turn to choose swing dice');

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(false);

  // Create a form for submitting swing values
  var swingform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
  });
  var swingtable = $('<table>', {'id': 'swing_table', });
  $.each(
    Game.api.player.swingRequestArray,
    function(letter, range) {
      var swingrow = $('<tr>', {});
      var swingtext = letter + ': (' + range.min + '-' + range.max + ')';
      swingrow.append($('<td>', { 'text': swingtext, }));
      var swinginput = $('<td>', {});
      swinginput.append($('<input>', {
        'type': 'text',
        'class': 'swing',
        'id': 'swing_' + letter,
        'size': '2',
        'maxlength': '2',
      }));
      swingrow.append(swinginput);
      swingtable.append(swingrow);
    });
  swingform.append(swingtable);
  swingform.append($('<br>'));
  swingform.append($('<button>', {
    'id': 'game_action_button',
    'text': 'Submit',
  }));

  // Add the swing die form to the left column of the die table
  var formtd = $('<td>', {});
  formtd.append($('<br>'));
  formtd.append(swingform);
  var formrow = $('<tr>', {});
  formrow.append(formtd);
  formrow.append($('<td>', {}));
  dietable.append(formrow);

  // Add the die table to the page
  Game.page.append(dietable);
  Game.pageAddFooter();

  // Function to invoke on button click
  Game.form = Game.formChooseSwingActive;

  // Now layout the page
  Game.layoutPage();
};

Game.actionChooseSwingInactive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader('Opponent\'s turn to choose swing dice');

  var dietable = Game.dieRecipeTable(false);
  Game.page.append(dietable);
  Game.page.append($('<br>'));

  Game.page.append($('<p>', {
    'text':
      'Your swing dice are set. ' +
      'Please wait patiently for your opponent to set swing dice.'
  }));

  Game.pageAddFooter();

  Game.form = null;

  // Now layout the page
  Game.layoutPage();
};

Game.actionReactToInitiativeActive = function() {
  Game.parseValidInitiativeActions();
  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Your turn to try to gain initiative using die skills');

  // Create a form for reacting to initiative
  var reactform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
  });

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(true, true);

  reactform.append(dietable);
  reactform.append($('<br>'));

  var reacttypeselect = $('<select>', {
    'id': 'react_type_select',
    'name': 'react_type_select',
  });
  $.each(
    Game.api.player.initiativeActions,
    function(typename, typedice) {
      var typetext;
      switch(typename) {
      case 'focus':
        typetext = 'Turn down focus dice';
        break;
      case 'chance':
        typetext = 'Reroll one chance die';
        break;
      case 'decline':
        typetext = 'Take no action';
        break;
      }
      reacttypeselect.append(
        $('<option>', {
          'value': typename,
          'label': typetext,
          'text': typetext,
        }));
    });
  reactform.append(reacttypeselect);

  reactform.append(
    $('<button>', {
      'id': 'game_action_button',
      'text': 'Submit',
    }));

  // Add the form to the page
  Game.page.append(reactform);
  Game.pageAddFooter();

  // Function to invoke on button click
  Game.form = Game.formReactToInitiativeActive;

  // Now layout the page
  Game.layoutPage();
};

Game.actionReactToInitiativeInactive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Opponent\'s turn to try to gain initiative using die skills');

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(true, false);

  Game.page.append(dietable);
  Game.page.append($('<br>'));

  Game.page.append($('<p>', {'text':
    'Please wait patiently for your opponent to use chance/focus dice' }));

  Game.pageAddFooter();

  // Function to invoke on button click
  Game.form = null,

  // Now layout the page
  Game.layoutPage();
};

Game.actionPlayTurnActive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader('Your turn to attack');
  Game.page.append($('<br>'));
  Game.pageAddDieBattleTable(true);
  Game.page.append($('<br>'));

  var attackdiv = $('<div>');
  attackdiv.append(Game.chatBox());
  var attackform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
  });

  var attacktypeselect = $('<select>', {
    'id': 'attack_type_select',
    'name': 'attack_type_select',
  });
  $.each(Game.api.validAttackTypeArray, function(typename, typevalue) {
    var typetext;
    if (typename == 'Pass') {
      typetext = typename;
    } else {
      typetext = typename + ' Attack';
    }
    attacktypeselect.append(
      $('<option>', {
        'value': typevalue,
        'label': typename,
        'text': typetext,
      }));
  });
  attackform.append(attacktypeselect);

  attackform.append($('<button>', {
    'id': 'game_action_button',
    'text': 'Submit',
  }));
  attackdiv.append(attackform);
  Game.page.append(attackdiv);

  Game.pageAddFooter();

  // Function to invoke on button click
  Game.form = Game.formPlayTurnActive;

  // Now layout the page
  Game.layoutPage();
};

Game.actionPlayTurnInactive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader('Opponent\'s turn to attack');
  Game.page.append($('<br>'));
  Game.pageAddDieBattleTable(false);
  Game.page.append($('<p>', {'text':
    'It is your opponent\'s turn to attack right now.' }));

  Game.pageAddFooter();

  Game.form = null;

  // Now layout the page
  Game.layoutPage();
};

Game.actionShowFinishedGame = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader('This game is over');
  Game.page.append($('<br>'));
  Game.pageAddGamePlayerStatus('player', false, false);
  Game.page.append($('<br>'));
  Game.pageAddGameWinner();
  Game.page.append($('<br>'));
  Game.pageAddGamePlayerStatus('opponent', true, false);

  Game.pageAddFooter();

  Game.form = null;

  // Now layout the page
  Game.layoutPage();
};

////////////////////////////////////////////////////////////////////////
// Form submission functions

// Form submission action for choosing swing dice
Game.formChooseSwingActive = function() {
  var textFieldsFilled = true;
  var swingValueArray = {};

  // Iterate over expected swing values
  $.each(Game.api.player.swingRequestArray, function(letter, range) {
    var value = $('#swing_' + letter).val();
    if ($.isNumeric(value)) {
      swingValueArray[letter] = value;
    } else {
      textFieldsFilled = false;
    }
  });

  if (textFieldsFilled) {
    $.post(
      Env.api_location,
      {
        type: 'submitSwingValues',
        game: Game.game,
        swingValueArray: swingValueArray,
        roundNumber: Game.api.roundNumber,
        timestamp: Game.api.timestamp,
      },
      function(rs) {
        if ('ok' == rs.status) {
          Env.message = {
            'type': 'success',
            'text': 'Successfully set swing values',
          };
        } else {
          Env.message = {
            'type': 'error',
            'text': rs.message,
          };
        }
        Game.showGamePage();
      }
    ).fail(
      function() {
        Env.message = {
          'type': 'error',
          'text': 'Internal error when calling submitSwingValues',
        };
        Game.showGamePage();
      }
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': 'Some swing values missing or nonnumeric',
    };
    Game.showGamePage();
  }
};

// Form submission action for reacting to initiative
Game.formReactToInitiativeActive = function() {
  var formValid = true;
  var error = false;
  var action = $('#react_type_select').val();
  var dieIdxArray = [];
  var dieValueArray = [];

  switch (action) {

  // valid action, nothing special to do
  case 'decline':
    break;

  case 'focus':
    if ('focus' in Game.api.player.initiativeActions) {
      $.each(Game.api.player.initiativeActions.focus, function(i, vals) {
        var value = parseInt($('#init_react_' + i).val(), 10);
        if (value != Game.api.player.valueArray[i]) {
          if (vals.indexOf(value) >= 0) {
            dieIdxArray.push(i);
            dieValueArray.push(value);
          } else {
            error = 'Invalid turndown value specified for focus die';
            formValid = false;
          }
        }
      });
      if (dieIdxArray.length === 0) {
        error = 'Specified focus action but did not turn down any dice';
        formValid = false;
      }
    } else {
      error = 'No focus dice to turn down';
      formValid = false;
    }
    break;

  case 'chance':
    if ('chance' in Game.api.player.initiativeActions) {
      $.each(Game.api.player.initiativeActions.chance, function(i, vals) {
        var value = $('#init_react_' + i).val();
        if (value != Game.api.player.valueArray[i]) {
          if (value == 'reroll') {
            dieIdxArray.push(i);
            dieValueArray.push(value);
          } else {
            error = 'Bad value specified for chance action - choose "reroll"';
            formValid = false;
          }
        }
      });
      if (dieIdxArray.length === 0) {
        error =
          'Specified chance action but did not choose any dice to reroll';
        formValid = false;
      } else if (dieIdxArray.length > 1) {
        error =
          'Specified chance action but chose more than one die to reroll';
        formValid = false;
      }
    } else {
      error = 'No chance dice to reroll';
      formValid = false;
    }
    break;

  default:
    error = 'Specified action is not valid';
    formValid = false;
  }

  if (formValid) {
    $.post(
      Env.api_location,
      {
        type: 'reactToInitiative',
        game: Game.game,
        roundNumber: Game.api.roundNumber,
        timestamp: Game.api.timestamp,
        action: action,
        dieIdxArray: dieIdxArray,
        dieValueArray: dieValueArray,
      },
      function(rs) {
        var message;
        if ('ok' == rs.status) {
          switch (action) {
          case 'chance':
            if (rs.data.gained_initiative) {
              message =
                'Successfully gained initiative by rerolling chance die';
            } else {
              message = 'Rerolled chance die, but did not gain initiative';
            }
            break;
          case 'decline':
            message = 'Declined to use chance/focus dice';
            break;
          case 'focus':
            message = 'Successfully gained initiative using focus dice';
            break;
          }

          Env.message = {
            'type': 'success',
            'text': message,
          };
        } else {
          Env.message = {
            'type': 'error',
            'text': rs.message,
          };
        }
        Game.showGamePage();
      }
    ).fail(
      function() {
        Env.message = {
          'type': 'error',
          'text': 'Internal error when calling reactToInitiative',
        };
        Game.showGamePage();
      }
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': error,
    };
    Game.showGamePage();
  }
};

// Form submission action for playing a turn
Game.formPlayTurnActive = function() {

  // Initialize the array of die select statuses to all false, then
  // turn on the dice which have been selected
  var dieSelectStatus = {};
  for (var i = 0 ; i < Game.api.player.nDie; i++) {
    dieSelectStatus[Game.dieIndexId('player', i)] = false;
  }
  for (var i = 0 ; i < Game.api.opponent.nDie; i++) {
    dieSelectStatus[Game.dieIndexId('opponent', i)] = false;
  }
  $('div.selected').each(function(index, element) {
    dieSelectStatus[$(element).attr('id')] = true;
  });

  // Get the specified attack type
  var attackType = $('#attack_type_select').val();

  // Get the game chat
  var chat = $('#game_chat').val();

  // Now try submitting the result
  $.post(
    Env.api_location,
    {
      type: 'submitTurn',
      game: Game.game,
      attackerIdx: Game.api.playerIdx,
      defenderIdx: Game.api.opponentIdx,
      dieSelectStatus: dieSelectStatus,
      attackType: attackType,
      chat: chat,
      roundNumber: Game.api.roundNumber,
      timestamp: Game.api.timestamp,
    },
    function(rs) {
      if (rs.status == 'ok') {
        Env.message = {
          'type': 'success',
          'text': rs.message,
        };
      } else {
        Env.message = {
          'type': 'error',
          'text': rs.message,
        };
      }
      Game.showGamePage();
    }
  ).fail(
    function() {
      Env.message = {
        'type': 'error',
        'text': 'Internal error when calling submitTurn',
      };
      Game.showGamePage();
    }
  );
};

////////////////////////////////////////////////////////////////////////
// Page layout helper routines

// Display header information about the game
Game.pageAddGameHeader = function(action_desc) {
  Game.page.append($('<div>', {'id': 'game_id',
                               'text': 'Game #' + Game.api.gameId, }));
  Game.page.append($('<div>', {'id': 'round_number',
                               'text': 'Round #' + Game.api.roundNumber, }));
  Game.page.append($('<div>', {'id': 'action_desc',
                               'class': 'action_desc',
                               'text': action_desc}));
  Game.page.append($('<br>'));
  return true;
};

// Display common page footer data
Game.pageAddFooter = function() {
  Game.pageAddTimestampFooter();
  Game.pageAddLogFooter();
};

// Display a footer-style message with the last action timestamp
Game.pageAddTimestampFooter = function() {

  // Timestamp has a different meaning if the game is over
  var timestamptext;
  if (Game.api.gameState == Game.GAME_STATE_END_GAME) {
    timestamptext = 'Game completed at';
  } else {
    timestamptext = 'Last action time';
  }

  Game.page.append($('<br>'));
  Game.page.append($('<div>', {
    'text': timestamptext + ': ' + Game.api.timestamp,
  }));
  return true;
};

// Display recent game data from the action log at the foot of the page
Game.pageAddLogFooter = function() {
  if ((Game.api.chatLog.length > 0) || (Game.api.actionLog.length > 0)) {
    var logdiv = $('<div>');
    var logtable = $('<table>');
    var logrow = $('<tr>');

    if (Game.api.actionLog.length > 0) {
      var actiontd = $('<td>', {'class': 'logtable', });
      actiontd.append($('<p>', {'text': 'Recent game activity', }));
      var actiontable = $('<table>', {'border': 'on', });
      $.each(Game.api.actionLog, function(logindex, logentry) {
        var nameclass;
        if (logentry.message.indexOf(Game.api.player.playerName + ' ') === 0) {
          nameclass = 'chatplayer';
        } else {
          nameclass = 'chatopponent';
        }
        var actionrow = $('<tr>');
        actionrow.append(
          $('<td>', {
            'class': nameclass,
            'nowrap': 'nowrap',
            'text': '(' + logentry.timestamp + ')',
          }));
        actionrow.append(
          $('<td>', {
            'class': 'left',
            'text': logentry.message,
          }));
        actiontable.append(actionrow);
      });
      actiontd.append(actiontable);
      logrow.append(actiontd);
    }

    if (Game.api.chatLog.length > 0) {
      var chattd = $('<td>', {'class': 'logtable', });
      chattd.append($('<p>', {'text': 'Recent game chat', }));
      var chattable = $('<table>', {'border': 'on', });
      $.each(Game.api.chatLog, function(logindex, logentry) {
        var chatrow = $('<tr>');
        var nameclass;
        if (logentry.player == Game.api.player.playerName) {
          nameclass = 'chatplayer';
        } else {
          nameclass = 'chatopponent';
        }
        chatrow.append($('<td>', {
          'class': nameclass,
          'nowrap': 'nowrap',
          'text': logentry.player + ' (' + logentry.timestamp + ')',
        }));
        chatrow.append($('<td>', {
          'class': 'left',
          'text': logentry.message,
        }));
        chattable.append(chatrow);
      });
      chattd.append(chattable);
      logrow.append(chattd);
    }

    logtable.append(logrow);
    logdiv.append(logtable);

    Game.page.append($('<hr>'));
    Game.page.append(logdiv);
  }
};


// Generate and return a two-column table of the dice in each player's recipe
Game.dieRecipeTable = function(react_initiative, active) {

  var dietable = $('<table>', {'id': 'die_recipe_table', });
  dietable.append(Game.playerOpponentHeaderRow('Player', 'playerName'));
  dietable.append(Game.playerOpponentHeaderRow('Button', 'buttonName'));
  dietable.append(Game.playerOpponentHeaderRow('', 'gameScoreStr'));

  if (react_initiative) {
    var focusHeaderLRow = $('<tr>');
    focusHeaderLRow.append($('<th>', { 'text': 'Recipe' }));
    focusHeaderLRow.append($('<th>', { 'text': 'Value' }));
    var focusHeaderRRow = focusHeaderLRow.clone();

    var focusLTable = $('<table>');
    var focusRTable = $('<table>');

    focusLTable.append(focusHeaderLRow);
    focusRTable.append(focusHeaderRRow);
  }

  var maxDice = Math.max(Game.api.player.nDie, Game.api.opponent.nDie);
  for (var i = 0; i < maxDice; i++) {
    var playerEnt = Game.dieTableEntry(
      i, Game.api.player.nDie,
      Game.api.player.dieRecipeArray,
      Game.api.player.sidesArray);
    var opponentEnt = Game.dieTableEntry(
      i, Game.api.opponent.nDie,
      Game.api.opponent.dieRecipeArray,
      Game.api.opponent.sidesArray);
    if (react_initiative) {
      var dieLRow = $('<tr>');
      var dieRRow = $('<tr>');
      dieLRow.append(playerEnt);
      if (active) {
        var initopts = [];
        if (('focus' in Game.api.player.initiativeActions) &&
            (i in Game.api.player.initiativeActions.focus)) {
          initopts = Game.api.player.initiativeActions.focus[i].concat();
        }
        if (('chance' in Game.api.player.initiativeActions) &&
            (i in Game.api.player.initiativeActions.chance)) {
          initopts.push('reroll');
        }
      }
      if ((active) && (initopts.length > 0)) {
        dieLRow.append(
          Game.dieValueSelectTd('init_react_' + i, initopts,
                                Game.api.player.valueArray[i]));
      } else {
        dieLRow.append($('<td>', { 'text': Game.api.player.valueArray[i] }));
      }
      dieRRow.append(opponentEnt);
      dieRRow.append($('<td>', { 'text': Game.api.opponent.valueArray[i] }));
      focusLTable.append(dieLRow);
      focusRTable.append(dieRRow);
    } else {
      var dierow = $('<tr>', {});
      dierow.append(playerEnt);
      dierow.append(opponentEnt);
      dietable.append(dierow);
    }
  }
  if (react_initiative) {
    var focusrow = $('<tr>');
    var focusLTd = $('<td>');
    var focusRTd = $('<td>');
    focusLTd.append(focusLTable);
    focusRTd.append(focusRTable);
    focusrow.append(focusLTd);
    focusrow.append(focusRTd);
    dietable.append(focusrow);
  }
  return dietable;
};

Game.dieTableEntry = function(i, nDie, dieRecipeArray, dieSidesArray) {
  if (i < nDie) {
    var dieval = Game.dieRecipeText(dieRecipeArray[i], dieSidesArray[i]);
    return $('<td>', {'text': dieval, });
  }
  return $('<td>', {});
};

// Display each player's dice in "battle" layout
Game.pageAddDieBattleTable = function(clickable) {

  Game.pageAddGamePlayerStatus('player', false, true);
  Game.pageAddGamePlayerDice('player', clickable);
  Game.page.append($('<br>'));
  Game.pageAddGamePlayerDice('opponent', clickable);
  Game.pageAddGamePlayerStatus('opponent', true, true);
  return true;
};

// Add a brief mid-game status listing for the requested player
Game.pageAddGamePlayerStatus = function(player, reversed, game_active) {

  // Player name
  var playerDiv = $('<div>');
  playerDiv.append($('<span>', {
    'text': 'Player: ' + Game.api[player].playerName,
  }));

  // Button name
  var buttonDiv = $('<div>');
  buttonDiv.append($('<span>', {
    'text': 'Button: ' + Game.api[player].buttonName,
  }));

  // Game score
  var gameScoreDiv = $('<div>');
  gameScoreDiv.append($('<span>', { 'text': Game.api[player].gameScoreStr, }));

  if (game_active) {
    // Round score, only applicable in active games
    var roundScoreDiv = $('<div>');
    roundScoreDiv.append($('<span>', {
      'text': 'Score: ' + Game.api[player].roundScore,
    }));

    // Dice captured this round, only applicable in active games
    var capturedDieText;
    if (Game.api[player].nCapturedDie > 0) {
      var capturedDieDescs = [];
      $.each(Game.api[player].capturedRecipeArray, function(idx, recipe) {
        capturedDieDescs.push(
          Game.dieRecipeText(recipe, Game.api[player].capturedSidesArray[idx]));
      });
      capturedDieText = capturedDieDescs.join(', ');
    } else {
      capturedDieText = 'none';
    }
    var capturedDiceDiv = $('<div>');
    capturedDiceDiv.append($('<span>', {
      'text': 'Dice captured: ' + capturedDieText,
    }));
  }

  // Order the elements depending on the "reversed" flag
  if (reversed) {
    if (game_active) {
      Game.page.append(capturedDiceDiv);
      Game.page.append(roundScoreDiv);
    }
    Game.page.append(gameScoreDiv);
    Game.page.append(buttonDiv);
    Game.page.append(playerDiv);
  } else {
    Game.page.append(playerDiv);
    Game.page.append(buttonDiv);
    Game.page.append(gameScoreDiv);
    if (game_active) {
      Game.page.append(roundScoreDiv);
      Game.page.append(capturedDiceDiv);
    }
  }

  return true;
};

// Add a display of all dice for the requested player, specifying whether
// the dice should be selectable
Game.pageAddGamePlayerDice = function(player, clickable) {
  var i = 0;
  while (i < Game.api[player].nDie) {
    var dieDiv = $('<div>', {
      'id': Game.dieIndexId(player, i),
      'class': 'die_img unselected',
      'style':
        'background-image: url(images/Circle.png);' +
        'height:70px;width:70px;background-size:100%',
    });
    dieDiv.append($('<span>', {
      'class': 'die_overlay',
      'text': Game.api[player].valueArray[i],
    }));
    dieDiv.append($('<br>'));

    var dieRecipeText = Game.dieRecipeText(
      Game.api[player].dieRecipeArray[i],
      Game.api[player].sidesArray[i]);
    dieDiv.append($('<span>', {
      'class': 'die_recipe',
      'text': dieRecipeText,
    }));
    if (clickable) {
      dieDiv.click(Game.dieBorderToggleHandler);
    }
    Game.page.append(dieDiv);
    i += 1;
  }
};

// Show the winner of a completed game
Game.pageAddGameWinner = function() {

  var playerWins = Game.api.player.gameScoreDict.W;
  var opponentWins = Game.api.opponent.gameScoreDict.W;
  var winnerName;
  if (playerWins > opponentWins) {
    winnerName = Game.api.player.playerName;
  } else if (playerWins < opponentWins) {
    winnerName = Game.api.opponent.playerName;
  } else {
    winnerName = false;
  }
  var winnerText;
  if (winnerName) {
    winnerText = winnerName + ' won!';
  } else {
    winnerText = 'TIE';
  }

  var winnerDiv = $('<div>');
  winnerDiv.append($('<span>', {
    'id': 'winner_name',
    'text': winnerText,
  }));
  Game.page.append(winnerDiv);
};

Game.dieIndexId = function(player, dieidx) {
  var playerIdx;
  if (player == 'player') {
    playerIdx = Game.api.playerIdx;
  } else {
    playerIdx = Game.api.opponentIdx;
  }
  return ('playerIdx_' + playerIdx + '_dieIdx_' + dieidx);
};

// Two-column row containing information about the player and the opponent
Game.playerOpponentHeaderRow = function(label, field) {
  var headerrow = $('<tr>', {});
  var prefix = '';
  if (label) {
    prefix = label + ': ';
  }
  headerrow.append($('<th>', {
    'text': prefix + Game.api.player[field],
  }));
  headerrow.append($('<th>', {
    'text': prefix + Game.api.opponent[field],
  }));
  return headerrow;
};

Game.playerWLTText = function(player) {
  var text = 'W/L/T: ' + Game.api[player].gameScoreDict.W +
              '/' + Game.api[player].gameScoreDict.L +
              '/' + Game.api[player].gameScoreDict.D +
              ' (' + Game.api.maxWins + ')';
  return text;
};

// If the recipe doesn't contain (sides), assume there are swing
// dice in the recipe, so we need to specify the current number
// of sides
Game.dieRecipeText = function(recipe, sides) {
  var dieRecipeText = recipe;
  if (sides) {
    var lparen = recipe.indexOf('(');
    var rparen = recipe.indexOf(')');
    var recipeSideStrings = recipe.substring(lparen + 1, rparen).split(',');
    var sidesum = 0;
    var swingcount = 0;
    for (var i = 0; i < recipeSideStrings.length; i++) {
      var itemSides = parseInt(recipeSideStrings[i], 10);
      if (itemSides > 0) {
        sidesum += itemSides;
      } else {
        swingcount += 1;
      }
    }
    if (sidesum != sides) {
      dieRecipeText = dieRecipeText.replace(
                        ')', '=' + (sides/swingcount) + ')');
    }
  }
  return dieRecipeText;
};

Game.dieValidTurndownValues = function(recipe, value) {
  // Focus dice can be turned down
  if (recipe.match('f')) {
    var turndown = [];
    var minval = 1;
    if (recipe.match(',')) {
      minval = 2;
    }
    for (var i = value - 1; i >= minval; i--) {
      turndown.push(i);
    }
    return turndown;
  }
  return [];
};

Game.dieCanRerollForInitiative = function(recipe) {
  if (recipe.match('c')) {
    return true;
  }
  return false;
};

Game.dieBorderToggleHandler = function() {
  $(this).toggleClass('selected unselected');
};

// The selected value is the first value provided, and is not part
// of the array
Game.dieValueSelectTd = function(
     selectname, valuearray, selectedval) {
  var selectTd = $('<td>');
  var select = $('<select>', {
    'id': selectname,
    'name': selectname,
    'class': 'center',
  });
  select.append($('<option>', {
    'value': selectedval,
    'label': selectedval,
    'text': selectedval,
    'selected': 'selected',
  }));
  $.each(valuearray, function(idx) {
    select.append($('<option>', {
      'value': valuearray[idx],
      'label': valuearray[idx],
      'text': valuearray[idx],
    }));
  });
  selectTd.append(select);
  return selectTd;
};

Game.chatBox = function() {
  var chattable = $('<table>');
  var chatrow = $('<tr>');
  chatrow.append($('<td>', {'text': 'Chat:', }));
  chatrow.append($('<textarea>', {
    'id': 'game_chat',
    'rows': '3',
    'cols': '50',
    'maxlength': '500',
  }));
  chattable.append(chatrow);
  return chattable;
};
