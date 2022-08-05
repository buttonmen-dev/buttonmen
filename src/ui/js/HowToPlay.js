// namespace for this "module"
var HowToPlay = {};

HowToPlay.bodyDivId = 'help_page';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * HowToPlay.showLoggedInPage() is the landing function. Always call
//   this first. It calls HowToPlay.showPage()
// * HowToPlay.showLoggedOutPage() is the other landing function.  Always call
//   this first when logged out.  It also calls HowToPlay.showPage()
// * HowToPlay.showPage() uses the data returned by the API to build
//   the contents of the page as HowToPlay.page and calls
//   Login.arrangePage()
////////////////////////////////////////////////////////////////////////

HowToPlay.showLoggedInPage = function() {
  HowToPlay.showPage();
};

// HowToPlay basically behaves roughly the same way regardless of whether or not
// you're logged in
HowToPlay.showLoggedOutPage = function() {
  HowToPlay.showLoggedInPage();
};

HowToPlay.showPage = function() {
  HowToPlay.page = $('<div>');

  HowToPlay.page.append(HowToPlay.bodyText());

  // Actually lay out the page
  Login.arrangePage(HowToPlay.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

HowToPlay.bodyText = function() {
  var bodyText = $('<div>').addClass('help_column');
  bodyText.append(
    $('<h1>').text('How to Play Button Men')
  );
  bodyText.append(HowToPlay.tableOfContents());
  bodyText.append(HowToPlay.info());
  return bodyText;
};

HowToPlay.tableOfContents = function() {
  var toc = $('<ul>');

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#DieSkills').text('Die skills')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#StartingTheGame').text('Starting the game')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#PlayingTheRound').text('Playing the round')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#Attacks').text('Attacks')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#Scoring').text('Scoring')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#TheNextRound').text('The next round')
    )
  );

  toc.append(
    $('<li>').append(
      $('<a>').attr('href', '#ReallyBasicStrategy')
              .text('Really basic strategy')
    )
  );

  return toc;
};

