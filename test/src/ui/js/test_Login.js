module("Login", {
  'setup': function() {
    BMTestUtils.LoginPre = BMTestUtils.getAllElements();

    // Create the login_header div so functions have something to modify
    if (document.getElementById('login_header') == null) {
      $('body').append($('<div>', {'id': 'container', }));
      $('#container').append($('<div>', {'id': 'c_body'}));
      $('#c_body').append($('<div>', {'id': 'login_header', }));
      $('#container').append($('<div>', {'id': 'c_footer'}));
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
    delete Login.footer;
    delete Login.message;
    delete Login.player;
    delete Login.logged_in;
    delete Env.window.location.search;
    delete Env.window.location.hash;
    delete Env.history.state;
    $('#container').remove();

    Api.automatedApiCall = false;

    // Page elements
    $('#login_header').remove();
    $('#header_separator').remove();

    Login.pageModule = null;
    Login.formElements = null;

    // Page elements
    $('#login_header').remove();

    BMTestUtils.deleteEnvMessage();

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

test("test_Login.getLoginHeader", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.getLoginHeader not implemented");
});

test("test_Login.getFooter", function(assert) {
  expect(4);

  // mock Login.getBody() because it is not the target of this test
  var cached_function = Login.getBody;
  Login.getBody = function() {
    assert.ok(true, "Login.getBody is called");
  };
  Login.getFooter();
  var footerProps = BMTestUtils.DOMNodePropArray(Login.footer[0]);
  var expectedProps = [ "DIV", {}, [
    [ "TABLE", {}, [
        [ "TBODY", {}, [
          [ "TR", { "class": "footerNav" }, [
            [ "TD", {}, [
              [ "A", { "href": "help.html" }, [ "Help" ] ]
            ] ],
            [ "TD", {}, [
              [ "A", { "href": "privacy.html" }, [ "Privacy" ] ]
            ] ]
          ] ]
        ] ],
        [ "BR" ]
    ] ],
    [ "DIV", {}, [
      "Button Men is copyright 1999, 2025 James Ernest and Cheapass Games: ",
      [ "A", { "href": "http://cheapass.com" }, [ "cheapass.com" ] ],
      " and ",
      [ "A", { "href": "http://beatpeopleup.cheapass.com" }, [ "beatpeopleup.cheapass.com" ] ],
      ", and is used with permission." ]
    ] ]
  ];
  assert.deepEqual(footerProps, expectedProps, "Footer contents are expected");

  Login.getBody = cached_function;
});

test("test_Login.getBody", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.getBody not implemented");
});

test("test_Login.showLoginHeader", function(assert) {
  expect(4);

  // mock Login.getLoginHeader() because it is not the target of this test
  var cached_function = Login.getLoginHeader;
  Login.getLoginHeader = function() {
    assert.ok(true, "Login.getLoginHeader is called");
  };

  // Empty the page container to test the contents the code will add
  $('#container').remove();
  Login.showLoginHeader('fakemodule');
  var containerProps = BMTestUtils.DOMNodePropArray($('#container')[0]);
  var expectedProps = [ "DIV", { "id": "container" }, [
    [ "DIV", { "id": "c_header" }, [
      [ "DIV", { "id": "login_header" }, [] ],
      [ "HR", { "id": "header_separator" }, [] ] ]
    ],
    [ "DIV", { "id": "c_body" }, [] ],
    [ "DIV", { "id": "c_footer" }, [] ] ]
  ];
  assert.deepEqual(containerProps, expectedProps, "This function correctly assembles the empty page frame");

  Login.getLoginHeader = cached_function;
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

test("test_Login.arrangeHeader", function(assert) {
  expect(3); // tests + 2 teardown

  Login.message = 'Hello.';
  Login.pageModule = {
    'bodyDivId': 'test_page',
    'showLoggedInPage':
      function() {
      assert.ok(true, "Login callback should be called");
    },
  };

  assert.equal($('#login_header').length, 1,
    "Login header div should be created");
});

test("test_Login.arrangeBody", function(assert) {
  expect(3);

  Login.message = 'Hello.';
  Login.pageModule = {
    'bodyDivId': 'test_page',
    'showLoggedInPage':
      function() {
      assert.ok(true, "Login callback should be called");
    },
  };

  BMTestUtils.setupFakeLogin();
  Login.arrangeBody();
  BMTestUtils.cleanupFakeLogin();

  var bodyDiv = $('#' + Login.pageModule.bodyDivId);
  assert.equal(bodyDiv.length, 1,
    "Main page body div should be created");
  bodyDiv.remove();
});

test("test_Login.arrangeFooter", function(assert) {
  expect(3);

  Login.message = 'Hello.';
  Login.pageModule = {
    'bodyDivId': 'test_page',
    'showLoggedInPage':
      function() {
      assert.ok(true, "Login callback should be called");
    },
  };

  BMTestUtils.setupFakeLogin();
  Login.arrangeFooter();
  BMTestUtils.cleanupFakeLogin();

  assert.equal($('#footer').length, 1,
    "Login footer div should be created");
});

test("test_Login.arrangePage", function(assert) {
  expect(5); // tests + 2 teardown

  Login.pageModule = { 'bodyDivId': 'test_page' };
  $('body').append($('<div>', {'id': Login.pageModule.bodyDivId, }));

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

  if (Login.pageModule) {
    $('#' + Login.pageModule.bodyDivId).remove();
  }
});


test("test_Login.getLoginForm", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Login.getLoginForm not implemented");
});

test("test_Login.stateLoggedIn", function(assert) {
  expect(6);
  Login.player = 'foobar';

  // mock Api.getNextNewPostId() because it is not the target of this test
  var cached_function = Api.getNextNewPostId;
  Api.getNextNewPostId = function(callback) {
    assert.equal(callback, Login.addMainNavbar, "Api.getNextNewPostId is called with expected callback");
  };

  Login.stateLoggedIn('example welcome text');
  var msgProps = BMTestUtils.DOMNodePropArray(Login.message[0]);
  var expectedMessage = [ "P", {}, [
      "example welcome text: You are logged in as foobar. ",
      [ "BUTTON", { "id": "login_action_button" }, [ "Logout?" ] ]
    ]
  ];
  assert.equal(Login.logged_in, true, "Login.logged_in is set to true");
  assert.equal(Login.form, Login.formLogout, "Login.form is set correctly");
  assert.deepEqual(msgProps, expectedMessage, "Login.message is set correctly");

  Api.getNextNewPostId = cached_function;
});

test("test_Login.stateLoggedOut", function(assert) {
  Login.status_type = Login.STATUS_NO_ACTIVITY;
  Login.stateLoggedOut('example welcome text');
  assert.equal(Login.logged_in, false, "Login.logged_in is set to false");
  assert.equal(Login.form, Login.formLogin, "Login.form is set correctly");
  var msgProps = BMTestUtils.DOMNodePropArray(Login.message[0]);

  var expectedMessage = [ "P", {}, [
      "example welcome text: ",
      "You are not logged in. ",
      [ "A", { "href": "create_user.html" }, [ "Create an account" ] ],
      " ",
      [ "A", { "href": "forgot_password.html" }, [ "(Forgot password?)" ] ]
    ]
  ];

  assert.deepEqual(msgProps, expectedMessage, "Login.message is set correctly after no login activity");

  var formElements = BMTestUtils.DOMNodePropArray(Login.formElements[0]);
  var expectedForm = [
    "DIV",
    { "class": "login" },
    [ [
        "FORM", { "action": "javascript:void(0);", "id": "login_action_form" },
        [
          "Username: ",
          [ "INPUT", { "id": "login_name", "name": "login_name", "type": "text" }, [] ],
          " Password: ",
          [ "INPUT", { "id": "login_pass", "name": "login_pass", "type": "password" }, [] ],
          " ",
          [ "BUTTON", { "id": "login_action_button" }, [ "Login" ] ],
          " ",
          [ "INPUT", { "id": "login_checkbox", "name": "login_checkbox", "type": "checkbox"}, [] ],
          "Keep me logged in"
      ] ]
    ]
  ];
  assert.deepEqual(formElements, expectedForm, "Login.formElements is set correctly after no login activity");

  Login.status_type = Login.STATUS_ACTION_SUCCEEDED;
  Login.stateLoggedOut('example welcome text');
  assert.equal(Login.logged_in, false, "Login.logged_in is set to false");
  assert.equal(Login.form, Login.formLogin, "Login.form is set correctly");
  msgProps = BMTestUtils.DOMNodePropArray(Login.message[0]);
  expectedMessage[2][1] = [ "FONT", { "color": "green" }, [ "Logout succeeded - login again? " ] ];
  assert.deepEqual(msgProps, expectedMessage, "Login.message is set correctly after successful logout");
  formElements = BMTestUtils.DOMNodePropArray(Login.formElements[0]);
  // james: expected form should not change here
  assert.deepEqual(formElements, expectedForm, "Login.formElements is set correctly after successful logout");

  Login.status_type = Login.STATUS_ACTION_FAILED;
  Login.stateLoggedOut('example welcome text');
  assert.equal(Login.logged_in, false, "Login.logged_in is set to false");
  assert.equal(Login.form, Login.formLogin, "Login.form is set correctly");
  msgProps = BMTestUtils.DOMNodePropArray(Login.message[0]);
  expectedMessage[2][1] = [
    "FONT", { "color": "red" }, [ "Login failed - username or password invalid, or email address has not been verified. " ] ];
  assert.deepEqual(msgProps, expectedMessage, "Login.message is set correctly after failed login");
  formElements = BMTestUtils.DOMNodePropArray(Login.formElements[0]);
  // james: expected form should not change here
  assert.deepEqual(formElements, expectedForm, "Login.formElements is set correctly after failed login");

  Login.status_type = 0;
});

test("test_Login.addMainNavbar", function(assert) {
  expect(4);
  Login.player = 'foobar';
  Login.message = $('<div>');

  // mock Login.addNewPostLink() because it is not the target of this test
  var cached_function = Login.addNewPostLink;
  Login.addNewPostLink = function(callback) {
    assert.ok(true, "Login.addNewPostLink is called");
  };

  Login.addMainNavbar();
  var msgProps = BMTestUtils.DOMNodePropArray(Login.message[0]);
  var expectedMessage = [ "DIV", {}, [
    [ "TABLE", {}, [
      [ "TBODY", {}, [
        [ "TR", { "class": "headerNav" }, [
          [ "TD", {}, [ [ "A", { "href": "index.html" }, [ "Overview" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "../ui/index.html?mode=monitor" }, [ "Monitor" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "create_game.html" }, [ "Create game" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "open_games.html" }, [ "Open games" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "tournaments.html" }, [ "Tournaments" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "prefs.html" }, [ "Preferences" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "profile.html?player=foobar" }, [ "Profile" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "history.html" }, [ "History" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "buttons.html" }, [ "Buttons" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "active_players.html" }, [ "Who's online" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "forum.html" }, [ "Forums" ] ] ] ],
          [ "TD", {}, [ [ "A", { "href": "../ui/index.html?mode=nextGame" }, [ "Next game" ] ] ] ] ]
        ] ]
      ] ]
    ] ]
  ];
  assert.deepEqual(msgProps, expectedMessage, "Login.message has expected contents");

  Login.addNewPostLink = cached_function;
});

test("test_Login.footerNavBar", function(assert) {
  var navbar = Login.footerNavBar();
  var expectedNavbar = [ "TABLE", {}, [
    [ "TBODY", {}, [
      [ "TR", { "class": "footerNav" }, [
        [ "TD", {}, [ [ "A", { "href": "help.html" }, [ "Help" ] ] ] ],
        [ "TD", {}, [ [ "A", { "href": "privacy.html" }, [ "Privacy" ] ] ] ]
      ] ]
    ] ],
    [ "BR" ]
  ] ];
  assert.deepEqual(BMTestUtils.DOMNodePropArray(navbar[0]), expectedNavbar, "footerNavbar has expected contents");
});

test("test_Login.addNewPostLink", function(assert) {
  Login.message = $('<table>');
  var navRow  = $('<tr>', { 'class': 'headerNav' });
  Login.message.append(navRow);
  var navTd = $('<td>');
  navRow.append(navTd);
  navTd.append($('<a>', { 'href': '#', 'text': 'Forums', }));

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
  navTd.append($('<a>', { 'href': '#', 'text': 'Forums', }));

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
