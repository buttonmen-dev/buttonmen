// namespace for this "module"
var Newgame = {
  'activity': {},
};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Newgame.showNewgamePage() is the landing function.  Always call
//   this first
// * Newgame.getNewgameOptions() asks the API for information about players
//   and buttons to be used when creating the game.  It clobbers
//   Newgame.api.  If successful, it calls
// * Newgame.showStatePage() determines what action to take next based on
//   the received data from getNewgameOptions().  It calls one of several
//   functions, Newgame.action<SomeAction>()
// * each Newgame.action<SomeAction>() function must set Newgame.page and
//   Newgame.form, then call Newgame.layoutPage()
// * Newgame.layoutPage() sets the contents of <div id="newgame_page">
//   on the live page
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Newgame.showNewgamePage = function() {

  // Setup necessary elements for displaying status messages
  $.getScript('js/Env.js');
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#newgame_page').length === 0) {
    $('body').append($('<div>', {'id': 'newgame_page', }));
  }

  // Get all needed information, then display newgame page
  Newgame.getNewgameData(Newgame.showPage);

};

Newgame.getNewgameData = function(callback) {
  if (Login.logged_in) {

    Api.getButtonData(function() {
      Api.getPlayerData(callback);
    });
  } else {

    // The player needs to be logged in for anything good to happen here
    Newgame.actionLoggedOut();
  }
};

// This function is called after Api.player has been loaded with new data
Newgame.showPage = function() {
  if ((Api.button.load_status == 'ok') && (Api.player.load_status == 'ok')) {
    Newgame.actionCreateGame();
  } else {
    Newgame.actionInternalErrorPage();
  }
};

// Actually lay out the page
Newgame.layoutPage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#newgame_page').empty();
  $('#newgame_page').append(Newgame.page);

  if (Newgame.form) {
    $('#newgame_action_button').click(Newgame.form);
  }
};

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by Newgame.js.
// Each function should start by populating Newgame.page and Newgame.form
// ane end by invoking Newgame.layoutPage();

Newgame.actionLoggedOut = function() {

  // Create empty page and undefined form objects to be filled later
  Newgame.page = $('<div>');
  Newgame.form = null;

  // Add the "logged out player" HTML contents
  Newgame.addLoggedOutPage();

  // Lay out the page
  Newgame.layoutPage();
};

Newgame.actionInternalErrorPage = function() {

  // Create empty page and undefined form objects to be filled later
  Newgame.page = $('<div>');
  Newgame.form = null;

  // Add the internal error HTML contents
  Newgame.addInternalErrorPage();

  // Lay out the page
  Newgame.layoutPage();
};

