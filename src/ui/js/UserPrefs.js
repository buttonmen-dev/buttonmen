// namespace for this "module"
var UserPrefs = {};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * UserPrefs.showUserPrefsPage() is the landing function.  Always call
//   this first.  It calls one of a couple of functions,
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
    UserPrefs.actionShowPrefs();
//  } else {
//    UserPrefs.actionLogin();
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

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by UserPrefs.js.
// Each function should start by populating UserPrefs.page and UserPrefs.form
// and end by invoking UserPrefs.layoutPage();

UserPrefs.actionShowPrefs = function() {

  // Create empty page and undefined form objects to be filled later
  UserPrefs.page = $('<div>');
  UserPrefs.form = null;

  var creatediv = $('<div>');
  creatediv.append($('<div>', {
                      'class': 'title2',
                      'text': 'Change player details'
                    }));
  var createform = $('<form>', {
                       'id': 'userprefs_action_form',
                       'action': "javascript:void(0);"
                     });

  // Table of user preferences
  var createtable = $('<table>', {'id': 'userprefs_create_table' });

  var entries = {
    'autopass': {
      'text': 'Allow autopass',
      'type': 'checkbox'
    }
  }

  $.each(entries, function(entryid, entryinfo) {
    var entryrow = $('<tr>');
    entryrow.append($('<td>', { 'text': entryinfo['text'] + ':' }));
    entryinput = $('<td>');
    entryinput.append($('<input>', {
                          'type': entryinfo['type'],
                          'name': entryid,
                          'id': 'userprefs_' + entryid
                        }));
    entryrow.append(entryinput);
    createtable.append(entryrow);
  });
  createform.append(createtable);

  // Form submission button
  createform.append($('<br>'));
  createform.append($('<button>', {
                        'id': 'userprefs_action_button',
                        'text': 'Save'
                      }));
  creatediv.append(createform);

  UserPrefs.page.append(creatediv);

  // Function to invoke on button click
  UserPrefs.form = UserPrefs.formSetPrefs;

  // Lay out the page
  UserPrefs.layoutPage();
}


////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

UserPrefs.formSetPrefs = function() {
  var autopass = ('true' == $('#userprefs_autopass').prop('checked'));

  $.post(Env.api_location, {
             type: 'savePlayerInfo',
             autopass: autopass
         },
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
//
//////////////////////////////////////////////////////////////////////////
//// These functions add pieces of HTML to UserPrefs.page
//
//
//
//////////////////////////////////////////////////////////////////////////
//// These functions generate and return pieces of HTML
//
