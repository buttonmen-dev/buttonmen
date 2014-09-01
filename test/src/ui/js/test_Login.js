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

    Login.pageModule = null;

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

  Login.message = 'Hello.';
  Login.pageModule = {
    'bodyDivId': 'test_page',
    'showLoggedInPage':
      function() {
      assert.ok(true, "Login callback should be called");
    },
  };

  BMTestUtils.setupFakeLogin();
  Login.getBody();
  BMTestUtils.cleanupFakeLogin();
});

  var bodyDiv = $('#' + Login.pageModule.bodyDivId);
  assert.equal(bodyDiv.length, 1,
    "Main page body div should be created");
  bodyDiv.remove();
  bodyDiv.empty();

  Login.arrangePage = function() {
    assert.ok(true,
      "Login.arrangePage() should be directly called when logged out");
  };

  Login.pageModule = {
    'bodyDivId': 'test_page',
    'showLoggedInPage':
      function() {
        assert.ok(true, "Login callback should not be called when logged out");
      },
  };

  Login.getBody();
});

test("test_Login.arrangePage", function(assert) {
  expect(5); // tests + 2 teardown

  Login.pageModule = { 'bodyDivId': 'test_page' };
  $('body').append($('<div>', {'id': Login.pageModule.bodyDivId, }));

  Env.message = {
    'type': 'none',
    'text': 'It\'s Howdy Doody time!',
  };

  var expectedPage = "page value";
  var expectedForm = "form value";
  var expectedSubmitSelector = "submitSelector value";

  Login.arrangeHeader = function() {
    assert.ok(true, "Login.arrangeHeader should be called");
  };

  Login.arrangeBody = function(actualPage, actualForm, actualSubmitSelector) {
    assert.equal(actualPage, expectedPage,
      "Login.arrangeBody should be called with the correct page");
    assert.equal(actualForm, expectedForm,
      "Login.arrangeBody should be called with the correct form");
    assert.equal(actualSubmitSelector, expectedSubmitSelector,
      "Login.arrangeBody should be called with the correct submitSelector");
  };

  Login.arrangeFooter = function() {
    assert.ok(true, "Login.arrangeFooter should be called");
  };

  Login.arrangePage(expectedPage, expectedForm, expectedSubmitSelector);

  var envMessage = $('#env_message');
  assert.equal(envMessage.text(), Env.message.text,
    'Env message should be displayed');
});

test("test_Login.arrangeHeader", function(assert) {
  expect(3); // tests + 2 teardown

  Login.message = 'Hello.';

  Login.arrangeHeader();

  assert.equal($('#login_header').text(), Login.message,
    'Login message should be displayed');
});

test("test_Login.arrangeBody", function(assert) {
  expect(4); // tests + 2 teardown

  Login.pageModule = { 'bodyDivId': 'test_page' };

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
  Login.arrangeBody(page, formFunction, '#testFormButton');

  var testPageContents = $('#test_page div.testPageContents');
  assert.equal(testPageContents.text(), page.text(),
    'Page contents should exist on actual page.');
  button.click();

  if (Login.pageModule) {
    $('#' + Login.pageModule.bodyDivId).remove();
    $('#' + Login.pageModule.bodyDivId).empty();
  }
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
