// namespace for this "module"
var Tournament = {
  'activity': {},
};

Tournament.bodyDivId = 'tournament_page';

// Tournament states must match those reported by the API
Tournament.TOURN_STATE_START_TOURNAMENT = 'START_TOURNAMENT';
Tournament.TOURN_STATE_JOIN_TOURNAMENT = 'JOIN_TOURNAMENT';
Tournament.TOURN_STATE_SHUFFLE_PLAYERS = 'SHUFFLE_PLAYERS';
Tournament.TOURN_STATE_START_ROUND = 'START_ROUND';
Tournament.TOURN_STATE_PLAY_GAMES = 'PLAY_GAMES';
Tournament.TOURN_STATE_END_ROUND = 'END_ROUND';
Tournament.TOURN_STATE_END_TOURNAMENT = 'END_TOURNAMENT';

Tournament.TOURN_STATE_CANCELLED = 'CANCELLED';

// Convenience HTML used in the mat layout to break text
Tournament.SPACE_BULLET = ' &nbsp;&bull;&nbsp; ';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Tournament.showLoggedInPage() is the landing function.  Always call this
//   first if logged in.
// * Tournament.getCurrentTournament() asks the API for information about the
//   requested tournament.  It clobbers Api.tournament.  If successful, it calls
// * Tournament.showStatePage() determines what action to take next based on
//   the received data from getCurrentTournament().  It calls one of several
//   functions, Tournament.action<SomeAction>(), and then calls
//   Login.arrangePage()
// * each Tournament.action<SomeAction>() function must set Tournament.page and
//   Tournament.form
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Tournament.showLoggedInPage = function() {
  // Find the current game, and invoke that with the "parse game state"
  // callback
  Tournament.getCurrentTournament(Tournament.showStatePage);
};

// Redraw the page after a previous action succeeded: to do this,
// clear all activity variables set by the previous invocation
Tournament.redrawTournamentPageSuccess = function() {
  Tournament.activity = {};
  Tournament.showLoggedInPage();
};

// Redraw the page after a previous action failed: to do this,
// retain activity variables where it makes sense to do so
Tournament.redrawTournamentPageFailure = function() {
  Tournament.showLoggedInPage();
};

// the current tournament should be provided as a GET parameter to the page
Tournament.getCurrentTournament = function(callbackfunc) {
  Tournament.tournament = Env.getParameterByName('tournament');
  if (Tournament.tournament === null) {
    Env.message = {
      'type': 'error',
      'text': 'No tournament specified.  Nothing to do.'
    };
    return callbackfunc();
  }
  if ($.isNumeric(Tournament.tournament) === false) {
    Env.message = {
      'type': 'error',
      'text': 'Specified tournament is not a valid number.  Nothing to do.'
    };
    return callbackfunc();
  }

  Api.getTournamentData(Tournament.tournament, callbackfunc);

  Tournament.showStatePage();
};

// Assemble and display the tournament portion of the page
Tournament.showStatePage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  // Figure out what to do next based on the tournament state
  if (Api.tournament.load_status === 'ok') {
    Tournament.showTournamentContents();
  } else {
    // Tournament retrieval failed, so just layout the page with no contents
    // and whatever message was received while trying to load the game
    Tournament.page = null;
    Tournament.form = null;
  }

  // Now lay out the page
  Login.arrangePage(
    Tournament.page,
    Tournament.form,
    '#tournament_action_button'
  );
};

Tournament.showTournamentContents = function() {
  // nothing to do on button click
  Tournament.form = null;

  Tournament.page = $('<div>');

  if (Api.tournament.isWatched) {
    Tournament.pageAddUnfollowTournamentLink();
  } else {
    Tournament.pageAddFollowTournamentLink();
  }

  Tournament.pageAddTournamentHeader();

  if (Api.tournament.tournamentState ===
      Tournament.TOURN_STATE_END_TOURNAMENT) {
    Tournament.pageAddWinnerInfo();
    Tournament.page.append($('<br>'));
  }

  if (Api.tournament.tournamentState !== Tournament.TOURN_STATE_CANCELLED) {
    Tournament.pageAddPlayerInfo();
    Tournament.page.append($('<br>'));

    Tournament.pageAddActions();
    Tournament.page.append($('<br>'));
  }

  if (Api.tournament.gameIdArrayArray.length > 0) {
    Tournament.showGames();
  }
};

