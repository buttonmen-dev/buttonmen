// namespace for this "module"
var Newgame = {
  'activity': {},
};

Newgame.bodyDivId = 'newgame_page';

// Maximum number of characters permitted in the game description
Newgame.GAME_DESCRIPTION_MAX_LENGTH = 255;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Newgame.showLoggedInPage() is the landing function.  Always call
//   this first
// * Newgame.getNewgameOptions() asks the API for information about players
//   and buttons to be used when creating the game.  It clobbers
//   Newgame.api.  If successful, it calls
// * Newgame.showStatePage() determines what action to take next based on
//   the received data from getNewgameOptions().  It calls one of several
//   functions, Newgame.action<SomeAction>()
// * each Newgame.action<SomeAction>() function must set Newgame.page and
//   Newgame.form, then call Login.arrangePage()
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Newgame.showLoggedInPage = function() {
  if (!Newgame.activity.opponentName) {
    Newgame.activity.opponentName = Env.getParameterByName('opponent');
  }
  if (!ButtonSelection.activity.playerButton) {
    ButtonSelection.activity.playerButton =
      Env.getParameterByName('playerButton');
  }
  if (!ButtonSelection.activity.opponentButton) {
    ButtonSelection.activity.opponentButton =
      Env.getParameterByName('opponentButton');
  }
  if (!Newgame.activity.previousGameId) {
    Newgame.activity.previousGameId = Env.getParameterByName('previousGameId');
    // We apparently don't want it to remain visible in the URL
    Env.removeParameterByName('previousGameId');
  }
  if (!Newgame.activity.nRounds) {
    Newgame.activity.nRounds = Env.getParameterByName('maxWins');
  }
  if (!Newgame.activity.isPlayer1Unlocked) {
    Newgame.activity.isPlayer1Unlocked = false;
  }

  // Get all needed information, then display newgame page
  ButtonSelection.getButtonSelectionData(Newgame.showPage);
};

// This function is called after Api.player has been loaded with new data
Newgame.showPage = function() {
  if ((Api.button.load_status == 'ok') && (Api.player.load_status == 'ok')) {
    Newgame.actionCreateGame();
  } else {
    Newgame.actionInternalErrorPage();
  }
};

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by Newgame.js.
// Each function should start by populating Newgame.page and Newgame.form
// ane end by invoking Login.arrangePage();

Newgame.actionLoggedOut = function() {

  // Create empty page and undefined form objects to be filled later
  Newgame.page = $('<div>');
  Newgame.form = null;

  // Add the "logged out player" HTML contents
  Newgame.addLoggedOutPage();

  // Lay out the page
  Login.arrangePage(Newgame.page, Newgame.form, '#newgame_action_button');
};

Newgame.actionInternalErrorPage = function() {

  // Create empty page and undefined form objects to be filled later
  Newgame.page = $('<div>');
  Newgame.form = null;

  // Add the internal error HTML contents
  Newgame.addInternalErrorPage();

  // Lay out the page
  Login.arrangePage(Newgame.page, Newgame.form, '#newgame_action_button');
};

Newgame.actionCreateGame = function() {

  Newgame.createPlayerLists();

  // Create empty page and undefined form objects to be filled later
  Newgame.page = $('<div>');
  if (Newgame.justCreatedGame === true) {
    Newgame.activity.previousGameId = undefined;
    Newgame.page.css('display', 'none');
  }
  Newgame.form = null;

  var creatediv = $('<div>');
  creatediv.append($('<div>', {
    'class': 'title2',
    'text': 'Create a new game',
  }));
  var createform = $('<form>', {
    'id': 'newgame_action_form',
    'action': 'javascript:void(0);',
  });

  // add generic options table to the form
  createform.append(Newgame.createMiscOptionsTable()).append($('<br />'));

  // add table of button selections to the form
  createform.append(Newgame.createButtonOptionsTable()).append($('<br />'));

  // Form submission button
  createform.append($('<button>', {
    'id': 'newgame_action_button',
    'text': 'Create game!',
  }));
  creatediv.append(createform);

  Newgame.page.append(creatediv);

  if (ButtonSelection.activity.anyUnimplementedButtons) {
    var warningpar = $('<p>');
    warningpar.append($('<i>', {
      'text': 'Note to testers: buttons whose names are prefixed with "--" ' +
              'contain unimplemented skills.  Selecting these buttons is not ' +
              'recommended.'
    }));
    Newgame.page.append(warningpar);
  }

  // Function to invoke on button click
  Newgame.form = Newgame.formCreateGame;

  // Lay out the page
  Login.arrangePage(Newgame.page, Newgame.form, '#newgame_action_button');

  // Unlock player 1 if this was previously unlocked before form submission
  if (Newgame.activity.isPlayer1Unlocked) {
    $('#player1_toggle').click();
  }

  // Make custom button fields visible if a custom button has been selected
  if ('CustomBM' == ButtonSelection.activity.playerButton) {
    $('#player_custom_recipe').parent().children().show();
  }
  if ('CustomBM' == ButtonSelection.activity.opponentButton) {
    $('#opponent_custom_recipe').parent().children().show();
  }

  // activate all Chosen comboboxes
  $('.chosen-select').chosen({ search_contains: true });
};

