// namespace for this "module"
var UserPrefs = {};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * UserPrefs.showUserPrefsPage() is the landing function.  Always call
//   this first
// * UserPrefs.getUserPrefs() asks the API for the user's current
//   preferences.  It clobbers UserPrefs.api, then calls
// * UserPrefs.assemblePage(), which calls one of a number of functions
//   UserPrefs.action<SomeAction>()
// * each UserPrefs.action<SomeAction>() function must set UserPrefs.page and
//   UserPrefs.form, then call UserPrefs.layoutPage()
// * UserPrefs.layoutPage() sets the contents of <div id="userprefs_page">
//   on the live page
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

UserPrefs.showUserPrefsPage = function() {

  // Setup necessary elements for displaying status messages
  $.getScript('js/Env.js');
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#userprefs_page').length == 0) {
    $('body').append($('<div>', {'id': 'userprefs_page' }));
  }

  // Only allow logged-in users to view and change preferences
  if (Login.logged_in) {
    UserPrefs.getUserPrefsData(UserPrefs.assemblePage);
  } else {
    Env.message = {
      'type': 'error',
      'text': "Can't view/set preferences because you are not logged in",
    };
    UserPrefs.actionFailed();
  }
}

// Assemble and display the userprefs portion of the page
UserPrefs.assemblePage = function() {
  if (UserPrefs.api.load_status == 'ok') {

    // There's only one possible action, allowing the user to change
    // the preferences
    UserPrefs.actionSetPrefs();
  } else {
    UserPrefs.actionFailed();
  }
}

// Actually lay out the page
UserPrefs.layoutPage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#userprefs_page').empty();
  $('#userprefs_page').append(UserPrefs.page);

  if (UserPrefs.form) {
    $('#userprefs_action_button').click(UserPrefs.form);
  }
}

UserPrefs.getUserPrefsData = function(callbackfunc) {
  UserPrefs.api = {
    'load_status': 'failed',
  }
  $.post(Env.api_location,
         { type: 'loadPlayerInfo' },
	 function(rs) {
	   if (rs.status == 'ok') {
	     if (UserPrefs.parseUserPrefsData(rs.data)) {
	       UserPrefs.api.load_status = 'ok';
	     } else {
	       Env.message = {
		 'type': 'error', 'text':
		   "User preferences received from server could not be parsed",
	       };
	     }
	   } else {
	     Env.message = {
	       'type': 'error', 'text': rs.message,
	     };
	   }
	   return callbackfunc();
	 }
  ).fail(function () {
    Env.message = {
      'type': 'error',
      'text': 'Internal error when calling loadPlayerInfo',
    };
    return callbackfunc();
  });
}

UserPrefs.parseUserPrefsData = function(data) {
  UserPrefs.api.load_status = 'ok';
  UserPrefs.api.autopass = data.autopass;
  return true;
}

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by UserPrefs.js.
// Each function should start by populating UserPrefs.page and
// UserPrefs.form and end by invoking UserPrefs.layoutPage();

UserPrefs.actionFailed = function() {

  // Create empty page and undefined form objects to be filled later
  UserPrefs.page = $('<div>');
  UserPrefs.form = null;

  // No text because page data acquisition failed - Env.message
  // will tell the user what happened

  // Lay out the page
  UserPrefs.layoutPage();
}

UserPrefs.actionSetPrefs = function() {

  // Create empty page and undefined form objects to be filled later
  UserPrefs.page = $('<div>');
  UserPrefs.form = null;

  var prefsdiv = $('<div>');
  prefsdiv.append($('<div>', {
                     'class': 'title2',
                     'text': 'Change player details'
                    }));
  var prefsform = $('<form>', {
                      'id': 'userprefs_action_form',
                      'action': "javascript:void(0);"
                    });

  // Table of user preferences
  var prefstable = $('<table>', {'id': 'userprefs_set_table' });

  var entries = {
    'autopass': {
      'text': 'Automatically pass when you have no valid attack',
      'type': 'checkbox',
      'checked': UserPrefs.api.autopass,
    }
  }

  $.each(entries, function(entrykey, entryinfo) {
    var entryrow = $('<tr>');
    entryrow.append($('<td>', { 'text': entryinfo['text'] + ':' }));
    entryinput = $('<td>');
    entryinput.append($('<input>', {
                          'type': entryinfo['type'],
                          'name': entrykey,
                          'id': 'userprefs_' + entrykey,
                          'checked': entryinfo['checked'],
                        }));
    entryrow.append(entryinput);
    prefstable.append(entryrow);
  });
  prefsform.append(prefstable);

  // Form submission button
  prefsform.append($('<br>'));
  prefsform.append($('<button>', {
                       'id': 'userprefs_action_button',
                       'text': 'Save preferences'
                     }));
  prefsdiv.append(prefsform);

  UserPrefs.page.append(prefsdiv);

  // Function to invoke on button click
  UserPrefs.form = UserPrefs.formSetPrefs;

  // Lay out the page
  UserPrefs.layoutPage();
}

////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

UserPrefs.formSetPrefs = function() {
  var autopass = $('#userprefs_autopass').prop('checked');

  $.post(Env.api_location,
         { type: 'savePlayerInfo', autopass: autopass },
         function(rs) {
           if ('ok' == rs.status) {
             Env.message = {
               'type': 'success',
               'text': 'User details set successfully.'
             };
           } else {
             Env.message = {
               'type': 'error',
               'text': rs.message
             };
           }
           UserPrefs.showUserPrefsPage();
         }
  ).fail(function() {
           Env.message = {
             'type': 'error',
             'text': 'Internal error when calling formSetPrefs.'
           };
           UserPrefs.showUserPrefsPage();
         });
}
