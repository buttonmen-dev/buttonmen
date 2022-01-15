// namespace for this "module"
var Login = {
  'status_type': 0,
};

// Login states
// N.B. These states cannot be used to determine whether the player
// is logged in.  They're only for deciding what status message to display.
Login.STATUS_NO_ACTIVITY      = 1;
Login.STATUS_ACTION_SUCCEEDED = 2;
Login.STATUS_ACTION_FAILED    = 3;

// This is used to refresh the Overview page if there's no next game
Login.nextGameRefreshCallback = false;

// Which module is responsible for loading the main part of the page
Login.pageModule = null;

// These elements contribute to the form, if there is a form
Login.formElements = null;

////////////////////////////////////////////////////////////////////////
//
// Action flow through every page:
// * Login.showLoginHeader() is the landing function. Always call this first. It
//   sets which module this page will be using (Overview, Game, History, etc.),
//   then calls Login.getLoginHeader()
// * Login.getLoginHeader() calls the API to see if the user is logged in and
//   constructs an appropriate header based on that. It then calls
//   Login.getFooter().
// * Login.getFooter() constructs the footer. Then it calls Login.getBody().
// * Login.getBody(), depending on A) whether or not the user is logged in and
//   B) whether or not the page module provides its own logged-out page,
//   either calls showLoggedInPage() or showLoggedOutPage() on the module
//   (each of which is expected to finish by calling Login.arrangePage())
//   *or* sets up a message that the user needs to log in and then calls
//   Login.arrangePage() itself.
// * Login.arrangePage() calls Login.arrangeHeader(), Login.arrangeBody() and
//   Login.arrangeFooter() to display everything that was constructed in the
//   previous three steps.
//
////////////////////////////////////////////////////////////////////////

// pageModule is the module that's responsible for loading the main part of the
// page, such as Overview or Game. It needs to have a bodyDivId property and
// a showLoggedInPage() method, and if it should be viewable when logged out,
// a showLoggedOutPage() method as well.
Login.showLoginHeader = function(pageModule) {
  // Note which module we're using for this page
  Login.pageModule = pageModule;

  // Check if this was an automatic redirect from the Monitor
  Api.automatedApiCall = (Env.getParameterByName('auto') == 'true');
  // Perform appendectomy (so a reload won't still register as automated)
  if (Api.automatedApiCall) {
    Env.removeParameterByName('auto');
  }

  // Make sure div elements that we will need exist in the page body
  if ($('#container').length === 0) {
    // create a container that will have 100% height, to allow correct
    // positioning of the page elements, especially the footer
    $('body').append($('<div>', {'id': 'container', }));
    $('#container').append($('<div>', {'id': 'c_header'}));
    $('#container').append($('<div>', {'id': 'c_body'}));
    $('#container').append($('<div>', {'id': 'c_footer'}));
    $('#c_header').append($('<div>', {'id': 'login_header', }));
    $('#c_header').append($('<hr>', { 'id': 'header_separator', }));
  }

  // Find the current login header contents and display them followed by
  // the main body of the page (via the current page module)
  Login.getLoginHeader();
};

// If not logged in, display an option to login
// If logged in, set an element, #player_name
Login.getLoginHeader = function() {
  if (Login.status_type === 0) {
    Login.status_type = Login.STATUS_NO_ACTIVITY;
  }
  $.post(
    Env.api_location,
    JSON.stringify({ 'type': 'loadPlayerName' }),
    function(rs) {
      var player_name = null;
      if (rs.status == 'ok') {
        player_name = rs.data.userName;
      }
      Login.player = player_name;
      var welcomeText = 'Welcome to Button Men';
      if (Config.siteType == 'development') {
        $('#login_header').css('background-color', '#cccccc');
        $('#favicon').attr('href', '/dev_favicon.ico');
        welcomeText += ' Dev Site';
      } else if (Config.siteType == 'staging') {
        $('#login_header').css('background-color', '#ffffcc');
        $('#favicon').attr('href', '/staging_favicon.ico');
        welcomeText += ' Staging Site';
      } else if (Config.siteType != 'production') {
        $('#login_header').css('background-color', '#ff7777');
        welcomeText += ' CONFIG ERROR';
      }
      if (Login.player === null) {
        Login.stateLoggedOut(welcomeText);
      } else {
        Login.stateLoggedIn(welcomeText);
      }

      Login.getFooter();
    }
  );
};

