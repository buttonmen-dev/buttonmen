// namespace for this "module"
var Profile = {};

Profile.bodyDivId = 'profile_page';
Profile.pageTitle = 'Profile &mdash; Button Men Online';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Profile.showLoggedInPage() is the landing function. Always call
// this first. It sets up #profile_page and calls Profile.getProfile()
// * Profile.getProfile() calls the API, setting Api.profile_info. It calls
//   Profile.showPage()
// * Profile.showPage() uses the data returned by the API to build
//   the contents of the page as Profile.page and calls Login.arrangePage()
////////////////////////////////////////////////////////////////////////

Profile.showLoggedInPage = function() {
  // Get all needed information, then display Profile page
  Profile.getProfile(Profile.showPage);
};

Profile.getProfile = function(callback) {
  var playerName = Env.getParameterByName('player');

  $('title').html(playerName + ' &mdash; Profile &mdash; Button Men Online');

  Api.loadProfileInfo(playerName, callback);
};

Profile.showPage = function() {
  Profile.page = $('<div>');

  if (Api.profile_info.load_status != 'ok') {
    if (Env.message === undefined || Env.message === null) {
      Env.message = {
        'type': 'error',
        'text': 'An internal error occurred while loading the profile info.',
      };
    }
  } else {
    Profile.page.append(Profile.buildProfileTable());
  }

  // Actually layout the page
  Login.arrangePage(Profile.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

Profile.buildProfileTable = function() {
  var table = $('<table>', { 'class': 'profileTable', });

  var thead = $('<thead>');
  table.append(thead);
  thead.append(Profile.buildProfileTableRow('Profile',
    Api.profile_info.name_ingame, 'unknown'));

  var tbody = $('<tbody>');
  table.append(tbody);

  var birthday = null;
  if (Api.profile_info.dob_month !== 0 && Api.profile_info.dob_day !== 0) {
    birthday = Api.MONTH_NAMES[Api.profile_info.dob_month] + ' ' +
      Api.profile_info.dob_day;
  }

  var challengeLinkHolder = null;
  if (Login.player != Api.profile_info.name_ingame) {
    challengeLinkHolder = $('<span>');
    challengeLinkHolder.append($('<a>', {
      'href':
        'create_game.html?opponent=' +
        encodeURIComponent(Api.profile_info.name_ingame),
      'text': 'Create game!',
    }));
    if (Api.profile_info.favorite_button) {
      challengeLinkHolder.append(' ');
      challengeLinkHolder.append($('<a>', {
        'href':
          'create_game.html?opponent=' +
          encodeURIComponent(Api.profile_info.name_ingame) +
          '&opponentButton=' +
          encodeURIComponent(Api.profile_info.favorite_button),
        'text': 'With ' + Api.profile_info.favorite_button + '!',
      }));
    }
  }

  var record = Api.profile_info.n_games_won + '/' +
    Api.profile_info.n_games_lost + ' (W/L)';

  var gamesLinksHolder = $('<span>');
  gamesLinksHolder.append($('<a>', {
    'text': 'Active',
    'href':
      Env.ui_root + 'history.html#!playerNameA=' +
      Api.profile_info.name_ingame + '&status=ACTIVE',
  }));
  gamesLinksHolder.append(' ');
  gamesLinksHolder.append($('<a>', {
    'text': 'Completed',
    'href':
      Env.ui_root + 'history.html#!playerNameA=' +
      Api.profile_info.name_ingame + '&status=COMPLETE',
  }));

  var favoriteButtonLink = null;
  if (Api.profile_info.favorite_button) {
    favoriteButtonLink = Env.buildButtonLink(Api.profile_info.favorite_button);
  }
  var favoriteButtonSetLink = null;
  if (Api.profile_info.favorite_buttonset) {
    favoriteButtonSetLink =
      Env.buildButtonSetLink(Api.profile_info.favorite_buttonset);
  }

  var commentHolder = null;
  if (Api.profile_info.comment) {
    commentHolder = $('<span>');
    var cookedComment = Env.prepareRawTextForDisplay(Api.profile_info.comment);
    commentHolder.append(cookedComment);
  }

  var vacationHolder = null;
  if (Api.profile_info.vacation_message) {
    vacationHolder = $('<span>');
    var cookedVacation =
      Env.prepareRawTextForDisplay(Api.profile_info.vacation_message);
    vacationHolder.append(cookedVacation);
  }

  var homepageLink = null;
  if (Api.profile_info.homepage) {
    var homepageUrl = Env.validateUrl(Api.profile_info.homepage);
    if (homepageUrl) {
      homepageLink = $('<a>', {
        'text': homepageUrl,
        'href': homepageUrl,
        'target': '_blank',
      });
    } else {
      homepageLink = $('<a>', {
        'text': 'INVALID URL',
        'href': 'javascript:alert("Homepage URL was invalid")',
        'target': '_blank',
      });
    }
  }

  var solipsismAlternatives = [
    'solipsism overflow',
    'autoludic prohibition',
    'cloning tanks offline',
    'tu ipse es',
    'can\'t. shan\'t. won\'t.',
    'on your own? no',
    'it\'d never work out',
    'solitaire unavailable',
    'try another castle',
    'expand your search',
    'you and your shadow?',
    'mirror match = mistake',
    'two\'s company; one\'s not',
    'other people exist!',
    'you know you too well',
    'isn\'t that cheating?',
    'you\'d probably lose',
    'you\'d obviously win',
    'it\'d just be a draw',
    'let others play you',
    'the loneliest number',
    'are you twins?',
    'one hand clapping',
    '1 + 0 != 2',
    'not yourself, silly',
    'I\'m sorry, Dave...',
    'ceci n\'est pas une erreur',
    'the site doesn\'t like that',
    'would summon Cthulhu',
    'spatio-temporal paradox',
    'bilocate much?',
    'looking out for #1?'
  ];
  var solipsindex = Math.floor(Math.random() * solipsismAlternatives.length);
  var solipsismNotification = solipsismAlternatives[solipsindex];

  tbody.append(Profile.buildProfileTableRow('Real name',
    Api.profile_info.name_irl, 'unknown', true));
  tbody.append(Profile.buildProfileTableRow('Record', record, 'none', true));
  tbody.append(Profile.buildProfileTableRow('Birthday', birthday, 'unknown',
    true));
  if (Api.profile_info.gender) {
    tbody.append(Profile.buildProfileTableRow('Gender',
      Api.profile_info.gender, 'irrelevant', true));
  }
  tbody.append(Profile.buildProfileTableRow('Email address',
    Api.profile_info.email, 'private', true));
  tbody.append(Profile.buildProfileTableRow('Member since',
    Env.formatTimestamp(Api.profile_info.creation_time, 'date'), 'unknown',
    true));
  tbody.append(Profile.buildProfileTableRow('Last visit',
    Env.formatTimestamp(Api.profile_info.last_access_time, 'date'), 'never',
    true));
  tbody.append(Profile.buildProfileTableRow('Games', gamesLinksHolder, '',
    true));
  tbody.append(Profile.buildProfileTableRow('Favorite button',
    favoriteButtonLink, 'undecided', true));
  tbody.append(Profile.buildProfileTableRow('Favorite button set',
    favoriteButtonSetLink, 'unselected', true));
  tbody.append(Profile.buildProfileTableRow(
    'Challenge ' + Api.profile_info.name_ingame + ' to a game',
    challengeLinkHolder, solipsismNotification, false));
  tbody.append(Profile.buildProfileTableRow('Homepage',
    homepageLink, 'homeless', false));
  tbody.append(Profile.buildProfileTableRow('Comment',
    commentHolder, 'none', false));
  if (vacationHolder) {
    tbody.append(Profile.buildProfileTableRow('Vacation Message',
      vacationHolder, 'none', false));
  }

  if (!Env.getCookieNoImages()) {
    var url;
    if (Api.profile_info.uses_gravatar) {
      url = 'http://www.gravatar.com/avatar/' + Api.profile_info.email_hash;
      if (Api.profile_info.image_size) {
        url += '?s=' + Api.profile_info.image_size;
      }
    } else {
      url = Env.ui_root + 'images/no-image.png';
    }
    var image = $('<img>', {
      'src': url,
      'class': 'profileImage',
    });

    var partialTds = table.find('td.partialValue');

    var imageTd = $('<td>', { 'class': 'partialValue', 'rowspan': '9', });
    partialTds.first().parent().append(imageTd);
    imageTd.append(image);
  }

  return table;
};

Profile.buildProfileTableRow = function(
    label, value, missingValue, shrinkable) {
  var valueClass = (shrinkable ? 'partialValue' : 'value');
  var tr = $('<tr>');
  tr.append($('<td>', { 'text': label + ':', 'class': 'label' }));
  if (value) {
    if (value instanceof jQuery) {
      tr.append($('<td>', {
        'class': valueClass,
        'colspan': (shrinkable ? '1': '2'),
      }).append(value));
    } else {
      tr.append($('<td>', {
        'text': value,
        'class': valueClass,
        'colspan': (shrinkable ? '1': '2'),
      }));
    }
  } else {
    tr.append($('<td>', {
      'text': missingValue,
      'class': 'missingValue ' + valueClass,
      'colspan': (shrinkable ? '1': '2'),
    }));
  }
  return tr;
};

