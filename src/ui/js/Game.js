// namespace for this "module"
var Game = {
  'activity': {},
};

Game.bodyDivId = 'game_page';

// Game states must match those reported by the API
Game.GAME_STATE_START_GAME = 'START_GAME';
Game.GAME_STATE_APPLY_HANDICAPS = 'APPLY_HANDICAPS';
Game.GAME_STATE_CHOOSE_JOIN_GAME = 'CHOOSE_JOIN_GAME';
Game.GAME_STATE_SPECIFY_RECIPES = 'SPECIFY_RECIPES';
Game.GAME_STATE_LOAD_DICE_INTO_BUTTONS = 'LOAD_DICE_INTO_BUTTONS';
Game.GAME_STATE_ADD_AVAILABLE_DICE_TO_GAME = 'ADD_AVAILABLE_DICE_TO_GAME';
Game.GAME_STATE_CHOOSE_AUXILIARY_DICE = 'CHOOSE_AUXILIARY_DICE';
Game.GAME_STATE_CHOOSE_RESERVE_DICE = 'CHOOSE_RESERVE_DICE';
Game.GAME_STATE_SPECIFY_DICE = 'SPECIFY_DICE';
Game.GAME_STATE_DETERMINE_INITIATIVE = 'DETERMINE_INITIATIVE';
Game.GAME_STATE_REACT_TO_INITIATIVE = 'REACT_TO_INITIATIVE';
Game.GAME_STATE_START_ROUND = 'START_ROUND';
Game.GAME_STATE_START_TURN = 'START_TURN';
Game.GAME_STATE_ADJUST_FIRE_DICE = 'ADJUST_FIRE_DICE';
Game.GAME_STATE_COMMIT_ATTACK = 'COMMIT_ATTACK';
Game.GAME_STATE_CHOOSE_TURBO_SWING = 'CHOOSE_TURBO_SWING';
Game.GAME_STATE_END_TURN = 'END_TURN';
Game.GAME_STATE_END_ROUND = 'END_ROUND';
Game.GAME_STATE_END_GAME = 'END_GAME';

Game.GAME_STATE_CANCELLED = 'CANCELLED';

// Convenience HTML used in the mat layout to break text
Game.SPACE_BULLET = ' &nbsp;&bull;&nbsp; ';

// Default number of action and chat log entries to display
Game.logEntryLimit = 10;

// Maximum number of characters permitted in a given chat message
Game.GAME_CHAT_MAX_LENGTH = 2000;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Game.showLoggedInPage() is the landing function.  Always call this first
// * Game.getCurrentGame() asks the API for information about the
//   requested game.  It clobbers Api.game.  If successful, it calls
// * Game.showStatePage() determines what action to take next based on
//   the received data from getCurrentGame().  It calls one of several
//   functions, Game.action<SomeAction>(), and then calls Login.arrangePage()
// * each Game.action<SomeAction>() function must set Game.page and
//   Game.form
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Game.showLoggedInPage = function() {
  if (Game.logEntryLimit && Env.getCookieCompactMode()) {
    Game.logEntryLimit = 5;
  }

  // Find the current game, and invoke that with the "parse game state"
  // callback
  Game.getCurrentGame(Game.showStatePage);
};

// Redraw the page after a previous action succeeded: to do this,
// clear all activity variables set by the previous invocation
Game.redrawGamePageSuccess = function() {
  Game.activity = {};
  Game.showLoggedInPage();
};

// Redraw the page after a previous action failed: to do this,
// retain activity variables where it makes sense to do so
Game.redrawGamePageFailure = function() {
  Game.showLoggedInPage();
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

  Env.callAsyncInParallel(
    [
      Api.getPendingGameCount,
      { 'func': Api.getGameData, 'args': [ Game.game, Game.logEntryLimit ] },
    ], callbackfunc);
};

// Assemble and display the game portion of the page
Game.showStatePage = function() {
  var includeFooter = true;
  var isChatHidden = false;

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  // Figure out what to do next based on the game state
  if (Api.game.load_status == 'ok') {
    // Set colors for use in game
    Game.color = {
      'player': Api.game.player.playerColor,
      'opponent': Api.game.opponent.playerColor,
      'noone': 'white',
    };

    if (Api.game.gameState == Game.GAME_STATE_CHOOSE_JOIN_GAME) {
      if (Api.game.isParticipant) {
        if (Api.game.player.waitingOnAction) {
          Game.actionChooseJoinGameActive();
        } else {
          Game.actionChooseJoinGameInactive();
        }
      } else {
        Game.actionChooseJoinGameNonplayer();
      }
    } else if (Api.game.gameState == Game.GAME_STATE_CANCELLED) {
      Game.actionShowCancelledGame();
    } else if (Api.game.gameState == Game.GAME_STATE_SPECIFY_DICE) {
      if (Api.game.isParticipant) {
        if (Api.game.player.waitingOnAction) {
          Game.actionSpecifyDiceActive();
        } else {
          Game.actionSpecifyDiceInactive();
        }
      } else {
        Game.actionSpecifyDiceNonplayer();
      }
    } else if (Api.game.gameState == Game.GAME_STATE_CHOOSE_AUXILIARY_DICE) {
      if (Api.game.isParticipant) {
        if (Api.game.player.waitingOnAction) {
          Game.actionChooseAuxiliaryDiceActive();
        } else {
          Game.actionChooseAuxiliaryDiceInactive();
        }
      } else {
        Game.actionChooseAuxiliaryDiceNonplayer();
      }
    } else if (Api.game.gameState == Game.GAME_STATE_CHOOSE_RESERVE_DICE) {
      if (Api.game.isParticipant) {
        if (Api.game.player.waitingOnAction) {
          Game.actionChooseReserveDiceActive();
        } else {
          Game.actionChooseReserveDiceInactive();
        }
      } else {
        Game.actionChooseReserveDiceNonplayer();
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
          isChatHidden = true;
        }
      } else {
        Game.actionPlayTurnNonplayer();
      }
    } else if (Api.game.gameState == Game.GAME_STATE_ADJUST_FIRE_DICE) {
      if (Api.game.isParticipant) {
        if (Api.game.player.waitingOnAction) {
          Game.actionAdjustFireDiceActive();
        } else {
          Game.actionAdjustFireDiceInactive();
        }
      } else {
        Game.actionAdjustFireDiceNonplayer();
      }
    } else if (Api.game.gameState == Game.GAME_STATE_END_GAME) {
      Game.actionShowFinishedGame();
    } else if (Api.game.gameState == Game.GAME_STATE_START_GAME) {
      Game.page =
        $('<p>', {'text': 'The game hasn\'t started yet.', });
      Game.form = null;
      includeFooter = false;
    } else {
      Game.page =
        $('<p>', {'text': 'Can\'t figure out what action to take next', });
      Game.form = null;
      includeFooter = false;
    }
  } else {
    // Game retrieval failed, so just layout the page with no contents
    // and whatever message was received while trying to load the game
    Game.page = null;
    Game.form = null;
    includeFooter = false;
  }

  if (includeFooter) {
    Game.pageAddFooter(isChatHidden);
  }

  // Now lay out the page
  Login.arrangePage(Game.page, Game.form, '#game_action_button');
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

    $.each(Api.game.player.activeDieArray, function(i, die) {
      var tdvals = Game.dieValidTurndownValues(die, Api.game.gameState);
      if (tdvals.length > 0) {
        focus[i] = tdvals;
        hasFocus = true;
      }

      if (Game.dieCanRerollForInitiative(die)) {
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

// What fire dice can the player adjust?
Game.parseValidFireOptions = function() {
  Api.game.player.fireOptions = {};
  if (Api.game.gameState == Game.GAME_STATE_ADJUST_FIRE_DICE) {
    $.each(Api.game.player.activeDieArray, function(i, die) {
      if ((die.skills.indexOf('Fire') >= 0) &&
          (die.properties.indexOf('IsAttacker') < 0)) {
        Api.game.player.fireOptions[i] =
          Game.dieValidTurndownValues(die, Api.game.gameState);
      }
    });
  }
};

// What reserve dice can the player choose
Game.parseValidReserveOptions = function() {
  Api.game.player.reserveOptions = {};
  if (Api.game.gameState == Game.GAME_STATE_CHOOSE_RESERVE_DICE) {

    $.each(Api.game.player.activeDieArray, function(i, die) {
      if (die.skills.indexOf('Reserve') >= 0) {
        Api.game.player.reserveOptions[i] = true;
      }
    });
  }
};

// What auxiliary dice will each player in this game get
Game.parseAuxiliaryDieOptions = function() {
  $.each(Api.game.player.activeDieArray, function(i, die) {
    if (die.skills.indexOf('Auxiliary') >= 0) {
      Api.game.player.auxiliaryDieIndex = i;
      Api.game.player.auxiliaryDieRecipe = die.recipe;
    }
  });
  $.each(Api.game.opponent.activeDieArray, function(i, die) {
    if (die.skills.indexOf('Auxiliary') >= 0) {
      Api.game.opponent.auxiliaryDieIndex = i;
      Api.game.opponent.auxiliaryDieRecipe = die.recipe;
    }
  });
};

////////////////////////////////////////////////////////////////////////
// Routines for each type of game action that could be taken

Game.actionChooseJoinGameActive = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Your turn to decide whether to join the game');

  var dietable = Game.dieRecipeTable(false);
  Game.page.append(dietable);
  Game.page.append($('<br>'));
  Game.page.append(Game.buttonTableWithoutDice());
};

Game.buttonTableWithoutDice = function() {
  var buttonTable = $('<table>');
  var buttonTr = $('<tr>');

  var playerButtonTd = Game.buttonImageDisplay('player');
  var opponentButtonTd = Game.buttonImageDisplay('opponent');

  buttonTr.append(playerButtonTd);
  buttonTr.append(opponentButtonTd);
  buttonTable.append(buttonTr);
  return(buttonTable);
};

Game.actionChooseJoinGameInactive = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Opponent\'s turn to decide whether to join the game');

  var dietable = Game.dieRecipeTable(false);
  Game.page.append(dietable);
  Game.page.append($('<br>'));
  Game.page.append(Game.buttonTableWithoutDice());
};

Game.actionChooseJoinGameNonplayer = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to decide whether to join the game ' +
    '(you are not in this game)'
  );

  var dietable = Game.dieRecipeTable(false);
  Game.page.append(dietable);
  Game.page.append($('<br>'));
  Game.page.append(Game.buttonTableWithoutDice());
};

Game.actionShowCancelledGame = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader('This game has been cancelled');

  var dieEndgameTable = $('<table>');
  var dieEndgameTr = $('<tr>');

  var playerButtonTd = Game.buttonImageDisplay('player');
  var opponentButtonTd = Game.buttonImageDisplay('opponent');

  dieEndgameTr.append(playerButtonTd);
  dieEndgameTr.append(opponentButtonTd);
  dieEndgameTable.append(dieEndgameTr);
  Game.page.append(dieEndgameTable);
  Game.logEntryLimit = undefined;
};

