// namespace for this "module"
var Faq = {};

Faq.bodyDivId = 'faq_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Faq.showLoggedInPage() is the landing function. Always call
//   this first. It calls Faq.showPage()
// * Faq.showLoggedOutPage() is the other landing function.  Always call
//   this first when logged out.  It also calls Faq.showPage()
// * Faq.showPage() uses the data returned by the API to build
//   the contents of the page as Faq.page and calls
//   Login.arrangePage()
////////////////////////////////////////////////////////////////////////

Faq.showLoggedInPage = function() {
  Faq.showPage();
};

// Faq basically behaves roughly the same way regardless of whether or not
// you're logged in
Faq.showLoggedOutPage = function() {
  Faq.showLoggedInPage();
};

Faq.showPage = function() {
  Faq.page = $('<div>');

  Faq.page.append(Faq.bodyText());

  // Actually lay out the page
  Login.arrangePage(Faq.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

Faq.bodyText = function() {
  var bodyText = $('<div>').addClass('help_column');
  bodyText.append(Faq.generalInfo());
  return bodyText;
};

Faq.generalInfo = function() {
  var text = $('<div>');

  text.append(
    $('<h1>').text('Frequently asked questions')
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
      '<a href="https://github.com/buttonmen-dev/buttonmen/issues/2469">' +
      'relevant issue</a> ' +
      'in our Github issue tracker.'
    )
  );

  return text;
};
