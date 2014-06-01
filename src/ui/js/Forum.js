// namespace for this "module"
var Forum = {
  'scrollTarget': undefined,
};

Forum.OPEN_STAR = '&#9734;';
Forum.SOLID_STAR = '&#9733;';

// I believe that a mySQL TEXT column can hold up to 2^16 - 1 bytes of UTF-8
// text, and a UTF-8 character can theoretically be up to four bytes wide (even
// if this is rare in practice), so our post bodies should be guaranteed to be
// able to hold at least (2^16 - 1)/4 characters.
Forum.BODY_MAX_LENGTH = 16383;
Forum.TITLE_MAX_LENGTH = 100;

Forum.SCROLL_ANIMATION_MILLISECONDS = 200;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Forum.showForumPage() is the landing function. Always call
//   this first. It sets up #forum_page and reads the URL to find out
//   the current board, thread and/or post, which it sets in Env.history.state.
//   It also binds Forum.showPage() to the page event that triggers on the
//   forward/backward button. Then it calls Forum.showPage() directly.
// * Forum.showPage() reads Env.history.state to find out what it should be
//   displaying. Then it calls the API to set either Api.forum_overview,
//   Api.forum_board or Api.forum_thread as appropriate, then passes control
//   to Forum.showOverview(), Forum.showBoard() or Forum.showThread().
// * Forum.showOverview() builds a version of Forum.page that includes a list
//   of boards on the Forum. Then it calls Forum.layOutPage().
// * Forum.showBoard() builds a version of Forum.page that includes a list
//   of threads on a given board and a form to create a new one (attaching the
//   Forum.postNewThread() event to it). Then it calls Forum.layOutPage().
// * Forum.showThread() builds a version of Forum.page that includes a list
//   of posts on a given thread and a form to create a new one (attaching the
//   Forum.replyToThread() event to it). Then it calls Forum.layOutPage().
// * History.layoutPage() sets the contents of <div id="history_page">
//   on the live page. It also binds Forum.linkToSubPage to every .pseudoLink
//   element (e.g., the links to a given board or thread).
//
// Major events:
// * Forum.linkToSubPage() is called every time a user clicks on an internal
//   link that brings them from one part of the forum to another. It examines
//   the element that was clicked on to find out the board, thread and/or post
//   it's posting to, sets that information in Env.history.state, then calls
//   Form.showPage().
// * Forum.postNewThread() calls the API to create a new thread, setting
//   Api.forum_thread with the results. It then calls Forum.showThread().
// * Forum.replyToThread() calls the API to create a new post, setting
//   Api.forum_thread with the results. It then calls Forum.showThread().
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// These functions are part of the main action flow to load the page

Forum.showForumPage = function() {
  // Setup necessary elements for displaying status messages
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#forum_page').length === 0) {
    $('body').append($('<div>', {'id': 'forum_page', }));
  }

  $(window).bind('popstate', Forum.showPage);

  var state = {
    'boardId': Env.getParameterByName('boardId'),
    'threadId': Env.getParameterByName('threadId'),
    'postId': Env.getParameterByName('postId'),
  };
  Env.history.replaceState(state, 'Button Men Online &mdash; Forum',
    Env.window.location.hash);
  Forum.showPage();
};

Forum.showPage = function() {
  if (!Login.logged_in) {
    Env.message = {
      'type': 'error',
      'text': 'Can\'t view the forum because you\'re not logged in',
    };
    Forum.layOutPage();
    return;
  }

  // Display the appropriate version of the page depending on the current state
  var state = Env.history.state;
  if (state.threadId) {
    Api.loadForumThread(state.threadId, state.postId, Forum.showThread);
  } else if (state.boardId) {
    Api.loadForumBoard(state.boardId, Forum.showBoard);
  } else {
    Api.loadForumOverview(Forum.showOverview);
  }
};