// It is time to choose swing dice, and the current player has dice to choose
Game.actionSpecifyDiceActive = function() {

  // Function to invoke on button click
  Game.form = Game.formSpecifyDiceActive;

  Game.page = $('<div>');
  Game.pageAddGameHeader('Your turn to choose die sizes');

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(false);

  // Create a form for submitting die values
  var diespecifyform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
  });

  var diespecifytable = Game.swingRangeTable(
    Api.game.player.swingRequestArray,
    'die_specify_table',
    true,
    true
  );

  // Add option dice to table
  $.each(
    Api.game.player.optRequestArray,
    function(position, vals) {
      var optrow = $('<tr>', {});
      var opttext = Api.game.player.activeDieArray[position].recipe + ':';
      optrow.append($('<td>', { 'text': opttext, }));
      var optinput = $('<td>', {});
      var optselect = $('<select>', {
        'id': 'option_' + position,
        'name': 'option_' + position,
      });
      $.each(vals, function(idx) {
        optselect.append($('<option>', {
          'value': vals[idx],
          'label': vals[idx],
          'text': vals[idx],
        }));
      });
      optinput.append(optselect);
      optrow.append(optinput);
      var optprevtext = '';
      if (position in Api.game.player.prevOptValueArray) {
        optprevtext =
          '(was: ' + Api.game.player.prevOptValueArray[position] + ')';
      }
      optrow.append($('<td>', { 'text': optprevtext, }));
      diespecifytable.append(optrow);
    });

  diespecifyform.append(diespecifytable);
  diespecifyform.append($('<br>'));
  diespecifyform.append($('<button>', {
    'id': 'game_action_button',
    'text': 'Submit',
  }));

  // If the opponent has any swing dice to set, make a table for those
  var opponentswing = Game.swingRangeTable(
    Api.game.opponent.swingRequestArray,
    'opponent_swing',
    false,
    false
  );

  // Don't bother making a table for opponent's option dice, because
  // those possible values are shown in the recipe already

  // Add the swing die form to the left column of the die table
  var formtd = $('<td>', { 'class': 'chooseswing', });
  formtd.append($('<br>'));
  formtd.append(diespecifyform);
  var opponenttd = $('<td>', { 'class': 'chooseswing', });
  opponenttd.append($('<br>'));
  opponenttd.append(opponentswing);
  var formrow = $('<tr>', {});
  formrow.append(formtd);
  formrow.append(opponenttd);
  formrow.append($('<td>', {}));
  dietable.append(formrow);

  // Add the die table to the page
  Game.page.append(dietable);
};

Game.actionSpecifyDiceInactive = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader('Opponent\'s turn to choose die sizes');

  var dietable = Game.dieRecipeTable(false);
  Game.page.append(dietable);
};

Game.actionSpecifyDiceNonplayer = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');

  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to set swing dice (you are not in this game)'
  );

  var dietable = Game.dieRecipeTable(false);
  Game.page.append(dietable);
  Game.page.append($('<br>'));
};

Game.actionChooseAuxiliaryDiceActive = function() {

  // Function to invoke on button click
  Game.form = Game.formChooseAuxiliaryDiceActive;

  Game.parseAuxiliaryDieOptions();
  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Your turn to decide whether to use auxiliary dice');

  // Create a form for reacting to initiative
  var auxform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
  });

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(false, true);

  auxform.append(dietable);

  var swingrangetable = Game.swingRangeTable(
    Api.game.player.swingRequestArray,
    'swing_range_table',
    false,
    false
  );

  var opponentswing = Game.swingRangeTable(
    Api.game.opponent.swingRequestArray,
    'opponent_swing',
    false,
    false
  );

  // Add the swing die form to the left column of the die table
  var formtd = $('<td>', { 'class': 'showswing', });
  formtd.append($('<br>'));
  formtd.append(swingrangetable);
  var opponenttd = $('<td>', { 'class': 'showswing', });
  opponenttd.append($('<br>'));
  opponenttd.append(opponentswing);
  var formrow = $('<tr>', {});
  formrow.append(formtd);
  formrow.append(opponenttd);
  formrow.append($('<td>', {}));
  dietable.append(formrow);

  auxform.append($('<br>'));

  var yestext = 'Use auxiliary dice this game: keep ' +
    Api.game.player.auxiliaryDieRecipe + ' in your button and ' +
    Api.game.opponent.auxiliaryDieRecipe + ' in your opponent\'s button';
  var notext = 'Don\'t use auxiliary dice this game';

  var auxselect = $('<select>', {
    'id': 'auxiliary_die_select',
    'name': 'auxiliary_die_select',
  });
  auxselect.append($('<option>', {
    'value': 'add',
    'label': yestext,
    'text': yestext,
    'selected': 'selected',
  }));
  auxselect.append($('<option>', {
    'value': 'decline',
    'label': notext,
    'text': notext,
  }));
  auxform.append(auxselect);

  auxform.append(
    $('<button>', {
      'id': 'game_action_button',
      'text': 'Submit',
    }));

  // Add the form to the page
  Game.page.append(auxform);
};

Game.actionChooseAuxiliaryDiceInactive = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Opponent\'s turn to decide whether to use auxiliary dice');

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(false, false);

  // Add the form to the page
  Game.page.append(dietable);
  Game.page.append($('<br>'));

  Game.page.append($('<p>', {'text':
    'Please wait for your opponent to decide whether to use auxiliary dice' }));
};

Game.actionChooseAuxiliaryDiceNonplayer = function() {

  // nothing to do on button click
  Game.form = null,

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to decide whether to use auxiliary dice (you are not in this game)'
  );

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(false, false);

  Game.page.append(dietable);
};

Game.actionChooseReserveDiceActive = function() {

  // Function to invoke on button click
  Game.form = Game.formChooseReserveDiceActive;

  Game.parseValidReserveOptions();
  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Your turn to choose reserve dice');

  // Create a form for choosing reserve dice
  var reserveform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
  });

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable('choose_reserve', true);

  reserveform.append(dietable);

  var swingrangetable = Game.swingRangeTable(
    Api.game.player.swingRequestArray,
    'swing_range_table',
    false,
    true
  );

  // Add the swing die form to the left column of the die table
  var formtd = $('<td>', { 'class': 'showswing', });
  formtd.append($('<br>'));
  formtd.append(swingrangetable);
  var formrow = $('<tr>', {});
  formrow.append(formtd);
  formrow.append($('<td>', {}));
  dietable.append(formrow);

  reserveform.append($('<br>'));

  var yestext = 'Add one reserve die to button';
  var notext = 'Don\'t add a reserve die this round';

  var reserveselect = $('<select>', {
    'id': 'reserve_select',
    'name': 'reserve_select',
  });
  reserveselect.append($('<option>', {
    'value': 'add',
    'label': yestext,
    'text': yestext,
    'selected': 'selected',
  }));
  reserveselect.append($('<option>', {
    'value': 'decline',
    'label': notext,
    'text': notext,
  }));
  reserveform.append(reserveselect);

  reserveform.append(
    $('<button>', {
      'id': 'game_action_button',
      'text': 'Submit',
    }));

  // Add the form to the page
  Game.page.append(reserveform);
};

Game.actionChooseReserveDiceInactive = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Opponent\'s turn to choose reserve dice');

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(false, false);

  // Add the table to the page
  Game.page.append(dietable);
  Game.page.append($('<br>'));

  Game.page.append($('<p>', {'text':
    'Please wait for your opponent to decide whether to add reserve dice' }));
};

Game.actionChooseReserveDiceNonplayer = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to choose reserve dice (you are not in this game)'
  );

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(false, false);

  // Add the table to the page
  Game.page.append(dietable);
};

Game.actionReactToInitiativeActive = function() {

  // Function to invoke on button click
  Game.form = Game.formReactToInitiativeActive;

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
  var dietable = Game.dieRecipeTable('react_to_initiative', true);

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
      var reacttypeopts = {
        'value': typename,
        'label': typetext,
        'text': typetext,
      };
      if (('initiativeReactType' in Game.activity) &&
          (Game.activity.initiativeReactType == typename)) {
        reacttypeopts.selected = 'selected';
      }
      reacttypeselect.append($('<option>', reacttypeopts));
    });
  reactform.append(reacttypeselect);

  reactform.append(
    $('<button>', {
      'id': 'game_action_button',
      'text': 'Submit',
    }));

  // Add the form to the page
  Game.page.append(reactform);
};

Game.actionReactToInitiativeInactive = function() {

  // nothing to do on button click
  Game.form = null,

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Opponent\'s turn to try to gain initiative using die skills');

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable('react_to_initiative', false);

  Game.page.append(dietable);
};

Game.actionReactToInitiativeNonplayer = function() {

  // nothing to do on button click
  Game.form = null,

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to gain initiative using die skills (you are not in this game)'
  );

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable('react_to_initiative', false);

  Game.page.append(dietable);
};

Game.actionPlayTurnActive = function() {

  // Function to invoke on button click
  Game.form = Game.formPlayTurnActive;

  Game.page = $('<div>');
  if (Env.getCookieCompactMode()) {
    Game.page.addClass('compactMode');
  }
  Game.pageAddGameHeader('Your turn to attack');
  Game.pageAddDieBattleTable(true);
  Game.page.append($('<br>'));

  var attackdiv = $('<div>');
  attackdiv.append(Game.chatBox());
  var attackform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
  });

  // Surrender is a valid attack type, so add it at the end of the
  // list of options
  Api.game.validAttackTypeArray.push('');
  Api.game.validAttackTypeArray.push('Surrender');

  // configure the attack type select object
  var attacktypeselect = $('<select>', {
    'id': 'attack_type_select',
    'name': 'attack_type_select',
  });

  // install a keyboard handler so that hitting "return" while on
  // the attack type select submits the form (don't install any
  // mouse click or space bar behavior)
  Env.addClickKeyboardHandlers(
    attacktypeselect,
    null,
    null,
    Game.form
  );

  var attacksExist = false;

  for (var i = 0; i < Api.game.validAttackTypeArray.length; i++) {
    var attacktype = Api.game.validAttackTypeArray[i];
    var typetext;
    if ((attacktype == 'Pass') || (attacktype === '')) {
      typetext = attacktype;
    } else if (attacktype == 'Surrender') {
      typetext = 'SURRENDER!?';
    } else {
      typetext = attacktype + ' Attack';
      attacksExist = true;
    }
    var attacktypeopts = {
      'value': attacktype,
      'label': typetext,
      'text': typetext,
    };
    if (('attackType' in Game.activity) &&
        (Game.activity.attackType == attacktype)) {
      attacktypeopts.selected = 'selected';
    }
    attacktypeselect.append($('<option>', attacktypeopts));
  }

  if (attacksExist) {
    var defaultAttackOptions = {
      'value': 'Default',
      'label': 'Default Attack',
      'text': 'Default Attack',
    };
    if (Game.activity.attackType == 'Default' || !Game.activity.attackType) {
      defaultAttackOptions.selected = 'selected';
    }
    attacktypeselect.prepend($('<option>', defaultAttackOptions));
  }

  attackform.append(attacktypeselect);

  attackform.append($('<button>', {
    'id': 'game_action_button',
    'text': 'Beat People UP!',
  }));
  attackdiv.append(attackform);
  Game.page.append(attackdiv);
};

Game.actionPlayTurnInactive = function() {

  // Function to invoke on button click
  Game.form = Game.formPlayTurnInactive;

  Game.page = $('<div>');
  if (Env.getCookieCompactMode()) {
    Game.page.addClass('compactMode');
  }
  Game.pageAddGameHeader('Opponent\'s turn to attack');
  Game.pageAddDieBattleTable(false);
  Game.page.append($('<br>'));

  if (Api.game.chatEditable && !Game.activity.chat) {
    Game.activity.chat = Api.game.chatLog[0].message;
  }
  var chatdiv = $('<div>');
  chatdiv.append(Game.chatBox(true));
  var chatform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
    'class': 'hiddenChatForm',
  });
  chatform.append($('<button>', {
    'id': 'game_action_button',
    'text': 'Change game message',
  }));
  chatdiv.append(chatform);
  Game.page.append(chatdiv);
};

