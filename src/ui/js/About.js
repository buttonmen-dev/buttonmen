// namespace for this "module"
var About = {};

About.bodyDivId = 'about_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * About.showLoggedInPage() is the landing function. Always call
//   this first. It calls About.showPage()
// * About.showLoggedOutPage() is the other landing function.  Always call
//   this first when logged out.  It also calls About.showPage()
// * About.showPage() uses the data returned by the API to build
//   the contents of the page as About.page and calls
//   Login.arrangePage()
////////////////////////////////////////////////////////////////////////

About.showLoggedInPage = function() {
  About.showPage();
};

// About basically behaves roughly the same way regardless of whether or not
// you're logged in
About.showLoggedOutPage = function() {
  About.showLoggedInPage();
};

About.showPage = function() {
  About.page = $('<div>');

  About.page.append(About.bodyText());

  // Actually lay out the page
  Login.arrangePage(About.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

About.bodyText = function() {
  var bodyText = $('<div>').addClass('help_column');
  bodyText.append(About.generalInfo());
  return bodyText;
};

About.generalInfo = function() {
  var text = $('<div>');

  text.append(
    $('<h1>').text('About this site')
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
      '<a href="https://github.com/buttonmen-dev/buttonmen/issues/2468">' +
      'relevant issue</a> ' +
      'in our Github issue tracker.'
    )
  );

  return text;
};
