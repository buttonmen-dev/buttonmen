<?php

  // Each PHP file in these directories should contain exactly one
  // class, named after the filename
  $php_compliant_dirs = array(
    "src/engine",
    "test/src/api",
    "test/src/engine",
    "test/src/engine/Utility"
  );

  // These directories may contain PHP files which we don't check
  // for class compliance
  $php_noncompliant_dirs = array(
    "deploy/amp",
    "deploy/jenkins",
    "deploy/pagodabox",
    "src/api",
    "src/database",
    "src/lib",
    "test/src/database"
  );

  // Each PHP file in the first directory must contain a matching
  // test file in the second directory
  $php_test_dirs = array(
    "src/engine" => "test/src/engine"
  );

  // Any other directories shouldn't contain PHP files

  function find_all_dirs(&$dirs, $startdir) {
    $php_skip_subdirs = array( '.', '..', '.git' );
    $dirs[] = $startdir;
    foreach (scandir($startdir) as $dirfile) {
      if (in_array($dirfile, $php_skip_subdirs)) {
        continue;
      }
      if ($startdir == '.') {
        $dirpath = "$dirfile";
      } else {
        $dirpath = "$startdir/$dirfile";
      }
      if (is_dir($dirpath)) {
        find_all_dirs($dirs, "$dirpath");
      }
    }
  }

  function verify_php_file_classes(&$problems, $subdir) {
    foreach (glob("$subdir/*.php") as $phpfile) {
      unset($concrete_output);
      exec('grep "^class .* {" ' . $phpfile, $concrete_output);
      $concrete_classes = count($concrete_output);
      unset($abstract_output);
      exec('grep "^abstract class .* {" ' . $phpfile, $abstract_output);
      $abstract_classes = count($abstract_output);
      $classes = $concrete_classes + $abstract_classes;
      if ($classes == 0) {
        $problems[] = "PHP file contains no classes: $phpfile";
      } elseif ($classes > 1) {
        $problems[] = "PHP file contains more than one class: $phpfile";
      } elseif ($concrete_classes == 1) {
        $classinfo = explode(" ", $concrete_output[0]);
        $expected_class = basename($phpfile, ".php");
        $found_class = $classinfo[1];
        if ($expected_class != $found_class) {
          $problems[] = "Class in PHP file doesn't match file name: $phpfile";
        }
      } elseif ($abstract_classes == 1) {
        $classinfo = explode(" ", $abstract_output[0]);
        $expected_class = basename($phpfile, ".php");
        $found_class = $classinfo[2];
        if ($expected_class != $found_class) {
          $problems[] = "Abstract class in PHP file doesn't match file name: $phpfile";
        }
      }
    }
  }

  function verify_no_php_files(&$problems, $subdir) {
    foreach (glob("$subdir/*.php") as $phpfile) {
      $problems[] = "PHP file in unexpected directory: $phpfile";
    }
  }

  function verify_php_test_coverage(&$problems, $srcdir, $testdir) {
    foreach (glob("$srcdir/*.php") as $srcfile) {
      $phpfile = basename($srcfile, '.php');
      $testfile = "$testdir/${phpfile}Test.php";
      if (!is_file($testfile)) {
        $problems[] =
          "No test file $testfile corresponding to source file $srcfile";
      }
    }
  }

  // Actually do the testing
  $testdir = $argv[1];
  print "Testing class/file layout compliance of PHP files under: $testdir\n";
  chdir($testdir);

  $subdirs = array();
  $problems = array();

  // Iterate over all subdirectories looking for unexpected or
  // misnamed PHP files
  find_all_dirs($subdirs, ".");
  foreach ($subdirs as $subdir) {
    if (in_array($subdir, $php_noncompliant_dirs)) {
      // don't run any checks for this directory
    } elseif (in_array($subdir, $php_compliant_dirs)) {
      verify_php_file_classes($problems, $subdir);
    } else {
      verify_no_php_files($problems, $subdir);
    }
  }

  // Do unit test coverage checks
  foreach ($php_test_dirs as $srcdir => $testdir) {
    verify_php_test_coverage($problems, $srcdir, $testdir);
  }

  if (count($problems) > 0) {
    print "PHP files found which don't comply with layout standards:\n";
    foreach ($problems as $problem) {
      print "  $problem\n";
    }
    exit(1);
  }
  print "OK\n";
  exit(0);
?>
