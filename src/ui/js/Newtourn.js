// namespace for this "module"
var Newtourn = {
  'activity': {},
};

Newtourn.bodyDivId = 'newtournament_page';

// Maximum number of characters permitted in the game description
Newtourn.TOURNAMENT_DESCRIPTION_MAX_LENGTH = 255;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Newtourn.showLoggedInPage() is the landing function.  Always call
//   this first
// * Newtourn.getNewtournamentOptions() asks the API for information about
//   players and buttons to be used when creating the tournament.  It clobbers
//   Newtourn.api.  If successful, it calls
// * Newtourn.showStatePage() determines what action to take next based on
//   the received data from getNewtournOptions().  It calls one of several
//   functions, Newtourn.action<SomeAction>()
// * each Newtourn.action<SomeAction>() function must set
//   Newtourn.page and Newtourn.form, then call Login.arrangePage()
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Newtourn.showLoggedInPage = function() {
  if (!Newtourn.activity.type) {
    Newtourn.activity.type = Env.getParameterByName('tournamentType');
  }
  if (!Newtourn.activity.nPlayer) {
    Newtourn.activity.nPlayer = Env.getParameterByName('nPlayer');
  }
  if (!Newtourn.activity.nRounds) {
    Newtourn.activity.nRounds = Env.getParameterByName('maxWins');
  }

  Newtourn.showPage();
};

//Newtourn.getNewtournData = function(callback) {
////  Env.callAsyncInParallel(
////    [
////      { 'func': Api.getButtonData, 'args': [ null ] },
////      Api.getPlayerData,
////    ], callback);
//};

Newtourn.showPage = function() {
  Newtourn.actionCreateTourn();
};

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by Newtourn.js.
// Each function should start by populating Newtourn.page and Newtourn.form
// ane end by invoking Login.arrangePage();

Newtourn.actionLoggedOut = function() {

  // Create empty page and undefined form objects to be filled later
  Newtourn.page = $('<div>');
  Newtourn.form = null;

  // Add the "logged out player" HTML contents
  Newtourn.addLoggedOutPage();

  // Lay out the page
  Login.arrangePage(Newtourn.page, Newtourn.form, '#newtourn_action_button');
};

Newtourn.actionInternalErrorPage = function() {

  // Create empty page and undefined form objects to be filled later
  Newtourn.page = $('<div>');
  Newtourn.form = null;

  // Add the internal error HTML contents
  Newtourn.addInternalErrorPage();

  // Lay out the page
  Login.arrangePage(Newtourn.page, Newtourn.form, '#newtourn_action_button');
};

Newtourn.actionCreateTourn = function() {
  // Create empty page and undefined form objects to be filled later
  Newtourn.page = $('<div>');
  if (Newtourn.justCreatedTourn === true) {
    Newtourn.page.css('display', 'none');
  }
  Newtourn.form = null;

  var creatediv = $('<div>');
  creatediv.append($('<div>', {
    'class': 'title2',
    'text': 'Create a new tournament',
  }));
  var createform = $('<form>', {
    'id': 'newtourn_action_form',
    'action': 'javascript:void(0);',
  });

  // add generic options table to the form
  createform.append(Newtourn.createMiscOptionsTable()).append($('<br />'));

//  // add table of button selections to the form
//  createform.append(Newtourn.createButtonOptionsTable()).append($('<br />'));

  // Form submission button
  createform.append($('<button>', {
    'id': 'newtourn_action_button',
    'text': 'Create tournament!',
  }));
  creatediv.append(createform);

  Newtourn.page.append(creatediv);

  // Function to invoke on button click
  Newtourn.form = Newtourn.formCreateTourn;

  // Lay out the page
  Login.arrangePage(Newtourn.page, Newtourn.form, '#newtourn_action_button');

  // Unlock player 1 if this was previously unlocked before form submission
  if (Newtourn.activity.isPlayer1Unlocked) {
    $('#player1_toggle').click();
  }

  // activate all Chosen comboboxes
//  $('.chosen-select').chosen({ search_contains: true });
};

Newtourn.createMiscOptionsTable = function() {
  var miscOptionsTable = $('<table>', {'id': 'newtourn_create_table', });

  miscOptionsTable.append(Newtourn.createTypeRow());
  miscOptionsTable.append(Newtourn.createNPlayerRow());
  miscOptionsTable.append(Newtourn.createRoundSelectRow());
  miscOptionsTable.append(Newtourn.createDescRow());

  return miscOptionsTable;
};

Newtourn.createTypeRow = function() {
  var selectRow = Newtourn.getSelectRow(
    'Tournament type',
    'type',
    {
      'Single Elimination': 'Single Elimination',
    },
    null,
    Newtourn.activity.type
  );

  return selectRow;
};

Newtourn.createNPlayerRow = function() {
  var selectRow = Newtourn.getSelectRow(
    'Number of players',
    'n_player',
    {
      '4': '4',
      '8': '8',
      '16': '16',
      '32': '32',
    },
    null,
    Newtourn.activity.nPlayer
  );

  return selectRow;
};