HowToPlay.info = function() {
  var bodytext = $('<div>');

  bodytext.append(
    $('<p>').text(
      'Button Men is a strategy dice game about beating people up, whether ' +
      'it\'s your friends or strange people on the Internet. It\'s quick ' +
      'and easy enough that each game can take only a few minutes in person. ' +
      'On this site, you can beat up a bunch of Internet strangers in ' +
      'parallel, for greater efficiency.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'While the rules are simple enough to pick up quickly, there\'s enough ' +
      'strategy to keep it interesting, and plenty of room for extra ' +
      'complexity to keep it fresh.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Each player has a character, which usually has art, an amusing ' +
      'description, and, most importantly, a recipe. The recipe is a set of ' +
      'dice, usually five of them. The simplest possible recipe would be ' +
      'something like:'
    )
  );

  bodytext.append(
    $('<p>').text(
      '(4) (4) (6) (10) (20)'
    ).addClass('die_details')
  );

  bodytext.append(
    $('<p>').text(
      'Five dice, each with a specified size. That\'s not all that ' +
      'interesting, so the basic recipe looks more like:'
    )
  );

  bodytext.append(
    $('<p>').text(
      '(4) (6) (10) (20) (X)'
    ).addClass('die_details')
  );

  bodytext.append(
    $('<p>').text(
      'The "(X)" is another die, called a Swing Die. At the start of the ' +
      'game, you secretly choose the size of the die, setting it to any ' +
      'size within the Swing Die\'s range. For an X swing, that\'s ' +
      'between 4 and 20. There are a bunch of available swing die ranges, ' +
      'but you don\'t need to worry about the rest of them for now.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'You are absolutely allowed to set your swing die to numbers that ' +
      'aren\'t a "real" die type. This can get awkward playing in real ' +
      'life, but we\'re on a computer, so we don\'t have to care. (You ' +
      'might also be surprised at how few die sizes don\'t exist. Serious ' +
      'Dice People will make 17-sided dice, just because they can.)'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Similar to Swing Dice, there are also Option Dice. They\'re written ' +
      'as two numbers separated by a slash, such as (4/12). Instead of ' +
      'letting you choose from a range, you can only pick between the ' +
      'available sizes.'
    )
  );

  bodytext.append(
    $('<p>').text(
      '(There are a few other ways to let players vary their recipes, but ' +
      'you don\'t need to worry about them right now.)'
    )
  );

  bodytext.append($('<a>').attr('name', 'DieSkills'));

  bodytext.append(
    $('<h2>').text(
      'Die skills'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Some dice have special abilities that change the way they work. On ' +
      'this site, they\'re indicated by adding letters to a die in the ' +
      'recipe, such as "c(10)", which is a 10-sided Chance die.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'We\'re not going to explain any die skills in this tutorial, but you ' +
      'should know that they exist, and that whenever we explain a rule, ' +
      'there\'s an implied "unless a die skill says differently".'
    )
  );

  bodytext.append($('<a>').attr('name', 'StartingTheGame'));

  bodytext.append(
    $('<h2>').text(
      'Starting the Game'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Once both players have picked their variable dice, they roll all ' +
      'their dice. The player who has the lowest number showing on a die ' +
      'goes first this round. If both players are tied for lowest, the ' +
      'next-lowest number goes first. (And so on, until you find a number ' +
      'that\'s different.)'
    )
  );

  bodytext.append(
    $('<p>').text(
      'So, if I roll:'
    )
  );

  bodytext.append(
    $('<p>').text(
      '1 3 6 7 20'
    ).addClass('die_details')
  );

  bodytext.append(
    $('<p>').text(
      'And you roll:'
    )
  );

  bodytext.append(
    $('<p>').text(
      '1 2 20 20 20'
    ).addClass('die_details')
  );

  bodytext.append(
    $('<p>').text(
      'We both have a 1, so we have to compare next-lowest dice. You have a ' +
      '2 and I have a 3, so you go first this round. The rest of your rolls ' +
      'are higher, but it doesn\'t matter.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'In the rare cases when the numbers match all the way, who goes first ' +
      'is determined randomly. (If you have more dice than me, and all the ' +
      'dice matched until I ran out, you would go first.)'
    )
  );

  bodytext.append($('<a>').attr('name', 'PlayingTheRound'));

  bodytext.append(
    $('<h2>').text(
      'Playing the Round'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Once we know who goes first, that player takes their turn. The dice ' +
      'we rolled earlier keep the numbers they rolled. On your ' +
      'turn, you will make an attack with one or more of your dice, and ' +
      'capture one of my dice. My die is removed from play and put into your ' +
      'score pile, and all the dice you attacked with are rerolled.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Once that happens, it\'s my turn, and I do the same. The round keeps ' +
      'going until either we both pass in turn, or one of us runs out of ' +
      'dice. At that point, we compare our scores to see who won the ' +
      'round. (Scoring will be explained later.)'
    )
  );

  bodytext.append(
    $('<p>').text(
      'On your turn, you must attack if you can. You only pass when you ' +
      'cannot attack. If there\'s a die you don\'t want to capture, ' +
      'you\'ll just have to make other attacks and hope that you can\'t ' +
      'attack it when the time comes. (Why wouldn\'t you want to capture ' +
      'a die? Die skills.)'
    )
  );

  bodytext.append($('<a>').attr('name', 'Attacks'));

  bodytext.append(
    $('<h2>').text(
      'Attacks'
    )
  );

  bodytext.append(
    $('<p>').text(
      'There are two types of attacks: Power and Skill.'
    )
  );

  bodytext.append(
    $('<h3>').text(
      'Power Attacks'
    )
  );

  bodytext.append(
    $('<p>').text(
      'In a Power attack, you use one of your dice to capture one of my dice ' +
      'that\'s showing a number equal to or lower than the number on the die ' +
      'you\'re attacking with.'
    )
  );

  bodytext.append(
    $('<h3>').text(
      'Skill Attacks'
    )
  );

  bodytext.append(
    $('<p>').text(
      'In a Skill attack, you use any number of your dice to capture a ' +
      'single die of mine. The numbers showing on your dice must add up ' +
      'to exactly the number showing on mine.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Yes, you can make a skill attack with a single die showing the same ' +
      'number as one of my dice. You could also make a power attack. Most of ' +
      'the time, it doesn\'t matter, and you won\'t have to specify which ' +
      'type of attack you made.'
    )
  );

  bodytext.append($('<a>').attr('name', 'Scoring'));

  bodytext.append(
    $('<h2>').text(
      'Scoring'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Once the round ends, we calculate our scores. Having dice left does ' +
      'not mean you automatically win the round.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Scores are calculated entirely on the size of the dice. The number ' +
      'showing doesn\'t matter at all.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Dice you captured from me are worth points equal to their size. Dice ' +
      'you still have in play are worth half their size in points. (Half ' +
      'points are allowed.)'
    )
  );

  bodytext.append(
    $('<p>').text(
      'On the site, both players\' current score will be tracked ' +
      'automatically. It will also tell you that you are plus or minus some ' +
      'number of \'sides\'. If I am currently plus six sides, then if you ' +
      'capture a six-sided die from me, the changes to our scores will leave ' +
      'us tied afterward.'
    )
  );

  bodytext.append(
    $('<p>').text(
      '(You would gain 6 for having captured it, while I would lose 3 for ' +
      'no longer having it in play. "Sides" makes it easier to think about, ' +
      'and is just as accurate until die skills get in the way.)'
    )
  );

  bodytext.append(
    $('<p>').text(
      'If the final scores are equal, then the round is a tie. There are no ' +
      'tie breakers.'
    )
  );

  bodytext.append($('<a>').attr('name', 'TheNextRound'));

  bodytext.append(
    $('<h2>').text(
      'The Next Round'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Games of Button Men are played until one player wins a specified ' +
      'number of rounds. On the site, it defaults to three, so you may have ' +
      'to play up to five games to see who wins. (Or even more if you manage ' +
      'to tie.)'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Once you\'ve determined the winner of the round, the loser gets to ' +
      'change the size of any variable dice they have. (This is not ' +
      'required, but it\'s an important part of strategy.) If there was a ' +
      'tie, neither player may change their dice.'
    )
  );

  bodytext.append(
    $('<p>').text(
      'Then, each player takes their dice, rolls them, and you go back to ' +
      'determining who goes first for the next round.'
    )
  );

  bodytext.append($('<a>').attr('name', 'ReallyBasicStrategy'));

  bodytext.append(
    $('<h2>').text(
      'Really Basic Strategy'
    )
  );

  bodytext.append(
    $('<ul>').append(
      $('<li>').text(
        'A large die like a 20-sider can often take several dice at the end, ' +
        'just by rolling high several times in a row. Save it for then if ' +
        'you can.'
      )
    ).append(
      $('<li>').text(
        'But it may be worth it to risk your big die to take your opponent\'s.'
      )
    ).append(
      $('<li>').text(
        'Look out for your opponent\'s skill attacks. Capturing your ' +
        'opponent\'s low-value die that is needed to capture your ' +
        'high-value die is usually better than capturing one of their ' +
        'higher-value dice.'
      )
    ).append(
      $('<li>').text(
        'Watch the \'sides\' difference. If you can take an opponent\'s ' +
        'die that pushes the difference higher than the remaining number ' +
        'of sides of your dice, do it. You will have won the round.'
      )
    )
  );

  bodytext.append(
    $('<p>').text(
      'Written by jl8e'
    ).addClass('byline')
  );

  return bodytext;
};
