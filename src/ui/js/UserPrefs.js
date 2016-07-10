// namespace for this "module"
var UserPrefs = {};

UserPrefs.bodyDivId = 'userprefs_page';

UserPrefs.NAME_IRL_MAX_LENGTH = 40;
UserPrefs.EMAIL_MAX_LENGTH = 254;
UserPrefs.MIN_IMAGE_SIZE = 80;
UserPrefs.MAX_IMAGE_SIZE = 200;
UserPrefs.GENDER_MAX_LENGTH = 100;
UserPrefs.HOMEPAGE_MAX_LENGTH = 100;
UserPrefs.COMMENT_MAX_LENGTH = 255;
UserPrefs.VACATION_MAX_LENGTH = 255;
UserPrefs.DEFAULT_COLORS = {
  'player_color': '#dd99dd',
  'opponent_color': '#ddffdd',
  'neutral_color_a': '#cccccc',
  'neutral_color_b': '#dddddd',
};
UserPrefs.ALTERNATE_GENDER_OPTION = 'It\'s complicated';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * UserPrefs.showLoggedInPage() is the landing function.  Always call
//   this first
// * UserPrefs.assemblePage(), which calls one of a number of functions
//   UserPrefs.action<SomeAction>()
// * each UserPrefs.action<SomeAction>() function must set UserPrefs.page and
//   UserPrefs.form, then call Login.arrangePage()
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

UserPrefs.showLoggedInPage = function() {
  Env.callAsyncInParallel(
    [
      { 'func': Api.getButtonData, 'args': [ null ] },
      Api.getUserPrefsData,
    ], UserPrefs.assemblePage);
};

// Assemble and display the userprefs portion of the page
UserPrefs.assemblePage = function() {
  if (Api.user_prefs.load_status == 'ok') {

    // There's only one possible action, allowing the user to change
    // the preferences
    UserPrefs.actionSetPrefs();
  } else {
    UserPrefs.actionFailed();
  }
};

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by UserPrefs.js.
// Each function should start by populating UserPrefs.page and
// UserPrefs.form and end by invoking Login.arrangePage();

UserPrefs.actionFailed = function() {

  // Create empty page and undefined form objects to be filled later
  UserPrefs.page = $('<div>');
  UserPrefs.form = null;

  // No text because page data acquisition failed - Env.message
  // will tell the user what happened

  // Lay out the page
  Login.arrangePage(UserPrefs.page, UserPrefs.form, '#userprefs_action_button');
};

