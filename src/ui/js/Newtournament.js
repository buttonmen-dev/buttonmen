// namespace for this "module"
var Newtournament = {
  'activity': {},
};

Newtournament.bodyDivId = 'newtournament_page';

// Maximum number of characters permitted in the game description
Newtournament.TOURNAMENT_DESCRIPTION_MAX_LENGTH = 255;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Newtournament.showLoggedInPage() is the landing function.  Always call
//   this first
// * Newtournament.getNewtournamentOptions() asks the API for information about
//   players and buttons to be used when creating the tournament.  It clobbers
//   Newtournament.api.  If successful, it calls
// * Newtournament.showStatePage() determines what action to take next based on
//   the received data from getNewtournamentOptions().  It calls one of several
//   functions, Newtournament.action<SomeAction>()
// * each Newtournament.action<SomeAction>() function must set
//   Newtournament.page and Newtournament.form, then call Login.arrangePage()
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Newtournament.showLoggedInPage = function() {
  if (!Newtournament.activity.type) {
    Newtournament.activity.type = Env.getParameterByName('tournamentType');
  }
  if (!Newtournament.activity.nPlayer) {
    Newtournament.activity.nPlayer = Env.getParameterByName('nPlayer');
  }
  if (!Newtournament.activity.nRounds) {
    Newtournament.activity.nRounds = Env.getParameterByName('maxWins');
  }

  Newtournament.showPage();
};

//Newtournament.getNewtournamentData = function(callback) {
////  Env.callAsyncInParallel(
////    [
////      { 'func': Api.getButtonData, 'args': [ null ] },
////      Api.getPlayerData,
////    ], callback);
//};

Newtournament.showPage = function() {
  Newtournament.actionCreateTournament();
};

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by Newtournament.js.
// Each function should start by populating Newtournament.page and
// Newtournament.form and end by invoking Login.arrangePage();

Newtournament.actionLoggedOut = function() {

  // Create empty page and undefined form objects to be filled later
  Newtournament.page = $('<div>');
  Newtournament.form = null;

  // Add the "logged out player" HTML contents
  Newtournament.addLoggedOutPage();

  // Lay out the page
  Login.arrangePage(
    Newtournament.page,
    Newtournament.form,
    '#newtournament_action_button'
  );
};

Newtournament.actionInternalErrorPage = function() {

  // Create empty page and undefined form objects to be filled later
  Newtournament.page = $('<div>');
  Newtournament.form = null;

  // Add the internal error HTML contents
  Newtournament.addInternalErrorPage();

  // Lay out the page
  Login.arrangePage(
    Newtournament.page,
    Newtournament.form,
    '#newtournament_action_button'
  );
};

Newtournament.actionCreateTournament = function() {
  // Create empty page and undefined form objects to be filled later
  Newtournament.page = $('<div>');
  if (Newtournament.justCreatedTournament === true) {
    Newtournament.page.css('display', 'none');
  }
  Newtournament.form = null;

  var creatediv = $('<div>');
  creatediv.append($('<div>', {
    'class': 'title2',
    'text': 'Create a new tournament',
  }));
  var createform = $('<form>', {
    'id': 'newtournament_action_form',
    'action': 'javascript:void(0);',
  });

  // add generic options table to the form
  createform.append(Newtournament.createMiscOptionsTable()).append($('<br />'));

//  // add table of button selections to the form
//  createform.append(
//    Newtournament.createButtonOptionsTable()).append($('<br />')
//  );

  // Form submission button
  createform.append($('<button>', {
    'id': 'newtournament_action_button',
    'text': 'Create tournament!',
  }));
  creatediv.append(createform);

  Newtournament.page.append(creatediv);

  // Function to invoke on button click
  Newtournament.form = Newtournament.formCreateTournament;

  // Lay out the page
  Login.arrangePage(
    Newtournament.page,
    Newtournament.form,
    '#newtournament_action_button'
  );

  // Unlock player 1 if this was previously unlocked before form submission
  if (Newtournament.activity.isPlayer1Unlocked) {
    $('#player1_toggle').click();
  }

  // activate all Chosen comboboxes
//  $('.chosen-select').chosen({ search_contains: true });
};

Newtournament.createMiscOptionsTable = function() {
  var miscOptionsTable = $('<table>', {'id': 'newtournament_create_table', });

  miscOptionsTable.append(Newtournament.createTypeRow());
  miscOptionsTable.append(Newtournament.createNPlayerRow());
  miscOptionsTable.append(Newtournament.createRoundSelectRow());
  miscOptionsTable.append(Newtournament.createDescRow());

  return miscOptionsTable;
};

Newtournament.createTypeRow = function() {
  var selectRow = Newtournament.getSelectRow(
    'Tournament type',
    'type',
    {
      'Single Elimination': 'Single Elimination',
    },
    null,
    Newtournament.activity.type
  );

  return selectRow;
};

Newtournament.createNPlayerRow = function() {
  var selectRow = Newtournament.getSelectRow(
    'Number of players',
    'n_player',
    {
      '4': '4',
      '8': '8',
      '16': '16',
      '32': '32',
    },
    null,
    Newtournament.activity.nPlayer
  );

  return selectRow;
};