Game.actionPlayTurnNonplayer = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  if (Env.getCookieCompactMode()) {
    Game.page.addClass('compactMode');
  }

  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to attack (you are not in this game)'
  );

  Game.page.append($('<br>'));
  Game.pageAddDieBattleTable(false);
};

Game.actionAdjustFireDiceActive = function() {

  // Function to invoke on button click
  Game.form = Game.formAdjustFireDiceActive;

  Game.parseValidFireOptions();
  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Your turn to complete an attack by adjusting fire dice');

  var attackerSum = 0;
  var attackerDiffFromMax = 0;
  $.each(Api.game.player.activeDieArray, function(i, die) {
    if (die.properties.indexOf('IsAttacker') >= 0) {
      attackerDiffFromMax += die.sides - die.value;
      attackerSum += die.value;
    }
  });

  var defenderSum = 0;
  $.each(Api.game.opponent.activeDieArray, function(i, die) {
    if (die.properties.indexOf('IsAttackTarget') >= 0) {
      defenderSum += die.value;
    }
  });

  var fireMessage = '';
  var attackType = Api.game.validAttackTypeArray[0];
  var exactFiringAmount = defenderSum - attackerSum;

  fireMessage += 'Turn down Fire dice by a total of ';

  if (('Power' == attackType) &&
    (attackerDiffFromMax > exactFiringAmount)) {
    fireMessage += 'between ' + Math.max(0, exactFiringAmount) +
                   ' and ' + attackerDiffFromMax;
  } else {
    fireMessage += exactFiringAmount;
  }

  fireMessage += ' to complete your ' + attackType + ' attack.';

  Game.page.append($('<div>', {
    'text': fireMessage,
  }));

  // Create a form for adjusting fire dice
  var fireform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
  });

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable('adjust_fire_dice', true);

  fireform.append(dietable);
  fireform.append($('<br>'));

  var fireactionselect = $('<select>', {
    'id': 'fire_action_select',
    'name': 'fire_action_select',
  });

  var allows_zero_turndown = (exactFiringAmount <= 0);

  var fireoptions = {};
  if (allows_zero_turndown) {
    fireoptions.no_turndown = 'Submit attack without turning down fire dice';
  }
  fireoptions.turndown = 'Turn down fire dice';
  fireoptions.cancel =
    'Don\'t turn down fire dice (cancelling the attack in progress)';

  $.each(fireoptions, function(actionname, actiontext) {
    var fireactionopts = {
      'value': actionname,
      'label': actiontext,
      'text': actiontext,
    };
    if ((actionname == 'no_turndown') ||
        (!allows_zero_turndown && (actionname == 'turndown'))) {
      fireactionopts.selected = 'selected';
    }
    fireactionselect.append($('<option>', fireactionopts));
  });
  fireform.append(fireactionselect);

  fireform.append(
    $('<button>', {
      'id': 'game_action_button',
      'text': 'Submit',
    }));

  // Add the form to the page
  Game.page.append(fireform);
};

Game.actionAdjustFireDiceInactive = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Opponent\'s turn to complete an attack by adjusting fire dice');

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable('adjust_fire_dice', false);

  Game.page.append(dietable);
};

Game.actionAdjustFireDiceNonplayer = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to complete an attack by adjusting fire dice' +
    ' (you are not in this game)');

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable('adjust_fire_dice', false);

  Game.page.append(dietable);
};

Game.actionShowFinishedGame = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader('This game is over');

  Game.page.append(Game.gameWinner());
  Game.page.append($('<br>'));
  Game.page.append(Game.buttonTableWithoutDice());
  Game.logEntryLimit = undefined;
};

////////////////////////////////////////////////////////////////////////
// Form submission functions

// Form submission action for choosing swing and option dice
Game.formSpecifyDiceActive = function() {
  var textFieldsFilled = true;
  var swingValueArray = {};
  var optionValueArray = {};

  // Iterate over expected swing values
  $.each(Api.game.player.swingRequestArray, function(letter) {
    var value = $('#swing_' + letter).val();
    if ($.isNumeric(value)) {
      swingValueArray[letter] = value;
    } else {
      textFieldsFilled = false;
    }
  });

  // Iterate over expected option values
  $.each(Api.game.player.optRequestArray, function(position) {
    var value = $('#option_' + position).val();
    if ($.isNumeric(value)) {
      optionValueArray[position] = value;
    } else {
      textFieldsFilled = false;
    }
  });

  if (textFieldsFilled) {
    Api.apiFormPost(
      {
        type: 'submitDieValues',
        game: Game.game,
        swingValueArray: swingValueArray,
        optionValueArray: optionValueArray,
        roundNumber: Api.game.roundNumber,
        timestamp: Api.game.timestamp,
      },
      { 'ok': { 'type': 'fixed', 'text': 'Successfully set swing values', },
        'notok': {'type': 'server', },
      },
      '#game_action_button',
      Game.showLoggedInPage,
      Game.showLoggedInPage
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': 'Some swing values missing or nonnumeric',
    };
    Game.showLoggedInPage();
  }
};

Game.formChooseAuxiliaryDiceActive = function() {
  Game.activity.auxiliaryDieAction = $('#auxiliary_die_select').val();
  if ((Game.activity.auxiliaryDieAction == 'add') ||
      (Game.activity.auxiliaryDieAction == 'decline')) {
    Api.apiFormPost(
      {
        type: 'reactToAuxiliary',
        game: Game.game,
        action: Game.activity.auxiliaryDieAction,
        dieIdx: Api.game.player.auxiliaryDieIndex,
      },
      { 'ok': { 'type': 'server', },
        'notok': {'type': 'server', },
      },
      '#game_action_button',
      Game.showLoggedInPage,
      Game.showLoggedInPage
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': 'Could not parse decision to use or not use auxiliary dice',
    };
    Game.showLoggedInPage();
  }
};

// Form submission action for choosing a reserve die
Game.formChooseReserveDiceActive = function() {
  Game.activity.reserveDieAction = $('#reserve_select').val();
  var inputValid = true;
  var inputError;
  var dieIdx = false;
  var diceChecked = 0;
  var postArgs = {
    type: 'reactToReserve',
    game: Game.game,
    action: Game.activity.reserveDieAction,
  };
  Game.activity.reserveDiceSelected = {};
  $.each(Api.game.player.reserveOptions, function(i) {
    if ($('#choose_reserve_' + i).prop('checked')) {
      Game.activity.reserveDiceSelected[i] = true;
      dieIdx = i;
      diceChecked++;
    }
  });
  if (Game.activity.reserveDieAction == 'add') {
    if (diceChecked != 1) {
      inputValid = false;
      inputError = 'If you choose to add reserve dice, you must select ' +
        'exactly one die to add';
    }
    postArgs.dieIdx = dieIdx;
  } else if (Game.activity.reserveDieAction == 'decline') {
    if (diceChecked !== 0) {
      inputValid = false;
      inputError = 'If you decline to add reserve dice, you cannot select ' +
        'any dice to add';
    }
  } else {
    inputValid = false;
    inputError = 'Could not parse decision to add or not add reserve dice';
  }

  if (inputValid) {
    Api.apiFormPost(
      postArgs,
      { 'ok': { 'type': 'server', },
        'notok': {'type': 'server', },
      },
      '#game_action_button',
      Game.showLoggedInPage,
      Game.showLoggedInPage
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': inputError,
    };
    Game.showLoggedInPage();
  }
};

// Form submission action for reacting to initiative
Game.formReactToInitiativeActive = function() {
  var formValid = true;
  var error = false;
  Game.activity.initiativeReactType = $('#react_type_select').val();
  Game.activity.initiativeDieIdxArray = [];
  Game.activity.initiativeDieValueArray = [];

  switch (Game.activity.initiativeReactType) {

  // valid action, nothing special to do, but validate selections just in case
  case 'decline':
    if ('focus' in Api.game.player.initiativeActions) {
      $.each(Api.game.player.initiativeActions.focus, function(i) {
        var value = $('#init_react_' + i).val();
        if (value != Api.game.player.activeDieArray[i].value) {
          error = 'Chose not to react to initiative, but modified a die value';
          formValid = false;
        }
      });
    }
    if ('chance' in Api.game.player.initiativeActions) {
      $.each(Api.game.player.initiativeActions.chance, function(i) {
        var value = $('#init_react_' + i).val();
        if (value != Api.game.player.activeDieArray[i].value) {
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
        if (value != Api.game.player.activeDieArray[i].value) {
          if (vals.indexOf(value) >= 0) {
            Game.activity.initiativeDieIdxArray.push(i);
            Game.activity.initiativeDieValueArray.push(value);
          } else {
            error = 'Invalid turndown value specified for focus die';
            formValid = false;
          }
        }
      });
      if (Game.activity.initiativeDieIdxArray.length === 0) {
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
        if (value != Api.game.player.activeDieArray[i].value) {
          if (value == 'reroll') {
            Game.activity.initiativeDieIdxArray.push(i);
            Game.activity.initiativeDieValueArray.push(value);
          } else {
            error = 'Bad value specified for chance action - choose "reroll"';
            formValid = false;
          }
        }
      });
      if (Game.activity.initiativeDieIdxArray.length === 0) {
        error =
          'Specified chance action but did not choose any dice to reroll';
        formValid = false;
      } else if (Game.activity.initiativeDieIdxArray.length > 1) {
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
    Api.apiFormPost(
      {
        type: 'reactToInitiative',
        game: Game.game,
        roundNumber: Api.game.roundNumber,
        timestamp: Api.game.timestamp,
        action: Game.activity.initiativeReactType,
        dieIdxArray: Game.activity.initiativeDieIdxArray,
        dieValueArray: Game.activity.initiativeDieValueArray,
      },
      { 'ok':
        {
          'type': 'function',
          'msgfunc': Game.reactToInitiativeSuccessMsg,
        },
        'notok': { 'type': 'server', },
      },
      '#game_action_button',
      Game.showLoggedInPage,
      Game.showLoggedInPage
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': error,
    };
    Game.showLoggedInPage();
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

// Form submission action for adjusting fire dice
Game.formAdjustFireDiceActive = function() {
  var formValid = true;
  var error = false;
  Game.activity.fireActionType = $('#fire_action_select').val();
  Game.activity.fireDieIdxArray = [];
  Game.activity.fireDieValueArray = [];

  switch (Game.activity.fireActionType) {

  // valid action, nothing special to do, but validate selections just in case
  case 'cancel':    // fallthrough to allow multiple cases with the same logic
  case 'no_turndown':
    $.each(Api.game.player.fireOptions, function(i, vals) {
      if (vals.length > 0) {
        var value = $('#fire_adjust_' + i).val();
        if (value != Api.game.player.activeDieArray[i].value) {
          error = 'Chose not to adjust fire dice, but modified a die value';
          formValid = false;
          Game.activity.fireDieIdxArray.push(i);
          Game.activity.fireDieValueArray.push(value);
        }
      }
    });
    break;

  case 'turndown':
    $.each(Api.game.player.fireOptions, function(i, vals) {
      if (vals.length > 0) {
        var value = parseInt($('#fire_adjust_' + i).val(), 10);
        if (value != Api.game.player.activeDieArray[i].value) {
          if (vals.indexOf(value) >= 0) {
            Game.activity.fireDieIdxArray.push(i);
            Game.activity.fireDieValueArray.push(value);
          } else {
            error = 'Invalid turndown value specified for fire die';
            formValid = false;
          }
        }
      }
    });
    if (Game.activity.fireDieIdxArray.length === 0) {
      error = 'Specified turndown action but did not turn down any dice';
      formValid = false;
    }
    break;

  default:
    error = 'Specified action is not valid';
    formValid = false;
  }

  if (formValid) {
    Api.apiFormPost(
      {
        type: 'adjustFire',
        game: Game.game,
        roundNumber: Api.game.roundNumber,
        timestamp: Api.game.timestamp,
        action: Game.activity.fireActionType,
        dieIdxArray: Game.activity.fireDieIdxArray,
        dieValueArray: Game.activity.fireDieValueArray,
      },
      {
        'ok': { 'type': 'server', },
        'notok': { 'type': 'server', },
      },
      '#game_action_button',
      Game.showLoggedInPage,
      Game.showLoggedInPage
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': error,
    };
    Game.showLoggedInPage();
  }
};

// Form submission action for playing a turn
Game.formPlayTurnActive = function() {
  Game.readCurrentGameActivity();

  // If surrender is chosen, first check to make sure no dice are
  // selected, then ask for confirmation.  Let the user try again
  // if they have selected dice or don't confirm the surrender
  if (Game.activity.attackType == 'Surrender') {
    var diceSelected = false;
    $.each(Game.activity.dieSelectStatus, function(idx, val) {
      if (val) {
        diceSelected = true;
      }
    });
    if (diceSelected) {
      Env.message = {
        'type': 'error',
        'text': 'Please deselect all dice before surrendering.',
      };
      return Game.redrawGamePageFailure();
    }

    var surrender = window.confirm(
      'Are you SURE you want to surrender this round?'
    );
    if (!(surrender)) {
      return Game.redrawGamePageFailure();
    }

  } else if (Game.activity.attackType === '') {
    Env.message = {
      'type': 'error',
      'text': 'You must select an attack type',
    };
    return Game.redrawGamePageFailure();
  }

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
    '#game_action_button',
    Game.redrawGamePageSuccess,
    Game.redrawGamePageFailure
  );
};

// Form submission action for updating chat when it's not your turn
Game.formPlayTurnInactive = function() {

  // Store the game chat in recent activity
  Game.activity.chat = $('#game_chat').val();

  var formargs = {
    type: 'submitChat',
    game: Game.game,
    chat: Game.activity.chat,
  };
  if (Api.game.chatEditable) {
    formargs.edit = Api.game.chatEditable;
  }

  Api.apiFormPost(
    formargs,
    { 'ok': { 'type': 'server', }, 'notok': { 'type': 'server', }, },
    '#game_action_button',
    Game.redrawGamePageSuccess,
    Game.redrawGamePageFailure
  );
};

// "Form" for cancelling a game
Game.formCancelGame = function(e) {
  e.preventDefault();

  var doGameCancel = Env.window.confirm(
    'Are you SURE you want to withdraw this game?'
  );
  if (!doGameCancel) {
    return;
  }

  var argsCancel = {
    'type': 'reactToNewGame',
    'gameId': $(this).attr('data-gameId'),
    'action': 'reject',
  };
  var argsDismiss = {
    'type': 'dismissGame',
    'gameId': $(this).attr('data-gameId'),
  };
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully withdrew game', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(
    argsCancel,
    messages,
    $(this),
    function() {
      Api.apiFormPost(
        argsDismiss,      // auto-dismiss on cancel
        messages,
        $(this),
        function() {
          window.location.href = Env.ui_root + 'index.html?mode=preference';
          return false;
        },
        Game.showLoggedInPage
      );
    },
    Game.showLoggedInPage
  );
};

// "Form" for accepting a game
Game.formAcceptGame = function(e) {
  e.preventDefault();
  var args = {
    'type': 'reactToNewGame',
    'gameId': $(this).attr('data-gameId'),
    'action': 'accept',
  };
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully accepted game', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Game.showLoggedInPage,
    Game.showLoggedInPage);
};

// "Form" for rejecting a game
Game.formRejectGame = function(e) {
  e.preventDefault();

  var doGameReject = Env.window.confirm(
    'Are you SURE you want to reject this game?'
  );
  if (!doGameReject) {
    return;
  }

  var args = {
    'type': 'reactToNewGame',
    'gameId': $(this).attr('data-gameId'),
    'action': 'reject',
  };
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully rejected game', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(
    args,
    messages,
    $(this),
    function() {
      window.location.href = Env.ui_root + 'index.html?mode=preference';
      return false;
    },
    Game.showLoggedInPage
  );
};