Newgame.createPlayerLists = function() {
  Newgame.activity.opponentNames = {};
  Newgame.activity.allPlayerNames = {};
  for (var playerName in Api.player.list) {
    Newgame.activity.allPlayerNames[playerName] = playerName;
    if ((playerName != Login.player) &&
        (Api.player.list[playerName].status == 'ACTIVE')) {
      Newgame.activity.opponentNames[playerName] = playerName;
    }
  }
};

Newgame.createMiscOptionsTable = function() {
  var miscOptionsTable = $('<table>', {'id': 'newgame_create_table', });

  miscOptionsTable.append(Newgame.createPlayer1Row());
  miscOptionsTable.append(Newgame.createPlayer2Row());
  miscOptionsTable.append(Newgame.createRoundSelectRow());
  miscOptionsTable.append(Newgame.createPrevGameRow());
  miscOptionsTable.append(Newgame.createDescRow());

  return miscOptionsTable;
};

Newgame.createPlayer1Row = function() {
  var player1Row = $('<tr>', {'id': 'player1_row', });
  player1Row.append($('<th>', {'text': 'You:', }));

  player1Row.append(
    $('<td>', {'text': Login.player, }).append(
      Newgame.createPlayer1Toggle()
    )
  );

  return player1Row;
};

Newgame.createPlayer1Toggle = function() {
  var player1Toggle = $('<input>', {
    'type': 'button',
    'id': 'player1_toggle',
    'class': 'player1_toggle',
    'value': 'Change first player',
  });

  player1Toggle.click(function() {
    if (!Newgame.activity.opponentName) {
      var oppName = $('#opponent_name_chosen > a > span').text();
      if (oppName != 'Anybody') {
        Newgame.activity.opponentName = oppName;
      }
    }

    $('#player1_row').remove();
    $('#player2_row').remove();

    ButtonSelection.getSelectRow(
      'Player 2',
      'opponent_name',
      Newgame.activity.allPlayerNames,
      null,
      Newgame.activity.opponentName,
      true,
      'Anybody'
    ).prependTo('#newgame_create_table');

    if (!Newgame.activity.playerName) {
      Newgame.activity.playerName = Login.player;
    }

    ButtonSelection.getSelectRow(
      'Player 1',
      'player_name',
      Newgame.activity.allPlayerNames,
      null,
      Newgame.activity.playerName,
      true
    ).prependTo('#newgame_create_table');

    // activate all Chosen comboboxes
    $('.chosen-select').chosen({ search_contains: true });
  });

  return player1Toggle;
};

Newgame.createPlayer2Row = function() {
  if (!('opponentName' in Newgame.activity)) {
    Newgame.activity.opponentName = null;
  }

  var player2Row = ButtonSelection.getSelectRow(
    'Opponent',
    'opponent_name',
    Newgame.activity.opponentNames,
    null,
    Newgame.activity.opponentName,
    true,
    'Anybody'
  );
  player2Row.prop('id', 'player2_row');

  return player2Row;
};

Newgame.createRoundSelectRow = function() {
  if (!('nRounds' in Newgame.activity) || !Newgame.activity.nRounds) {
    Newgame.activity.nRounds = '3';
  }
  var selectRow = ButtonSelection.getSelectRow(
    'Winner is first player to win',
    'n_rounds',
    {
      '1': '1 round',
      '2': '2 rounds',
      '3': '3 rounds',
      '4': '4 rounds',
      '5': '5 rounds',
    },
    null,
    Newgame.activity.nRounds
  );

  return selectRow;
};

