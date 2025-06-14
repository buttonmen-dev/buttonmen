// namespace for this "module"
var TournamentOverview = {
  'activity': {},
};

TournamentOverview.bodyDivId = 'tournament_overview_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * TournamentOverview.showLoggedInPage() is the landing function. Always call
//   this first when logged in.
// * TournamentOverview.getTournamentOverview() asks the API for information
//   about the player's tournament overview status (currently, the lists of new,
//   active, completed, and cancelled tournaments, and potentially any other
//   tournaments that have been tagged to be watched).
//   It sets Api.new_tournaments, Api.active_tournaments,
//   Api.completed_tournaments, Api.cancelled_tournaments, and potentially
//   Api.tagged_tournaments. If successful, it calls
//   TournamentOverview.showPage().
// * TournamentOverview.showPage() assembles the page contents as a variable.
//
// N.B. There is no form submission on this page (aside from the [Dismiss]
// links); it's just a landing page with links to other pages. So it's
// logically somewhat simpler than e.g. Game.js.
////////////////////////////////////////////////////////////////////////

TournamentOverview.showLoggedInPage = function() {
  // Get all needed information, then display overview page
  TournamentOverview.getOverview(TournamentOverview.showPage);
};

TournamentOverview.getOverview = function(callback) {
  Env.callAsyncInParallel([
    Api.getTournamentsData
  ], callback);
};

TournamentOverview.showPage = function() {
  TournamentOverview.page = $('<div>');

  TournamentOverview.pageAddNewtournamentLink();
  TournamentOverview.pageAddTournamentTables();

  // Actually lay out the page
  Login.arrangePage(TournamentOverview.page);

  TournamentOverview.updateSectionHeaderCounts('closedtournaments');
  TournamentOverview.updateSectionHeaderCounts('opentournaments');
  TournamentOverview.updateSectionHeaderCounts('activetournaments');

  TournamentOverview.updateVisibilityOfTables();
};


//////////////////////////////////////////////////////////////////////////
//// Helper routines to add HTML entities to existing pages
//
TournamentOverview.pageAddNewtournamentLink = function() {
  var newtournamentDiv = $('<div>');
  var newtournamentPar = $('<p>');
  var newtournamentLink = $('<a>', {
    'href': Env.ui_root + 'create_tournament.html',
    'text': 'Create a new tournament',
  });

  newtournamentPar.append(newtournamentLink);
  newtournamentDiv.append(newtournamentPar);
  TournamentOverview.page.append(newtournamentDiv);
};

// Add tables for types of existing tournaments
TournamentOverview.pageAddTournamentTables = function() {
  TournamentOverview.pageAddTournamentTable('closed', 'Closed tournaments');
  TournamentOverview.pageAddTournamentTable('open', 'Open tournaments');
  TournamentOverview.pageAddTournamentTable('active', 'Active tournaments');
};

TournamentOverview.pageAddTournamentTable = function(
    tournamentType,
    sectionHeader,
    reverseSortOrder
  ) {
  var tournamentsource = [];
  var tournamentIdx;
  var tableClass;
  var showDismiss = false;
  var showFollow = false;
  var showNPlayersJoined = false;

  switch (tournamentType) {
  case 'open':
    // open tournaments that you haven't joined
    for (tournamentIdx = 0;
         tournamentIdx < Api.tournaments.tournaments.length;
         tournamentIdx++) {
      if (
        (Api.tournaments.tournaments[tournamentIdx].status === 'OPEN') &&
        !Api.tournaments.tournaments[tournamentIdx].hasJoined
      ) {
        tournamentsource.push(Api.tournaments.tournaments[tournamentIdx]);
      }
    }

    // open tournaments that you have joined
    for (tournamentIdx = 0;
         tournamentIdx < Api.tournaments.tournaments.length;
         tournamentIdx++) {
      if (
        (Api.tournaments.tournaments[tournamentIdx].status === 'OPEN') &&
        Api.tournaments.tournaments[tournamentIdx].hasJoined
      ) {
        tournamentsource.push(Api.tournaments.tournaments[tournamentIdx]);
      }
    }
    showNPlayersJoined = true;
    showFollow = true;
    tableClass = 'opentournaments';
    break;
  case 'active':
    for (tournamentIdx = 0;
         tournamentIdx < Api.tournaments.tournaments.length;
         tournamentIdx++) {
      if ((Api.tournaments.tournaments[tournamentIdx].status === 'ACTIVE') &&
          Api.tournaments.tournaments[tournamentIdx].isWatched) {
        tournamentsource.push(Api.tournaments.tournaments[tournamentIdx]);
      }
    }
    showFollow = true;
    tableClass = 'activetournaments';
    break;
  case 'closed':
    for (tournamentIdx = 0;
         tournamentIdx < Api.tournaments.tournaments.length;
         tournamentIdx++) {
      if (
        (
          (Api.tournaments.tournaments[tournamentIdx].status === 'COMPLETE') ||
          (Api.tournaments.tournaments[tournamentIdx].status === 'CANCELLED')
        ) &&
        Api.tournaments.tournaments[tournamentIdx].isWatched
      ) {
        tournamentsource.push(Api.tournaments.tournaments[tournamentIdx]);
      }
    }
    showDismiss = true;
    tableClass = 'closedtournaments';
    break;
  default:

  }

  if (tournamentsource.length === 0) {
    return;
  }

  if (reverseSortOrder === true) {
    tournamentsource.reverse();
  }

  TournamentOverview.addTableRows(
    TournamentOverview.addTableStructure(
      tableClass, sectionHeader, showDismiss, showFollow
    ),
    tournamentsource,
    showDismiss,
    showFollow,
    showNPlayersJoined
  );
};

