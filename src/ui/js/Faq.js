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
  var text = $('<div>').addClass('help');

  text.append(
    $('<h1>').text('Frequently asked questions')
  );

  text.append(Faq.tableOfContents());

  text.append(Faq.content());

  return text;
};

Faq.tableOfContents = function() {
  var toc = $('<ul>');

  toc.append(Faq.gameplayLinks());
  toc.append(Faq.userprefsLinks());
  toc.append(Faq.forumLinks());
  toc.append(Faq.unansweredLink());

  return toc;
};

Faq.gameplayLinks = function() {
  var links = $('<li>').append(
    $('<a>').attr('href', '#Gameplay').text('Gameplay')
  );

  var sublinks = $('<ul>');

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#DefaultAttack').text(
      'What is a default attack?'
    )
  ));
  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#ButtonSpecial').text(
      'What are button specials?'
    )
  ));
  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#FlyingSquirrel').text(
      'Why can\'t I perform a Skill attack with The Flying Squirrel?'
    )
  ));
  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#JapaneseBeetle').text(
      'Why can\'t I perform a Skill attack against The Japanese Beetle?'
    )
  ));

  links.append(sublinks);

  return links;
};

Faq.userprefsLinks = function() {
  var links = $('<li>').append(
    $('<a>').attr('href', '#Userprefs').text(
      'User Preferences'
    )
  );

  var sublinks = $('<ul>');

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#Monitor').text(
      'What does the monitor do?'
    )
  ));
  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#FireOvershooting').text(
      'What does "Enable fire overshooting for power attacks" do?'
    )
  ));

  links.append(sublinks);

  return links;
};

Faq.forumLinks = function() {
  var links = $('<li>').append(
    $('<a>').attr('href', '#Forum').text(
      'Forums'
    )
  );

  var sublinks = $('<ul>');

  sublinks.append($('<li>').append(
    $('<a>').attr('href', '#ForumLink').text(
      'How do I link to another forum post?'
    )
  ));

  links.append(sublinks);

  return links;
};

Faq.unansweredLink = function() {
  var link = $('<li>').append(
    $('<a>').attr('href', '#Unanswered').text(
      'Have a question that we haven\'t answered?'
    )
  );

  return link;
};

Faq.content = function() {
  var content = $(document.createDocumentFragment());

  content.append(Faq.gameplayContent());
  content.append(Faq.userprefsContent());
  content.append(Faq.forumContent());
  content.append(Faq.unansweredContent());

  return content;
};

Faq.gameplayContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Gameplay'));
  content.append($('<h2>').text('Gameplay'));

  content.append(Faq.defaultAttackContent());
  content.append(Faq.buttonSpecialContent());
  content.append(Faq.flyingSquirrelContent());
  content.append(Faq.japaneseBeetleContent());

  return content;
};

Faq.defaultAttackContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'DefaultAttack'));
  content.append(
    $('<h3>').text('What is a default attack?')
  );
  content.append($('<p>').text(
    'The default attack is the preselected attack that the game chooses ' +
    'for you. There are often several different attacks that can be ' +
    'executed with the same dice.'
  ));

  content.append($('<p>').text(
    'For example, a z(6):5 (i.e., a 6-sided Speed die showing 5) could ' +
    'capture an (8):5 (i.e., an 8-sided die showing 5) with any of the ' +
    'following attacks:'
  ));

  content.append(
    $('<ul>').append(
      $('<li>').text(
        'Power attack (since the value of the attacking die is at least as ' +
        'high as that of the defending die)'
      )
    ).append(
      $('<li>').text(
        'Skill attack (since the sum of the values of the attacking dice ' +
        'is exactly equal to the value of the defending die)'
      )
    ).append(
      $('<li>').text(
        'Speed attack (since the value of the attacking die is exactly ' +
        'equal to the sum of the values of the defending dice)'
      )
    )
  );

  content.append($('<p>').text(
    'It doesn\'t matter which attack you select, the result will always ' +
    'be the same: (8):5 will be captured and z(6):5 will reroll. In order ' +
    'to save you time, the page doesn\'t force you to select an attack and ' +
    'just executes the first in this list (the power attack). This is the ' +
    'default attack.'
  ));

  content.append($('<p>').text(
    'However, in some situations your choice might be relevant.'
  ));

  content.append($('<p>').text(
    'For example, a Dt(6):5 (i.e., a 6-sided die with the Trip skill and ' +
    'the Doppleganger skill) could capture an (8):5 with any of the ' +
    'following attacks:'
  ));

  content.append(
    $('<ul>').append(
      $('<li>').text(
        'Power attack (since the value of the attacking die is at least as ' +
        'high as that of the defending die)'
      )
    ).append(
      $('<li>').text(
        'Skill attack (since the sum of the values of the attacking dice ' +
        'is exactly equal to the value of the defending die)'
      )
    ).append(
      $('<li>').text(
        'Trip attack'
      )
    )
  );

  content.append($('<p>').text(
    'Now it matters which attack you select:'
  ));

  content.append(
    $('<ul>').append(
      $('<li>').text(
        'If you select the power attack, the (8):5 will be captured and ' +
        'the attacking die will turn into an (8) due to the ' +
        'Doppelganger skill.'
      )
    ).append(
      $('<li>').text(
        'If you select the skill attack, the (8):5 will be captured but ' +
        'the attacking die will remain a Dt(6), since the Doppelganger ' +
        'skill doesn\'t trigger.'
      )
    ).append(
      $('<li>').text(
        'If you select the trip attack, both dice reroll and the ' +
        '(8):5 might not even be captured.'
      )
    )
  );

  content.append($('<p>').text(
    'If you select the default attack in this situation, the game will ' +
    'ask you to choose which of these three attacks you intend to perform.'
  ));

  content.append($('<p>').text(
    'In short, you never have to worry about the default attack. If it ' +
    'matters which attack you select, the game will tell you to be more ' +
    'specific when selecting the type of attack.'
  ));

  return content;
};

