// namespace for this "module"
var Api = {
  'data': {},
};

////////////////////////////////////////////////////////////////////////
// This module should not layout a page or generate any HTML.  It exists
// only as a collection of routines which load and parse a particular
// type of data from the server.
//
// Each routine should be defined as: Api.getXData(callbackfunc),
// and should do these things:
// * call responder.php with arguments which load the requested type of
//   data from the server
// * call Api.parseXData(rs) to parse the response from the server
//   and populate Api.x in whatever way is desired
// * call the requested callback function no matter what happened with
//   the data load
// 
// Notes:
// * these routines may assume that the login header has already been
//   loaded, and therefore that the contents of Login.logged_in and
//   Login.player are available
// * these routines are not appropriate for form submission of actions
//   that change the server state --- they're only for loading and
//   parsing server data so page layout modules can use it
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// Load and parse a list of buttons

Api.getButtonData = function(callbackfunc) {
  Api.button = {
    'load_status': 'failed',
  };
  $.post('../api/responder.php',
         { type: 'loadButtonNames', },
         function(rs) {
           if (rs.message == 'All button names retrieved successfully.') {
             if (Api.parseButtonData(rs)) {
               Api.button.load_status = 'ok';
             } else {
               Env.message = {
                 'type': 'error',
                 'text': 'Could not parse button list from server',
               };
             }
           } else {
             Env.message = {
               'type': 'error',
               'text': 'Could not load button list from server',
             };
           }
           return callbackfunc();
         }
  ).fail(function() {
    Env.message = {
      'type': 'error',
      'text': 'Internal error when loading button list from server',
    };
    return callbackfunc();
  });
}

Api.parseButtonData = function(rs) {
  Api.button.list = {};
  if ((rs.buttonNameArray == null) || (rs.recipeArray == null)) {
    return false;
  }
  var i = 0;
  while (i < rs.buttonNameArray.length) {
    Api.button.list[rs.buttonNameArray[i]] = {
      'recipe': rs.recipeArray[i],
    };
    i++;
  }
  return true;
}

////////////////////////////////////////////////////////////////////////
// Load and parse a list of players

Api.getPlayerData = function(callbackfunc) {
  Api.player = {
    'load_status': 'failed',
  };
  $.post('../api/responder.php',
         { type: 'loadPlayerNames', },
         function(rs) {
           if (rs.message == 'Names retrieved successfully.') {
             if (Api.parsePlayerData(rs)) {
               Api.player.load_status = 'ok';
             } else {
               Env.message = {
                 'type': 'error',
                 'text': 'Could not parse player list from server',
               };
             }
           } else {
             Env.message = {
               'type': 'error',
               'text': 'Could not load player list from server',
             };
           }
           return callbackfunc();
         }
  ).fail(function() {
    Env.message = {
      'type': 'error',
      'text': 'Internal error when loading player list from server',
    };
    return callbackfunc();
  });
}

// Right now, we only get a list of names, but make a dict in case
// there's more data available later
Api.parsePlayerData = function(rs) {
  Api.player.list = {};
  if (rs.nameArray == null) {
    return false;
  }
  var i = 0;
  while (i < rs.nameArray.length) {
    Api.player.list[rs.nameArray[i]] = {
    };
    i++;
  }
  return true;
}