Newgame.createPrevGameRow = function() {
  if (!('previousGameId' in Newgame.activity)) {
    Newgame.activity.previousGameId = null;
  } else if (Newgame.activity.previousGameId) {
    var prevGameRow = $('<tr>');
    prevGameRow.append($('<th>', {'text': 'Copy chat from:' }));
    var prevGameLink = $('<a>', {
      'text': 'Game ' + Newgame.activity.previousGameId,
      'href': 'game.html?game=' + Newgame.activity.previousGameId,
    });
    prevGameRow.append($('<td>').append(prevGameLink));

    return prevGameRow;
  }
};

Newgame.createDescRow = function() {
  if (!('description' in Newgame.activity)) {
    Newgame.activity.description = '';
  }
  var descRow = $('<tr>');
  descRow.append($('<th>', {'text': 'Description (optional):' }));
  var descInput = $('<textarea>', {
    'id': 'description',
    'name': 'description',
    'rows': '3',
    'class': 'gameDescInput',
    'maxlength': Newgame.GAME_DESCRIPTION_MAX_LENGTH,
    'text': Newgame.activity.description,
  });
  descRow.append($('<td>').append(descInput));

  return descRow;
};

Newgame.createButtonOptionsTable = function(doShowOpponent) {
  if (typeof doShowOpponent === 'undefined') {
    doShowOpponent = true;
  }
  
  ButtonSelection.loadButtonsIntoDicts();

  var buttonOptionsTable = $('<table>', {'id': 'newgame_button_table', });

  // table header
  if (doShowOpponent) {
    var headerRow = $('<tr>');
    headerRow.append($('<th>', {'text': 'Your button:', }));
    headerRow.append($('<th>', {'text': 'Opponent\'s button:', }));
    buttonOptionsTable.append(headerRow);
  }

  // make a row with one column for each button that we need to select
  // i.e., ours and potentially the opponent's
  var containerRow = $('<tr>');
  buttonOptionsTable.append(containerRow);
  
  var playerCol = $('<td>');
  containerRow.append(playerCol);
  playerCol.append(ButtonSelection.getSingleButtonOptionsTable('player'));
  
  if (doShowOpponent) {
    var opponentCol = $('<td>');
    containerRow.append(opponentCol);
    opponentCol.append(ButtonSelection.getSingleButtonOptionsTable('opponent'));
  }
  
  return buttonOptionsTable;
};

////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