Faq.buttonSpecialContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'ButtonSpecial'));
  content.append(
    $('<h3>').text(
      'What are button specials?'
    )
  );

  content.append($('<p>').text(
    'As you are playing Button Men, you may find that there is sometimes ' +
    'a help line for "Button specials" above the description of the die ' +
    'skills. This is a visual reminder that one or both of the buttons in ' +
    'play has triggered some special rules. These special button-specific ' +
    'rules are described in the in-game help mouseovers and on the ' +
    'corresponding button page.'
  ));

  content.append($('<p>').html(
    'For example, check out ' +
    '<a href="buttons.html?button=Giant">Giant</a>' + ', ' +
    '<a href="buttons.html?button=Echo">Echo</a>' + ', or ' +
    '<a href="buttons.html?button=Gordo">Gordo</a>' +
    '.'
  ));

  return content;
};

Faq.flyingSquirrelContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'FlyingSquirrel'));
  content.append(
    $('<h3>').text(
      'Why can\'t I perform a Skill attack with The Flying Squirrel?'
    )
  );

  content.append($('<p>').html(
    '<a href="buttons.html?button=The Flying Squirrel">' +
    'The Flying Squirrel</a> has a ' +
    '<a href="#ButtonSpecial">button special</a> ' +
    'that says that it can\'t do Skill attacks. Too bad. At least it has ' +
    'Speed dice.'
  ));

  return content;
};

Faq.japaneseBeetleContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'JapaneseBeetle'));
  content.append(
    $('<h3>').text(
      'Why can\'t I perform a Skill attack against The Japanese Beetle?'
    )
  );

  content.append($('<p>').html(
    '<a href="buttons.html?button=The Japanese Beetle">' +
    'The Japanese Beetle</a> has a ' +
    '<a href="#ButtonSpecial">button special</a> ' +
    'that says that it can\'t be attacked by Skill attacks. Too bad. ' +
    'At least he only has four dice.'
  ));

  return content;
};

Faq.userprefsContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Userprefs'));
  content.append($('<h2>').text('User Preferences'));

  content.append(Faq.monitorContent());
  content.append(Faq.fireOvershootingContent());

  return content;
};

Faq.monitorContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Monitor'));
  content.append(
    $('<h3>').text(
      'What does the monitor do?'
    )
  );

  content.append($('<p>').text(
    'The monitor checks once a minute to see if anything of potential ' +
    'interest has changed on the site.'
  ));

  content.append($('<p>').text(
    'There are three preferences that affect the behaviour of the monitor:'
  ));

  content.append(
    $('<ul>').append(
      $('<li>').text(
        '"Redirect to waiting games when in Monitor mode": If there is at ' +
        'least one game waiting for you to act, the monitor will ' +
        'automatically redirect the browser to such a game.'
      )
    ).append(
      $('<li>').text(
        '"Redirect to new forum posts when in Monitor mode": If there is at ' +
        'least one new forum post, the monitor will automatically redirect ' +
        'the browser to a new forum post.'
      )
    ).append(
      $('<li>').text(
        '"Automatically Monitor after "Next game" runs out": If you are ' +
        'navigating by using the "Next game" links and there is no game left ' +
        'requiring you to act, the browser will trigger the monitor.'
      )
    )
  );

  return content;
};

