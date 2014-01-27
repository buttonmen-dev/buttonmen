// namespace for this "module"
var Game = {
  'activity': {},
};

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
//   requested game.  It clobbers Api.game.  If successful, it calls
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

// Redraw the page after a previous action succeeded: to do this,
// clear all activity variables set by the previous invocation
Game.redrawGamePageSuccess = function() {
  Game.activity = {};
  Game.showGamePage();
};

// Redraw the page after a previous action failed: to do this,
// retain activity variables where it makes sense to do so
Game.redrawGamePageFailure = function() {
  Game.showGamePage();
};

// the current game should be provided as a GET parameter to the page
Game.getCurrentGame = function(callbackfunc) {
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

  Api.getGameData(Game.game, callbackfunc);
};

// Assemble and display the game portion of the page
Game.showStatePage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  // Figure out what to do next based on the game state
  if (Api.game.load_status == 'ok') {
    if (Api.game.gameState == Game.GAME_STATE_SPECIFY_DICE) {
      if (Api.game.isParticipant) {
        if (Api.game.player.waitingOnAction) {
          Game.actionChooseSwingActive();
        } else {
          Game.actionChooseSwingInactive();
        }
      } else {
        Game.actionChooseSwingNonplayer();
      }
    } else if (Api.game.gameState == Game.GAME_STATE_REACT_TO_INITIATIVE) {
      if (Api.game.isParticipant) {
        if (Api.game.player.waitingOnAction) {
          Game.actionReactToInitiativeActive();
        } else {
          Game.actionReactToInitiativeInactive();
        }
      } else {
        Game.actionReactToInitiativeNonplayer();
      }
    } else if (Api.game.gameState == Game.GAME_STATE_START_TURN) {
      if (Api.game.isParticipant) {
        if (Api.game.player.waitingOnAction) {
          Game.actionPlayTurnActive();
        } else {
          Game.actionPlayTurnInactive();
        }
      } else {
        Game.actionPlayTurnNonplayer();
      }
    } else if (Api.game.gameState == Game.GAME_STATE_END_GAME) {
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

// What actions can this player take during the react to initiative phase
Game.parseValidInitiativeActions = function() {
  Api.game.player.initiativeActions = {};
  if (Api.game.gameState == Game.GAME_STATE_REACT_TO_INITIATIVE) {
    var focus = {};
    var chance = {};
    var hasFocus = false;
    var hasChance = false;

    $.each(Api.game.player.dieRecipeArray, function(i) {
      var tdvals = Game.dieValidTurndownValues(
        Api.game.player.dieRecipeArray[i],
        Api.game.player.valueArray[i]);
      if (tdvals.length > 0) {
        focus[i] = tdvals;
        hasFocus = true;
      }

      if (Game.dieCanRerollForInitiative(Api.game.player.dieRecipeArray[i])) {
        chance[i] = true;
        hasChance = true;
      }
    });

    if (hasFocus) {
      Api.game.player.initiativeActions.focus = focus;
    }
    if (hasChance) {
      Api.game.player.initiativeActions.chance = chance;
    }
    Api.game.player.initiativeActions.decline = true;
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
    Api.game.player.swingRequestArray,
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

Game.actionChooseSwingNonplayer = function() {
  Game.page = $('<div>');

  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to set swing dice (you are not in this game)'
  );

  var dietable = Game.dieRecipeTable(false);
  Game.page.append(dietable);
  Game.page.append($('<br>'));

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
    Api.game.player.initiativeActions,
    function(typename) {
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

Game.actionReactToInitiativeNonplayer = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to gain initiative using die skills (you are not in this game)'
  );

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(true, false);

  Game.page.append(dietable);

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
  $.each(Api.game.validAttackTypeArray, function(typename, typevalue) {
    var typetext;
    if (typename == 'Pass') {
      typetext = typename;
    } else {
      typetext = typename + ' Attack';
    }
    var attacktypeopts = {
      'value': typevalue,
      'label': typename,
      'text': typetext,
    };
    if (('attackType' in Game.activity) &&
        (Game.activity.attackType == typename)) {
      attacktypeopts.selected = 'selected';
    }
    attacktypeselect.append($('<option>', attacktypeopts));
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

Game.actionPlayTurnNonplayer = function() {
  Game.page = $('<div>');

  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to attack (you are not in this game)'
  );

  Game.page.append($('<br>'));
  Game.pageAddDieBattleTable(false);
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
  $.each(Api.game.player.swingRequestArray, function(letter) {
    var value = $('#swing_' + letter).val();
    if ($.isNumeric(value)) {
      swingValueArray[letter] = value;
    } else {
      textFieldsFilled = false;
    }
  });

  if (textFieldsFilled) {
    Api.apiFormPost(
      {
        type: 'submitSwingValues',
        game: Game.game,
        swingValueArray: swingValueArray,
        roundNumber: Api.game.roundNumber,
        timestamp: Api.game.timestamp,
      },
      { 'ok': { 'type': 'fixed', 'text': 'Successfully set swing values', },
        'notok': {'type': 'server', },
      },
      Game.showGamePage,
      Game.showGamePage
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

  // valid action, nothing special to do, but validate selections just in case
  case 'decline':
    if ('focus' in Api.game.player.initiativeActions) {
      $.each(Api.game.player.initiativeActions.focus, function(i) {
        var value = $('#init_react_' + i).val();
        if (value != Api.game.player.valueArray[i]) {
          error = 'Chose not to react to initiative, but modified a die value';
          formValid = false;
        }
      });
    }
    if ('chance' in Api.game.player.initiativeActions) {
      $.each(Api.game.player.initiativeActions.chance, function(i) {
        var value = $('#init_react_' + i).val();
        if (value != Api.game.player.valueArray[i]) {
          error =
            'Chose not to react to initiative, but selected dice to reroll';
          formValid = false;
        }
      });
    }
    break;

  case 'focus':
    if ('focus' in Api.game.player.initiativeActions) {
      $.each(Api.game.player.initiativeActions.focus, function(i, vals) {
        var value = parseInt($('#init_react_' + i).val(), 10);
        if (value != Api.game.player.valueArray[i]) {
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
    if ('chance' in Api.game.player.initiativeActions) {
      $.each(Api.game.player.initiativeActions.chance, function(i) {
        var value = $('#init_react_' + i).val();
        if (value != Api.game.player.valueArray[i]) {
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
    Game.activity.initiativeReactType = action;
    Api.apiFormPost(
      {
        type: 'reactToInitiative',
        game: Game.game,
        roundNumber: Api.game.roundNumber,
        timestamp: Api.game.timestamp,
        action: action,
        dieIdxArray: dieIdxArray,
        dieValueArray: dieValueArray,
      },
      { 'ok':
        {
          'type': 'function',
          'msgfunc': Game.reactToInitiativeSuccessMsg,
        },
        'notok': { 'type': 'server', },
      },
      Game.showGamePage,
      Game.showGamePage
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': error,
    };
    Game.showGamePage();
  }
};

Game.reactToInitiativeSuccessMsg = function(message, data) {
  switch (Game.activity.initiativeReactType) {
  case 'chance':
    if (data.gainedInitiative) {
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
};

// Form submission action for playing a turn
Game.formPlayTurnActive = function() {

  // Initialize the array of die select statuses to all false, then
  // turn on the dice which have been selected
  Game.activity.dieSelectStatus = {};
  var i;
  for (i = 0 ; i < Api.game.player.nDie; i++) {
    Game.activity.dieSelectStatus[Game.dieIndexId('player', i)] = false;
  }
  for (i = 0 ; i < Api.game.opponent.nDie; i++) {
    Game.activity.dieSelectStatus[Game.dieIndexId('opponent', i)] = false;
  }
  $('div.selected').each(function(index, element) {
    Game.activity.dieSelectStatus[$(element).attr('id')] = true;
  });

  // Get the specified attack type
  Game.activity.attackType = $('#attack_type_select').val();

  // Store the game chat in recent activity
  Game.activity.chat = $('#game_chat').val();

  // Now try submitting the result
  Api.apiFormPost(
    {
      type: 'submitTurn',
      game: Game.game,
      attackerIdx: Api.game.playerIdx,
      defenderIdx: Api.game.opponentIdx,
      dieSelectStatus: Game.activity.dieSelectStatus,
      attackType: Game.activity.attackType,
      chat: Game.activity.chat,
      roundNumber: Api.game.roundNumber,
      timestamp: Api.game.timestamp,
    },
    { 'ok': { 'type': 'server', }, 'notok': { 'type': 'server', }, },
    Game.redrawGamePageSuccess,
    Game.redrawGamePageFailure
  );
};

////////////////////////////////////////////////////////////////////////
// Page layout helper routines

// Display header information about the game
Game.pageAddGameHeader = function(action_desc) {
  Game.page.append($('<div>', {'id': 'game_id',
                               'text': 'Game #' + Api.game.gameId, }));
  Game.page.append($('<div>', {'id': 'round_number',
                               'text': 'Round #' + Api.game.roundNumber, }));
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
  if (Api.game.gameState == Game.GAME_STATE_END_GAME) {
    timestamptext = 'Game completed at';
  } else {
    timestamptext = 'Last action time';
  }

  Game.page.append($('<br>'));
  Game.page.append($('<div>', {
    'text': timestamptext + ': ' + Api.game.timestamp,
  }));
  return true;
};

// Display recent game data from the action log at the foot of the page
Game.pageAddLogFooter = function() {
  if ((Api.game.chatLog.length > 0) || (Api.game.actionLog.length > 0)) {
    var logdiv = $('<div>');
    var logtable = $('<table>');
    var logrow = $('<tr>');

    if (Api.game.actionLog.length > 0) {
      var actiontd = $('<td>', {'class': 'logtable', });
      actiontd.append($('<p>', {'text': 'Recent game activity', }));
      var actiontable = $('<table>', {'border': 'on', });
      $.each(Api.game.actionLog, function(logindex, logentry) {
        var nameclass;
        if (logentry.message.indexOf(Api.game.player.playerName + ' ') === 0) {
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

    if (Api.game.chatLog.length > 0) {
      var chattd = $('<td>', {'class': 'logtable', });
      chattd.append($('<p>', {'text': 'Recent game chat', }));
      var chattable = $('<table>', {'border': 'on', });
      $.each(Api.game.chatLog, function(logindex, logentry) {
        var chatrow = $('<tr>');
        var nameclass;
        if (logentry.player == Api.game.player.playerName) {
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

  // variables only needed in the react_initiative case
  var focusLTable;
  var focusRTable;

  var dietable = $('<table>', {'id': 'die_recipe_table', });
  dietable.append(Game.playerOpponentHeaderRow('Player', 'playerName'));
  dietable.append(Game.playerOpponentHeaderRow('Button', 'buttonName'));
  dietable.append(Game.playerOpponentHeaderRow('', 'gameScoreStr'));

  if (react_initiative) {
    var focusHeaderLRow = $('<tr>');
    focusHeaderLRow.append($('<th>', { 'text': 'Recipe' }));
    focusHeaderLRow.append($('<th>', { 'text': 'Value' }));
    var focusHeaderRRow = focusHeaderLRow.clone();

    focusLTable = $('<table>');
    focusRTable = $('<table>');

    focusLTable.append(focusHeaderLRow);
    focusRTable.append(focusHeaderRRow);
  }

  var maxDice = Math.max(Api.game.player.nDie, Api.game.opponent.nDie);
  for (var i = 0; i < maxDice; i++) {
    var playerEnt = Game.dieTableEntry(
      i, Api.game.player.nDie,
      Api.game.player.dieRecipeArray,
      Api.game.player.sidesArray);
    var opponentEnt = Game.dieTableEntry(
      i, Api.game.opponent.nDie,
      Api.game.opponent.dieRecipeArray,
      Api.game.opponent.sidesArray);
    if (react_initiative) {
      var dieLRow = $('<tr>');
      var dieRRow = $('<tr>');
      dieLRow.append(playerEnt);
      var initopts = [];
      if (active) {
        if (('focus' in Api.game.player.initiativeActions) &&
            (i in Api.game.player.initiativeActions.focus)) {
          initopts = Api.game.player.initiativeActions.focus[i].concat();
        }
        if (('chance' in Api.game.player.initiativeActions) &&
            (i in Api.game.player.initiativeActions.chance)) {
          initopts.push('reroll');
        }
      }
      if ((active) && (initopts.length > 0)) {
        dieLRow.append(
          Game.dieValueSelectTd('init_react_' + i, initopts,
                                Api.game.player.valueArray[i]));
      } else {
        dieLRow.append($('<td>', { 'text': Api.game.player.valueArray[i] }));
      }
      dieRRow.append(opponentEnt);
      dieRRow.append($('<td>', { 'text': Api.game.opponent.valueArray[i] }));
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
    'text': 'Player: ' + Api.game[player].playerName,
  }));

  // Button name
  var buttonDiv = $('<div>');
  buttonDiv.append($('<span>', {
    'text': 'Button: ' + Api.game[player].buttonName,
  }));

  // Game score
  var gameScoreDiv = $('<div>');
  gameScoreDiv.append($('<span>', { 'text': Api.game[player].gameScoreStr, }));

  var roundScoreDiv;
  var capturedDiceDiv;
  if (game_active) {
    // Round score, only applicable in active games
    roundScoreDiv = $('<div>');
    roundScoreDiv.append($('<span>', {
      'text': 'Score: ' + Api.game[player].roundScore,
    }));

    // Dice captured this round, only applicable in active games
    var capturedDieText;
    if (Api.game[player].nCapturedDie > 0) {
      var capturedDieDescs = [];
      $.each(Api.game[player].capturedRecipeArray, function(idx, recipe) {
        capturedDieDescs.push(
          Game.dieRecipeText(recipe, Api.game[player].capturedSidesArray[idx]));
      });
      capturedDieText = capturedDieDescs.join(', ');
    } else {
      capturedDieText = 'none';
    }
    capturedDiceDiv = $('<div>');
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
Game.pageAddGamePlayerDice = function(player, player_active) {
  var i = 0;
  while (i < Api.game[player].nDie) {

    // Find out whether this die is clickable: it is if the player
    // is active and this particular die is not disabled
    var clickable;
    if (player_active) {
      if (('disabled' in Api.game[player].diePropertiesArray[i]) &&
          Api.game[player].diePropertiesArray[i].disabled) {
        clickable = false;
      } else {
        clickable = true;
      }
    } else {
      clickable = false;
    }

    var dieDiv;
    var dieIndex = Game.dieIndexId(player, i);
    var divOpts = {
      'id': dieIndex,
      'style':
        'background-image: url(images/Circle.png);' +
        'height:70px;width:70px;background-size:100%',
    };
    if (clickable) {
      if (('dieSelectStatus' in Game.activity) &&
          (dieIndex in Game.activity.dieSelectStatus) &&
          (Game.activity.dieSelectStatus[dieIndex])) {
        divOpts.class = 'die_img selected';
      } else {
        divOpts.class = 'die_img unselected';
      }
      dieDiv = $('<div>', divOpts);
      dieDiv.click(Game.dieBorderToggleHandler);
    } else {
      divOpts.class = 'die_img die_greyed';
      dieDiv = $('<div>', divOpts);
    }
    dieDiv.append($('<span>', {
      'class': 'die_overlay',
      'text': Api.game[player].valueArray[i],
    }));
    dieDiv.append($('<br>'));

    var dieRecipeText = Game.dieRecipeText(
      Api.game[player].dieRecipeArray[i],
      Api.game[player].sidesArray[i]);
    dieDiv.append($('<span>', {
      'class': 'die_recipe',
      'text': dieRecipeText,
    }));
    Game.page.append(dieDiv);
    i += 1;
  }
};

// Show the winner of a completed game
Game.pageAddGameWinner = function() {

  var playerWins = Api.game.player.gameScoreDict.W;
  var opponentWins = Api.game.opponent.gameScoreDict.W;
  var winnerName;
  if (playerWins > opponentWins) {
    winnerName = Api.game.player.playerName;
  } else if (playerWins < opponentWins) {
    winnerName = Api.game.opponent.playerName;
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
    playerIdx = Api.game.playerIdx;
  } else {
    playerIdx = Api.game.opponentIdx;
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
    'text': prefix + Api.game.player[field],
  }));
  headerrow.append($('<th>', {
    'text': prefix + Api.game.opponent[field],
  }));
  return headerrow;
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
  var chatarea = $('<textarea>', {
    'id': 'game_chat',
    'rows': '3',
    'cols': '50',
    'maxlength': '500',
  });

  // Add previous chat contents from a rejected turn submission if any
  if ('chat' in Game.activity) {
    chatarea.append(Game.activity.chat);
  }
  chatrow.append(chatarea);
  chattable.append(chatrow);
  return chattable;
};

// Return a friendly string with the names of the players for whom
// the game is currently waiting
Game.waitingOnPlayerNames = function() {

  var waitingPlayers = [];
  if (Api.game.player.waitingOnAction) {
    waitingPlayers.push(Api.game.player.playerName);
  }
  if (Api.game.opponent.waitingOnAction) {
    waitingPlayers.push(Api.game.opponent.playerName);
  }

  return (waitingPlayers.join(' and '));
};
