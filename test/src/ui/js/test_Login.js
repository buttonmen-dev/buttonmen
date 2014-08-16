module("Login", {
  'setup': function() {
    BMTestUtils.LoginPre = BMTestUtils.getAllElements();

    // Back up any methods that we might decide to replace with mocks
    BMTestUtils.LoginBackup = { };
    BMTestUtils.CopyAllMethods(Login, BMTestUtils.LoginBackup);

    // Create the login_header div so functions have something to modify
    if (document.getElementById('login_header') == null) {
      $('body').append($('<div>', {'id': 'login_header', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create
    BMTestUtils.deleteEnvMessage();
    delete Api.gameNavigation;
    delete Api.forumNavigation;
    delete Env.window.location.href;
    delete Login.message;
    delete Login.footer;
    delete Env.window.location.search;
    delete Env.window.location.hash;
    delete Env.history.state;

    Api.automatedApiCall = false;

    // Page elements
    $('#login_header').remove();
    $('#login_header').empty();
    $('#header_separator').remove();
    $('#footer_separator').remove();
    $('#footer').remove();
    $('#footer').empty();

    Login.bodyDivId = null;

    // Page elements
    $('#login_header').remove();
    $('#login_header').empty();

    BMTestUtils.deleteEnvMessage();

    // Restore any methods that we might have replaced with mocks
    BMTestUtils.CopyAllMethods(BMTestUtils.LoginBackup, Login);

    // Fail if any other elements were added or removed
    BMTestUtils.LoginPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.LoginPost, BMTestUtils.LoginPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Login module has been loaded
test("test_Login_is_loaded", function(assert) {
  assert.ok(Login, "The Login namespace exists");
});

test("test_Login.showLoginHeader", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.showLoginHeader not implemented");
});

test("test_Login.showLoginHeader_auto", function(assert) {
  expect(4); // tests + 2 teardown

  Login.getLoginHeader = function() {
    assert.ok(!Api.automatedApiCall,
      'showLoginHeader should not set Api.automatedApiCall without auto=true');
  };
  Login.showLoginHeader();

  Env.window.location.search = '?auto=true';
  Login.getLoginHeader = function() {
    assert.ok(Api.automatedApiCall,
      'showLoginHeader should set Api.automatedApiCall when auto=true');
  };
  Login.showLoginHeader();
});

test("test_Login.getLoginHeader", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.getLoginHeader not implemented");
});

test("test_Login.getFooter", function(assert) {
  expect(5); // tests + 2 teardown

  Login.callback = function() {
    assert.ok(true, "Login callback should be called when logged in");
  };

  Login.arrangePage = function() {
    assert.ok(false, "Login.arrangePage() should not be called when logged in");
  };

  BMTestUtils.setupFakeLogin();
  Login.getFooter();
  BMTestUtils.cleanupFakeLogin();

  assert.ok(Login.footer.text().match('Cheapass Games'),
    'Footer should contain copyright notice');
  assert.ok(Login.footer.text().match('help@buttonweavers.com'),
    'Footer should contain contact info');
});

test("test_Login.getFooter_loggedOut", function(assert) {
  expect(3); // tests + 2 teardown

  Login.callback = function() {
    assert.ok(false, "Login callback should not be called when logged out");
  };

  Login.arrangePage = function() {
    assert.ok(true, "Login.arrangePage() should be called when logged out");
  };

  Login.getFooter();
});

test("test_Login.arrangePage", function(assert) {
  expect(5); // tests + 2 teardown

  Login.bodyDivId = 'test_page';
  $('body').append($('<div>', {'id': Login.bodyDivId, }));

  Env.setupEnvStub();
  Env.message = {
    'type': 'none',
    'text': 'It\'s Howdy Doody time!',
  };

  var page = $('<div>', {
    'class': 'testPageContents',
    'text': 'Kilroy was here.',
  });
  var button = $('<input>', {
    'type': 'button',
    'id': 'testFormButton',
  });
  page.append(button);
  var formFunction = function() {
    assert.ok(true, 'Form should be activated on click.');
  };
  Login.arrangePage(page, formFunction, '#testFormButton');

  var envMessage = $('#env_message');
  assert.equal(envMessage.text(), Env.message.text,
    'Env message should be displayed');
  var testPageContents = $('#test_page div.testPageContents');
  assert.equal(testPageContents.text(), page.text(),
    'Page contents should exist on actual page.');
  button.click();

  $('#' + Login.bodyDivId).remove();
  $('#' + Login.bodyDivId).empty();
});

test("test_Login.arrangeHeader", function(assert) {
  expect(4); // tests + 2 teardown

  Login.message = 'Hello.';
  Login.bodyDivId = 'test_page';

  Login.arrangeHeader();

  var bodyDiv = $('#' + Login.bodyDivId);
  assert.equal(bodyDiv.length, 1,
    "Main page body div should be created");
  bodyDiv.remove();
  bodyDiv.empty();

  assert.equal($('#env_message').length, 1,
    "Env message div should be created");
});

test("test_Login.arrangeFooter", function(assert) {
  expect(3); // tests + 2 teardown

  Login.footer = $('<div>', { 'text': 'Abracadabra' });
  Login.arrangeFooter();
  assert.equal($('#footer').text(), 'Abracadabra');
});

test("test_Login.getLoginForm", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.getLoginForm not implemented");
});

test("test_Login.stateLoggedIn", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.stateLoggedIn not implemented");
});

test("test_Login.stateLoggedOut", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.stateLoggedOut not implemented");
});

test("test_Login.addMainNavbar", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.addMainNavbar not implemented");
});

test("test_Login.addNewPostLink", function(assert) {
  Login.message = $('<table>');
  var navRow  = $('<tr>', { 'class': 'headerNav' });
  Login.message.append(navRow);
  var navTd = $('<td>');
  navRow.append(navTd);
  navTd.append($('<a>', { 'href': '#', 'text': 'Forum', }));

  Api.forumNavigation = {
    'nextNewPostId': 7,
    'nextNewPostThreadId': 3,
  };

  Login.addNewPostLink();
  assert.ok(navRow.find('a:contains("(New post)")').length,
    'Link should be created to new post when there is a new post');
});

test("test_Login.addNewPostLink_noNewPost", function(assert) {
  Login.message = $('<table>');
  var navRow  = $('<tr>', { 'class': 'headerNav' });
  var navTd = $('<td>');
  navRow.append(navTd);
  navTd.append($('<a>', { 'href': '#', 'text': 'Forum', }));

  Api.forumNavigation = {
    'nextNewPostId': null,
    'nextNewPostThreadId': null,
  };

  Login.addNewPostLink();
  assert.ok(!navRow.find('a:contains("(New post)")').length,
    'Link should not be created to new post when there is no new post');
});

test("test_Login.postToResponder", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.postToResponder not implemented");
});

test("test_Login.formLogout", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.formLogout not implemented");
});

test("test_Login.formLogin", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.formLogin not implemented");
});

test("test_Login.goToNextPendingGame", function(assert) {
  Env.window.location.href = "/ui/game.html?game=1";
  Api.gameNavigation = {
    'load_status': 'ok',
    'nextGameId': 7,
  };

  Login.goToNextPendingGame();
  notEqual(Env.window.location.href, null, "The page has been redirected");
  if (Env.window.location.href !== null && Env.window.location.href !== undefined)
  {
    assert.ok(Env.window.location.href.match(/game\.html\?game=7/),
      "The page has been redirected to the next game");
  }
});

test("test_Login.goToNextPendingGame_no_next_game", function(assert) {
  Env.window.location.href = "/ui/game.html?game=1";

  Api.gameNavigation = {
    'load_status': 'ok',
    'nextGameId': null,
  };

  Login.goToNextPendingGame();
  notEqual(Env.window.location.href, null, "The page has been redirected");
  if (Env.window.location.href !== null && Env.window.location.href !== undefined)
  {
    assert.ok(Env.window.location.href.match(/\/ui\/index\.html\?mode=preference$/),
      "The page has been redirected to the Overview page");
  }
});
