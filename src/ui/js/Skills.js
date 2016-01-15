// namespace for this "module"
var Skills = {};

Skills.bodyDivId = 'skills_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Skills.showLoggedInPage() is the landing function. Always call
//   this first. It calls Skills.showPage()
// * Skills.showLoggedOutPage() is the other landing function.  Always call
//   this first when logged out.  It also calls Skills.showPage()
// * Skills.showPage() uses the data returned by the API to build
//   the contents of the page as Skills.page and calls
//   Login.arrangePage()
////////////////////////////////////////////////////////////////////////

Skills.showLoggedInPage = function() {
  Skills.showPage();
};

// Skills basically behaves roughly the same way regardless of whether or not
// you're logged in
Skills.showLoggedOutPage = function() {
  Skills.showLoggedInPage();
};

Skills.showPage = function() {
  Skills.page = $('<div>');

  Skills.page.append(Skills.bodyText());

  // Actually lay out the page
  Login.arrangePage(Skills.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

Skills.bodyText = function() {
  var bodyText = $('<div>').addClass('help_column');
  bodyText.append(Skills.generalInfo());
  return bodyText;
};

Skills.generalInfo = function() {
  var text = $('<div>');

  text.append(
    $('<h1>').text('Die and button skills')
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
      '<a href="https://github.com/buttonmen-dev/buttonmen/issues/1925">' +
      'relevant issue</a> ' +
      'in our Github issue tracker.'
    )
  );

  return text;
};
