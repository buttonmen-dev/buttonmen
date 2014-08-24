// namespace for this "module"
var ActivePlayers = {};

ActivePlayers.bodyDivId = 'activeplayers_page';

ActivePlayers.NUMBER_OF_ACTIVE_PLAYERS = 50;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * ActivePlayers.showLoggedInPage() is the landing function. Always call
//   this first. It calls the API, setting Api.active_players, and then
//   calls ActivePlayers.showPage()
// * ActivePlayers.showPage() uses the data returned by the API to build
//   the contents of the page as ActivePlayers.page and calls
//   Login.arrangePage()
////////////////////////////////////////////////////////////////////////

ActivePlayers.showLoggedInPage = function() {
  // Get all needed information, then display Active Players page
  Api.getActivePlayers(ActivePlayers.NUMBER_OF_ACTIVE_PLAYERS,
    ActivePlayers.showPage);
};

ActivePlayers.showPage = function() {
  ActivePlayers.page = $('<div>');

  if (Api.active_players.load_status != 'ok') {
    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'An internal error occurred while loading the list of players.',
      };
    }
  } else if ((Api.active_players.players.length === 0)) {
    Env.message = {
      'type': 'none',
      'text': 'There are no players. You are the last human on Earth.',
    };
  } else {
    ActivePlayers.page.append($('<h2>', {'text': 'Who\'s Online', }));
    ActivePlayers.page.append(ActivePlayers.buildPlayersTable());
  }

  // Actually lay out the page
  Login.arrangePage(ActivePlayers.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

ActivePlayers.buildPlayersTable = function() {
  var table = $('<table>', { 'class': 'activePlayerList', });

  var thead = $('<thead>');
  table.append(thead);
  var headerRow = $('<tr>');
  thead.append(headerRow);
  headerRow.append($('<th>', {
    'class': 'player',
    'text': 'Player',
  }));
  headerRow.append($('<th>', {
    'class': 'idleness',
    'text': 'Idle',
  }));

  var tbody = $('<tbody>');
  table.append(tbody);
  $.each(Api.active_players.players, function(index, playerData) {
    var playerRow = $('<tr>');
    tbody.append(playerRow);
    var profileLink = Env.buildProfileLink(playerData.playerName);
    playerRow.append($('<td>', {
      'class': 'player',
    }).append(profileLink));
    playerRow.append($('<td>', {
      'class': 'idleness',
      'text': playerData.idleness
    }));
  });

  return table;
};
