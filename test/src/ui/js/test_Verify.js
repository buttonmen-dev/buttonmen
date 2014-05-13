module("Verify", {
  'setup': function() {
    BMTestUtils.VerifyPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the verify_page div so functions have something to modify
    if (document.getElementById('verify_page') == null) {
      $('body').append($('<div>', {'id': 'verify_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JS objects
    delete Verify.page;

    // Page elements
    $('#verify_page').remove();
    $('#verify_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.VerifyPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.VerifyPost, BMTestUtils.VerifyPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Verify module has been loaded
test("test_Verify_is_loaded", function() {
  ok(Verify, "The Verify namespace exists");
});

asyncTest("test_Verify.showVerifyPage", function() {
  Verify.showVerifyPage();
  var item = document.getElementById('verify_page');
  equal(item.nodeName, "DIV",
        "#verify_page is a div after showVerifyPage() is called");
  start();
});

asyncTest("test_Verify.getVerifyParams", function() {
  Verify.getVerifyParams(function() {
    equal(Env.message.type, "success",
          "getVerifyParams() succeeds in its POST");
    start();
  });
});

test("test_Verify.showStatePage", function() {
  Env.setupEnvStub();
  Env.message = {
    'type': 'error',
    'text': 'test error',
  };
  Verify.showStatePage();
  var item = document.getElementById('env_message');
  ok(item.innerHTML.match('test error'), "env message is set by this function");
});

test("test_Verify.setVerifyUserSuccessMessage", function() {
  Verify.setVerifyUserSuccessMessage('test success');
  equal(Env.message.type, 'success', 'message type is success');
});

test("test_Verify.setVerifyUserFailureMessage", function() {
  Verify.setVerifyUserFailureMessage('test failure');
  equal(Env.message.type, 'error', 'message type is error');
});
