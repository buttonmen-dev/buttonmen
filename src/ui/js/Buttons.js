// namespace for this "module"
var Buttons = {};

////////////////////////////////////////////////////////////////////////
//
// Action flow through this page:
// * Buttons.showButtonsPage() is the landing function.  Always call
//   this first. It sets up #buttons_page and inspects the query string.
//   Depending on what it finds there, it calls the API and sets either
//   Api.button or Api.buttonSet. It then calls either Buttons.showButton(),
//   Buttons.showSet() or Buttons.showSetList().
// * Buttons.showButton() uses the data returned by the API to build a page
//   describing a single button, then calls Buttons.arragePage().
// * Buttons.showSet() uses the data returned by the API to build a page
//   describing a single button set with a list of all of its buttons, then
//   calls Buttons.arragePage().
// * Buttons.showSetList() uses the data returned by the API to build a page
//   describing a list of all button sets, then calls Buttons.arragePage().
// * Buttons.arragePage() sets the contents of <div id="buttons_page">
//   on the live page.
////////////////////////////////////////////////////////////////////////

Buttons.showButtonsPage = function() {

  // Setup necessary elements for displaying status messages
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#buttons_page').length === 0) {
    $('body').append($('<div>', { 'id': 'buttons_page', }));
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

    Buttons.arrangePage();
    return;
  }

  // Assume that the version of the button name from the API is canonical
  for (var buttonName in Api.button.list) {
    Buttons.buttonName = buttonName;
    break;
  }

  if (Api.button.list[Buttons.buttonName] === undefined) {
    Env.message = {
      'type': 'none',
      'text': 'Button not found.',
    };

    Buttons.arrangePage();
    return;
  }

  var button = Api.button.list[Buttons.buttonName];

  var mainDiv = $('<div>', { 'class': 'singleButton' });

  var buttonDetailsArea = $('<div>', { 'class': 'buttonDetails' });
  mainDiv.append(buttonDetailsArea);
  buttonDetailsArea.append(Buttons.buildButtonBox(button));
  var secondBox = $('<div>', { 'class': 'secondaryDetails' });
  buttonDetailsArea.append(secondBox);
  secondBox.append($('<p>', {
    'class': 'flavorText',
    'text': (button.flavorText ? button.flavorText : 'No flavor text.'),
  }));

  if (button.hasUnimplementedSkill) {
    mainDiv.append($('<p>', {
      'class': 'warning',
      'text':
        'Warning: This button has unimplemented skills and may not work as ' +
        'expected in games.',
    }));
  }

  if (button.specialText) {
    mainDiv.append($('<h2>', { 'text': 'Special' }));
    mainDiv.append($('<p>', { 'text': button.specialText }));
  }

  var skillsTable = $('<table>', { 'class': 'skills' });
  $.each(button.dieSkills, function(skill, info) {
    var skillRow = $('<tr>');
    skillsTable.append(skillRow);

    skillRow.append($('<th>', { 'text': skill + ' (' + info.code + ')' }));
    var skillDescriptionCell = $('<td>');
    skillRow.append(skillDescriptionCell);
    skillDescriptionCell.append($('<p>', { 'text': info.description }));
    $.each(info.interacts, function(otherSkill, interaction) {
      skillDescriptionCell.append($('<p>', {
        'class': 'interaction',
        'text': 'Interaction with ' + otherSkill + ': ' + interaction
      }));
    });
  });
  // It's debatable whether or not die types should strictly be consider skills,
  // but I think it's fair to lump them in together here.
  $.each(button.dieTypes, function(dieType, info) {
    var skillRow = $('<tr>');
    skillsTable.append(skillRow);

    skillRow.append($('<th>', { 'text': dieType + ' (' + info.code + ')' }));
    var skillDescriptionCell = $('<td>');
    skillRow.append(skillDescriptionCell);
    skillDescriptionCell.append($('<p>', { 'text': info.description }));
  });

  if (skillsTable.find('tr').length > 0) {
    mainDiv.append($('<h2>', { 'text': 'Skills' }));
    mainDiv.append(skillsTable);
  }

  var returnLinkHolder = $('<div>', {
    'class': 'returnLink',
    'text': 'Return to ',
  });
  mainDiv.append(returnLinkHolder);
  returnLinkHolder.append($('<a>', {
    'href': 'buttons.html?set=' + encodeURIComponent(button.buttonSet),
    'text': button.buttonSet,
  }));

  Buttons.page.append(mainDiv);

  Buttons.arrangePage();
  return;
};