// "Form" for dismissing a game after it's completed
Game.formDismissGame = function(e) {
  e.preventDefault();
  var args = { 'type': 'dismissGame', 'gameId': $(this).attr('data-gameId'), };
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully dismissed game', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(
    args,
    messages,
    $(this),
    function() {
      window.location.href = Env.ui_root + 'index.html?mode=preference';
      return false;
    },
    Game.showLoggedInPage
  );
};

Game.readCurrentGameActivity = function() {
  // Initialize the array of die select statuses to all false, then
  // turn on the dice which have been selected
  Game.activity.dieSelectStatus = {};
  var i;
  for (i = 0 ; i < Api.game.player.activeDieArray.length; i++) {
    Game.activity.dieSelectStatus[Game.dieIndexId('player', i)] = false;
  }
  for (i = 0 ; i < Api.game.opponent.activeDieArray.length; i++) {
    Game.activity.dieSelectStatus[Game.dieIndexId('opponent', i)] = false;
  }
  $('div.selected').each(function(index, element) {
    Game.activity.dieSelectStatus[$(element).attr('id')] = true;
  });

  // Get the specified attack type
  Game.activity.attackType = $('#attack_type_select').val();

  // Store the game chat in recent activity (minus trailing whitespace)
  var chat = $('#game_chat').val();
  if (chat !== undefined && chat !== null) {
    chat = chat.replace(/\s+$/, '');
  }
  Game.activity.chat = chat;
};

Game.showFullLogHistory = function() {
  Game.readCurrentGameActivity();
  Game.logEntryLimit = undefined;
  Game.showLoggedInPage();
};

////////////////////////////////////////////////////////////////////////
// Page layout helper routines

// Display header information about the game
Game.pageAddGameHeader = function(action_desc) {
  var gameTitle =
    'Game #' + Api.game.gameId + Game.SPACE_BULLET +
      Api.game.player.playerName + ' (' + Api.game.player.button.name +
      ') vs. ' + Api.game.opponent.playerName + ' (' +
      Api.game.opponent.button.name + ') ' + Game.SPACE_BULLET + ' ';
  if (Api.game.gameState == Game.GAME_STATE_END_GAME) {
    gameTitle += 'Completed';
  } else if (Api.game.gameState == Game.GAME_STATE_CANCELLED) {
    gameTitle += 'Cancelled';
  } else if (Api.game.gameState == Game.GAME_STATE_CHOOSE_JOIN_GAME) {
    gameTitle += 'New Game';
  } else {
    gameTitle += 'Round #' + Api.game.roundNumber;
  }
  $('title').html(gameTitle + ' &mdash; Button Men Online');

  Game.page.append(
    $('<div>', {
      'id': 'game_id',
      'html': gameTitle,
    }));
  var bgcolor = '#ffffff';
  if (Api.game.player.waitingOnAction) {
    bgcolor = Game.color.player;
  } else if (Api.game.opponent.waitingOnAction) {
    bgcolor = Game.color.opponent;
  }

  if (Api.game.description) {
    Game.page.append($('<div>', {
      'text': Api.game.description,
      'class': 'gameDescDisplay',
    }));
  }

  var actionSpan = $('<span>', {
    'id': 'action_desc_span',
    'class': 'action_desc_span',
    'style': 'background: none repeat scroll 0 0 ' + bgcolor,
    'text': action_desc,
  });
  var actionDiv = $('<div>', { 'class': 'action_desc_div', });
  actionDiv.append(actionSpan);

  // If there's new chat the player hasn't seen yet, notify them
  if (Api.game.isParticipant && Api.game.player.lastActionTime &&
      Api.game.chatLog.length &&
      Api.game.chatLog[0].timestamp > Api.game.player.lastActionTime) {
    actionDiv.append(Game.SPACE_BULLET);
    actionDiv.append($('<span>', {
      'class': 'action_desc_span new',
      'text': 'New chat message',
    }));
  }
  Game.page.append(actionDiv);

  Game.page.append($('<br>'));

  if (Api.game.isParticipant && !Api.game.player.hasDismissedGame &&
      (Api.game.gameState == Game.GAME_STATE_END_GAME ||
       Api.game.gameState == Game.GAME_STATE_CANCELLED)) {
    var dismissDiv = $('<div>');
    Game.page.append(dismissDiv);
    var dismissLink = $('<a>', {
      'text': '[Dismiss Game]',
      'href': '#',
      'data-gameId': Api.game.gameId,
    });
    dismissLink.click(Game.formDismissGame);
    dismissDiv.append(dismissLink);
    Game.page.append($('<br>'));
  }

  if (Api.game.isParticipant &&
      Api.game.gameState == Game.GAME_STATE_CHOOSE_JOIN_GAME) {
    var acceptRejectDiv = $('<div>');
    Game.page.append(acceptRejectDiv);

    if (Api.game.player.waitingOnAction) {
      var acceptLink = $('<a>', {
        'text': '[Accept Game]',
        'href': '#',
        'data-gameId': Api.game.gameId,
      });
      acceptLink.click(Game.formAcceptGame);
      acceptRejectDiv.append(acceptLink);

      acceptRejectDiv.append(' ');

      var rejectLink = $('<a>', {
        'text': '[Reject Game]',
        'href': '#',
        'data-gameId': Api.game.gameId,
      });
      rejectLink.click(Game.formRejectGame);
      acceptRejectDiv.append(rejectLink);
    } else {
      var cancelLink = $('<a>', {
        'text': '[Withdraw Game]',
        'href': '#',
        'data-gameId': Api.game.gameId,
      });
      cancelLink.click(Game.formCancelGame);
      acceptRejectDiv.append(cancelLink);
    }

    Game.page.append($('<br>'));
  }

  if (Api.game.gameState == Game.GAME_STATE_START_TURN &&
      Api.game.isParticipant && Api.game.player.waitingOnAction &&
      Api.game.player.canStillWin === false) {
    var cantWinDiv = $('<div>', {
      'class': 'cantWin',
      'text':
        'It is impossible for you to win this round. It might be a good ' +
        'time to surrender.',
    });
    Game.page.append(cantWinDiv);
    Game.page.append($('<br>'));
  }

  return true;
};

// Display common page footer data
Game.pageAddFooter = function(isChatHidden) {
  if (!Game.page) {
    return;
  }

  Game.pageAddGameNavigationFooter();
  Game.pageAddUnhideChatButton(isChatHidden);
  Game.pageAddSkillListFooter();
  Game.pageAddNewGameLinkFooter();
  Game.pageAddLogFooter();
};

Game.pageAddUnhideChatButton = function(isChatHidden) {
  if (!isChatHidden) {
    return false;
  }

  var unhideButton = $('<input>', {
    'type': 'button',
    'value': 'Add/Edit Chat',
  });
  unhideButton.click(function() {
    $('.hiddenChatForm').show();
    $('.unhideChat').hide();
    $('#game_chat').focus();
    // Also, a hack to move the cursor to the end
    var chat = $('#game_chat').val();
    $('#game_chat').val('');
    $('#game_chat').val(chat);
  });

  var unhideDiv = $('<div>', { 'class': 'unhideChat', });
  Game.page.append(unhideDiv);
  unhideDiv.append($('<br>'));
  unhideDiv.append(unhideButton);
  unhideDiv.append($('<br>'));
};

