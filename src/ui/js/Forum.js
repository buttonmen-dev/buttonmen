// namespace for this "module"
var Forum = {
  'boardId': null,
  'threadId': null,
  'postId': null,
  'scrollTarget': null,
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

  Forum.boardId = Env.getParameterByName('boardId');
  Forum.threadId = Env.getParameterByName('threadId');
  Forum.postId = Env.getParameterByName('postId');

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

  // Get all needed information for the current mode, then display the
  // appropriate version of the page
  if (Forum.threadId) {
    Api.loadForumThread(Forum.threadId, Forum.postId, Forum.showThread);
  } else if (Forum.boardId) {
    Api.loadForumBoard(Forum.boardId, Forum.showBoard);
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

  var boardDiv = $('<div>');
  Forum.page.append(boardDiv);
  boardDiv.append('Board ' + Api.forum_board.boardId + ': ' + Api.forum_board.boardName);
  boardDiv.append(' (' + Api.forum_board.description + '). ');

  var threadsTable = $('<table>');
  Forum.page.append(threadsTable);

  $.each(Api.forum_board.threads, function(threadId, thread) {
    var threadTr = $('<tr>');
    threadsTable.append(threadTr);
    var threadTd = $('<td>');
    threadTr.append(threadTd);
    threadTd.append('Thread ' + threadId + ': ' + thread.threadTitle + '. ');
    threadTd.append(thread.numberOfPosts + ' posts. First new post: ' + thread.firstNewPostId + '. ');
    threadTd.append('Started by ' + thread.originalPosterName + ' on ' + thread.originalCreationTime + '. ');
    threadTd.append('Latest by ' + thread.latestPosterName + ' on ' + thread.latestLastUpdateTime + '. ');
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

  $.each(Api.forum_thread.posts, function(postId, post) {
    table.append(Forum.buildPostRow(postId, post));
  });

  var replyTr = $('<tr>', { 'class': 'reply' });
  table.append(replyTr);
  replyTr.append($('<td>', {
    'class': 'attribution',
    'text': 'Reply to thread:',
  }));
  var replyBodyTd = $('<td>', { 'class': 'body' });
  replyTr.append(replyBodyTd);
  replyBodyTd.append($('<textarea>'));
  var replyButton = $('<input>', {
    'id': 'whyohwhyohwhy',
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
  if (scrollTarget) {
    scrollTarget = $(scrollTarget);
    var scrollTop = scrollTarget.offset().top - 5;
    $('html, body').animate({ scrollTop: scrollTop }, 0);
  }
};

Forum.linkToSubPage = function() {
  Forum.boardId = $(this).attr('data-boardId');
  Forum.threadId = $(this).attr('data-threadId');
  Forum.postId = $(this).attr('data-postId');

  Forum.showPage();
};

Forum.buildPostRow = function(postId, post) {
  var tr = $('<tr>');
  if (postId == Api.forum_thread.currentPostId) {
    Forum.scrollTarget = tr;
  }

  var attributionTd = $('<td>', { 'class': 'attribution' });
  tr.append(attributionTd);

  var nameDiv = $('<div>', {
    'class': 'name',
  });
  attributionTd.append(nameDiv);
  var anchorSymbol =
    ((postId == Api.forum_thread.currentPostId) ?
      Forum.SOLID_STAR :
      Forum.OPEN_STAR);
  var postAnchor = $('<span>', {
    'class': 'postAnchor',
    'href':
      'forum.html#!threadId=' + Api.forum_thread.threadId +
        '&postId=' + postId,
    'html': anchorSymbol,
  })
  nameDiv.append(postAnchor);
  nameDiv.append(post.posterName);

  postAnchor.click(function() {
    //TODO set the hashbang!
    $('.postAnchor').html(Forum.OPEN_STAR);
    $(this).html(Forum.SOLID_STAR);
    Forum.scrollTo(this);
  });

  attributionTd.append($('<div>', {
    'class': 'date',
    'text': 'Posted: ' + Env.formatTimestamp(post.creationTime, 'datetime'),
  }));
  if (post.lastUpdateTime != post.creationTime) {
    attributionTd.append($('<div>', {
      'class': 'date',
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
