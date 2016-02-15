// namespace for this "module"
if (!(Env)) {
  var Env = {};
}

// Keycodes for use when handling keyboard events
Env.KEYCODE_TAB = 9;
Env.KEYCODE_RETURN = 13;
Env.KEYCODE_SPACEBAR = 32;

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
  Env.window = {
    location: {},
    confirm: function() {return true;},
  };
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

Env.removeParameterByName = function(name) {
  // If the query string is already empty, there's obviously nothing to do
  if (!Env.window.location.search) { return; }

  var newQueryString = '?';
  var parameterChunks = Env.window.location.search.split(/[?&]/);
  $.each(parameterChunks, function(index, chunk) {
    if (chunk === '') { return; }
    if (chunk.split('=')[0] == name) { return; }
    newQueryString += chunk + '&';
  });

  // Trim off the trailing ? or &
  newQueryString = newQueryString.replace(/[?&]$/, '');
  var newUrl =
    Env.window.location.origin + Env.window.location.pathname +
    newQueryString + Env.window.location.hash;
  // Replace the current URL without messing up the browser history
  Env.history.replaceState(null, $(document).find('title').text(), newUrl);
};

// Make sure that the page body contains a div for displaying status
// messages
Env.setupEnvStub = function() {
  if ($('#env_message').length === 0) {
    $('#c_body').append($('<div>', {'id': 'env_message', }));
  }
};

