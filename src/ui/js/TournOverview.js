// namespace for this "module"
var TournOverview = {
  'activity': {},
};

TournOverview.bodyDivId = 'tourn_overview_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * TournOverview.showLoggedInPage() is the landing function. Always call this
//   first when logged in.
// * TournOverview.getTournOverview() asks the API for information about the
//   player's tournament overview status (currently, the lists of new, active,
//   completed, and cancelled tournaments, and potentially any other
//   tournaments that have been tagged to be watched).
//   It sets Api.new_tourns, Api.active_tourns, Api.completed_tourns,
//   Api.cancelled_tourns, and potentially Api.tagged_tourns. If successful, it
//   calls TournOverview.showPage().
// * TournOverview.showPage() assembles the page contents as a variable.
//
// N.B. There is no form submission on this page (aside from the [Dismiss]
// links); it's just a landing page with links to other pages. So it's
// logically somewhat simpler than e.g. Game.js.
////////////////////////////////////////////////////////////////////////

TournOverview.showLoggedInPage = function() {
  // Get all needed information, then display overview page
  TournOverview.getOverview(TournOverview.showPage);
};

TournOverview.getOverview = function(callback) {
  Env.callAsyncInParallel([
    Api.getTournsData
  ], callback);
};

TournOverview.showPage = function() {
  TournOverview.page = $('<div>');

  TournOverview.pageAddNewtournLink();
  TournOverview.pageAddTournTables();

  // Actually lay out the page
  Login.arrangePage(TournOverview.page);

  TournOverview.updateSectionHeaderCounts('newtourns');
  TournOverview.updateSectionHeaderCounts('createdtourns');
  TournOverview.updateSectionHeaderCounts('joinedtourns');
  TournOverview.updateSectionHeaderCounts('activetourns');
  TournOverview.updateSectionHeaderCounts('completedtourns');
  TournOverview.updateSectionHeaderCounts('cancelledtourns');

  TournOverview.updateVisibilityOfTables();
};


//////////////////////////////////////////////////////////////////////////
//// Helper routines to add HTML entities to existing pages
//
TournOverview.pageAddNewtournLink = function() {
  var newtournDiv = $('<div>');
  var newtournPar = $('<p>');
  var newtournLink = $('<a>', {
    'href': Env.ui_root + 'create_tournament.html',
    'text': 'Create a new tournament',
  });

  newtournPar.append(newtournLink);
  newtournDiv.append(newtournPar);
  TournOverview.page.append(newtournDiv);
};

// Add tables for types of existing tournaments
TournOverview.pageAddTournTables = function() {
  TournOverview.pageAddTournTable('new', 'New tournaments');
  TournOverview.pageAddTournTable('created', 'Created tournaments');
  TournOverview.pageAddTournTable('joined', 'Joined tournaments');
  TournOverview.pageAddTournTable('active', 'Active tournaments');
  TournOverview.pageAddTournTable('completed', 'Completed tournaments');
  TournOverview.pageAddTournTable('cancelled', 'Cancelled tournaments');
};

TournOverview.pageAddTournTable = function(
    tournType,
    sectionHeader,
    reverseSortOrder
  ) {
  var tournsource = [];
  var tournIdx;
  var tableClass;
  var showDismiss = false;
  var showNPlayersJoined = false;

  switch (tournType) {
  case 'new':
    for (tournIdx = 0; tournIdx < Api.tourns.tourns.length; tournIdx++) {
      if ((Api.tourns.tourns[tournIdx].status == 'OPEN') &&
          !Api.tourns.tourns[tournIdx].hasJoined) {
        tournsource.push(Api.tourns.tourns[tournIdx]);
      }
    }
    showNPlayersJoined = true;
    tableClass = 'newtourns';
    break;
  case 'created':
    for (tournIdx = 0; tournIdx < Api.tourns.tourns.length; tournIdx++) {
      if (Api.tourns.tourns[tournIdx].isCreator &&
          Api.tourns.tourns[tournIdx].isWatched) {
        tournsource.push(Api.tourns.tourns[tournIdx]);
      }
    }
    showNPlayersJoined = true;
    tableClass = 'createdtourns';
    break;
  case 'joined':
    for (tournIdx = 0; tournIdx < Api.tourns.tourns.length; tournIdx++) {
      if ((Api.tourns.tourns[tournIdx].status == 'OPEN') &&
          Api.tourns.tourns[tournIdx].hasJoined) {
        tournsource.push(Api.tourns.tourns[tournIdx]);
      }
    }
    showNPlayersJoined = true;
    tableClass = 'joinedtourns';
    break;
  case 'active':
    for (tournIdx = 0; tournIdx < Api.tourns.tourns.length; tournIdx++) {
      if ((Api.tourns.tourns[tournIdx].status == 'ACTIVE') &&
          Api.tourns.tourns[tournIdx].isWatched) {
        tournsource.push(Api.tourns.tourns[tournIdx]);
      }
    }
    tableClass = 'activetourns';
    break;
  case 'completed':
    for (tournIdx = 0; tournIdx < Api.tourns.tourns.length; tournIdx++) {
      if ((Api.tourns.tourns[tournIdx].status == 'COMPLETE') &&
          Api.tourns.tourns[tournIdx].isWatched) {
        tournsource.push(Api.tourns.tourns[tournIdx]);
      }
    }
    tableClass = 'completedtourns';
    showDismiss = true;
    break;
  case 'cancelled':
    for (tournIdx = 0; tournIdx < Api.tourns.tourns.length; tournIdx++) {
      if ((Api.tourns.tourns[tournIdx].status == 'CANCELLED') &&
          Api.tourns.tourns[tournIdx].isWatched) {
        tournsource.push(Api.tourns.tourns[tournIdx]);
      }
    }
    tableClass = 'cancelledtourns';
    showDismiss = true;
    break;
  default:

  }

  if (tournsource.length === 0) {
    return;
  }

  if (reverseSortOrder === true) {
    tournsource.reverse();
  }

  TournOverview.addTableRows(
    TournOverview.addTableStructure(tableClass, sectionHeader, showDismiss),
    tournsource,
    showDismiss,
    showNPlayersJoined
  );
};