UserPrefs.actionSetPrefs = function() {
  // Include the option to leave them blank
  var buttons = { '': '' };
  var buttonSets = { '': '' };

  $.each(Api.button.list, function(button, buttonInfo) {
    buttonSets[buttonInfo.buttonSet] = buttonInfo.buttonSet;
    buttons[button] = button;
  });

  // Create empty page and undefined form objects to be filled later
  UserPrefs.page = $('<div>');
  UserPrefs.form = null;

  var prefsdiv = $('<div>');
  var prefsform = $('<form>', {
    'id': 'userprefs_action_form',
    'action': 'javascript:void(0);'
  });

  // We can't use a variable as a key when we're defining an object like this,
  // so we need to do that entry separately.
  var genderDefaults = {
    '': '',
    'Male': 'Male',
    'Female': 'Female',
  };
  genderDefaults[UserPrefs.ALTERNATE_GENDER_OPTION] =
    UserPrefs.ALTERNATE_GENDER_OPTION;

  var dieBackgroundDefaults = {
    'circle': 'circle',
    'symmetric': 'symmetric',
    'realistic': 'realistic',
  };

  var profileBlurb = 'These settings affect what appears on your profile page.';
  var profileSettings = {
    'name_irl': {
      'text': 'Real name',
      'type': 'text',
      'value': Api.user_prefs.name_irl,
      'length': UserPrefs.NAME_IRL_MAX_LENGTH,
    },
    'is_email_public': {
      'text': 'Make email address public',
      'type': 'checkbox',
      'checked': Api.user_prefs.is_email_public,
    },
    'dob': {
      'text': 'Birthday',
      'type': 'date',
      'value': {
        'month': Api.user_prefs.dob_month,
        'day': Api.user_prefs.dob_day,
      },
    },
    'gender_select': {
      'text': 'Gender',
      'type': 'select',
      'value': Api.user_prefs.gender,
      'source': genderDefaults,
    },
    'gender_text': {
      'text': 'Feel free to elaborate',
      'type': 'text',
      'value': Api.user_prefs.gender,
      'length': UserPrefs.GENDER_MAX_LENGTH,
    },
    'uses_gravatar': {
      'text': 'Use gravatar for profile image',
      'type': 'checkbox',
      'checked': Api.user_prefs.uses_gravatar,
    },
    'image_size': {
      'text': 'Gravatar image size',
      'type': 'text',
      'value': Api.user_prefs.image_size,
      'after': ' pixels',
    },
    'favorite_button': {
      'text': 'Favorite button',
      'type': 'select',
      'value': Api.user_prefs.favorite_button,
      'source': buttons,
    },
    'favorite_buttonset': {
      'text': 'Favorite button set',
      'type': 'select',
      'value': Api.user_prefs.favorite_buttonset,
      'source': buttonSets,
    },
    'homepage': {
      'text': 'Homepage',
      'type': 'text',
      'value': Api.user_prefs.homepage,
      'length': UserPrefs.HOMEPAGE_MAX_LENGTH,
    },
    'comment': {
      'text': 'Comment',
      'type': 'textarea',
      'value': Api.user_prefs.comment,
      'length': UserPrefs.COMMENT_MAX_LENGTH,
    },
    'vacation_message': {
      'text': 'Vacation Message',
      'type': 'textarea',
      'value': Api.user_prefs.vacation_message,
      'length': UserPrefs.VACATION_MAX_LENGTH,
    },
  };

  var autoBlurb = 'These preferences configure things that the site can do ' +
    'automatically for you.';
  var autoPrefs = {
    'autoaccept': {
      'text': 'Automatically accept challenges from other players',
      'type': 'checkbox',
      'checked': Api.user_prefs.autoaccept,
    },
    'autopass': {
      'text': 'Automatically pass when you have no valid attack',
      'type': 'checkbox',
      'checked': Api.user_prefs.autopass,
    },
    'monitor_redirects_to_game': {
      'text': 'Redirect to waiting games when in Monitor mode',
      'type': 'checkbox',
      'checked': Api.user_prefs.monitor_redirects_to_game,
    },
    'monitor_redirects_to_forum': {
      'text': 'Redirect to new forum posts when in Monitor mode',
      'type': 'checkbox',
      'checked': Api.user_prefs.monitor_redirects_to_forum,
    },
    'automatically_monitor': {
      'text': 'Automatically Monitor after "Next game" runs out',
      'type': 'checkbox',
      'checked': Api.user_prefs.automatically_monitor,
    },
  };

  var gameplayBlurb = 'These preferences make advanced Button Men attacks ' +
    'available to you, at the expense of a more complex user experience.';
  var gameplayPrefs = {
    'fire_overshooting': {
      'text': 'Enable fire overshooting for power attacks',
      'type': 'checkbox',
      'checked': Api.user_prefs.fire_overshooting,
    },
  };

  var colorBlurb =
    'These are the colors used to represent each player in a game.';
  var colorPrefs = {
    'player_color': {
      'text': 'Your color',
      'type': 'color',
      'value': Api.user_prefs.player_color,
    },
    'opponent_color': {
      'text': 'Your opponent\'s color',
      'type': 'color',
      'value': Api.user_prefs.opponent_color,
    },
    'neutral_color_a': {
      'text': 'Neutral player color',
      'type': 'color',
      'value': Api.user_prefs.neutral_color_a,
    },
    'neutral_color_b': {
      'text': 'Neutral player opponent\'s color',
      'type': 'color',
      'value': Api.user_prefs.neutral_color_b,
    },
  };

  var accountBlurb = 'Current password is required to change email address ' +
    'or password.';
  var accountSettings = {
    'current_password': {
      'text': 'Current password',
      'type': 'password',
      'value': '',
    },
    'new_password': {
      'text': 'New password',
      'type': 'password',
      'value': '',
    },
    'confirm_new_password': {
      'text': 'Confirm new password',
      'type': 'password',
      'value': '',
    },
    'current_email': {
      'text': 'Current email address',
      'type': 'display',
      'value': Api.user_prefs.email,
      'length': UserPrefs.EMAIL_MAX_LENGTH,
    },
    'new_email': {
      'text': 'New email address',
      'type': 'text',
      'value': '',
      'length': UserPrefs.EMAIL_MAX_LENGTH,
    },
    'confirm_new_email': {
      'text': 'Confirm new email address',
      'type': 'text',
      'value': '',
    },
  };

  var matBlurb = 'These preferences control the look and feel of the ' +
    'game mat.';
  var matPrefs = {
    'die_background_select': {
      'text': 'Die background',
      'type': 'select',
      'value': Api.user_prefs.die_background,
      'source': dieBackgroundDefaults,
    },
    'noImages': {
      'text': 'Don\'t load button or player images',
      'type': 'checkboxBrowser',
      'checked': Env.getCookieNoImages(),
    },
    'compactMode': {
      'text': 'Use compact version of game interface',
      'type': 'checkboxBrowser',
      'checked': Env.getCookieCompactMode(),
    }
  };

  var prefsTable = $('<table>', { 'class': 'prefsTable', });
  prefsdiv.append(prefsTable);

  UserPrefs.appendToPreferencesTable(prefsTable, 'Profile Settings',
    profileBlurb, profileSettings);
  UserPrefs.appendToPreferencesTable(prefsTable, 'Automation Preferences',
    autoBlurb, autoPrefs);
  UserPrefs.appendToPreferencesTable(prefsTable, 'Gameplay Preferences',
    gameplayBlurb, gameplayPrefs);
  UserPrefs.appendToPreferencesTable(prefsTable, 'Color Preferences',
    colorBlurb, colorPrefs);
  UserPrefs.appendToPreferencesTable(prefsTable, 'Game Mat Preferences',
    matBlurb, matPrefs);
  UserPrefs.appendToPreferencesTable(prefsTable, 'Account Settings',
    accountBlurb, accountSettings);

  // Gender and gravatar inputs are dynamic
  var genderText = prefsTable.find('#userprefs_gender_text');
  var genderSelect = prefsTable.find('#userprefs_gender_select');
  if (Api.user_prefs.gender === '' || Api.user_prefs.gender == 'Male' ||
      Api.user_prefs.gender == 'Female') {
    genderText.closest('tr').hide();
    genderText.val('');
  } else if (Api.user_prefs.gender == UserPrefs.ALTERNATE_GENDER_OPTION) {
    genderText.val('');
  } else {
    genderSelect.val(UserPrefs.ALTERNATE_GENDER_OPTION);
  }
  genderSelect.change(function() {
    if (genderSelect.val() == UserPrefs.ALTERNATE_GENDER_OPTION) {
      genderText.closest('tr').show();
    } else {
      genderText.closest('tr').hide();
      genderText.val('');
    }
  });

  var gravatarCheck = prefsTable.find('#userprefs_uses_gravatar');
  var imageSizeText = prefsTable.find('#userprefs_image_size');
  if (!Api.user_prefs.uses_gravatar) {
    imageSizeText.closest('tr').hide();
    imageSizeText.val('');
  }
  gravatarCheck.change(function() {
    if (gravatarCheck.is(':checked')) {
      imageSizeText.closest('tr').show();
    } else {
      imageSizeText.closest('tr').hide();
      genderText.val('');
    }
  });

  // Form submission button
  prefsform.append($('<button>', {
    'id': 'userprefs_action_button',
    'text': 'Save preferences'
  }));
  prefsdiv.append(prefsform);

  UserPrefs.page.append(prefsdiv);

  // Function to invoke on button click
  UserPrefs.form = UserPrefs.formSetPrefs;

  // Lay out the page
  Login.arrangePage(UserPrefs.page, UserPrefs.form, '#userprefs_action_button');
};

