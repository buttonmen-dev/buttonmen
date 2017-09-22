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
  if (!Newgame.activity.playerButton) {
    Newgame.activity.playerButton = Env.getParameterByName('playerButton');
  }
  if (!Newgame.activity.opponentButton) {
    Newgame.activity.opponentButton = Env.getParameterByName('opponentButton');
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
  Newgame.getNewgameData(Newgame.showPage);
};

Newgame.getNewgameData = function(callback) {
  Env.callAsyncInParallel(
    [
      { 'func': Api.getButtonData, 'args': [ null ] },
      Api.getPlayerData,
    ], callback);
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

  // Table of miscellaneous game creation options
  var miscOptionsTable = $('<table>', {'id': 'newgame_create_table', });

  var player1Row = $('<tr>', {'id': 'player1_row', });
  player1Row.append($('<th>', {'text': 'You:', }));

  var player1Toggle = $('<input>', {
    'type': 'button',
    'id': 'player1_toggle',
    'class': 'player1_toggle',
    'value': 'Change first player',
  });

  player1Row.append(
    $('<td>', {'text': Login.player, }).append(player1Toggle)
  );
  miscOptionsTable.append(player1Row);

  // Setup opponent choice, including autocomplete from a dropdown
  // FIXME: i couldn't get autocomplete to display properly, so
  // temporarily used a select for the opponent name
  var opponentNames = {};
  var allPlayerNames = {};
  for (var playerName in Api.player.list) {
    allPlayerNames[playerName] = playerName;
    if ((playerName != Login.player) &&
        (Api.player.list[playerName].status == 'ACTIVE')) {
      opponentNames[playerName] = playerName;
    }
  }
  if (!('opponentName' in Newgame.activity)) {
    Newgame.activity.opponentName = null;
  }

  var player2Row = Newgame.getSelectRow(
    'Opponent',
    'opponent_name',
    opponentNames,
    null,
    Newgame.activity.opponentName,
    'Anybody'
  );
  player2Row.prop('id', 'player2_row');

  miscOptionsTable.append(player2Row);

  player1Toggle.click(function() {
    $('#player1_row').remove();
    $('#player2_row').remove();

    Newgame.getSelectRow(
      'Player 2',
      'opponent_name',
      allPlayerNames,
      null,
      Newgame.activity.opponentName,
      'Anybody'
    ).prependTo('#newgame_create_table');

    if (!Newgame.activity.playerName) {
      Newgame.activity.playerName = Login.player;
    }

    Newgame.getSelectRow(
      'Player 1',
      'player_name',
      allPlayerNames,
      null,
      Newgame.activity.playerName
    ).prependTo('#newgame_create_table');
  });

  // Round selection
  if (!('nRounds' in Newgame.activity) || !Newgame.activity.nRounds) {
    Newgame.activity.nRounds = '3';
  }
  miscOptionsTable.append(
    Newgame.getSelectRow('Winner is first player to win', 'n_rounds',
      {'1': '1 round', '2': '2 rounds', '3': '3 rounds',
       '4': '4 rounds', '5': '5 rounds', },
      null, Newgame.activity.nRounds));

  // Previous game
  if (!('previousGameId' in Newgame.activity)) {
    Newgame.activity.previousGameId = null;
  } else if (Newgame.activity.previousGameId) {
    var prevGameRow = $('<tr>');
    miscOptionsTable.append(prevGameRow);
    prevGameRow.append($('<th>', {'text': 'Copy chat from:' }));
    var prevGameLink = $('<a>', {
      'text': 'Game ' + Newgame.activity.previousGameId,
      'href': 'game.html?game=' + Newgame.activity.previousGameId,
    });
    prevGameRow.append($('<td>').append(prevGameLink));
  }

  // Game description text
  if (!('description' in Newgame.activity)) {
    Newgame.activity.description = '';
  }
  var descRow = $('<tr>');
  miscOptionsTable.append(descRow);
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

  // add generic options table to the form
  createform.append(miscOptionsTable);
  createform.append($('<br>'));

  // Table of button selections
  var buttonOptionsTable = $('<table>', {'id': 'newgame_button_table', });

  // Load buttons and recipes into dicts for use in selects
  Newgame.activity.buttonRecipe = {};
  Newgame.activity.buttonGreyed = {};
  Newgame.activity.buttonSets = {};
  Newgame.activity.dieSkills = {};
  Newgame.activity.tournLegal = {
    'yes': true,
    'no': true,
  };
  Newgame.activity.anyUnimplementedButtons = false;
  $.each(Api.button.list, function(button, buttoninfo) {
    Newgame.activity.buttonSets[buttoninfo.buttonSet] = true;
    $.each(buttoninfo.dieSkills, function(i, dieSkill) {
      Newgame.activity.dieSkills[dieSkill] = true;
    });
    if (buttoninfo.hasUnimplementedSkill) {
      Newgame.activity.buttonRecipe[button] =
        '-- ' + button + ': ' + buttoninfo.recipe;
      Newgame.activity.buttonGreyed[button] = true;
      Newgame.activity.anyUnimplementedButtons = true;
    } else {
      Newgame.activity.buttonRecipe[button] = button + ': ' + buttoninfo.recipe;
      Newgame.activity.buttonGreyed[button] = false;
    }
  });

  // Parse previously input options
  Newgame.initializeButtonLimits();

  if (!('playerButton' in Newgame.activity)) {
    Newgame.activity.playerButton = null;
  }
  if (!('opponentButton' in Newgame.activity)) {
    Newgame.activity.opponentButton = null;
  }

  // Set the initial list of selectable buttons for each player
  Newgame.activity.buttonList = {};
  Newgame.updateButtonList('player', null);
  Newgame.updateButtonList('opponent', null);

  // table header
  var headerRow = $('<tr>');
  headerRow.append($('<th>', {'text': 'Your button:', }));
  headerRow.append($('<th>', {'text': 'Opponent\'s button:', }));
  buttonOptionsTable.append(headerRow);

  // button limit rows
  buttonOptionsTable.append(Newgame.getButtonLimitRow(
    'Button set:',
    'button_sets',
    Newgame.activity.buttonSets
  ));
  buttonOptionsTable.append(Newgame.getButtonLimitRow(
    'Tournament legal:',
    'tourn_legal',
    Newgame.activity.tournLegal,
    false
  ));
  buttonOptionsTable.append(Newgame.getButtonLimitRow(
    'Die skill:',
    'die_skills',
    Newgame.activity.dieSkills
  ));

  // button selection row
  var selectRow = $('<tr>');
  selectRow.append(Newgame.getButtonSelectTd('player'));
  selectRow.append(Newgame.getButtonSelectTd('opponent'));
  buttonOptionsTable.append(selectRow);

  // Form submission button
  createform.append(buttonOptionsTable);
  createform.append($('<br>'));
  createform.append($('<button>', {
    'id': 'newgame_action_button',
    'text': 'Create game!',
  }));
  creatediv.append(createform);

  Newgame.page.append(creatediv);

  if (Newgame.activity.anyUnimplementedButtons) {
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
  Newgame.activity.playerButton = $('#player_button').val();
  Newgame.activity.opponentButton = $('#opponent_button').val();
  Newgame.activity.nRounds = $('#n_rounds').val();
  Newgame.activity.description = $('#description').val();

  var errorMessage = '';

  if (!Newgame.activity.playerName) {
    errorMessage = 'Please select the first player.';
  } else if (Newgame.activity.playerName == Newgame.activity.opponentName) {
    errorMessage = 'The two player names cannot be the same.';
  } else if (!Newgame.activity.playerButton) {
    if ($('#player_name').length) {
      errorMessage = 'Please select a button for player 1';
    } else {
      errorMessage = 'Please select a button for yourself.';
    }
  } else if (Newgame.activity.opponentName &&
    !(Newgame.activity.opponentName in Api.player.list)) {
    errorMessage =
      'Specified opponent ' + Newgame.activity.opponentName +
      ' is not recognized';
  } else if (Newgame.activity.opponentName &&
    !Newgame.activity.opponentButton) {
    errorMessage =
      'Please select a button for ' + Newgame.activity.opponentName;
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
      Newgame.activity.playerButton,
    ];
    playerInfoArray[1] = [
      Newgame.activity.opponentName,
      Newgame.activity.opponentButton,
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
  });
  anotherGamePar.append(anotherGameBtn);
  gamePar.append(anotherGamePar);

  Env.message = {
    'type': 'success',
    'text': '',
    'obj': gamePar,
  };
};