Login.getFooter = function() {
  Login.footer = $('<div>');

  Login.footer.append(Login.footerNavBar());

  var copyright = $('<div>');
  Login.footer.append(copyright);
  copyright.append(
    'Button Men is copyright 1999, 2021 James Ernest and Cheapass Games: ');
  copyright.append($('<a>', {
    'href': 'https://cheapass.com',
    'text': 'www.cheapass.com',
  }));
  copyright.append(' and ');
  copyright.append($('<a>', {
    'href': 'https://beatpeopleup.cheapass.com',
    'text': 'www.beatpeopleup.com',
  }));
  copyright.append(', and is used with permission.');

  Login.getBody();
};

Login.getBody = function() {
  if (Login.logged_in) {
    return Login.pageModule.showLoggedInPage();
  } else if (Login.pageModule.showLoggedOutPage) {
    Login.pageModule.showLoggedOutPage();
  } else {
    Env.message = {
      'type': 'error',
      'text': 'You must be logged in in order to view this page.',
    };
    Login.arrangePage();
  }
};

Login.arrangePage = function(page, form, submitSelector) {
  // Now that the player is being given control, we're no longer automated
  Api.automatedApiCall = false;

  Login.arrangeHeader();

  // Set up necessary elements for displaying status messages
  Env.setupEnvStub();

  Login.arrangeBody(page, form, submitSelector);

  Login.arrangeFooter();

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();
};

Login.arrangeHeader = function() {
  $('#login_header').empty();
  $('#login_header').append(Login.message);

  if (Login.formElements) {
    $('#login_header').append(Login.formElements);
  }

  if (Login.form) {
    $('#login_name').focus();
    $('#login_action_button').click(Login.form);
  }
};

Login.arrangeBody = function(page, form, submitSelector) {
  // Make sure the div element that we will need exists in the page body
  if (!Login.pageModule || !Login.pageModule.bodyDivId) {
    Env.message = {
      'type': 'error',
      'text':
        'This page failed to load. Your browser may have cached an outdated ' +
        'version of it. Try reloading the page, and if that doesn\'t work, ' +
        'please drop us a line at help@buttonweavers.com or file a bug ' +
        'report. Sorry for the inconvenience.',
    };
    return;
  }

  if ($('#' + Login.pageModule.bodyDivId).length === 0) {
    $('#c_body').append($('<div>', {
      'id': Login.pageModule.bodyDivId,
      'class': 'mainBody',
    }));
  }

  $('#' + Login.pageModule.bodyDivId).empty();
  $('#' + Login.pageModule.bodyDivId).append(page);

  if (form && submitSelector) {
    $(submitSelector).click(form);
  }
};

Login.arrangeFooter = function() {
  $('#c_footer').empty();
  $('#c_footer').append('<br />');
  $('#c_footer').append($('<hr>', { 'id': 'footer_separator', }));
  $('#c_footer').append($('<div>', {'id': 'footer', }));

  $('#footer').empty();
  $('#footer').append(Login.footer);
};

// Get an empty form of the Login type
Login.getLoginForm = function() {
  var loginform = $('<form>', {
    'id': 'login_action_form',
    'action': 'javascript:void(0);',
  });
  return loginform;
};

////////////////////////////////////////////////////////////////////////
// One function for each possible logged in state
// The function should set up a header and a form