Tournament.pageAddTournamentHeader = function() {
  var tournamentTitle =
    'Tournament #' + Api.tournament.tournamentId + Tournament.SPACE_BULLET;
  if (Api.tournament.tournamentState ===
      Tournament.TOURN_STATE_END_TOURNAMENT) {
    tournamentTitle += 'Completed';
  } else if (Api.tournament.tournamentState ===
             Tournament.TOURN_STATE_CANCELLED) {
    tournamentTitle += 'Cancelled';
  } else if (Api.tournament.tournamentState ===
             Tournament.TOURN_STATE_JOIN_TOURNAMENT) {
    tournamentTitle += 'New Tournament';
  } else {
    tournamentTitle += 'Round #' + Api.tournament.roundNumber;
  }

  tournamentTitle += Tournament.SPACE_BULLET + 'Created by ' +
                     Api.tournament.creatorName;

  $('title').html(tournamentTitle + ' &mdash; Button Men Online');

  Tournament.page.append(
    $('<div>', {
      'id': 'tournament_id',
      'html': tournamentTitle,
    }));
//  var bgcolor = '#ffffff';
//  if (Api.tournament.player.waitingOnAction) {
//    bgcolor = Tournament.color.player;
//  } else if (Api.tournament.opponent.waitingOnAction) {
//    bgcolor = Tournament.color.opponent;
//  }

  if (Api.tournament.description) {
    Tournament.page.append($('<div>', {
      'text': Api.tournament.description,
      'class': 'gameDescDisplay',
    }));
  }

  Tournament.page.append($('<br>'));

  Tournament.pageAddTournamentInfo();
  Tournament.page.append($('<br>'));
};

Tournament.pageAddUnfollowTournamentLink = function () {
  if (Api.tournament.isParticipant &&
      (Api.tournament.tournamentState !==
       Tournament.TOURN_STATE_END_TOURNAMENT) &&
      (Api.tournament.tournamentState !==
       Tournament.TOURN_STATE_CANCELLED)) {
    return;
  }

  var unfollowTournamentDiv = $('<div>', {
    'class': 'follow_tournament',
  });
  var unfollowTournamentLink = $('<a>', {
    'text': 'Unfollow tournament',
    'href': '#',
    'data-tournamentId': Api.tournament.tournamentId,
  });
  unfollowTournamentLink.click(Tournament.formUnfollowTournament);

  unfollowTournamentDiv.append('[')
          .append(unfollowTournamentLink)
          .append(']');

  Tournament.page.append(unfollowTournamentDiv);
};

Tournament.formUnfollowTournament = function(e) {
  e.preventDefault();
  var args = {'type': 'unfollowTournament',
              'tournamentId': $(this).attr('data-tournamentId'),};
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully unfollowed tournament', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Tournament.showLoggedInPage,
    Tournament.showLoggedInPage);
};

Tournament.pageAddFollowTournamentLink = function () {
  var followTournamentDiv = $('<div>', {
    'class': 'follow_tournament',
  });
  var followTournamentLink = $('<a>', {
    'text': 'Follow tournament',
    'href': '#',
    'data-tournamentId': Api.tournament.tournamentId,
  });
  followTournamentLink.click(Tournament.formFollowTournament);

  followTournamentDiv.append('[')
          .append(followTournamentLink)
          .append(']');

  Tournament.page.append(followTournamentDiv);
};

Tournament.formFollowTournament = function(e) {
  e.preventDefault();
  var args = {'type': 'followTournament',
              'tournamentId': $(this).attr('data-tournamentId'),};
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully followed tournament', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Tournament.showLoggedInPage,
    Tournament.showLoggedInPage);
};

