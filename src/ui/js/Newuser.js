// namespace for this "module"
var Newuser = {};

// Valid username match
Newuser.VALID_USERNAME_REGEX = /^[A-Za-z0-9_]+$/;

// Valid email match
Newuser.VALID_EMAIL_REGEX = /^[A-Za-z0-9_+-]+@[A-Za-z0-9\.-]+$/;

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
  if ($('#newuser_page').length === 0) {
    $('body').append($('<div>', {'id': 'newuser_page', }));
  }

  if (Newuser.justCreatedAccount === true) {
    // Don't re-display the form if they've already created an account
    Newuser.page = $('<div>');
    Newuser.layoutPage();
  } else if (Login.logged_in === true) {
    // Don't allow logged-in users to create new accounts
    Newuser.actionLoggedIn();
  } else {
    Newuser.actionCreateUser();
  }
};

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
};

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
};

Newuser.actionCreateUser = function() {

  // Create empty page and undefined form objects to be filled later
  Newuser.page = $('<div>');
  Newuser.form = null;

  var creatediv = $('<div>');
  creatediv.append($('<div>', {
    'class': 'title2',
    'text': 'Create a new user account',
  }));
  var warningpar = $('<p>');
  warningpar.append($('<i>', {
    'text': 'Warning: reusing passwords between websites is risky ' +
            'in general, and you should definitely avoid it here.  We\'re ' +
            'a free game site, and we\'re in alpha release.  Please do NOT ' +
            'reuse a valuable account password for your Button Men account.'
  }));
  creatediv.append(warningpar);
  var createform = $('<form>', {
    'id': 'newuser_action_form',
    'action': 'javascript:void(0);',
  });

  // Table of user creation options
  var createtable = $('<table>', {'id': 'newuser_create_table', });

  var entries = {
    'username': {
      'text': 'Username',
      'type': 'text',
      'maxlength': 25,
    },
    'password': {
      'text': 'Password',
      'type': 'password',
    },
    'password_confirm': {
      'text': 'Password (again)',
      'type': 'password',
    },
    'email': {
      'text': 'E-mail address',
      'type': 'text',
      'maxlength': 254,
    },
    'email_confirm': {
      'text': 'E-mail address (again)',
      'type': 'text',
      'maxlength': 254,
    },
  };

  $.each(entries, function(entryid, entryinfo) {
    var entryrow = $('<tr>');
    entryrow.append($('<td>', {
      'class': 'left',
      'text': entryinfo.text + ':',
    }));
    var entryinput = $('<td>');
    entryinput.append($('<input>', {
      'type': entryinfo.type,
      'name': entryid,
      'id': 'newuser_' + entryid,
      'maxlength': entryinfo.maxlength,
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
};


////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

Newuser.formCreateUser = function() {
  var username = $('#newuser_username').val();
  var password = $('#newuser_password').val();
  var password_confirm = $('#newuser_password_confirm').val();
  var email = $('#newuser_email').val();
  var email_confirm = $('#newuser_email_confirm').val();

  if (username.length === 0) {
    Env.message = {
      'type': 'error',
      'text': 'You need to set a username',
    };
    Newuser.showNewuserPage();

  } else if (!(username.match(Newuser.VALID_USERNAME_REGEX))) {
    Env.message = {
      'type': 'error',
      'text': 'Usernames may only contain letters, numbers, and underscores',
    };
    Newuser.showNewuserPage();

  } else if (password.length === 0) {
    Env.message = {
      'type': 'error',
      'text': 'You need to set a password',
    };
    Newuser.showNewuserPage();
  } else if (password != password_confirm) {
    Env.message = {
      'type': 'error',
      'text': 'Passwords do not match',
    };
    Newuser.showNewuserPage();
  } else if (email.length === 0) {
    Env.message = {
      'type': 'error',
      'text': 'You need to provide an e-mail address',
    };
    Newuser.showNewuserPage();
  } else if (email != email_confirm) {
    Env.message = {
      'type': 'error',
      'text': 'E-mail addresses do not match',
    };
    Newuser.showNewuserPage();
  } else {
    Api.apiFormPost(
      {
        type: 'createUser',
        username: username,
        password: password,
        email: email,
      },
      { 'ok':
        {
          'type': 'function',
          'msgfunc': Newuser.setCreateUserSuccessMessage,
        },
        'notok': { 'type': 'server', },
      },
      'newuser_action_button',
      Newuser.showNewuserPage,
      Newuser.showNewuserPage
    );
  }
};

Newuser.setCreateUserSuccessMessage = function(message) {
  Newuser.justCreatedAccount = true;
  Env.message = {
    'type': 'success',
    'text': message,
  };
};

////////////////////////////////////////////////////////////////////////
// These functions add pieces of HTML to Newuser.page

Newuser.addLoggedInPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': 'Can\'t create a user because you are already logged in',
  }));
  errorDiv.append($('<a>', {
    'href': 'index.html',
    'text': 'Go back to the homepage and beat some people up',
  }));
  Newuser.page.append(errorDiv);
};

////////////////////////////////////////////////////////////////////////
// These functions generate and return pieces of HTML

