// namespace for this "module"
var Forum = {
  'scrollTarget': undefined,
};

Forum.bodyDivId = 'forum_page';
Forum.pageTitle = 'Forum &mdash; Button Men Online';

Forum.OPEN_STAR = '&#9734;';
Forum.SOLID_STAR = '&#9733;';

// I believe that a mySQL TEXT column can hold up to 2^16 - 1 bytes of UTF-8
// text, and a UTF-8 character can theoretically be up to four bytes wide (even
// if this is rare in practice), so our post bodies should be guaranteed to be
// able to hold at least (2^16 - 1)/4 characters.
Forum.FORUM_BODY_MAX_LENGTH = 16000;
Forum.FORUM_TITLE_MAX_LENGTH = 100;

Forum.SCROLL_ANIMATION_MILLISECONDS = 200;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Forum.showLoggedInPage() is the landing function. Always call
//   this first. It sets up #forum_page and reads the URL to find out
//   the current board, thread and/or post, which it sets in Env.history.state.
//   It also binds Forum.showPage() to the page event that triggers on the
//   forward/backward button. Then it calls Forum.showPage() directly.
// * Forum.showPage() reads the state it was passed to find out what it should
//   be displaying. Then it calls the API to set either Api.forum_overview,
//   Api.forum_board or Api.forum_thread as appropriate, then passes control
//   to Forum.showOverview(), Forum.showBoard() or Forum.showThread().
// * Forum.showOverview() builds a version of Forum.page that includes a list
//   of boards on the Forum. Then it calls Forum.arrangePage().
// * Forum.showBoard() builds a version of Forum.page that includes a list
//   of threads on a given board and a form to create a new one (attaching the
//   Forum.formPostNewThread() event to it). Then it calls Forum.arrangePage().
// * Forum.showThread() builds a version of Forum.page that includes a list
//   of posts on a given thread and a form to create a new one (attaching the
//   Forum.formReplyToThread() event to it). Then it calls Forum.arrangePage().
// * Forum.arrangePage() calls Login.arrangePage(). It also binds
//   Forum.formLinkToSubPage to every .pseudoLink element (e.g., the links to a
//   given board or thread).
//
// Major events:
// * Forum.formLinkToSubPage() is called every time a user clicks on an internal
//   link that brings them from one part of the forum to another. It examines
//   the element that was clicked on to find out the board, thread and/or post
//   it's posting to, sets that information in Env.history.state, then calls
//   Form.showPage().
// * Forum.formPostNewThread() calls the API to create a new thread, setting
//   Api.forum_thread with the results. It then calls Forum.showThread().
// * Forum.formReplyToThread() calls the API to create a new post, setting
//   Api.forum_thread with the results. It then calls Forum.showThread().
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// These functions are part of the main action flow to load the page

Forum.showLoggedInPage = function() {
  $(window).bind('popstate', Forum.showPage);

  var state = {
    'boardId': Env.getParameterByName('boardId'),
    'threadId': Env.getParameterByName('threadId'),
    'postId': Env.getParameterByName('postId'),
  };
  Env.history.replaceState(state, Forum.pageTitle, Env.window.location.hash);
  Forum.showPage(state);
};

