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
    "deploy/circleci",
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

  // In these directories, we limit the use of explicit (int) casts
  $php_limit_int_cast_dirs = array(
    "src/api",
    "src/engine",
  );
  $php_limit_int_cast_exceptions = array(
    // exceptions for $_SESSION variables
    "src/api/ApiResponder.php" => 2,
    "src/api/api_core.php" => 1,
    // exception for the API-spec-level validate and cast of all numeric args to int
    "src/api/ApiSpec.php" => 2,
    // exceptions for the three int types BMDB supports on select
    "src/engine/BMDB.php" => 3,

    // arbitrarily high exceptions because we are not yet limiting casts in these files (#712)
    "src/engine/BMAttack.php" => 1000,
    "src/engine/BMDie.php" => 1000,
    "src/engine/BMDieSwing.php" => 1000,
    "src/engine/BMGame.php" => 1000,
    "src/engine/BMInterfaceGame.php" => 1000,
    "src/engine/BMPlayer.php" => 1000,
    // This file is converted to BMDB, but we haven't yet done the exercise of ensuring that
    // subdie values reconstructed from flags are always ints.
    // Leave those casts in place until we do
    "src/engine/BMInterface.php" => 2,
  );

  // In these directories, we limit explicit references to $_SESSION variables
  $php_limit_sessionvar_dirs = array(
    "src/api",
    "src/database",
    "src/engine",
    "src/lib",
  );
  // Files in the above dirs which are allowed to contain $_SESSION variables at all
  $php_limit_sessionvar_exceptions = array(
    "src/api/api_core.php",
    "src/api/ApiResponder.php",
  );
  // Limit direct access to $_SESSION['user_id'] so we can control its type
  $php_limit_session_user_id_files = array(
    "src/api/ApiResponder.php" => 2,
  );

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

  function verify_limited_int_casts(&$problems, $subdir, $exceptions) {
    foreach (glob("$subdir/*.php") as $phpfile) {
      unset($cast_count_output);
      exec('grep "(int)" ' . $phpfile, $cast_count_output);
      $num_casts = count($cast_count_output);
      if (array_key_exists($phpfile, $exceptions)) {
        $allowed_casts = $exceptions[$phpfile];
      } else {
        $allowed_casts = 0;
      }
      if ($num_casts > $allowed_casts) {
        $problems[] = "PHP file contains more than expected " . $allowed_casts . " explicit (int) casts: $phpfile";
      }
    }
  }

  function verify_limited_sessionvars(&$problems, $subdir, $exceptions, $userid_limits) {
    foreach (glob("$subdir/*.php") as $phpfile) {
      if (in_array($phpfile, $exceptions)) {
        if (array_key_exists($phpfile, $userid_limits)) {
          // This file can contain $_SESSION variables at all, but $_SESSION['user_id'] is limited
          $allowed_userid = $userid_limits[$phpfile];
          unset($grep_output);
          exec('grep "\$_SESSION\[\'user_id\'\]" ' . $phpfile, $grep_output);
          if (count($grep_output) > $allowed_userid) {
            $problems[] = "PHP file contains more than expected " . $allowed_userid . " explicit references to SESSION user_id: $phpfile";
          }
        }
      } else {
        // Files without exceptions aren't allowed to contain any $_SESSION variables
        unset($grep_output);
        exec('grep "\$_SESSION" ' . $phpfile, $grep_output);
        if (count($grep_output) > 0) {
          $problems[] = "PHP file contains explicit references to SESSION variables: $phpfile";
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
    if (in_array($subdir, $php_limit_int_cast_dirs)) {
      verify_limited_int_casts($problems, $subdir, $php_limit_int_cast_exceptions);
    }
    if (in_array($subdir, $php_limit_sessionvar_dirs)) {
      verify_limited_sessionvars($problems, $subdir, $php_limit_sessionvar_exceptions, $php_limit_session_user_id_files);
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
