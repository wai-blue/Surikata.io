<?php

spl_autoload_register(function($class) {
  $class = trim(str_replace("\\", "/", $class), "/");

  if (preg_match('/^Surikata\/Core\/([\w\/]+)/', $class, $m)) {
    require_once(ADMIN_PANEL_SRC_DIR."/Core/{$m[1]}.php");
  } else if (preg_match('/^Surikata\/Lib\/([\w\/]+)/', $class, $m)) {
    require_once(ADMIN_PANEL_SRC_DIR."/Lib/{$m[1]}.php");
  } else if (preg_match('/^Surikata\/Plugins\/([\w\/]+)/', $class, $m)) {
    $file_1 = PLUGINS_DIR."/{$m[1]}/Main.php";
    $file_2 = PLUGINS_DIR."/{$m[1]}.php";
    if (is_file($file_1)) {
      require_once($file_1);
    } if (is_file($file_2)) {
      require_once($file_2);
    }
  // } else if (preg_match('/^Surikata\/Controllers\/([\w\/]+)/', $class, $m)) {
  //   $file_1 = CONTROLLERS_DIR."/{$m[1]}/Main.php";
  //   $file_2 = CONTROLLERS_DIR."/{$m[1]}.php";
  //   if (is_file($file_1)) {
  //     require_once($file_1);
  //   } if (is_file($file_2)) {
  //     require_once($file_2);
  //   }
  } else if (preg_match('/^Surikata\/Themes\/([\w\/]+)/', $class, $m)) {
    $file_1 = THEMES_DIR."/{$m[1]}/Main.php";
    $file_2 = THEMES_DIR."/{$m[1]}.php";

    if (is_file($file_1)) {
      require_once($file_1);
    } if (is_file($file_2)) {
      require_once($file_2);
    }
  }
});

