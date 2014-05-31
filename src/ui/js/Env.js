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
          '<div class="chatQuote"><div class="chatQuotee">Quote:</div>',
      'closingHtml': '</div>',
    },
    'game': {
      'isAtomic': true,
      'isLink': true,
      'openingHtml': '<a class="chatGameLink" href="game.html?game=###">Game ',
      'closingHtml': '</a>',
    },
    'player': {
      'isAtomic': true,
      'isLink': true,
      'openingHtml':
          '<a class="chatPlayerLink" href="profile.html?player=###">',
      'closingHtml': '</a>',
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
  $.each(replacements, function(tagName, tagInfo) {
    if (allStartTagsPattern !== '') {
      allStartTagsPattern += '|';
    }
    // Matches, e.g., '[ b ]' or '[game = "123"]'
    // The (?:... part means that we want parentheses around the whole
    // thing (so we we can OR it together with other ones), but we don't
    // want to capture the value of the whole thing as a group
    allStartTagsPattern +=
      '(?:\\[\\s*(' + Env.escapeRegexp(tagName) +
        ')\\s*(?:=\\s*"([^"]*)")?\\s*])';
  });

  while (htmlToParse) {
    var currentPattern = allStartTagsPattern;
    if (tagStack.length !== 0) {
      // The tag that was most recently opened
      var tagName = tagStack[tagStack.length - 1];
      // Matches '[/i]' et al.
      // (so that we can spot the end of the current tag as well as start
      // tags)
      currentPattern +=
        '|(?:\\[\\s*(/\\s*' + Env.escapeRegexp(tagName) + ')\\s*])';
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
      var tagName = '';
      for (var i = 2; i < match.length; i++) {
        tagName = match[i];
        if (tagName) {
          break;
        }
      }
      tagName = tagName.toLowerCase();
      var tagParameter = match[i+1] || '';
      var stuffAfterTag = match[match.length - 1];

      outputHtml += stuffBeforeTag;
      if (tagName.substring(0, 1) === '/') {
        // If we've found our closing tag, we can finish the current tag and
        // pop it off the stack
        tagName = tagStack.pop();
        outputHtml += replacements[tagName].closingHtml;
      } else {
        var htmlOpeningTag = replacements[tagName].openingHtml;
        // Insert things like the game ID into a game.html link
        htmlOpeningTag = htmlOpeningTag.replace('###', encodeURIComponent(tagParameter));
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
    var tagName = tagStack.pop();
    outputHtml += replacements[tagName].closingHtml;
  }

  return outputHtml;
}

Env.escapeRegexp = function(str) {
  return str.replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
};