Newgame.actionCreateGame = function() {

  // Create empty page and undefined form objects to be filled later
  Newgame.page = $('<div>');
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

  // Table of game creation options
  var createtable = $('<table>', {'id': 'newgame_create_table', });

  var playerRow = $('<tr>');
  playerRow.append($('<th>', {'text': 'You:', }));
  playerRow.append($('<td>', {'text': Login.player, }));
  createtable.append(playerRow);

  // Setup opponent choice, including autocomplete from a dropdown
  // FIXME: i couldn't get autocomplete to display properly, so
  // temporarily used a select for the opponent name
  var playerNames = {};
  for (var playerName in Api.player.list) {
    if ((playerName != Login.player) &&
        (Api.player.list[playerName].status == 'active')) {
      playerNames[playerName] = playerName;
    }
  }
  if (!('opponentName' in Newgame.activity)) {
    Newgame.activity.opponentName = null;
  }
  createtable.append(
    Newgame.getSelectRow('Opponent', 'opponent_name', playerNames,
                         null, Newgame.activity.opponentName));

  // Load buttons and recipes into a dict for use in selects
  var buttonRecipe = {};
  var buttonGreyed = {};
  var anyUnimplementedButtons = false;
  $.each(Api.button.list, function(button, buttoninfo) {
    if (buttoninfo.hasUnimplementedSkill) {
      buttonRecipe[button] = '-- ' + button + ': ' + buttoninfo.recipe;
      buttonGreyed[button] = true;
      anyUnimplementedButtons = true;
    } else {
      buttonRecipe[button] = button + ': ' + buttoninfo.recipe;
      buttonGreyed[button] = false;
    }
  });

  // Player button selection
  if (!('playerButton' in Newgame.activity)) {
    Newgame.activity.playerButton = null;
  }
  createtable.append(
    Newgame.getSelectRow(
      'Your button', 'player_button', buttonRecipe,
      buttonGreyed, Newgame.activity.playerButton));

  // Opponent button selection
  if (!('opponentButton' in Newgame.activity)) {
    Newgame.activity.opponentButton = null;
  }
  createtable.append(
    Newgame.getSelectRow(
      'Opponent\'s button', 'opponent_button', buttonRecipe,
      buttonGreyed, Newgame.activity.opponentButton));

  // Round selection
  if (!('nRounds' in Newgame.activity)) {
    Newgame.activity.nRounds = '3';
  }
  createtable.append(
    Newgame.getSelectRow('Winner is first player to win', 'n_rounds',
      {'1': '1 round', '2': '2 rounds', '3': '3 rounds',
       '4': '4 rounds', '5': '5 rounds', },
      null, Newgame.activity.nRounds));

  // Form submission button
  createform.append(createtable);
  createform.append($('<br>'));
  createform.append($('<button>', {
    'id': 'newgame_action_button',
    'text': 'Create game!',
  }));
  creatediv.append(createform);

  Newgame.page.append(creatediv);

  if (anyUnimplementedButtons) {
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
  Newgame.layoutPage();
};


////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

Newgame.formCreateGame = function() {

  Newgame.activity.opponentName = $('#opponent_name').val();
  Newgame.activity.playerButton = $('#player_button').val();
  Newgame.activity.opponentButton = $('#opponent_button').val();

  var validSelect = //Newgame.activity.opponentName &&
                    Newgame.activity.playerButton &&
                    Newgame.activity.opponentButton;

  if (!validSelect) {
    Env.message = {
      'type': 'error',
      'text':
        'Please select an opponent, your button, and your opponent\'s button',
    };
    Newgame.showNewgamePage();
  //}  else if (!(Newgame.activity.opponentName in Api.player.list)) {
  // Env.message = {
  //   'type': 'error',
  //   'text': 'Specified opponent ' + Newgame.activity.opponentName +
  //           ' is not recognized',
  // };
  // Newgame.showNewgamePage();

  } else {
    var playerNameArray = [
      Login.player,
      Newgame.activity.opponentName,
    ];
    var buttonNameArray = [
      Newgame.activity.playerButton,
      Newgame.activity.opponentButton,
    ];

    Newgame.activity.nRounds = $('#n_rounds').val();

    // N.B. Newgame.activity is always retained between loads: on
    // failure so the player can correct selections, on success in
    // case the player wants to create another similar game.
    // Therefore, it's fine to pass the form post the same function
    // (showNewgamePage) for both success and failure conditions.
    Api.apiFormPost(
      {
        type: 'createGame',
        playerNameArray: playerNameArray,
        buttonNameArray: buttonNameArray,
        maxWins: Newgame.activity.nRounds,
      },
      { 'ok':
        {
          'type': 'function',
          'msgfunc': Newgame.setCreateGameSuccessMessage,
        },
        'notok': { 'type': 'server', },
      },
      'newgame_action_button',
      Newgame.showNewgamePage,
      Newgame.showNewgamePage
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
  var gameId = data.gameId;
  var gameLink = $('<a>', {
    'href': 'game.html?game=' + gameId,
    'text': 'Go to game page',
  });
  var gamePar = $('<p>', {'text': message + ' ', });
  gamePar.append(gameLink);
  Env.message = {
    'type': 'success',
    'text': '',
    'obj': gamePar,
  };
};

////////////////////////////////////////////////////////////////////////
// These functions generate and return pieces of HTML

Newgame.getSelectRow = function(rowname, selectname, valuedict,
                                greydict, selectedval) {
  var selectRow = $('<tr>');
  selectRow.append($('<th>', {'text': rowname + ':', }));

  var select = $('<select>', {
    'id': selectname,
    'name': selectname,
  });

  // If there's no default, put an invalid default value first
  if (selectedval === null) {
    select.append($('<option>', {
      'value': '',
      'class': 'yellowed',
      'text': 'Choose ' + rowname.toLowerCase(),
    }));
  }

  $.each(valuedict, function(key, value) {
    var selectopts = {
      'value': key,
      'label': value,
      'text': value,
    };
    if (selectedval == key) {
      selectopts.selected = 'selected';
    }
    if ((greydict !== null) && (greydict[key])) {
      selectopts['class'] = 'greyed';
    }
    select.append($('<option>', selectopts));
  });
  var selectTd = $('<td>');
  selectTd.append(select);
  selectRow.append(selectTd);
  return selectRow;
};
