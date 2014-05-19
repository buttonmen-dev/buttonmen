// namespace for this "module"
var OpenGames = {};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * OpenGames.showOpenGamesPage() is the landing function.  Always call
//   this first
////////////////////////////////////////////////////////////////////////

OpenGames.showOpenGamesPage = function() {

  // Setup necessary elements for displaying status messages
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#opengames_page').length === 0) {
    $('body').append($('<div>', {'id': 'opengames_page', }));
  }

  // Get all needed information, then display Open Games page
  OpenGames.getOpenGames(OpenGames.showPage);
};

OpenGames.getOpenGames = function(callback) {
  if (Login.logged_in) {
    Api.getOpenGamesData(function() {
      Api.getButtonData(callback);
    });
  } else {
    return callback();
  }
};

OpenGames.showPage = function() {
  OpenGames.page = $('<div>');

  if (!Login.logged_in) {
    Env.message = {
      'type': 'error',
      'text': 'Can\'t join games because you are not logged in',
    };
  } else if (Api.open_games.load_status != 'ok') {
    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'An internal error occurred while loading the list of games.',
      };
    }
  } else if ((Api.open_games.games.length === 0)) {
    Env.message = {
      'type': 'none',
      'text': 'There are no open games.',
    };
  } else {
    var buttons = { };
    var anyUnimplementedButtons = true;

    $.each(Api.button.list, function(button, buttoninfo) {
      if (buttoninfo.hasUnimplementedSkill) {
        buttons[button] = {
          'recipe': '-- ' + button + ': ' + buttoninfo.recipe,
          'greyed': true,
        };
        anyUnimplementedButtons = true;
      } else {
        buttons[button] = {
          'recipe': button + ': ' + buttoninfo.recipe,
          'greyed': false,
        };
      }
    });

    OpenGames.page.append($('<h2>', {'text': 'Open Games', }));
    OpenGames.page.append(OpenGames.buildGameTable(buttons));

    if (anyUnimplementedButtons) {
      var warning = $('<p>', {
        'text': 'Note to testers: buttons whose names are prefixed with "--" ' +
                'contain unimplemented skills.  Selecting these buttons is not ' +
                'recommended.',
        'style': 'font-style: italic;',
      });
      OpenGames.page.append(warning);
    }
  }

  // Actually layout the page
  OpenGames.layoutPage();
};

OpenGames.layoutPage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#opengames_page').empty();
  $('#opengames_page').append(OpenGames.page);
};

OpenGames.joinOpenGame = function() {
  var joinButton = $(this);
  var gameId = joinButton.attr('data-gameId');
  var buttonSelect = joinButton.closest('tr').find('td.victimButton select');
  var buttonName = buttonSelect.val();

  if (buttonSelect.length > 0 && !buttonName) {
    Env.message = {
      'type': 'error',
      'text': 'You must select a button in order to join game ' + gameId + '.',
    };
    Env.showStatusMessage();
    return;
  }

  Api.joinOpenGame(gameId, buttonName,
    function() {
      OpenGames.displayJoinResult(joinButton, buttonSelect, gameId, buttonName);
    },
    function() {
      OpenGames.displayJoinResult();
    });
};

OpenGames.displayJoinResult =
  function(joinButton, buttonSelect, gameId, buttonName) {
    // If an error occurred, display it
    if (joinButton === undefined) {
      if (Env.message === undefined || Env.message === null) {
        Env.message = {
          'type': 'error',
          'text': 'An internal error occurred while trying to join the game.',
        };
      }
      OpenGames.getOpenGames(OpenGames.showPage);
      return;
    }

    joinButton.hide();
    joinButton.after($('<a>', {
      'text': 'Go to Game ' + gameId,
      'href': 'game.html?game=' + gameId,
    }));

    if (buttonSelect !== undefined) {
      buttonSelect.hide();
      buttonSelect.after($('<span>', { 'text': buttonName, }));
    }
  };

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

OpenGames.buildGameTable = function(buttons) {
  var table = $('<table>', { 'class': 'gameList', });

  var thead = $('<thead>');
  table.append(thead);
  var headerRow = $('<tr>');
  thead.append(headerRow);
  headerRow.append($('<th>', { 'text': 'Game', }));
  headerRow.append($('<th>', { 'text': 'Your Button', }));
  headerRow.append($('<th>', { 'text': 'Challenger\'s Button', }));
  headerRow.append($('<th>', { 'text': 'Challenger', }));

  var tbody = $('<tbody>');
  table.append(tbody);
  $.each(Api.open_games.games, function(index, game) {
    var gameRow = $('<tr>');
    tbody.append(gameRow);
    if (game.challengerName == Login.player) {
      gameRow.append($('<td>', {
        'text': 'Open Game',
        'class': 'gameAction',
        'style': 'font-style: italic;',
      }));
    } else {
      var gameActionTd = $('<td>', { 'class': 'gameAction', });
      gameRow.append(gameActionTd);
      var joinButton = $('<button>', {
        'type': 'button',
        'text': 'Join Game ' + game.gameId,
        'data-gameId': game.gameId,
      });
      gameActionTd.append(joinButton);
      joinButton.click(OpenGames.joinOpenGame);
    }
    if (game.victimButton !== null) {
      gameRow.append($('<td>', {
        'text': game.victimButton,
        'class': 'victimButton',
      }));
    } else if (game.challengerName == Login.player) {
      gameRow.append($('<td>', {
        'text': 'Any Button',
        'class': 'victimButton',
        'style': 'font-style: italic;',
      }));
    } else {
      var victimButtonTd = $('<td>', { 'class': 'victimButton', });
      gameRow.append(victimButtonTd);
      var buttonSelect = $('<select>');
      victimButtonTd.append(buttonSelect);

      buttonSelect.append($('<option>', {
        'text': 'Choose button',
        'value': '',
      }));

      $.each(buttons, function(buttonName, buttonInfo) {
        buttonSelect.append($('<option>', {
          'text': buttonInfo.recipe,
          'value': buttonName,
          'class': (buttonInfo.greyed ? 'greyed' : ''),
        }));
      });
    }
    gameRow.append($('<td>', {
      'text': game.challengerButton,
    }));
    gameRow.append($('<td>', {
      'text': game.challengerName,
      'style': 'background-color: ' + game.challengerColor + ';',
    }));
  });

  return table;
};
