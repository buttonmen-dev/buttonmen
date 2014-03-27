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
Game.GAME_STATE_END_TURN = 'END_TURN';
Game.GAME_STATE_END_ROUND = 'END_ROUND';
Game.GAME_STATE_END_GAME = 'END_GAME';

// Convenience HTML used in the mat layout to break text
Game.SPACE_BULLET = ' &nbsp;&bull;&nbsp; ';

// Colors used by the game display
Game.COLORS = {
  'players': {
    'player': '#dd99dd',
    'opponent': '#ddffdd',
  },
};

// Default number of action and chat log entries to display
Game.logHistoryLength = 10;

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

  Api.getGameData(Game.game, Game.logHistoryLength, callbackfunc);
};

// Assemble and display the game portion of the page
Game.showStatePage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  // Set colors for use in game - for now, all games use the same colors
  Game.color = Game.COLORS.players;

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

// What reserve dice can the player choose
Game.parseValidReserveOptions = function() {
  Api.game.player.reserveOptions = {};
  if (Api.game.gameState == Game.GAME_STATE_CHOOSE_RESERVE_DICE) {

    $.each(Api.game.player.dieSkillsArray, function(i) {
      if ('Reserve' in Api.game.player.dieSkillsArray[i]) {
        Api.game.player.reserveOptions[i] = true;
      }
    });
  }
};

// What auxiliary dice will each player in this game get
Game.parseAuxiliaryDieOptions = function() {
  $.each(Api.game.player.dieSkillsArray, function(i) {
    if ('Auxiliary' in Api.game.player.dieSkillsArray[i]) {
      Api.game.player.auxiliaryDieIndex = i;
      Api.game.player.auxiliaryDieRecipe = Api.game.player.dieRecipeArray[i];
    }
  });
  $.each(Api.game.opponent.dieSkillsArray, function(i) {
    if ('Auxiliary' in Api.game.opponent.dieSkillsArray[i]) {
      Api.game.opponent.auxiliaryDieIndex = i;
      Api.game.opponent.auxiliaryDieRecipe =
        Api.game.opponent.dieRecipeArray[i];
    }
  });
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
  var swingtable = $('<table>', { 'id': 'swing_table', });
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

  // Add the swing die form to the left column of the die table
  var formtd = $('<td>', { 'class': 'chooseswing', });
  formtd.append($('<br>'));
  formtd.append(swingform);
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

Game.actionChooseAuxiliaryDiceActive = function() {
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

  // Function to invoke on button click
  Game.form = Game.formChooseAuxiliaryDiceActive;

  // Now layout the page
  Game.layoutPage();
};

Game.actionChooseAuxiliaryDiceInactive = function() {
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

  // Function to invoke on button click
  Game.form = null;

  // Now layout the page
  Game.layoutPage();
};

Game.actionChooseAuxiliaryDiceNonplayer = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader(
    'Waiting for ' + Game.waitingOnPlayerNames() +
    ' to decide whether to use auxiliary dice (you are not in this game)'
  );

  // Get a table containing the existing die recipes
  var dietable = Game.dieRecipeTable(false, false);

  Game.page.append(dietable);

  Game.pageAddFooter();

  // Function to invoke on button click
  Game.form = null,

  // Now layout the page
  Game.layoutPage();
};

Game.actionChooseReserveDiceActive = function() {
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

  // Function to invoke on button click
  Game.form = Game.formChooseReserveDiceActive;

  // Now layout the page
  Game.layoutPage();
};

Game.actionChooseReserveDiceInactive = function() {
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

  // Function to invoke on button click
  Game.form = null;

  // Now layout the page
  Game.layoutPage();
};