Forum.showOverview = function() {
  Forum.page = $('<div>', { 'class': 'forum' });
  if (!Api.verifyApiData('forum_overview', Forum.layOutPage)) {
    return;
  }

  var table = $('<table>', { 'class': 'boards' });
  Forum.page.append(table);

  var headingTr = $('<tr>');
  table.append(headingTr);
  var headingTd = $('<td>', { 'class': 'heading' });
  headingTr.append(headingTd);

  var breadcrumb = $('<div>', { 'class': 'breadcrumb' });
  headingTd.append(breadcrumb);
  breadcrumb.append($('<span>', {
    'class': 'mainBreadrumb',
    'text': 'Button Men Forums',
  }));

  headingTr.append($('<td>', { 'class': 'notes', 'html': '&nbsp;', }));

  $.each(Api.forum_overview.boards, function(index, board) {
    table.append(Forum.buildBoardRow(board));
  });

  var markReadDiv = $('<div>', { 'class': 'markRead' });
  Forum.page.append(markReadDiv);
  var markReadButton = $('<input>', {
    'type': 'button',
    'value': 'Mark all boards as read',
  });
  markReadDiv.append(markReadButton);
  markReadButton.click(function() {
    Api.markForumRead(Forum.showOverview);
  });

  // Actually lay out the page
  Forum.layOutPage();
};

Forum.showBoard = function() {
  Forum.page = $('<div>', { 'class': 'forum' });
  if (!Api.verifyApiData('forum_board', Forum.layOutPage)) {
    return;
  }

  var table = $('<table>', {
    'class': 'threads ' + Api.forum_board.shortName
  });
  Forum.page.append(table);

  var headingTr = $('<tr>');
  table.append(headingTr);
  var headingTd = $('<td>', { 'class': 'heading' });
  headingTr.append(headingTd);

  var breadcrumb = $('<div>', { 'class': 'breadcrumb' });
  headingTd.append(breadcrumb);
  breadcrumb.append($('<a>', {
    'class': 'pseudoLink',
    'text': 'Forum',
  }));
  breadcrumb.append(': ');
  breadcrumb.append($('<span>', {
    'class': 'mainBreadrumb',
    'text': Api.forum_board.boardName,
  }));
  headingTd.append($('<div>', {
    'class': 'subHeader minor',
    'text': Api.forum_board.description,
  }));

  var newThreadTd = $('<td>', { 'class': 'notes' });
  headingTr.append(newThreadTd);
  var newThreadButton = $('<input>', {
    'id': 'newThreadButton',
    'type': 'button',
    'value': 'New thread',
  });
  newThreadTd.append(newThreadButton);
  newThreadButton.click(Forum.toggleNewThreadForm);

  var newThreadTr = $('<tr>', { 'class': 'writePost' });
  table.append(newThreadTr);
  var contentTd = $('<td>', { 'class': 'body' });
  newThreadTr.append(contentTd);
  contentTd.append($('<input>', {
    'type': 'text',
    'class': 'title',
    'placeholder': 'Thread title...',
    'maxlength': Forum.TITLE_MAX_LENGTH,
  }));
  contentTd.append($('<textarea>', { 'maxlength': Forum.BODY_MAX_LENGTH }));
  var cancelButton = $('<input>', {
    'type': 'button',
    'value': 'Cancel',
  });
  contentTd.append(cancelButton);
  cancelButton.click(Forum.toggleNewThreadForm);
  var replyButton = $('<input>', {
    'type': 'button',
    'value': 'Post new thread',
  });
  contentTd.append(replyButton);
  replyButton.click(Forum.postNewThread);

  var notesTd = $('<td>', {
    'class': 'attribution',
  }).append(Forum.buildHelp());
  newThreadTr.append(notesTd);

  if (Api.forum_board.threads.length === 0) {
    var emptyTr = $('<tr>');
    table.append(emptyTr);
    emptyTr.append($('<td>', { 'text': 'No threads', 'class': 'title', }));
    emptyTr.append($('<td>', { 'html': '&nbsp;', 'class': 'notes', }));
  }

  $.each(Api.forum_board.threads, function(index, thread) {
    table.append(Forum.buildThreadRow(thread));
  });

  var markReadDiv = $('<div>', { 'class': 'markRead' });
  Forum.page.append(markReadDiv);
  var markReadButton = $('<input>', {
    'type': 'button',
    'value': 'Mark board as read',
  });
  markReadDiv.append(markReadButton);
  markReadButton.click(function() {
    Api.markForumBoardRead(Forum.showOverview);
  });

  // Actually lay out the page
  Forum.layOutPage();
};

