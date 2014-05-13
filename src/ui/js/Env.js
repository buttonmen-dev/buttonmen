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
  if (timestamp === undefined || timestamp === null) {
    return '';
  }

  if (format === null || format === undefined) {
    format = 'datetime';
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
Env.parseDateTime = function(input, format, strict) {
  if (input === undefined || input === null || input === '') {
    return null;
  }

  if (format === null || format === undefined) {
    format = 'datetime';
    strict = false;
  } else if (strict === null || strict === undefined) {
    strict = true;
  }

  var datetime;
  if (format == 'date') {
    datetime = moment(input, 'YYYY-MM-DD', strict);
  } else if (format == 'time') {
    datetime = moment(input, ' HH:mm:ss', strict);
  } else if (format == 'datetime') {
    datetime = moment(input, 'YYYY-MM-DD HH:mm:ss', strict);
  } else {
    datetime = moment(input, format, strict);
  }
  if (!datetime.isValid()) {
    return null;
  }

  return datetime.unix();
};