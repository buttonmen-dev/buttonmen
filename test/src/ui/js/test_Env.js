module("Env", {
  'setup': function() {
    BMTestUtils.EnvPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create an empty #c_body for use by these tests, because they don't create a normal page
    $('body').append($('<div>', {'id': 'container', }));
    $('#container').append($('<div>', {'id': 'c_body'}));
},
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create
    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();
    $('#container').remove();

    delete Env.window.location.origin;
    delete Env.window.location.pathname;
    delete Env.window.location.search;
    delete Env.window.location.hash;
    delete Env.window.location.href;
    delete Env.history.state;

    // Fail if any other elements were added or removed
    BMTestUtils.EnvPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.EnvPost, BMTestUtils.EnvPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Env module has been loaded
test("test_Env_is_loaded", function(assert) {
  expect(3); // number of tests plus 2 for the teardown test
  assert.ok(Env, "The Env namespace exists");
});

test("test_Env.getParameterByName", function(assert) {
  expect(4); // number of tests plus 2 for the teardown test

  Env.window.location.search = '?game=29';
  Env.window.location.hash = '#!playerNameA=tester&buttonNameA=Avis';

  var gameId = Env.getParameterByName('game');
  assert.equal(gameId, '29', 'Query string parameter is found');

  var playerNameA = Env.getParameterByName('playerNameA');
  assert.equal(playerNameA, 'tester', 'Hashbang parameter is found');
});

test("test_Env.removeParameterByName", function(assert) {
  Env.window.location.origin = 'http://buttonweavers.com';
  Env.window.location.pathname = '/testpage.html';
  Env.window.location.search = '?auto=true';
  Env.window.location.hash = '';

  Env.removeParameterByName('auto');
  assert.equal(
    Env.window.location.href,
    'http://buttonweavers.com/testpage.html',
    'Query string parameter should be removed from URL'
  );

  Env.window.location.origin = 'http://buttonweavers.com:4444';
  Env.window.location.pathname = '/testpage.html';
  Env.window.location.search = '?game=7&auto=true&sparrow=African';
  Env.window.location.hash = '#!playerNameA=tester&buttonNameA=Avis';

  Env.removeParameterByName('auto');
  assert.equal(
    Env.window.location.href,
    'http://buttonweavers.com:4444/testpage.html' +
      '?game=7&sparrow=African#!playerNameA=tester&buttonNameA=Avis',
    'Query string parameter should be removed from URL'
  );

  Env.window.location.origin = 'http://buttonweavers.com';
  Env.window.location.pathname = '/game.html';
  Env.window.location.search = '?game=7';
  Env.window.location.hash = '';

  Env.removeParameterByName('auto');
  assert.equal(
    Env.window.location.href,
    'http://buttonweavers.com/game.html?game=7',
    'URL should be unchanged'
  );
});

test("test_Env.setupEnvStub", function(assert) {
  expect(5); // number of tests plus 2 for the teardown test

  var item = document.getElementById('env_message');
  assert.equal(item, null, "#env_message is null before setupEnvStub is called");

  // Setup the env stub, display the message, and verify that it is empty
  Env.setupEnvStub();
  item = document.getElementById('env_message');
  assert.equal(item.nodeName, "DIV",
        "#env_message is a div after setupEnvStub is called");
  assert.equal(item.innerHTML, "",
        "#env_message has empty HTML after setupEnvStub is called");
});

test("test_Env.showStatusMessage", function(assert) {
  expect(6); // number of tests plus 2 for the teardown test

  // Setup the env stub, display the message, and verify that it is empty
  Env.setupEnvStub();
  Env.showStatusMessage();
  var item = $('#env_message');
  assert.equal(item.html(), "", "Initial #env_message is empty");

  // Set message text, try to display the message, and verify the text/color
  Env.message = {
    'type': 'error',
    'text': 'test message text'
  };
  Env.showStatusMessage();
  assert.equal(item.html(), "<p><font color=\"red\">test message text</font></p>",
        "Populated #env_message has expected text and color");

  // Modify message text, try to display the message, and verify the text/color
  Env.message = {
    'type': 'success',
    'text': 'new message text'
  };
  Env.showStatusMessage();
  assert.equal(item.html(), "<p><font color=\"green\">new message text</font></p>",
        "Modified #env_message has expected text and color");

  // Set invalid message type, and verify the default of no color
  Env.message = {
    'type': 'foobar',
    'text': 'newer message text'
  };
  Env.showStatusMessage();
  assert.equal(item.html(), "<p><font>newer message text</font></p>",
        "#env_message has expected text and color when type is invalid");
});

test("test_Env.formatTimestamp", function(assert) {
  expect(5); // number of tests plus 2 for the teardown test

  var expectedDate = '2014-03-23';
  var expectedTime = '21:38:10';
  var expectedDateTime = '2014-03-23 21:38:10';

  var timestamp = moment(expectedDateTime, 'YYYY-MM-DD HH:mm:ss', true).unix();

  var results = Env.formatTimestamp(timestamp, 'date');
  assert.equal(results, expectedDate, 'formatTimestamp returned correct date');

  var results = Env.formatTimestamp(timestamp, 'time');
  assert.equal(results, expectedTime, 'formatTimestamp returned correct time');

  var results = Env.formatTimestamp(timestamp);
  assert.equal(results, expectedDateTime, 'formatTimestamp returned correct datetime');
});

test("test_Env.parseDateTime", function(assert) {
  var expectedTimestamp = 1395610690;
  var datetimeString = Env.formatTimestamp(expectedTimestamp);

  var results = Env.parseDateTime(datetimeString, 'datetime');
  assert.equal(results, expectedTimestamp, 'parseDateTime returned correct timestamp');
});

test("test_Env.setCookieNoImages", function(assert) {
  var value = false;
  Env.setCookieNoImages(value);
  assert.equal(Env.getCookieNoImages(), value, 'noImage is false');
  });

test("test_Env.getCookieNoImages", function(assert) {
  value = true;
  Env.setCookieNoImages(value);
  assert.equal(Env.getCookieNoImages(), value, 'noImage is true');
});

test("test_Env.setCookieCompactMode", function(assert) {
  var value = false;
  Env.setCookieCompactMode(value);
  assert.equal(Env.getCookieCompactMode(), value, 'compactMode is false');
  });

test("test_Env.getCookieCompactMode", function(assert) {
  value = true;
  Env.setCookieCompactMode(value);
  assert.equal(Env.getCookieCompactMode(), value, 'compactMode is true');
});

test("test_Env.addClickKeyboardHandlers", function(assert) {
  stop();
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
  };
  var spacePressed;
  var registerSpacePress = function() {
    spacePressed = true;
  };
  var returnPressed;
  var registerReturnPress = function() {
    returnPressed = true;
  };
  Env.addClickKeyboardHandlers(item, registerMouseClick, registerSpacePress, registerReturnPress);

  var spacePress = jQuery.Event('keydown');
  spacePress.which = 32;

  var returnPress = jQuery.Event('keydown');
  returnPress.which = 13;

  mouseClicked = false;
  spacePressed = false;
  returnPressed = false;
  item.trigger('click');
  assert.equal(mouseClicked, true, "Mouse handler was invoked on mouse click");
  assert.equal(spacePressed, false, "Spacebar handler was not invoked on mouse click");
  assert.equal(returnPressed, false, "Return key handler was not invoked on mouse click");

  mouseClicked = false;
  spacePressed = false;
  returnPressed = false;
  item.trigger(spacePress);
  assert.equal(mouseClicked, false, "Mouse handler was not invoked on spacebar key press");
  assert.equal(spacePressed, true, "Spacebar handler was invoked on spacebar key press");
  assert.equal(returnPressed, false, "Return key handler was not invoked on spacebar key press");

  mouseClicked = false;
  spacePressed = false;
  returnPressed = false;
  item.trigger(returnPress);
  assert.equal(mouseClicked, false, "Mouse handler was not invoked on return key press");
  assert.equal(spacePressed, false, "Spacebar handler was not invoked on return key press");
  assert.equal(returnPressed, true, "Return key handler was invoked on return key press");

  start();
});

test("test_Env.prepareRawTextForDisplay", function(assert) {
  var rawText = '<b>HTML</b>\n[i]BB Code[/i]';
  var holder = $('<div>');
  holder.append(Env.prepareRawTextForDisplay(rawText));

  assert.ok(holder.find('b').length == 0, '<b> tag should not be allowed unmolested');
  assert.ok(holder.find('.chatItalic').length == 1, '[i] tag should be converted to HTML');
  assert.ok(holder.find('br').length == 1, 'Newline should become <br> tag');
});

test("test_Env.applyBbCodeToHtml", function(assert) {
  var rawHtml = '<b>HTML</b><br/>[i]BB Code[/i]';
  var holder = $('<div>');
  holder.append(Env.applyBbCodeToHtml(rawHtml));
  assert.ok(holder.find('b').length == 1, '<b> tag *should* be allowed unmolested');
  assert.ok(holder.find('.chatItalic').length == 1, '[i] tag should be converted to HTML');
});

test("test_Env.removeBbCodeFromHtml", function(assert) {
  var rawHtml = '<b>HTML</b><br/>[i]BB Code[/i]';
  var newHtml = Env.removeBbCodeFromHtml(rawHtml);
  assert.equal(newHtml, '<b>HTML</b><br/>BB Code', 'Stripped-down HTML should be correct');
});

test("test_Env.escapeRegexp", function(assert) {
  var rawText = 'example.com';
  var escapedPattern = Env.escapeRegexp(rawText);
  assert.ok('example.com'.match(escapedPattern),
    'Pattern should still match original text');
  assert.ok(!'example_com'.match(escapedPattern),
    'Pattern should not match variant text');
  assert.equal(escapedPattern, 'example\\.com',
    'Escaped pattern should be as expected');
});

test("test_Env.buildProfileLink", function(assert) {
  var link = Env.buildProfileLink('tester');
  assert.equal(link.attr('href'), 'profile.html?player=tester',
    'Link should point to profile page.');

  var linktext = Env.buildProfileLink('tester', true);
  assert.equal(linktext, 'profile.html?player=tester',
    'Link text should point to profile page.');
});

test("test_Env.buildVacationImage", function(assert) {
  var vacationImage = Env.buildVacationImage();
  assert.equal(vacationImage.attr('src'),
     Env.ui_root + 'images/vacation16.png',
     'Vacation image should point to correct image.');
  assert.equal(vacationImage.attr('class'), 'playerFlag',
     'Vacation image should have a CSS class of playerFlag.');
  assert.equal(vacationImage.attr('title'), 'On Vacation',
     'Vacation image should have a title of On Vacation.');

  vacationImage = Env.buildVacationImage('large');
  assert.equal(vacationImage.attr('src'),
     Env.ui_root + 'images/vacation22.png',
     'Vacation image should point to correct image.');
});

test("test_Env.buildButtonLink", function(assert) {
  var link = Env.buildButtonLink('tester');
  assert.equal(link.attr('href'), 'buttons.html?button=tester',
    'Link should point to button page.');

  var link = Env.buildButtonLink('tester', '(1) (2) (3) (4) (5)');
  assert.equal(link.attr('href'), 'buttons.html?button=tester',
    'Link should point to button page.');
  assert.equal(link.attr('title'), '(1) (2) (3) (4) (5)',
    'Link should include recipe as tooltip.');

  var linktext = Env.buildButtonLink('tester', null, true);
  assert.equal(linktext, 'buttons.html?button=tester',
    'Link text should point to button page.');
});

test("test_Env.buildButtonSetLink", function(assert) {
  var link = Env.buildButtonSetLink('tester');
  assert.equal(link.attr('href'), 'buttons.html?set=tester',
    'Link should point to button page.');

  var linktext = Env.buildButtonSetLink('tester', true);
  assert.equal(linktext, 'buttons.html?set=tester',
    'Link text should point to button page.');
});

test("test_Env.toggleSpoiler", function(assert) {
  var spoiler = $('<span>', { 'class': 'chatSpoiler' });
  var eventTriggerSpan = {'target': {'tagName': 'span'}};
  var eventTriggerAnchor = {'target': {'tagName': 'a'}};

  Env.toggleSpoiler.call(spoiler, eventTriggerSpan);
  assert.ok(spoiler.hasClass('chatExposedSpoiler'),
    'Spoiler should be styled as revealed');

  Env.toggleSpoiler.call(spoiler, eventTriggerAnchor);
  assert.ok(spoiler.hasClass('chatExposedSpoiler'),
    'Spoiler toggle should not change');

  Env.toggleSpoiler.call(spoiler, eventTriggerSpan);
  assert.ok(!spoiler.hasClass('chatExposedSpoiler'),
    'Spoiler should not be styled as revealed');
});

test("test_Env.callAsyncInParallel", function(assert) {
  stop();
  expect(4); // number of tests plus 2 for the teardown test

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
    assert.equal(result1, 33, 'func1 should have completed its async task');
    assert.equal(result2, 33, 'func2 should have completed its async task');
    start();
  });
});

test("test_Env.callAsyncInParallel_withArgs", function(assert) {
  stop();
  expect(4); // number of tests plus 2 for the teardown test

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
      assert.equal(result1, 11, 'func1 should have completed its async task');
      assert.equal(result2, 22, 'func2 should have completed its async task');
      start();
    }
  );
});

test("test_Env.validateUrl", function(assert) {
  var rawUrl = 'example.com';
  var cleanedUrl = Env.validateUrl(rawUrl);
  assert.equal(cleanedUrl, 'http://example.com', 'URL should have correct protocol');

  var rawUrl = 'javascript:alert(\'Evil\');';
  var cleanedUrl = Env.validateUrl(rawUrl);
  assert.equal(cleanedUrl, null, 'Malicious URL should be rejected');

  var rawUrl = 'http://example.com/$';
  var cleanedUrl = Env.validateUrl(rawUrl);
  assert.equal(cleanedUrl, null,
    'URL with inappropriate characters should be rejected');

  var rawUrl = 'https://example.com';
  var cleanedUrl = Env.validateUrl(rawUrl);
  assert.equal(cleanedUrl, rawUrl, 'Valid URL should be unaffected');
});