////////////////////////////////////////////////////////////////////////
// These functions generate and return pieces of HTML

Newgame.getSelectRow = function(rowname, selectname, valuedict,
                                greydict, selectedval, blankOption) {
  var selectRow = $('<tr>');
  selectRow.append($('<th>', {'text': rowname + ':', }));

  var selectTd = Newgame.getSelectTd(
    rowname, selectname, valuedict, greydict, selectedval, blankOption);

  selectRow.append(selectTd);
  return selectRow;
};

Newgame.getSelectTd = function(nametext, selectname, valuedict,
                               greydict, selectedval, blankOption) {
  var select = $('<select>', {
    'id': selectname,
    'name': selectname,
  });

  var optionlist = Newgame.getSelectOptionList(
    nametext, valuedict, greydict, selectedval, blankOption);
  var optioncount = optionlist.length;
  for (var i = 0; i < optioncount; i++) {
    select.append(optionlist[i]);
  }

  var selectTd = $('<td>');
  selectTd.append(select);

  return selectTd;
};

Newgame.getSelectOptionList = function(
    nametext, valuedict, greydict, selectedval, blankOption) {

  var optionlist = [];

  if (blankOption !== undefined) {
    // If blanks are allowed, then display a special entry for that
    optionlist.push($('<option>', {
      'value': '',
      'text': blankOption,
    }));
  } else {
    // If there's no default, put an invalid default value first
    if (selectedval === null) {
      optionlist.push($('<option>', {
        'value': '',
        'class': 'yellowed',
        'text': 'Choose ' + nametext.toLowerCase(),
      }));
    }
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
    optionlist.push($('<option>', selectopts));
  });
  return optionlist;
};

