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

  // Also place the UI root in a reasonable location
  Env.ui_root = '../ui/';

  // We also want to mock the window and history objects in unit tests
  Env.window = { location: {} };
  Env.history = {
    pushState: function(state, title, url) {
      Env.history.state = state;
      // We can make these more sophisticated later if we need to
      Env.window.location.href = url;
      Env.window.location.search = url;
      Env.window.location.hash = url;
    },
    replaceState: function(state, title, url) {
      Env.history.pushState(state, title, url);
    }
  };
} else {
  Env.api_location = '../api/responder';
  Env.window = window;
  Env.history = history;

  // UI portion of the location at which the user is accessing the site
  Env.ui_root = Env.window.location.pathname.replace(/\/ui\/.*/, '/ui/');
}

// Courtesy of stackoverflow: http://stackoverflow.com/a/5158301
Env.getParameterByName = function(name) {
  var match = new RegExp('[?&]' + name + '=([^&]*)').exec(
    Env.window.location.search
  );
  if (match) {
    return decodeURIComponent(match[1].replace(/\+/g, ' '));
  }
  // We want to check both the query string *and* the hashbang
  match = new RegExp('[!&]' + name + '=([^&]*)').exec(
    Env.window.location.hash
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

  if (!format) {
    format = 'datetime';
  }

  // Most pages don't use moment, so we don't always load it in the HTML
  if (typeof moment === 'undefined') {
    $.ajaxSetup({ async: false, });
    $.getScript('js/extern/moment.js');
    $.ajaxSetup({ async: true, });
  }

  var datetime = moment.unix(timestamp);
  if (!datetime.isValid()) {
    return null;
  }

  if (format == 'date') {
    return datetime.format('YYYY-MM-DD');
  } else if (format == 'time') {
    return datetime.format('HH:mm:ss');
  } else if (format == 'datetime') {
    return datetime.format('YYYY-MM-DD HH:mm:ss');
  } else {
    return datetime.format(format);
  }
};

// Parses a date or time string into a Unix-style timestamp.
// format parameter options:
//   'date' for '2014-03-23'
//   'time' for '17:54:32'
//   'datetime' for '2014-03-23 17:54:32'
// strict is a bool indicating whether the specified format should be strictly
// required.
Env.parseDateTime = function(input, format) {
  if (!input) {
    return null;
  }

  if (!format) {
    format = 'datetime';
  }

  // Most pages don't use moment, so we don't always load it in the HTML
  if (typeof moment === 'undefined') {
    $.ajaxSetup({ async: false, });
    $.getScript('js/extern/moment.js');
    $.ajaxSetup({ async: true, });
  }

  var datetime;
  if (format == 'date') {
    datetime = moment(input, 'YYYY-MM-DD', true);
  } else if (format == 'time') {
    datetime = moment(input, ' HH:mm:ss', true);
  } else if (format == 'datetime') {
    datetime = moment(input, 'YYYY-MM-DD HH:mm:ss', true);
  } else {
    datetime = moment(input, format, true);
  }
  if (!datetime.isValid()) {
    return null;
  }

  return datetime.unix();
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
