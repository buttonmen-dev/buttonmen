// namespace for this "module"
var Game = {
  'activity': {},
};

// Game states must match those reported by the API
Game.GAME_STATE_START_GAME = 'START_GAME';
Game.GAME_STATE_APPLY_HANDICAPS = 'APPLY_HANDICAPS';
Game.GAME_STATE_CHOOSE_AUXILIARY_DICE = 'CHOOSE_AUXILIARY_DICE';
Game.GAME_STATE_CHOOSE_RESERVE_DICE = 'CHOOSE_RESERVE_DICE';
Game.GAME_STATE_LOAD_DICE_INTO_BUTTONS = 'LOAD_DICE_INTO_BUTTONS';
Game.GAME_STATE_ADD_AVAILABLE_DICE_TO_GAME = 'ADD_AVAILABLE_DICE_TO_GAME';
Game.GAME_STATE_SPECIFY_DICE = 'SPECIFY_DICE';
Game.GAME_STATE_DETERMINE_INITIATIVE = 'DETERMINE_INITIATIVE';
Game.GAME_STATE_REACT_TO_INITIATIVE = 'REACT_TO_INITIATIVE';
Game.GAME_STATE_START_ROUND = 'START_ROUND';
Game.GAME_STATE_START_TURN = 'START_TURN';
Game.GAME_STATE_ADJUST_FIRE_DICE = 'ADJUST_FIRE_DICE';
Game.GAME_STATE_END_TURN = 'END_TURN';
Game.GAME_STATE_END_ROUND = 'END_ROUND';
Game.GAME_STATE_END_GAME = 'END_GAME';

// Convenience HTML used in the mat layout to break text
Game.SPACE_BULLET = ' &nbsp;&bull;&nbsp; ';

// Default number of action and chat log entries to display
Game.logEntryLimit = 10;

// Maximum number of characters permitted in a given chat message
Game.GAME_CHAT_MAX_LENGTH = 500;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Game.showGamePage() is the landing function.  Always call this first
// * Game.getCurrentGame() asks the API for information about the
//   requested game.  It clobbers Api.game.  If successful, it calls
// * Game.showStatePage() determines what action to take next based on
//   the received data from getCurrentGame().  It calls one of several
//   functions, Game.action<SomeAction>()
// * each Game.action<SomeAction>() function must set Game.page and
//   Game.form, then call Game.arrangePage()
// * Game.arrangePage() sets the contents of <div id="game_page"> on the
//   live page
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Game.showGamePage = function() {

  // Setup necessary elements for displaying status messages
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#game_page').length === 0) {
    $('body').append($('<div>', {'id': 'game_page', }));
  }

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

  Env.callAsyncInParallel(
    [
      Api.getPendingGameCount,
      { 'func': Api.getGameData, 'args': [ Game.game, Game.logEntryLimit ] },
    ], callbackfunc);
};

// Assemble and display the game portion of the page
Game.showStatePage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  // Set colors for use in game
  Game.color = {
    'player': Api.game.player.playerColor,
    'opponent': Api.game.opponent.playerColor,
  };

  // Figure out what to do next based on the game state
  if (Api.game.load_status == 'ok') {
    if (Api.game.gameState == Game.GAME_STATE_SPECIFY_DICE) {
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
      Game.arrangePage();
    } else {
      Game.page =
        $('<p>', {'text': 'Can\'t figure out what action to take next', });
      Game.form = null;
      Game.arrangePage();
    }
  } else {

    // Game retrieval failed, so just layout the page with no contents
    // and whatever message was received while trying to load the game
    Game.page = null;
    Game.form = null;
    Game.arrangePage();
  }
};

