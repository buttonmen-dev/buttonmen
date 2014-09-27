module("Forum", {
  'setup': function() {
    BMTestUtils.ForumPre = BMTestUtils.getAllElements();

    // Back up any properties that we might decide to replace with mocks
    BMTestUtils.ForumBackup = { };
    BMTestUtils.CopyAllMethods(Forum, BMTestUtils.ForumBackup);

    BMTestUtils.setupFakeLogin();

    // Create the forum_page div so functions have something to modify
    if (document.getElementById('forum_page') == null) {
      $('body').append($('<div>', {'id': 'forum_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.forum_overview;
    delete Api.forum_board;
    delete Api.forum_thread;
    delete Env.window.location.href;
    delete Env.window.location.search;
    delete Env.window.location.hash;
    delete Env.history.state;
    delete Forum.page;
    delete Forum.scrollTarget;
    delete Login.message;

    // Page elements
    $('#forum_page').remove();
    $('#forum_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Restore any properties that we might have replaced with mocks
    BMTestUtils.CopyAllMethods(BMTestUtils.ForumBackup, Forum);

    // Fail if any other elements were added or removed
    BMTestUtils.ForumPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.ForumPost, BMTestUtils.ForumPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Forum module has been loaded
test("test_Forum_is_loaded", function() {
  ok(Forum, "The Forum namespace exists");
});

test("test_Forum.showForumPage", function() {
  expect(3); // tests plus teardown test
  $('div#forum_page').remove();
  Env.window.location.hash = '#!threadId=6';
  Forum.showPage = function() {
    equal($('div#forum_page').length, 1,
      '#forum_page should exist after showForumPage() is called');
    equal(Env.history.state.threadId, 6,
      'History state should be set to match location hash');
  };
  Forum.showForumPage();
});

asyncTest("test_Forum.showPage", function() {
  expect(2); // tests plus teardown test

  Env.history.state = { };
  Forum.arrangePage = Forum.showBoard = Forum.showThread =
    function() {
      ok(false, 'Forum.showPage() should call Forum.showOverview()');
      start();
    };
  Forum.showOverview = function() {
    ok(true, 'Forum.showPage() should call the Forum.showOverview()');
    start();
  };
  Forum.showPage();
});

asyncTest("test_Forum.showPage_board", function() {
  expect(2); // tests plus teardown test

  Env.history.state = { 'boardId': 3 };
  Forum.arrangePage = Forum.showOverview = Forum.showThread =
    function() {
      ok(false, 'Forum.showPage() should call Forum.showBoard()');
      start();
    };
  Forum.showBoard = function() {
    ok(true, 'Forum.showPage() should call the Forum.showBoard()');
    start();
  };
  Forum.showPage();
});

asyncTest("test_Forum.showPage_thread", function() {
  expect(2); // tests plus teardown test

  Env.history.state = { 'threadId': 6 };
  Forum.arrangePage = Forum.showOverview = Forum.showBoard =
    function() {
      ok(false, 'Forum.showPage() should call Forum.showThread()');
      start();
    };
  Forum.showThread = function() {
    ok(true, 'Forum.showPage() should call the Forum.showThread()');
    start();
  };
  Forum.showPage();
});

asyncTest("test_Forum.showOverview", function() {
  Login.message = $('<div>');
  Api.loadForumOverview(function() {
    Forum.showOverview();
    ok(Forum.page.find('table.boards').length > 0,
       'The created page should have a table of the boards in the forum');
    start();
  });
});

asyncTest("test_Forum.showBoard", function() {
  Login.message = $('<div>');
  Api.loadForumBoard(1, function() {
    Forum.showBoard();
    ok(Forum.page.find('table.threads').length > 0,
       'The created page should have a table of the threads on the board');
    start();
  });
});

asyncTest("test_Forum.showThread", function() {
  Login.message = $('<div>');
  Api.loadForumThread(1, 2, function() {
    Forum.showThread();
    ok(Forum.page.find('table.posts').length > 0,
       'The created page should have a table of the posts in the thread');
    start();
  });
});

test("test_Forum.arrangePage", function() {
  Login.message = $('<div>');
  Forum.page = $('<div>');
  Forum.page.append($('<p>', { 'text': 'hi world', }));
  Forum.page.append($('<a>', { 'class': 'pseudoLink', }));
  Forum.arrangePage();
  var pseudoLink = $('a.pseudoLink');
  equal(pseudoLink.length, 1, 'There should be one pseudoLink on the page.');
  ok(pseudoLink.attr('href'),
    'The pseudoLink should have been assigned an href');
});

test("test_Forum.formLinkToSubPage", function() {
  var element = $('<a>', {
    'data-threadId': 6,
    'data-postId': 9,
  });
  Forum.showPage = function() { };
  // .call() calls a function, setting the passed-in parameter as 'this'
  Forum.formLinkToSubPage.call(element, $.Event());
  equal(Env.history.state.threadId, 6,
    'The threadId should be set in the history state');
  equal(Env.history.state.threadId, 6,
    'The postId should be set in the history state');
});

test("test_Forum.toggleNewThreadForm", function() {
  $('#forum_page').append($('<input>', {
    'type': 'button',
    'id': 'newThreadButton',
    'visibility': 'visible',
  }));
  Forum.toggleNewThreadForm();
  equal($('#newThreadButton').css('visibility'), 'hidden',
    '#newThreadButton should have been hidden');
  Forum.toggleNewThreadForm();
  equal($('#newThreadButton').css('visibility'), 'visible',
    '#newThreadButton should have been unhidden');
});

asyncTest("test_Forum.formPostNewThread", function() {
  expect(2); // tests plus teardown test

  Api.forum_board = { 'boardId': 3 };

  var formHolder = $('<div>');
  formHolder.append($('<input>', { 'class': 'title', 'value': 'Test', } ));
  formHolder.append($('<textarea>', { 'text': 'Test body' } ));
  var button = $('<input>', { 'type': 'button' });
  formHolder.append(button);

  Forum.showThread = function() {
    equal(Api.forum_thread.load_status, 'ok',
      'The thread should be loaded after it was added');
    start();
  };
  // .call() calls a function, setting the passed-in parameter as 'this'
  Forum.formPostNewThread.call(button);
});

asyncTest("test_Forum.formReplyToThread", function() {
  expect(2); // tests plus teardown test

  Api.forum_thread = { 'threadId': 3 };

  var formHolder = $('<div>');
  formHolder.append($('<textarea>', { 'text': 'Test body' } ));
  var button = $('<input>', { 'type': 'button' });
  formHolder.append(button);

  Forum.showThread = function() {
    equal(Api.forum_thread.load_status, 'ok',
       'The thread should be loaded after it was replied to');
    start();
  };
  // .call() calls a function, setting the passed-in parameter as 'this'
  Forum.formReplyToThread.call(button);
});

test("test_Forum.quotePost", function() {
  var postText = 'woe to thee';

  var postTr = $('<tr>', { 'class': 'writePost' });
  var postBody = $('<td>', { 'class': 'body', 'data-rawPost': postText, });
  postTr.append(postBody);
  var button = $('<input>', { 'type': 'button' });
  postBody.append(button);

  var replyTr = $('<tr>', { 'class': 'writePost' });
  $('#forum_page').append(replyTr);
  var replyBox = $('<textarea>');
  replyTr.append($('<td>', { 'class': 'body' }).append(replyBox));

  // .call() calls a function, setting the passed-in parameter as 'this'
  Forum.quotePost.call(button);
  var replyText = replyBox.val();
  ok(replyText.match(postText),
    'Reply textarea should contain quoted post text');
});

test("test_Forum.buildBoardRow", function() {
  var board = {
    'boardId': 3,
    'boardName': 'Features and Bugs',
    'boardColor': '#d0e0f0',
    'threadColor': '#e7f0f7',
    'description':
      'Feedback on new features that have been added, features you\'d like ' +
        'to see or bugs you\'ve discovered.',
    'numberOfThreads': 3,
    'firstNewPostId': 9,
    'firstNewPostThreadId': 3,
  };

  var row = Forum.buildBoardRow(board);
  var boardLink = row.find('a.pseudoLink');
  equal(boardLink.attr('data-boardId'), 3,
    'Row should contain a link to the board.');
});

test("test_Forum.buildThreadRow", function() {
  var thread = {
    'threadId': 6,
    'threadTitle': 'Who likes ice cream?',
    'numberOfPosts': 3,
    'originalPosterName': 'tester',
    'originalCreationTime': 1401055337,
    'latestPosterName': 'tester2',
    'latestLastUpdateTime': 1401055397,
    'firstNewPostId': 9,
  };

  var row = Forum.buildThreadRow(thread);
  var boardLink = row.find('a.pseudoLink');
  equal(boardLink.attr('data-threadId'), 6,
    'Row should contain a link to the thread.');
});

test("test_Forum.buildPostRow", function() {
  Api.forum_thread = { 'currentPostId': null };

  var post = {
    'postId': 9,
    'posterName': 'tester',
    'creationTime': 1401055337,
    'lastUpdateTime': 1401055337,
    'isNew': true,
    'body': 'I can\'t be the only one!',
    'deleted': false,
  };

  var row = Forum.buildPostRow(post);
  var newFlag = row.find('.new');
  equal(newFlag.length, 1, 'Row should indicate the post is new.');
});

test("test_Forum.buildHelp", function() {
  var help = Forum.buildHelp();
  var helpText = help.text();
  ok(helpText.match(/\[spoiler]/), 'Help text should mention BB code tags.');
});

test("test_Forum.scrollTo", function() {
  expect(3); // tests plus teardown test

  var massiveDiv = $('<div>', { 'html': '&nbsp;' });
  massiveDiv.css('height', '5000px');
  $('#forum_page').append(massiveDiv);
  var scrollTarget = $('<div>', { 'html': '&nbsp;' });
  $('#forum_page').append(scrollTarget);

  // This is a bit kludgy, but scrolling is apparently not something that is
  // not practical to test here, so let's mock away...
  var real$ = $;
  $ = function(selector) {
    mockObj = real$(selector);
    mockObj.animate = function(options, duration) {
      notEqual(options.scrollTop, 0,
        'Forum.scrollTo() should attempt to scroll down the page');
      equal(duration, Forum.SCROLL_ANIMATION_MILLISECONDS,
        'Scrolling duration should be configured in pseudoconstant');
    };
    return mockObj;
  };
  Forum.scrollTo(scrollTarget);
  $ = real$;
});

test("test_Forum.readStateFromElement", function() {
  var element = $('<a>', {
    'data-threadId': 6,
    'data-postId': 9,
  });
  var state = Forum.readStateFromElement(element);

  equal(state.boardId, undefined, 'The boardId should not be set.');
  equal(state.threadId, 6, 'The threadId should be set.');
  equal(state.postId, 9, 'The postId should be set.');
});

test("test_Forum.buildUrlHash", function() {
  var state = { 'boardId': 3 };
  var hash = Forum.buildUrlHash(state);

  equal(hash, '#!boardId=3', 'The hash should reflect the state.');
});

asyncTest("test_Forum.parseFormPost", function() {
  expect(2); // tests plus teardown test

  Forum.parseFormPost(
    {
      'type': 'markForumRead',
      'timestamp': 0,
    }, 'forum_overview',
    null,
    function() {
      equal(Api.forum_overview.load_status, 'ok', 'Response be loaded');
      start();
    }
  );
});

test("test_Forum.showError", function() {
  Login.message = $('<div>');
  Env.setupEnvStub();
  Env.message = {
    'type': 'error',
    'text': 'Test error.',
  };
  Forum.showError();
  equal($('#env_message').text(), 'Test error.', 'Error displayed');
});
