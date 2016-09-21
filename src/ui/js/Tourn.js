// namespace for this "module"
var Tourn = {
  'activity': {},
};

Tourn.bodyDivId = 'tourn_page';

// Tourn states must match those reported by the API
Tourn.TOURN_STATE_START_TOURNAMENT = 'START_TOURNAMENT';
Tourn.TOURN_STATE_JOIN_TOURNAMENT = 'JOIN_TOURNAMENT';
Tourn.TOURN_STATE_SHUFFLE_PLAYERS = 'SHUFFLE_PLAYERS';
Tourn.TOURN_STATE_START_ROUND = 'START_ROUND';
Tourn.TOURN_STATE_PLAY_GAMES = 'PLAY_GAMES';
Tourn.TOURN_STATE_END_ROUND = 'END_ROUND';
Tourn.TOURN_STATE_END_TOURNAMENT = 'END_TOURNAMENT';

Tourn.TOURN_STATE_CANCELLED = 'CANCELLED';

// Convenience HTML used in the mat layout to break text
Tourn.SPACE_BULLET = ' &nbsp;&bull;&nbsp; ';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Tourn.showLoggedInPage() is the landing function.  Always call this first
//   if logged in.
// * Tourn.getCurrentTourn() asks the API for information about the
//   requested tournament.  It clobbers Api.tourn.  If successful, it calls
// * Tourn.showStatePage() determines what action to take next based on
//   the received data from getCurrentTourn().  It calls one of several
//   functions, Tourn.action<SomeAction>(), and then calls Login.arrangePage()
// * each Tourn.action<SomeAction>() function must set Tourn.page and
//   Tourn.form
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Tourn.showLoggedInPage = function() {
  // Find the current game, and invoke that with the "parse game state"
  // callback
  Tourn.getCurrentTourn(Tourn.showStatePage);
};

// Redraw the page after a previous action succeeded: to do this,
// clear all activity variables set by the previous invocation
Tourn.redrawTournPageSuccess = function() {
  Tourn.activity = {};
  Tourn.showLoggedInPage();
};

// Redraw the page after a previous action failed: to do this,
// retain activity variables where it makes sense to do so
Tourn.redrawTournPageFailure = function() {
  Tourn.showLoggedInPage();
};

// the current tourn should be provided as a GET parameter to the page
Tourn.getCurrentTourn = function(callbackfunc) {
  Tourn.tourn = Env.getParameterByName('tournament');
  if (Tourn.tourn === null) {
    Env.message = {
      'type': 'error',
      'text': 'No tournament specified.  Nothing to do.'
    };
    return callbackfunc();
  }
  if ($.isNumeric(Tourn.tourn) === false) {
    Env.message = {
      'type': 'error',
      'text': 'Specified tournament is not a valid number.  Nothing to do.'
    };
    return callbackfunc();
  }

  Api.getTournData(Tourn.tourn, callbackfunc);

  Tourn.showStatePage();
};

// Assemble and display the tournament portion of the page
Tourn.showStatePage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  // Figure out what to do next based on the tournament state
  if (Api.tourn.load_status == 'ok') {
    Tourn.showTournContents();
  } else {
    // Tourn retrieval failed, so just layout the page with no contents
    // and whatever message was received while trying to load the game
    Tourn.page = null;
    Tourn.form = null;
  }

  // Now lay out the page
  Login.arrangePage(Tourn.page, Tourn.form, '#tourn_action_button');
};

Tourn.showTournContents = function() {
  // nothing to do on button click
  Tourn.form = null;

  Tourn.page = $('<div>');
  Tourn.pageAddTournHeader();

  if (Api.tourn.isWatched) {
    Tourn.pageAddUnfollowTournLink();
  } else {
    Tourn.pageAddFollowTournLink();
  }

  if (Api.tourn.tournamentState == Tourn.TOURN_STATE_END_TOURNAMENT) {
    Tourn.pageAddWinnerInfo();
    Tourn.page.append($('<br>'));
  }

  if (Api.tourn.tournamentState != Tourn.TOURN_STATE_CANCELLED) {
    Tourn.pageAddPlayerInfo();
    Tourn.page.append($('<br>'));

    Tourn.pageAddActions();
    Tourn.page.append($('<br>'));
  }

  if (Api.tourn.gameIdArrayArray.length > 0) {
    Tourn.showGames();
  }
};