Buttons.showSet = function() {
  Buttons.page = $('<div>');

  if (Api.buttonSet.load_status != 'ok') {
    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'An internal error occurred while loading the button set.',
      };
    }

    Buttons.arrangePage();
    return;
  }

  // Assume that the version of the button name from the API is canonical
  for (var setName in Api.buttonSet.list) {
    Buttons.setName = setName;
    break;
  }

  if (Api.buttonSet.list[Buttons.setName] === undefined) {
    Env.message = {
      'type': 'none',
      'text': 'Button set not found.',
    };

    Buttons.arrangePage();
    return;
  }

  var buttonSet = Api.buttonSet.list[Buttons.setName];

  var mainDiv = $('<div>', { 'class': 'singleSet' });

  mainDiv.append($('<h2>', { 'text': buttonSet.setName }));
  $.each(buttonSet.buttons, function(buttonName, button) {
    mainDiv.append(Buttons.buildButtonBox(button));
  });
  var returnLinkHolder = $('<div>', {
    'class': 'returnLink',
    'text': 'Return to ',
  });
  mainDiv.append(returnLinkHolder);
  returnLinkHolder.append($('<a>', {
    'href': 'buttons.html',
    'text': 'All Button Sets',
  }));

  Buttons.page.append(mainDiv);

  Buttons.arrangePage();
};

Buttons.showSetList = function() {
  Buttons.page = $('<div>');

  if (Api.buttonSet.load_status != 'ok') {
    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'An internal error occurred while loading the button set.',
      };
    }

    Buttons.arrangePage();
    return;
  }

  var mainDiv = $('<div>', { 'class': 'allSets' });

  mainDiv.append($('<h2>', { 'text': 'All Button Sets' }));
  $.each(Api.buttonSet.list, function(setName, buttonSet) {
    var setBox = $('<div>', { 'class': 'setBox', });
    mainDiv.append(setBox);
    if (buttonSet.onlyHasUnimplementedButtons) {
      setBox.addClass('unimplemented');
    }
    setBox.append($('<a>', {
      'text': setName,
      'href': 'buttons.html?set=' + encodeURIComponent(setName),
      'class': 'buttonSetLink',
    }));
    setBox.append($('<div>', {
      'text': 'Buttons: ' + buttonSet.numberOfButtons,
    }));
    var skills = '';
    $.each(buttonSet.dieSkills, function(index, dieSkill) {
      skills += dieSkill + ', ';
    });
    $.each(buttonSet.dieTypes, function(index, dieType) {
      skills += dieType + ', ';
    });
    if (skills) {
      // Trim off the trailing ', '
      skills = skills.replace(/(, )$/, '');
      setBox.append($('<div>', {
        'text': 'Skills: ' + skills,
      }));
    }
  });

  Buttons.page.append(mainDiv);

  Buttons.arrangePage();
};

Buttons.buildButtonBox = function(button) {
  var buttonBox = $('<div>', { 'class': 'buttonBox' });
  if (button.hasUnimplementedSkill) {
    buttonBox.addClass('unimplemented');
  }
  buttonBox.append($('<img>', {
    'src': Env.ui_root + 'images/button/' + button.artFilename,
    'width': '150px',
  }));
  buttonBox.append($('<div>', { 'text': button.recipe }));
  if (button.buttonName == Buttons.buttonName) {
    buttonBox.append($('<div>', {
      'text': button.buttonName,
      'class': 'buttonName',
    }));
  } else {
    buttonBox.append($('<a>', {
      'href': 'buttons.html?button=' + encodeURIComponent(button.buttonName),
      'text': button.buttonName,
      'class': 'buttonName',
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
