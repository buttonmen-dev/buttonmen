// namespace for this "module"
var ForgotPassword = {};

ForgotPassword.bodyDivId = 'forgot_password_page';

ForgotPassword.showLoggedInPage = function() {
  // Don't allow logged-in users to use this page
  ForgotPassword.actionLoggedIn();
};

ForgotPassword.showLoggedOutPage = function() {
  if (ForgotPassword.justRequestedReset === true) {
    // Don't re-display the form if they've already created an account
    ForgotPassword.page = $('<div>');
    ForgotPassword.arrangePage();
  } else {
    ForgotPassword.actionRequestReset();
  }
};

// Actually lay out the page
ForgotPassword.arrangePage = function() {
  Login.arrangePage(ForgotPassword.page);

  if (ForgotPassword.form) {
    $('#forgot_password_action_button').click(ForgotPassword.form);
  }
};

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by ForgotPassword.js.
// Each function should start by populating ForgotPassword.page and
// ForgotPassword.form and end by invoking ForgotPassword.arrangePage();

ForgotPassword.actionLoggedIn = function() {

  // Create empty page and undefined form objects to be filled later
  ForgotPassword.page = $('<div>');
  ForgotPassword.form = null;

  // Add the "logged in player" HTML contents
  ForgotPassword.addLoggedInPage();

  // Lay out the page
  ForgotPassword.arrangePage();
};

ForgotPassword.actionRequestReset = function() {

  // Create empty page and undefined form objects to be filled later
  ForgotPassword.page = $('<div>');
  ForgotPassword.form = null;

  var requestdiv = $('<div>');
  requestdiv.append($('<div>', {
    'class': 'title2',
    'text': 'Request password reset',
  }));
  requestdiv.append($('<p>', {
    'text': 'If your email address has been verified and you have forgotten ' +
            'your Button Men password, you can reset it.'
  }));
  requestdiv.append($('<p>', {
    'text': 'Enter your username, and we will send a password reset link to ' +
            'the e-mail address we have on file for your account.'
  }));
  var requestform = $('<form>', {
    'id': 'forgot_password_action_form',
    'action': 'javascript:void(0);',
  });

  // Table of password reset request options
  var requesttable = $('<table>', {'id': 'forgot_password_request_table', });

  var entries = {
    'username': {
      'text': 'Username',
      'type': 'text',
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
      'id': 'forgot_password_' + entryid,
      'maxlength': entryinfo.maxlength,
    }));
    entryrow.append(entryinput);
    requesttable.append(entryrow);
  });
  requestform.append(requesttable);

  // Form submission button
  requestform.append($('<br>'));
  requestform.append($('<button>', {
    'id': 'forgot_password_action_button',
    'text': 'Request password reset link',
  }));
  requestdiv.append(requestform);

  requestdiv.append($('<p>', {
    'text': 'If your email address was never successfully verified, ' +
            'your password cannot be reset automatically. Let us know ' +
            'at help@buttonweavers.com, and we\'ll sort this out for you.'
  }));

  ForgotPassword.page.append(requestdiv);

  // Function to invoke on button click
  ForgotPassword.form = ForgotPassword.formRequestReset;

  // Lay out the page
  ForgotPassword.arrangePage();
};


////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

ForgotPassword.formRequestReset = function() {
  var username = $('#forgot_password_username').val();

  if (username.length === 0) {
    Env.message = {
      'type': 'error',
      'text': 'You need to specify a username.',
    };
    ForgotPassword.showLoggedOutPage();

  } else {
    Api.apiFormPost(
      {
        type: 'forgotPassword',
        username: username,
      },
      { 'ok':
        {
          'type': 'function',
          'msgfunc': ForgotPassword.setRequestedResetSuccessMessage,
        },
        'notok': { 'type': 'server', },
      },
      '#forgot_password_action_button',
      ForgotPassword.showLoggedOutPage,
      ForgotPassword.showLoggedOutPage
    );
  }
};

ForgotPassword.setRequestedResetSuccessMessage = function(message) {
  ForgotPassword.justRequestedReset = true;
  Env.message = {
    'type': 'success',
    'text': message,
  };
};

////////////////////////////////////////////////////////////////////////
// These functions add pieces of HTML to ForgotPassword.page

ForgotPassword.addLoggedInPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': 'Can\'t request password reset because you are already logged in',
  }));
  errorDiv.append($('<a>', {
    'href': 'prefs.html',
    'text': 'Logged-in users can change their passwords ' +
            'on the Preferences page',
  }));
  ForgotPassword.page.append(errorDiv);
};

