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

  Tournament.pageAddFollowTournamentLink();
  Tournament.pageAddUnfollowTournamentLink();
  Tournament.pageAddDismissTournamentLink();

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

  if (Api.tournament.gameDataArrayArray.length > 0) {
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
      'id': 'tournament_desc',
      'html': Env.applyBbCodeToHtml(Api.tournament.description),
      'class': 'gameDescDisplay',
    }));
  }

  Tournament.page.append($('<br>'));

  Tournament.pageAddTournamentInfo();
  Tournament.page.append($('<br>'));
};

Tournament.pageAddDismissTournamentLink = function () {
  // must be in a completed tournament and following it
  if (!(
        Api.tournament.isParticipant &&
        (
          (Api.tournament.tournamentState ==
             Tournament.TOURN_STATE_END_TOURNAMENT) ||
          (Api.tournament.tournamentState ==
             Tournament.TOURN_STATE_CANCELLED)
        ) &&
        Api.tournament.isWatched
     )) {
    return;
  }

  var dismissTournamentDiv = $('<div>', {
    'class': 'follow_tournament',
  });
  var dismissTournamentLink = $('<a>', {
    'text': 'Dismiss tournament',
    'href': '#',
    'data-tournamentId': Api.tournament.tournamentId,
  });
  dismissTournamentLink.click(Tournament.formDismissTournament);

  dismissTournamentDiv.append('[')
          .append(dismissTournamentLink)
          .append(']');

  Tournament.page.append(dismissTournamentDiv);
};

Tournament.formDismissTournament = function(e) {
  e.preventDefault();
  var args = {'type': 'dismissTournament',
              'tournamentId': $(this).attr('data-tournamentId'),};
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully dismissed tournament', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Tournament.showLoggedInPage,
    Tournament.showLoggedInPage);
};

