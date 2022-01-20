// namespace for this "module"
var Help = {};

Help.bodyDivId = 'help_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Help.showLoggedInPage() is the landing function. Always call
//   this first. It calls Help.showPage()
// * Help.showLoggedOutPage() is the other landing function.  Always call
//   this first when logged out.  It also calls Help.showPage()
// * Help.showPage() uses the data returned by the API to build
//   the contents of the page as Help.page and calls
//   Login.arrangePage()
////////////////////////////////////////////////////////////////////////

Help.showLoggedInPage = function() {
  Help.showPage();
};

// Help basically behaves roughly the same way regardless of whether or not
// you're logged in
Help.showLoggedOutPage = function() {
  Help.showLoggedInPage();
};

Help.showPage = function() {
  Help.page = $('<div>');

  Help.page.append(Help.bodyText());

  // Actually lay out the page
  Login.arrangePage(Help.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

Help.bodyText = function() {
  var bodyText = $('<div>').addClass('help_column');
  bodyText.append(Help.generalInfo());
  bodyText.append(Help.developerInfo());
  return bodyText;
};

Help.generalInfo = function() {
  var text = $('<div>');

  text.append(
    $('<h1>').text('General help')
  );

  text.append(
    $('<p>').html(
      'We have a ' +
      '<a href="getting-started.html">tutorial to help you get started</a>' +
      ' using this site.'
    )
  );

  text.append(
    $('<p>').html(
      'Check out the ' +
      '<a href="skills.html">skills reference page</a>' +
      ' for details about how the various die and button skills work, ' +
      'interact with each other, work differently on this site ' +
      'than elsewhere, etc.'
    )
  );

  text.append(
    $('<p>').html(
      'Learn more <a href="about.html">about this project and this site</a>.'
    )
  );

  text.append(
    $('<p>').html(
      'The ' +
      '<a href="forum.html">forums</a>' +
      ' on this site are a good place to engage with the community. ' +
      'There\'s also a ' +
      '<a href="https://buttonweavers.fandom.com/">Buttonweavers wiki</a> ' +
      'and a ' +
      '<a href="https://buttonmen.fandom.com/">Button Men wiki</a>.'
    )
  );

  text.append(
    $('<p>').html(
      'Other questions not covered here? Try the <a href="faq.html">FAQ</a>.'
    )
  );

  text.append(
    $('<p>').html(
      'If you\'ve forgotten your password and need to reset it, use the ' +
      '"Forgot password?" link at the top of the page.'
    )
  );

  text.append(
    $('<p>').html(
      'If you\'re still stuck, contact us directly at help@buttonweavers.com.'
    )
  );

  return text;
};

Help.developerInfo = function() {
  var text = $('<div>');

  text.append(
    $('<h1>').text('For developers and testers')
  );

  text.append(
    $('<p>').html(
      'This site has ' +
      '<a href="https://github.com/buttonmen-dev/">a project on GitHub</a>' +
      ', and you can browse ' +
      '<a href="https://github.com/buttonmen-dev/buttonmen/">' +
      'the repository</a>' +
      ' there.'
    )
  );

  text.append(
    $('<p>').html(
      'If you\'d like to contribute to the project, ' +
      'one of the best things to do is come and chat to the developers on ' +
      '<a href="irc://irc.freenode.net/buttonmen">IRC</a>. ' +
      'You can also check out our ' +
      '<a href=' +
      '"https://github.com/buttonmen-dev/buttonmen/wiki/Developer-guide">' +
      'developer guide</a> ' +
      'and our ' +
      '<a href=' +
      '"https://github.com/buttonmen-dev/buttonmen/wiki/Tester-guide">' +
      'tester guide</a>. ' +
      'If you have a GitHub account, you can ' +
      '<a href="https://github.com/buttonmen-dev/buttonmen/issues">' +
      'view and create tickets</a>' +
      ' about bugs, feature requests, etc. Or just post to ' +
      '<a href="forum.html">the forums</a> ' +
      'and attract our attention over there.'
    )
  );

  return text;
};