Forum.showPage = function(state) {
  // If this was called from a popState event, the parameter might be an event
  // object containing a state rather than the state itself
  if (state.state !== undefined) {
    state = state.state;
  }
  if (state.originalEvent !== undefined) {
    state = state.originalEvent.state;
  }

  // If no usable state has been found yet, regenerate it from the URL
  if ((state === undefined) || (state === null)) {
    state = {
      'boardId': Env.getParameterByName('boardId'),
      'threadId': Env.getParameterByName('threadId'),
      'postId': Env.getParameterByName('postId'),
    };
  }

  // Display the appropriate version of the page depending on the current state
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
  if (!Api.verifyApiData('forum_overview', Forum.arrangePage)) {
    return;
  }

  $('title').html(Forum.pageTitle);

  var table = $('<table>', { 'class': 'boards floatable' });
  Forum.page.append(table);

  var thead = $('<thead>');
  table.append(thead);

  var headingTr = $('<tr>');
  thead.append(headingTr);
  var headingTd = $('<td>', { 'class': 'heading' });
  headingTr.append(headingTd);

  var breadcrumb = $('<div>', { 'class': 'breadcrumb' });
  headingTd.append(breadcrumb);
  breadcrumb.append($('<span>', {
    'class': 'mainBreadcrumb',
    'text': 'Button Men Forums',
  }));

  headingTr.append($('<td>', { 'class': 'notes', 'html': '&nbsp;', }));

  var tbody = $('<tbody>');
  table.append(tbody);

  $.each(Api.forum_overview.boards, function(index, board) {
    tbody.append(Forum.buildBoardRow(board));
  });

  var markReadTd = $('<td>', { 'class': 'markRead', 'colspan': 2, });
  tbody.append($('<tr>').append(markReadTd));
  var markAllBoardsReadButton = $('<input>', {
    'type': 'button',
    'value': 'Mark all boards as read',
  });
  markReadTd.append(markAllBoardsReadButton);
  markAllBoardsReadButton.click(function() {
    Forum.parseFormPost(
      {
        'type': 'markForumRead',
        'timestamp': Api.forum_overview.timestamp,
      },
      'forum_overview',
      $(this),
      function() {
        Api.getNextNewPostId(function() {
          Login.addNewPostLink();
          Forum.showOverview();
        });
      }
    );
  });

  // Actually lay out the page
  Forum.arrangePage();
};