Tournament.pageAddTournamentInfo = function () {
  var infoDiv = $('<div>');
  Tournament.page.append(infoDiv);

  var tournamentTypePar = $('<p>', {
    'text': 'Tournament type: ' +
            Tournament.friendlyTournamentType(Api.tournament.type),
  });
  infoDiv.append(tournamentTypePar);

  var nPlayersPar = $('<p>', {
    'text': 'Number of players: ' + Api.tournament.nPlayers,
  });
  infoDiv.append(nPlayersPar);
};

Tournament.friendlyTournamentType = function(tournamentType) {
  switch (tournamentType) {
  case 'SingleElimination':
    return 'Single Elimination';
  default:
    return tournamentType;
  }
};

Tournament.pageAddWinnerInfo = function () {
  var winnerDiv = $('<div>');
  Tournament.page.append(winnerDiv);

  var winnerIdx = Api.tournament.remainCountArray.findIndex(
    function(x) {return (x > 0);}
  );
  var winnerPar = $('<p>', {
    'text': 'Winner: ' + Api.tournament.playerDataArray[winnerIdx].playerName
  });
  winnerDiv.append(winnerPar);
};

Tournament.pageAddPlayerInfo = function () {
  var playerDiv = $('<div>');
  Tournament.page.append(playerDiv);

  var playerArray = [];
  var playerList = '';
  if (Api.tournament.playerDataArray) {
    var nPlayersJoined = Api.tournament.playerDataArray.length;
    for (var playerIdx = 0; playerIdx < nPlayersJoined; playerIdx++) {
      playerArray.push(Api.tournament.playerDataArray[playerIdx].playerName);
    }
    playerList = playerArray.join(', ');
  } else {
    playerList = 'None';
  }

  var playersText = 'Players in this tournament: ' + playerList;
  var playersTitlePar = $('<p>', {
    'text': playersText,
  });

  playerDiv.append(playersTitlePar);
};

Tournament.pageAddActions = function () {
  if (Api.tournament.tournamentState ===
      Tournament.TOURN_STATE_JOIN_TOURNAMENT) {
    var actionDiv = $('<div>', {
      'id': 'actionDiv',
    });
    Tournament.page.append(actionDiv);

    if (Api.tournament.isParticipant) {
      var leaveLink = $('<a>', {
        'text': '[Leave Tournament]',
        'href': '#',
        'data-tournamentId': Api.tournament.tournamentId,
      });
      leaveLink.click(Tournament.formLeaveTournament);
      actionDiv.append('<p>').append(leaveLink);

      var changeLink = $('<a>', {
        'text': '[Change button]',
        'id': 'changeLink',
        'href': '#',
        'data-tournamentId': Api.tournament.tournamentId,
      });
      changeLink.click(Tournament.formChooseButton);
      actionDiv.append('<p>').append(changeLink);
    } else {
      var joinLink = $('<a>', {
        'text': '[Join Tournament]',
        'id': 'joinLink',
        'href': '#',
        'data-tournamentId': Api.tournament.tournamentId,
      });
      joinLink.click(Tournament.formChooseButton);
      actionDiv.append(joinLink);
    }

    var buttonSelectDiv = $('<div>', {
      'id': 'buttonSelectDiv',
    });
    var loadingButtonsPar = $('<p>', {
      'id': 'loadingButtonsPar',
      'text': 'Loading buttons ...',
    });

    buttonSelectDiv.append(loadingButtonsPar);
    actionDiv.append(buttonSelectDiv);

    buttonSelectDiv.hide();

    if (Api.tournament.isCreator) {
      var cancelLink = $('<a>', {
        'text': '[Cancel Tournament]',
        'id': 'cancelLink',
        'href': '#',
        'data-tournamentId': Api.tournament.tournamentId,
      });
      cancelLink.click(Tournament.formCancelTournament);
      actionDiv.append('&nbsp;');
      actionDiv.append(cancelLink);
    }
  }
};

