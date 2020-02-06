// namespace for this "module"
var VerifyReset = {
  'activity': {},
};

VerifyReset.bodyDivId = 'verifyreset_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * VerifyReset.showLoggedInPage() is the landing function.  Always call
//   this first when logged in.  It calls VerifyReset.actionLoggedIn()
// * VerifyReset.showLoggedOutPage() is the other landing function.  Always call
//   this first when logged out.  It calls VerifyReset.actionCreateUser()
// * each VerifyReset.action<SomeAction>() function must set
//   VerifyReset.page and VerifyReset.form, then call
//   VerifyReset.arrangePage()
// * VerifyReset.arrangePage() calls Login.arrangePage()
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GENERIC FUNCTIONS: these do not depend on the action being taken

VerifyReset.showLoggedInPage = function() {
  // Don't allow logged-in users to reset passwords
  VerifyReset.actionLoggedIn();
};

VerifyReset.showLoggedOutPage = function() {
  // Get the URL parameters and error out early if they're missing
  VerifyReset.activity.playerId = Env.getParameterByName('id');
  VerifyReset.activity.playerKey = Env.getParameterByName('key');
  if ((VerifyReset.activity.playerId === null) ||
      (VerifyReset.activity.playerKey === null)) {
    Env.message = {
      'type': 'error',
      'text': 'Verification URL is missing expected parameters - ' +
              'be sure to copy the entire link from the e-mail you got',
    };
    VerifyReset.page = $('<div>');
    VerifyReset.arrangePage();
  } else if (VerifyReset.justResetPassword === true) {
    // Don't re-display the form if they've already reset their password
    VerifyReset.page = $('<div>');
    VerifyReset.arrangePage();
  } else {
    VerifyReset.actionResetPassword();
  }
};

// Actually lay out the page
VerifyReset.arrangePage = function() {
  Login.arrangePage(VerifyReset.page);

  if (VerifyReset.form) {
    $('#verifyreset_action_button').click(VerifyReset.form);
  }
};

////////////////////////////////////////////////////////////////////////
// This section contains one page for each type of next action used for
// flow through the page being laid out by VerifyReset.js.
// Each function should start by populating VerifyReset.page and
// VerifyReset.form and end by invoking VerifyReset.arrangePage();

VerifyReset.actionLoggedIn = function() {

  // Create empty page and undefined form objects to be filled later
  VerifyReset.page = $('<div>');
  VerifyReset.form = null;

  // Add the "logged in player" HTML contents
  VerifyReset.addLoggedInPage();

  // Lay out the page
  VerifyReset.arrangePage();
};

VerifyReset.actionResetPassword = function() {

  // Create empty page and undefined form objects to be filled later
  VerifyReset.page = $('<div>');
  VerifyReset.form = null;

  var resetdiv = $('<div>');
  resetdiv.append($('<div>', {
    'class': 'title2',
    'text': 'Reset account password',
  }));
  var warningpar = $('<p>');
  warningpar.append($('<i>', {
    'text': 'Warning: please do NOT ' +
            'use a valuable account password for your Button Men account.'
  }));
  resetdiv.append(warningpar);
  var resetform = $('<form>', {
    'id': 'verifyreset_action_form',
    'action': 'javascript:void(0);',
  });

  // Table of user creation options
  var resettable = $('<table>', {'id': 'verifyreset_password_table', });

  var entries = {
    'password': {
      'text': 'New password',
      'type': 'password',
    },
    'password_confirm': {
      'text': 'New password (again)',
      'type': 'password',
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
      'id': 'verifyreset_' + entryid,
    }));
    entryrow.append(entryinput);
    resettable.append(entryrow);
  });
  resetform.append(resettable);

  // Form submission button
  resetform.append($('<br>'));
  resetform.append($('<button>', {
    'id': 'verifyreset_action_button',
    'text': 'Reset password!',
  }));
  resetdiv.append(resetform);

  VerifyReset.page.append(resetdiv);

  // Function to invoke on button click
  VerifyReset.form = VerifyReset.formResetPassword;

  // Lay out the page
  VerifyReset.arrangePage();
};


////////////////////////////////////////////////////////////////////////
// These functions define form submissions, one per action type

VerifyReset.formResetPassword = function() {
  var password = $('#verifyreset_password').val();
  var password_confirm = $('#verifyreset_password_confirm').val();

  if (password.length === 0) {
    Env.message = {
      'type': 'error',
      'text': 'You need to set a password',
    };
    VerifyReset.showLoggedOutPage();
  } else if (password != password_confirm) {
    Env.message = {
      'type': 'error',
      'text': 'Passwords do not match',
    };
    VerifyReset.showLoggedOutPage();
  } else {
    Api.apiFormPost(
      {
        type: 'resetPassword',
        playerId: VerifyReset.activity.playerId,
        playerKey: VerifyReset.activity.playerKey,
        password: password,
      },
      { 'ok':
        {
          'type': 'function',
          'msgfunc': VerifyReset.setResetPasswordSuccessMessage,
        },
        'notok': { 'type': 'server', },
      },
      '#verifyreset_action_button',
      VerifyReset.showLoggedOutPage,
      VerifyReset.showLoggedOutPage
    );
  }
};

VerifyReset.setResetPasswordSuccessMessage = function(message) {
  VerifyReset.justResetPassword = true;
  Env.message = {
    'type': 'success',
    'text': message,
  };
};

////////////////////////////////////////////////////////////////////////
// These functions add pieces of HTML to VerifyReset.page

VerifyReset.addLoggedInPage = function() {
  var errorDiv = $('<div>');
  errorDiv.append($('<p>', {
    'text': 'Can\'t reset password because you are already logged in',
  }));
  errorDiv.append($('<a>', {
    'href': 'index.html',
    'text': 'Go back to the homepage and beat some people up',
  }));
  VerifyReset.page.append(errorDiv);
};

////////////////////////////////////////////////////////////////////////
// These functions generate and return pieces of HTML

