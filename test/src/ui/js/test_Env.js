module("Env", {
  'setup': function() {
    BMTestUtils.EnvPre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create
    BMTestUtils.deleteEnvMessage();

    // Fail if any other elements were added or removed
    BMTestUtils.EnvPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.EnvPre, BMTestUtils.EnvPost,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Env module has been loaded
test("test_Env_is_loaded", function() {
  expect(2); // number of tests plus 1 for the teardown test
  ok(Env, "The Env namespace exists");
});

// Can't test this as written because we can't modify the real
// location.search, and Env.getParameterByName won't accept a fake one
test("test_Env.getParameterByName", function() {
  expect(1); // number of tests plus 1 for the teardown test
});

test("test_Env.setupEnvStub", function() {
  expect(4); // number of tests plus 1 for the teardown test

  var item = document.getElementById('env_message');
  equal(item, null, "#env_message is null before setupEnvStub is called");

  // Setup the env stub, display the message, and verify that it is empty
  Env.setupEnvStub();
  item = document.getElementById('env_message');
  equal(item.nodeName, "DIV",
        "#env_message is a div after setupEnvStub is called");
  equal(item.innerHTML, "",
        "#env_message has empty HTML after setupEnvStub is called");
});

test("test_Env.showStatusMessage", function() {
  expect(5); // number of tests plus 1 for the teardown test

  // Setup the env stub, display the message, and verify that it is empty
  Env.setupEnvStub();
  Env.showStatusMessage();
  var item = $('#env_message');
  equal(item.html(), "", "Initial #env_message is empty");

  // Set message text, try to display the message, and verify the text/color
  Env.message = {
    'type': 'error',
    'text': 'test message text'
  };
  Env.showStatusMessage();
  equal(item.html(), "<p><font color=\"red\">test message text</font></p>",
        "Populated #env_message has expected text and color");

  // Modify message text, try to display the message, and verify the text/color
  Env.message = {
    'type': 'success',
    'text': 'new message text'
  };
  Env.showStatusMessage();
  equal(item.html(), "<p><font color=\"green\">new message text</font></p>",
        "Modified #env_message has expected text and color");

  // Set invalid message type, and verify the default of no color
  Env.message = {
    'type': 'foobar',
    'text': 'newer message text'
  };
  Env.showStatusMessage();
  equal(item.html(), "<p><font>newer message text</font></p>",
        "#env_message has expected text and color when type is invalid");
});
