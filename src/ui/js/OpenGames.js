// namespace for this "module"
var OpenGames = {};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * OpenGames.showOpenGamesPage() is the landing function.  Always call
//   this first. It sets up #opengames_page and calls OpenGames.getOpenGames()
// * OpenGames.getOpenGames() calls the API, setting Api.button and
//   Api.open_games. It calls OpenGames.showPage()
// * OpenGames.showPage() uses the data returned by the API to build
//   the contents of the page as OpenGames.page and calls
//   OpenGames.arrangePage()
//
//* OpenGames.joinOpenGame() is called whenever the user clicks on one of the
//  Join Game buttons. It calls the API to join the game, setting
//  Api.join_game_result if successful
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
    Env.callAsyncInParallel([
      Api.getOpenGamesData,
      { 'func': Api.getButtonData, 'args': [ null ] },
    ], callback);
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
    var buttons = {
      '__random': {
          'recipe': 'Random button',
          'greyed': false,
        },
      };
    var anyUnimplementedButtons = false;

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

    var joinableGames = OpenGames.buildGameTable('joinable', buttons);
    if (joinableGames) {
      OpenGames.page.append($('<h3>', {'text': 'Games you can join', }));
      OpenGames.page.append(joinableGames);
    }

    var yourGames = OpenGames.buildGameTable('yours', buttons);
    if (yourGames) {
      OpenGames.page.append($('<h3>',
        {'text': 'Your open games (waiting for other players to join)', }));
      OpenGames.page.append(yourGames);
    }

    if (anyUnimplementedButtons) {
      var warning = $('<p>', {
        'text': 'Note to testers: buttons whose names are prefixed with ' +
                '"--" contain unimplemented skills.  Selecting these buttons ' +
                'is not recommended.',
        'style': 'font-style: italic;',
      });
      OpenGames.page.append(warning);
    }
  }

  // Actually layout the page
  OpenGames.arrangePage();
};

OpenGames.arrangePage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#opengames_page').empty();
  $('#opengames_page').append(OpenGames.page);
};

OpenGames.joinOpenGame = function() {
  // Clear any previous error message
  Env.message = null;
  Env.showStatusMessage();

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

OpenGames.displayJoinResult = function(
    joinButton, buttonSelect, gameId, buttonName) {
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

OpenGames.buildGameTable = function(tableType, buttons) {
  var table = $('<table>', { 'class': 'gameList', });

  var thead = $('<thead>');
  table.append(thead);
  var headerRow = $('<tr>');
  thead.append(headerRow);
  headerRow.append($('<th>', { 'text': 'Game', }));
  if (tableType == 'joinable') {
    headerRow.append($('<th>', { 'text': 'Your Button', }));
    headerRow.append($('<th>', { 'text': 'Challenger\'s Button', }));
    headerRow.append($('<th>', { 'text': 'Challenger', }));
  } else {
    headerRow.append($('<th>', { 'text': 'Your Button', }));
    headerRow.append($('<th>', { 'text': 'Opponent\'s Button', }));
  }
  headerRow.append($('<th>', { 'text': 'Rounds', }));

  var tbody = $('<tbody>');
  table.append(tbody);
  var anyRows = false;
  $.each(Api.open_games.games, function(index, game) {

    // The table of joinable games should contain only games in
    // which the active player is not the challenger.  The table
    // of your open games should contain only games in which the
    // active player is the challenger.
    if (game.challengerName == Login.player) {
      if (tableType == 'joinable') {
        return true;
      }
    } else {
      if (tableType == 'yours') {
        return true;
      }
    }
    anyRows = true;

    var gameRow = $('<tr>');
    tbody.append(gameRow);

    if (tableType == 'yours') {
      // Lay out rows for your open games table
      gameRow.append($('<td>', {
        'class': 'gameAction',
        'text': 'Game ' + game.gameId,
      }));
      if (game.challengerButton == '__random') {
        gameRow.append($('<td>', {
          'text': 'Random Button',
          'style': 'font-style: italic;',
        }));
      } else {
        gameRow.append($('<td>', {
          'text': game.challengerButton,
        }));
      }

      if (game.victimButton == '__random') {
        gameRow.append($('<td>', {
          'text': 'Random Button',
          'class': 'victimButton',
          'style': 'font-style: italic;',
        }));
      } else if (game.victimButton === null) {
        gameRow.append($('<td>', {
          'text': 'Any Button',
          'class': 'victimButton',
          'style': 'font-style: italic;',
        }));
      } else {
        gameRow.append($('<td>', {
          'text': game.victimButton,
          'class': 'victimButton',
        }));
      }
    } else {
      // Lay out rows for joinable games table
      var gameActionTd = $('<td>', { 'class': 'gameAction', });
      gameRow.append(gameActionTd);
      var joinButton = $('<button>', {
        'type': 'button',
        'text': 'Join Game ' + game.gameId,
        'data-gameId': game.gameId,
      });
      gameActionTd.append(joinButton);
      joinButton.click(OpenGames.joinOpenGame);

      if (game.victimButton == '__random') {
        gameRow.append($('<td>', {
          'text': 'Random Button',
          'class': 'victimButton',
          'style': 'font-style: italic;',
        }));
      } else if (game.victimButton === null) {
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
      } else {
        gameRow.append($('<td>', {
          'text': game.victimButton,
          'class': 'victimButton',
        }));
      }

      if (game.challengerButton == '__random') {
        gameRow.append($('<td>', {
          'text': 'Random Button',
          'style': 'font-style: italic;',
        }));
      } else {
        gameRow.append($('<td>', {
          'text': game.challengerButton,
        }));
      }
      gameRow.append($('<td>', {
        'style': 'background-color: ' + game.challengerColor + ';',
      }).append(Env.buildProfileLink(game.challengerName)));
    }

    gameRow.append($('<td>', {
      'text': game.targetWins,
    }));

    if (game.description) {
      var descRow = $('<tr>');
      tbody.append(descRow);
      var descTd = $('<td>', {
        'class': 'gameDescDisplay',
        'colspan': (tableType == 'yours' ? 4 : 5),
        'text': game.description,
      });
      descRow.append(descTd);
    }
  });

  if (anyRows) {
    return table;
  } else {
    return null;
  }
};