// Show a status message which might have been set by any module
Env.showStatusMessage = function() {
  $('#env_message').empty();
  if (Env.message) {
    // Make sure we're at the top of the page so the user will see the message
    $('html, body').animate({ scrollTop: 0 }, 200);
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

// utility function to add a click handler and also handle keydown
// effects
Env.addClickKeyboardHandlers = function(
     element, clickHandlerCallback, spaceHandlerCallback,
     returnHandlerCallback) {

  // invoke the click handler on mouse click
  if (clickHandlerCallback) {
    element.click(clickHandlerCallback);
  }

  // If the caller specified either "spacebar" or "return" keydown
  // behavior, install a keydown handler.
  if (spaceHandlerCallback || returnHandlerCallback) {
    element.keydown(function(eventData) {
      var doSwallowKeypress = false;

      // if a space handler was specified, invoke it on spacebar
      if (spaceHandlerCallback && eventData.which == Env.KEYCODE_SPACEBAR) {
        spaceHandlerCallback.call(element, eventData);
        // actively swallow the space event to stop scrolling
        doSwallowKeypress = true;
      }

      // if a return handler was specified, invoke it on return
      if (returnHandlerCallback && eventData.which == Env.KEYCODE_RETURN) {
        returnHandlerCallback.call(element, eventData);
      }

      if (doSwallowKeypress) {
        return false;
      }
    });
  }
};

// Takes text that was entered by a user and turns it into HTML that's ready to
// be displayed on a page.
Env.prepareRawTextForDisplay = function(rawText) {
  // First, we write it into an HTML element as text; this way, jQuery treat
  // any special characters like < as things that need to be escaped (into
  // things like &lt;). Then we can pull the clean HTML version back out again.
  var tempDiv = $('<div>', { 'text': rawText });
  var html = tempDiv.html();

  // Next, we deal with any whitespace in the string that might not be
  // displayed correctly in HTML, like newlines and indentation.

  // HTML-ify initial spaces, to preserve indentation
  html = html.replace(/^ /, '&nbsp;');
  // Likewise for spaces at the start of each line
  html = html.replace(/\n /, '\n&nbsp;');
  // Preserve strings of multiple spaces
  html = html.replace(/  /g, '&nbsp;&nbsp;');
  // HTML-ify line breaks to preserve newlines
  html = html.replace(/\n/g, '<br />');

  // Parse markup like '[game="123"]' and '[b] blah [/b]' into HTML
  html = Env.applyBbCodeToHtml(html);

  return html;
};

Env.applyBbCodeToHtml = function(htmlToParse) {
  // This is all rather more complicated than one might expect, but any attempt
  // to parse BB code using simple regular expressions rather than tokenization
  // is in the same family as parsing HTML with regular expressions, which
  // summons Zalgo.
  // (See: http://stackoverflow.com/
  //   questions/1732348/regex-match-open-tags-except-xhtml-self-contained-tags)

  var replacements = {
    'b': {
      'openingHtml': '<span class="chatBold">',
      'closingHtml': '</span>',
    },
    'i': {
      'openingHtml': '<span class="chatItalic">',
      'closingHtml': '</span>',
    },
    'u': {
      'openingHtml': '<span class="chatUnderlined">',
      'closingHtml': '</span>',
    },
    's': {
      'openingHtml': '<span class="chatStruckthrough">',
      'closingHtml': '</span>',
    },
    'code': {
      'openingHtml': '<span class="chatCode">',
      'closingHtml': '</span>',
    },
    'spoiler': {
      'openingHtml': '<span class="chatSpoiler">',
      'closingHtml': '</span>',
    },
    'quote': {
      'openingHtml':
          '<div class="chatQuote"><div class="chatQuotee">Quote:</div>' +
            '<span class="chatQuoteBody">',
      'alternateOpeningHtml':
          '<div class="chatQuote"><div class="chatQuotee">### said:</div>' +
            '<span class="chatQuoteBody">',
      'closingHtml': '</span></div>',
    },
    'game': {
      'isAtomic': true,
      'isLink': true,
      'openingHtml': '<a class="chatGameLink" href="game.html?game=###">Game ',
      'closingHtml': '</a>',
      'escapeParameter': true,
    },
    'player': {
      'isAtomic': true,
      'isLink': true,
      'openingHtml':
          '<a class="chatPlayerLink" href="profile.html?player=###">',
      'closingHtml': '</a>',
      'escapeParameter': true,
    },
    'button': {
      'isAtomic': true,
      'isLink': true,
      'openingHtml':
          '<a class="chatButtonLink" href="buttons.html?button=###">',
      'closingHtml': '</a>',
      'escapeParameter': true,
    },
    'set': {
      'isAtomic': true,
      'isLink': true,
      'openingHtml':
          '<a class="chatButtonSetLink" href="buttons.html?set=###">',
      'closingHtml': '</a>',
      'escapeParameter': true,
    },
    'wiki': {
      'isAtomic': true,
      'isLink': true,
      'openingHtml':
          '<a class="chatWikiLink" ' +
          'href="http://buttonweavers.wikia.com/wiki/###">Wiki: ',
      'closingHtml': '</a>',
      'escapeParameter': true,
    },
    'issue': {
      'isAtomic': true,
      'isLink': true,
      'openingHtml':
          '<a class="chatIssueLink" ' +
          'href="https://github.com/buttonmen-dev/buttonmen/issues/###">' +
          'Issue ',
      'closingHtml': '</a>',
      'escapeParameter': true,
    },
    '[': {
      'isAtomic': true,
      'openingHtml': '[',
    },
  };

  var outputHtml = '';
  var tagStack = [ ];

  // We want to build a pattern that we can use to identify any single
  // BB code start tag
  var allStartTagsPattern = '';
  $.each(replacements, function(tagName) {
    if (allStartTagsPattern !== '') {
      allStartTagsPattern += '|';
    }
    // Matches, e.g., '[ b ]' or '[game = "123"]'
    // The (?:... part means that we want parentheses around the whole
    // thing (so we we can OR it together with other ones), but we don't
    // want to capture the value of the whole thing as a group
    allStartTagsPattern +=
      '(?:\\[(' + Env.escapeRegexp(tagName) + ')(?:=([^\\]]*?))?])';
  });

  var tagName;

  while (htmlToParse) {
    var currentPattern = allStartTagsPattern;
    if (tagStack.length !== 0) {
      // The tag that was most recently opened
      tagName = tagStack[tagStack.length - 1];
      // Matches '[/i]' et al.
      // (so that we can spot the end of the current tag as well)
      currentPattern +=
        '|(?:\\[(/' + Env.escapeRegexp(tagName) + ')])';
    }
    // The first group should be non-greedy (hence the ?), and the last one
    // should be greedy, so that nested tags work right
    // (E.g., in '...blah[/quote] blah [/quote] blah', we want the first .*
    // to end at the first [/quote], not the second)
    currentPattern = '^(.*?)(?:' + currentPattern + ')(.*)$';
    // case-insensitive, multi-line
    var regExp = new RegExp(currentPattern, 'im');

    var match = htmlToParse.match(regExp);
    if (match) {
      var stuffBeforeTag = match[1];
      // javascript apparently believes that capture groups that don't
      // match anything are just important as those that do. So we need
      // to do some acrobatics to find the ones we actually care about.
      // (match[0] is the whole matched string; match[1] is the stuff before
      // the tag. So we start with match[2].)
      tagName = '';
      for (var i = 2; i < match.length; i++) {
        tagName = match[i];
        if (tagName) {
          break;
        }
      }
      tagName = tagName.toLowerCase();
      var tagParameter = match[i + 1] || '';
      var stuffAfterTag = match[match.length - 1];

      outputHtml += stuffBeforeTag;
      if (tagName.substring(0, 1) === '/') {
        // If we've found our closing tag, we can finish the current tag and
        // pop it off the stack
        tagName = tagStack.pop();
        outputHtml += replacements[tagName].closingHtml;
      } else {
        var htmlOpeningTag;
        if (tagParameter && replacements[tagName].alternateOpeningHtml) {
          htmlOpeningTag = replacements[tagName].alternateOpeningHtml;
        } else {
          htmlOpeningTag = replacements[tagName].openingHtml;
        }
        // Insert things like the game ID into a game.html link
        if (replacements[tagName].escapeParameter) {
          // We need to HTML decode the parameter before we URI encode it, 
          // and the easiest way is to pretend we're going to render it
          var tempDiv = $('<div>');
          tempDiv.html(tagParameter);
          var decodedTagParameter = tempDiv.text();
          htmlOpeningTag = htmlOpeningTag.replace(
            '###',
            encodeURIComponent(decodedTagParameter)
          );
        } else {
          htmlOpeningTag = htmlOpeningTag.replace('###', tagParameter);
        }
        outputHtml += htmlOpeningTag;
        if (!replacements[tagName].isAtomic) {
          // If there's a closing tag coming along later, push this tag
          // on the stack so we'll know we're waiting on it
          tagStack.push(tagName);
        } else if (replacements[tagName].closingHtml) {
          // If there's no closing BB code tag, but there's closing HTML,
          // then we just apply it now
          outputHtml += tagParameter;
          outputHtml += replacements[tagName].closingHtml;
        }
      }

      htmlToParse = stuffAfterTag;
    } else {
      // If we don't find any more BB code tags that we're interested in,
      // then we must have reached the end
      outputHtml += htmlToParse;
      htmlToParse = '';
    }
  }

  // If any tags didn't get closed properly, then close them now
  while (tagStack.length > 0) {
    tagName = tagStack.pop();
    outputHtml += replacements[tagName].closingHtml;
  }

  // While we're here, we want to make sure there's an event set up to
  // reveal the contents of spoiler tags when requested.
  // (We turn the event off first so we're not binding it multiple times.)
  $(document).off('click', '.chatSpoiler')
    .on('click', '.chatSpoiler', Env.toggleSpoiler);

  return outputHtml;
};

Env.escapeRegexp = function(str) {
  return str.replace(/([.?*+^$[\]\\(){}|-])/g, '\\$1');
};

// Utility function to link to a profile page given a player name
Env.buildProfileLink = function(playerName, textOnly) {
  var url = 'profile.html?player=' + encodeURIComponent(playerName);
  if (textOnly) {
    return url;
  } else {
    return $('<a>', {
      'href': url,
      'text': playerName,
    });
  }
};

// Utility function to build a vacation image object
Env.buildVacationImage = function() {
  return  $('<img>', {
          'src': Env.ui_root + 'images/vacation.png',
          'class': 'playerFlag',
          'title': 'On Vacation'
        });
}

// Utility function to link to a button page given a button name
Env.buildButtonLink = function(buttonName, recipe, textOnly) {
  var url = 'buttons.html?button=' + encodeURIComponent(buttonName);
  if (textOnly) {
    return url;
  }
  var link =
    $('<a>', {
      'href': url,
      'text': buttonName,
    });
  if (recipe) {
    link.attr('title', recipe);
    link.append($('<span>', {
      'class': 'info_icon',
      'text': 'i',
    }));
  }
  return link;
};

// Utility function to link to a button set page given a button set name
Env.buildButtonSetLink = function(buttonSetName, textOnly) {
  var url = 'buttons.html?set=' + encodeURIComponent(buttonSetName);
  if (textOnly) {
    return url;
  } else {
    return $('<a>', {
      'href': url,
      'text': buttonSetName,
    });
  }
};

// Reveal (or un-reveal) the contents of spoiler tags
Env.toggleSpoiler = function() {
  $(this).toggleClass('chatExposedSpoiler');
};

// Calls several asynchronous methods in parallel, calling the final callback
// when they've all completed. All functions must take a callback as their
// final argument.
// The functions parameter is expected to be an array, and each element in
// that array should either be a function pointer or an object. If an object,
// it should have a "func" property containing the function pointer and an
// "args" property containing an array of arguments to be passed to the
// function (not counting the callback argument, which will be handled by
// callAsyncInParallel).
Env.callAsyncInParallel = function(functions, finalCallback) {
  var completedFunctions = [ ];

  // First, initialize all the function statuses before we start executing
  // anything
  $.each(functions, function(index) {
    // We're identifying these by index rather than name or function pointer
    // or object since javascript object properties can only be keyed by
    // strings, and we might be calling the same function (with the same string
    // name) with different sets of arguments.
    completedFunctions[index] = false;
  });

  $.each(functions, function(executionIndex, value) {
    var functionDetails;
    if (typeof(value) == 'function') {
      functionDetails = {
        'func': value,
        'args': [ ],
      };
    } else {
      functionDetails = value;
    }

    // Add *our* callback as the last parameter. We need it defined inline like
    // this so that it can close over the current state and know which function
    // was being executed.
    functionDetails.args.push(function() {
      // Flag that the function that this current callback was made by is
      // complete
      completedFunctions[executionIndex] = true;

      var allCompleted = true;
      $.each(completedFunctions, function(index, value) {
        allCompleted = allCompleted && value;
      });

      // We're apparently done!
      if (allCompleted) {
        finalCallback();
      }
    });

    functionDetails.func.apply(this, functionDetails.args);
  });
};

// Takes a URL that was entered by a user and returns a version of it that's
// safe to insert into an anchor tag (or returns NULL if we can't sensibly do
// that).
// Based in part on advice from http://stackoverflow.com/questions/205923
Env.validateUrl = function(url) {
  // First, check for and reject anything with inappropriate characters
  // (We can expand this list later if it becomes necessary)
  if (!url.match(/^[-A-Za-z0-9+&@#/%?=~_!:,.\(\)]+$/)) {
    return null;
  }

  // Then ensure that it begins with http:// or https://
  if (url.toLowerCase().indexOf('http://') !== 0 &&
      url.toLowerCase().indexOf('https://') !== 0) {
    url = 'http://' + url;
  }

  // This should create a relatively safe URL. It does not verify that it's a
  // *valid* URL, but if it is invalid, this should at least render it impotent.
  // This also doesn't verify that the URL points to a safe page, but that is
  // outside of the scope of this function.
  return url;
};