Faq.fireOvershootingContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'FireOvershooting'));
  content.append(
    $('<h3>').text(
      'What does "Enable fire overshooting for power attacks" do?'
    )
  );

  content.append($('<p>').text(
    'Fire dice can be turned down to help with skill attacks or power attacks.'
  ));

  content.append($('<p>').text(
    'Usually, if you want to use it to help with a power attack, the sum ' +
    'of the values of the attacking dice you select will be less than the ' +
    'value of the defending die and the game will know you wish to turn ' +
    'down a Fire die.'
  ));

  content.append($('<p>').text(
    'If you decide to attack a (8):4 (i.e., an 8-sided die showing 4) with ' +
    'a (6):3 (i.e., a 6-sided die showing 3), you could turn down a Fire ' +
    'die by one and increase the (6):3 to a (6):4. This is usually the ' +
    'best option.'
  ));

  content.append($('<p>').text(
    'You could also turn a Fire die by two and increase the (6):3 to a ' +
    '(6):5. This is called fire overshooting. However, this additional ' +
    'point is usually lost, since the (6):5 rerolls after the attack. ' +
    'Still, you could. For example, if you had an F(6):4 as well, the ' +
    'game would ask you to turn down your Fire die (and turn up your ' +
    '(6):5) by a value between 1 and 3.'
  ));

  content.append($('<p>').text(
    'You could also do this if the attacking die already showed a higher ' +
    'value than the defending one. For example, if you had a (6):5 ' +
    'attacking an (8):4, you could still use a Fire die to turn the (6):5 ' +
    'up to show 6. Again, this additional point is usually lost when rerolling.'
  ));

  content.append($('<p>').text(
    'In some very specific situations, you might still think it good to do ' +
    'this (or maybe you just want to see the world burn). In this case, you ' +
    'should have "Enable fire overshooting for power attacks" activated, ' +
    'otherwise the game will not prompt you to turn down Fire dice when a ' +
    'normal Power attack would be valid.'
  ));

  content.append($('<p>').text(
    'The reason this is not activated by default is that in most situations, ' +
    'fire overshooting is unnecessary and the game doesn\'t want to burden ' +
    'you with that choice any time you wish to perform a perfectly ' +
    'reasonable Power attack with a (20):19 against a (4):1 just because ' +
    'you happen to also have a Fire die.'
  ));

  return content;
};

Faq.forumContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Forum'));
  content.append($('<h2>').text('Forums'));

  content.append(Faq.forumLinkContent());

  return content;
};

Faq.forumLinkContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'ForumLink'));
  content.append(
    $('<h3>').text(
      'How do I link to another forum post?'
    )
  );

  content.append($('<p>').text(
    'When you are posting in a forum, use the [forum] element to link to ' +
    'another forum post. The syntax is [forum=t,p]text[/forum], where text ' +
    'is the text that appears as the link. The number t is the thread id, ' +
    'and the number p is the post id. The numbers appear at the bottom of ' +
    'the post that you want to link to, in the footer of the post in a ' +
    'very light color, to the left of the Quote button. '
  ));

  content.append($('<p>').html(
    'For example, use the syntax [forum=93,1301]Learn about adopting a ' +
    'button[/forum] to generate a link that looks like ' +
    '<a href="forum.html#!threadId=93&postId=1301">Learn about adopting a ' +
    'button</a>.'
  ));

  content.append($('<p>').text(
    'If you want to refer to another post in the same thread, you can ' +
    'either use the [forum] element or simply use the Quote ' +
    'button instead.'
  ));

  return content;
};

Faq.unansweredContent = function() {
  var content = $(document.createDocumentFragment());

  content.append($('<a>').attr('name', 'Unanswered'));
  content.append(
    $('<h3>').text(
      'Have a question that we haven\'t answered?'
    )
  );

  content.append($('<p>').text(
    'If you haven\'t found an answer here, feel free to ask in the forums. '
  ));

  content.append($('<p>').html(
    'Once you have an answer, consider helping us out by letting us know on ' +
    'the ' +
    '<a href="https://github.com/buttonmen-dev/buttonmen/issues/2469">' +
    'relevant issue</a> ' +
    'in our Github issue tracker.'
  ));

  return content;
};