Forum.showThread = function() {
  Forum.page = $('<div>', { 'class': 'forum' });
  if (!Api.verifyApiData('forum_thread', Forum.layOutPage)) {
    return;
  }

  var table = $('<table>', { 'class': 'posts' });
  Forum.page.append(table);

  // Well, this is awkward and ugly, but it *seems* to fix a problem I was
  // having. To wit: using table-layout: fixed; on a table, giving widths to
  // individual cells, but then starting the table with a row containing
  // colspan="2" cell meant that the individual widths of the cells in the
  // other rows were ignored. So instead, we'll start the table with a dummy
  // empty row with properly-widthed cells that will hopefully be invisible to
  // everyone.
  var dummyTr = $('<tr>');
  table.append(dummyTr);
  dummyTr.append($('<td>', { 'class': 'attribution' }));
  dummyTr.append($('<td>', { 'class': 'body' }));

  var headingTd = $('<td>', {
    'class': 'heading',
    'colspan': 2,
  });
  table.append(
    $('<tr>', { 'class': Api.forum_thread.boardShortName }).append(headingTd)
  );

  var breadcrumb = $('<div>', { 'class': 'breadcrumb' });
  headingTd.append(breadcrumb);
  breadcrumb.append($('<div>', {
    'class': 'mainBreadrumb',
    'text': Api.forum_thread.threadTitle,
  }));


  var subHeader = $('<div>', { 'class': 'subHeader' });
  headingTd.append(subHeader);
  subHeader.append($('<a>', {
    'class': 'pseudoLink',
    'text': 'Forum',
  }));
  subHeader.append(': ');
  subHeader.append($('<a>', {
    'class': 'pseudoLink',
    'text': Api.forum_thread.boardName,
    'data-boardId': Api.forum_thread.boardId,
  }));

  $.each(Api.forum_thread.posts, function(index, post) {
    table.append(Forum.buildPostRow(post));
  });

  var replyTr = $('<tr>', { 'class': 'writePost' });
  table.append(replyTr);

  replyTr.append($('<td>', {
    'class': 'attribution'
  }).append(Forum.buildHelp()));

  var replyBodyTd = $('<td>', { 'class': 'body' });
  replyTr.append(replyBodyTd);
  replyBodyTd.append($('<textarea>', {
    'placeholder': 'Reply to thread...',
    'maxlength': Forum.BODY_MAX_LENGTH,
  }));
  var replyButton = $('<input>', {
    'type': 'button',
    'value': 'Post reply',
    'maxlength': Forum.BODY_MAX_LENGTH,
  });
  replyBodyTd.append(replyButton);
  replyButton.click(Forum.replyToThread);

  var markReadDiv = $('<div>', { 'class': 'markRead' });
  Forum.page.append(markReadDiv);
  var markReadButton = $('<input>', {
    'type': 'button',
    'value': 'Mark thread as read',
  });
  markReadDiv.append(markReadButton);
  markReadButton.click(function() {
    Api.markForumThreadRead(Forum.showBoard);
  });

  // Actually lay out the page
  Forum.layOutPage();
};

Forum.layOutPage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  Forum.page.find('.pseudoLink').each(function() {
    $(this).click(Forum.linkToSubPage);
    var state = Forum.readStateFromElement(this);
    $(this).attr('href', 'forum.html' + Forum.buildUrlHash(state));
  });

  $('#forum_page').empty();
  $('#forum_page').append(Forum.page);

  Forum.scrollTo(Forum.scrollTarget);
};


////////////////////////////////////////////////////////////////////////
// These are events that are triggered by user actions

Forum.linkToSubPage = function() {
  var state = Forum.readStateFromElement(this);
  Env.history.pushState(state, 'Button Men Online &mdash; Forum',
    Forum.buildUrlHash(state));
  Forum.showPage();
};

Forum.toggleNewThreadForm = function() {
  // Using visibility rather than display: hidden so we don't reflow the table
  if ($('#newThreadButton').css('visibility') == 'visible') {
    $('#newThreadButton').css('visibility', 'hidden');
    $('tr.writePost textarea').val('');
    $('tr.writePost input.title').val('');
    $('tr.thread').hide();
    $('tr.writePost').show();
    $('tr.writePost input.title').focus();
  } else {
    $('tr.writePost').hide();
    $('tr.thread').show();
    $('#newThreadButton').css('visibility', 'visible');
  }
};

Forum.postNewThread = function() {
  var title = $(this).parent().find('input.title').val().trim();
  var body = $(this).parent().find('textarea').val().trim();
  if (!title || !body) {
    Env.message = {
      'type': 'error',
      'text': 'The thread title and body are both required',
    };
    Env.showStatusMessage();
    return;
  }
  Api.createForumThread(title, body, Forum.showThread);
};

