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
  if ($('#game_page').length == 0) {
    $('body').append($('<div>', {'id': 'game_page', }));
  }

  // Find the current game, and invoke that with the "parse game state"
  // callback
  Game.getCurrentGame(Game.showStatePage);
}

// the current game should be provided as a GET parameter to the page
Game.getCurrentGame = function(callbackfunc) {
  Game.api = {
    'load_status': 'failed',
  }
  Game.game = Env.getParameterByName('game');
  if (Game.game == null) {
    Env.message = {
      'type': 'error', 'text': 'No game specified.  Nothing to do.'
    };
    return callbackfunc();
  }
  if ($.isNumeric(Game.game) == false) {
    Env.message = {
      'type': 'error',
      'text': 'Specified game is not a valid number.  Nothing to do.'
    };
    return callbackfunc();
  }
  if (Login.logged_in == false) {
    Env.message = {
      'type': 'error',
      'text': 'Not logged in.  Nothing to do.'
    };
    return callbackfunc();
  }

  $.post(Env.api_location,
         { type: 'loadGameData', game: Game.game, },
         function(rs) {
           if (rs.status == 'ok') {
             Game.api.gameData = rs.data.gameData;
             Game.api.timestamp = rs.data.timestamp;
             Game.api.actionLog = rs.data.gameActionLog;
             if (Game.parseGameData(rs.data.currentPlayerIdx, rs.data.playerNameArray)) {
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
}

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
    } else if (Game.api.gameState == Game.GAME_STATE_START_TURN) {
      if (Game.api.player.waitingOnAction) {
        Game.actionPlayTurnActive();
      } else {
        Game.actionPlayTurnInactive();
      }
    } else if (Game.api.gameState == Game.GAME_STATE_END_GAME) {
      Game.actionShowFinishedGame();
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
  if ($('#game_page').length == 0) {
    throw("Internal error: #game_page not defined in layoutPage()");
  }

  $('#game_page').empty();
  $('#game_page').append(Game.page);

  if (Game.form) {
    $('#game_action_button').click(Game.form);
  }
}

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
  Game.api.maxWins = Game.api.gameData['data']['maxWins'];
  Game.api.gameState = Game.api.gameData['data']['gameState'];
  Game.api.playerIdx = currentPlayerIdx;
  Game.api.opponentIdx = 1 - currentPlayerIdx;
  Game.api.validAttackTypeArray = Game.api.gameData['data']['validAttackTypeArray'];

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

  var swingdiv = $('<div>');

  // Get a table containing the existing die recipes
  dietable = Game.dieRecipeTable();

  // Create a form for submitting swing values
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
                               'class': 'swing',
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
}

Game.actionChooseSwingInactive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader();

  dietable = Game.dieRecipeTable();
  Game.page.append(dietable);
  Game.page.append($('<br>'));

  Game.page.append($('<p>', {'text':
    'Your swing dice are set.  Please wait patiently for your opponent to set swing dice.' }));

  Game.pageAddFooter();

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

  var attacktypeselect = $('<select>', {
                             'id': 'attack_type_select',
                             'name': 'attack_type_select',
                           });
  $.each(Game.api.validAttackTypeArray, function(typename, typevalue) {
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
}

Game.actionPlayTurnInactive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader();
  Game.page.append($('<br>'));
  Game.pageAddDieBattleTable(false);
  Game.page.append($('<p>', {'text':
    "It is your opponent's turn to attack right now." }));

  Game.pageAddFooter();

  Game.form = null;

  // Now layout the page
  Game.layoutPage();
}

Game.actionShowFinishedGame = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader();
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
}

////////////////////////////////////////////////////////////////////////
// Form submission functions

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
    $.post(Env.api_location, {
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
    ).fail(function() {
             Env.message = { 
               'type': 'error',
               'text': 'Internal error when calling submitSwingValues',
             };
             Game.showGamePage();
           });
  } else {
    Env.message = { 
      'type': 'error',
      'text': 'Some swing values missing or nonnumeric',
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

  // Get the specified attack type
  var attackType = $('#attack_type_select').val();

  // Now try submitting the result
  $.post(Env.api_location, {
           type: 'submitTurn',
           game: Game.game,
           attackerIdx: Game.api.playerIdx,
           defenderIdx: Game.api.opponentIdx,
           dieSelectStatus: dieSelectStatus,
           attackType: attackType,
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
    ).fail(function() {
             Env.message = { 
               'type': 'error',
               'text': 'Internal error when calling submitTurn',
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

// Display common page footer data
Game.pageAddFooter = function() {
  Game.pageAddTimestampFooter();
  Game.pageAddActionLogFooter();
}

// Display a footer-style message with the last action timestamp
Game.pageAddTimestampFooter = function() {

  // Timestamp has a different meaning if the game is over
  if (Game.api.gameState == Game.GAME_STATE_END_GAME) {
    var timestamptext = 'Game completed at';
  } else {
    var timestamptext = 'Last action time';
  }

  Game.page.append($('<br>'));
  Game.page.append($('<div>', {
                      'text': timestamptext + ': ' + Game.api.timestamp,
                     }));
  return true;
}

// Display recent game data from the action log at the foot of the page
Game.pageAddActionLogFooter = function() {
  if (Game.api.actionLog.length > 0) {
    var logdiv = $('<div>');
    logdiv.append($('<p>', {'text': 'Recent game activity', }));
    var logtable = $('<table>');
    $.each(Game.api.actionLog, function(logindex, logentry) {
      var logrow = $('<tr>');
      logrow.append($('<td>', {
        'class': 'left', 
        'nowrap': 'nowrap', 
        'text': '(' + logentry.timestamp + ')', }));
      logrow.append($('<td>', {
        'class': 'left', 
        'text': logentry.message, }));
      logtable.append(logrow);
    });
    logdiv.append(logtable);

    Game.page.append($('<hr>'));
    Game.page.append(logdiv);
  }
}


// Generate and return a two-column table of the dice in each player's recipe
Game.dieRecipeTable = function() {

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
    dierow.append(
      Game.dieTableEntry(i, Game.api.player.nDie,
                         Game.api.player.dieRecipeArray,
                         Game.api.player.sidesArray));
    dierow.append(
      Game.dieTableEntry(i, Game.api.opponent.nDie,
                         Game.api.opponent.dieRecipeArray,
                         Game.api.opponent.sidesArray));
    dietable.append(dierow);
  }
  return dietable;
}

Game.dieTableEntry = function(i, nDie, dieRecipeArray, dieSidesArray) {
  if (i < nDie) {
    var dieval = dieRecipeArray[i];
    var diesides = dieSidesArray[i];
    if ((diesides != null) &&
        (dieval.indexOf('(' + diesides + ')') == -1)) {
      dieval += '=' + diesides;
    }
    return $('<td>', {'text': dieval, });
  }
    return $('<td>', {});
}

// Display each player's dice in "battle" layout
Game.pageAddDieBattleTable = function(clickable) {

  Game.pageAddGamePlayerStatus('player', false, true);
  Game.pageAddGamePlayerDice('player', clickable);
  Game.page.append($('<br>'));
  Game.pageAddGamePlayerDice('opponent', clickable);
  Game.pageAddGamePlayerStatus('opponent', true, true);
  return true;
}

// Add a brief mid-game status listing for the requested player
Game.pageAddGamePlayerStatus = function(player, reversed, game_active) {

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
            "/" + Game.api[player].gameScoreDict['D'] +
            " (" + Game.api.maxWins + ")", }));

  // Round score, only applicable in active games
  if (game_active) {
    var roundScoreDiv = $('<div>');
    roundScoreDiv.append($('<span>', {
      'text': "Score: " + Game.api[player].roundScore, }));
  }

  // Order the elements depending on the "reversed" flag
  if (reversed == true) {
    if (game_active) {
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
    }
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
                    'style':
                      'background-image: url(images/Circle.png);' +
                      'height:70px;width:70px;background-size:100%',
                   });
    dieDiv.append($('<span>', {
                      'class': 'die_overlay',
                      'text': Game.api[player].valueArray[i],
                    }));
    dieDiv.append($('<br>'));

    // If the recipe doesn't contain (sides), assume there are swing
    // dice in the recipe, so we need to specify the current number
    // of sides
    var dieSides =  '(' + Game.api[player].sidesArray[i] + ')';
    var dieRecipeText = Game.api[player].dieRecipeArray[i];
    if (dieRecipeText.indexOf(dieSides) === -1) {
      dieRecipeText = dieRecipeText.replace(
                        ')',
                        '=' + Game.api[player].sidesArray[i] + ')');
    }
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
}

// Show the winner of a completed game
Game.pageAddGameWinner = function() {

  var playerWins = Game.api.player.gameScoreDict['W'];
  var opponentWins = Game.api.opponent.gameScoreDict['W'];
  if (playerWins > opponentWins) {
    var winnerName = Game.api.player.playerName;
  } else if (playerWins < opponentWins) {
    var winnerName = Game.api.opponent.playerName;
  } else {
    var winnerName = false;
  }
  if (winnerName) {
    var winnerText = winnerName + ' won!';
  } else {
    var winnerText = 'TIE';
  }

  var winnerDiv = $('<div>');
  winnerDiv.append($('<span>', {
                       'id': 'winner_name',
                       'text': winnerText, }));
  Game.page.append(winnerDiv);
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