Newtourn.createRoundSelectRow = function() {
  if (!('nRounds' in Newtourn.activity) || !Newtourn.activity.nRounds) {
    Newtourn.activity.nRounds = '3';
  }
  var selectRow = Newtourn.getSelectRow(
    'Each game has',
    'n_rounds',
    {
      '1': '1 round',
      '2': '2 rounds',
      '3': '3 rounds',
      '4': '4 rounds',
      '5': '5 rounds',
    },
    null,
    Newtourn.activity.nRounds
  );

  return selectRow;
};

Newtourn.createDescRow = function() {
  if (!('description' in Newtourn.activity)) {
    Newtourn.activity.description = '';
  }
  var descRow = $('<tr>');
  descRow.append($('<th>', {'text': 'Description (optional):' }));
  var descInput = $('<textarea>', {
    'id': 'description',
    'name': 'description',
    'rows': '3',
    'class': 'gameDescInput',
    'maxlength': Newtourn.TOURNAMENT_DESCRIPTION_MAX_LENGTH,
    'text': Newtourn.activity.description,
  });
  descRow.append($('<td>').append(descInput));

  return descRow;
};


////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

Newtourn.formCreateTourn = function() {
  Newtourn.activity.type = $('#type').val();
  Newtourn.activity.nPlayer = $('#n_player').val();
  Newtourn.activity.nRounds = $('#n_rounds').val();
  Newtourn.activity.description = $('#description').val();

  var errorMessage = '';

  if (!Newtourn.activity.type) {
    errorMessage = 'Please select a tournament type.';
  } else if (!Newtourn.activity.nPlayer) {
    errorMessage = 'Please select the number of players.';
  }

  if (errorMessage.length) {
    Env.message = {
      'type': 'error',
      'text': errorMessage,
    };
    Newtourn.showLoggedInPage();
  } else {
    var args =
      {
        type: 'createTournament',
        tournamentType: Newtourn.activity.type,
        nPlayer: Newtourn.activity.nPlayer,
        maxWins: Newtourn.activity.nRounds,
        description: Newtourn.activity.description,
      };

    // N.B. Newtourn.activity is always retained between loads: on
    // failure so the player can correct selections, on success in
    // case the player wants to create another similar game.
    // Therefore, it's fine to pass the form post the same function
    // (showLoggedInPage) for both success and failure conditions.
    Api.apiFormPost(
      args,
      {
        'ok': {
          'type': 'function',
          'msgfunc': Newtourn.setCreateTournSuccessMessage,
        },
        'notok': { 'type': 'server', },
      },
      '#newtourn_action_button',
      Newtourn.showLoggedInPage,
      Newtourn.showLoggedInPage
    );
  }
};

////////////////////////////////////////////////////////////////////////
// These functions add pieces of HTML to Newtourn.page

Newtourn.addLoggedOutPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': 'Can\'t create a tournament because you are not logged in',
  }));
  Newtourn.page.append(errorDiv);
};

Newtourn.addInternalErrorPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': 'Can\'t create a tournament.  Something went wrong when ' +
            'loading data from server.',
  }));
  Newtourn.page.append(errorDiv);
};

Newtourn.setCreateTournSuccessMessage = function(message, data) {
  Newtourn.justCreatedTourn = true;

  var tournamentId = data.tournamentId;
  var tournLink = $('<a>', {
    'href': 'tournament.html?tournament=' + tournamentId,
    'text': 'Go to tournament page',
  });

  var tournPar = $('<p>', {'text': message + ' ', });
  tournPar.append(tournLink);

  var anotherTournPar = $('<p>', { 'id': 'createAnotherTourn', });
  var anotherTournBtn = $('<input>', {
    'type': 'button',
    'value': 'Create another tournament?',
  });
  anotherTournBtn.click(function() {
    Newtourn.justCreatedTourn = false;
    $('p#createAnotherTourn').hide();
    $('div#newtournament_page > div').show();
    // reset Chosen select dropdowns
//    $('.chosen-select').chosen('destroy').chosen({ search_contains: true });
  });
  anotherTournPar.append(anotherTournBtn);
  tournPar.append(anotherTournPar);

  Env.message = {
    'type': 'success',
    'text': '',
    'obj': tournPar,
  };
};

////////////////////////////////////////////////////////////////////////
// These functions generate and return pieces of HTML

Newtourn.getSelectRow = function(rowname, selectname, valuedict,
                                greydict, selectedval, isComboBox,
                                blankOption) {
  var selectRow = $('<tr>');
  selectRow.append($('<th>', {'text': rowname + ':', }));

  var selectTd = Newtourn.getSelectTd(rowname, selectname, valuedict,
                                     greydict, selectedval, isComboBox,
                                     blankOption);

  selectRow.append(selectTd);
  return selectRow;
};

Newtourn.getSelectTd = function(nametext, selectname, valuedict,
                               greydict, selectedval, isComboBox, blankOption) {
  var select = $('<select>', {
    'id': selectname,
    'name': selectname,
  });

  if (isComboBox) {
    select.addClass('chosen-select');
  }

  var optionlist = Newtourn.getSelectOptionList(
    nametext, valuedict, greydict, selectedval, blankOption);
  var optioncount = optionlist.length;
  for (var i = 0; i < optioncount; i++) {
    select.append(optionlist[i]);
  }

  var selectTd = $('<td>');
  selectTd.append(select);

  return selectTd;
};

Newtourn.getSelectOptionList = function(
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
