// namespace for this "module"
var Skills = {};

Skills.bodyDivId = 'skills_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Skills.showLoggedInPage() is the landing function. Always call
//   this first. It calls Skills.showPage()
// * Skills.showLoggedOutPage() is the other landing function.  Always call
//   this first when logged out.  It also calls Skills.showPage()
// * Skills.showPage() uses the data returned by the API to build
//   the contents of the page as Skills.page and calls
//   Login.arrangePage()
////////////////////////////////////////////////////////////////////////

Skills.showLoggedInPage = function() {
  // Get all needed information, then display Skills page
  Skills.getInfo(Skills.showPage);
};

Skills.getInfo = function(callback) {
  Env.callAsyncInParallel([
    Api.getDieSkillsData,
    Api.getDieTypesData,
  ], callback);
};

// Skills basically behaves roughly the same way regardless of whether or not
// you're logged in
Skills.showLoggedOutPage = function() {
  Skills.showLoggedInPage();
};

Skills.showPage = function() {
  Skills.page = $('<div>');

  if (Api.dieSkills.load_status != 'ok' || Api.dieTypes.load_status != 'ok') {
    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'An internal error occurred while loading the ' +
                'list of skills and die types.',
      };
    }

    Login.arrangePage(Skills.page);
    return;
  }

  Skills.page.append(Skills.directoryDiv());
  Skills.page.append(Skills.skillDescriptionsDiv());
  Skills.page.append(Skills.dieTypeDescriptionsDiv());

  // Actually lay out the page
  Login.arrangePage(Skills.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

Skills.directoryDiv = function() {
  var directoryDiv = $('<div>', { 'class': 'directory' });
  directoryDiv.append($('<h2>', { 'text': 'Die Skills and Types' }));

  var displayList = Api.dieSkills.list;
  var dieTypesList = Api.dieTypes.list;
  for (var dieTypeKey in dieTypesList) {
    displayList[dieTypeKey] = dieTypesList[dieTypeKey];
  }
  var displayCodes = Object.keys(displayList);
  displayCodes.sort(Skills.orderAlphabeticallyByCode);

  $.each(displayCodes, function(idx, code) {
    var info = displayList[code];
    var directoryItemDiv = $('<div>', { 'class': 'item' });
    var symbolDiv =  $('<div>', { 'class': 'symbol' });
    var symbolLink =
      $('<a>', {
        'href': '#' + info.name.replace(/\s+/g, ''),
        'text': code
      });
    symbolDiv.append(symbolLink);

    var nameLink =
      $('<a>', {
        'class': 'link',
        'href': '#' + info.name.replace(/\s+/g, ''),
        'text': info.name
      });

    directoryItemDiv.append(symbolDiv);
    directoryItemDiv.append(nameLink);

    directoryDiv.append(directoryItemDiv);
  });
  return directoryDiv;
};

Skills.orderAlphabeticallyByCode = function(a, b) {
  var comparison = a.length - b.length;

  if (!comparison) {
    comparison = a.toLowerCase().charCodeAt(0) - b.toLowerCase().charCodeAt(0);
  }

  if (!comparison) {
    comparison = a.charCodeAt(0) - b.charCodeAt(0);
  }

  return comparison;
};

Skills.skillDescriptionsDiv = function() {
  var skillDescriptionsDiv = $('<div>', { 'class': 'skillDescriptions' });
  skillDescriptionsDiv.append($('<h2>', { 'text': 'Skill Descriptions' }));

  var skillsTable = $('<table>', { 'class': 'skills' });
  $.each(Api.dieSkills.list, function(code, info) {
    var skillRow = $('<tr>');
    skillsTable.append(skillRow);

    var skillHeader = $('<th>');
    skillHeader.append($('<a>', {
      'id': info.name.replace(/\s+/g, ''),
      'text': info.name + ': ' + code
    }));
    skillRow.append(skillHeader);

    var skillDescriptionCell = $('<td>');
    skillRow.append(skillDescriptionCell);
    skillDescriptionCell.append($('<p>', { 'text': info.description }));

    if (info.interacts) {
      $.each(info.interacts, function(otherSkill, interaction) {
        skillDescriptionCell.append($('<p>', {
          'class': 'interaction',
          'text': 'Interaction with ' + otherSkill + ': ' + interaction
        }));
      });
    }
  });

  skillDescriptionsDiv.append(skillsTable);
  return skillDescriptionsDiv;
};

Skills.dieTypeDescriptionsDiv = function() {
  var dieTypeDescriptionsDiv = $('<div>', { 'class': 'dieTypeDescriptions' });
  dieTypeDescriptionsDiv.append($('<h2>', { 'text': 'Die Type Descriptions' }));

  var skillsTable = $('<table>', { 'class': 'skills' });

  $.each(Api.dieTypes.list, function(code, info) {
    var skillRow = $('<tr>');
    skillsTable.append(skillRow);

    var skillHeader = $('<th>');
    skillHeader.append($('<a>', {
      'id': info.name.replace(/\s+/g, ''),
      'text': info.name + ': ' + code
    }));
    skillRow.append(skillHeader);

    var skillDescriptionCell = $('<td>');
    skillRow.append(skillDescriptionCell);
    skillDescriptionCell.append($('<p>', { 'text': info.description }));
  });
  dieTypeDescriptionsDiv.append(skillsTable);
  return dieTypeDescriptionsDiv;
};
