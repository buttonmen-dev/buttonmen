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

// If not logged in, display an option to login
// If logged in, set an element, #player_name
Login.getLoginHeader = function() {
  if (Login.status_type == 0) {
    Login.status_type = Login.STATUS_NO_ACTIVITY;
  }
  $.post('../api/responder.php',
         {type: 'loadPlayerName'},
         function(rs) {
           var player_name = null;
           if (rs.status == 'ok') {
             player_name = rs.data.userName;
           }
           Login.player = player_name;
           if (Login.player == null) {
             Login.stateLoggedOut();
           } else {
             Login.stateLoggedIn();
           }
           return Login.layoutHeader();
         }
  );
};

Login.showLoginHeader = function(callbackfunc) {
  // Save the callback function
  Login.callback = callbackfunc;

  // Make sure div elements that we will need exist in the page body
  if ($('#login_header').length == 0) {
    $('body').append($('<div>', {'id': 'login_header', }));
    $('body').append($('<hr>'));
  }

  // Find the current login header contents, and display them followed
  // by the specified callback routine
  Login.getLoginHeader();
}

Login.layoutHeader = function() {
  $('#login_header').empty();
  $('#login_header').append(Login.message);

  if (Login.form) {
    $('#login_action_button').click(Login.form);
  }
  return Login.callback();
}

// Get an empty form of the Login type
Login.getLoginForm = function() {
  var loginform = $('<form>', {
                      'id': 'login_action_form',
                      'action': "javascript:void(0);",
                    });
  return loginform;
}

////////////////////////////////////////////////////////////////////////
// One function for each possible logged in state
// The function should setup a header and a form

Login.stateLoggedIn = function() {
  Login.message = $('<p>', {
    'text': 'Welcome to ButtonMen: You are logged in as ' + Login.player + '. ',
  });

  var loginform = Login.getLoginForm();
  loginform.append($('<button>', {
                       'id': 'login_action_button',
                       'text': 'Logout?',
                     }));
  Login.form = Login.formLogout;

  Login.message.append(loginform);
  Login.logged_in = true;
}

Login.stateLoggedOut = function() {
  Login.message = $('<p>');
  Login.message.append('Welcome to ButtonMen: ');
  if (Login.status_type == Login.STATUS_ACTION_FAILED) {
    Login.message.append(
      $('<font>', {
          'color': Env.messageTypeColors['error'],
          'text': 'Login failed - username or password invalid',
        }));
  } else if (Login.status_type == Login.STATUS_ACTION_SUCCEEDED) {
    Login.message.append(
      $('<font>', {
          'color': Env.messageTypeColors['success'],
          'text': 'Logout succeeded - login again?',
        }));
  } else {
    Login.message.append('You are not logged in. ');
  }

  var loginform = Login.getLoginForm();
  loginform.append('Username: ');
  loginform.append($('<input>', {
                       'type': 'text',
                       'id': 'login_name',
                       'name': 'login_name',
                     }));
  loginform.append(' Password: ');
  loginform.append($('<input>', {
                       'type': 'password',
                       'id': 'login_pass',
                       'name': 'login_pass',
                     }));
  loginform.append(' ');
  loginform.append($('<button>', {
                       'id': 'login_action_button',
                       'text': 'Login',
                     }));

  Login.message.append(loginform);
  Login.form = Login.formLogin;
  Login.logged_in = false;
}

////////////////////////////////////////////////////////////////////////
// One function for each possible form action
// The function should contact the server and then redisplay the page

// Generic function which actually posts to responder.  All other forms
// should use this.
// The login header is too sparse to display status about success/failure
// of attempts to contact responder, so, for now, don't give the user
// any feedback, just redisplay the header no matter what.  (Fix this later.)
Login.postToResponder = function(responder_args) {
  $.post('../api/responder.php',
         responder_args,
         function(rs) {
           if (rs.status == 'ok') {
             Login.status_type = Login.STATUS_ACTION_SUCCEEDED;
             Env.message = null;
           } else {
             Login.status_type = Login.STATUS_ACTION_FAILED;
           }
           Login.showLoginHeader(Login.callback);
         }
  ).fail(function() {
           Login.status_type = Login.STATUS_ACTION_FAILED;
           Login.showLoginHeader(Login.callback);
         });
}

Login.formLogout = function() {
  var logoutargs = {
    'type': 'logout',
  };
  Login.postToResponder(logoutargs);
}

Login.formLogin = function() {
  var username = null;
  var password = null;
  $('input#login_name').each(function(index, element) {
    username = $(element).val();
  });
  $('input#login_pass').each(function(index, element) {
    password = $(element).val();
  });

  var loginargs = {
    'type': 'login',
    'username': username,
    'password': password,
  };
  Login.postToResponder(loginargs);
}
