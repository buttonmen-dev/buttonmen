<?php

  // Each PHP file in these directories should contain exactly one class, named after the filename
  $php_compliant_dirs = array(
    "src/engine",
    "test/src/engine",
    "test/src/engine/Utility",
  );

  // These directories may contain PHP files which we don't check for class compliance
  $php_noncompliant_dirs = array(
    "deploy/pagodabox",
    "src/api",
    "src/database",
  );

  // Any other directories shouldn't contain PHP files

  function find_all_dirs(&$dirs, $startdir) {
    $php_skip_subdirs = array( '.', '..', '.git', );
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
      unset($output);
      exec('grep "^class .* {" ' . $phpfile, $output);
      $numlines = count($output);
      if ($numlines == 0) {
        $problems[] = "PHP file contains no classes: $phpfile";
      } elseif ($numlines > 1) {
        $problems[] = "PHP file contains more than one class: $phpfile";
      } else {
        $classinfo = explode(" ", $output[0]);
        $expected_class = basename($phpfile, ".php");
        $found_class = $classinfo[1];
        if ($expected_class != $found_class) {
          $problems[] = "Class in PHP file doesn't match file name: $phpfile";
        }
      }
    }
  }

  function verify_no_php_files(&$problems, $subdir) {
    foreach (glob("$subdir/*.php") as $phpfile) {
      $problems[] = "PHP file in unexpected directory: $phpfile";
    }
  }

  // Actually do the testing
  $testdir = $argv[1];
  print "Testing class/file layout compliance of PHP files under: $testdir\n";
  chdir($testdir);

  $subdirs = array();
  $problems = array();

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