TournamentOverview.addTableStructure = function(
  tableClass, sectionHeader, showDismiss, showFollow
) {
  var tableBody = TournamentOverview.page.find(
    'table.' + tableClass + ' tbody'
  );

  // create table
  var tableDiv = $('<div>', { 'class': tableClass + 'Holder listHolder', });
  var h2SectionHeader = $('<h2>', {
    'text': sectionHeader,
    'class': 'sectionHeader',
    'id': 'h2' + tableClass,
    'data-text': sectionHeader,
  }).click(function() {
    $('.' + tableClass + 'Div').toggle('blind', 300);
    $('#pre-caret-' + tableClass)
      .toggleClass('ui-icon-triangle-1-e ui-icon-triangle-1-s');
    if (!TournamentOverview.activity.visibility) {
      TournamentOverview.activity.visibility = {};
    }
    TournamentOverview.activity.visibility[tableClass] =
      $('#pre-caret-' + tableClass).hasClass('ui-icon-triangle-1-s');
    sessionStorage.setItem(
      'TournamentOverviewVisibility',
      JSON.stringify(TournamentOverview.activity.visibility)
    );
  });
  tableDiv.append(h2SectionHeader);
  // need an extra div so that the blind animation works correctly
  var internalTableDiv = $('<div>', { 'class': tableClass + 'Div', });
  var table = $('<table>', { 'class': 'tournamentList ' + tableClass, });
  internalTableDiv.append(table);
  tableDiv.append(internalTableDiv);
  TournamentOverview.page.append(tableDiv);

  // add table header
  var tableHead = $('<thead>');
  var headerRow = $('<tr>');
  headerRow.append($('<th>', {'text': 'Tournament', }));
  headerRow.append($('<th>', {'text': 'Description', }));
  headerRow.append($('<th>', {'text': 'Type', }));
  headerRow.append($('<th>', {'text': '# of players', }));
  headerRow.append($('<th>', {'text': 'Creator', }));
  if (showDismiss) {
    headerRow.append($('<th>', {'text': 'Dismiss', }));
  } else if (showFollow) {
    headerRow.append($('<th>', {'text': 'Follow', }));
  }
  tableHead.append(headerRow);
  table.append(tableHead);

  // add table body
  tableBody = $('<tbody>');
  table.append(tableBody);

  return tableBody;
};

TournamentOverview.addTableRows = function(
  tableBody, tournamentsource, showDismiss, showFollow, showNPlayersJoined
) {
  var tournamentInfo;
  var tournamentRow;

  for (var tournamentIdx = 0;
       tournamentIdx < tournamentsource.length;
       tournamentIdx++) {
    tournamentInfo = tournamentsource[tournamentIdx];

    tournamentRow = $('<tr>');
    TournamentOverview.addTournamentCol(tournamentRow, tournamentInfo);
    TournamentOverview.addDescCol(
      tournamentRow, tournamentInfo.tournamentDescription
    );
    TournamentOverview.addTypeCol(tournamentRow, tournamentInfo);
    TournamentOverview.addNPlayersCol(
      tournamentRow, tournamentInfo, showNPlayersJoined
    );
    TournamentOverview.addPlayerCol(tournamentRow, tournamentInfo.creatorName);
    if (showDismiss) {
      TournamentOverview.addDismissCol(tournamentRow, tournamentInfo);
    } else if (showFollow) {
      TournamentOverview.addFollowCol(tournamentRow, tournamentInfo);
    }

    tableBody.append(tournamentRow);
  }
};

TournamentOverview.addTournamentCol = function(tournamentRow, tournamentInfo) {
  var tournamentLinkTd = $('<td>');

  var verb = 'View';

  if ((tournamentInfo.status === 'OPEN') && !tournamentInfo.hasJoined) {
    verb = 'Join';
  }

  tournamentLinkTd.append($('<a>', {
    'href': 'tournament.html?tournament=' + tournamentInfo.tournamentId,
    'text': verb + ' ' + tournamentInfo.tournamentId,
  }));

  tournamentRow.append(tournamentLinkTd);
};