Game.arrangePage = function() {
  if ($('#game_page').length === 0) {
    throw('Internal error: #game_page not defined in arrangePage()');
  }

  $('#game_page').empty();
  $('#game_page').append(Game.page);

  // If a game form is specified, activate the game form on mouse click.
  // (The form will automatically be invoked when the player presses
  // the return key as well.)
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
  var diespecifytable = $('<table>', { 'id': 'die_specify_table', });

  // Add swing dice to table
  $.each(
    Api.game.player.swingRequestArray,
    function(letter, range) {
      var swingrow = $('<tr>', {});
      var swingtext = letter + ' (' + range.min + '-' + range.max + '):';
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
      var swingprevtext = '';
      if (letter in Api.game.player.prevSwingValueArray) {
        swingprevtext =
          '(was: ' + Api.game.player.prevSwingValueArray[letter] + ')';
      }
      swingrow.append($('<td>', { 'text': swingprevtext, }));
      diespecifytable.append(swingrow);
    });

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
  var opponentswing = $('<table>', { 'id': 'opponent_swing', });
  $.each(
    Api.game.opponent.swingRequestArray,
    function(letter, range) {
      var swingrow = $('<tr>', {});
      var swingtext = letter + ': (' + range.min + '-' + range.max + ')';
      swingrow.append($('<td>', { 'text': swingtext, }));
      opponentswing.append(swingrow);
    });

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
  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
};

