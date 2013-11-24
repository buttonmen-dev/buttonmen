// namespace for this "module"
var Newuser = {};

// Valid username match
Newuser.VALID_USERNAME_REGEX = /^[A-Za-z0-9_]+$/;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Newuser.showNewuserPage() is the landing function.  Always call
//   this first.  It calls one of a couple of functions,
//   Newuser.action<SomeAction>()
// * each Newuser.action<SomeAction>() function must set Newuser.page and
//   Newuser.form, then call Newuser.layoutPage()
// * Newuser.layoutPage() sets the contents of <div id="newuser_page">
//   on the live page
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

Newuser.showNewuserPage = function() {

  // Setup necessary elements for displaying status messages
  $.getScript('js/Env.js');
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#newuser_page').length == 0) {
    $('body').append($('<div>', {'id': 'newuser_page', }));
  }

  // Don't allow logged-in users to create new accounts
  if (Login.logged_in == true) {
    Newuser.actionLoggedIn();
  } else {
    Newuser.actionCreateUser();
  }
}

// Actually lay out the page
Newuser.layoutPage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#newuser_page').empty();
  $('#newuser_page').append(Newuser.page);

  if (Newuser.form) {
    $('#newuser_action_button').click(Newuser.form);
  }
}

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by Newuser.js.
// Each function should start by populating Newuser.page and Newuser.form
// ane end by invoking Newuser.layoutPage();

Newuser.actionLoggedIn = function() {

  // Create empty page and undefined form objects to be filled later
  Newuser.page = $('<div>');
  Newuser.form = null;

  // Add the "logged in player" HTML contents
  Newuser.addLoggedInPage();

  // Lay out the page
  Newuser.layoutPage();
}

Newuser.actionCreateUser = function() {

  // Create empty page and undefined form objects to be filled later
  Newuser.page = $('<div>');
  Newuser.form = null;

  var creatediv = $('<div>');
  creatediv.append($('<div>', {
                      'class': 'title2',
                      'text': 'Create a new user account',
                    }));
  var createform = $('<form>', {
                       'id': 'newuser_action_form',
                       'action': "javascript:void(0);",
                     });

  // Table of user creation options
  var createtable = $('<table>', {'id': 'newuser_create_table', });

  var entries = {
    'username': {
      'text': 'Username',
      'type': 'text',
    },
    'password': {
      'text': 'Password',
      'type': 'password',
    },
  }

  $.each(entries, function(entryid, entryinfo) {
    var entryrow = $('<tr>');
    entryrow.append($('<td>', { 'text': entryinfo['text'] + ':', }));
    entryinput = $('<td>');
    entryinput.append($('<input>', {
                          'type': entryinfo['type'],
                          'name': entryid,
                          'id': 'newuser_' + entryid,
                        }));
    entryrow.append(entryinput);
    createtable.append(entryrow);
  });
  createform.append(createtable);

  // Form submission button
  createform.append($('<br>'));
  createform.append($('<button>', {
                        'id': 'newuser_action_button',
                        'text': 'Create user!',
                      }));
  creatediv.append(createform);

  Newuser.page.append(creatediv);

  // Function to invoke on button click
  Newuser.form = Newuser.formCreateUser;

  // Lay out the page
  Newuser.layoutPage();
}


////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

Newuser.formCreateUser = function() {
  var username = $('#newuser_username').val();

  if (!(username.match(Newuser.VALID_USERNAME_REGEX))) {
    Env.message = {
      'type': 'error',
      'text': 'Usernames may only contain letters, numbers, and underscores',
    };
    Newuser.showNewuserPage();

  } else {
    var password = $('#newuser_password').val();
    if (password.length == 0) {
      Env.message = {
        'type': 'error',
        'text': 'Password may not be null',
      };
      Newuser.showNewuserPage();
    } else {
      $.post('../api/responder.php', {
               type: 'createUser',
               username: username,
               password: password,
             },
             function(rs) {
               if ('ok' == rs.status) {
                 var userName = rs.data.userName;
                 var indexLink = $('<a>', {
                   'href': 'index.html',
                   'text': 'Go back to the homepage, login, and start ' + 
                           'beating people up',
                 });
               var userPar = $('<p>',
                                {'text': rs.message + ' ', });
               userPar.append($('<br>'));
               userPar.append(indexLink);
               Env.message = {
                 'type': 'success',
                 'text': '',
                 'obj': userPar,
               };
               Newuser.showNewuserPage();
             } else {
               Env.message = {
                 'type': 'error',
                 'text': rs.message,
               };
               Newuser.showNewuserPage();
             }
           }
    ).fail(function() {
             Env.message = { 
               'type': 'error',
               'text': 'Internal error when calling createUser',
             };
             Newuser.showNewuserPage();
           });
    }
  }
}

////////////////////////////////////////////////////////////////////////
// These functions add pieces of HTML to Newuser.page

Newuser.addLoggedInPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': "Can't create a user because you are already logged in",
  }))
  errorDiv.append($('<a>', {
    'href': 'index.html',
    'text': 'Go back to the homepage and beat some people up',
  }));
  Newuser.page.append(errorDiv);
}

////////////////////////////////////////////////////////////////////////
// These functions generate and return pieces of HTML

