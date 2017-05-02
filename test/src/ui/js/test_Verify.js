module("Verify", {
  'setup': function() {
    BMTestUtils.VerifyPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the verify_page div so functions have something to modify
    if (document.getElementById('verify_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'verify_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete Verify.page;

    // Page elements
    $('#verify_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.VerifyPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.VerifyPost, BMTestUtils.VerifyPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Verify module has been loaded
test("test_Verify_is_loaded", function(assert) {
  assert.ok(Verify, "The Verify namespace exists");
});

// The purpose of this test is to demonstrate that the flow of
// Verify.showLoggedInPage() is correct for a showXPage function,
// namely that it calls an API getter with a showStatePage function
// as a callback.
//
// Accomplish this by mocking the invoked functions
test("test_Verify.showLoggedInPage", function(assert) {
  expect(5);
  var cached_getVerifyParams = Verify.getVerifyParams;
  var cached_showStatePage = Verify.showStatePage;
  var getVerifyParamsCalled = false;
  Verify.showStatePage = function() {
    assert.ok(getVerifyParamsCalled, "Verify.getVerifyParams is called before Verify.showStatePage");
  };
  Verify.getVerifyParams = function(callback) {
    getVerifyParamsCalled = true;
    assert.equal(callback, Verify.showStatePage,
      "Verify.getVerifyParams is called with Verify.showStatePage as an argument");
    callback();
  };

  Verify.showLoggedInPage();
  var item = document.getElementById('verify_page');
  assert.equal(item.nodeName, "DIV",
        "#verify_page is a div after showLoggedInPage() is called");
  Verify.getVerifyParams = cached_getVerifyParams;
  Verify.showStatePage = cached_showStatePage;
});

// At this point, Verify.showLoggedOutPage is a pointer to
// Verify.showLoggedInPage
test("test_Verify.showLoggedOutPage", function(assert) {
  assert.equal(Verify.showLoggedOutPage, Verify.showLoggedInPage,
    "Verify.showLoggedOutPage and Verify.showLoggedInPage should be the same");
});

test("test_Verify.getVerifyParams", function(assert) {
  stop();
  Verify.getVerifyParams(function() {
    assert.equal(Env.message.type, "success",
          "getVerifyParams() succeeds in its POST");
    start();
  });
});

test("test_Verify.showStatePage", function(assert) {
// FIXME: put a test here that actually meaningfully tests the code
});

test("test_Verify.setVerifyUserSuccessMessage", function(assert) {
  Verify.setVerifyUserSuccessMessage('test success');
  assert.equal(Env.message.type, 'success', 'message type is success');
});

test("test_Verify.setVerifyUserFailureMessage", function(assert) {
  Verify.setVerifyUserFailureMessage('test failure');
  assert.equal(Env.message.type, 'error', 'message type is error');
});
