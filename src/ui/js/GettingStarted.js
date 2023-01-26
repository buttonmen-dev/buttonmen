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
  GettingStarted.page = $('<div>');

  GettingStarted.page.append(GettingStarted.loggedOutBodyText());

  // Actually lay out the page
  Login.arrangePage(GettingStarted.page);
};

GettingStarted.showPage = function() {
  GettingStarted.page = $('<div>');

  GettingStarted.page.append(GettingStarted.bodyText());

  // Actually lay out the page
  Login.arrangePage(GettingStarted.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

GettingStarted.loggedOutBodyText = function() {
  var bodyText = $('<div>').addClass('help_column');
  bodyText.append(GettingStarted.loggedOutInfo());
  return bodyText;
};

GettingStarted.loggedOutInfo = function() {
  var text = $('<div>').addClass('help');

  text.append(
    $('<h1>').text('Getting started (when you\'re logged out)')
  );

  text.append(
    $('<p>').html(
      'If you haven\'t already created an account, go ahead and ' +
      '<a href="create_user.html">create an account</a>.'
    )
  );

  text.append(
    $('<p>').text(
      'Once you have created an account, verify it via the link that was ' +
      'sent to your email address. If you can\'t find the email (even ' +
      'after checking your spam folder) or you have any problems verifying ' +
      'your account, get in touch with us directly at help@buttonweavers.com.'
    )
  );

  text.append(
    $('<p>').text(
      'After verifying your account, you should be able to log in to the ' +
      'site. When you\'re logged in, visit this page again, and you\'ll ' +
      'be able to access our full tutorial.'
    )
  );

  return text;
};

GettingStarted.bodyText = function() {
  var bodyText = $('<div>').addClass('help_column');
  bodyText.append(GettingStarted.generalInfo());
  return bodyText;
};

GettingStarted.generalInfo = function() {
  var text = $('<div>').addClass('help');

  text.append(
    $('<h1>').text('Getting started')
  );

  text.append(GettingStarted.tableOfContents());

  text.append(GettingStarted.content());

  return text;
};

GettingStarted.tableOfContents = function() {
  var toc = $('<ul>');

  toc.append(GettingStarted.buttonmenLinks());
  toc.append(GettingStarted.siteLinks());
  toc.append(GettingStarted.whatNowLinks());

  return toc;
};

GettingStarted.buttonmenLinks = function() {
  var links = $('<li>').append(
    $('<a>').attr('href', '#ButtonMen').text('Learning about Button Men')
  );

  var sublinks = $('<ul>');

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#BasicRules').text(
      'Where can I learn about the basic rules?'
    )
  ));

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#Strategy').text(
      'How do I learn about Button Men strategy?'
    )
  ));

  links.append(sublinks);

  return links;
};

GettingStarted.siteLinks = function() {
  var links = $('<li>').append(
    $('<a>').attr('href', '#Site').text('Using this website')
  );

  var sublinks = $('<ul>');

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#JoinGame').text(
      'How do I join a new game?'
    )
  ));

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#ButtonProgression').text(
      'Which buttons should I start with?'
    )
  ));

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#StartGame').text(
      'How do I create a new game?'
    )
  ));

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#Profile').text(
      'How do I customise my profile?'
    )
  ));

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#Preferences').text(
      'How do I set my gameplay and site preferences?'
    )
  ));

  links.append(sublinks);

  return links;
};

GettingStarted.whatNowLinks = function() {
  var links = $('<li>').append(
    $('<a>').attr('href', '#WhatNow').text('What else should I do?')
  );

  return links;
};

GettingStarted.content = function() {
  var content = $(document.createDocumentFragment());

  content.append(GettingStarted.buttonmenContent());
  content.append(GettingStarted.siteContent());
  content.append(GettingStarted.whatNowContent());

  return content;
};

GettingStarted.buttonmenContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'ButtonMen'));
  content.append($('<h2>').text('Learning about Button Men'));

  content.append(GettingStarted.basicRulesContent());
  content.append(GettingStarted.strategyContent());

  return content;
};

GettingStarted.basicRulesContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'BasicRules'));
  content.append(
    $('<h3>').text('Where can I learn about the basic rules?')
  );
  content.append(
    $('<p>').html(
      'You can find the basic rules ' +
      '<a href="' +
      'https://buttonmen.fandom.com/wiki/Button_Men_Rules#The_Basics' +
      '">here</a>.'
    )
  );
  content.append(
    $('<p>').html(
      'There is also a tutorial: ' +
      '<a href="how_to_play.html">How to Play Button Men</a>.'
    )
  );

  return content;
};

