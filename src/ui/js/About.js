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
      'This website is a place where people play and talk about ' +
      '<a href="http://cheapass.com/free-games/button-men">Button Men</a>' +
      ', a game by ' +
      '<a href="http://cheapass.com/">Cheapass Games</a>.'
    )
  );

  text.append(
    $('<p>').html(
      'Many of us have fond memories of the old Button Men Online site run ' +
      'by Dana Huyler. This site is our way of keeping that community alive.'
    )
  );

  text.append(
    $('<p>').html(
      'The history of this site can be found in the following blog posts:'
    )
  );

  text.append(
    $('<ul>').append(
      $('<li>').html(
        '<a href="http://buttonweavers.blogspot.com/2017/06/why-php.html">' +
          'Why PHP? A brief history of the resurrection of Button Men Online' +
        '</a>'
      )
    ).append(
      $('<li>').html(
        '<a href="http://buttonweavers.blogspot.com/2018/01/' +
          'a-short-history-of-closed-alpha-testing.html">' +
          'A short history of the closed alpha testing period' +
        '</a>'
      )
    )
  );

  text.append(
    $('<p>').html(
      'Games tend to be played at a relaxed pace. We get to know each other ' +
      'mostly through in-game chat and the forums.'
    )
  );

  text.append(
    $('<p>').html(
      'The site is available for all to join at no cost, and we will never ' +
      'use a pay-to-win model.'
    )
  );

  text.append(
    $('<p>').html(
      'The site is proudly run, developed, and maintained by community ' +
      'volunteers. The underlying source code is open source and can be ' +
      'found on ' +
      '<a href="https://github.com/buttonmen-dev/buttonmen">Github</a>.'
    )
  );

  return text;
};