Tournament.pageAddUnfollowTournamentLink = function () {
  // must be not in a tournament, but still following it
  if (!(
       !Api.tournament.isParticipant &&
       Api.tournament.isWatched
     )) {
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
  // must be not following a tournament, and the tournament is not complete
  //
  // also, since we can't unfollow a tournament that we're in, we don't need
  // this link for any tournament that we're in
  if (!(
    !Api.tournament.isWatched &&
    (Api.tournament.tournamentState !=
       Tournament.TOURN_STATE_END_TOURNAMENT) &&
    (Api.tournament.tournamentState !=
       Tournament.TOURN_STATE_CANCELLED) &&
    !Api.tournament.isParticipant
   )) {
    return;
  }

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

  var nRoundsPar = $('<p>', {
    'text': 'Number of tournament rounds: ' + Api.tournament.maxRound,
  });
  infoDiv.append(nRoundsPar);

  var winPar = $('<p>', {
    'text': 'Each game is played until one player has won: ' +
            Api.tournament.maxWins + ' round' +
            ((Api.tournament.maxWins > 1) ? 's' : ''),
  });
  infoDiv.append(winPar);
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

  var winnerIdx;
  var isWinnerFound = Api.tournament.remainCountArray.some(
    function(item, idx) {
      winnerIdx = idx;
      return item > 0;
    }
  );

  if (!isWinnerFound) {
    return;
  }

  var winnerPar = $('<p>', {
    'class': 'winner_name',
    'text': 'Winner: ' + Api.tournament.playerDataArray[winnerIdx].playerName
  });
  winnerDiv.append(winnerPar);
};

Tournament.pageAddPlayerInfo = function () {
  var playerCell;
  var buttonCell;
  var playerRow;

  var playerDiv = $('<div>');
  var playerTable = $('<table>');

  var headerRow = $('<tr>').append('<th>Player</th>', '<th>Button</th>');
  playerTable.append(headerRow);

  if (Api.tournament.playerDataArray) {
    var nPlayersJoined = Api.tournament.playerDataArray.length;
    for (var playerIdx = 0; playerIdx < nPlayersJoined; playerIdx++) {
      var playerName = Api.tournament.playerDataArray[playerIdx].playerName;
      var buttonName = Api.tournament.playerDataArray[playerIdx].buttonName;

      var playerLink = $('<a>', {
        'href': 'profile.html?player=' + playerName,
        'text': playerName,
      });
      var buttonLink = $('<a>', {
        'href': 'buttons.html?button=' + buttonName,
        'text': buttonName,
      });

      playerCell = $('<td>').append(playerLink);
      buttonCell = $('<td>').append(buttonLink);
      playerRow = $('<tr>').append(playerCell, buttonCell);
      playerTable.append(playerRow);
    }
  } else {
    playerTable.append('<tr>', '<td>None</td>', '<td>None</td>');
  }

  playerDiv.append(playerTable);
  Tournament.page.append(playerDiv);
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

      var dontJoinLink = $('<a>', {
        'text': '[Don\'t Join Tournament]',
        'id': 'dontJoinLink',
        'href': '#',
      });
      dontJoinLink.click(Tournament.showLoggedInPage);
      dontJoinLink.hide();
      actionDiv.append(dontJoinLink);
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
      joinButton.prop('disabled', true);
      $('#buttonSelectDiv').append(joinButton);

      $('#player_button').on('change', function() {
        joinButton.prop('disabled', '' === this.value);
      });

      $('#dontJoinLink').show();
    } else if ($('#changeLink').length) {
      var changeButton = $('<button>', {
        'id': 'change_button',
        'text': 'Select Button',
      });
      changeButton.click(Tournament.formChangeButton);
      changeButton.prop('disabled', true);
      $('#buttonSelectDiv').append(changeButton);

      $('#player_button').on('change', function() {
        changeButton.prop('disabled', '' === this.value);
      });
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

  if ('cancel' === type) {
    var doTournamentCancel = Env.window.confirm(
      'Are you SURE you want to cancel this tournament?'
    );
    if (!doTournamentCancel) {
      return;
    }
  }

  var requiresButtonChoice = ('join' === type) || ('changeButton' === type);
  var hasButtonChoice =
    $.isArray(buttonNameArray) &&
    (buttonNameArray.length >= 1) &&
    buttonNameArray[0] !== '';

  if (requiresButtonChoice && !hasButtonChoice) {
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
  var gameData;
  var gameDataArray;
  var gameIdx;
  var gameRow;
  var roundIdx;
  var roundVal;
  var winnerCell;

  var gameDiv = $('<div>');
  var gameTable = $('<table>');

  gameTable.append(
    $('<tr>').append(
      '<th>Round</th>',
      '<th>Game</th>',
      '<th>Winner</th>',
      '<th>Score</th>',
      '<th>Player 1</th>',
      '<th>Button 1</th>',
      '<th>Player 2</th>',
      '<th>Button 2</th>'
    )
  );

  var nRound = Api.tournament.gameDataArrayArray.length;

  for (roundIdx = 0; roundIdx < nRound; roundIdx++) {
    roundVal = roundIdx + 1;
    gameDataArray = Api.tournament.gameDataArrayArray[roundIdx];

    for (gameIdx = 0; gameIdx < gameDataArray.length; gameIdx++) {
      gameData = gameDataArray[gameIdx];

      if (null === gameData.winner) {
        winnerCell = '';
      } else {
        winnerCell = $('<a>', {
          'href': 'profile.html?player=' + gameData.winner,
          'text': gameData.winner,
        });
      }

      gameRow = $('<tr>').append(
        $('<td>').text(roundVal),
        $('<td>').append($('<a>', {
          'href': 'game.html?game=' + gameData.gameId,
          'text': gameData.gameId,
        })),
        $('<td>').append(winnerCell),
        $('<td>').append(
          gameData.nWinsArray[0] + '/' +
          gameData.nWinsArray[1] + '/' +
          gameData.ndraws + ' (' +
          gameData.n_target_wins + ')'
        ),
        $('<td>').append(gameData.isOnVacationArray[0] ?
                         Env.buildVacationImage() : '')
                 .append($('<a>', {
          'href': 'profile.html?player=' + gameData.playerArray[0],
          'text': gameData.playerArray[0],
        })),
        $('<td>').append($('<a>', {
          'href': 'buttons.html?button=' + gameData.buttonArray[0],
          'text': gameData.buttonArray[0],
        })),
        $('<td>').append(gameData.isOnVacationArray[1] ?
                         Env.buildVacationImage() : '')
                 .append($('<a>', {
          'href': 'profile.html?player=' + gameData.playerArray[1],
          'text': gameData.playerArray[1],
        })),
        $('<td>').append($('<a>', {
          'href': 'buttons.html?button=' + gameData.buttonArray[1],
          'text': gameData.buttonArray[1],
        }))
      );

      gameTable.append(gameRow);
    }
  }

  gameDiv.append(gameTable);
  Tournament.page.append(gameDiv);
};
