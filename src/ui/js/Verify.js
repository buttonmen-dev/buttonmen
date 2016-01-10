// namespace for this "module"
var Verify = {
};

Verify.bodyDivId = 'verify_page';

Verify.showLoggedInPage = function() {
  // Find the verification parameters and submit them to the server,
  // then display the result
  Verify.getVerifyParams(Verify.showStatePage);
};

// Verify basically behaves roughly the same way regardless of whether or not
// you're logged in
Verify.showLoggedOutPage = Verify.showLoggedInPage;

Verify.getVerifyParams = function(callbackfunc) {
  var playerId = Env.getParameterByName('id');
  var playerKey = Env.getParameterByName('key');
  if ((playerId === null) || (playerKey === null)) {
    Env.message = {
      'type': 'error',
      'text': 'Verification URL is missing expected parameters - ' +
              'be sure to copy the entire link from the e-mail you got',
    };
    return callbackfunc();
  }

  // now submit the verification parameters to the server
  Api.apiFormPost(
    {
      type: 'verifyUser',
      playerId: playerId,
      playerKey: playerKey,
    },
    { 'ok':
      {
        'type': 'function',
        'msgfunc': Verify.setVerifyUserSuccessMessage,
      },
      'notok': {
        'type': 'function',
        'msgfunc': Verify.setVerifyUserFailureMessage,
      },
    },
    null,
    callbackfunc,
    callbackfunc
  );
};

Verify.showStatePage = function() {
  Verify.page = $('<div>');

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  Login.arrangePage(Verify.page);
};

Verify.setVerifyUserSuccessMessage = function(message) {
  var indexLink = $('<a>', {
    'href': 'index.html',
    'text': 'Go back to the homepage, log in, and start beating people up',
  });
  var userPar = $('<p>', {'text': message + ' ', });
  userPar.append($('<br>'));
  userPar.append(indexLink);
  Env.message = {
    'type': 'success',
    'text': '',
    'obj': userPar,
  };
};

// It's likely that, after verifying their accounts, users will
// login while still on the verify.html page.  If that happens,
// give a reasonable message instead of the server's error.  Otherwise,
// pass the server's error along
Verify.setVerifyUserFailureMessage = function(message) {
  if (Login.logged_in &&
      message.match(/User with ID \d+ is not waiting to be verified/)) {
    var indexLink = $('<a>', {
      'href': 'index.html',
      'text': 'Go back to the homepage and start beating people up',
    });
    var userPar = $('<p>', {'text': 'Your account has been verified.' + ' ', });
    userPar.append($('<br>'));
    userPar.append(indexLink);
    Env.message = {
      'type': 'none',
      'text': '',
      'obj': userPar,
    };
  } else {
    Env.message = {
      'type': 'error',
      'text': message,
    };
  }
};