Login.stateLoggedIn = function(welcomeText) {
  Login.formElements = null;
  Login.message = $('<p>');
  Login.message.append(
    welcomeText + ': You are logged in as ' + Login.player + '. '
  );
  Login.message.append($('<button>', {
    'id': 'login_action_button',
    'text': 'Logout?',
  }));

  Api.getNextNewPostId(Login.addMainNavbar);
  Login.form = Login.formLogout;
  Login.logged_in = true;
};

Login.stateLoggedOut = function(welcomeText) {
  Login.message = $('<p>');
  Login.message.append(welcomeText + ': ');
  if (Login.status_type == Login.STATUS_ACTION_FAILED) {
    Login.message.append(
      $('<font>', {
        'color': Env.messageTypeColors.error,
        'text': 'Login failed - username or password invalid, or email ' +
                'address has not been verified. ',
      }));
  } else if (Login.status_type == Login.STATUS_ACTION_SUCCEEDED) {
    Login.message.append(
      $('<font>', {
        'color': Env.messageTypeColors.success,
        'text': 'Logout succeeded - login again? ',
      }));
  } else {
    Login.message.append('You are not logged in. ');
  }

  Login.message.append($('<a>', {
    'href': 'create_user.html',
    'text': 'Create an account',
  }));
  Login.message.append(' ');
  Login.message.append($('<a>', {
    'href': 'forgot_password.html',
    'text': '(Forgot password?)',
  }));

  Login.formElements = $('<div>', {
    'class': 'login',
  });
  var loginExisting = Login.getLoginForm();
  loginExisting.append('Username: ');
  loginExisting.append($('<input>', {
    'type': 'text',
    'id': 'login_name',
    'name': 'login_name',
  }));
  loginExisting.append(' Password: ');
  loginExisting.append($('<input>', {
    'type': 'password',
    'id': 'login_pass',
    'name': 'login_pass',
  }));
  loginExisting.append(' ');
  loginExisting.append($('<button>', {
    'id': 'login_action_button',
    'text': 'Login',
  }));
  loginExisting.append(' ');
  loginExisting.append($('<input>', {
    'type': 'checkbox',
    'id': 'login_checkbox',
    'name': 'login_checkbox',
  }));
  loginExisting.append('Keep me logged in');

  Login.formElements.append(loginExisting);

  Login.form = Login.formLogin;
  Login.logged_in = false;
};

////////////////////////////////////////////////////////////////////////
// Helper functions which add text to the existing message

Login.addMainNavbar = function() {
  var navtable = $('<table>');
  var navrow = $('<tr>', { 'class': 'headerNav' });
  var links = {
    'Overview': 'index.html',
    'Monitor': Env.ui_root + 'index.html?mode=monitor',
    'Create game': 'create_game.html',
    'Open games': 'open_games.html',
    'Preferences': 'prefs.html',
    'Profile': Env.buildProfileLink(Login.player, true),
    'History': 'history.html',
    'Buttons': 'buttons.html',
    'Who\'s online': 'active_players.html',
    'Forum': 'forum.html',
    'Next game': Env.ui_root + 'index.html?mode=nextGame',
  };
  $.each(links, function(text, url) {
    var navtd = $('<td>');
    navtd.append($('<a>', { 'href': url, 'text': text }));
    navrow.append(navtd);
  });
  navrow.find('a:contains("Next game")').click(function(e) {
    e.preventDefault();
    Api.getNextGameId(Login.goToNextPendingGame);
  });
  navtable.append(navrow);
  Login.message.append(navtable);

  Login.addNewPostLink();
};

Login.footerNavBar = function() {
  var navtable = $('<table>');
  var navrow = $('<tr>', { 'class': 'footerNav' });
  var links = {
    'Help': 'help.html',
    'Privacy': 'privacy.html',
  };
  $.each(links, function(text, url) {
    var navtd = $('<td>');
    navtd.append($('<a>', { 'href': url, 'text': text }));
    navrow.append(navtd);
  });
  navtable.append(navrow);
  navtable.append($('<br>'));

  return navtable;
};