Tournament.formChooseButton = function () {
  // show "Loading buttons ..."
  $('#buttonSelectDiv').show();
  $('#joinLink').hide();
  $('#changeLink').hide();
  $('#cancelLink').hide();

  // load list of Button Men
  Api.getButtonData(null, function(){
    // add button selector table
    ButtonSelection.loadButtonsIntoDicts();
    
    var buttonSelector = ButtonSelection.getSingleButtonOptionsTable('player');
    $('#buttonSelectDiv').append(buttonSelector).append($('<br />'));

    // add form submission button
    if ($('#joinLink').length) {
      var joinButton = $('<button>', {
        'id': 'join_tournament_button',
        'text': 'Join Tournament!',
      });
      joinButton.click(Tournament.formJoinTournament);
      $('#buttonSelectDiv').append(joinButton);
    } else if ($('#changeLink').length) {
      var changeButton = $('<button>', {
        'id': 'change_button',
        'text': 'Select Button',
      });
      changeButton.click(Tournament.formChangeButton);
      $('#buttonSelectDiv').append(changeButton);
    }

    // hide "Loading buttons ..."
    $('#loadingButtonsPar').hide();

    // activate all Chosen comboboxes
    $('.chosen-select').chosen({ search_contains: true });
  });
};

Tournament.formUpdateTournament = function (
  type, successText, e, buttonNameArray
) {
  e.preventDefault();
  
  if (!($.isArray(buttonNameArray)) || (buttonNameArray.length < 1)) {
    Env.message = {
      'type': 'error',
      'text': 'buttonNameArray must be a non-empty array.'
    };
    return Tournament.showLoggedInPage();
  }
  
  if ('' === buttonNameArray[0]) {
    Env.message = {
      'type': 'error',
      'text': 'No button chosen.'
    };
    return Tournament.showLoggedInPage();
  }
  
  var args = {
    'type': 'updateTournament',
    'tournamentId': Api.tournament.tournamentId,
    'action': type,
  };

  if (typeof buttonNameArray !== 'undefined') {
    args.buttonNames = buttonNameArray;
  }

  var messages = {
    'ok': { 'type': 'fixed', 'text': successText, },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Tournament.showLoggedInPage,
    Tournament.showLoggedInPage);
};

Tournament.formChangeButton = function (
  type, successText, e, buttonNameArray
) {
  e.preventDefault();

  var args = {
    'type': 'updateTournament',
    'tournament': Api.tournament.tournamentId,
    'action': type,
  };

  if (typeof buttonNameArray !== 'undefined') {
    args.buttonNames = buttonNameArray;
  }

  var messages = {
    'ok': { 'type': 'fixed', 'text': successText, },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Tournament.showLoggedInPage,
    Tournament.showLoggedInPage);
};

Tournament.formCancelTournament = function (e) {
  Tournament.formUpdateTournament(
    'cancel', 'Successfully cancelled tournament', e
  );
};

Tournament.formJoinTournament = function (e) {
  Tournament.formUpdateTournament(
    'join',
    'Successfully joined tournament',
    e,
    [$('#player_button').val()]
  );
};

Tournament.formChangeButton = function (e) {
  Tournament.formUpdateTournament(
    'changeButton',
    'Successfully selected button',
    e,
    [$('#player_button').val()]
  );
};

Tournament.formLeaveTournament = function (e) {
  Tournament.formUpdateTournament('leave', 'Successfully left tournament', e);
};

Tournament.showGames = function () {
  var gameCell;
  var gameId;
  var gameIdArray;
  var gameIdx;
  var gameRow;
  var roundCell;
  var roundIdx;
  var roundVal;

  var gameDiv = $('<div>');
  var gameTable = $('<table>');

  var nRound = Api.tournament.gameIdArrayArray.length;

  for (roundIdx = 0; roundIdx < nRound; roundIdx++) {
    roundVal = roundIdx + 1;
    gameIdArray = Api.tournament.gameIdArrayArray[roundIdx];

    for (gameIdx = 0; gameIdx < gameIdArray.length; gameIdx++) {
      roundCell = $('<td>').text('Round ' + roundVal);

      gameId = gameIdArray[gameIdx];
      gameCell = $('<td>').append($('<a>', {
        'href': 'game.html?game=' + gameId,
        'text': 'Game ' + gameId,
      }));

      gameRow = $('<tr>').append(roundCell, gameCell);

      gameTable.append(gameRow);
    }
  }

  gameDiv.append(gameTable);
  Tournament.page.append(gameDiv);
};