Newtournament.createRoundSelectRow = function() {
  if (!('nRounds' in Newtournament.activity) ||
      !Newtournament.activity.nRounds) {
    Newtournament.activity.nRounds = '3';
  }
  var selectRow = Newtournament.getSelectRow(
    'Each game is played until one player has won',
    'n_rounds',
    {
      '1': '1 round',
      '2': '2 rounds',
      '3': '3 rounds',
      '4': '4 rounds',
      '5': '5 rounds',
    },
    null,
    Newtournament.activity.nRounds
  );

  return selectRow;
};

Newtournament.createDescRow = function() {
  if (!('description' in Newtournament.activity)) {
    Newtournament.activity.description = '';
  }
  var descRow = $('<tr>');
  descRow.append($('<th>', {'text': 'Description (optional):' }));
  var descInput = $('<textarea>', {
    'id': 'description',
    'name': 'description',
    'rows': '3',
    'class': 'gameDescInput',
    'maxlength': Newtournament.TOURNAMENT_DESCRIPTION_MAX_LENGTH,
    'text': Newtournament.activity.description,
  });
  descRow.append($('<td>').append(descInput));

  return descRow;
};


////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

Newtournament.formCreateTournament = function() {
  Newtournament.activity.type = $('#type').val();
  Newtournament.activity.nPlayer = $('#n_player').val();
  Newtournament.activity.nRounds = $('#n_rounds').val();
  Newtournament.activity.description = $('#description').val();

  var errorMessage = '';

  if (!Newtournament.activity.type) {
    errorMessage = 'Please select a tournament type.';
  } else if (!Newtournament.activity.nPlayer) {
    errorMessage = 'Please select the number of players.';
  }

  if (errorMessage.length) {
    Env.message = {
      'type': 'error',
      'text': errorMessage,
    };
    Newtournament.showLoggedInPage();
  } else {
    var args =
      {
        type: 'createTournament',
        tournamentType: Newtournament.activity.type,
        nPlayer: Newtournament.activity.nPlayer,
        maxWins: Newtournament.activity.nRounds,
        description: Newtournament.activity.description,
      };

    // N.B. Newtournament.activity is always retained between loads: on
    // failure so the player can correct selections, on success in
    // case the player wants to create another similar game.
    // Therefore, it's fine to pass the form post the same function
    // (showLoggedInPage) for both success and failure conditions.
    Api.apiFormPost(
      args,
      {
        'ok': {
          'type': 'function',
          'msgfunc': Newtournament.setCreateTournamentSuccessMessage,
        },
        'notok': { 'type': 'server', },
      },
      '#newtournament_action_button',
      Newtournament.showLoggedInPage,
      Newtournament.showLoggedInPage
    );
  }
};

////////////////////////////////////////////////////////////////////////
// These functions add pieces of HTML to Newtournament.page

Newtournament.addLoggedOutPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': 'Can\'t create a tournament because you are not logged in',
  }));
  Newtournament.page.append(errorDiv);
};

Newtournament.addInternalErrorPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': 'Can\'t create a tournament.  Something went wrong when ' +
            'loading data from server.',
  }));
  Newtournament.page.append(errorDiv);
};

Newtournament.setCreateTournamentSuccessMessage = function(message, data) {
  Newtournament.justCreatedTournament = true;

  var tournamentId = data.tournamentId;
  var tournamentLink = $('<p>').append(
    $('<a>', {
      'href': 'tournament.html?tournament=' + tournamentId,
      'text': 'Go to tournament page',
    })
  );

  var tournamentPar = $('<p>', {'text': message + ' ', });
  tournamentPar.append(tournamentLink);

  var anotherTournamentPar = $('<p>', { 'id': 'createAnotherTournament', });
  var anotherTournamentBtn = $('<input>', {
    'type': 'button',
    'value': 'Create another tournament?',
  });
  anotherTournamentBtn.click(function() {
    Newtournament.justCreatedTournament = false;
    $('p#createAnotherTournament').hide();
    $('div#newtournament_page > div').show();
    // reset Chosen select dropdowns
//    $('.chosen-select').chosen('destroy').chosen({ search_contains: true });
  });
  anotherTournamentPar.append(anotherTournamentBtn);
  tournamentPar.append(anotherTournamentPar);

  Env.message = {
    'type': 'success',
    'text': '',
    'obj': tournamentPar,
  };
};

////////////////////////////////////////////////////////////////////////
// These functions generate and return pieces of HTML

Newtournament.getSelectRow = function(rowname, selectname, valuedict,
                                greydict, selectedval, isComboBox,
                                blankOption) {
  var selectRow = $('<tr>');
  selectRow.append($('<th>', {'text': rowname + ':', }));

  var selectTd = Newtournament.getSelectTd(rowname, selectname, valuedict,
                                     greydict, selectedval, isComboBox,
                                     blankOption);

  selectRow.append(selectTd);
  return selectRow;
};

Newtournament.getSelectTd = function(nametext, selectname, valuedict,
                               greydict, selectedval, isComboBox, blankOption) {
  var select = $('<select>', {
    'id': selectname,
    'name': selectname,
  });

  if (isComboBox) {
    select.addClass('chosen-select');
  }

  var optionlist = Newtournament.getSelectOptionList(
    nametext, valuedict, greydict, selectedval, blankOption);
  var optioncount = optionlist.length;
  for (var i = 0; i < optioncount; i++) {
    select.append(optionlist[i]);
  }

  var selectTd = $('<td>');
  selectTd.append(select);

  return selectTd;
};

Newtournament.getSelectOptionList = function(
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