Login.addNewPostLink = function() {
  var navRow = Login.message.find('.headerNav');
  navRow.find('a:contains("(New post)")').parent().remove();

  if (Api.forumNavigation.nextNewPostId) {
    var newPostTd = $('<td>');
    newPostTd.append($('<a>', {
      'text': '(New post)',
      'href':
        'forum.html#!threadId=' + Api.forumNavigation.nextNewPostThreadId +
          '&postId=' + Api.forumNavigation.nextNewPostId,
      'class': 'pseudoLink',
      'data-threadId': Api.forumNavigation.nextNewPostThreadId,
      'data-postId': Api.forumNavigation.nextNewPostId,
    }));
    navRow.find('a:contains("Forum")').parent().after(newPostTd);
  }
};

////////////////////////////////////////////////////////////////////////
// One function for each possible form action
// The function should contact the server and then redisplay the page

// Generic function which actually posts to responder.  All other forms
// should use this.
// The login header is too sparse to display status about success/failure
// of attempts to contact responder, so, for now, don't give the user
// any feedback, just redisplay the header no matter what.  (Fix this later.)
Login.postToResponder = function(responder_args) {
  $.post(
    Env.api_location,
    JSON.stringify(responder_args),
    function(rs) {
      if (rs.status == 'ok') {
        Login.status_type = Login.STATUS_ACTION_SUCCEEDED;
        Env.message = null;
      } else {
        Login.status_type = Login.STATUS_ACTION_FAILED;
      }
      if (responder_args.type == 'logout') {
        Env.window.location.href = Env.ui_root;
      } else {
        Login.showLoginHeader(Login.pageModule);
      }
    }
  ).fail(
    function() {
      Login.status_type = Login.STATUS_ACTION_FAILED;
      Login.showLoginHeader(Login.pageModule);
    }
  );
};

Login.formLogout = function() {
  var logoutargs = {
    'type': 'logout',
  };
  Login.postToResponder(logoutargs);
};

Login.formLogin = function() {
  var username = null;
  var password = null;
  var doStayLoggedIn = false;
  $('input#login_name').each(function(_index, element) {
    username = $.trim($(element).val());
  });
  $('input#login_pass').each(function(_index, element) {
    password = $(element).val();
  });
  $('input#login_checkbox').each(function(_index, element) {
    doStayLoggedIn = $(element).is(':checked');
  });

  var loginargs = {
    'type': 'login',
    'username': username,
    'password': password,
    'doStayLoggedIn': doStayLoggedIn,
  };
  Login.postToResponder(loginargs);
};

////////////////////////////////////////////////////////////////////////
// Navigation events and helpers

// Redirect to the player's next pending game if there is one
Login.goToNextPendingGame = function() {
  // If we're making this call automatically for the monitor, keep track of that
  var appendix = '';
  if (Api.automatedApiCall) {
    appendix = '&auto=true';
  }

  if (Api.gameNavigation.load_status == 'ok') {
    if (Api.gameNavigation.nextGameId !== null &&
        $.isNumeric(Api.gameNavigation.nextGameId)) {
      Env.window.location.href =
        'game.html?game=' + Api.gameNavigation.nextGameId + appendix;
    } else {
      // If there are no active games, and we're on the Overview page, tell
      // the user so and refresh the list of games
      if (Login.nextGameRefreshCallback) {
        Env.message = {
          'type': 'none',
          'text': 'There are no games waiting for you to play'
        };
        Login.nextGameRefreshCallback();
      } else {
        // If we're not on the Overview page, send them there
        Env.window.location.href = '/ui/index.html?mode=preference' + appendix;
      }
    }
  } else {
    Env.message = {
      'type': 'error',
      'text': 'Your next game could not be found'
    };
    Env.showStatusMessage();
  }
};
