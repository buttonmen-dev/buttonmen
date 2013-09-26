// namespace for this "module"
var Env = {};

// Courtesy of stackoverflow:
// http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values/5158301#5158301
Env.getParameterByName = function(name) {
    var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
    return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
}