GettingStarted.strategyContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Strategy'));
  content.append(
    $('<h3>').text('How do I learn about Button Men strategy?')
  );
  content.append(
    $('<p>').text(
      'Button Men is a game that has simple rules, but there can be ' +
      'great depth in trying to find a winning strategy. Many long-term ' +
      'players continue to discover ways to improve their play even after ' +
      'having played thousands of games. One of the best ways ' +
      'to learn the strategy is just to play lots of games.'
    )
  );
  content.append(
    $('<p>').html(
      'A number of articles were written about Button Men strategy in the ' +
      'early 2000s, and they can be found ' +
      '<a href="https://buttonmen.fandom.com/wiki/Button_Men_Strategy">' +
      'here</a>. There are also many discussions of Button Men strategy on ' +
      'the ' +
      '<a href="forum.html">forums</a>. Also, ' +
      '<a href="profile.html?player=ElihuRoot">ElihuRoot</a> has a ' +
      '<a href="https://buttonmen.blogspot.com">blog</a> that focuses ' +
      'on Button Men strategy.'
    )
  );

  return content;
};

GettingStarted.siteContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Site'));
  content.append($('<h2>').text('Using this website'));

  content.append(GettingStarted.joinGameContent());
  content.append(GettingStarted.buttonProgressionContent());
  content.append(GettingStarted.startGameContent());
  content.append(GettingStarted.profileContent());
  content.append(GettingStarted.preferencesContent());

  return content;
};

GettingStarted.joinGameContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'JoinGame'));
  content.append(
    $('<h3>').text('How do I join a new game?')
  );
  content.append(
    $('<p>').html(
      'You can find new games that have been created by other players by ' +
      'going to the <a href="open_games.html">Open Games</a> page ' +
      'via the navigation bar at the top of the page. ' +
      'You may join any of the open games listed by clicking on the ' +
      'corresponding "Join Game #####" button.'
    )
  );
  content.append(
    $('<p>').text(
      'To preview the recipe of either button, you can hover over the ' +
      'blue information icon if you\'re on a computer. Alternatively, ' +
      'if you click on the button name, you will be taken to the ' +
      'button description page, which has a full listing of all the ' +
      'skills used in the button recipe.'
    )
  );

  return content;
};

GettingStarted.buttonProgressionContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'ButtonProgression'));
  content.append(
    $('<h3>').text('Which buttons should I start with?')
  );
  content.append(
    $('<p>').text(
      'If you are new to Button Men, it is probably a good idea to start ' +
      'out by playing simple button recipes.'
    )
  );
  content.append(
    $('<p>').html(
      'The simplest recipes to understand are those with no special ' +
      'skills and one swing die. You can find such buttons in the ' +
      '<a href="buttons.html?set=Soldiers">Soldiers</a> and ' +
      '<a href="buttons.html?set=The Core">The Core</a> button sets.'
    )
  );
  content.append(
    $('<p>').html(
      'The next simplest recipes are those with one special skill. ' +
      'Many button sets were designed to use only one skill. Here ' +
      'is an abbreviated list of skills and the corresponding button sets.'
    )
  );

  var setlist = $('<ul>');
  setlist.append(
    $('<li>').html(
      'Berserk: <a href="buttons.html?set=Bruno">Bruno</a>'
    )
  );
  setlist.append(
    $('<li>').html(
      'Focus: <a href="buttons.html?set=Legend of the Five Rings">' +
      'Legend of the Five Rings</a>'
    )
  );
  setlist.append(
    $('<li>').html(
      'Poison: <a href="buttons.html?set=West Side">West Side</a>'
    )
  );
  setlist.append(
    $('<li>').html(
      'Reserve: Any of the Anime sets, which are ' +
      '<a href="buttons.html?set=Sailor Moon 1">Sailor Moon 1</a>, ' +
      '<a href="buttons.html?set=Sailor Moon 2">Sailor Moon 2</a>, and ' +
      '<a href="buttons.html?set=Tenchi Muyo!">Tenchi Muyo!</a>'
    )
  );
  setlist.append(
    $('<li>').html(
      'Rush: <a href="buttons.html?set=Uptown">Uptown</a>'
    )
  );
  setlist.append(
    $('<li>').html(
      'Shadow: <a href="buttons.html?set=Vampyres">Vampyres</a> and ' +
      '<a href="buttons.html?set=The Delta">The Delta</a>'
    )
  );
  setlist.append(
    $('<li>').html(
      'Speed: <a href="buttons.html?set=BRAWL">BRAWL</a>'
    )
  );
  setlist.append(
    $('<li>').html(
      'Trip: <a href="buttons.html?set=Lunch Money">Lunch Money</a>'
    )
  );

  content.append(setlist);

  content.append(
    $('<p>').html(
      'At some stage, you should also learn about option dice, which ' +
      'are similar to swing dice but a little simpler. ' +
      'You can find these in button sets like ' +
      '<a href="buttons.html?set=Fantasy">Fantasy</a> and ' +
      '<a href="buttons.html?set=Iron Chef">Iron Chef</a>.'
    )
  );

  content.append(
    $('<p>').html(
      'A full list of button sets, along with the skills used, can be ' +
      'found on the ' +
      '<a href="buttons.html">Buttons</a> page, which is accessible from ' +
      'the navigation bar at the top of the page.'
    )
  );

  content.append(
    $('<p>').html(
      'A full list of skills can be found on the ' +
      '<a href="skills.html">Die Skills and Types</a> page, which is accessible ' +
      'from the help page.'
    )
  );

  return content;
};

