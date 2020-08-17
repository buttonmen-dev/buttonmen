// namespace for this "module"
var Privacy = {};

Privacy.bodyDivId = 'privacy_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Privacy.showLoggedInPage() is the landing function. Always call
//   this first. It calls Privacy.showPage()
// * Privacy.showLoggedOutPage() is the other landing function.  Always call
//   this first when logged out.  It also calls Privacy.showPage()
// * Privacy.showPage() uses the data returned by the API to build
//   the contents of the page as Privacy.page and calls
//   Login.arrangePage()
////////////////////////////////////////////////////////////////////////

Privacy.showLoggedInPage = function() {
  Privacy.showPage();
};

// Privacy basically behaves roughly the same way regardless of whether or not
// you're logged in
Privacy.showLoggedOutPage = function() {
  Privacy.showLoggedInPage();
};

Privacy.showPage = function() {
  Privacy.page = $('<div>');

  Privacy.page.append(Privacy.bodyText());

  // Actually lay out the page
  Login.arrangePage(Privacy.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

Privacy.bodyText = function() {
  var bodyText = $('<div>').addClass('help_column');
  bodyText.append(Privacy.generalInfo());
  return bodyText;
};

Privacy.generalInfo = function() {
  var text = $('<div>').addClass('help');

  text.append(
    $('<h1>').text('Privacy policy')
  );

  text.append(Privacy.tableOfContents());

  text.append(Privacy.content());

  return text;
};

Privacy.tableOfContents = function() {
  var toc = $('<ul>');

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#PersonalData').text('Personal data')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#Cookies').text('Cookies')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#Comments').text('Comments')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#Retain').text('How long we retain your data')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#Rights').text('Your rights over your data')
    )
  );

  return toc;
};

Privacy.content = function() {
  var content = $(document.createDocumentFragment());

  content.append(Privacy.personalDataContent());
  content.append(Privacy.cookiesContent());
  content.append(Privacy.commentsContent());
  content.append(Privacy.retainContent());
  content.append(Privacy.rightsContent());

  return content;
};

Privacy.personalDataContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'PersonalData'));
  content.append($('<h2>').text('Personal data'));

  content.append($('<p>').text(
    'To create an account on buttonweavers.com, you need to provide us ' +
    'with a valid e-mail address. This is used to allow the site to ' +
    'provide you with a password reset link if you forget your password. ' +
    'It may also be used to allow a site administrator to contact you ' +
    'with important news about the website, like a major site disruption.'
  ));

  content.append($('<p>').text(
    'In your personal preferences, you may choose to make more ' +
    'information available about yourself on your personal profile. Such ' +
    'information should be considered to be publicly available, even if ' +
    'you have attempted to hide this information from other players.'
  ));

  return content;
};

Privacy.cookiesContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Cookies'));
  content.append($('<h2>').text('Cookies'));

  content.append($('<p>').text(
    'You may opt-in to stay logged into the site, which will set a ' +
    'number of cookies on your browser. These cookies last for one year, ' +
    'unless you log out of your account, in which case the login cookies ' +
    'will be removed.'
  ));

  return content;
};

Privacy.commentsContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Comments'));
  content.append($('<h2>').text('Comments'));

  content.append($('<p>').text(
    'It is possible to leave comments in-game and on the forums. These ' +
    'should be considered to be publicly accessible, even if the in-game ' +
    'chat has been set to private.'
  ));

  return content;
};

Privacy.retainContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Retain'));
  content.append($('<h2>').text('How long we retain your data'));

  content.append($('<p>').text(
    'All users can see, edit, or delete their personal information at ' +
    'any time, except that they cannot change their username. Website ' +
    'administrators can also see and edit that information. However, ' +
    'edited or deleted data will still be available on backups and ' +
    'should be considered to be retained indefinitely.'
  ));

  return content;
};

Privacy.rightsContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Rights'));
  content.append($('<h2>').text('Your rights over your data'));

  content.append($('<p>').text(
    'If you have an account on this site, you can request to receive an ' +
    'exported file of the personal data we hold about you, including any ' +
    'data you have provided to us. You can also request that we erase ' +
    'any personal data we hold about you. This does not include any data ' +
    'we are obliged to keep for administrative, legal, or security purposes.'
  ));

  content.append($('<p>').text(
    'We will not use your personal data for any purpose other than that ' +
    'necessary to administer the buttonweavers.com website. We will not ' +
    'share unanonymised or personal data with third parties.'
  ));

  content.append($('<p>').text(
    'Comments in the forum and in-game chat are not considered to be ' +
    'personal data. However, exceptional requests for a transcript ' +
    '(and possibly deletion) of such data will be considered on a ' +
    'case-by-case basis.'
  ));

  return content;
};
