// namespace for this "module"
var Forum = {
  'boardId': null,
  'threadId': null,
  'postId': null,
  'mode': null,
};

Forum.FORUM_MODE_OVERVIEW = 'OVERVIEW';
Forum.FORUM_MODE_BOARD = 'BOARD';
Forum.FORUM_MODE_THREAD = 'THREAD';

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Forum.showForumPage() is the landing function. Always call
//   this first. It sets up #forum_page and reads the URL to find out
//   if we're in overview, board or thread mode. Then it Forum.showPage()
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
  Forum.page = $('<div>');

  if (!Login.logged_in) {
    Env.message = {
      'type': 'error',
      'text': 'Can\'t view the forum because you\'re not logged in',
    };
    Forum.layoutPage();
    return;
  }

  // Get all needed information for the current mode, then display the
  // appropriate version of the page
  if (Forum.threadId) {
    Api.loadForumThread(Forum.threadId, Forum.showThread);
  } else if (Forum.boardId) {
    Api.loadForumBoard(Forum.boardId, Forum.showBoard);
  } else {
    Api.loadForumOverview(Forum.showOverview);
  }
};

Forum.showOverview = function() {
  if (!Api.verifyApiData('forum_overview')) {
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
  Forum.layoutPage();
};

Forum.showBoard = function() {
  if (!Api.verifyApiData('forum_board')) {
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
  Forum.layoutPage();
};

Forum.showThread = function() {
  if (!Api.verifyApiData('forum_thread')) {
    return;
  }

  var threadDiv = $('<div>');
  Forum.page.append(threadDiv);
  threadDiv.append('Thread ' + Api.forum_thread.threadId + ': ' + Api.forum_thread.threadTitle);
  threadDiv.append(' (Board ' + Api.forum_thread.boardId + ': ' + Api.forum_thread.boardName + '). ');

  var postsTable = $('<table>');
  Forum.page.append(postsTable);

  $.each(Api.forum_thread.posts, function(postId, post) {
    var postTr = $('<tr>');
    postsTable.append(postTr);
    var postTd = $('<td>');
    postTr.append(postTd);
    postTd.append('Post ' + postId + ' by ' + post.posterName + ' on ' + post.creationTime + '(updated ' + post.lastUpdateTime + '). ');
    postTd.append('New? ' + post.isNew + '. Deleted? ' + post.deleted + '. Body: ' + post.body);
  });

  // Actually lay out the page
  Forum.layoutPage();
};

Forum.layoutPage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#forum_page').empty();
  $('#forum_page').append(Forum.page);
};