Forum.showBoard = function() {
  Forum.page = $('<div>', { 'class': 'forum' });
  if (!Api.verifyApiData('forum_board', Forum.arrangePage)) {
    return;
  }

  $('title').html(Api.forum_board.boardName +
    ' &mdash; ' + Forum.pageTitle);

  var table = $('<table>', {
    'class': 'threads floatable'
  });
  Forum.page.append(table);

  var thead = $('<thead>');
  table.append(thead);

  var headingTr = $('<tr>');
  thead.append(headingTr);
  var headingTd = $('<td>', { 'class': 'heading' });
  headingTr.append(headingTd);
  headingTd.css('background-color', Api.forum_board.boardColor);

  var breadcrumb = $('<div>', { 'class': 'breadcrumb' });
  headingTd.append(breadcrumb);
  breadcrumb.append($('<a>', {
    'class': 'pseudoLink',
    'text': 'Forum',
  }));
  breadcrumb.append(': ');
  breadcrumb.append($('<span>', {
    'class': 'mainBreadcrumb',
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

  var tbody = $('<tbody>');
  table.append(tbody);

  var newThreadTr = $('<tr>', { 'class': 'writePost' });
  tbody.append(newThreadTr);
  var contentTd = $('<td>', { 'class': 'body' });
  newThreadTr.append(contentTd);
  contentTd.append($('<input>', {
    'type': 'text',
    'class': 'title',
    'placeholder': 'Thread title...',
    'maxlength': Forum.FORUM_TITLE_MAX_LENGTH,
  }));
  contentTd.append($('<textarea>', {
    'maxlength': Forum.FORUM_BODY_MAX_LENGTH
  }));
  var replyButton = $('<input>', {
    'type': 'button',
    'value': 'Post new thread',
  });
  contentTd.append(replyButton);
  replyButton.click(Forum.formPostNewThread);
  var cancelButton = $('<input>', {
    'type': 'button',
    'value': 'Cancel',
  });
  contentTd.append(cancelButton);
  cancelButton.click(Forum.toggleNewThreadForm);

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
    tbody.append(Forum.buildThreadRow(thread, Api.forum_board.threadColor));
  });

  var markReadTd = $('<td>', { 'class': 'markRead', 'colspan': 2, });
  tbody.append($('<tr>').append(markReadTd));
  var markBoardReadButton = $('<input>', {
    'id': 'markBoardReadButton',
    'type': 'button',
    'value': 'Mark board as read',
  });
  markReadTd.append(markBoardReadButton);
  markBoardReadButton.click(function() {
    Forum.parseFormPost(
      {
        'type': 'markForumBoardRead',
        'boardId': Api.forum_board.boardId,
        'timestamp': Api.forum_board.timestamp,
      },
      'forum_overview',
      $(this),
      function() {
        Api.getNextNewPostId(function() {
          Login.addNewPostLink();
          Forum.showOverview();
        });
      }
    );
  });

  // Actually lay out the page
  Forum.arrangePage();
};

Forum.showThread = function() {
  Forum.page = $('<div>', { 'class': 'forum' });
  if (!Api.verifyApiData('forum_thread', Forum.arrangePage)) {
    return;
  }

  // Don't display special characters (such as "&auml;") in page title
  var tempDiv = $('<div>', { 'text': Api.forum_thread.threadTitle});
  var pageTitle = tempDiv.html();

  $('title').html(pageTitle + ' &mdash; ' + Forum.pageTitle);

  var table = $('<table>', { 'class': 'posts floatable' });
  Forum.page.append(table);

  var thead = $('<thead>');
  table.append(thead);

  // Well, this is awkward and ugly, but it *seems* to fix a problem I was
  // having. To wit: using table-layout: fixed; on a table, giving widths to
  // individual cells, but then starting the table with a row containing
  // colspan="2" cell meant that the individual widths of the cells in the
  // other rows were ignored. So instead, we'll start the table with a dummy
  // empty row with properly-widthed cells that will hopefully be invisible to
  // everyone.
  var dummyTr = $('<tr>', { 'class': 'dummy' });
  thead.append(dummyTr);
  dummyTr.append($('<td>', { 'class': 'attribution' }));
  dummyTr.append($('<td>', { 'class': 'body' }));

  var headingTd = $('<td>', {
    'class': 'heading',
    'colspan': 2,
  });
  thead.append($('<tr>').append(headingTd));
  headingTd.css('background-color', Api.forum_thread.boardThreadColor);

  var breadcrumb = $('<div>', { 'class': 'breadcrumb' });
  headingTd.append(breadcrumb);
  breadcrumb.append($('<div>', {
    'class': 'mainBreadcrumb',
    'text': Api.forum_thread.threadTitle,
  }));

  var subHeader = $('<div>', { 'class': 'subHeader' });
  headingTd.append(subHeader);

  var linksBack = $('<div>', { 'class': 'linksBack' });
  subHeader.append(linksBack);

  linksBack.append($('<a>', {
    'class': 'pseudoLink',
    'text': 'Forum',
  }));
  linksBack.append(': ');
  linksBack.append($('<a>', {
    'class': 'pseudoLink',
    'text': Api.forum_thread.boardName,
    'data-boardId': Api.forum_thread.boardId,
  }));

  var linksWithin = $('<div>', { 'class': 'linksWithin' });
  subHeader.append(linksWithin);
  // We'll populate this div with links later on, after we've created all the
  // stuff we're linking to

  var tbody = $('<tbody>');
  table.append(tbody);

  $.each(Api.forum_thread.posts, function(index, post) {
    tbody.append(Forum.buildPostRow(post));
  });

  var replyTr = $('<tr>', { 'class': 'writePost' });
  tbody.append(replyTr);

  replyTr.append($('<td>', {
    'class': 'attribution'
  }).append(Forum.buildHelp()));

  var replyBodyTd = $('<td>', { 'class': 'body' });
  replyTr.append(replyBodyTd);
  var replyBodyTextArea = $('<textarea>', {
    'placeholder': 'Reply to thread...',
    'maxlength': Forum.FORUM_BODY_MAX_LENGTH,
  });
  replyBodyTextArea.on('change keyup paste', function() {
    if ('' === $(this).val().trim()) {
      if ('disabled' == $('.markThreadReadButton').attr('disabled')) {
        $('.markThreadReadButton').removeAttr('disabled');
        $('.markThreadReadButton').removeAttr('title');
      }
    } else {
      if ('disabled' != $('.markThreadReadButton').attr('disabled')) {
        $('.markThreadReadButton').attr('disabled', 'disabled');
        $('.markThreadReadButton').attr('title',
          'Disabled because there is text in the reply box');
      }
    }
  });
  replyBodyTd.append(replyBodyTextArea);

  var replyButton = $('<input>', {
    'type': 'button',
    'value': 'Post reply',
  });
  replyBodyTd.append(replyButton);

  replyButton.click(Forum.formReplyToThread);

  var markReadTd = $('<td>', { 'class': 'markRead', 'colspan': 2, });
  table.append($('<tr>').append(markReadTd));
  var markThreadReadButton = $('<input>', {
    'class': 'markThreadReadButton',
    'type': 'button',
    'value': 'Mark thread as read',
  });
  markReadTd.append(markThreadReadButton);
  markThreadReadButton.click(function() {
    Forum.parseFormPost(
      {
        'type': 'markForumThreadRead',
        'threadId': Api.forum_thread.threadId,
        'boardId': Api.forum_thread.boardId,
        'timestamp': Api.forum_thread.timestamp,
      },
      'forum_board',
      $(this),
      function() {
        Api.getNextNewPostId(function() {
          Login.addNewPostLink();
          Forum.showBoard();
        });
      }
    );
  });

  linksWithin.append($('<a>', {
    'text': 'Jump to top',
  }).click(function () { Forum.scrollTo(); }));
  linksWithin.append(' | ');
  linksWithin.append($('<a>', {
    'text': 'Jump to bottom',
  }).click(function() { Forum.scrollTo(replyTr); }));
  linksWithin.append(' | ');
  linksWithin.append($('<a>', {
    'text': 'Jump to next new post',
    'data-boardId': Api.forum_thread.boardId,
    'data-threadId': Api.forum_thread.threadId,
  }).click(Forum.jumpToNextNewPost));
  linksWithin.append(' | ');
  linksWithin.append(markThreadReadButton.clone(true));

  // Actually lay out the page
  Forum.arrangePage();
};

Forum.arrangePage = function() {
  var pseudoLinks =
    Forum.page.find('.pseudoLink').add(Login.message.find('.pseudoLink'));
  pseudoLinks.each(function() {
    $(this).click(Forum.formLinkToSubPage);
    var state = Forum.readStateFromElement(this);
    $(this).attr('href', 'forum.html' + Forum.buildUrlHash(state));
  });
  Forum.page.find('a.postAnchor').each(function() {
    var state = Forum.readStateFromElement(this);
    $(this).attr('href', 'forum.html' + Forum.buildUrlHash(state));
  });

  Login.arrangePage(Forum.page);

  $('table.floatable').floatThead();

  Forum.scrollTo(Forum.scrollTarget);

  // the page should have stopped loading by now, but add an extra 100 ms
  // to deal with slow page loads, then reflow the floating header
  setTimeout(function() { $('table.floatable').trigger('reflow'); }, 100);
};


////////////////////////////////////////////////////////////////////////
// These are events that are triggered by user actions

Forum.formLinkToSubPage = function(e) {
  // Don't let confused browsers execute click events for things that
  // aren't proper clicks!
  var button = (e.which || e.button);
  if (button > 1 || e.ctrlKey || e.metaKey || e.shiftKey) {
    return;
  }

  e.preventDefault();
  var state = Forum.readStateFromElement(this);
  Env.history.pushState(state, Forum.pageTitle, Forum.buildUrlHash(state));
  Env.message = null;
  Forum.showPage(state);
};

Forum.toggleNewThreadForm = function() {
  // Using visibility rather than display: hidden so we don't reflow the table
  if ($('#newThreadButton').css('visibility') == 'visible') {
    $('#newThreadButton').css('visibility', 'hidden');
    $('#markBoardReadButton').css('visibility', 'hidden');
    $('tr.writePost textarea').val('');
    $('tr.writePost input.title').val('');
    $('tr.thread').hide();
    $('tr.writePost').show();
    $('tr.writePost input.title').focus();
  } else {
    $('tr.writePost').hide();
    $('tr.thread').show();
    $('#newThreadButton').css('visibility', 'visible');
    $('#markBoardReadButton').css('visibility', 'visible');
    Env.message = null;
    Env.showStatusMessage();
  }
};

Forum.formPostNewThread = function() {
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

  var args = {
    'type': 'createForumThread',
    'boardId': Api.forum_board.boardId,
    'title': title,
    'body': body,
  };

  Forum.parseFormPost(args, 'forum_thread', $(this), Forum.showThread);
};

Forum.formReplyToThread = function() {
  var body = $(this).parent().find('textarea').val().trim();
  if (!body) {
    Env.message = {
      'type': 'error',
      'text': 'The post body is required',
    };
    Env.showStatusMessage();
    return;
  }

  var args = {
    'type': 'createForumPost',
    'threadId': Api.forum_thread.threadId,
    'body': body,
  };

  Forum.parseFormPost(args, 'forum_thread', $(this), Forum.showThread);
};

Forum.quotePost = function() {
  var postRow = $(this).closest('tr');
  var quotedText = postRow.find('td.body').attr('data-rawPost');
  var quotee = postRow.find('td.attribution div.name a:nth-child(2)').text();
  var replyBox = $('tr.writePost td.body textarea');

  var replyText = replyBox.val();
  if (replyText && replyText.slice(-1) != '\n') {
    replyText += '\n';
  }
  replyText += '[quote=' + quotee + ']' + quotedText + '[/quote]' + '\n';

  replyBox.val(replyText);
  replyBox.change();
  replyBox.prop('scrollTop', replyBox.prop('scrollHeight'));
  replyBox.focus();
  Forum.scrollTo(replyBox.closest('tr'));
};

Forum.editPost = function() {
  $('.markThreadReadButton').attr('disabled', 'disabled');
  $('.markThreadReadButton').attr('title', 'Disabled when editing a reply');

  var postRow = $(this).closest('tr');
  var oldText = postRow.find('td.body').attr('data-rawPost');
  var postId = $(this).attr('data-postId');

  var bodyTd = postRow.find('td.body');
  var editTd = $('<td>', { 'class': 'editBody' });

  editTd.append($('<textarea>', {
    'text': oldText,
    'maxlength': Forum.FORUM_BODY_MAX_LENGTH,
  }));


  var saveButton = $('<input>', {
    'type': 'button',
    'value': 'Save edits',
    'data-postId': postId,
  });
  editTd.append(saveButton);
  saveButton.click(Forum.formSaveEditPost);

  var cancelButton = $('<input>', {
    'type': 'button',
    'value': 'Cancel',
  });
  editTd.append(cancelButton);
  cancelButton.click(Forum.cancelEditPost);

  bodyTd.hide();
  postRow.append(editTd);
};

Forum.cancelEditPost = function() {
  if ('disabled' == $('.markThreadReadButton').attr('disabled')) {
    $('.markThreadReadButton').removeAttr('disabled');
    $('.markThreadReadButton').removeAttr('title');
  }

  var postRow = $(this).closest('tr');
  postRow.find('td.editBody').remove();
  postRow.find('td.body').show();
};

Forum.formSaveEditPost = function() {
  var body = $(this).parent().find('textarea').val().trim();
  var postId = $(this).attr('data-postId');

  if (!body) {
    Env.message = {
      'type': 'error',
      'text': 'The post body is required',
    };
    Env.showStatusMessage();
    return;
  }

  var args = {
    'type': 'editForumPost',
    'postId': postId,
    'body': body,
  };

  Forum.parseFormPost(args, 'forum_thread', $(this), Forum.showThread);
};

Forum.jumpToNextNewPost = function(e) {
  // In order to determine which post is the "next" new one, we're going to
  // pick an arbitrary point a little bit below the header and say that the
  // first new post below that point is "next"
  var cutOffPoint = 0;
  var floatContainer = $('div.forum > .floatThead-container');
  if (floatContainer.length) {
    cutOffPoint = floatContainer.offset().top + floatContainer.height() + 20;
  }

  var newPostId = 0;
  $('tr:has(.postAnchor):has(.new)').each(function(index, row) {
    if ($(row).offset().top > cutOffPoint) {
      newPostId = $(row).find('.postAnchor').attr('data-postId');
      return false;
    }
  });

  if (newPostId > 0) {
    $(this).attr('data-postId', newPostId);

    // Now that we've primed the link to behave like a pseudolink pointing at
    // the post in question, simulate a click event as if it were that
    // pseudolink
    return Forum.formLinkToSubPage.call(this, e);
  }
  else {
    Forum.scrollTo($('tr.writePost'));
  }
};

////////////////////////////////////////////////////////////////////////
// These functions build HTML to help render the page

Forum.buildBoardRow = function(board) {
  var tr = $('<tr>');

  var titleTd = $('<td>', { 'class': 'title' });
  tr.append(titleTd);
  titleTd.css('background-color', board.boardColor);

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

Forum.buildThreadRow = function(thread, threadColor) {
  var tr = $('<tr>', { 'class': 'thread' });

  var titleTd = $('<td>', { 'class': 'title' });
  tr.append(titleTd);
  titleTd.css('background-color', threadColor);

  titleTd.append($('<div>').append($('<a>', {
    'class': 'pseudoLink',
    'text': thread.threadTitle,
    'data-threadId': thread.threadId,
  })));

  var postDates =
    'Originally by ' +
      Env.buildProfileLink(thread.originalPosterName).prop('outerHTML') +
      ' at ' + Env.formatTimestamp(thread.originalCreationTime) + '. ';
  if (thread.latestLastUpdateTime != thread.originalCreationTime) {
    postDates += 'Latest by ' +
      Env.buildProfileLink(thread.latestPosterName).prop('outerHTML') +
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
  attributionTd.css('background-color', post.posterColor);

  var nameDiv = $('<div>', {
    'class': 'name',
  });
  attributionTd.append(nameDiv);
  var anchorSymbol =
    ((post.postId == Api.forum_thread.currentPostId) ?
      Forum.SOLID_STAR :
      Forum.OPEN_STAR);
  var postAnchor = $('<a>', {
    'class': 'postAnchor',
    'data-threadId': Api.forum_thread.threadId,
    'data-postId': post.postId,
    'html': anchorSymbol,
  });
  nameDiv.append(postAnchor);
  nameDiv.append(Env.buildProfileLink(post.posterName));

  postAnchor.click(function(e) {
    e.preventDefault();
    var state = Forum.readStateFromElement(this);
    Env.history.pushState(state, 'Forum &mdash; Button Men Online',
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
  var buttonHolder = $('<div>', { 'class': 'splitRight', });
  if (post.posterName == Login.player && !post.deleted) {
    var editButton = $('<input>', {
      'type': 'button',
      'value': 'Edit',
      'data-postId': post.postId,
    });
    buttonHolder.append(editButton);
    editButton.click(Forum.editPost);
  }
  var quoteButton = $('<input>', { 'type': 'button', 'value': 'Quote' });
  buttonHolder.append(quoteButton);
  postFooter.append(buttonHolder);
  quoteButton.click(Forum.quotePost);

  bodyTd.attr('data-rawPost', post.body);
  if (post.deleted) {
    bodyTd.addClass('deleted');
  }

  return tr;
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
      '[quote]text[/quote]: ',
  }));
  helpDiv.append($('<div>', {
    'class': 'subHelp',
    'html':
      '<span class="chatQuote">' +
      '<span class="chatQuotee">Quote:</span>' +
      '&nbsp;text&nbsp;</span>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html':
      '[quote=Jota]text[/quote]: ',
  }));
  helpDiv.append($('<div>', {
    'class': 'subHelp',
    'html':
      '<span class="chatQuote">' +
      '<span class="chatQuotee">Jota said:</span>' +
      '&nbsp;text&nbsp;</span>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[game=123]: <a href="game.html?game=123">Game 123</a>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[player=Jota]: <a href="profile.html?player=Jota">Jota</a>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[button=Avis]: <a href="buttons.html?button=Avis">Avis</a>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[set=Soldiers]: <a href="buttons.html?set=Soldiers">Soldiers</a>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[wiki=UBFC]: ' +
            '<a href="http://buttonweavers.wikia.com/wiki/UBFC">' +
            'Wiki: UBFC</a>',
  }));
  helpDiv.append($('<div>', {
    'class': 'help',
    'html': '[issue=1841]: <a href=' +
            '"https://github.com/buttonmen-dev/buttonmen/issues/1841"' +
            '>Issue 1841</a>',
  }));
  helpDiv.append($('<text>', {
    'text': 'For actual brackets: ',
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

    var floatContainer = $('div.forum > .floatThead-container');
    if (floatContainer.length) {
      scrollTop -= (floatContainer.height() + 8);
    }
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
  } else {
    hash = '#';
  }

  return hash;
};

Forum.parseFormPost = function(args, apiKey, submitButton, callback) {
  Env.message = null;

  var messages = {
    'ok': {
      'type': 'function',
      'msgfunc': function(message, data) {
        Api[apiKey] = { 'load_status': 'ok' };
        Api.parseGenericData(data, apiKey);
      },
    },
    'notok': { 'type': 'server', },
  };

  Api.apiFormPost(args, messages, submitButton, callback, Forum.showError);
};

Forum.showError = function() {
  Forum.page = $('<div>', { 'class': 'forum' });
  Forum.arrangePage();
};