Forum.replyToThread = function() {
  var body = $(this).parent().find('textarea').val().trim();
  if (!body) {
    Env.message = {
      'type': 'error',
      'text': 'The post body is required',
    };
    Env.showStatusMessage();
    return;
  }
  Api.createForumPost(body, Forum.showThread);
};

Forum.quotePost = function() {
  var postRow = $(this).closest('tr');
  var quotedText = postRow.find('td.body').attr('data-rawPost');
  var replyBox = $('tr.writePost td.body textarea');

  var replyText = replyBox.val();
  if (replyText && replyText.slice(-1) != '\n') {
    replyText += '\n';
  }
  replyText += '[quote]' + quotedText + '[/quote]' + '\n';

  replyBox.val(replyText);
  replyBox.prop('scrollTop', replyBox.prop('scrollHeight'));
  replyBox.focus();
  Forum.scrollTo(replyBox.closest('tr'));
};

////////////////////////////////////////////////////////////////////////
// These functions build HTML to help render the page

Forum.buildBoardRow = function(board) {
  var tr = $('<tr>', { 'class': board.shortName });

  var titleTd = $('<td>', { 'class': 'title' });
  tr.append(titleTd);

  titleTd.append($('<div>').append($('<a>', {
    'class': 'pseudoLink',
    'text': board.boardName,
    'data-boardId': board.boardId,
  })));

  titleTd.append($('<div>', {
    'class': 'minor',
    'text': board.description,
  }));

  var notesTd = $('<td>', { 'class': 'notes' });
  tr.append(notesTd);
  var numberOfThreads = board.numberOfThreads + ' thread' +
    (board.numberOfThreads != 1 ? 's ' : ' ');
  notesTd.append($('<div>', {
    'class': 'minor splitLeft',
    'text': numberOfThreads,
  }));
  var newDiv = $('<div>', { 'class': 'splitRight' });
  notesTd.append(newDiv);
  if (board.firstNewPostId) {
    newDiv.append($('<div>', { 'class': 'new' })
      .append('*')
      .append($('<a>', {
        'class': 'pseudoLink',
        'text': 'NEW',
        'data-threadId': board.firstNewPostThreadId,
        'data-postId': board.firstNewPostId,
      })).append('*')
    );
  }

  return tr;
};

Forum.buildThreadRow = function(thread) {
  var tr = $('<tr>', { 'class': 'thread' });

  var titleTd = $('<td>', { 'class': 'title' });
  tr.append(titleTd);

  titleTd.append($('<div>').append($('<a>', {
    'class': 'pseudoLink',
    'text': thread.threadTitle,
    'data-threadId': thread.threadId,
  })));

  var postDates =
    'Originally by ' +
      Forum.buildProfileLink(thread.originalPosterName).prop('outerHTML') +
      ' at ' + Env.formatTimestamp(thread.originalCreationTime) + '. ';
  if (thread.latestLastUpdateTime != thread.originalCreationTime) {
    postDates += 'Latest by ' +
      Forum.buildProfileLink(thread.latestPosterName).prop('outerHTML') +
        ' at ' + Env.formatTimestamp(thread.latestLastUpdateTime) + '.';
  }
  titleTd.append($('<div>', {
    'class': 'minor',
    'html': postDates,
  }));

  var notesTd = $('<td>', { 'class': 'notes' });
  tr.append(notesTd);
  var numberOfPosts =
    thread.numberOfPosts + ' post' + (thread.numberOfPosts != 1 ? 's ' : ' ');
  notesTd.append($('<div>', {
    'class': 'minor splitLeft',
    'text': numberOfPosts,
  }));
  var newDiv = $('<div>', { 'class': 'splitRight' });
  notesTd.append(newDiv);
  if (thread.firstNewPostId) {
    newDiv.append($('<div>', { 'class': 'new' })
      .append('*')
      .append($('<a>', {
        'class': 'pseudoLink',
        'text': 'NEW',
        'data-threadId': thread.threadId,
        'data-postId': thread.firstNewPostId,
      })).append('*')
    );
  }

  return tr;
};