Game.actionSpecifyDiceInactive = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader('Opponent\'s turn to choose die sizes');

  var dietable = Game.dieRecipeTable(false);
  Game.page.append(dietable);

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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
  var dietable = Game.dieRecipeTable(false, false);

  auxform.append(dietable);
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
  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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
  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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
  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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
  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  for (var i = 0; i < Api.game.validAttackTypeArray.length; i++) {
    var attacktype = Api.game.validAttackTypeArray[i];
    var typetext;
    if ((attacktype == 'Pass') || (attacktype === '')) {
      typetext = attacktype;
    } else if (attacktype == 'Surrender') {
      typetext = 'SURRENDER!?';
    } else {
      typetext = attacktype + ' Attack';
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
  attackform.append(attacktypeselect);

  attackform.append($('<button>', {
    'id': 'game_action_button',
    'text': 'Beat People UP!',
  }));
  attackdiv.append(attackform);
  Game.page.append(attackdiv);

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  if (Api.game.chatEditable) {
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

  Game.pageAddFooter(true);

  // Now layout the page
  Game.arrangePage();
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
  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
};

Game.actionAdjustFireDiceActive = function() {

  // Function to invoke on button click
  Game.form = Game.formAdjustFireDiceActive;

  Game.parseValidFireOptions();
  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Your turn to complete an attack by adjusting fire dice');

  var attackerSum = 0;
  $.each(Api.game.player.activeDieArray, function(i, die) {
    if (die.properties.indexOf('IsAttacker') >= 0) {
      attackerSum += die.value;
    }
  });

  var defenderSum = 0;
  $.each(Api.game.opponent.activeDieArray, function(i, die) {
    if (die.properties.indexOf('IsAttackTarget') >= 0) {
      defenderSum += die.value;
    }
  });

  Game.page.append($('<div>', {
    'text': 'Turn down Fire dice by a total of ' +
            (defenderSum - attackerSum) +
            ' to make up the difference between the sum of your attacking' +
            ' dice (' + attackerSum + ') and the defending die value (' +
            defenderSum + ').',
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

  var fireoptions = {
    'turndown': 'Turn down fire dice',
    'cancel':
      'Don\'t turn down fire dice (cancelling the attack in progress)',
  };
  $.each(fireoptions, function(actionname, actiontext) {
    var fireactionopts = {
      'value': actionname,
      'label': actiontext,
      'text': actiontext,
    };
    if (actionname == 'turndown') {
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
  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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

  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
};

Game.actionShowFinishedGame = function() {

  // nothing to do on button click
  Game.form = null;

  Game.page = $('<div>');
  Game.pageAddGameHeader('This game is over');

  Game.page.append(Game.gameWinner());
  Game.page.append($('<br>'));

  var dieEndgameTable = $('<table>');
  var dieEndgameTr = $('<tr>');

  var playerButtonTd = Game.buttonImageDisplay('player');
  var opponentButtonTd = Game.buttonImageDisplay('opponent');

  dieEndgameTr.append(playerButtonTd);
  dieEndgameTr.append(opponentButtonTd);
  dieEndgameTable.append(dieEndgameTr);
  Game.page.append(dieEndgameTable);
  Game.logEntryLimit = undefined;
  Game.pageAddFooter();

  // Now layout the page
  Game.arrangePage();
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
      'game_action_button',
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
      'game_action_button',
      Game.showGamePage,
      Game.showGamePage
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': 'Could not parse decision to use or not use auxiliary dice',
    };
    Game.showGamePage();
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
      'game_action_button',
      Game.showGamePage,
      Game.showGamePage
    );
  } else {
    Env.message = {
      'type': 'error',
      'text': inputError,
    };
    Game.showGamePage();
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
      'game_action_button',
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

// Form submission action for adjusting fire dice
Game.formAdjustFireDiceActive = function() {
  var formValid = true;
  var error = false;
  Game.activity.fireActionType = $('#fire_action_select').val();
  Game.activity.fireDieIdxArray = [];
  Game.activity.fireDieValueArray = [];

  switch (Game.activity.fireActionType) {

  // valid action, nothing special to do, but validate selections just in case
  case 'cancel':
    $.each(Api.game.player.fireOptions, function(i) {
      var value = $('#fire_adjust_' + i).val();
      if (value != Api.game.player.activeDieArray[i].value) {
        error = 'Chose not to adjust fire dice, but modified a die value';
        formValid = false;
      }
    });
    break;

  case 'turndown':
    $.each(Api.game.player.fireOptions, function(i, vals) {
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
      { 'ok':
        {
          'type': 'fixed',
          'text': 'Successfully completed attack by turning down fire dice',
        },
        'notok': { 'type': 'server', },
      },
      'game_action_button',
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
    'game_action_button',
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
    'game_action_button',
    Game.redrawGamePageSuccess,
    Game.redrawGamePageFailure
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
  Game.showGamePage();
};

////////////////////////////////////////////////////////////////////////
// Page layout helper routines

// Display header information about the game
Game.pageAddGameHeader = function(action_desc) {
  var gameTitle =
    'Game #' + Api.game.gameId + Game.SPACE_BULLET +
      Api.game.player.playerName + ' (' + Api.game.player.button.name +
      ') vs. ' + Api.game.opponent.playerName + ' (' +
      Api.game.opponent.button.name + ') ' + Game.SPACE_BULLET +
      'Round #' + Api.game.roundNumber;
  $('title').html('Button Men Online &mdash; ' + gameTitle);

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

  var descspan = $('<span>', {
    'id': 'action_desc_span',
    'class': 'action_desc_span',
    'style': 'background: none repeat scroll 0 0 ' + bgcolor,
    'text': action_desc,
  });
  var descdiv = $('<div>', { 'class': 'action_desc_div', });
  descdiv.append(descspan);
  Game.page.append(descdiv);
  Game.page.append($('<br>'));

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
  Game.pageAddGameNavigationFooter();
  Game.pageAddUnhideChatButton(isChatHidden);
  Game.pageAddSkillListFooter();
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
    'href': 'javascript: Api.getNextGameId(Login.goToNextPendingGame);',
    'text': 'Go to your next pending game ' + countText,
  }));
  Game.page.append(linkDiv);
  return true;
};

// Display a footer-style message with the list of skills in this game
Game.pageAddSkillListFooter = function() {
  var gameSkillDiv = $('<div>', {
    'text': 'Die skills in this game: ',
  });

  var firstSkill = true;
  var firstInteract;
  var skillDesc;
  $.each(Api.game.gameSkillsInfo, function(skill, info) {
    skillDesc = skill + ' (' + info.code + '): ' + info.description;

    firstInteract = true;
    $.each(info.interacts, function(otherSkill, interactDesc) {
      if (firstInteract) {
        skillDesc += '\n\nInteraction with other skills in this game:';
      }
      skillDesc += '\n * ' + otherSkill + ': ' + interactDesc;
    });

    if (!(firstSkill)) {
      gameSkillDiv.append('&nbsp;&nbsp;');
    }
    gameSkillDiv.append($('<span>', {
      'text': skill,
      'title': skillDesc,
      'class': 'skill_desc',
    }));
    gameSkillDiv.append($('<span>', {
      'text': 'i',
      'title': skillDesc,
      'class': 'skill_desc_i',
    }));
    firstSkill = false;
  });

  if (firstSkill) {
    gameSkillDiv.append('none');
  }

  Game.page.append($('<br>'));
  Game.page.append(gameSkillDiv);
  return true;
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
        if (logentry.message.indexOf(Api.game.player.playerName + ' ') === 0) {
          actionplayer = 'player';
        } else {
          actionplayer = 'opponent';
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
        } else {
          chatplayer = 'opponent';
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

    if (Game.logEntryLimit !== undefined) {
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
            'text': Api.game.player.activeDieArray[i].value,
          }));
        }
        dieRRow.append(opponentEnt);
        dieRRow.append($('<td>', {
          'text': Api.game.opponent.activeDieArray[i].value,
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

Game.dieTableEntry = function(i, activeDieArray) {
  if (i < activeDieArray.length) {
    var die = activeDieArray[i];
    var dieval = Game.dieRecipeText(die.recipe, die.sides);
    var dieopts = {
      'text': dieval,
      'title': die.description,
    };
    if ((die.properties.indexOf('dizzy') >= 0) &&
        (die.skills.indexOf('Focus') >= 0)) {
      dieopts.class = 'recipe_greyed';
      dieopts.title += '. (This die is dizzy because it has been turned ' +
        'down.  If the owner wins initiative, this die can\'t be used in ' +
        'their first attack.)';
    } else if ((die.properties.indexOf('disabled') >= 0) &&
               (die.skills.indexOf('Chance') >= 0)) {
      dieopts.class = 'recipe_greyed';
      dieopts.title += '. (This chance die cannot be rerolled again ' +
        'during this round, because the player has already rerolled a ' +
        'chance die)';
    } else if (die.properties.indexOf('IsAttacker') >= 0) {
      dieopts.class = 'recipe_inuse';
      dieopts.title += '. (This die is an attacker in the attack which ' +
        'is currently in progress.)';
    } else if (die.properties.indexOf('IsAttackTarget') >= 0) {
      dieopts.class = 'recipe_inuse';
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
  if (Api.game.gameState == Game.GAME_STATE_END_GAME) {
    tdClass += ' button_postgame';
  }
  var buttonTd = $('<td>', {
    'class': tdClass,
    'style': 'background: ' + Game.color[player],
  });
  var playerName = $('<div>', {
    'html': $('<b>', { 'text': 'Player: ', }),
  });
  playerName.append(Env.buildProfileLink(Api.game[player].playerName));
  var playerWLT = $('<div>', {
    'text': Api.game[player].gameScoreStr,
  });
  var buttonInfo = $('<div>', {
    'text': 'Button: ' + Api.game[player].button.name,
  });
  var buttonRecipe = $('<div>', {
    'text': Api.game[player].button.recipe,
  });

  if (player == 'opponent' && Api.game.gameState != Game.GAME_STATE_END_GAME) {
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
  if (player == 'player' || Api.game.gameState == Game.GAME_STATE_END_GAME) {
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
  var gameScoreDiv = $('<div>', { 'html': Api.game[player].gameScoreStr, });

  var capturedDiceDiv;
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
        capturedDieDescs.push(Game.dieRecipeText(die.recipe, die.sides));
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
      statusDiv.append(capturedDiceDiv);
    }
    statusDiv.append(gameScoreDiv);

  } else {
    statusDiv.append(gameScoreDiv);
    if (game_active) {
      statusDiv.append(capturedDiceDiv);
    }
  }
  return statusDiv;
};

// Return a display of all dice for the requested player, specifying
// whether the dice should be selectable
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

  var dieDiv;
  var dieRecipeDiv;
  var dieContainerDiv;
  var dieBorderDiv;

  var dieRecipeText;

  for (var i = 0; i < Api.game[player].activeDieArray.length; i++) {
    var die = Api.game[player].activeDieArray[i];

    // Find out whether this die is clickable: it is if the player
    // is active and this particular die is not dizzy
    var clickable;
    if (player_active) {
      if (die.properties.indexOf('dizzy') >= 0) {
        clickable = false;
      } else {
        clickable = true;
      }
    } else {
      clickable = false;
    }

    var dieIndex = Game.dieIndexId(player, i);

    var containerDivOpts = {
      'id': dieIndex,
      'title': die.description,
    };
    var borderDivOpts = {
      'class': 'die_border',
    };
    var divOpts = { };

    if (clickable) {
      // clickable dice should be selectable via keyboard as well
      containerDivOpts.tabIndex = 0;
      if (('dieSelectStatus' in Game.activity) &&
          (dieIndex in Game.activity.dieSelectStatus) &&
          (Game.activity.dieSelectStatus[dieIndex])) {
        containerDivOpts.class = 'hide_focus die_container die_alive selected';
      } else {
        containerDivOpts.class =
          'hide_focus die_container die_alive unselected_' + player;
        borderDivOpts.style = 'border: 2px solid ' + Game.color[player];
      }
      divOpts.class = 'die_img';
      dieContainerDiv = $('<div>', containerDivOpts);
      dieBorderDiv = $('<div>', borderDivOpts);
      dieDiv = $('<div>', divOpts);
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
    } else {
      borderDivOpts.style = 'border: 2px solid ' + Game.color[player];
      divOpts.class = 'die_img die_greyed';
      if (player_active) {
        containerDivOpts.title +=
          '. (This die is dizzy because it was turned ' +
          'down.  It can\'t be used during this attack.)';
      }
      containerDivOpts.class = 'die_container die_alive';
      dieContainerDiv = $('<div>', containerDivOpts);
      dieBorderDiv = $('<div>', borderDivOpts);
      dieDiv = $('<div>', divOpts);
    }
    dieDiv.append($('<span>', {
      'class': 'die_overlay die_number_' + player,
      'text': die.value,
    }));

    dieRecipeText = Game.dieRecipeText(die.recipe, die.sides);
    dieRecipeDiv = $('<div>');
    dieRecipeDiv.append($('<span>', {
      'class': 'die_recipe_' + player,
      'text': dieRecipeText,
    }));

    dieBorderDiv.append(dieDiv);
    if (player == 'player') {
      dieContainerDiv.append(dieRecipeDiv);
      dieContainerDiv.append(dieBorderDiv);
    } else {
      dieContainerDiv.append(dieBorderDiv);
      dieContainerDiv.append(dieRecipeDiv);
    }
    allDice.append(dieContainerDiv);
  }

  // Loop over all of the captured dice and display any that are flagged as
  // having been captured just now.
  $.each(Api.game[nonplayer].capturedDieArray, function(i, die) {
    if (die.properties.indexOf('WasJustCaptured') >= 0) {
      dieContainerDiv = $('<div>', {
        'class': 'die_container die_dead' ,
        'title':
          'This die was just captured in the last attack and is no longer ' +
          'in play.',
      });
      dieBorderDiv = $('<div>', {
        'class': 'die_border',
        'style': 'border: 2px solid ' + Game.color[player],
      });
      dieDiv = $('<div>', { 'class': 'die_img', });

      dieDiv.append($('<span>', {
        'class': 'die_overlay die_number_' + player,
        'html': '&nbsp;' + die.value + '&nbsp;',
      }));

      dieRecipeDiv = $('<div>');
      dieRecipeDiv.append($('<span>', {
        'class': 'die_recipe_' + player,
        'text': Game.dieRecipeText(die.recipe, die.sides),
      }));

      dieBorderDiv.append(dieDiv);
      if (player == 'player') {
        dieContainerDiv.append(dieRecipeDiv);
        dieContainerDiv.append(dieBorderDiv);
      } else {
        dieContainerDiv.append(dieBorderDiv);
        dieContainerDiv.append(dieRecipeDiv);
      }
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
    playerInfo.append(Env.buildProfileLink(Api.game.player[field]));
    opponentInfo.append(Env.buildProfileLink(Api.game.opponent[field]));
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
Game.dieRecipeText = function(recipe, sides) {
  var dieRecipeText = recipe;
  if (sides) {
    var lparen = recipe.indexOf('(');
    var rparen = recipe.indexOf(')');
    var recipeSideString = recipe.substring(lparen + 1, rparen);
    var recipeSideOptionStrings = recipeSideString.split('/');
    if (recipeSideOptionStrings.length > 1) {
      dieRecipeText = dieRecipeText.replace(')', '=' + sides + ')');
    } else {
      var recipeSideTwinStrings = recipeSideString.split(',');
      var sidesum = 0;
      var swingcount = 0;
      for (var i = 0; i < recipeSideTwinStrings.length; i++) {
        var itemSides = parseInt(recipeSideTwinStrings[i], 10);
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
