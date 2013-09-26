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
Game.GAME_STATE_START_ROUND = 30;
Game.GAME_STATE_START_TURN = 40;
Game.GAME_STATE_END_TURN = 48;
Game.GAME_STATE_END_ROUND = 50;
Game.GAME_STATE_END_GAME = 60;

Game.messageTypeColors = {
  'none': 'black',
  'error': 'red',
  'success': 'green',
};

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

  // Make sure a couple of div elements that we will need exist in the
  // page body
  if ($('#game_message').length == 0) {
    $('body').append($('<div>', {'id': 'game_message', }));
  }
  if ($('#game_page').length == 0) {
    $('body').append($('<div>', {'id': 'game_page', }));
  }

  // Find the current game, and invoke that with the "parse game state"
  // callback
  Game.getCurrentGame(Game.showStatePage);
}

// the current game should be provided as a GET parameter to the page
Game.getCurrentGame = function(callbackfunc) {
  $.getScript('js/Env.js');
  Game.api = {
    'load_status': 'failed',
  }
  Game.game = Env.getParameterByName('game');
  if (Game.game == null) {
    Game.message = {
      'type': 'error', 'text': 'No game specified.  Nothing to do.'
    };
    return callbackfunc();
  }
  if ($.isNumeric(Game.game) == false) {
    Game.message = {
      'type': 'error',
      'text': 'Specified game is not a valid number.  Nothing to do.'
    };
    return callbackfunc();
  }
  if (Login.logged_in == false) {
    Game.message = {
      'type': 'error',
      'text': 'Not logged in.  Nothing to do.'
    };
    return callbackfunc();
  }

  $.post('../api/responder.php',
         { type: 'loadGameData', game: Game.game, },
         function(rs) {
           if (rs.status == 'ok') {
             Game.api.gameData = rs.gameData;
             Game.api.timestamp = rs.timestamp;
             if (Game.parseGameData(rs.currentPlayerIdx, rs.playerNameArray)) {
               Game.api.load_status = 'ok';
             } else {
               Game.message = {
                 'type': 'error',
                 'text': 'Game data received from server could not be parsed!',
               };
             }
           } else {
             Game.message = {
               'type': 'error',
               'text': 'Failed to lookup status of game ' + Game.game,
             };
           }
           return callbackfunc();
         }
  ).fail(function() {
    Game.message = {
      'type': 'error',
      'text': 'Internal error when looking up game',
    };
    return callbackfunc();
  });
}