GettingStarted.startGameContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'StartGame'));
  content.append(
    $('<h3>').text('How do I create a new game?')
  );
  content.append(
    $('<p>').html(
      'You can create a new game by going to the ' +
      '<a href="create_game.html">Create game</a> page ' +
      'via the navigation bar at the top of the page. '
    )
  );
  content.append(
    $('<p>').html(
      'If you know which player you wish to challenge, ' +
      'select their name in the Opponent dropdown, otherwise ' +
      'leave the dropdown set to <em>Anybody</em>.'
    )
  );
  content.append(
    $('<p>').text(
      'The standard number of rounds is 3, but you can change ' +
      'this if you wish.'
    )
  );
  content.append(
    $('<p>').text(
      'Add a description if you wish. If you are looking for help or ' +
      'guidance, this is a good place to mention that you are a new player.'
    )
  );
  content.append(
    $('<p>').html(
      'Now choose the two buttons that will be used for this game. ' +
      'You can filter for these buttons using the button set filter.'
    )
  );
  content.append(
    $('<p>').html(
      'If you prefer, you can allow your opponent to choose their button. ' +
      'To do this, leave their button dropdown set to <em>Any button</em>.'
    )
  );
  content.append(
    $('<p>').html(
      'If you are feeling brave, one or both of the buttons can be ' +
      'selected at random. To do this, leave the corresponding button ' +
      'dropdown set to <em>Random button</em>. Beware, the button ' +
      'may end up having a really complicated recipe! ' +
      '(Note: <em>Random button</em> does not currently restrict its choice ' +
      'based on skill or set filters.)'
    )
  );
  content.append(
    $('<p>').html(
      'If you are looking for a really tough challenge, you may be ' +
      'interested in buttons with randomly generated recipes! These ' +
      'can be found in the button set ' +
      '<a href="buttons.html?set=RandomBM">RandomBM</a>. Choose ' +
      '<a href="buttons.html?button=RandomBMPentaskill">' +
      'RandomBMPentaskill</a> at your peril!'
    )
  );

  return content;
};

GettingStarted.profileContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Profile'));
  content.append(
    $('<h3>').text('How do I customise my profile?')
  );

  content.append(
    $('<p>').html(
      'Your profile page can be accessed via the navigation bar at the ' +
      'top of the page. Many elements of this page can be customised ' +
      'from the <em>Profile Settings</em> section of the ' +
      '<a href="prefs.html">Preferences</a> page, also accessible ' +
      'via the navigation bar.'
    )
  );

  return content;
};

GettingStarted.preferencesContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Preferences'));
  content.append(
    $('<h3>').text('How do I set my gameplay and site preferences?')
  );

  content.append(
    $('<p>').html(
      'Your gameplay and site preferences can be customised on the ' +
      '<a href="prefs.html">Preferences</a> page, which is accessible ' +
      'via the navigation bar at the top of the page.'
    )
  );
  content.append(
    $('<p>').html(
      'A number of preferences are described in detail on the ' +
      '<a href="faq.html">FAQ</a> page.'
    )
  );
  content.append(
    $('<p>').html(
      'When customising your colour scheme, avoid choosing ' +
      'pure black, white, and red, because this could make ' +
      'certain important parts of the game mat invisible.'
    )
  );

  return content;
};

GettingStarted.whatNowContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'WhatNow'));
  content.append($('<h2>').text('What else should I do?'));

  content.append(
    $('<p>').html(
      'Come and say hello in the <a href="forum.html">forums</a>, maybe ' +
      'put out feelers for a mentor if you\'d like one. Also, take a look ' +
      'for player-run tournaments, perhaps you\'d like to join one.'
    )
  );

  return content;
};