Newgame.getButtonSelectTd = function(player) {
  if (player == 'player') {
    return Newgame.getSelectTd(
      'Your button',
      'player_button',
      Newgame.activity.buttonList.player,
      Newgame.activity.buttonGreyed,
      Newgame.activity.playerButton);
  } else {
    return Newgame.getSelectTd(
      'Opponent\'s button',
      'opponent_button',
      Newgame.activity.buttonList.opponent,
      Newgame.activity.buttonGreyed,
      Newgame.activity.opponentButton,
     'Any button');
  }
};

Newgame.updateButtonSelectTd = function(player) {
  var selectid;
  var optionlist;
  if (player == 'player') {
    selectid = 'player_button';
    optionlist = Newgame.getSelectOptionList(
      'Your button',
      Newgame.activity.buttonList.player,
      Newgame.activity.buttonGreyed,
      Newgame.activity.playerButton);
  } else {
    selectid = 'opponent_button';
    optionlist = Newgame.getSelectOptionList(
      'Opponent\'s button',
      Newgame.activity.buttonList.opponent,
      Newgame.activity.buttonGreyed,
      Newgame.activity.opponentButton,
     'Any button');
  }

  var optioncount = optionlist.length;
  var select = $('#' + selectid);
  select.empty();
  for (var i = 0; i < optioncount; i++) {
    select.append(optionlist[i]);
  }
};

Newgame.updateButtonList = function(player, limitid) {
  if (limitid) {
    var optsTag = '#' + Newgame.getLimitSelectid(player, limitid) + ' option';
    $.each($(optsTag), function() {
      Newgame.activity.buttonLimits[player][limitid][$(this).val()] = false;
    });
    $.each($(optsTag + ':selected'), function() {
      Newgame.activity.buttonLimits[player][limitid][$(this).val()] = true;
    });
  }

  if (Newgame.activity.buttonLimits[player].button_sets.ANY &&
      Newgame.activity.buttonLimits[player].tourn_legal.ANY &&
      Newgame.activity.buttonLimits[player].die_skills.ANY) {
    Newgame.activity.buttonList[player] = {
      '__random': 'Random button',
    };
  } else {
    Newgame.activity.buttonList[player] = {};
  }

  var choiceid;
  var hasSkill;
  $.each(Api.button.list, function(button, buttoninfo) {

    // If the user has specified any limits based on button set,
    // skip buttons which are not in one of the sets the user has
    // specified
    if (!Newgame.activity.buttonLimits[player].button_sets.ANY) {
      choiceid = Newgame.getChoiceId(
        player, 'button_sets', buttoninfo.buttonSet);
      if (!Newgame.activity.buttonLimits[player].button_sets[choiceid]) {
        return true;
      }
    }

    // If the user has specified any limits based on TL status,
    // skip buttons which do not have the status the user has specified
    if (!Newgame.activity.buttonLimits[player].tourn_legal.ANY) {
      if (buttoninfo.isTournamentLegal) {
        choiceid = Newgame.getChoiceId(player, 'tourn_legal', 'yes');
      } else {
        choiceid = Newgame.getChoiceId(player, 'tourn_legal', 'no');
      }
      if (!Newgame.activity.buttonLimits[player].tourn_legal[choiceid]) {
        return true;
      }
    }

    // If the user has specified any limits based on die skills,
    // skip buttons which do not have at least one requested skills
    if (!Newgame.activity.buttonLimits[player].die_skills.ANY) {
      hasSkill = false;
      $.each(buttoninfo.dieSkills, function(i, dieSkill) {
        choiceid = Newgame.getChoiceId(player, 'die_skills', dieSkill);
        if (Newgame.activity.buttonLimits[player].die_skills[choiceid]) {
          hasSkill = true;
        }
      });
      if (!hasSkill) {
        return true;
      }
    }

    Newgame.activity.buttonList[player][button] =
      Newgame.activity.buttonRecipe[button];
  });

  // if we're updating an existing button select dropdown, change it now
  if (limitid) {
    Newgame.updateButtonSelectTd(player);
  }
};