// Assemble and display the game portion of the page
Game.showStatePage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Game.showStatusMessage();

  // Figure out what to do next based on the game state
  if (Game.api.load_status == 'ok') {
    if (Game.api.gameState == Game.GAME_STATE_SPECIFY_DICE) {
      if (Game.api.player.waitingOnAction) {
        Game.actionChooseSwingActive();
      } else {
        Game.actionChooseSwingInactive();
      }
    } else if (Game.api.gameState == Game.GAME_STATE_START_TURN) {
      if (Game.api.player.waitingOnAction) {
        Game.actionPlayTurnActive();
      } else {
        Game.actionPlayTurnInactive();
      }
    } else {
      Game.page
        = $('<p>', {'text': "Can't figure out what action to take next", });
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
}

Game.layoutPage = function() {
  $('#game_page').empty();
  $('#game_page').append(Game.page);

  if (Game.form) {
    $('#game_action_button').click(Game.form);
  }
}

/////
// HELPERS for generic functions

// Show a status message based on a static list of options
Game.showStatusMessage = function() {
  $('#game_message').empty();
  if (Game.message) {
    var msgobj = $('<p>');
    msgobj.append($('<font>',
                  {
                    'color': Game.messageTypeColors[Game.message.type],
                    'text': Game.message.text,
                  }));
    $('#game_message').append(msgobj);
  }
}

// Utility routine to parse the game data returned by the server
// Adds three types of game data:
//   Game.api.*: metadata about the entire game
//   Game.api.player: data about the logged in player
//   Game.api.opponent: data about the opposing player
// The "player" and "opponent" items should be identically formatted
Game.parseGameData = function(currentPlayerIdx, playerNameArray) {

  // Do some sanity-checking of the gameData object we have
  if (Game.api.gameData['status'] != 'ok') {
    return false;
  };
  if (Game.game != Game.api.gameData['data']['gameId']) {
    return false;
  };
  if ($.isNumeric(currentPlayerIdx) == false) {
    return false;
  }
 
  Game.api.gameId =  Game.api.gameData['data']['gameId'];
  Game.api.roundNumber = Game.api.gameData['data']['roundNumber'];
  Game.api.gameState = Game.api.gameData['data']['gameState'];
  Game.api.playerIdx = currentPlayerIdx;
  Game.api.opponentIdx = 1 - currentPlayerIdx;

  // Set defaults for both players
  Game.api.player = Game.parsePlayerData(
                      Game.api.playerIdx, playerNameArray);
  Game.api.opponent = Game.parsePlayerData(
                        Game.api.opponentIdx, playerNameArray);
  return true;
}

// Given a player index, parse all data out of the appropriate arrays,
// and return it.  This function can be used for either the logged-in
// player or the opponent.
Game.parsePlayerData = function(playerIdx, playerNameArray) {
  var data = {
    'playerId': Game.api.gameData['data']['playerIdArray'][playerIdx],
    'playerName': playerNameArray[playerIdx],
    'buttonName': Game.api.gameData['data']['buttonNameArray'][playerIdx],
    'waitingOnAction':
      Game.api.gameData['data']['waitingOnActionArray'][playerIdx],
    'roundScore': Game.api.gameData['data']['roundScoreArray'][playerIdx],
    'gameScoreDict':
      Game.api.gameData['data']['gameScoreArrayArray'][playerIdx],
    'nDie': Game.api.gameData['data']['nDieArray'][playerIdx],
    'valueArray': Game.api.gameData['data']['valueArrayArray'][playerIdx],
    'sidesArray': Game.api.gameData['data']['sidesArrayArray'][playerIdx],
    'dieRecipeArray':
      Game.api.gameData['data']['dieRecipeArrayArray'][playerIdx],
    'swingRequestArray':
      Game.api.gameData['data']['swingRequestArrayArray'][playerIdx],
  }

  // activePlayerIdx may be either player or may be null
  if (Game.api.gameData['data']['activePlayerIdx'] == playerIdx) {
    data['isActive'] = true;
  } else {
    data['isActive'] = false;
  }

  // playerWithInitiativeIdx may be either player or may be null
  if (Game.api.gameData['data']['playerWithInitiativeIdx'] == playerIdx) {
    data['hasInitiative'] = true;
  } else {
    data['hasInitiative'] = false;
  }

  return data;
}

////////////////////////////////////////////////////////////////////////
// Routines for each type of game action that could be taken

// It is time to choose swing dice, and the current player has dice to choose
Game.actionChooseSwingActive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader();
  Game.pageAddDieRecipeTable();

  // Display a form for submitting swing values
  var swingdiv = $('<div>');
  var swingform = $('<form>', {
                      'id': 'game_action_form',
                      'action': "javascript:void(0);",
                    });
  var swingtable = $('<table>', {'id': 'swing_table', });
  $.each (Game.api.player.swingRequestArray,
          function(index, value) {
            var swingrow = $('<tr>', {});
            swingrow.append($('<td>', { 'text': value + ':', }));
            var swinginput = $('<td>', {});
            swinginput.append($('<input>', {
                               'type': 'text',
                               'class': 'text',
                               'id': 'swing_' + index,
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
  swingdiv.append(swingform);
  Game.page.append(swingform);

  Game.pageAddTimestampFooter();

  // Function to invoke on button click
  Game.form = Game.formChooseSwingActive;

  // Now layout the page
  Game.layoutPage();
}

Game.actionChooseSwingInactive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader();
  Game.pageAddDieRecipeTable();

  Game.page.append($('<p>', {'text':
    'Your swing dice are already set.  Please wait patiently for your opponent to set swing dice.' }));

  Game.pageAddTimestampFooter();

  Game.form = null;

  // Now layout the page
  Game.layoutPage();
}

Game.actionPlayTurnActive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader();
  Game.page.append($('<br>'));
  Game.pageAddDieBattleTable(true);
  Game.page.append($('<br>'));

  var attackdiv = $('<div>');
  var attackform = $('<form>', {
                       'id': 'game_action_form',
                       'action': "javascript:void(0);",
                     });
  attackform.append($('<button>', {
                        'id': 'game_action_button',
                        'text': 'Submit',
                      }));
  attackdiv.append(attackform);
  Game.page.append(attackdiv);

  Game.pageAddTimestampFooter();

  // Function to invoke on button click
  Game.form = Game.formPlayTurnActive;

  // Now layout the page
  Game.layoutPage();
}

Game.actionPlayTurnInactive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader();
  Game.page.append($('<br>'));
  Game.pageAddDieBattleTable(false);
  Game.page.append($('<p>', {'text':
    "It is your opponent's turn to attack right now." }));

  Game.pageAddTimestampFooter();

  Game.form = null;

  // Now layout the page
  Game.layoutPage();
}

// Form submission action for choosing swing dice
Game.formChooseSwingActive = function() {
  var textFieldsFilled = true;
  var swingValueArray = [];

  $('input:text').each(function(index, element) {
    var value = $(element).val();
    if ($.isNumeric(value)) {
      swingValueArray[index] = value;
    } else {
      textFieldsFilled = false;
    }
  });

  if (textFieldsFilled) {
    $.post('../api/responder.php', {
             type: 'submitSwingValues',
             game: Game.game,
             swingValueArray: swingValueArray,
             roundNumber: Game.api.roundNumber,
             timestamp: Game.api.timestamp,
           },
           function(rs) {
             if ('ok' == rs.status) {
               Game.message = {
                 'type': 'success',
                 'text': 'Successfully set swing values',
               };
             } else {
               Game.message = {
                 'type': 'error',
                 'text': 'Failed to set swing values',
               };
             }
             Game.showGamePage();
           }
    ).fail(function() {
             Game.message = { 
               'type': 'error',
               'text': 'Received internal error while submitting swing values',
             };
             Game.showGamePage();
           });
  } else {
    Game.message = { 
      'type': 'error',
      'text': 'Not enough swing values specified',
    };
    Game.showGamePage();
  }
}

// Form submission action for playing a turn
Game.formPlayTurnActive = function() {

  // Initialize the array of die select statuses to all false, then
  // turn on the dice which have been selected
  var dieSelectStatus = {};
  for (i = 0 ; i < Game.api.player.nDie; i++) {
    dieSelectStatus[Game.dieIndexId('player', i)] = false;
  }
  for (i = 0 ; i < Game.api.opponent.nDie; i++) {
    dieSelectStatus[Game.dieIndexId('opponent', i)] = false;
  }
  $('div.selected').each(function(index, element) {
    dieSelectStatus[$(element).attr('id')] = true;
  });

  // Now try submitting the result
  $.post('../api/responder.php', {
           type: 'submitTurn',
           game: Game.game,
           attackerIdx: Game.api.playerIdx,
           defenderIdx: Game.api.opponentIdx,
           dieSelectStatus: dieSelectStatus,
           roundNumber: Game.api.roundNumber,
           timestamp: Game.api.timestamp,
         },
         function(rs) {
           if ('attack valid' == rs.status) {
             Game.message = {
               'type': 'success',
               'text': 'Attack succeeded: ' + rs.message,
             };
           } else {
             Game.message = {
               'type': 'error',
               'text': 'Attack invalid',
             };
           }
           Game.showGamePage();
         }
    ).fail(function() {
             Game.message = { 
               'type': 'error',
               'text': 'Received internal error while attacking',
             };
             Game.showGamePage();
           });
}

////////////////////////////////////////////////////////////////////////
// Page layout helper routines

// Display header information about the game
Game.pageAddGameHeader = function() {
  Game.page.append($('<div>', {'id': 'game_id',
                               'text': 'Game #' + Game.api.gameId, }));
  Game.page.append($('<div>', {'id': 'round_number',
                               'text': 'Round #' + Game.api.roundNumber, }));
  return true;
}

// Display a footer-style message with the last action timestamp
Game.pageAddTimestampFooter = function() {
  Game.page.append($('<br>'));
  Game.page.append($('<div>', {
                      'text': 'Last action time: ' + Game.api.timestamp,
                     }));
  return true;
}

// Display a table of dice in each player's recipe
Game.pageAddDieRecipeTable = function() {

  var dietable = $('<table>', {'id': 'die_description_table', });
  var headerrow = $('<tr>', {});
  headerrow.append($('<th>', {
                     'id': 'header_current_player',
                     'text': Game.api.player.playerName,
                     }))
  headerrow.append($('<th>', {
                     'id': 'header_opponent',
                     'text': Game.api.opponent.playerName,
                     }))
  dietable.append(headerrow);
  var maxDice = Math.max(Game.api.player.nDie, Game.api.opponent.nDie);
  for (var i = 0; i < maxDice; i++) {
    var dierow = $('<tr>', {});
    if (i < Game.api.player.nDie) {
      var dieval = Game.api.player.dieRecipeArray[i];
      if ((Game.api.player.sidesArray[i] != null) &&
          (Game.api.player.sidesArray[i] != dieval)) {
        dieval += '=' + Game.api.player.sidesArray[i]
      }
      dierow.append($('<td>', {'text': dieval, }));
    } else {
      dierow.append($('<td>', {}));
    }
    if (i < Game.api.opponent.nDie) {
      dierow.append($('<td>', {'text': Game.api.opponent.dieRecipeArray[i], }));
    } else {
      dierow.append($('<td>', {}));
    }
    dietable.append(dierow);
  }
  Game.page.append(dietable);
  Game.page.append($('<br>'));

  return true;
}

// Display each player's dice in "battle" layout
Game.pageAddDieBattleTable = function(clickable) {

  Game.pageAddGamePlayerStatus('player', false);
  Game.pageAddGamePlayerDice('player', clickable);
  Game.page.append($('<br>'));
  Game.pageAddGamePlayerDice('opponent', clickable);
  Game.pageAddGamePlayerStatus('opponent', true);
  return true;
}

// Add a brief mid-game status listing for the requested player
Game.pageAddGamePlayerStatus = function(player, reversed) {

  // Player name
  var playerDiv = $('<div>');
  playerDiv.append($('<span>', {
    'text': "Player: " + Game.api[player].playerName, }));

  // Button name
  var buttonDiv = $('<div>');
  buttonDiv.append($('<span>', {
    'text': "Button: " + Game.api[player].buttonName, }));

  // Game score
  var gameScoreDiv = $('<div>');
  gameScoreDiv.append($('<span>', {
    'text': "W/L/T: " + Game.api[player].gameScoreDict['W'] +
            "/" + Game.api[player].gameScoreDict['L'] + 
            "/" + Game.api[player].gameScoreDict['D'], }));

  // Round score
  var roundScoreDiv = $('<div>');
  roundScoreDiv.append($('<span>', {
    'text': "Score: " + Game.api[player].roundScore, }));

  // Order the elements depending on the "reversed" flag
  if (reversed == true) {
    Game.page.append(roundScoreDiv);
    Game.page.append(gameScoreDiv);
    Game.page.append(buttonDiv);
    Game.page.append(playerDiv);
  } else {
    Game.page.append(playerDiv);
    Game.page.append(buttonDiv);
    Game.page.append(gameScoreDiv);
    Game.page.append(roundScoreDiv);
  }

  return true;
}

// Add a display of all dice for the requested player, specifying whether
// the dice should be selectable
Game.pageAddGamePlayerDice = function(player, clickable) {
  var i = 0;
  while (i < Game.api[player].nDie) {
    var dieDiv = $('<div>', {
                    'id': Game.dieIndexId(player, i),
                    'class': 'die_img unselected',
                    'style': 'background-image: url(../api/images/Circle.png)',
                   });
    dieDiv.append($('<span>', {
                      'class': 'die_overlay',
                      'text': Game.api[player].valueArray[i] +
                              ' d' + Game.api[player].sidesArray[i],
                    }));
    if (clickable) {
      dieDiv.click(Game.dieBorderToggleHandler);
    }
    Game.page.append(dieDiv);
    i += 1;
  }
}

Game.dieIndexId = function(player, dieidx) {
  if (player == 'player') {
    var playerIdx = Game.api.playerIdx;
  } else {
    var playerIdx = Game.api.opponentIdx;
  }
  return ('playerIdx_' + playerIdx + '_dieIdx_' + dieidx);
}

Game.dieBorderToggleHandler = function() {
  $(this).toggleClass('selected unselected');
}