Tourn.pageAddTournHeader = function() {
  var tournTitle =
    'Tournament #' + Api.tourn.tournamentId + Tourn.SPACE_BULLET;
  if (Api.tourn.tournamentState == Tourn.TOURN_STATE_END_TOURNAMENT) {
    tournTitle += 'Completed';
  } else if (Api.tourn.tournamentState == Tourn.TOURN_STATE_CANCELLED) {
    tournTitle += 'Cancelled';
  } else if (Api.tourn.tournamentState == Tourn.TOURN_STATE_JOIN_TOURNAMENT) {
    tournTitle += 'New Tournament';
  } else {
    tournTitle += 'Round #' + Api.tourn.roundNumber;
  }

  tournTitle += Tourn.SPACE_BULLET + 'Created by ' + Api.tourn.creatorName;

  $('title').html(tournTitle + ' &mdash; Button Men Online');

  Tourn.page.append(
    $('<div>', {
      'id': 'tournament_id',
      'html': tournTitle,
    }));
//  var bgcolor = '#ffffff';
//  if (Api.tourn.player.waitingOnAction) {
//    bgcolor = Tourn.color.player;
//  } else if (Api.tourn.opponent.waitingOnAction) {
//    bgcolor = Tourn.color.opponent;
//  }

  if (Api.tourn.description) {
    Tourn.page.append($('<div>', {
      'text': Api.tourn.description,
      'class': 'gameDescDisplay',
    }));
  }

  Tourn.page.append($('<br>'));

  Tourn.pageAddTournInfo();
  Tourn.page.append($('<br>'));
};

Tourn.pageAddUnfollowTournLink = function () {
  if (Api.tourn.isParticipant &&
      (Api.tourn.tournamentState != Tourn.TOURN_STATE_END_TOURNAMENT) &&
      (Api.tourn.tournamentState != Tourn.TOURN_STATE_CANCELLED)) {
    return;
  }

  var unfollowTournDiv = $('<div>', {
    'class': 'follow_tourn',
  });
  var unfollowTournLink = $('<a>', {
    'text': 'Unfollow tournament',
    'href': '#',
    'data-tournId': Api.tourn.tournamentId,
  });
  unfollowTournLink.click(Tourn.formUnfollowTournament);

  unfollowTournDiv.append('[')
          .append(unfollowTournLink)
          .append(']');

  Tourn.page.append(unfollowTournDiv);
};

Tourn.formUnfollowTournament = function(e) {
  e.preventDefault();
  var args = {'type': 'unfollowTourn',
              'tournId': $(this).attr('data-tournId'),};
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully unfollowed tournament', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Tourn.showLoggedInPage,
    Tourn.showLoggedInPage);
};

Tourn.pageAddFollowTournLink = function () {
  var followTournDiv = $('<div>', {
    'class': 'follow_tourn',
  });
  var followTournLink = $('<a>', {
    'text': 'Follow tournament',
    'href': '#',
    'data-tournId': Api.tourn.tournamentId,
  });
  followTournLink.click(Tourn.formFollowTournament);

  followTournDiv.append('[')
          .append(followTournLink)
          .append(']');

  Tourn.page.append(followTournDiv);
};

Tourn.formFollowTournament = function(e) {
  e.preventDefault();
  var args = {'type': 'followTourn',
              'tournId': $(this).attr('data-tournId'),};
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully followed tournament', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Tourn.showLoggedInPage,
    Tourn.showLoggedInPage);
};

Tourn.pageAddTournInfo = function () {
  var infoDiv = $('<div>');
  Tourn.page.append(infoDiv);

  var tournTypePar = $('<p>', {
    'text': 'Tournament type: ' + Tourn.friendlyTournType(Api.tourn.type),
  });
  infoDiv.append(tournTypePar);

  var nPlayersPar = $('<p>', {
    'text': 'Number of players: ' + Api.tourn.nPlayers,
  });
  infoDiv.append(nPlayersPar);
};

Tourn.friendlyTournType = function(tournType) {
  switch (tournType) {
  case 'SingleElimination':
    return 'Single Elimination';
  default:
    return tournType;
  }
};