// Display a link to the next game requiring action
Game.pageAddGameNavigationFooter = function() {
  if (!Api.game.isParticipant || Api.game.player.waitingOnAction) {
    return false;
  }

  var countText;
  if (Api.pending_games.count) {
    countText = '(at least ' + Api.pending_games.count + ')';
  } else {
    countText = '(if any)';
  }
  Game.page.append($('<br>'));
  var linkDiv = $('<div>');
  linkDiv.append($('<a>', {
    'href': Env.ui_root + 'index.html?mode=nextGame',
    'text': 'Go to your next pending game ' + countText,
  }).click(function(e) {
    e.preventDefault();
    Api.getNextGameId(Login.goToNextPendingGame);
  }));
  Game.page.append(linkDiv);
  return true;
};

// Display a footer-style message with the list of skills in this game
Game.pageAddSkillListFooter = function() {
  var gameSkillDiv = $('<div>');

  var dieSkillSpanArray = [];
  var buttonSkillSpanArray = [];
  var interactDescArray;
  var skillDesc;
  var skillSpan;

  $.each(Api.game.gameSkillsInfo, function(skill, info) {
    skillDesc = skill;
    if (info.code) {
      skill += ' (' + info.code + ')';
      skillDesc += ' (' + info.code + ')';
    }
    skillDesc += ': ' + info.description;

    interactDescArray = [];
    $.each(info.interacts, function(otherSkill, interactDesc) {
      interactDescArray.push(' * ' + otherSkill + ': ' + interactDesc);
    });

    if (interactDescArray.length > 0) {
      skillDesc += '\n\nInteraction with other skills: \n';
      skillDesc += interactDescArray.join('\n');
    }

    skillSpan = $('<span>').append($('<span>', {
      'text': skill,
      'title': skillDesc,
      'class': 'skill_desc',
    })).append($('<span>', {
      'text': 'i',
      'title': skillDesc,
      'class': 'info_icon',
    }));

    if (info.code) {
      dieSkillSpanArray.push(skillSpan);
    } else {
      buttonSkillSpanArray.push(skillSpan);
    }
  });

  if (0 === (dieSkillSpanArray.length + buttonSkillSpanArray.length)) {
    gameSkillDiv.append('Skills in this game: none');
  } else {
    if (buttonSkillSpanArray.length > 0) {
      gameSkillDiv.append(
        Game.createSkillDiv(
          buttonSkillSpanArray,
          'Button specials'
        )
      );
    }
    if (dieSkillSpanArray.length > 0) {
      gameSkillDiv.append(
        Game.createSkillDiv(
          dieSkillSpanArray,
          'Die skills'
        )
      );
    }
  }

  Game.page.append($('<br>'));
  Game.page.append(gameSkillDiv);
  return true;
};

Game.createSkillDiv = function(spanArray, divTitle) {
  var skillDiv = $('<div>');
  skillDiv = skillDiv.append($('<span>', {
    'text': divTitle + ': ',
    'class': 'skill_title',
  }));

  $.each(spanArray, function(index, value) {
    skillDiv = skillDiv.append(value);
    if (index < spanArray.length - 1) {
      skillDiv.append('&nbsp;&nbsp;');
    }
  });

  return skillDiv;
};

// Display links to create new games similar to this one
Game.pageAddNewGameLinkFooter = function() {
  if ((Api.game.gameState != Game.GAME_STATE_END_GAME) &&
      (Api.game.gameState != Game.GAME_STATE_CANCELLED)) {
    return;
  }

  var linkDiv;
  if (Api.game.isParticipant) {
    Game.page.append($('<br>'));

    Game.page.append($('<div>', {
      'text':
        'Challenge ' + Api.game.opponent.playerName +
        ' to another game, preserving chat:',
    }));

    linkDiv = $('<div>');
    Game.page.append(linkDiv);

    linkDiv.append(Game.buildNewGameLink(
      'same buttons',
      Api.game.opponent.playerName,
      Api.game.player.button.name,
      Api.game.opponent.button.name,
      Api.game.gameId
    ));

    if (Api.game.player.button.name != Api.game.opponent.button.name) {
      linkDiv.append(Game.buildNewGameLink(
        'reverse',
        Api.game.opponent.playerName,
        Api.game.opponent.button.name,
        Api.game.player.button.name,
      Api.game.gameId
      ));
    }

    linkDiv.append(Game.buildNewGameLink(
      'random buttons',
      Api.game.opponent.playerName,
      '__random',
      '__random',
      Api.game.gameId
    ));

    linkDiv.append(Game.buildNewGameLink(
      'new buttons',
      Api.game.opponent.playerName,
      null,
      null,
      Api.game.gameId
    ));
  }

  Game.page.append($('<br>'));

  Game.page.append($('<div>', {
    'text': 'Create an open game with these buttons: ',
  }));

  linkDiv = $('<div>');
  Game.page.append(linkDiv);

  if (Api.game.player.button.name == Api.game.opponent.button.name) {
    linkDiv.append(Game.buildNewGameLink(
      'you both play ' + Api.game.player.button.name,
      null,
      Api.game.player.button.name,
      Api.game.player.button.name,
      null
    ));
  } else {
    linkDiv.append(Game.buildNewGameLink(
      'you play ' + Api.game.player.button.name,
      null,
      Api.game.player.button.name,
      Api.game.opponent.button.name,
      null
    ));

    linkDiv.append(Game.buildNewGameLink(
      'you play ' + Api.game.opponent.button.name,
      null,
      Api.game.opponent.button.name,
      Api.game.player.button.name,
      null
    ));
  }

  Game.page.append($('<br>'));
};

// Constructs a span containing a link to the Create Game page
Game.buildNewGameLink = function(text, opponent, button, opponentButton,
    previousGameId) {
  var holder = $('<span>');
  holder.append('[');
  var url = 'create_game.html?';
  if (opponent) {
    url += 'opponent=' + encodeURIComponent(opponent) + '&';
  }
  if (button) {
    url += 'playerButton=' + encodeURIComponent(button) + '&';
  }
  if (opponentButton) {
    url += 'opponentButton=' + encodeURIComponent(opponentButton) + '&';
  }
  if (previousGameId) {
    url += 'previousGameId=' + previousGameId + '&';
  }
  url += 'maxWins=' + Api.game.maxWins;

  holder.append($('<a>', {
    'text': text,
    'href': url,
  }));
  holder.append('] ');
  return holder;
};

// Display recent game data from the action log at the foot of the page
Game.pageAddLogFooter = function() {
  if ((Api.game.chatLog.length > 0) || (Api.game.actionLog.length > 0)) {
    var logdiv = $('<div>');
    var logtable = $('<table>');
    if (Api.game.actionLog.length > 0 && Api.game.chatLog.length > 0 &&
      !Env.getCookieCompactMode()) {
      logtable.addClass('twocolumn');
    }

    var actiontd;
    if (Api.game.actionLog.length > 0) {
      actiontd = $('<td>', {'class': 'logtable', });
      actiontd.append($('<p>', {'text': 'Recent game activity', }));
      var actiontable = $('<table>', {'border': 'on', });
      $.each(Api.game.actionLog, function(logindex, logentry) {
        var actionplayer;
        if (logentry.player == Api.game.player.playerName) {
          actionplayer = 'player';
        } else if (logentry.player == Api.game.opponent.playerName) {
          actionplayer = 'opponent';
        } else {
          actionplayer = 'noone';
        }
        var actionrow = $('<tr>');
        actionrow.append(
          $('<td>', {
            'class': 'chat',
            'style': 'background-color: ' + Game.color[actionplayer],
            'nowrap': 'nowrap',
            'text': '(' + Env.formatTimestamp(logentry.timestamp) + ')',
          }));
        var messageClass = 'left logmessage';
        if (Api.game.isParticipant && Api.game.player.lastActionTime &&
          logentry.timestamp > Api.game.player.lastActionTime) {
          messageClass += ' new';
        }
        // Env.prepareRawTextForDisplay() converts the dangerous raw text
        // into safe HTML.
        actionrow.append(
          $('<td>', {
            'class': messageClass,
            'html': Env.prepareRawTextForDisplay(logentry.message),
          }));
        actiontable.append(actionrow);
      });
      actiontd.append(actiontable);
    }

    var chattd;
    if (Api.game.chatLog.length > 0) {
      chattd = $('<td>', {'class': 'logtable', });
      chattd.append($('<p>', {'text': 'Recent game chat', }));
      var chattable = $('<table>', {'border': 'on', });
      $.each(Api.game.chatLog, function(logindex, logentry) {
        var chatrow = $('<tr>');
        var chatplayer;
        if (logentry.player == Api.game.player.playerName) {
          chatplayer = 'player';
        } else if (logentry.player == Api.game.opponent.playerName) {
          chatplayer = 'opponent';
        } else {
          chatplayer = 'noone';
        }
        chatrow.append($('<td>', {
          'class': 'chat',
          'style': 'background-color: ' + Game.color[chatplayer],
          'nowrap': 'nowrap',
          'text': logentry.player + ' (' +
            Env.formatTimestamp(logentry.timestamp) + ')',
        }));
        var messageClass = 'left logmessage';
        if (Api.game.isParticipant && Api.game.player.lastActionTime &&
          logentry.timestamp > Api.game.player.lastActionTime) {
          messageClass += ' new';
        }
        // Env.prepareRawTextForDisplay() converts the dangerous raw text
        // into safe HTML.
        chatrow.append($('<td>', {
          'class': messageClass,
          'html': Env.prepareRawTextForDisplay(logentry.message),
        }));
        chattable.append(chatrow);
      });
      chattd.append(chattable);
    }

    if (Env.getCookieCompactMode()) {
      if (chattd) {
        var chatRow = $('<tr>');
        logtable.append(chatRow);
        chatRow.append(chattd);
      }
      if (actiontd) {
        var actionRow = $('<tr>');
        logtable.append(actionRow);
        actionRow.append(actiontd);
      }
    } else {
      var logRow = $('<tr>');
      logtable.append(logRow);
      if (actiontd) {
        logRow.append(actiontd);
      }
      if (chattd) {
        logRow.append(chattd);
      }
    }

    if (Game.logEntryLimit !== undefined &&
        ((Api.game.actionLogCount > Api.game.actionLog.length) ||
         (Api.game.chatLogCount > Api.game.chatLog.length))) {
      var historyrow = $('<tr>', { 'class': 'loghistory' });
      var historytd = $('<td>');
      if ((Api.game.actionLog.length > 0) && (Api.game.chatLog.length > 0)) {
        historytd.attr('colspan', 2);
      }

      historytd.append('[');
      historytd.append($('<a>', {
        'href': 'javascript:Game.showFullLogHistory();',
        'text': 'View full activity and chat history',
      }));
      historytd.append(']');

      historyrow.append(historytd);
      logtable.append(historyrow);
    }

    logdiv.append(logtable);

    Game.page.append($('<hr>'));
    Game.page.append(logdiv);
  }
};


