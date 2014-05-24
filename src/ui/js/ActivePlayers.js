// namespace for this "module"
var ActivePlayers = {};

Api.NUMBEER_OF_ACTIVE_PLAYERS = 50;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * ActivePlayers.showActivePlayersPage() is the landing function. Always call
//   this first. It sets up #activeplayers_page and calls
//   ActivePlayers.getActivePlayers()
// * ActivePlayers.getActivePlayers() calls the API, setting Api.active_players.
//   It calls ActivePlayers.showPage()
// * ActivePlayers.showPage() uses the data returned by the API to build
//   the contents of the page as ActivePlayers.page and calls
//   ActivePlayers.layoutPage()
// * ActivePlayers.layoutPage() sets the contents of
//   <div id="activeplayers_page"> on the live page
////////////////////////////////////////////////////////////////////////

ActivePlayers.showActivePlayersPage = function() {

  // Setup necessary elements for displaying status messages
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#activeplayers_page').length === 0) {
    $('body').append($('<div>', {'id': 'activeplayers_page', }));
  }

  // Get all needed information, then display Active Players page
  ActivePlayers.getActivePlayers(ActivePlayers.showPage);
};

ActivePlayers.getActivePlayers = function(callback) {
  if (Login.logged_in) {
    Api.getActivePlayers(Api.NUMBEER_OF_ACTIVE_PLAYERS, callback);
  } else {
    return callback();
  }
};

ActivePlayers.showPage = function() {
  ActivePlayers.page = $('<div>');

  if (!Login.logged_in) {
    Env.message = {
      'type': 'error',
      'text': 'Can\'t view players because you\'re not logged in',
    };
  } else if (Api.active_players.load_status != 'ok') {
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
    ActivePlayers.page.append(ActivePlayers.buildPlayerTable());
  }

  // Actually layout the page
  ActivePlayers.layoutPage();
};

ActivePlayers.layoutPage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#activeplayers_page').empty();
  $('#activeplayers_page').append(ActivePlayers.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

ActivePlayers.buildPlayerTable = function() {
  var table = $('<table>', { 'class': 'playerList', });

  var thead = $('<thead>');
  table.append(thead);
  var headerRow = $('<tr>');
  thead.append(headerRow);
  headerRow.append($('<th>', { 'text': 'Player', }));
  headerRow.append($('<th>', { 'text': 'Idle', }));

  var tbody = $('<tbody>');
  table.append(tbody);
  $.each(Api.active_players.players, function(index, playerData) {
    var playerRow = $('<tr>');
    tbody.append(playerRow);
    var profileLink = $('<a>', {
      'href':
        'profile.html?player=' + encodeURIComponent(playerData.playerName),
      'text': playerData.playerName,
    });
    playerRow.append($('<td>').append(profileLink));
    playerRow.append($('<td>', { 'text': playerData.idleness }));
  });

  return table;
};