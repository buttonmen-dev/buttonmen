// namespace for this "module"
var Verify = {
};

Verify.showVerifyPage = function() {
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#verify_page').length === 0) {
    $('body').append($('<div>', {'id': 'verify_page', }));
  }

  // Find the verification parameters and submit them to the server,
  // then display the result
  Verify.getVerifyParams(Verify.showStatePage);
};

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
      'notok': { 'type': 'server', },
    },
    null,
    callbackfunc,
    callbackfunc
  );
};

Verify.showStatePage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();
};

Verify.setVerifyUserSuccessMessage = function(message) {
  var indexLink = $('<a>', {
    'href': 'index.html',
    'text': 'Go back to the homepage, login, and start beating people up',
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
