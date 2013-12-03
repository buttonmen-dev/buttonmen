// Test utilities belong to the BMTestUtils module
var BMTestUtils = {};

// Utility to get all elements in the document DOM
// This is used to detect whether modules are erroneously modifying the DOM
BMTestUtils.getAllElements = function() {
  var elementInfo = [];
  var allElements = document.getElementsByTagName("*");
  for (var i=0, max=allElements.length; i < max; i++) {
    var elemNode = allElements[i].nodeName;
    var elemId = allElements[i].id;
    var elemClass = allElements[i].className;

    // Skip module-name and test-name SPAN elements created by QUnit itself
    if ((elemNode == "SPAN") && (elemId == "") &&
        ((elemClass == "module-name") || (elemClass == "test-name"))) {
      continue;
    }

    elementInfo.push(
      "node=" + elemNode + ", id=" + elemId + ", class=" + elemClass
    );
  }
  return elementInfo;
}
