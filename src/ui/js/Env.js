// namespace for this "module"
if (!(Env)) {
  var Env = {};
}

// Colors for status messages
Env.messageTypeColors = {
  'none': 'black',
  'error': 'red',
  'success': 'green',
};

// Courtesy of stackoverflow:
// http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values/5158301#5158301
Env.getParameterByName = function(name) {
    var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
    return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
}

// Make sure that the page body contains a div for displaying status
// messages
Env.setupEnvStub = function() {
  if ($('#env_message').length == 0) {
    $('body').append($('<div>', {'id': 'env_message', }));
  }
}

// Show a status message which might have been set by any module
Env.showStatusMessage = function() {
  $('#env_message').empty();
  if (Env.message) {
    var msgobj = $('<p>');
    msgobj.append($('<font>',
                  {
                    'color': Env.messageTypeColors[Env.message.type],
                    'text': Env.message.text,
                  }));
    if ('obj' in Env.message) {
      msgobj.append(Env.message.obj);
    }
    $('#env_message').append(msgobj);
  }
}
