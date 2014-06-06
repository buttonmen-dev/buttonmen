// namespace for this "module"
var Profile = {};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Profile.showProfilePage() is the landing function. Always call
// this first. It sets up #profile_page and calls Profile.getProfile()
// * Profile.getProfile() calls the API, setting Api.profile_info. It calls
//   Profile.showPage()
// * Profile.showPage() uses the data returned by the API to build
//   the contents of the page as Profile.page and calls Profile.layoutPage()
// * Profile.layoutPage() sets the contents of <div id="profile_page"> on the
//   live page
////////////////////////////////////////////////////////////////////////

Profile.showProfilePage = function() {

  // Setup necessary elements for displaying status messages
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#profile_page').length === 0) {
    $('body').append($('<div>', {'id': 'profile_page', }));
  }

  // Get all needed information, then display Profile page
  Profile.getProfile(Profile.showPage);
};

Profile.getProfile = function(callback) {
  var playerName = Env.getParameterByName('player');

  if (Login.logged_in) {
    Api.loadProfileInfo(playerName, callback);
  } else {
    return callback();
  }
};

Profile.showPage = function() {
  Profile.page = $('<div>');

  if (!Login.logged_in) {
    Env.message = {
      'type': 'error',
      'text': 'Can\'t view player profile because you are not logged in',
    };
  } else if (Api.profile_info.load_status != 'ok') {
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
  Profile.layoutPage();
};

Profile.layoutPage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#profile_page').empty();
  $('#profile_page').append(Profile.page);
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

  var challengeLink = null;
  if (Login.player != Api.profile_info.name_ingame) {
    challengeLink = $('<a>', {
      'href': 'create_game.html?opponent=' +
        encodeURIComponent(Api.profile_info.name_ingame),
      'text': 'Create game!',
    });
  }

  var record = Api.profile_info.n_games_won + ' W / ' +
    Api.profile_info.n_games_lost + ' L';

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
  tbody.append(Profile.buildProfileTableRow('Email address',
    Api.profile_info.email, 'private', true));
  tbody.append(Profile.buildProfileTableRow('Member since',
    Env.formatTimestamp(Api.profile_info.creation_time, 'date'), 'unknown',
    true));
  tbody.append(Profile.buildProfileTableRow('Last visit',
    Env.formatTimestamp(Api.profile_info.last_access_time, 'date'), 'never',
    true));
  tbody.append(Profile.buildProfileTableRow(
    'Challenge ' + Api.profile_info.name_ingame + ' to a game',
    challengeLink, solipsismNotification, true));
  tbody.append(Profile.buildProfileTableRow('Comment',
    Api.profile_info.comment, 'none', false));

  if (!Env.getCookieNoImages()) {
    var url = 'images/no-image.png';
    var image = $('<img>', {
      'src': url,
      'class': 'profileImage',
    });

    var partialTds = table.find('td.partialValue');

    var imageTd = $('<td>', { 'class': 'partialValue', 'rowspan': '7', });
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