TournamentOverview.addTypeCol = function(tournamentRow, tournamentInfo) {
  var tournamentTypeTd = $('<td>', {'text': tournamentInfo.tournamentType});

  tournamentRow.append(tournamentTypeTd);
};

TournamentOverview.addDescCol = function(tournamentRow, description) {
  var descText = '';
  if (typeof(description) === 'string') {
    descText = description.substring(0, 30) +
               ((description.length > 30) ? '...' : '');
  }
  tournamentRow.append($('<td>', {
    'class': 'tournamentDescDisplay',
    'text': descText,
  }));
};

TournamentOverview.addPlayerCol = function(gameRow, playerName) {
  gameRow.append($('<td>').append(Env.buildProfileLink(playerName)));
};

TournamentOverview.addNPlayersCol = function(
  tournamentRow, tournamentInfo, showNPlayersJoined
) {
  var tournamentNPlayersTd;

  if (showNPlayersJoined) {
    tournamentNPlayersTd = $('<td>',
      {'text': tournamentInfo.nPlayersJoined + '/' + tournamentInfo.nPlayers}
    );
  } else {
    tournamentNPlayersTd = $('<td>', {'text': tournamentInfo.nPlayers});
  }

  tournamentRow.append(tournamentNPlayersTd);
};

TournamentOverview.addDismissCol = function(tournamentRow, tournamentInfo) {
  var dismissTd = $('<td>');
  dismissTd.css('white-space', 'nowrap');
  tournamentRow.append(dismissTd);

  var dismissLink = $('<a>', {
    'text': 'Dismiss',
    'href': '#',
    'data-tournamentId': tournamentInfo.tournamentId,
  });
  dismissLink.click(TournamentOverview.formDismissTournament);
  dismissTd.append('[')
           .append(dismissLink)
           .append(']');
};

TournamentOverview.formDismissTournament = function(e) {
  e.preventDefault();
  var args = {'type': 'dismissTournament',
              'tournamentId': $(this).attr('data-tournamentId'),};
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully dismissed tournament', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), TournamentOverview.showLoggedInPage,
    TournamentOverview.showLoggedInPage);
};

TournamentOverview.addFollowCol = function(tournamentRow, tournamentInfo) {
  var followTd = $('<td>');
  followTd.css('white-space', 'nowrap');
  tournamentRow.append(followTd);

  if (tournamentInfo.hasJoined) {
    followTd.append('Participant');
    return;
  }

  var followLink;

  if (tournamentInfo.isWatched) {
    followLink = $('<a>', {
      'text': 'Unfollow',
      'href': '#',
      'data-tournamentId': tournamentInfo.tournamentId,
    });
    followLink.click(TournamentOverview.formUnfollowTournament);
  } else {
    followLink = $('<a>', {
      'text': 'Follow',
      'href': '#',
      'data-tournamentId': tournamentInfo.tournamentId,
    });
    followLink.click(TournamentOverview.formFollowTournament);
  }

  followTd.append('[')
           .append(followLink)
           .append(']');
};

TournamentOverview.formFollowTournament = function(e) {
  e.preventDefault();
  var args = {'type': 'followTournament',
              'tournamentId': $(this).attr('data-tournamentId'),};
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully followed tournament', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), TournamentOverview.showLoggedInPage,
    TournamentOverview.showLoggedInPage);
};

TournamentOverview.formUnfollowTournament = function(e) {
  e.preventDefault();
  var args = {'type': 'unfollowTournament',
              'tournamentId': $(this).attr('data-tournamentId'),};
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully unfollowed tournament', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), TournamentOverview.showLoggedInPage,
    TournamentOverview.showLoggedInPage);
};

TournamentOverview.updateSectionHeaderCounts = function(tableClass) {
  var header = TournamentOverview.page.find(
    'div.' + tableClass + 'Holder h2.sectionHeader'
  );
  var tableRows = TournamentOverview.page.find(
    'div.' + tableClass +
    'Holder table.tournamentList tbody tr:visible:not(.spacer)'
  );
  var count = tableRows.size();

  header.text(header.attr('data-text') + ' (' + count + ')');

  header.prepend($('<span>', {'id': 'pre-caret-' + tableClass})
        .addClass('ui-icon ui-icon-triangle-1-s pre-caret'));
};

TournamentOverview.updateVisibilityOfTables = function() {
  TournamentOverview.activity.visibility =
    JSON.parse(sessionStorage.getItem('TournamentOverviewVisibility'));

  if (TournamentOverview.activity.visibility) {
    Object.keys(TournamentOverview.activity.visibility).forEach(function(key) {
      if (!TournamentOverview.activity.visibility[key]) {
        $('.' + key + 'Div').hide();
        $('#pre-caret-' + key).toggleClass(
          'ui-icon-triangle-1-e ui-icon-triangle-1-s'
        );
      }
    });
  }
};