Tourn.pageAddWinnerInfo = function () {
  var winnerDiv = $('<div>');
  Tourn.page.append(winnerDiv);

  var winnerIdx = Api.tourn.remainCountArray.findIndex(
    function(x) {return (x > 0);}
  );
  var winnerPar = $('<p>', {
    'text': 'Winner: ' + Api.tourn.playerDataArray[winnerIdx].playerName
  });
  winnerDiv.append(winnerPar);
};

Tourn.pageAddPlayerInfo = function () {
  var playerDiv = $('<div>');
  Tourn.page.append(playerDiv);

  var playerArray = [];
  var playerList = '';
  if (Api.tourn.playerDataArray) {
    var nPlayersJoined = Api.tourn.playerDataArray.length;
    for (var playerIdx = 0; playerIdx < nPlayersJoined; playerIdx++) {
      playerArray.push(Api.tourn.playerDataArray[playerIdx].playerName);
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

Tourn.pageAddActions = function () {
  if (Api.tourn.tournamentState == Tourn.TOURN_STATE_JOIN_TOURNAMENT) {
    var actionDiv = $('<div>', {
      'id': 'actionDiv',
    });
    Tourn.page.append(actionDiv);

    if (Api.tourn.isParticipant) {
      var leaveLink = $('<a>', {
        'text': '[Leave Tournament]',
        'href': '#',
        'data-tournamentId': Api.tourn.tournamentId,
      });
      leaveLink.click(Tourn.formLeaveTourn);
      actionDiv.append(leaveLink);
    } else {
      var joinLink = $('<a>', {
        'text': '[Select Button for Tournament]',
        'id': 'joinLink',
        'href': '#',
        'data-tournamentId': Api.tourn.tournamentId,
      });
      joinLink.click(Tourn.formChooseButton);
      actionDiv.append(joinLink);

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
    }

    if (Api.tourn.isCreator) {
      var cancelLink = $('<a>', {
        'text': '[Cancel Tournament]',
        'id': 'cancelLink',
        'href': '#',
        'data-tournamentId': Api.tourn.tournamentId,
      });
      cancelLink.click(Tourn.formCancelTourn);
      actionDiv.append('&nbsp;');
      actionDiv.append(cancelLink);
    }
  }
};

Tourn.formChooseButton = function () {
  // show "Loading buttons ..."
  $('#buttonSelectDiv').show();
  $('#joinLink').hide();
  $('#cancelLink').hide();

  // load list of Button Men
  Api.getButtonData(null, function(){
    // add button selector table
    var buttonSelector = Newgame.createButtonOptionsTable(false);
    $('#buttonSelectDiv').append(buttonSelector).append($('<br />'));

    // add form submission button
    var joinButton = $('<button>', {
      'id': 'join_tournament_button',
      'text': 'Join Tournament!',
    });
    joinButton.click(Tourn.formJoinTourn);
    $('#buttonSelectDiv').append(joinButton);

    // hide "Loading buttons ..."
    $('#loadingButtonsPar').hide();

    // activate all Chosen comboboxes
    $('.chosen-select').chosen({ search_contains: true });
  });
};

Tourn.formActTourn = function (type, successText, e, buttonNameArray) {
  e.preventDefault();
  var args = {
    'type': 'actTourn',
    'tourn': Api.tourn.tournamentId,
    'action': type,
  };

  if (typeof buttonNameArray !== 'undefined') {
    args.button_names = buttonNameArray;
  }

  var messages = {
    'ok': { 'type': 'fixed', 'text': successText, },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Tourn.showLoggedInPage,
    Tourn.showLoggedInPage);
};

Tourn.formCancelTourn = function (e) {
  Tourn.formActTourn('cancel', 'Successfully cancelled tournament', e);
};

Tourn.formJoinTourn = function (e) {
  Tourn.formActTourn(
    'join',
    'Successfully joined tournament',
    e,
    '["' + $('#player_button').val() + '"]'
  );
};

Tourn.formLeaveTourn = function (e) {
  Tourn.formActTourn('leave', 'Successfully left tournament', e);
};

Tourn.showGames = function () {
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

  var nRound = Api.tourn.gameIdArrayArray.length;

  for (roundIdx = nRound - 1; roundIdx >= 0; roundIdx--) {
    roundVal = roundIdx + 1;
    gameIdArray = Api.tourn.gameIdArrayArray[roundIdx];

    for (gameIdx = gameIdArray.length - 1; gameIdx >= 0; gameIdx--) {
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
  Tourn.page.append(gameDiv);
};