Forum.buildPostRow = function(post) {
  var tr = $('<tr>');
  if (post.postId == Api.forum_thread.currentPostId) {
    Forum.scrollTarget = tr;
  }

  var attributionTd = $('<td>', { 'class': 'attribution' });
  tr.append(attributionTd);

  var nameDiv = $('<div>', {
    'class': 'name',
  });
  attributionTd.append(nameDiv);
  var anchorSymbol =
    ((post.postId == Api.forum_thread.currentPostId) ?
      Forum.SOLID_STAR :
      Forum.OPEN_STAR);
  var postAnchor = $('<span>', {
    'class': 'postAnchor',
    'data-threadId': Api.forum_thread.threadId,
    'data-postId': post.postId,
    'html': anchorSymbol,
  });
  nameDiv.append(postAnchor);
  nameDiv.append(Forum.buildProfileLink(post.posterName));

  postAnchor.click(function() {
    var state = Forum.readStateFromElement(this);
    Env.history.pushState(state, 'Button Men Online &mdash; Forum',
      Forum.buildUrlHash(state));
    $('.postAnchor').html(Forum.OPEN_STAR);
    $(this).html(Forum.SOLID_STAR);
    Forum.scrollTo($(this).closest('tr'));
  });

  if (post.isNew) {
    attributionTd.append($('<div>', {
      'class': 'new',
      'text': '*NEW*',
    }));
  }

  var bodyTd = $('<td>', { 'class': 'body' });
  tr.append(bodyTd);
  // Env.prepareRawTextForDisplay() converts the dangerous raw text
  // into safe HTML.
  bodyTd.append(Env.prepareRawTextForDisplay(post.body));
  bodyTd.append($('<hr>'));
  var postFooter = $('<div>', { 'class': 'postFooter' });
  bodyTd.append(postFooter);
  var postDates =
    'Posted at ' + Env.formatTimestamp(post.creationTime, 'datetime') + '. ';
  if (post.lastUpdateTime != post.creationTime) {
    postDates +=
      'Edited at ' + Env.formatTimestamp(post.lastUpdateTime, 'datetime') + '.';
  }
  postFooter.append($('<div>', {
    'class': 'splitLeft',
    'text': postDates,
  }));
  var quoteButton = $('<input>', { 'type': 'button', 'value': 'Quote' });
  postFooter.append($('<div>', { 'class': 'splitRight', }).append(quoteButton));
  quoteButton.click(Forum.quotePost);

  bodyTd.attr('data-rawPost', post.body);
  if (post.deleted) {
    bodyTd.addClass('deleted');
  }

  return tr;
};

Forum.buildProfileLink = function(playerName) {
  return $('<a>', {
    'href': 'profile.html?player=' + encodeURIComponent(playerName),
    'text': playerName,
  });
};

Forum.buildHelp = function() {
  var helpDiv = $('<div>', { 'text': 'Available markup: ' });
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[b]text[/b]: <span class="chatBold">text</span>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[i]text[/i]: <span class="chatItalic">text</span>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[u]text[/u]: <span class="chatUnderlined">text</span>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[s]text[/s]: <span class="chatStruckthrough">text</span>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[code]text[/code]: <span class="chatCode">text</span>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[spoiler]text[/spoiler]: <span class="chatSpoiler">text</span>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html':
      '[quote]text[/quote]: <span class="chatQuote">&nbsp;text&nbsp;</span>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[game="123"]: <a href="game.html?game=123">Game 123</a>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[player="Jota"]: <a href="profilep.html?player=Jota">Jota</a>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[[]b]text[/b]: [b]text[/b]',
  }));
  return helpDiv;
};

////////////////////////////////////////////////////////////////////////
// Miscellaneous utility functions

Forum.scrollTo = function(scrollTarget) {
  var scrollTop = 0;
  if (scrollTarget) {
    scrollTarget = $(scrollTarget);
    scrollTop = scrollTarget.offset().top - 5;
  }
  $('html, body').animate({ scrollTop: scrollTop },
    Forum.SCROLL_ANIMATION_MILLISECONDS);
};

Forum.readStateFromElement = function(stateElement) {
  var state = {
    'boardId': $(stateElement).attr('data-boardId'),
    'threadId': $(stateElement).attr('data-threadId'),
    'postId': $(stateElement).attr('data-postId'),
  };
  return state;
};

Forum.buildUrlHash = function(state) {
  var hash = '';
  if (state.boardId) {
    hash += '&boardId=' + state.boardId;
  }
  if (state.threadId) {
    hash += '&threadId=' + state.threadId;
  }
  if (state.postId) {
    hash += '&postId=' + state.postId;
  }
  if (hash) {
    hash = '#!' + hash.substr(1);
  }
  return hash;
};