Newgame.formCreateGame = function() {

  if ($('#player_name').length) {
    Newgame.activity.isPlayer1Unlocked = true;
    Newgame.activity.playerName = $('#player_name').val();
  } else {
    Newgame.activity.isPlayer1Unlocked = false;
    Newgame.activity.playerName = Login.player;
  }

  Newgame.activity.opponentName = $('#opponent_name').val();
  ButtonSelection.activity.playerButton = $('#player_button').val();
  ButtonSelection.activity.playerCustomRecipe =
    $('#player_custom_recipe').val();
  ButtonSelection.activity.opponentButton = $('#opponent_button').val();
  ButtonSelection.activity.opponentCustomRecipe =
    $('#opponent_custom_recipe').val();
  Newgame.activity.nRounds = $('#n_rounds').val();
  Newgame.activity.description = $('#description').val();

  var errorMessage = '';

  if (!Newgame.activity.playerName) {
    errorMessage = 'Please select the first player.';
  } else if (Newgame.activity.playerName == Newgame.activity.opponentName) {
    errorMessage = 'The two player names cannot be the same.';
  } else if (!ButtonSelection.activity.playerButton) {
    if ($('#player_name').length) {
      errorMessage = 'Please select a button for player 1';
    } else {
      errorMessage = 'Please select a button for yourself.';
    }
  } else if (ButtonSelection.activity.playerButton == 'CustomBM' &&
    !ButtonSelection.activity.playerCustomRecipe) {
    if ($('#player_name').length) {
      errorMessage = 'Please enter a custom recipe for player 1';
    } else {
      errorMessage = 'Please enter a custom recipe for yourself.';
    }
  } else if (Newgame.activity.opponentName &&
    !(Newgame.activity.opponentName in Api.player.list)) {
    errorMessage =
      'Specified opponent ' + Newgame.activity.opponentName +
      ' is not recognized';
  } else if (Newgame.activity.opponentName &&
    !ButtonSelection.activity.opponentButton) {
    errorMessage =
      'Please select a button for ' + Newgame.activity.opponentName;
  } else if (Newgame.activity.opponentName &&
    ButtonSelection.activity.opponentButton == 'CustomBM' &&
    !ButtonSelection.activity.opponentCustomRecipe) {
    errorMessage =
      'Please enter a custom recipe for ' + Newgame.activity.opponentName;
  }

  if (errorMessage.length) {
    Env.message = {
      'type': 'error',
      'text': errorMessage,
    };
    Newgame.showLoggedInPage();
  } else {
    // create an array with one element for each player/button combination
    var playerInfoArray = [];
    playerInfoArray[0] = [
      Newgame.activity.playerName,
      ButtonSelection.activity.playerButton,
      ButtonSelection.activity.playerCustomRecipe,
    ];
    playerInfoArray[1] = [
      Newgame.activity.opponentName,
      ButtonSelection.activity.opponentButton,
      ButtonSelection.activity.opponentCustomRecipe,
    ];

    var args =
      {
        type: 'createGame',
        playerInfoArray: playerInfoArray,
        maxWins: Newgame.activity.nRounds,
        description: Newgame.activity.description,
      };
    if (Newgame.activity.previousGameId) {
      args.previousGameId = Newgame.activity.previousGameId;
    }

    var customRecipeArray = [];

    if (ButtonSelection.activity.playerCustomRecipe ||
        ButtonSelection.activity.opponentCustomRecipe) {
      customRecipeArray = ['', ''];
    }
    if (ButtonSelection.activity.playerCustomRecipe) {
      customRecipeArray[0] = ButtonSelection.activity.playerCustomRecipe;
    }
    if (ButtonSelection.activity.opponentCustomRecipe) {
      customRecipeArray[1] = ButtonSelection.activity.opponentCustomRecipe;
    }

    args.customRecipeArray = customRecipeArray;

    // N.B. Newgame.activity is always retained between loads: on
    // failure so the player can correct selections, on success in
    // case the player wants to create another similar game.
    // Therefore, it's fine to pass the form post the same function
    // (showLoggedInPage) for both success and failure conditions.
    Api.apiFormPost(
      args,
      {
        'ok': {
          'type': 'function',
          'msgfunc': Newgame.setCreateGameSuccessMessage,
        },
        'notok': { 'type': 'server', },
      },
      '#newgame_action_button',
      Newgame.showLoggedInPage,
      Newgame.showLoggedInPage
    );
  }
};

////////////////////////////////////////////////////////////////////////
// These functions add pieces of HTML to Newgame.page

Newgame.addLoggedOutPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': 'Can\'t create a game because you are not logged in',
  }));
  Newgame.page.append(errorDiv);
};

Newgame.addInternalErrorPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': 'Can\'t create a game.  Something went wrong when ' +
            'loading data from server.',
  }));
  Newgame.page.append(errorDiv);
};

Newgame.setCreateGameSuccessMessage = function(message, data) {
  Newgame.justCreatedGame = true;

  var gameId = data.gameId;
  var gameLink;
  if (Newgame.activity.opponentName)
  {
    gameLink = $('<a>', {
      'href': 'game.html?game=' + gameId,
      'text': 'Go to game page',
    });
  } else {
    gameLink = $('<a>', {
      'href': 'open_games.html',
      'text': 'Go to open games page',
    });
  }

  var gamePar = $('<p>', {'text': message + ' ', });
  gamePar.append(gameLink);

  var anotherGamePar = $('<p>', { 'id': 'createAnotherGame', });
  var anotherGameBtn = $('<input>', {
    'type': 'button',
    'value': 'Create another game?',
  });
  anotherGameBtn.click(function() {
    Newgame.justCreatedGame = false;
    $('p#createAnotherGame').hide();
    $('div#newgame_page > div').show();
    // reset Chosen select dropdowns
    $('.chosen-select').chosen('destroy').chosen({ search_contains: true });
  });
  anotherGamePar.append(anotherGameBtn);
  gamePar.append(anotherGamePar);

  Env.message = {
    'type': 'success',
    'text': '',
    'obj': gamePar,
  };
};
