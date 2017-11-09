// namespace for this "module"
var OpenGames = {};

OpenGames.bodyDivId = 'opengames_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * OpenGames.showLoggedInPage() is the landing function.  Always call
//   this first. It sets up #opengames_page and calls OpenGames.getOpenGames()
// * OpenGames.getOpenGames() calls the API, setting Api.button and
//   Api.open_games. It calls OpenGames.showPage()
// * OpenGames.showPage() uses the data returned by the API to build
//   the contents of the page as OpenGames.page and calls
//   Login.arrangePage()
//
//* OpenGames.joinOpenGame() is called whenever the user clicks on one of the
//  Join Game buttons. It calls the API to join the game, setting
//  Api.join_game_result if successful
////////////////////////////////////////////////////////////////////////

OpenGames.showLoggedInPage = function() {
  // Get all needed information, then display Open Games page
  OpenGames.getOpenGames(OpenGames.showPage);
};

OpenGames.getOpenGames = function(callback) {
  Env.callAsyncInParallel([
    Api.getOpenGamesData,
    { 'func': Api.getButtonData, 'args': [ null ] },
  ], callback);
};

OpenGames.showPage = function() {
  OpenGames.page = $('<div>');

  if (Api.open_games.load_status != 'ok') {
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
  Login.arrangePage(OpenGames.page);
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
    var buttonNameSpan;
    if (buttonName == '__random') {
      buttonNameSpan = $('<span>', {
        'text': 'Random Button',
        'style': 'font-style: italic;',
      });
    } else {
      buttonNameSpan = $('<span>', { 'text': buttonName, });
    }
    buttonSelect.after(buttonNameSpan);
  }
};

OpenGames.cancelOpenGame = function() {
  // Clear any previous error message
  Env.message = null;
  Env.showStatusMessage();

  var cancelButton = $(this);
  var gameId = cancelButton.attr('data-gameId');

  Api.cancelOpenGame(gameId,
    function() {
      OpenGames.displayCancelResult(cancelButton, gameId);
    },
    function() {
      OpenGames.displayCancelResult();
    });
};

OpenGames.displayCancelResult = function(cancelButton, gameId) {
  // If an error occurred, display it
  if (cancelButton === undefined) {
    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'An internal error occurred while trying to cancel the game.',
      };
    }
    OpenGames.getOpenGames(OpenGames.showPage);
    return;
  }

  cancelButton.hide();
  cancelButton.after($('<span>', {
    'text': 'Cancelled Game ' + gameId,
  }));
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
  if (tableType != 'joinable') {
    headerRow.append($('<th>', { 'text': 'Action', }));
  }

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
        gameRow.append($('<td>').append(
          Env.buildButtonLink(
            game.challengerButton,
            buttons[game.challengerButton].recipe
          )
        ));
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
        gameRow.append($('<td>', { 'class': 'victimButton' }).append(
          Env.buildButtonLink(
            game.victimButton,
            buttons[game.victimButton].recipe
          )
        ));
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
        gameRow.append($('<td>', { 'class': 'victimButton' }).append(
          Env.buildButtonLink(
            game.victimButton,
            buttons[game.victimButton].recipe
          )
        ));
      }

      if (game.challengerButton == '__random') {
        gameRow.append($('<td>', {
          'text': 'Random Button',
          'style': 'font-style: italic;',
        }));
      } else {
        gameRow.append($('<td>').append(
          Env.buildButtonLink(
            game.challengerButton,
            buttons[game.challengerButton].recipe
          )
        ));
      }
      gameRow.append($('<td>', {
        'style': 'background-color: ' + game.challengerColor + ';',
      }).append((game.isChallengerOnVacation) ? Env.buildVacationImage() : '')
        .append(Env.buildProfileLink(game.challengerName)));
    }

    gameRow.append($('<td>', {
      'text': game.targetWins,
    }));

    if (tableType == 'yours') {
      var gameCancelTd = $('<td>', { 'class': 'gameAction', });
      gameRow.append(gameCancelTd);
      var cancelButton = $('<button>', {
        'type': 'button',
        'text': 'Cancel Game',
        'data-gameId': game.gameId,
      });
      gameCancelTd.append(cancelButton);
      cancelButton.click(OpenGames.cancelOpenGame);
    }

    if (game.description) {
      var descRow = $('<tr>');
      tbody.append(descRow);
      var descTd = $('<td>', {
        'class': 'gameDescDisplay',
        'colspan': 5,
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
