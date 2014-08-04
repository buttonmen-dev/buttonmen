// namespace for this "module"
var Buttons = {};

////////////////////////////////////////////////////////////////////////
//
// //TODO WRITE THIS
//
// Action flow through this page:
// * Buttons.showButtonsPage() is the landing function.  Always call
//   this first. It sets up #buttons_page and calls Buttons.getButtons()
// * Buttons.getButtons() calls the API, setting Api.button and
//   Api.open_games. It calls Buttons.showPage()
// * Buttons.showPage() uses the data returned by the API to build
//   the contents of the page as Buttons.page and calls
//   Buttons.arrangePage()
//
//* Buttons.joinOpenGame() is called whenever the user clicks on one of the
//  Join Game buttons. It calls the API to join the game, setting
//  Api.join_game_result if successful
////////////////////////////////////////////////////////////////////////

Buttons.showButtonsPage = function() {

  // Setup necessary elements for displaying status messages
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#buttons_page').length === 0) {
    $('body').append($('<div>', {'id': 'buttons_page', }));
  }

  // Figure out what we're here to display, get all needed information,
  // then display the page
  Buttons.buttonName = Env.getParameterByName('button');
  Buttons.setName = Env.getParameterByName('set');
  if (Buttons.buttonName) {
    Api.getButtonData(Buttons.buttonName, Buttons.showButton);
  } else if (Buttons.setName) {
    Api.getButtonSetData(Buttons.setName, Buttons.showSet);
  } else {
    Api.getButtonSetData(null, Buttons.showSetList);
  }
};

Buttons.showButton = function() {
  Buttons.page = $('<div>');

  if (Api.button.load_status != 'ok') {
    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'An internal error occurred while loading the button.',
      };
    }
  } else if (Api.button.list[Buttons.buttonName] === undefined) {
    Env.message = {
      'type': 'none',
      'text': 'Button not found.',
    };
  } else {
    var button = Api.button.list[Buttons.buttonName];

    var mainDiv = $('<div>', { 'class': 'singleButton' });

    var buttonDetailsArea = $('<div>');
    mainDiv.append(buttonDetailsArea);
    buttonDetailsArea.append(Buttons.buildButtonBox(button));
    buttonDetailsArea.append($('<div>', {
      'text': (button.flavorText ? button.flavorText : 'No flavor text.'),
      'class': 'flavorText',
    }));

    Buttons.page.append(mainDiv);
  }

  // Actually lay out the page
  Buttons.arrangePage();
};

Buttons.buildButtonBox = function(button) {
  var buttonBox = $('<div>', { 'class': 'buttonBox' });
  buttonBox.append($('<img>', {
    'src': Env.ui_root + 'images/button/' + button.artFilename,
    'width': '150px',
  }));
  buttonBox.append($('<div>', { 'text': button.recipe }));
  if (button.buttonName == Buttons.buttonName) {
    buttonBox.append($('<div>', { 'text': button.buttonName }));
  } else {
    buttonBox.append($('<a>', {
      'href': 'buttons.html?button=' + encodeURIComponent(button.buttonName),
      'text': button.buttonName,
    }));
  }
  return buttonBox;
};

Buttons.arrangePage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#buttons_page').empty();
  $('#buttons_page').append(Buttons.page);
};