// Generate and return a two-column table of the dice in each player's recipe
Game.dieRecipeTable = function(table_action, active) {

  // variables only needed in cases with table actions
  var subLTable;
  var subRTable;

  var dietable = $('<table>', {'id': 'die_recipe_table', });
  dietable.append(Game.playerOpponentHeaderRow('Player', 'playerName'));
  dietable.append(Game.playerOpponentHeaderRow('Button', 'buttonName'));
  dietable.append(Game.playerOpponentHeaderRow('', 'gameScoreStr'));

  if (table_action) {
    subLTable = $('<table>');
    subRTable = $('<table>');

    var subHeaderLRow = $('<tr>');
    var subHeaderRRow = $('<tr>');

    // contents of table headers depend on the type of table action
    if ((table_action == 'react_to_initiative') ||
        (table_action == 'adjust_fire_dice')) {
      subHeaderLRow.append($('<th>', { 'text': 'Recipe' }));
      subHeaderLRow.append($('<th>', { 'text': 'Value' }));
      subHeaderRRow = subHeaderLRow.clone();
    } else if (table_action == 'choose_reserve') {
      subHeaderLRow.append($('<th>', { 'text': 'Recipe' }));
      subHeaderLRow.append($('<th>', { 'text': 'Add die?' }));
      subHeaderRRow.append($('<th>', { 'text': 'Recipe' }));
    }

    subLTable.append(subHeaderLRow);
    subRTable.append(subHeaderRRow);
  }

  var maxDice = Math.max(
    Api.game.player.activeDieArray.length,
    Api.game.opponent.activeDieArray.length);
  for (var i = 0; i < maxDice; i++) {
    var playerEnt = Game.dieTableEntry(i, Api.game.player.activeDieArray);
    var opponentEnt = Game.dieTableEntry(i, Api.game.opponent.activeDieArray);
    var defaultval;
    if (table_action) {
      var dieLRow = $('<tr>');
      var dieRRow = $('<tr>');
      if (table_action == 'react_to_initiative') {
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
          defaultval = Api.game.player.activeDieArray[i].value;
          if ('initiativeDieIdxArray' in Game.activity) {
            $.each(Game.activity.initiativeDieIdxArray, function(idx, val) {
              if (val == i) {
                defaultval = Game.activity.initiativeDieValueArray[idx];
              }
            });
          }
          dieLRow.append(
            Game.dieValueSelectTd('init_react_' + i, initopts,
              Api.game.player.activeDieArray[i].value, defaultval));
        } else {
          dieLRow.append($('<td>', {
            'text': Game.activeDieFieldString(
              i, 'value', Api.game.player.activeDieArray),
          }));
        }
        dieRRow.append(opponentEnt);
        dieRRow.append($('<td>', {
          'text': Game.activeDieFieldString(
            i, 'value', Api.game.opponent.activeDieArray),
        }));
      } else if (table_action == 'adjust_fire_dice') {
        dieLRow.append(playerEnt);
        var fireopts = [];
        if (active) {
          if (i in Api.game.player.fireOptions) {
            fireopts = Api.game.player.fireOptions[i].concat();
          }
        }
        if ((active) && (fireopts.length > 0)) {
          defaultval = Api.game.player.activeDieArray[i].value;
          if ('fireDieIdxArray' in Game.activity) {
            $.each(Game.activity.fireDieIdxArray, function(idx, val) {
              if (val == i) {
                defaultval = Game.activity.fireDieValueArray[idx];
              }
            });
          }
          dieLRow.append(
            Game.dieValueSelectTd('fire_adjust_' + i, fireopts,
              Api.game.player.activeDieArray[i].value, defaultval));
        } else {
          dieLRow.append($('<td>', {
            'text': Game.activeDieFieldString(
                      i,
                      'value',
                      Api.game.player.activeDieArray
                    ),
          }));
        }
        dieRRow.append(opponentEnt);
        dieRRow.append($('<td>', {
          'text': Game.activeDieFieldString(
                    i,
                    'value',
                    Api.game.opponent.activeDieArray
                  ),
        }));
      } else if (table_action == 'choose_reserve') {
        dieLRow.append(playerEnt);
        if (i in Api.game.player.reserveOptions) {
          var checkTd = $('<td>');
          var inputOptions = {
            'type': 'checkbox',
            'name': 'choose_reserve_' + i,
            'id': 'choose_reserve_' + i,
          };
          if (('reserveDiceSelected' in Game.activity) &&
              (i in Game.activity.reserveDiceSelected)) {
            inputOptions.checked = 'checked';
          }
          checkTd.append($('<input>', inputOptions));
          dieLRow.append(checkTd);
        } else {
          dieLRow.append($('<td>'));
        }
        dieRRow.append(opponentEnt);
      }
      subLTable.append(dieLRow);
      subRTable.append(dieRRow);
    } else {
      var dierow = $('<tr>', {});
      dierow.append(playerEnt);
      dierow.append(opponentEnt);
      dietable.append(dierow);
    }
  }
  if (table_action) {
    var subrow = $('<tr>');
    var subLTd = $('<td>');
    var subRTd = $('<td>');
    subLTd.append(subLTable);
    subRTd.append(subRTable);
    subrow.append(subLTd);
    subrow.append(subRTd);
    dietable.append(subrow);
  }

  return dietable;
};

Game.playerWLTText = function(player) {
  var text;
  if ((Api.game.gameState == Game.GAME_STATE_CHOOSE_JOIN_GAME) ||
      (Api.game.gameState == Game.GAME_STATE_CANCELLED)) {
    text = 'W/L/T: –/–/–';
  } else {
    text = 'W/L/T: ' + Api.game[player].gameScoreArray.W +
           '/' + Api.game[player].gameScoreArray.L +
           '/' + Api.game[player].gameScoreArray.D;
  }

  text += ' (' + Api.game.maxWins + ')';
  return text;
};

// Generate and return a table of the swing ranges for the player's swing dice
Game.swingRangeTable = function(swingRequestArray, id, allowInput, showPrev) {
  var swingrangetable = $('<table>', { 'id': id, });
  $.each(
    swingRequestArray,
    function(letter, range) {
      var swingrow = $('<tr>', {});
      var swingtext = letter;
      if (!allowInput) {
        swingtext += ':';
      }
      swingtext += ' (' + range.min + '-' + range.max + ')';
      if (allowInput) {
        swingtext += ':';
      }
      swingrow.append($('<td>', { 'text': swingtext, }));
      if (allowInput) {
        var swinginput = $('<td>', {});
        swinginput.append($('<input>', {
          'type': 'text',
          'class': 'swing',
          'id': 'swing_' + letter,
          'size': '2',
          'maxlength': '2',
        }));
        swingrow.append(swinginput);
      }
      if (showPrev) {
        var swingprevtext = '';
        if (letter in Api.game.player.prevSwingValueArray) {
          swingprevtext =
            '(was: ' + Api.game.player.prevSwingValueArray[letter] + ')';
          swingrow.append($('<td>', { 'text': swingprevtext, }));
        }
      }
      swingrangetable.append(swingrow);
    });

  return swingrangetable;
};


Game.dieTableEntry = function(i, activeDieArray) {
  if (i < activeDieArray.length) {
    var die = activeDieArray[i];
    var dieval = Game.dieRecipeText(die);
    var dieopts = {
      'text': dieval,
      'title': die.description,
    };
    if ((die.properties.indexOf('Dizzy') >= 0) &&
        (die.skills.indexOf('Focus') >= 0)) {
      dieopts['class'] = 'recipe_greyed';
      if (Api.game.gameState == Game.GAME_STATE_REACT_TO_INITIATIVE) {
        dieopts.title += '. (This die is dizzy because it has been turned ' +
          'down. If the owner wins initiative, this die can\'t be used in ' +
          'their first attack.)';
      } else {
        dieopts.title += '. (This die is dizzy because it has been turned ' +
          'down. It can\'t be used during this attack.)';
      }
    } else if ((die.properties.indexOf('Disabled') >= 0) &&
               (die.skills.indexOf('Chance') >= 0)) {
      dieopts['class'] = 'recipe_greyed';
      dieopts.title += '. (This chance die cannot be rerolled again ' +
        'during this round, because the player has already rerolled a ' +
        'chance die)';
    } else if (die.properties.indexOf('IsAttacker') >= 0) {
      dieopts['class'] = 'recipe_inuse';
      dieopts.title += '. (This die is an attacker in the attack which ' +
        'is currently in progress.)';
    } else if (die.properties.indexOf('IsAttackTarget') >= 0) {
      dieopts['class'] = 'recipe_inuse';
      dieopts.title += '. (This die is a target of the attack which ' +
        'is currently in progress.)';
    }
    return $('<td>', dieopts);
  }
  return $('<td>', {});
};

Game.activeDieFieldString = function(i, field, activeDieArray) {
  if (i < activeDieArray.length) {
    return activeDieArray[i][field];
  } else {
    return '';
  }
};

// Display each player's dice in "battle" layout
Game.pageAddDieBattleTable = function(clickable) {
  var dieBattleTable = $('<table>');
  var dieBattleTr = $('<tr>');
  var dieBattleTd = $('<td>', {'class': 'battle_mat', });

  var playerButtonTd = Game.buttonImageDisplay('player');
  var opponentButtonTd = Game.buttonImageDisplay('opponent');

  var diePlayerDiv = $('<div>', {
    'class': 'battle_mat_player',
    'style': 'background: none repeat scroll 0 0 ' + Game.color.opponent,
  });
  var diePlayerOverlayDiv = $('<div>', {
    'class': 'battle_mat_player_overlay',
    'style': 'background: none repeat scroll 0 0 ' + Game.color.player,
  });
  diePlayerOverlayDiv.append(Game.gamePlayerStatus('player', false, true));
  diePlayerOverlayDiv.append(Game.gamePlayerDice('player', clickable));
  diePlayerDiv.append(diePlayerOverlayDiv);
  dieBattleTd.append(diePlayerDiv);

  var dieOpponentDiv = $('<div>', {
    'class': 'battle_mat_opponent',
    'style': 'background: none repeat scroll 0 0 ' + Game.color.player,
  });
  var dieOpponentOverlayDiv = $('<div>', {
    'class': 'battle_mat_opponent_overlay',
    'style': 'background: none repeat scroll 0 0 ' + Game.color.opponent,
  });
  dieOpponentOverlayDiv.append(Game.gamePlayerDice('opponent', clickable));
  dieOpponentOverlayDiv.append(Game.gamePlayerStatus('opponent', true, true));
  dieOpponentDiv.append(dieOpponentOverlayDiv);
  dieBattleTd.append(dieOpponentDiv);

  dieBattleTr.append(playerButtonTd);
  dieBattleTr.append(dieBattleTd);
  dieBattleTr.append(opponentButtonTd);
  dieBattleTable.append(dieBattleTr);
  Game.page.append(dieBattleTable);
  return true;
};

// return a TD containing the button image for the player or opponent
// button image is a png, image name is derived from button name,
// all lowercase, spaces and punctuation removed
Game.buttonImageDisplay = function(player) {
  var tdClass = 'button_' + player;
  var isPreOrPostGame = Api.game.gameState == Game.GAME_STATE_END_GAME ||
                        Api.game.gameState == Game.GAME_STATE_CANCELLED ||
                        Api.game.gameState == Game.GAME_STATE_CHOOSE_JOIN_GAME;

  if (isPreOrPostGame) {
    tdClass += ' button_prepostgame';
  }
  var buttonTd = $('<td>', {
    'class': tdClass,
    'style': 'background: ' + Game.color[player],
  });
  var playerName = $('<div>', {
    'html': $('<b>', { 'text': 'Player: ', }),
  });
  if (Api.game[player].isOnVacation) {
    playerName.append(Env.buildVacationImage());
  }
  playerName.append(Env.buildProfileLink(Api.game[player].playerName));
  var playerWLT = $('<div>', {
    'text': Game.playerWLTText(player),
  });
  var buttonInfo = $('<div>', {
    'text': 'Button: '
  });
  buttonInfo.append(Env.buildButtonLink(Api.game[player].button.name));
  var buttonRecipe = $('<div>');
  if (Api.game.gameState == Game.GAME_STATE_END_GAME) {
    buttonRecipe.text(Api.game[player].button.originalRecipe);
  } else {
    buttonRecipe.text(Api.game[player].button.recipe);
  }

  if (player == 'opponent' && !isPreOrPostGame) {
    buttonTd.append(playerName);
    buttonTd.append(buttonInfo);
    buttonTd.append(buttonRecipe);
  }
  if (Api.game.gameState == Game.GAME_STATE_END_GAME) {
    buttonTd.append(playerWLT);
  }
  if (Env.getCookieNoImages() || Env.getCookieCompactMode()) {
    buttonTd.append($('<div>', { 'style': 'height: 150px; width: 150px;', }));
  } else {
    buttonTd.append($('<img>', {
      'src':
        Env.ui_root + 'images/button/' + Api.game[player].button.artFilename,
      'width': '150px',
    }));
  }
  if (player == 'player' || isPreOrPostGame) {
    buttonTd.append(buttonRecipe);
    buttonTd.append(buttonInfo);
    buttonTd.append(playerName);
  }
  return buttonTd;
};