TournOverview.addTableStructure = function(
  tableClass, sectionHeader, showDismiss
) {
  var tableBody = TournOverview.page.find('table.' + tableClass + ' tbody');

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
    if (!TournOverview.activity.visibility) {
      TournOverview.activity.visibility = {};
    }
    TournOverview.activity.visibility[tableClass] =
      $('#pre-caret-' + tableClass).hasClass('ui-icon-triangle-1-s');
    sessionStorage.setItem('TournOverviewVisibility',
                           JSON.stringify(TournOverview.activity.visibility));
  });
  tableDiv.append(h2SectionHeader);
  // need an extra div so that the blind animation works correctly
  var internalTableDiv = $('<div>', { 'class': tableClass + 'Div', });
  var table = $('<table>', { 'class': 'tournList ' + tableClass, });
  internalTableDiv.append(table);
  tableDiv.append(internalTableDiv);
  TournOverview.page.append(tableDiv);

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
  }
  tableHead.append(headerRow);
  table.append(tableHead);

  // add table body
  tableBody = $('<tbody>');
  table.append(tableBody);

  return tableBody;
};

TournOverview.addTableRows = function(
  tableBody, tournsource, showDismiss, showNPlayersJoined
) {
  var tournInfo;
  var tournRow;

  for (var tournIdx = 0; tournIdx < tournsource.length; tournIdx++) {
    tournInfo = tournsource[tournIdx];

    tournRow = $('<tr>');
    TournOverview.addTournCol(tournRow, tournInfo);
    TournOverview.addDescCol(tournRow, tournInfo.tournDescription);
    TournOverview.addTypeCol(tournRow, tournInfo);
    TournOverview.addNPlayersCol(tournRow, tournInfo, showNPlayersJoined);
    TournOverview.addPlayerCol(tournRow, tournInfo.creatorName);
    if (showDismiss) {
      TournOverview.addDismissCol(tournRow, tournInfo);
    }

    tableBody.append(tournRow);
  }
};

TournOverview.addTournCol = function(tournRow, tournInfo) {
  var tournLinkTd = $('<td>');

  tournLinkTd.append($('<a>', {
    'href': 'tournament.html?tournament=' + tournInfo.tournId,
    'text': tournInfo.tournId,
  }));

  tournRow.append(tournLinkTd);
};

TournOverview.addTypeCol = function(tournRow, tournInfo) {
  var tournTypeTd = $('<td>', {'text': tournInfo.tournType});

  tournRow.append(tournTypeTd);
};

TournOverview.addDescCol = function(tournRow, description) {
  var descText = '';
  if (typeof(description) == 'string') {
    descText = description.substring(0, 30) +
               ((description.length > 30) ? '...' : '');
  }
  tournRow.append($('<td>', {
    'class': 'tournDescDisplay',
    'text': descText,
  }));
};

TournOverview.addPlayerCol = function(gameRow, playerName) {
  gameRow.append($('<td>').append(Env.buildProfileLink(playerName)));
};

TournOverview.addNPlayersCol = function(
  tournRow, tournInfo, showNPlayersJoined
) {
  var tournNPlayersTd;

  if (showNPlayersJoined) {
    tournNPlayersTd = $('<td>', {'text': tournInfo.nPlayersJoined + '/' +
                                         tournInfo.nPlayers});
  } else {
    tournNPlayersTd = $('<td>', {'text': tournInfo.nPlayers});
  }

  tournRow.append(tournNPlayersTd);
};

TournOverview.addDismissCol = function(tournRow, tournInfo) {
  var dismissTd = $('<td>');
  dismissTd.css('white-space', 'nowrap');
  tournRow.append(dismissTd);

  var dismissLink = $('<a>', {
    'text': 'Dismiss',
    'href': '#',
    'data-tournId': tournInfo.tournId,
  });
  dismissLink.click(TournOverview.formDismissTourn);
  dismissTd.append('[')
           .append(dismissLink)
           .append(']');
};

TournOverview.formDismissTourn = function(e) {
  e.preventDefault();
  var args = {'type': 'dismissTourn',
              'tournId': $(this).attr('data-tournId'),};
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully dismissed tournament', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), TournOverview.showLoggedInPage,
    TournOverview.showLoggedInPage);
};

TournOverview.updateSectionHeaderCounts = function(tableClass) {
  var header = TournOverview.page.find(
    'div.' + tableClass + 'Holder h2.sectionHeader'
  );
  var tableRows = TournOverview.page.find(
    'div.' + tableClass +
    'Holder table.tournList tbody tr:visible:not(.spacer)'
  );
  var count = tableRows.size();

  header.text(header.attr('data-text') + ' (' + count + ')');

  header.prepend($('<span>', {'id': 'pre-caret-' + tableClass})
        .addClass('ui-icon ui-icon-triangle-1-s pre-caret'));
};

TournOverview.updateVisibilityOfTables = function() {
  TournOverview.activity.visibility =
    JSON.parse(sessionStorage.getItem('TournOverviewVisibility'));

  if (TournOverview.activity.visibility) {
    Object.keys(TournOverview.activity.visibility).forEach(function(key) {
      if (!TournOverview.activity.visibility[key]) {
        $('.' + key + 'Div').hide();
        $('#pre-caret-' + key).toggleClass(
          'ui-icon-triangle-1-e ui-icon-triangle-1-s'
        );
      }
    });
  }
};
