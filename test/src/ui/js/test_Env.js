module("Env", {
  'setup': function() {
    BMTestUtils.EnvPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();
},
  'teardown': function() {

    // Delete all elements we expect this module to create
    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    delete Env.window.location.search;
    delete Env.window.location.hash;

    // Fail if any other elements were added or removed
    BMTestUtils.EnvPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.EnvPost, BMTestUtils.EnvPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Env module has been loaded
test("test_Env_is_loaded", function() {
  expect(2); // number of tests plus 1 for the teardown test
  ok(Env, "The Env namespace exists");
});

// Can't test this as written because we can't modify the real
// location.search, and Env.getParameterByName won't accept a fake one
test("test_Env.getParameterByName", function() {
  expect(3); // number of tests plus 1 for the teardown test

  Env.window.location.search = '?game=29';
  Env.window.location.hash = '#!playerNameA=tester&buttonNameA=Avis'

  var gameId = Env.getParameterByName('game');
  equal(gameId, '29', 'Query string parameter is found');

  var playerNameA = Env.getParameterByName('playerNameA');
  equal(playerNameA, 'tester', 'Hashbang parameter is found');
});

test("test_Env.setupEnvStub", function() {
  expect(4); // number of tests plus 1 for the teardown test

  var item = document.getElementById('env_message');
  equal(item, null, "#env_message is null before setupEnvStub is called");

  // Setup the env stub, display the message, and verify that it is empty
  Env.setupEnvStub();
  item = document.getElementById('env_message');
  equal(item.nodeName, "DIV",
        "#env_message is a div after setupEnvStub is called");
  equal(item.innerHTML, "",
        "#env_message has empty HTML after setupEnvStub is called");
});

test("test_Env.showStatusMessage", function() {
  expect(5); // number of tests plus 1 for the teardown test

  // Setup the env stub, display the message, and verify that it is empty
  Env.setupEnvStub();
  Env.showStatusMessage();
  var item = $('#env_message');
  equal(item.html(), "", "Initial #env_message is empty");

  // Set message text, try to display the message, and verify the text/color
  Env.message = {
    'type': 'error',
    'text': 'test message text'
  };
  Env.showStatusMessage();
  equal(item.html(), "<p><font color=\"red\">test message text</font></p>",
        "Populated #env_message has expected text and color");

  // Modify message text, try to display the message, and verify the text/color
  Env.message = {
    'type': 'success',
    'text': 'new message text'
  };
  Env.showStatusMessage();
  equal(item.html(), "<p><font color=\"green\">new message text</font></p>",
        "Modified #env_message has expected text and color");

  // Set invalid message type, and verify the default of no color
  Env.message = {
    'type': 'foobar',
    'text': 'newer message text'
  };
  Env.showStatusMessage();
  equal(item.html(), "<p><font>newer message text</font></p>",
        "#env_message has expected text and color when type is invalid");
});

test("test_Env.formatTimestamp", function() {
  expect(4); // number of tests plus 1 for the teardown test

  // Since the method we're testing produces output for the local time zone,
  // we need to compensate for that in order to test it.
  var offsetInMinutes = new Date().getTimezoneOffset();
  var timestamp = 1395610690 + (offsetInMinutes * 60);

  var expectedDate = '2014-03-23';
  var expectedTime = '21:38:10';
  var expectedDateTime = '2014-03-23 21:38:10';

  var results = Env.formatTimestamp(timestamp, 'date');
  equal(results, expectedDate, 'formatTimestamp returned correct date');

  var results = Env.formatTimestamp(timestamp, 'time');
  equal(results, expectedTime, 'formatTimestamp returned correct time');

  var results = Env.formatTimestamp(timestamp);
  equal(results, expectedDateTime, 'formatTimestamp returned correct datetime');
});

test("test_Env.parseDateTime", function() {
  var input = '2014-03-23 21:38:10';
  var offsetInMinutes = new Date().getTimezoneOffset();
  var expectedOutput = 1395610690 + (offsetInMinutes * 60);

  var results = Env.parseDateTime(input, 'datetime');

  equal(results, expectedOutput, 'parseDateTime returned correct timestamp');
});

test("test_Env.setCookieNoImages", function() {
  var value = false;
  Env.setCookieNoImages(value);
  equal(Env.getCookieNoImages(), value, 'noImage is false');
  });

test("test_Env.getCookieNoImages", function() {
  value = true;
  Env.setCookieNoImages(value);
  equal(Env.getCookieNoImages(), value, 'noImage is true');
});

test("test_Env.setCookieCompactMode", function() {
  var value = false;
  Env.setCookieCompactMode(value);
  equal(Env.getCookieCompactMode(), value, 'compactMode is false');
  });

test("test_Env.getCookieCompactMode", function() {
  value = true;
  Env.setCookieCompactMode(value);
  equal(Env.getCookieCompactMode(), value, 'compactMode is true');
});

asyncTest("test_Env.addClickKeyboardHandlers", function() {
  $('body').append($('h1', {'text': 'is there anybody out there?', 'id': 'env_handler_test', }));
  $('body').append($('<div>', {'id': 'env_message', }));
  $('#env_message').append($('<button>', {
    'id': 'env_handler_test',
    'text': 'Submit',
    'tabIndex': 0,
  }));
  item = $('#env_handler_test');

  var mouseClicked;
  var registerMouseClick = function() {
    mouseClicked = true;
  }
  var spacePressed;
  var registerSpacePress = function() {
    spacePressed = true;
  }
  var returnPressed;
  var registerReturnPress = function() {
    returnPressed = true;
  }
  Env.addClickKeyboardHandlers(item, registerMouseClick, registerSpacePress, registerReturnPress);

  var spacePress = jQuery.Event('keydown');
  spacePress.which = 32;

  var returnPress = jQuery.Event('keydown');
  returnPress.which = 13;

  mouseClicked = false;
  spacePressed = false;
  returnPressed = false;
  item.trigger('click');
  equal(mouseClicked, true, "Mouse handler was invoked on mouse click");
  equal(spacePressed, false, "Spacebar handler was not invoked on mouse click");
  equal(returnPressed, false, "Return key handler was not invoked on mouse click");

  mouseClicked = false;
  spacePressed = false;
  returnPressed = false;
  item.trigger(spacePress);
  equal(mouseClicked, false, "Mouse handler was not invoked on spacebar key press");
  equal(spacePressed, true, "Spacebar handler was invoked on spacebar key press");
  equal(returnPressed, false, "Return key handler was not invoked on spacebar key press");

  mouseClicked = false;
  spacePressed = false;
  returnPressed = false;
  item.trigger(returnPress);
  equal(mouseClicked, false, "Mouse handler was not invoked on return key press");
  equal(spacePressed, false, "Spacebar handler was not invoked on return key press");
  equal(returnPressed, true, "Return key handler was invoked on return key press");

  start();
});

test("test_Env.prepareRawTextForDisplay", function() {
  var rawText = '<b>HTML</b>\n[i]BB Code[/i]';
  var holder = $('<div>');
  holder.append(Env.prepareRawTextForDisplay(rawText));

  ok(holder.find('b').length == 0, '<b> tag should not be allowed unmolested');
  ok(holder.find('.chatItalic').length == 1, '[i] tag should be converted to HTML');
  ok(holder.find('br').length == 1, 'Newline should become <br> tag');
});

test("test_Env.applyBbCodeToHtml", function() {
  var rawHtml = '<b>HTML</b><br/>[i]BB Code[/i]';
  var holder = $('<div>');
  holder.append(Env.applyBbCodeToHtml(rawHtml));
  ok(holder.find('b').length == 1, '<b> tag *should* be allowed unmolested');
  ok(holder.find('.chatItalic').length == 1, '[i] tag should be converted to HTML');
});

test("test_Env.escapeRegexp", function() {
  var rawText = 'example.com';
  var escapedPattern = Env.escapeRegexp(rawText);
  ok('example.com'.match(escapedPattern),
    'Pattern should still match original text');
  ok(!'example_com'.match(escapedPattern),
    'Pattern should not match variant text');
  equal(escapedPattern, 'example\\.com',
    'Escaped pattern should be as expected');
});

test("test_Env.sanitizeUrl", function() {
  var rawUrl = 'example.com';
  var cleanedUrl = Env.validateUrl(rawUrl);
  equal(cleanedUrl, 'http://example.com', 'URL should have correct protocol');

  var rawUrl = 'javascript:alert(\'Evil\');';
  var cleanedUrl = Env.validateUrl(rawUrl);
  equal(cleanedUrl, null, 'Malicious URL should be rejected');

  var rawUrl = 'http://example.com/$';
  var cleanedUrl = Env.validateUrl(rawUrl);
  equal(cleanedUrl, null,
    'URL with inappropriate characters should be rejected');

  var rawUrl = 'https://example.com';
  var cleanedUrl = Env.validateUrl(rawUrl);
  equal(cleanedUrl, rawUrl, 'Valid URL should be unaffected');
});

test("test_Env.buildProfileLink", function() {
  var link = Env.buildProfileLink('tester');
  equal(link.attr('href'), 'profile.html?player=tester',
    'Link should point to profile page.');

  var linktext = Env.buildProfileLink('tester', true);
  equal(linktext, 'profile.html?player=tester',
    'Link text should point to profile page.');
});

test("test_Env.toggleSpoiler", function() {
  var spoiler = $('<span>', { 'class': 'chatSpoiler' });

  Env.toggleSpoiler.call(spoiler);
  ok(spoiler.hasClass('chatExposedSpoiler'), 'Spoiler should be styled as revealed');

  Env.toggleSpoiler.call(spoiler);
  ok(!spoiler.hasClass('chatExposedSpoiler'),
    'Spoiler should not be styled as revealed');
asyncTest("test_Env.callAsyncInParallel", function() {
  expect(3); // number of tests plus 1 for the teardown test

  var result1 = 0;
  var result2 = 0;

  var func1 = function(callback) {
    setTimeout(function() {
      result1 = 33;
      callback();
    }, 50);
  };

  var func2 = function(callback) {
    setTimeout(function() {
      result2 = 33;
      callback();
    }, 100);
  };

  Env.callAsyncInParallel([ func1, func2 ], function() {
    equal(result1, 33, 'func1 should have completed its async task');
    equal(result2, 33, 'func2 should have completed its async task');
    start();
  });
});

asyncTest("test_Env.callAsyncInParallel_withArgs", function() {
  expect(3); // number of tests plus 1 for the teardown test

  var result1 = 0;
  var result2 = 0;

  var func1 = function(input, callback) {
    setTimeout(function() {
      result1 = input;
      callback();
    }, 50);
  };

  var func2 = function(input, callback) {
    setTimeout(function() {
      result2 = input;
      callback();
    }, 100);
  };

  Env.callAsyncInParallel(
    [
      { 'func': func1, 'args': [ 11 ] },
      { 'func': func2, 'args': [ 22 ] },
    ], function() {
      equal(result1, 11, 'func1 should have completed its async task');
      equal(result2, 22, 'func2 should have completed its async task');
      start();
    });
  });
});