Game.actionChooseReserveDiceNonplayer = function() {
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

  // Function to invoke on button click
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
  var dietable = Game.dieRecipeTable('react_to_initiative', false);

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
  var dietable = Game.dieRecipeTable('react_to_initiative', false);

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
  Game.pageAddDieBattleTable(true);
  Game.page.append($('<br>'));

  var attackdiv = $('<div>');
  attackdiv.append(Game.chatBox());
  var attackform = $('<form>', {
    'id': 'game_action_form',
    'action': 'javascript:void(0);',
  });

  var validAttackTypes = [];
  $.each(Api.game.validAttackTypeArray, function(typename) {
    validAttackTypes.push(typename);
  });
  validAttackTypes.sort();
  // Surrender is a valid attack type, so add it at the end of the
  // list of options
  validAttackTypes.push('');
  validAttackTypes.push('Surrender');

  var attacktypeselect = $('<select>', {
    'id': 'attack_type_select',
    'name': 'attack_type_select',
  });
  for (var i = 0; i < validAttackTypes.length; i++) {
    var attacktype = validAttackTypes[i];
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

  // Function to invoke on button click
  Game.form = Game.formPlayTurnActive;

  // Now layout the page
  Game.layoutPage();
};

Game.actionPlayTurnInactive = function() {
  Game.page = $('<div>');
  Game.pageAddGameHeader('Opponent\'s turn to attack');
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
  Game.logHistoryLength = 0;
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
        if (value != Api.game.player.valueArray[i]) {
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

// Form submission action for playing a turn
Game.formPlayTurnActive = function() {
  Game.readCurrentGameActivity();

  // If surrender is chosen, ask for confirmation, and let the user
  // try again if they don't confirm
  if (Game.activity.attackType == 'Surrender') {
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

Game.readCurrentGameActivity = function() {
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
};

Game.showFullLogHistory = function() {
  Game.readCurrentGameActivity();
  Game.logHistoryLength = 0;
  Game.showGamePage();
};

////////////////////////////////////////////////////////////////////////
// Page layout helper routines

// Display header information about the game
Game.pageAddGameHeader = function(action_desc) {
  Game.page.append(
    $('<div>', {
      'id': 'game_id',
      'html':
	'Game #' + Api.game.gameId + Game.SPACE_BULLET +
	Api.game.player.playerName + ' (' + Api.game.player.buttonName +
	') vs. ' + Api.game.opponent.playerName + ' (' +
	Api.game.opponent.buttonName + ') ' + Game.SPACE_BULLET +
        'Round #' + Api.game.roundNumber,
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

    if (Game.logHistoryLength > 0) {
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
    if (table_action == 'react_to_initiative') {
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

  var maxDice = Math.max(Api.game.player.nDie, Api.game.opponent.nDie);
  for (var i = 0; i < maxDice; i++) {
    var playerEnt = Game.dieTableEntry(
      i, Api.game.player.nDie,
      Api.game.player.dieRecipeArray,
      Api.game.player.sidesArray,
      Api.game.player.diePropertiesArray,
      Api.game.player.dieSkillsArray,
      Api.game.player.dieDescriptionArray);
    var opponentEnt = Game.dieTableEntry(
      i, Api.game.opponent.nDie,
      Api.game.opponent.dieRecipeArray,
      Api.game.opponent.sidesArray,
      Api.game.opponent.diePropertiesArray,
      Api.game.opponent.dieSkillsArray,
      Api.game.opponent.dieDescriptionArray);
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
          var defaultval = Api.game.player.valueArray[i];
          if ('initiativeDieIdxArray' in Game.activity) {
            $.each(Game.activity.initiativeDieIdxArray, function(idx, val) {
              if (val == i) {
                defaultval = Game.activity.initiativeDieValueArray[idx];
              }
            });
          }
          dieLRow.append(
            Game.dieValueSelectTd('init_react_' + i, initopts,
              Api.game.player.valueArray[i], defaultval));
        } else {
          dieLRow.append($('<td>', { 'text': Api.game.player.valueArray[i] }));
        }
        dieRRow.append(opponentEnt);
        dieRRow.append($('<td>', { 'text': Api.game.opponent.valueArray[i] }));
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

Game.dieTableEntry = function(
  i, nDie, dieRecipeArray, dieSidesArray, diePropertiesArray,
  dieSkillsArray, dieDescriptionArray
) {
  if (i < nDie) {
    var dieval = Game.dieRecipeText(dieRecipeArray[i], dieSidesArray[i]);
    var dieopts = {
      'text': dieval,
      'title': dieDescriptionArray[i],
    };
    if (diePropertiesArray[i]) {
      if (('dizzy' in diePropertiesArray[i]) &&
          (diePropertiesArray[i].dizzy) &&
          ('Focus' in dieSkillsArray[i]) &&
          (dieSkillsArray[i].Focus)) {
        dieopts.class = 'recipe_greyed';
        dieopts.title += '. (This die is dizzy because it has been turned ' +
          'down.  If the owner wins initiative, this die can\'t be used in ' +
          'their first attack.)';
      } else if (('disabled' in diePropertiesArray[i]) &&
                 (diePropertiesArray[i].disabled) &&
                 ('Chance' in dieSkillsArray[i]) &&
                 (dieSkillsArray[i].Chance)) {
        dieopts.class = 'recipe_greyed';
        dieopts.title += '. (This chance dice cannot be rerolled again ' +
          'during this round, because the player has already rerolled a ' +
          'chance die)';
      }
    }
    return $('<td>', dieopts);
  }
  return $('<td>', {});
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
  diePlayerOverlayDiv.append($('<br>'));
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
  dieOpponentOverlayDiv.append($('<br>'));
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
  var buttonTd = $('<td>', {
    'class': 'button_' + player,
    'style': 'background: ' + Game.color[player],
  });
  var playerName = $('<div>', {
    'html': $('<b>', { 'text': 'Player: ' + Api.game[player].playerName }),
  });
  var playerWLT = $('<div>', {
    'text': Api.game[player].gameScoreStr,
  });
  var buttonInfo = $('<div>', {
    'text': 'Button: ' + Api.game[player].buttonName,
  });
  var buttonRecipe = $('<div>', {
    'text': Api.game[player].buttonRecipe,
  });

  if (player == 'opponent') {
    buttonTd.append(playerName);
    buttonTd.append(buttonInfo);
    buttonTd.append(buttonRecipe);
  } else if (Api.game.gameState == Game.GAME_STATE_END_GAME) {
    buttonTd.append(playerWLT);
  }
  buttonTd.append($('<img>', {
    'src':
      '/ui/images/button/' +
      Api.game[player].buttonName.toLowerCase().replace(/[^a-z0-9]/g, '') +
      '.png',
    'width': '150px',
    'onerror': 'this.src="/ui/images/button/BMdefaultRound.png"',
  }));
  if (player == 'player') {
    buttonTd.append(buttonRecipe);
    buttonTd.append(buttonInfo);
    buttonTd.append(playerName);
  } else if (Api.game.gameState == Game.GAME_STATE_END_GAME) {
    buttonTd.append(playerWLT);
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
  var i = 0;
  while (i < Api.game[player].nDie) {

    // Find out whether this die is clickable: it is if the player
    // is active and this particular die is not dizzy
    var clickable;
    if (player_active) {
      if (('dizzy' in Api.game[player].diePropertiesArray[i]) &&
          Api.game[player].diePropertiesArray[i].dizzy) {
        clickable = false;
      } else {
        clickable = true;
      }
    } else {
      clickable = false;
    }

    var dieDiv;
    var dieBorderDiv;

    var dieIndex = Game.dieIndexId(player, i);
    var borderDivOpts = {
      'id': dieIndex,
    };
    var divOpts = {
      'title': Api.game[player].dieDescriptionArray[i],
    };
    if (clickable) {
      if (('dieSelectStatus' in Game.activity) &&
          (dieIndex in Game.activity.dieSelectStatus) &&
          (Game.activity.dieSelectStatus[dieIndex])) {
        borderDivOpts.class = 'die_border selected';
      } else {
        borderDivOpts.class = 'die_border unselected_' + player;
        borderDivOpts.style = 'border: 2px solid ' + Game.color[player];
      }
      divOpts.class = 'die_img';
      dieBorderDiv = $('<div>', borderDivOpts);
      dieDiv = $('<div>', divOpts);
      if (player == 'player') {
        dieBorderDiv.click(Game.dieBorderTogglePlayerHandler);
      } else {
        dieBorderDiv.click(Game.dieBorderToggleOpponentHandler);
      }
    } else {
      divOpts.class = 'die_img die_greyed';
      if (player_active) {
        divOpts.title += '. (This die is dizzy because it was turned ' +
        'down.  It can\'t be used during this attack.)';
      }
      borderDivOpts.class = 'die_border';
      dieBorderDiv = $('<div>', borderDivOpts);
      dieDiv = $('<div>', divOpts);
    }
    dieDiv.append($('<span>', {
      'class': 'die_overlay die_number_' + player,
      'text': Api.game[player].valueArray[i],
    }));
    dieDiv.append($('<br>'));

    var dieRecipeText = Game.dieRecipeText(
      Api.game[player].dieRecipeArray[i],
      Api.game[player].sidesArray[i]);
    dieDiv.append($('<span>', {
      'class': 'die_recipe_' + player,
      'text': dieRecipeText,
    }));
    dieBorderDiv.append(dieDiv);

    allDice.append(dieBorderDiv);
    i += 1;
  }

  return allDice;
};

// Show the winner of a completed game
Game.gameWinner = function() {

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
  if ($(this).hasClass('unselected_player')) {
    $(this).attr('style', 'border: 2px solid ' + Game.color.player);
  } else {
    $(this).attr('style', '');
  }
};

Game.dieBorderToggleOpponentHandler = function() {
  $(this).toggleClass('selected unselected_opponent');
  if ($(this).hasClass('unselected_opponent')) {
    $(this).attr('style', 'border: 2px solid ' + Game.color.opponent);
  } else {
    $(this).attr('style', '');
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
