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

// location of backend API - depends whether we're testing
if ('unit_test' in Env) {
  if (Env.unit_test == 'phantom') {
    // PhantomJS unit test uses a separate local server solely to
    // run dummy_responder.php, and does not bother with a redirect
    // from the bare responder name
    Env.api_location = 'http://localhost:8082/dummy_responder.php';
  } else {
    Env.api_location = '../api/dummy_responder';
  }
  // We also want to mock the window object in unit tests
  Env.window = { location: {} };
} else {
  Env.api_location = '../api/responder';
  Env.window = window;
}

// Courtesy of stackoverflow: http://stackoverflow.com/a/5158301
Env.getParameterByName = function(name) {
  var match = new RegExp('[?&]' + name + '=([^&]*)').exec(
    window.location.search
  );
  return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
};

// Make sure that the page body contains a div for displaying status
// messages
Env.setupEnvStub = function() {
  if ($('#env_message').length === 0) {
    $('body').append($('<div>', {'id': 'env_message', }));
  }
};

// Show a status message which might have been set by any module
Env.showStatusMessage = function() {
  $('#env_message').empty();
  if (Env.message) {
    var msgobj = $('<p>');
    msgobj.append(
      $('<font>', {
        'color': Env.messageTypeColors[Env.message.type],
        'text': Env.message.text,
      }));
    if ('obj' in Env.message) {
      msgobj.append(Env.message.obj);
    }
    $('#env_message').append(msgobj);
  }
};

// Formats a Unix-style timestamp as a human-readable date and/or time.
// format parameter options:
//   'date' for '2014-03-23'
//   'time' for '17:54:32'
//   'datetime' for '2014-03-23 17:54:32'
Env.formatTimestamp = function(timestamp, format) {
  if (!timestamp) {
    return '';
  }
  if (format === null || format === undefined) {
    format = 'datetime';
  }

  var dateTime = new Date(timestamp * 1000);

  var year = dateTime.getFullYear();
  var month = Env.padLeft(dateTime.getMonth() + 1, '0', 2);
  var day = Env.padLeft(dateTime.getDate(), '0', 2);
  var hours = Env.padLeft(dateTime.getHours(), '0', 2);
  var minutes = Env.padLeft(dateTime.getMinutes(), '0', 2);
  var seconds = Env.padLeft(dateTime.getSeconds(), '0', 2);

  var formattedDate = year + '-' + month + '-' + day;
  var formattedTime = hours + ':' + minutes + ':' + seconds;

  if (format == 'date') {
    return formattedDate;
  }
  if (format == 'time') {
    return formattedTime;
  }
  if (format == 'datetime') {
    return formattedDate + ' ' + formattedTime;
  }
};

// Pads the input string with copies of the paddingCharacter until it's at
// least minLength long.
Env.padLeft = function(input, paddingCharacter, minLength) {
  var padding = '';
  for (var i = 0; i < minLength; i++) {
    padding = padding + paddingCharacter;
  }

  var output = padding + input;
  return output.slice(minLength * -1);
};

Env.setCookieNoImages = function(value) {
  // Set the cookie to expire ten years from now (expect bug reports in 2024)
  $.cookie(Login.player + '_noImages', value, { expires: 3650, });
};
Env.getCookieNoImages = function() {
  // Cookies are stored as strings, but we want to return a bool
  return ($.cookie(Login.player + '_noImages') == 'true');
};

Env.setCookieCompactMode = function(value) {
  // Set the cookie to expire ten years from now (expect bug reports in 2024)
  $.cookie(Login.player + '_compactMode', value, { expires: 3650, });
};
Env.getCookieCompactMode = function() {
  // Cookies are stored as strings, but we want to return a bool
  return ($.cookie(Login.player + '_compactMode') == 'true');
};