// Return a brief mid-game status listing for the requested player
Game.gamePlayerStatus = function(player, reversed, game_active) {

  // Status div for entire section
  var statusDiv = $('<div>', {
    'class': 'status_' + player,
  });

  // Game score
  var gameScoreDiv = $('<div>', { 'html': Game.playerWLTText(player), });

  var capturedDiceDiv;
  var outOfPlayDiceDiv;
  if (game_active) {

    // Round score, only applicable in active games
    gameScoreDiv.append(Game.SPACE_BULLET);
    var sideScoreStr = Api.game[player].sideScore;
    if (sideScoreStr > 0) {
      sideScoreStr = '+' + sideScoreStr;
    }
    gameScoreDiv.append($('<b>', {
      'text':
        'Score: ' + Api.game[player].roundScore + ' (' +
        sideScoreStr + ' sides)',
    }));

    // Dice captured this round, only applicable in active games
    var capturedDieText;
    if (Api.game[player].capturedDieArray.length > 0) {
      var capturedDieDescs = [];

      $.each(Api.game[player].capturedDieArray, function(i, die) {
        capturedDieDescs.push(Game.dieRecipeText(die, true));
      });
      capturedDieText = capturedDieDescs.join(', ');
    } else {
      capturedDieText = 'none';
    }
    capturedDiceDiv = $('<div>');
    capturedDiceDiv.append($('<span>', {
      'text': 'Dice captured: ' + capturedDieText,
    }));

    // Dice that are out of play, only applicable in active games
    var outOfPlayDieText;
    if (('outOfPlayDieArray' in Api.game[player]) &&
        Api.game[player].outOfPlayDieArray.length > 0) {
      var outOfPlayDieDescs = [];

      $.each(Api.game[player].outOfPlayDieArray, function(i, die) {
        outOfPlayDieDescs.push(Game.dieRecipeText(die, true));
      });
      outOfPlayDieText = outOfPlayDieDescs.join(', ');
      outOfPlayDiceDiv = $('<div>');
      outOfPlayDiceDiv.append($('<span>', {
        'text': 'Dice out of play: ' + outOfPlayDieText,
      }));
    }
  }

  // Order the elements depending on the "reversed" flag
  if (reversed) {
    if (game_active) {
      if (undefined !== outOfPlayDiceDiv) {
        statusDiv.append(outOfPlayDiceDiv);
      }
      statusDiv.append(capturedDiceDiv);
    }
    statusDiv.append(gameScoreDiv);

  } else {
    statusDiv.append(gameScoreDiv);
    if (game_active) {
      statusDiv.append(capturedDiceDiv);
      if (undefined !== outOfPlayDiceDiv) {
        statusDiv.append(outOfPlayDiceDiv);
      }
    }
  }
  return statusDiv;
};

/**
 * Should a die be displayed as clickable in a given player mat?
 *
 * The return object contains a boolean saying whether the die is clickable,
 * and a string describing the reason why or why not.
 *
 * @param  object  die            The die to be displayed
 * @param  boolean die_status     Status of the die ('active' or 'captured')
 * @param  string  player         Whose is the die? ('player' or 'opponent')
 * @param  boolean player_active  Is the player displaying the die active?
 * @return object
 */
Game.dieClickableInfo = function(die, player, die_status, player_active) {
  if (die_status == 'captured') {
    if (die.properties.indexOf('WasJustCaptured') >= 0) {
      return {
        'isClickable': false,
        'reason': 'This die was just captured in the last attack and is no ' +
                  'longer in play.',
      };
    }
    return {
      'isClickable': false,
      'reason': 'This die was captured during an earlier attack.',
    };
  }

  if (!player_active) {
    return {
      'isClickable': false,
      'reason':
        'The game is not awaiting action from the player loading the mat.',
    };
  }

  if (die.properties.indexOf('Dizzy') >= 0) {
    return {
      'isClickable': false,
      'reason': 'This die is dizzy because it was turned ' +
                'down.  It can\'t be used during this attack.',
    };
  }

  // The opponent's Warrior dice are not clickable
  if ((player != 'player') && (die.skills.indexOf('Warrior') >= 0)) {
    return {
      'isClickable': false,
      'reason': 'This die is a Warrior die and can\'t be targeted.',
    };
  }

  // Otherwise the die is clickable
  return {
    'isClickable': true,
    'reason': 'Active dice are clickable by default.',
  };
};

/**
 * Return a div containing a die recipe for use in a game battle mat
 *
 * @param  object die      The die whose recipe is to be displayed
 * @param  string player   Whose is the die? ('player' or 'opponent')
 * @return object          jQuery containing the recipe DIV
 */
Game.createGameMatDieRecipeDiv = function(die, player) {
  var dieRecipeDiv = $('<div>');
  dieRecipeDiv.append($('<span>', {
    'class': 'die_recipe_' + player,
    'text': Game.dieRecipeText(die),
  }));
  return dieRecipeDiv;
};

/**
 * Return a div containing a die for use in a game battle mat.
 *
 * This function creates the circular die image itself, and any
 * background or decoration which attaches directly to the image.
 *
 * @param  object  die          The die whose recipe is to be displayed
 * @param  string  player       Whose is the die? ('player' or 'opponent')
 * @param  string  dieStatus    Status of the die ('active' or 'captured')
 * @param  boolean isClickable  Is the die clickable?
 * @return object               jQuery containing the die DIV
 */
Game.createGameMatDieDiv = function(die, player, dieStatus, isClickable) {
  var divOpts = {
    'class': 'die_img',
  };
  if ((dieStatus == 'active') && !isClickable) {
    divOpts['class'] += ' die_greyed';
  }

  var dieNumberSpanOpts = {
    'class': 'die_overlay die_number_' + player,
  };
  if (dieStatus == 'active') {
    dieNumberSpanOpts.text = die.value;
  } else {
    dieNumberSpanOpts.html = '&nbsp;' + die.value + '&nbsp;';
  }

  var dieDiv = $('<div>', divOpts);
  dieDiv.append($('<span>', dieNumberSpanOpts));
  return dieDiv;
};

/**
 * Return a div containing a border for a die.
 *
 * If the die is not currently selected, insert a border in the
 * background color of the player's mat, to hide the visible red border.
 *
 * @param  string  player       Whose is the die? ('player' or 'opponent')
 * @param  boolean isSelected   Is the die currently selected?
 * @return object               jQuery containing the border DIV
 */
Game.createGameMatBorderDiv = function(player, isSelected) {
  var borderDivOpts = {
    'class': 'die_border',
  };
  if (!isSelected) {
    borderDivOpts.style = 'border: 2px solid ' + Game.color[player];
  }

  var dieBorderDiv = $('<div>', borderDivOpts);
  return dieBorderDiv;
};

/**
 * Return a div containing a die with a selectable border,
 * for use in a game battle mat.
 *
 * This is a thin wrapper which creates the die div, creates
 * the border div, and attaches the die to the border.
 *
 * @param  object  die          The die whose recipe is to be displayed
 * @param  string  player       Whose is the die? ('player' or 'opponent')
 * @param  string  dieStatus    Status of the die ('active' or 'captured')
 * @param  boolean isClickable  Is the die clickable?
 * @param  boolean isSelected   Is the die currently selected?
 * @return object               jQuery containing the die/border DIV
 */
Game.createGameMatDieWithBorderDiv = function(
    die, player, dieStatus, isClickable, isSelected) {
  var dieDiv = Game.createGameMatDieDiv(die, player, dieStatus, isClickable);
  var dieBorderDiv = Game.createGameMatBorderDiv(player, isSelected);
  dieBorderDiv.append(dieDiv);
  return dieBorderDiv;
};

/**
 * Return a dict containing the set of attributes to be attached to the
 * outside container for any battle mat die
 *
 * @param  object  die              The die whose recipe is to be displayed
 * @param  string  player           Whose is the die? ('player' or 'opponent')
 * @param  boolean player_active    Is the game awaiting action from the player
 *                                  who is loading the page?
 * @param  string  dieStatus        Status of the die ('active' or 'captured')
 * @param  string  dieIndex         Backend index for this die, or null if the
 *                                  die is captured
 * @param  object  dieClickableInfo Whether the die is clickable, and why
 * @return object                   dict containing the div options to use
 */
Game.getDieContainerDivOptions = function(
    die, player, player_active, dieStatus, dieIndex, dieClickableInfo,
    isSelected) {
  var divOptions = {
    'class': 'die_container',
  };
  if (dieIndex) {
    divOptions.id = dieIndex;
  }

  // Set descriptions for active, captured, and unclickable dice
  if (dieStatus == 'captured') {
    divOptions.title = dieClickableInfo.reason;
  } else {
    divOptions.title = die.description;
    if (player_active && !dieClickableInfo.isClickable) {
      divOptions.title += '. (' + dieClickableInfo.reason + ')';
    }
  }

  if (dieStatus == 'active') {
    divOptions.class += ' die_alive';
  } else {
    divOptions.class += ' die_dead';
  }

  // configure clickable dice to be selectable via keyboard,
  // and indicate whether they are currently selected
  if (dieClickableInfo.isClickable) {
    divOptions.tabIndex = 0;
    divOptions.class += ' hide_focus';
    if (isSelected) {
      divOptions.class += ' selected';
    } else {
      divOptions.class += ' unselected_' + player;
    }
  }

  return divOptions;
};

/**
 * Return a filled-in "container" div for a die on a battle mat
 *
 * This function fully renders a die, including its image, its
 * recipe, its border, and all needed HTML class attributes, for
 * inclusion on a battle mat.  It is largely a wrapper which calls
 * other functions and assembles the divs they return.
 *
 * @param  object  die              The die which is to be displayed
 * @param  string  player           Whose is the die? ('player' or 'opponent')
 * @param  boolean player_active    Is the game awaiting action from the player
 *                                  who is loading the page?
 * @param  string  dieStatus        Status of the die ('active' or 'captured')
 * @param  string  dieIndex         Backend index for this die, or null if the
 *                                  die is captured
 * @param  object  dieClickableInfo Whether the die is clickable, and why
 * @param  boolean isSelected       Is the die currently selected?
 * @return object                   jQuery containing the die container DIV
 */
