// namespace for this "module"
var Loader = {};

// These scripts are loaded by every page, so we have them here by default
Loader.defaultScripts = [
  'js/extern/jquery.cookie.js',
  'js/extern/moment.js',
];

// These BM modules are loaded by every page
Loader.defaultBMModules = [
  'Api',
  'Config',
  'Env',
  'Login',
];

Loader.loadScripts = function(scripts, callback) {
  // The function to be called when all the scripts have been loaded.
  Loader.callback = callback;

  // Tracks which scripts are still loading and which have finished
  Loader.loadStatus = { };
  $.each(Loader.defaultScripts, function(index, script) {
    Loader.loadStatus[script] = false;
  });
  $.each(Loader.defaultBMModules, function(index, module) {
    var script = 'js/' + module + '.js';
    Loader.loadStatus[script] = false;
  });
  $.each(scripts, function(index, script) {
    Loader.loadStatus[script] = false;
  });

  // Load each script with $.getScript, passing it a callback that checks
  // whether or not it was the last script to finish loading
  $.each(Loader.loadStatus, function(script) {
    $.ajax({
      url: script,
      dataType: 'script',
      cache: true,
      success: function() {
        Loader.loadStatus[script] = true;
        var allScriptsAreLoaded = true;
        $.each(Loader.loadStatus, function(script, status) {
          if (!status) {
            allScriptsAreLoaded = false;
          }
        });
        // If this was the last one to finish, then start loading the page!
        if (allScriptsAreLoaded) {
          callback();
        }
      }
    });
  });
};
