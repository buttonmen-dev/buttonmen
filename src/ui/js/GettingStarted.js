// namespace for this "module"
var GettingStarted = {};

GettingStarted.bodyDivId = 'getting_started_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * GettingStarted.showLoggedInPage() is the landing function. Always call
//   this first. It calls GettingStarted.showPage()
// * GettingStarted.showLoggedOutPage() is the other landing function.  Always
//   call this first when logged out.  It also calls GettingStarted.showPage()
// * GettingStarted.showPage() uses the data returned by the API to build
//   the contents of the page as GettingStarted.page and calls
//   Login.arrangePage()
////////////////////////////////////////////////////////////////////////

GettingStarted.showLoggedInPage = function() {
  GettingStarted.showPage();
};

// GettingStarted basically behaves roughly the same way regardless of whether
// or not you're logged in
GettingStarted.showLoggedOutPage = function() {
  GettingStarted.showLoggedInPage();
};

GettingStarted.showPage = function() {
  GettingStarted.page = $('<div>');

  GettingStarted.page.append(GettingStarted.bodyText());

  // Actually lay out the page
  Login.arrangePage(GettingStarted.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

GettingStarted.bodyText = function() {
  var bodyText = $('<div>').addClass('help_column');
  bodyText.append(GettingStarted.generalInfo());
  return bodyText;
};

GettingStarted.generalInfo = function() {
  var text = $('<div>');

  text.append(
    $('<h1>').text('Getting started')
  );

  text.append(
    $('<p>').html(
      'Content coming soon!'
    )
  );

  text.append(
    $('<p>').html(
      'To encourage us to work on this page or to contribute yourself, ' +
      'see the ' +
      '<a href="https://github.com/buttonmen-dev/buttonmen/issues/2467">' +
      'relevant issue</a> ' +
      'in our Github issue tracker.'
    )
  );

  return text;
};