Newgame.getButtonLimitRow = function(desctext, limitid, choices, multi) {
  // Default to multi-selects
  if (multi === undefined) { multi = true; }

  var limitRow = $('<tr>');
  limitRow.append(Newgame.getButtonLimitTd(
    'player', desctext, limitid, choices, multi));
  limitRow.append(Newgame.getButtonLimitTd(
    'opponent', desctext, limitid, choices, multi));
  return limitRow;
};

Newgame.getButtonLimitTd = function(player, desctext, limitid, choices, multi) {
  var limitTd = $('<td>');
  var limitSubtable = $('<table>');
  var limitSubrow = $('<tr>');
  limitSubrow.append($('<td>', {'text': desctext + ' ', }));
  var selectId = Newgame.getLimitSelectid(player, limitid);
  var limitSelect = $('<select>', {
    'id': selectId,
    'name': selectId,
    'multiple': multi,
    'onchange': 'Newgame.updateButtonList("' + player + '", "' + limitid + '")',
  });

  // dicts in javascript don't fully work - make a separate array
  // of keys so we can sort it
  var choicekeys = [];
  $.each(choices, function(choice) {
    choicekeys.push(choice);
  });
  choicekeys.sort();

  var anyOptionOpts = {
    'value': 'ANY',
    'label': 'ANY',
    'text': 'ANY',
  };
  if (Newgame.activity.buttonLimits[player][limitid].ANY) {
    anyOptionOpts.selected = 'selected';
  }
  limitSelect.append($('<option>', anyOptionOpts));

  var inputid;
  $.each(choicekeys, function(i, choice) {
    inputid = Newgame.getChoiceId(player, limitid, choice);
    var selectopts = {
      'value': inputid,
      'label': choice,
      'text': choice,
    };
    if (Newgame.activity.buttonLimits[player][limitid][inputid]) {
      selectopts.selected = 'selected';
    }
    limitSelect.append($('<option>', selectopts));
  });
  limitSubrow.append(limitSelect);
  limitSubtable.append(limitSubrow);
  limitTd.append(limitSubtable);

  return limitTd;
};

Newgame.getLimitSelectid = function(player, limitid) {
  return 'limit_' + player + '_' + limitid;
};

Newgame.getChoiceId = function(player, limitid, choice) {
  return 'limit_' + player + '_' + limitid + '_' +
    choice.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
};

// This initializes button limits only if they're unset; otherwise
// it leaves them alone
Newgame.initializeButtonLimits = function() {
  if (!('buttonLimits' in Newgame.activity)) {
    Newgame.activity.buttonLimits = {};
  }
  var choiceid;
  var players = ['player', 'opponent'];
  var player;
  for (var i = 0; i < 2; i++) {
    player = players[i];
    if (!(player in Newgame.activity.buttonLimits)) {
      Newgame.activity.buttonLimits[player] = {};
    }

    if (!('button_sets' in Newgame.activity.buttonLimits[player])) {
      Newgame.activity.buttonLimits[player].button_sets = {
        'ANY': true,
      };
    }
    $.each(Newgame.activity.buttonSets, function(buttonset) {
      choiceid = Newgame.getChoiceId(player, 'button_sets', buttonset);
      if (!(choiceid in Newgame.activity.buttonLimits[player].button_sets)) {
        Newgame.activity.buttonLimits[player].button_sets[choiceid] = false;
      }
    });

    if (!('tourn_legal' in Newgame.activity.buttonLimits[player])) {
      Newgame.activity.buttonLimits[player].tourn_legal = {
        'ANY': true,
      };
    }
    $.each(Newgame.activity.tournLegal, function(yesno) {
      choiceid = Newgame.getChoiceId(player, 'tourn_legal', yesno);
      if (!(choiceid in Newgame.activity.buttonLimits[player].tourn_legal)) {
        Newgame.activity.buttonLimits[player].tourn_legal[choiceid] = false;
      }
    });

    if (!('die_skills' in Newgame.activity.buttonLimits[player])) {
      Newgame.activity.buttonLimits[player].die_skills = {
        'ANY': true,
      };
    }
    $.each(Newgame.activity.dieSkills, function(dieSkill) {
      choiceid = Newgame.getChoiceId(player, 'die_skills', dieSkill);
      if (!(choiceid in Newgame.activity.buttonLimits[player].die_skills)) {
        Newgame.activity.buttonLimits[player].die_skills[choiceid] = false;
      }
    });
  }
};
