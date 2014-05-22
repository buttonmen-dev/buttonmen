// namespace for this "module"
var Profile = {};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Profile.showProfilePage() is the landing function. Always call
// this first. It sets up #profile_page and calls Profile.getProfile()
// * Profile.getProfile() calls the API, setting Api.button and
// Api.open_games. It calls Profile.showPage()
// * Profile.showPage() uses the data returned by the API to build
// the contents of the page as Profile.page and calls Profile.layoutPage()
//
//* Profile.joinOpenGame() is called whenever the user clicks on one of the
// Join Game buttons. It calls the API to join the game, setting
// Api.join_game_result if successful
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
  if (Api.profile_info.dob_month != 0 && Api.profile_info.dob_day != 0) {
    birthday = Api.MONTH_NAMES[Api.profile_info.dob_month] + ' ' +
      Api.profile_info.dob_day;
  }

  var challengeLink = $('<a>', {
    'href': 'create_game.html?playerName=' +
      encodeURIComponent(Api.profile_info.name_ingame),
    'text': 'Create game!',
  });

  tbody.append(Profile.buildProfileTableRow('Real name',
    Api.profile_info.name_irl, 'unknown', true));
  tbody.append(Profile.buildProfileTableRow('Record (W/L)',
    Api.profile_info.n_games_won + ' / ' + Api.profile_info.n_games_lost,
    'none', true));
  tbody.append(Profile.buildProfileTableRow('Birthday', birthday, 'unknown',
    true));
  tbody.append(Profile.buildProfileTableRow('Email address',
    Api.profile_info.email, 'private', true));
  tbody.append(Profile.buildProfileTableRow('Member since',
    Env.formatTimestamp(Api.profile_info.creation_time, 'date'), 'unknown',
    true));
  //TODO make this last_access_time
  tbody.append(Profile.buildProfileTableRow('Last visit',
    Env.formatTimestamp(Api.profile_info.last_action_time, 'date'), 'never, \n\
    true'));
  tbody.append(Profile.buildProfileTableRow(
    'Challenge ' + Api.profile_info.name_ingame + ' to a game',
    challengeLink, 'unavailable', true));
  tbody.append(Profile.buildProfileTableRow('Comment',
    Api.profile_info.comment, 'none', false));

  if (Api.profile_info.image_path) {
    var url = Api.profile_info.image_path;
    if (!url.match(/^http/i)) {
      url = 'http://' + url;
    }
    var image = $('<img>', {
      'src': url,
      'class': 'profileImage',
    })

    var valueTds = table.find('td.shrinkable');
    valueTds.removeClass('value');
    valueTds.addClass('partialValue');

    var imageTd = $('<td>', { 'class': 'partialValue', 'rowspan': '7', });
    valueTds.first().parent().append(imageTd);
    imageTd.append(image);

    var valueTds = table.find('td.unshrinkable');
    valueTds.attr('colspan', '2');
  }

  return table;
};

Profile.buildProfileTableRow =
  function(label, value, missingValue, shrinkable) {
  var valueClass = (shrinkable ? 'shrinkable' : 'unshrinkable');
  var tr = $('<tr>');
  tr.append($('<td>', { 'text': label + ':', 'class': 'label' }));
  if (value) {
    if (value instanceof jQuery) {
      tr.append($('<td>', { 'class': 'value ' + valueClass }).append(value));
    } else {
      tr.append($('<td>', {
        'text': value, 'class':
          'value ' + valueClass
      }));
    }
  } else {
    tr.append($('<td>', {
      'text': missingValue,
      'class': 'missingValue ' + valueClass
    }));
  }
  return tr;
}
