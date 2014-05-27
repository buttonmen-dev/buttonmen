// namespace for this "module"
var Forum = {
  'scrollTarget': undefined,
};

Forum.OPEN_STAR = '&#9734;';
Forum.SOLID_STAR = '&#9733;';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Forum.showForumPage() is the landing function. Always call
//   this first. It sets up #forum_page and reads the URL to find out
//   the current board, thread and/or post. Then it calls Forum.showPage()
// * Forum.showPage() calls the API to set either Api.forum_overview,
//   Api.forum_board or Api.forum_thread as appropriate, then passes control
//   to Forum.showOverview(), Forum.showBoard() or Forum.showThread()
// * Forum.showOverview() populates the ... yeah, stuff
//
////////////////////////////////////////////////////////////////////////

Forum.showForumPage = function() {
  // Setup necessary elements for displaying status messages
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#forum_page').length === 0) {
    $('body').append($('<div>', {'id': 'forum_page', }));
  }

  var boardId = Env.getParameterByName('boardId');
  var threadId = Env.getParameterByName('threadId');
  var postId = Env.getParameterByName('postId');

  Forum.showPage(boardId, threadId, postId);
};

Forum.showPage = function(boardId, threadId, postId) {
  if (!Login.logged_in) {
    Env.message = {
      'type': 'error',
      'text': 'Can\'t view the forum because you\'re not logged in',
    };
    Forum.layOutPage();
    return;
  }

  // Get all needed information for the current mode, then display the
  // appropriate version of the page
  if (threadId) {
    Api.loadForumThread(threadId, postId, Forum.showThread);
  } else if (boardId) {
    Api.loadForumBoard(boardId, Forum.showBoard);
  } else {
    Api.loadForumOverview(Forum.showOverview);
  }
};

Forum.showOverview = function() {
  Forum.page = $('<div>', { 'class': 'forum' });
  if (!Api.verifyApiData('forum_overview', Forum.layOutPage)) {
    return;
  }

  Forum.page.append($('<h2>', {'text': 'Message Boards', }));

  var boardsTable = $('<table>');
  Forum.page.append(boardsTable);

  $.each(Api.forum_overview.boards, function(boardId, board) {
    var boardTr = $('<tr>');
    boardsTable.append(boardTr);
    var boardTd = $('<td>');
    boardTr.append(boardTd);
    boardTd.append('Board ' + boardId + ': ' + board.boardName + '. ');
    boardTd.append('Description: ' + board.description + '. ');
    boardTd.append(board.numberOfThreads + ' threads. First new post: ' + board.firstNewPostId + ', in thread: ' + board.firstNewPostThreadId);
  });

  // Actually lay out the page
  Forum.layOutPage();
};