////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

UserPrefs.formSetPrefs = function() {
  Env.message = null;
  Env.showStatusMessage();

  var name_irl = $('#userprefs_name_irl').val();
  var is_email_public = $('#userprefs_is_email_public').prop('checked');
  var dob_month = $('#userprefs_dob_month').val();
  var dob_day = $('#userprefs_dob_day').val();
  var gender = $('#userprefs_gender_text').val();
  if (!gender) {
    gender = $('#userprefs_gender_select').val();
  }
  var uses_gravatar = $('#userprefs_uses_gravatar').prop('checked');
  var favorite_button = $('#userprefs_favorite_button').val();
  var favorite_buttonset = $('#userprefs_favorite_buttonset').val();
  var image_size = $('#userprefs_image_size').val();
  var homepage = $('#userprefs_homepage').val();
  var comment = $('#userprefs_comment').val();
  var vacation_message= $('#userprefs_vacation_message').val().trim();
  var autoaccept = $('#userprefs_autoaccept').prop('checked');
  var autopass = $('#userprefs_autopass').prop('checked');
  var fire_overshooting = $('#userprefs_fire_overshooting').prop('checked');
  var monitor_redirects_to_game =
    $('#userprefs_monitor_redirects_to_game').prop('checked');
  var monitor_redirects_to_forum =
    $('#userprefs_monitor_redirects_to_forum').prop('checked');
  var automatically_monitor =
    $('#userprefs_automatically_monitor').prop('checked');
  var player_color = $('#userprefs_player_color').spectrum('get');
  var opponent_color = $('#userprefs_opponent_color').spectrum('get');
  var neutral_color_a = $('#userprefs_neutral_color_a').spectrum('get');
  var neutral_color_b = $('#userprefs_neutral_color_b').spectrum('get');
  var current_password = $('#userprefs_current_password').val();
  var new_password = $('#userprefs_new_password').val();
  var confirm_new_password = $('#userprefs_confirm_new_password').val();
  var new_email = $('#userprefs_new_email').val();
  var confirm_new_email = $('#userprefs_confirm_new_email').val();
  var die_background = $('#userprefs_die_background_select').val();
  var noImages = $('#userprefs_noImages').prop('checked');
  var compactMode = $('#userprefs_compactMode').prop('checked');

  var validationErrors = '';

  if ((dob_month !== 0 && dob_day === 0) ||
    (dob_month === 0 && dob_day !== 0)) {
    validationErrors += 'Birthday is incomplete. ';
  }

  if (image_size !== '') {
    if (isNaN(image_size)) {
      validationErrors += 'Gravatar size must be a number of pixels. ';
    } else {
      image_size = parseInt(image_size, 10);
      if (image_size < UserPrefs.MIN_IMAGE_SIZE ||
          image_size > UserPrefs.MAX_IMAGE_SIZE) {
        validationErrors +=
          'Gravatar size must be between ' + UserPrefs.MIN_IMAGE_SIZE +
          ' and ' + UserPrefs.MAX_IMAGE_SIZE + ' pixels. ';
      }
    }
  }

  if (new_password != confirm_new_password) {
    validationErrors += 'New passwords do not match. ';
  }
  if (new_email != confirm_new_email) {
    validationErrors += 'New email addresses do not match. ';
  }
  if (new_email && !new_email.match(Api.VALID_EMAIL_REGEX)) {
    validationErrors += 'Email address is formatted incorrectly. ';
  }
  if (new_password && !current_password) {
    validationErrors += 'Current password is required to change password. ';
  }
  if (new_email && !current_password) {
    validationErrors += 'Current password is required to change email. ';
  }

  if (validationErrors !== '') {
    Env.message = {
      'type': 'error',
      'text': validationErrors,
    };
    Env.showStatusMessage();
    return;
  }

  Env.setCookieNoImages(noImages);
  Env.setCookieCompactMode(compactMode);

  // Only pass these values if the user typed something in
  if (!current_password) {
    current_password = undefined;
  }
  if (!new_password) {
    new_password = undefined;
  }
  if (!new_email) {
    new_email = undefined;
  }

  if (!favorite_button) {
    favorite_button = undefined;
  }

  if (!favorite_buttonset) {
    favorite_buttonset = undefined;
  }

  if (!image_size) {
    image_size = undefined;
  }

  Api.apiFormPost(
    {
      'type': 'savePlayerInfo',
      'name_irl': name_irl,
      'is_email_public': is_email_public,
      'dob_month': dob_month,
      'dob_day': dob_day,
      'gender': gender,
      'favorite_button': favorite_button,
      'favorite_buttonset': favorite_buttonset,
      'image_size': image_size,
      'uses_gravatar': uses_gravatar,
      'homepage': homepage,
      'comment': comment,
      'vacation_message': vacation_message,
      'autoaccept': autoaccept,
      'autopass': autopass,
      'fire_overshooting': fire_overshooting,
      'monitor_redirects_to_game': monitor_redirects_to_game,
      'monitor_redirects_to_forum': monitor_redirects_to_forum,
      'automatically_monitor': automatically_monitor,
      'player_color': player_color.toHexString(),
      'opponent_color': opponent_color.toHexString(),
      'neutral_color_a': neutral_color_a.toHexString(),
      'neutral_color_b': neutral_color_b.toHexString(),
      'current_password': current_password,
      'new_password': new_password,
      'new_email': new_email,
      'die_background': die_background,
    },
    {
      'ok': { 'type': 'fixed', 'text': 'User details set successfully.', },
      'notok': { 'type': 'server', }
    },
    '#userprefs_action_button',
    UserPrefs.showLoggedInPage,
    UserPrefs.showLoggedInPage
  );
};