Game.createDieContainerDiv = function(
    die, player, player_active, dieStatus, dieIndex, dieClickableInfo,
    isSelected) {
  var containerDivOpts = Game.getDieContainerDivOptions(
    die, player, player_active, dieStatus, dieIndex, dieClickableInfo,
    isSelected);
  var dieBorderDiv = Game.createGameMatDieWithBorderDiv(
    die, player, dieStatus, dieClickableInfo.isClickable, isSelected);
  var dieRecipeDiv = Game.createGameMatDieRecipeDiv(die, player);

  var dieContainerDiv = $('<div>', containerDivOpts);

  // If the dice belong to the player, the recipe is above the dice.
  // If the dice belong to the opponent, the recipe is below the dice.
  if (player == 'player') {
    dieContainerDiv.append(dieRecipeDiv);
    dieContainerDiv.append(dieBorderDiv);
  } else {
    dieContainerDiv.append(dieBorderDiv);
    dieContainerDiv.append(dieRecipeDiv);
  }

  // If the die is clickable, install keyboard handlers
  if (dieClickableInfo.isClickable) {
    if (player == 'player') {
      Env.addClickKeyboardHandlers(
        dieContainerDiv,
        Game.dieBorderTogglePlayerHandler,
        Game.dieBorderTogglePlayerHandler,
        Game.form
      );
    } else {
      Env.addClickKeyboardHandlers(
        dieContainerDiv,
        Game.dieBorderToggleOpponentHandler,
        Game.dieBorderToggleOpponentHandler,
        Game.form
      );
    }
    Game.dieFocusOutlineHandler(dieContainerDiv);
  }
  return dieContainerDiv;
};


/**
 * Return a game-mat-style display of all dice belonging to the
 * requested player
 *
 * @param  string  player        Whose dice to display ('player' or 'opponent')
 * @param  boolean player_active Is the game awaiting action from the player
 *                               who is loading the page?
 * @return object                jQuery containing the div of dice
 */
Game.gamePlayerDice = function(player, player_active) {
  var nonplayer;
  if (player == 'player') {
    nonplayer = 'opponent';
  } else {
    nonplayer = 'player';
  }
  var allDice = $('<div>', {
    'class': 'dice_' + player,
  });

  var die;
  var dieIndex;
  var dieClickableInfo;
  var isSelected;
  var dieContainerDiv;

  for (var i = 0; i < Api.game[player].activeDieArray.length; i++) {
    die = Api.game[player].activeDieArray[i];
    dieIndex = Game.dieIndexId(player, i);
    dieClickableInfo = Game.dieClickableInfo(
      die, player, 'active', player_active);
    isSelected = (('dieSelectStatus' in Game.activity) &&
      (dieIndex in Game.activity.dieSelectStatus) &&
      Game.activity.dieSelectStatus[dieIndex]);

    dieContainerDiv = Game.createDieContainerDiv(
      die, player, player_active, 'active', dieIndex,
      dieClickableInfo, isSelected);
    allDice.append(dieContainerDiv);
  }

  // Loop over all of the captured dice and display any that are flagged as
  // having been captured just now.
  $.each(Api.game[nonplayer].capturedDieArray, function(i, die) {
    if (die.properties.indexOf('WasJustCaptured') >= 0) {
      dieClickableInfo = Game.dieClickableInfo(
        die, player, 'captured', player_active);

      dieContainerDiv = Game.createDieContainerDiv(
	die, player, player_active, 'captured', null,
        dieClickableInfo, false);
      allDice.append(dieContainerDiv);
    }
  });

  return allDice;
};

// Show the winner of a completed game
Game.gameWinner = function() {

  var playerWins = Api.game.player.gameScoreArray.W;
  var opponentWins = Api.game.opponent.gameScoreArray.W;
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
    'class': 'winner_name',
    'html': $('<b>', { 'text': winnerText, }),
  }));
  return winnerDiv;
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
  var playerInfo = $('<th>', { 'text': prefix, });
  var opponentInfo = $('<th>', { 'text': prefix, });
  if (field == 'playerName') {
    if (Api.game.player.isOnVacation) {
      playerInfo.append(Env.buildVacationImage());
    }
    playerInfo.append(Env.buildProfileLink(Api.game.player[field]));
    if (Api.game.opponent.isOnVacation) {
      opponentInfo.append(Env.buildVacationImage());
    }
    opponentInfo.append(Env.buildProfileLink(Api.game.opponent[field]));
  } else if (field == 'buttonName') {
    playerInfo.append(Env.buildButtonLink(Api.game.player[field]));
    opponentInfo.append(Env.buildButtonLink(Api.game.opponent[field]));
  } else if (field == 'gameScoreStr') {
    playerInfo.append(Game.playerWLTText('player'));
    opponentInfo.append(Game.playerWLTText('opponent'));
  } else {
    playerInfo.append(Api.game.player[field]);
    opponentInfo.append(Api.game.opponent[field]);
  }

  headerrow.append(playerInfo);
  headerrow.append(opponentInfo);
  return headerrow;
};

// If the recipe doesn't contain (sides), assume there are swing
// or option dice in the recipe, so we need to specify the current
// number of sides
Game.dieRecipeText = function(die, allowShowValues) {
  var dieRecipeText = die.recipe;
  if (die.sides !== undefined && die.sides !== null) {
    var lparen = die.recipe.indexOf('(');
    var rparen = die.recipe.indexOf(')');
    var recipeSideString = die.recipe.substring(lparen + 1, rparen);
    var recipeSideOptionStrings = recipeSideString.split('/');
    if (recipeSideOptionStrings.length > 1) {
      dieRecipeText = dieRecipeText.replace(')', '=' + die.sides + ')');
    } else {
      var recipeSideTwinStrings = recipeSideString.split(',');

      var sidesum = 0;
      var swingcount = 0;
      for (var i = 0; i < recipeSideTwinStrings.length; i++) {
        var itemSides = parseInt(recipeSideTwinStrings[i], 10);
        if (isNaN(itemSides)) {
          swingcount += 1;
        } else {
          sidesum += itemSides;
        }
      }

      if (swingcount > 0) {
        var subdieRecipeArray = [];
        var subdieSidesArray = [];
        var subdieIdx;

        if (die.subdieArray !== undefined && die.subdieArray !== null) {
          for (subdieIdx = 0;
               subdieIdx < recipeSideTwinStrings.length;
               subdieIdx++) {
            subdieSidesArray[subdieIdx] = die.subdieArray[subdieIdx].sides;
          }
        } else {
          // continue to handle old cases where there is no explicit
          // information about subdice
          for (subdieIdx = 0;
               subdieIdx < recipeSideTwinStrings.length;
               subdieIdx++) {
            subdieSidesArray[subdieIdx] = die.sides/swingcount;
          }
        }

        for (subdieIdx = 0;
             subdieIdx < recipeSideTwinStrings.length;
             subdieIdx++) {
          subdieRecipeArray[subdieRecipeArray.length] =
            recipeSideTwinStrings[subdieIdx] + '=' +
            subdieSidesArray[subdieIdx];
        }
        dieRecipeText =
          dieRecipeText.replace(/\(.+\)/, '(' +
          subdieRecipeArray.join(',') + ')');
      }
    }
  }

  if ((typeof allowShowValues !== 'undefined') &&
      (allowShowValues === true) &&
      ('properties' in die) &&
      ('indexOf' in die.properties) &&
      (die.properties.indexOf('ValueRelevantToScore') >= 0)) {
    dieRecipeText += ':' + die.value;
  }

  return dieRecipeText;
};

Game.dieValidTurndownValues = function(die, gameState) {
  // Focus dice can be turned down during "react to initiative" state
  // Fire dice can be turned down during "adjust fire dice" state
  if (((die.skills.indexOf('Focus') >= 0) &&
       (gameState == Game.GAME_STATE_REACT_TO_INITIATIVE)) ||
      ((die.skills.indexOf('Fire') >= 0) &&
       (gameState == Game.GAME_STATE_ADJUST_FIRE_DICE))) {
    var turndown = [];
    var minval = 1;
    if (die.recipe.match(',')) {
      minval = 2;
    }
    for (var i = die.value - 1; i >= minval; i--) {
      turndown.push(i);
    }
    return turndown;
  }
  return [];
};

Game.dieCanRerollForInitiative = function(die) {
  if (die.skills.indexOf('Chance') >= 0) {
    return true;
  }
  return false;
};

// The border toggling behavior is complex because of these requirements:
// * the border must be defined with the same width both when the die is
//   selected and when it isn't --- otherwise, the die jumps on the page
// * when the die is unselected, the border must be colored to match
//   the background, which is dynamic (known only to JS, not to CSS)
// * when the die is unselected, the border must have rounded edges,
//   so that the rightmost player die and leftmost opponent die
//   don't have their invisible borders overlap the rounded edge
//   of that player's mat section.  when the die selected, the
//   border must be square
// and also because of these implementation annoyances:
// * $(this).toggleClass() is a very nice way to toggle class
//   definitions --- there isn't anything nearly as tidy for toggling
//   inline style properties
// * the initial die definition (in Game.gamePlayerDice) doesn't
//   go through this function, so we have to duplicate the attributes here
//
// Summary of the solution used:
// * the static style elements (selected die border shape/color and
//   unselected die shape) are defined in CSS as the classes
//   "selected" and "unselected_{player,opponent}"
// * the unselected color is defined by an override of the "style"
//   attribute (which then needs to be cleared when the die becomes
//   selected)
// * when the die is defined in Game.gamePlayerDice, the class and
//   overrides are set correctly for that die's state at creation time
// * when the die is toggled, toggleClass() is used to toggle the
//   class, then item's class memberships are used to decide whether
//   to add or remove the style override
// Simplify on your own time, and at your peril!

Game.dieBorderTogglePlayerHandler = function() {
  $(this).toggleClass('selected unselected_player');
  var borderDiv = $(this).find('.die_border');
  if ($(this).hasClass('unselected_player')) {
    borderDiv.attr('style', 'border: 2px solid ' + Game.color.player);
  } else {
    borderDiv.attr('style', '');
  }
};

Game.dieBorderToggleOpponentHandler = function() {
  $(this).toggleClass('selected unselected_opponent');
  var borderDiv = $(this).find('.die_border');
  if ($(this).hasClass('unselected_opponent')) {
    borderDiv.attr('style', 'border: 2px solid ' + Game.color.opponent);
  } else {
    borderDiv.attr('style', '');
  }
};

// The selected value is the first value provided, and is not part
// of the array
Game.dieValueSelectTd = function(
     selectname, valuearray, maxval, selectedval) {
  var selectTd = $('<td>');
  var select = $('<select>', {
    'id': selectname,
    'name': selectname,
    'class': 'center',
  });
  var valoption = {
    'value': maxval,
    'label': maxval,
    'text': maxval,
  };
  if (maxval == selectedval) {
    valoption.selected = 'selected';
  }
  select.append($('<option>', valoption));

  $.each(valuearray, function(idx) {
    valoption = {
      'value': valuearray[idx],
      'label': valuearray[idx],
      'text': valuearray[idx],
    };
    if (valuearray[idx] == selectedval) {
      valoption.selected = 'selected';
    }
    select.append($('<option>', valoption));
  });
  selectTd.append(select);
  return selectTd;
};

Game.chatBox = function(hidden) {
  var chattable = $('<table>');
  if (hidden) {
    chattable.addClass('hiddenChatForm');
  }
  var chatrow = $('<tr>');
  chatrow.append($('<td>', {'text': 'Chat:', }));
  var chattd = $('<td>');
  var chatarea = $('<textarea>', {
    'id': 'game_chat',
    'rows': '3',
    'cols': '50',
    'maxlength': Game.GAME_CHAT_MAX_LENGTH,
  });

  // Add previous chat contents from a rejected turn submission if any
  if ('chat' in Game.activity) {
    chatarea.val(Game.activity.chat);
  }
  chattd.append(chatarea);
  chatrow.append(chattd);
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

// if tab is released while focused on any die (i.e. if we tab to
// any die), remove the hide_focus class from all dice
Game.dieFocusOutlineHandler = function(element) {
  element.keyup(function(eventData) {
    if (eventData.which == Env.KEYCODE_TAB) {
      $('.die_container').removeClass('hide_focus');
    }
  });
};