Forum.showBoard = function() {
  Forum.page = $('<div>', { 'class': 'forum' });
  if (!Api.verifyApiData('forum_board', Forum.layOutPage)) {
    return;
  }

  var table = $('<table>', { 'class': 'threads' });
  Forum.page.append(table);

  var headingTr = $('<tr>');
  table.append(headingTr);
  var headingTd = $('<td>', { 'class': 'heading' });
  headingTr.append(headingTd);

  var breadcrumb = $('<div>', { 'class': 'breadcrumb' });
  headingTd.append(breadcrumb);
  breadcrumb.append($('<span>', {
    'class': 'pseudoLink',
    'text': 'Forum',
  }));
  breadcrumb.append(' &gt; ');
  breadcrumb.append($('<span>', {
    'class': 'boardNameHeader',
    'text': Api.forum_board.boardName,
  }));
  headingTd.append($('<div>', {
    'class': 'boardDescriptionHeader',
    'text': Api.forum_board.description,
  }));

  var toggleNewThreadForm = function() {
    // Using visibility rather than display: hidden so we don't reflow the table
    if ($('#newThreadButton').css('visibility') == 'visible') {
      $('#newThreadButton').css('visibility', 'hidden');
      $('tr.writePost textarea').val('');
      $('tr.writePost input.title').val('');
      $('tr.writePost').show();
      $('tr.writePost input.title').focus();
    } else {
      $('tr.writePost').hide();
      $('#newThreadButton').css('visibility', 'visible');
    }
  };

  var newThreadTd = $('<td>', { 'class': 'heading' });
  headingTr.append(newThreadTd);
  var newThreadButton = $('<input>', {
    'id': 'newThreadButton',
    'type': 'button',
    'value': 'New thread',
  });
  newThreadTd.append(newThreadButton);
  newThreadButton.click(toggleNewThreadForm);

  var newThreadTr = $('<tr>', { 'class': 'writePost' });
  table.append(newThreadTr);
  var contentTd = $('<td>', { 'class': 'body' });
  newThreadTr.append(contentTd);
  contentTd.append($('<input>', {
    'type': 'text',
    'class': 'title',
    'placeholder': 'Thread title...',
  }));
  contentTd.append($('<textarea>'));
  var cancelButton = $('<input>', {
    'type': 'button',
    'value': 'Cancel',
  });
  contentTd.append(cancelButton);
  cancelButton.click(toggleNewThreadForm);
  var replyButton = $('<input>', {
    'type': 'button',
    'value': 'Post new thread',
  });
  contentTd.append(replyButton);
  replyButton.click(Forum.postNewThread);

  //TODO when we support BB code, put instructions for it here
  var notesTd = $('<td>', {
    'class': 'attribution',
    'html': '&nbsp;',
  });
  newThreadTr.append(notesTd);

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

  var headingTd = $('<td>', {
    'class': 'heading',
    'colspan': 2,
  });
  table.append($('<tr>').append(headingTd));

  var breadcrumb = $('<div>', { 'class': 'breadcrumb' });
  headingTd.append(breadcrumb);
  breadcrumb.append($('<span>', {
    'class': 'pseudoLink',
    'text': 'Forum',
  }));
  breadcrumb.append(' &gt; ');
  breadcrumb.append($('<span>', {
    'class': 'pseudoLink',
    'text': Api.forum_thread.boardName,
    'data-boardId': Api.forum_thread.boardId,
  }));
  breadcrumb.append(' &gt; ');

  headingTd.append($('<div>', {
    'class': 'titleHeader',
    'text': Api.forum_thread.threadTitle,
  }));

  $.each(Api.forum_thread.posts, function(index, post) {
    table.append(Forum.buildPostRow(post));
  });

  var replyTr = $('<tr>', { 'class': 'writePost' });
  table.append(replyTr);
  //TODO when we support BB code, put instructions for it here
  replyTr.append($('<td>', {
    'class': 'attribution',
    'html': '&nbsp;',
  }));
  var replyBodyTd = $('<td>', { 'class': 'body' });
  replyTr.append(replyBodyTd);
  replyBodyTd.append($('<textarea>', { 'placeholder': 'Reply to thread...' }));
  var replyButton = $('<input>', {
    'type': 'button',
    'value': 'Post reply',
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

  Forum.page.find('.pseudoLink').click(Forum.linkToSubPage);

  $('#forum_page').empty();
  $('#forum_page').append(Forum.page);

  Forum.scrollTo(Forum.scrollTarget);
};

Forum.scrollTo = function(scrollTarget) {
  var scrollTop = 0;
  if (scrollTarget) {
    scrollTarget = $(scrollTarget);
    scrollTop = scrollTarget.offset().top - 5;
  }
  $('html, body').animate({ scrollTop: scrollTop }, 200);
};

Forum.linkToSubPage = function() {
  var boardId = $(this).attr('data-boardId');
  var threadId = $(this).attr('data-threadId');
  var postId = $(this).attr('data-postId');

  Forum.showPage(boardId, threadId, postId);
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
    'href':
      'forum.html#!threadId=' + Api.forum_thread.threadId +
        '&postId=' + post.postId,
    'html': anchorSymbol,
  })
  nameDiv.append(postAnchor);
  nameDiv.append(post.posterName);

  postAnchor.click(function() {
    //TODO set the hashbang!
    $('.postAnchor').html(Forum.OPEN_STAR);
    $(this).html(Forum.SOLID_STAR);
    Forum.scrollTo($(this).closest('tr'));
  });

  attributionTd.append($('<div>', {
    'class': 'minor',
    'text': 'Posted: ' + Env.formatTimestamp(post.creationTime, 'datetime'),
  }));
  if (post.lastUpdateTime != post.creationTime) {
    attributionTd.append($('<div>', {
      'class': 'minor',
      'text': 'Edited: ' + Env.formatTimestamp(post.lastUpdateTime, 'datetime'),
    }));
  }

  if (post.isNew) {
    attributionTd.append($('<div>', {
      'class': 'new',
      'text': '*NEW*',
    }));
  }

  var bodyTd = $('<td>', { 'class': 'body' });
  tr.append(bodyTd);
  // TODO use the code from the chat to escape stuff here
  bodyTd.append(post.body);
  if (post.deleted) {
    bodyTd.addClass('deleted');
  }

  return tr;
};

Forum.replyToThread = function() {
  //TODO this needs validation!
  var body = $(this).parent().find('textarea').val();
  Api.createForumPost(body, Forum.showThread);
};

Forum.postNewThread = function() {
  //TODO this needs validation!
  var title = $(this).parent().find('input.title').val();
  var body = $(this).parent().find('textarea').val();
  Api.createForumThread(title, body, Forum.showThread);
};

Forum.buildThreadRow = function(thread) {
  var tr = $('<tr>');

  var titleTd = $('<td>', { 'class': 'threadTitle' });
  tr.append(titleTd);

  titleTd.append($('<div>', {
    'class': 'pseudoLink',
    'text': thread.threadTitle,
    'data-threadId': thread.threadId,
  }));

  var postDates =
    'Originally by ' + thread.originalPosterName + ' at ' +
      Env.formatTimestamp(thread.originalCreationTime) + '.';
  if (thread.latestLastUpdateTime != thread.originalCreationTime) {
    postDates += ' Updated by ' + thread.latestPosterName + ' at ' +
      Env.formatTimestamp(thread.latestLastUpdateTime) + '.';
  }
  titleTd.append($('<div>', {
    'class': 'minor',
    'text': postDates,
  }));

  var notesTd = $('<td>', { 'class': 'threadNotes' });
  tr.append(notesTd);
  var numberOfPosts =
    thread.numberOfPosts + ' post' + (thread.numberOfPosts != 1 ? 's ' : ' ');
  notesTd.append($('<div>', {
    'class': 'minor',
    'text': numberOfPosts,
  }));
  if (thread.firstNewPostId) {
    notesTd.append($('<div>', {
      'class': 'pseudoLink new',
      'text': '*NEW*',
      'data-threadId': thread.threadId,
      'data-postId': thread.firstNewPostId,
    }));
  }

  return tr;
};