////////////////////////////////////////////////////////////////////////
// Utilty functions for building page elements

UserPrefs.appendToPreferencesTable = function(prefsTable, sectionTitle,
  sectionBlurb, prefs) {
  var titleRow = $('<tr>');
  prefsTable.append(titleRow);
  titleRow.append($('<th>', {
    'class': 'title2',
    'text': sectionTitle,
    'colspan': '2',
  }));

  var blurbRow = $('<tr>');
  prefsTable.append(blurbRow);
  blurbRow.append($('<td>', {
    'html': sectionBlurb,
    'style': 'font-style: italic;',
    'colspan': '2',
  }));

  $.each(prefs, function(entryKey, entryInfo) {
    var entryRow = $('<tr>');
    var labelText = entryInfo.text;
    if (labelText) {
      labelText += ':';
    }
    entryRow.append($('<td>', {
      'text': labelText,
      'class': 'label label_' + entryInfo.type,
    }));
    var entryInput = $('<td>', { 'class': 'value', });
    switch(entryInfo.type) {
    case 'display':
      entryInput.append($('<span>', { 'text': entryInfo.value, }));
      break;
    case 'date':
      var monthSelect = $('<select>', {
        'name': entryKey + '_month',
        'id': 'userprefs_' + entryKey + '_month',
      });
      entryInput.append(monthSelect);
      for (var monthIndex = 0; monthIndex <= 12; monthIndex++) {
        monthSelect.append($('<option>', {
          'value': monthIndex,
          'text': Api.MONTH_NAMES[monthIndex],
          'selected': (entryInfo.value.month == monthIndex),
        }));
      }

      var daySelect = $('<select>', {
        'name': entryKey + '_day',
        'id': 'userprefs_' + entryKey + '_day',
      });
      entryInput.append(daySelect);
      for (var dayIndex = 0; dayIndex <= 31; dayIndex++) {
        daySelect.append($('<option>', {
          'value': dayIndex,
          'text': (dayIndex === 0 ? 'Day' : dayIndex),
          'selected': (entryInfo.value.day == dayIndex),
        }));
      }

      break;
    case 'textarea':
      entryInput.append($('<textarea>', {
        'name': entryKey,
        'id': 'userprefs_' + entryKey,
        'maxlength': entryInfo.length,
        'rows': 6,
      }).val(entryInfo.value));
      break;
    case 'image':
      if (entryInfo.value) {
        var url = entryInfo.value;
        if (!url.match(/^http/i)) {
          url = 'http://' + url;
        }
        entryInput.append($('<img>', {
          'src': url,
          'class': 'profileImage',
        }));
      }
      break;
    case 'select':
      var select = $('<select>', {
        'name': entryKey,
        'id': 'userprefs_' + entryKey,
      });
      entryInput.append(select);
      $.each(entryInfo.source, function(key, value) {
        select.append($('<option>', {
          'text': key,
          'value': value,
        }));
      });
      select.val(entryInfo.value);
      break;
    case 'color':
      var colorPicker = $('<input>', {
        'type': 'text',
        'name': entryKey,
        'id': 'userprefs_' + entryKey,
      });
      entryInput.append(colorPicker);
      colorPicker.spectrum({
        'color': entryInfo.value,
        'showInput': true,
        'showInitial': true,
        'preferredFormat': 'hex',
        'localStorageKey': 'spectrum.bmColorPrefs',
      });

      entryInput.append(' ');
      var defaultColorLink = $('<span>', {
        'text': 'Default ',
        'class': 'pseudoLink',
      });
      entryInput.append(defaultColorLink);
      defaultColorLink.click(function() {
        colorPicker.spectrum('set', UserPrefs.DEFAULT_COLORS[entryKey]);
      });
      break;
    case 'checkboxBrowser':
      entryInput.append($('<input>', {
        'type': 'checkbox',
        'name': entryKey,
        'id': 'userprefs_' + entryKey,
        'value': entryInfo.value,
        'checked': entryInfo.checked,
        'maxlength': entryInfo.length,
      }));
      entryInput.append($('<span>', {
        'text': '(only applies to the current browser)',
        'class': 'current_browser_only',
      }));
      break;
    default:
      entryInput.append($('<input>', {
        'type': entryInfo.type,
        'name': entryKey,
        'id': 'userprefs_' + entryKey,
        'value': entryInfo.value,
        'checked': entryInfo.checked,
        'maxlength': entryInfo.length,
      }));
    }
    entryRow.append(entryInput);

    if (entryInfo.after) {
      entryInput.append(entryInfo.after);
    }
    prefsTable.append(entryRow);
  });

  var spacerRow = $('<tr>');
  prefsTable.append(spacerRow);
  spacerRow.append($('<td>', { 'colspan': '2', }).append('&nbsp;'));
};
